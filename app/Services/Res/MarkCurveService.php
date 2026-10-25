<?php

namespace App\Services\Res;

use App\Models\Res\Grade;
use App\Models\Res\Mark;
use App\Models\Res\MarkCurve;
use App\Models\Res\Subject;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * معالجة تشوّه توزيع درجات مادة: عرض التوزيع، معاينة التعديل، تطبيقه، والتراجع عنه.
 *
 * التعديل يمسّ درجة الامتحان (mark) فقط ويُقاس التوزيع على المجموع (امتحان + أعمال سنة).
 * كل إجراء يحفظ قيم كل درجة قبله وبعده (MarkCurve + MarkCurveItem) ليُتراجع عنه كاملاً.
 *
 * @phpstan-type Change array{mark: Mark, total: int, newMark: int, newTotal: int}
 * @phpstan-type Params array{from?: int|float, to?: int|float, delta?: int|float, targetMean?: int|float, targetSd?: int|float}
 */
class MarkCurveService
{
    public function __construct(
        private readonly MarkEntryService $entry,
        private readonly RegulationService $regulation,
    ) {}

    /**
     * درجات المادة القابلة للمعالجة: لها درجة ولا سبب مطبّق عليها.
     *
     * @return Collection<int, Mark>
     */
    public function marksFor(Subject $subject, int|string $batch, int|string $examType): Collection
    {
        return Mark::query()
            ->with('student')
            ->where('subject_code', $subject->code)
            ->where('batch_code', $batch)
            ->where('level_code', $subject->level_code)
            ->where('examType', (int) $examType)
            ->whereNotNull('mark')
            ->whereNull('reson_code')
            ->get();
    }

    /**
     * توزيع المجموع: عدد الطلاب لكل درجة (0..100) وفي كل تقدير، مع المتوسط والانحراف المعياري.
     *
     * @param  Collection<int, int>  $totals  مجاميع الدرجات
     * @return array{histogram: array<int, int>, grades: list<array{title: string, lower: int, upper: int, count: int}>, count: int, mean: float, sd: float, failCount: int}
     */
    public function distribution(Collection $totals, ?string $plan = null): array
    {
        $profile = RegulationProfile::current();
        $plan ??= $profile->defaultPlan();
        $histogram = array_fill(0, 101, 0);

        foreach ($totals as $total) {
            $histogram[max(0, min(100, (int) $total))]++;
        }

        $grades = Grade::forPlan($plan)->sortBy('lower')->map(fn (Grade $grade): array => [
            'title' => (string) $grade->title,
            'lower' => (int) $grade->lower,
            'upper' => (int) $grade->upper,
            'count' => $totals->filter(fn ($t): bool => $t >= $grade->lower && $t <= $grade->upper)->count(),
        ])->values()->all();

        $passFrom = Grade::lowestPassing($plan)?->lower ?? $profile->passMark();
        $mean = $totals->isEmpty() ? 0.0 : (float) $totals->avg();

        return [
            'histogram' => $histogram,
            'grades' => $grades,
            'count' => $totals->count(),
            'mean' => round($mean, 2),
            'sd' => round($this->standardDeviation($totals, $mean), 2),
            'failCount' => $totals->filter(fn ($t): bool => $t < $passFrom)->count(),
        ];
    }

    /**
     * التعديلات المقترحة دون حفظ (للمعاينة والتطبيق).
     *
     * @param  Params  $params
     * @return Collection<int, Change>
     */
    public function changes(Subject $subject, int|string $batch, int|string $examType, string $method, array $params): Collection
    {
        $marks = $this->marksFor($subject, $batch, $examType);
        $max = $this->maxMark($subject, (int) $examType);

        $totals = $marks->map(fn (Mark $m): int => (int) $m->mark + (int) $m->cwork);
        $mean = $totals->isEmpty() ? 0.0 : (float) $totals->avg();
        $sd = $this->standardDeviation($totals, $mean);

        $targetTotal = match ($method) {
            MarkCurve::METHOD_RANGE => $this->rangeTarget($params),
            MarkCurve::METHOD_STDDEV => $this->stddevTarget($params, $mean, $sd),
            default => throw new DomainException('طريقة المعالجة غير معروفة.'),
        };

        return $marks->map(function (Mark $mark) use ($targetTotal, $max): ?array {
            $total = (int) $mark->mark + (int) $mark->cwork;
            $newMark = max(0, min($max, (int) $mark->mark + ($targetTotal($total) - $total)));

            return $newMark === (int) $mark->mark
                ? null
                : ['mark' => $mark, 'total' => $total, 'newMark' => $newMark, 'newTotal' => $total + ($newMark - (int) $mark->mark)];
        })->filter()->values();
    }

    /**
     * مجاميع الدرجات بعد التعديل المقترح (لمنحنى "بعد المعالجة").
     *
     * @param  Params  $params
     * @return Collection<int, int>
     */
    public function projectedTotals(Subject $subject, int|string $batch, int|string $examType, string $method, array $params): Collection
    {
        $changed = $this->changes($subject, $batch, $examType, $method, $params)
            ->keyBy(fn (array $change): int => $change['mark']->id);

        return $this->marksFor($subject, $batch, $examType)->map(
            fn (Mark $m): int => $changed->get($m->id)['newTotal'] ?? ((int) $m->mark + (int) $m->cwork),
        );
    }

    /**
     * يطبّق المعالجة ويحفظ اللقطة، ثم يعيد حساب توصية كل طالب متأثر.
     *
     * @param  Params  $params
     */
    public function apply(User $user, Subject $subject, int|string $batch, int|string $examType, string $method, array $params, ?string $note = null): MarkCurve
    {
        $this->entry->assertCanEdit($user, $subject, $batch);

        $changes = $this->changes($subject, $batch, $examType, $method, $params);

        if ($changes->isEmpty()) {
            throw new DomainException('لا توجد درجات تتأثر بهذه المعاملات.');
        }

        return DB::transaction(function () use ($user, $subject, $batch, $examType, $method, $params, $note, $changes): MarkCurve {
            $curve = MarkCurve::query()->create([
                'subject_code' => $subject->code,
                'batch_code' => $batch,
                'level_code' => $subject->level_code,
                'exam_type' => (int) $examType,
                'method' => $method,
                'params' => $params,
                'note' => $note,
                'affected_count' => $changes->count(),
                'user_id' => $user->id,
            ]);

            foreach ($changes as $change) {
                $mark = Mark::query()->lockForUpdate()->findOrFail($change['mark']->id);
                $this->assertNotLocked($mark);

                $old = $mark->only(['mark', 'grade', 'point']);

                $mark->mark = $change['newMark'];
                [$mark->grade, $mark->point] = $this->entry->gradeFor($mark, $change['mark']->student, (int) $examType, 0);
                $mark->save();

                $curve->items()->create([
                    'mark_id' => $mark->id,
                    'university_no' => $mark->university_no,
                    'old_mark' => $old['mark'],
                    'old_grade' => $old['grade'],
                    'old_point' => $old['point'],
                    'new_mark' => $mark->mark,
                    'new_grade' => $mark->grade,
                    'new_point' => $mark->point,
                ]);
            }

            $this->refreshStudents($curve, $subject);

            activity('results')->performedOn($curve)->causedBy($user)
                ->withProperties(['method' => $method, 'params' => $params, 'affected' => $changes->count()])
                ->log('معالجة تشوّه درجات مادة');

            return $curve;
        });
    }

    /**
     * يعيد كل درجة إلى ما كانت عليه قبل الإجراء. يُرفض إن عُدّلت أي درجة منها بعده
     * (حتى لا تضيع تعديلات لاحقة)، أو إن قُفلت بالاعتماد.
     */
    public function revert(User $user, MarkCurve $curve): MarkCurve
    {
        if ($curve->isReverted()) {
            throw new DomainException('تم التراجع عن هذا الإجراء مسبقاً.');
        }

        $subject = Subject::query()->findOrFail($curve->subject_code);
        $this->entry->assertCanEdit($user, $subject, $curve->batch_code);

        return DB::transaction(function () use ($user, $curve, $subject): MarkCurve {
            $items = $curve->items()->get();
            $marks = Mark::query()->lockForUpdate()->whereIn('id', $items->pluck('mark_id'))->get()->keyBy('id');

            foreach ($items as $item) {
                $mark = $marks->get($item->mark_id);

                if (! $mark || (int) $mark->mark !== (int) $item->new_mark) {
                    throw new DomainException("لا يمكن التراجع: عُدّلت درجة الطالب {$item->university_no} بعد المعالجة.");
                }

                $this->assertNotLocked($mark);
            }

            foreach ($items as $item) {
                $marks->get($item->mark_id)->forceFill([
                    'mark' => $item->old_mark,
                    'grade' => $item->old_grade,
                    'point' => $item->old_point,
                ])->save();
            }

            $this->refreshStudents($curve, $subject);

            $curve->forceFill(['reverted_at' => now(), 'reverted_by' => $user->id])->save();

            activity('results')->performedOn($curve)->causedBy($user)->log('التراجع عن معالجة تشوّه');

            return $curve;
        });
    }

    /**
     * @param  Params  $params
     * @return callable(int): int
     */
    private function rangeTarget(array $params): callable
    {
        $from = (float) ($params['from'] ?? 0);
        $to = (float) ($params['to'] ?? 100);
        $delta = (int) round((float) ($params['delta'] ?? 0));

        if ($from > $to) {
            throw new DomainException('بداية النطاق أكبر من نهايته.');
        }

        if ($delta === 0) {
            throw new DomainException('حدد درجة الإضافة أو النقص.');
        }

        return fn (int $total): int => $total >= $from && $total <= $to ? $total + $delta : $total;
    }

    /**
     * يعيد تشكيل التوزيع إلى متوسط وانحراف معياري مستهدفين: z = (x - mean) / sd.
     *
     * @param  Params  $params
     * @return callable(int): int
     */
    private function stddevTarget(array $params, float $mean, float $sd): callable
    {
        $targetMean = (float) ($params['targetMean'] ?? $mean);
        $targetSd = (float) ($params['targetSd'] ?? $sd);

        if ($sd <= 0.0) {
            throw new DomainException('لا يمكن المعالجة بالانحراف المعياري: الدرجات متطابقة أو غير كافية.');
        }

        if ($targetSd <= 0.0) {
            throw new DomainException('الانحراف المعياري المستهدف يجب أن يكون أكبر من صفر.');
        }

        return fn (int $total): int => (int) round($targetMean + (($total - $mean) / $sd) * $targetSd);
    }

    private function maxMark(Subject $subject, int $examType): int
    {
        $examMax = (int) ($subject->exam ?: 100);

        return $examType === 2 ? min($examMax, RegulationProfile::current()->supplementaryMaxMark()) : $examMax;
    }

    private function assertNotLocked(Mark $mark): void
    {
        if ($mark->is_locked) {
            throw new DomainException('بعض الدرجات معتمدة ومقفلة نهائياً، ولا تمكن معالجتها.');
        }
    }

    private function refreshStudents(MarkCurve $curve, Subject $subject): void
    {
        $students = Mark::query()->with('student')->whereIn('id', $curve->items()->pluck('mark_id'))->get()
            ->pluck('student')->filter()->unique('university_no');

        foreach ($students as $student) {
            $this->regulation->refresh(
                $student,
                $subject->faculty_code,
                $subject->level_code,
                $curve->batch_code,
                $subject->semester_id,
                $curve->exam_type,
            );
        }
    }

    /**
     * @param  Collection<int, int>  $totals
     */
    private function standardDeviation(Collection $totals, float $mean): float
    {
        if ($totals->count() < 2) {
            return 0.0;
        }

        return sqrt($totals->sum(fn ($t): float => ($t - $mean) ** 2) / $totals->count());
    }
}

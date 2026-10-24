<?php

namespace App\Services\Res;

use App\Models\Reg\Faculty;
use App\Models\Res\Finalization;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\RecordMark;
use App\Models\Res\RecordRecom;
use App\Models\Res\SubjectApprovalStatus;
use App\Models\Student;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use App\Reg\ShowAbsentCode;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

/**
 * اعتماد نتائج مجموعة (كلية + قسم + مستوى + دفعة) وتحويلها إلى سجل أكاديمي ثابت تقرأ منه الشهادات.
 *
 * الاعتماد لشخص واحد لكل كلية برمز شخصي فريد. عند الاعتماد: لقطة موقّعة + قفل الدرجات والتوصيات.
 * تعديل المدير الأعلى لنتيجة معتمدة يُسجَّل في سجل التدقيق وتُنشأ نسخة جديدة مرقّمة وتبقى القديمة.
 *
 * @phpstan-type Cohort array{faculty: int|string, department: int|string, level: int|string, batch: int|string}
 */
class FinalizationService
{
    public const MAX_ATTEMPTS = 5;

    public const LOCKOUT_SECONDS = 900;

    public const MIN_CODE_LENGTH = 6;

    /** رمز اعتماد المعتمد يُخزَّن HMAC فقط، ويُمنع تكراره بين شخصين بفهرس فريد. */
    public static function codeHash(string $code): string
    {
        return hash_hmac('sha256', trim($code), (string) config('app.key'));
    }

    // ───────────── المعتمد والرمز ─────────────

    public function approverFor(int|string $faculty): ?User
    {
        $id = Faculty::query()->where('code', $faculty)->value('results_approver_id');

        return $id ? User::query()->find($id) : null;
    }

    public function canApprove(User $user, int|string $faculty): bool
    {
        return filled($user->approval_code_hash)
            && (int) Faculty::query()->where('code', $faculty)->value('results_approver_id') === (int) $user->id;
    }

    public function setApprovalCode(User $user, string $code): void
    {
        if (mb_strlen(trim($code)) < self::MIN_CODE_LENGTH) {
            throw new DomainException('رمز الاعتماد يجب ألا يقل عن '.self::MIN_CODE_LENGTH.' خانات.');
        }

        $hash = self::codeHash($code);

        if (User::query()->where('approval_code_hash', $hash)->whereKeyNot($user->id)->exists()) {
            throw new DomainException('رمز الاعتماد مستخدم من شخص آخر، اختر رمزاً فريداً.');
        }

        $user->forceFill(['approval_code_hash' => $hash])->save();
    }

    private function verifyCode(User $user, string $code): void
    {
        $key = "results-approval:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            throw new DomainException("تم تجاوز عدد المحاولات، أعد المحاولة بعد {$minutes} دقيقة.");
        }

        if (blank($user->approval_code_hash) || ! hash_equals((string) $user->approval_code_hash, self::codeHash($code))) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);

            throw new DomainException('رمز الاعتماد غير صحيح.');
        }

        RateLimiter::clear($key);
    }

    // ───────────── الاعتماد ─────────────

    /**
     * @param  Cohort  $cohort
     * @return list<string> أسباب منع الاعتماد (فارغة = يمكن الاعتماد)
     */
    public function blockers(array $cohort): array
    {
        $blockers = [];

        if ($this->activeFor($cohort)) {
            return ['تم اعتماد هذه المجموعة مسبقاً.'];
        }

        $roster = $this->roster($cohort);

        if ($roster->isEmpty()) {
            return ['لا توجد نتائج لهذه المجموعة.'];
        }

        $marks = $this->cohortMarks($roster, $cohort);

        if ($marks->isEmpty()) {
            return ['لا توجد نتائج لهذه المجموعة.'];
        }

        $subjects = $marks->pluck('subject_code')->unique();

        $stages = SubjectApprovalStatus::query()
            ->whereIn('subject_code', $subjects)
            ->where('batch_code', $cohort['batch'])
            ->get()
            ->keyBy('subject_code');

        $pending = $subjects->filter(fn ($code) => $stages->get($code)?->current_stage !== MarkApprovalStage::Approved)->count();

        if ($pending > 0) {
            $blockers[] = "{$pending} مادة لم تصل مرحلة (معتمد نهائياً).";
        }

        $recoms = Recom::query()
            ->whereIn('university_no', $roster)
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch'])
            ->get();

        $incomplete = $recoms->where('recom_code', 9999)->pluck('university_no')->unique()->count();

        if ($incomplete > 0) {
            $blockers[] = "{$incomplete} طالب بدرجات ناقصة.";
        }

        $remedial = array_map(intval(...), ShowAbsentCode::getRemedialValues());
        $needSecond = $recoms->filter(fn (Recom $r) => $r->examType->value === 1 && in_array((int) $r->recom_code, [...$remedial, 3001, 3004], true))->pluck('university_no')->unique();
        $hadSecond = $recoms->filter(fn (Recom $r) => $r->examType->value === 2)->pluck('university_no')->unique();
        $missingSecond = $needSecond->diff($hadSecond)->count();

        if ($missingSecond > 0) {
            $blockers[] = "{$missingSecond} طالب لم تُدخل نتيجة ملحقه.";
        }

        return $blockers;
    }

    /**
     * @param  Cohort  $cohort
     */
    public function finalize(User $user, array $cohort, string $code, ?string $ip = null): Finalization
    {
        if (! $this->canApprove($user, $cohort['faculty'])) {
            throw new DomainException('لست معتمد نتائج هذه الكلية.');
        }

        $this->verifyCode($user, $code);

        $blockers = $this->blockers($cohort);

        if ($blockers !== []) {
            throw new DomainException('لا يمكن الاعتماد: '.implode(' ', $blockers));
        }

        return DB::transaction(function () use ($user, $cohort, $ip): Finalization {
            $roster = $this->roster($cohort);

            $finalization = $this->snapshot($user, $cohort, $roster, 1, Finalization::KIND_APPROVAL, null, $ip);

            $this->lock($cohort, $roster);

            return $finalization;
        });
    }

    // ───────────── تعديل المدير الأعلى ─────────────

    /**
     * يُستدعى بعد أن يعدّل المدير الأعلى نتيجة طالب معتمدة: يجدّد لقطة الطالب في نسخة جديدة مرقّمة.
     *
     * @param  array<string, mixed>  $before  قيم الصف قبل التعديل (للتدقيق)
     */
    public function recordAmendment(User $actor, Mark $mark, array $before, ?string $ip = null): ?Finalization
    {
        $active = Finalization::query()->active()
            ->where('level_code', $mark->level_code)
            ->where('batch_code', $mark->batch_code)
            ->whereHas('marks', fn ($q) => $q->where('university_no', $mark->university_no))
            ->first();

        activity('results')
            ->performedOn($mark)
            ->causedBy($actor)
            ->withProperties([
                'before' => $before,
                'after' => $mark->only(['mark', 'cwork', 'grade', 'point', 'reson_code']),
                'finalization_id' => $active?->id,
            ])
            ->log('تعديل نتيجة معتمدة');

        if (! $active) {
            return null;
        }

        $cohort = [
            'faculty' => $active->faculty_code,
            'department' => $active->department_code,
            'level' => $active->level_code,
            'batch' => $active->batch_code,
        ];

        return DB::transaction(function () use ($actor, $cohort, $active, $mark, $ip): Finalization {
            $reason = "تعديل المدير الأعلى لنتيجة الطالب {$mark->university_no} في المادة {$mark->subject_code}";

            $next = $this->snapshot(
                $actor, $cohort, $this->roster($cohort), $active->version + 1, Finalization::KIND_AMENDMENT, $reason, $ip,
                carryFrom: $active, replaceStudent: $mark->university_no,
            );

            Mark::query()->where('university_no', $mark->university_no)
                ->where('level_code', $mark->level_code)->where('batch_code', $mark->batch_code)
                ->update(['is_locked' => true]);
            Recom::query()->where('university_no', $mark->university_no)
                ->where('level_code', $mark->level_code)->where('batch_code', $mark->batch_code)
                ->update(['is_locked' => true]);

            return $next;
        });
    }

    /**
     * هل لقطة هذه النسخة مطابقة لبصمتها وقت الاعتماد؟ (كشف أي تلاعب مباشر في قاعدة البيانات)
     */
    public function isIntact(Finalization $finalization): bool
    {
        $marks = $finalization->marks()->get()->map(fn (RecordMark $r): array => $this->carryRow($r))->all();
        $recoms = $finalization->recoms()->get()->map(fn (RecordRecom $r): array => $this->carryRow($r))->all();

        return hash_equals($finalization->digest, $this->digest($marks, $recoms));
    }

    // ───────────── الاستعلام ─────────────

    /**
     * @param  Cohort  $cohort
     */
    public function activeFor(array $cohort): ?Finalization
    {
        return Finalization::query()->active()->forCohort($cohort)->first();
    }

    // ───────────── داخلي ─────────────

    /**
     * طلاب المجموعة: من لهم توصية أو درجة في المستوى والدفعة، أو يتبعون القسم حالياً.
     *
     * @param  Cohort  $cohort
     * @return Collection<int, int|string>
     */
    private function roster(array $cohort): Collection
    {
        $recoms = Recom::query()
            ->where('faculty_code', $cohort['faculty'])
            ->where('department_code', $cohort['department'])
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch'])
            ->pluck('university_no');

        $marks = Mark::query()
            ->where('department_code', $cohort['department'])
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch'])
            ->pluck('university_no');

        $current = Student::query()
            ->where('faculty_code', $cohort['faculty'])
            ->where('department_code', $cohort['department'])
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch'])
            ->pluck('university_no');

        return $recoms->merge($marks)->merge($current)->unique()->values();
    }

    /**
     * آخر درجة لكل مادة لكل طالب (الملحق يغلب الأساسي لأنه الأحدث).
     *
     * @param  Collection<int, int|string>  $roster
     * @param  Cohort  $cohort
     * @return Collection<int, Mark>
     */
    private function cohortMarks(Collection $roster, array $cohort): Collection
    {
        return Mark::query()
            ->with(['subject:code,name_ar,name_en,crch', 'reason:code,name_ar'])
            ->whereIn('university_no', $roster)
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch'])
            ->get()
            ->filter(fn (Mark $m) => $m->mark !== null || $m->reson_code !== null || filled($m->grade))
            ->sortByDesc('id')
            ->unique(fn (Mark $m) => $m->university_no.'|'.$m->subject_code)
            ->values();
    }

    /**
     * @param  Cohort  $cohort
     * @param  Collection<int, int|string>  $roster
     */
    private function snapshot(
        User $user,
        array $cohort,
        Collection $roster,
        int $version,
        string $kind,
        ?string $reason,
        ?string $ip,
        ?Finalization $carryFrom = null,
        int|string|null $replaceStudent = null,
    ): Finalization {
        $fresh = $carryFrom && $replaceStudent !== null ? collect([$replaceStudent]) : $roster;

        $marks = $this->cohortMarks($fresh, $cohort);
        $recoms = Recom::query()->with('recomName:code,name_ar')
            ->whereIn('university_no', $fresh)
            ->where('level_code', $cohort['level'])
            ->where('batch_code', $cohort['batch'])
            ->get();

        $markRows = $marks->map(fn (Mark $m): array => $this->markRow($m))->all();
        $recomRows = $recoms->map(fn (Recom $r): array => $this->recomRow($r))->all();

        if ($carryFrom && $replaceStudent !== null) {
            $keep = fn (array $row): bool => (string) $row['university_no'] !== (string) $replaceStudent;

            $markRows = [
                ...$carryFrom->marks()->get()->map(fn (RecordMark $r): array => $this->carryRow($r))->filter($keep)->all(),
                ...array_filter($markRows, fn (array $row): bool => ! $keep($row)),
            ];
            $recomRows = [
                ...$carryFrom->recoms()->get()->map(fn (RecordRecom $r): array => $this->carryRow($r))->filter($keep)->all(),
                ...array_filter($recomRows, fn (array $row): bool => ! $keep($row)),
            ];
        }

        $digest = $this->digest($markRows, $recomRows);
        $approvedAt = now();

        if ($carryFrom) {
            $carryFrom->forceFill(['is_active' => false])->save();
        }

        $finalization = Finalization::query()->create([
            'faculty_code' => $cohort['faculty'],
            'department_code' => $cohort['department'],
            'level_code' => $cohort['level'],
            'batch_code' => $cohort['batch'],
            'version' => $version,
            'kind' => $kind,
            'is_active' => true,
            'user_id' => $user->id,
            'approved_at' => $approvedAt,
            'ip' => $ip,
            'students_count' => collect($markRows)->pluck('university_no')->unique()->count(),
            'marks_count' => count($markRows),
            'reason' => $reason,
            'digest' => $digest,
            'signature' => $this->sign($digest, $user, $approvedAt->toIso8601String()),
        ]);

        $now = now();
        $stamp = fn (array $row): array => $row + ['finalization_id' => $finalization->id, 'created_at' => $now];

        foreach (array_chunk($markRows, 500) as $chunk) {
            RecordMark::query()->insert(array_map($stamp, $chunk));
        }

        foreach (array_chunk($recomRows, 500) as $chunk) {
            RecordRecom::query()->insert(array_map($stamp, $chunk));
        }

        return $finalization;
    }

    /**
     * @param  Cohort  $cohort
     * @param  Collection<int, int|string>  $roster
     */
    private function lock(array $cohort, Collection $roster): void
    {
        Mark::query()->whereIn('university_no', $roster)
            ->where('level_code', $cohort['level'])->where('batch_code', $cohort['batch'])
            ->update(['is_locked' => true]);

        Recom::query()->whereIn('university_no', $roster)
            ->where('level_code', $cohort['level'])->where('batch_code', $cohort['batch'])
            ->update(['is_locked' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function markRow(Mark $mark): array
    {
        return [
            'university_no' => $mark->university_no,
            'subject_code' => $mark->subject_code,
            'subject_name_ar' => $mark->subject?->name_ar,
            'subject_name_en' => $mark->subject?->name_en,
            'crch' => $mark->subject?->crch,
            'faculty_code' => $mark->faculty_code,
            'department_code' => $mark->department_code,
            'special_code' => $mark->special_code,
            'level_code' => $mark->level_code,
            'batch_code' => $mark->batch_code,
            'semester' => (int) $mark->semester,
            'source_exam_type' => (int) $mark->examType,
            'mark' => $mark->mark,
            'cwork' => $mark->cwork,
            'grade' => $mark->grade,
            'point' => $mark->point,
            'reson_code' => $mark->reson_code,
            'reson_name' => $mark->reason?->name_ar,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recomRow(Recom $recom): array
    {
        return [
            'university_no' => $recom->university_no,
            'faculty_code' => $recom->faculty_code,
            'department_code' => $recom->department_code,
            'special_code' => $recom->special_code,
            'level_code' => $recom->level_code,
            'batch_code' => $recom->batch_code,
            'semester' => $recom->semester->value,
            'examType' => $recom->examType->value,
            'countSubject' => $recom->countSubject,
            'sumMarks' => $recom->sumMarks,
            's_pass' => $recom->s_pass,
            's_fail' => $recom->s_fail,
            'crch_fail' => $recom->crch_fail,
            's_abs' => $recom->s_abs,
            'crch' => $recom->crch,
            'points' => $recom->points,
            'gpa' => $recom->gpa,
            'cgpa' => $recom->cgpa,
            'recom_code' => $recom->recom_code,
            'recom_name' => $recom->recomName?->name_ar,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function carryRow(RecordMark|RecordRecom $row): array
    {
        return Collection::make($row->getAttributes())->except(['id', 'finalization_id', 'created_at'])->all();
    }

    /**
     * @param  list<array<string, mixed>>  $marks
     * @param  list<array<string, mixed>>  $recoms
     */
    private function digest(array $marks, array $recoms): string
    {
        $normalize = function (array $rows): array {
            $rows = array_map(function (array $row): array {
                ksort($row);

                return array_map(fn ($value) => match (true) {
                    is_null($value) => null,
                    is_numeric($value) => (string) (float) $value,
                    default => (string) $value,
                }, $row);
            }, $rows);

            usort($rows, fn (array $a, array $b): int => strcmp(json_encode($a), json_encode($b)));

            return $rows;
        };

        return hash('sha256', json_encode([$normalize($marks), $normalize($recoms)], JSON_UNESCAPED_UNICODE));
    }

    private function sign(string $digest, User $user, string $approvedAt): string
    {
        return hash_hmac('sha256', "{$digest}|{$user->id}|{$approvedAt}", (string) config('app.key').$user->approval_code_hash);
    }
}

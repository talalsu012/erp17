<?php

namespace App\Services\Res;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\Special;
use App\Models\Res\LtRecom;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Student;
use App\Models\User;
use App\Reg\RecomCode;
use Illuminate\Support\Collection;

/**
 * النتيجة النهائية السنوية في النظام الفصلي-السنوي: نتيجة كل فصل ثم نتيجة السنة من مجموع الفصلين.
 *
 * لكل طالب: إحصائيات كل فصل (مستوى من مستويات السنة)، وإحصائيات السنة مجمّعة (المعدل مرجّح بالساعات المعتمدة)
 * على آخر درجة لكل مقرر (فالملحق يغلب الأساسي)، والقرار النهائي: توصية الملاحق (نهاية السنة) إن وُجدت،
 * وإلا نجاح إن لم يبق رسوب أو غياب، وإلا أول توصية فصل لم يُحسم أمرها (تنتظر الملاحق).
 */
class YearResultService
{
    /** توصيات تعني أن الطالب اجتاز السنة */
    private const PASSED = [RecomCode::Pass->value, RecomCode::RemoveFail->value];

    /** توصيات تعني أن النتيجة تنتظر ملاحق أو بدائل */
    private const PENDING = [RecomCode::Supplementary->value, RecomCode::Substitute->value, RecomCode::SubstituteAndSupplementary->value];

    public function __construct(
        private readonly RegulationService $regulation,
        private readonly AcademicYearService $years,
        private readonly ResultSheetService $sheets,
    ) {}

    /**
     * @param  array{faculty: mixed, department?: mixed, special?: mixed, level: mixed, batch: mixed}  $p
     * @return array<string, mixed>
     */
    public function build(array $p, ?User $user = null): array
    {
        $user ??= auth()->user();
        $profile = RegulationProfile::forFaculty($p['faculty']);
        $batch = (string) $p['batch'];
        $levels = $this->years->levelsOfYear($p['level']);
        sort($levels);

        $marks = ($user ? $user->constrainToAcademicScope(Mark::query()) : Mark::query())
            ->with(['subject:code,crch'])
            ->where('faculty_code', $p['faculty'])
            ->where('batch_code', $batch)
            ->whereIn('level_code', $levels)
            ->when($p['department'] ?? null, fn ($q, $v) => $q->where('department_code', $v))
            ->when($p['special'] ?? null, fn ($q, $v) => $q->where('special_code', $v))
            ->get()
            ->filter(fn (Mark $m): bool => $m->mark !== null || $m->reson_code !== null)
            ->groupBy('university_no');

        $students = Student::query()->whereIn('university_no', $marks->keys())->orderBy('name_ar')->get();

        $recoms = Recom::query()
            ->with('recomName')
            ->whereIn('university_no', $marks->keys())
            ->whereIn('level_code', $levels)
            ->where('batch_code', $batch)
            ->get()
            ->groupBy('university_no');

        $rows = $students->map(fn (Student $student): array => $this->row(
            $student,
            $marks->get($student->university_no, collect()),
            $recoms->get($student->university_no, collect()),
            $levels,
            $profile,
        ));

        $facultyInfo = Faculty::where('code', $p['faculty'])->first();
        $departmentInfo = ($p['department'] ?? null) ? Department::where('code', $p['department'])->first() : null;

        return [
            'facultyInfo' => $facultyInfo,
            'departmentInfo' => $departmentInfo,
            'specialInfo' => ($p['special'] ?? null) ? Special::where('code', $p['special'])->first() : null,
            'levels' => Level::query()->whereIn('code', $levels)->orderBy('code')->get(),
            'academicYear' => is_numeric($batch) ? $batch.'/'.($batch + 1) : $batch,
            'rows' => $rows,
            'summary' => $this->summary($rows),
            'logos' => $this->sheets->logos($p['faculty']),
            'headerTitle' => $this->sheets->metaText($facultyInfo, 'header_title'),
            'footerTitle' => $this->sheets->metaText($facultyInfo, 'footer_title'),
            'signatories' => $this->sheets->signatories($facultyInfo, $departmentInfo),
        ];
    }

    /**
     * @param  Collection<int, Mark>  $marks
     * @param  Collection<int, Recom>  $recoms
     * @param  list<int>  $levels
     * @return array<string, mixed>
     */
    private function row(Student $student, Collection $marks, Collection $recoms, array $levels, RegulationProfile $profile): array
    {
        $passMark = RegulationService::passMarkFor($student, $profile);
        $latest = $marks->sortByDesc('id')->unique('subject_code');

        $semesters = [];

        foreach ($levels as $level) {
            $levelMarks = $latest->filter(fn (Mark $m): bool => (int) $m->level_code === $level);
            $stats = $this->regulation->statistics($levelMarks, $levelMarks->count(), $passMark, $profile);
            $firstSitting = $recoms->filter(fn (Recom $r): bool => $r->examType->value === 1 && (int) $r->level_code === $level)->sortByDesc('id')->first();

            $semesters[$level] = $stats + ['recom_code' => $firstSitting?->recom_code, 'recom_name' => $firstSitting?->recomName?->name_ar];
        }

        $year = $this->regulation->statistics($latest, $latest->count(), $passMark, $profile);

        $supplementary = $recoms->filter(fn (Recom $r): bool => $r->examType->value === 2)->sortByDesc('id')->first();
        $unsettledSemester = $recoms
            ->filter(fn (Recom $r): bool => $r->examType->value === 1 && ! in_array((string) $r->recom_code, self::PASSED, true))
            ->sortBy('level_code')
            ->first();

        $code = match (true) {
            $latest->isEmpty() => RecomCode::Incomplete->value,
            $supplementary !== null => (string) $supplementary->recom_code,
            $year['s_fail'] === 0 && $year['s_abs'] === 0 => RecomCode::Pass->value,
            $unsettledSemester !== null => (string) $unsettledSemester->recom_code,
            default => RecomCode::Supplementary->value,
        };

        return [
            'student' => $student,
            'semesters' => $semesters,
            'year' => $year,
            'recom_code' => $code,
            'recom_name' => LtRecom::query()->find($code)?->name_ar ?? $code,
            'passed' => in_array($code, self::PASSED, true),
            'pending' => in_array($code, self::PENDING, true),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{total: int, passed: int, pending: int, other: int, byDecision: list<array{code: string, name: string, count: int}>, highestGpa: float|null}
     */
    private function summary(Collection $rows): array
    {
        return [
            'total' => $rows->count(),
            'passed' => $rows->where('passed', true)->count(),
            'pending' => $rows->where('pending', true)->count(),
            'other' => $rows->where('passed', false)->where('pending', false)->count(),
            'byDecision' => $rows->groupBy('recom_code')
                ->map(fn (Collection $group, string|int $code): array => [
                    'code' => (string) $code,
                    'name' => (string) $group->first()['recom_name'],
                    'count' => $group->count(),
                ])
                ->sortByDesc('count')
                ->values()
                ->all(),
            'highestGpa' => $rows->isEmpty() ? null : (float) $rows->max(fn (array $row): float => (float) $row['year']['gpa']),
        ];
    }
}

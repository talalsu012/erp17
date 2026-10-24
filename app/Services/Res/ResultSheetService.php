<?php

namespace App\Services\Res;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\Special;
use App\Models\Res\LtRecom;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Models\User;
use App\Reg\AbsStatus;
use App\Reg\ShowAbsentCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * يجهّز بيانات كشف النتيجة (الحالية أو السابقة) لقوالب الطباعة.
 *
 * قائمة الطلاب تُبنى من سجل التوصيات والدرجات (وليس من الصف الحالي للطالب فقط)،
 * لأن الطالب المنقول إلى مستوى/عام آخر لم يعد يطابق دفعته ومستواه القديمين في جدول الطلاب.
 */
class ResultSheetService
{
    public const DEFAULT_PER_PAGE = 16;

    /** قرار التجميد (res_ltrecoms.code) وبداية رمز حالة الطالب المجمّد */
    public const FROZEN_RECOM = 1006;

    private const FROZEN_STATUS_PREFIX = '20040002';

    /** رموز التقدير التي تعني رسوباً (للتلوين والإحصاء) */
    public const FAIL_GRADES = ['ر', 'F', 'ض', 'رسوب'];

    /**
     * @param  array{faculty: mixed, department?: mixed, special?: mixed, level: mixed, batch: mixed, absent: int|string, subjects?: array, fields?: array, result_type?: string, show_history?: bool, per_page?: int}  $p
     */
    public function build(array $p, ?User $user = null): array
    {
        $user ??= auth()->user();
        $batch = (string) $p['batch'];
        $level = $p['level'];
        $faculty = $p['faculty'];
        $department = $p['department'] ?? null;
        $special = $p['special'] ?? null;
        $subjects = array_values($p['subjects'] ?? []);
        $examType = (int) $p['absent'];
        $isPrevious = ($p['result_type'] ?? 'current') === 'previous';
        $isSecondRound = $examType === 2;

        $roster = $this->rosterIds($p, $isPrevious, $isSecondRound, $user);

        $students = Student::query()
            ->whereIn('university_no', $roster)
            ->with($this->eagerLoads($subjects, $batch, $level, $department, $special, $examType, $isSecondRound))
            ->orderBy('name_ar')
            ->get();

        $this->overlayFrozen($students, $subjects, LtRecom::find(self::FROZEN_RECOM));

        $students->each(function (Student $student) use ($isSecondRound) {
            if ($isSecondRound) {
                $student->setRelation('marks', $student->marks->groupBy('subject_code')
                    ->map(fn ($m) => $m->firstWhere('examType', 2) ?? $m->firstWhere('examType', 1))
                    ->values());
            }

            $student->setRelation('sheetRecom', $student->recom->first());
        });

        $facultyInfo = Faculty::where('code', $faculty)->first();
        $departmentInfo = $department ? Department::where('code', $department)->first() : null;

        return [
            'facultyInfo' => $facultyInfo,
            'departmentInfo' => $departmentInfo,
            'specialInfo' => $special ? Special::where('code', $special)->when($department, fn ($q, $v) => $q->where('department_code', $v))->first() : null,
            'headerTitle' => $this->metaText($facultyInfo, 'header_title'),
            'footerTitle' => $this->metaText($facultyInfo, 'footer_title'),
            'levelInfo' => Level::where('code', $level)->first(),
            'absentName' => AbsStatus::tryFrom($examType)?->getLabel() ?? 'غير محدد',
            'academicYear' => is_numeric($batch) ? $batch.'/'.($batch + 1) : $batch,
            'isPrevious' => $isPrevious,
            'students' => $students,
            'subjects' => $subjects,
            'subjectNames' => Subject::whereIn('code', $subjects)->get()->keyBy('code'),
            'fields' => $p['fields'] ?? [],
            'perPage' => $this->perPage($p['per_page'] ?? null),
            'showHistory' => (bool) ($p['show_history'] ?? false),
            'history' => ($p['show_history'] ?? false) ? $this->history($students, $batch) : collect(),
            'counts' => $this->priorCounts($students, $batch),
            'stats' => $this->stats($students, $roster->count()),
            'subjectStats' => $this->subjectStats($students),
            'highestGpa' => $this->highestGpa($students),
            'recomStats' => $this->recomStats($students),
            'subjectPassStats' => $this->subjectPassStats($students, $subjects ?: $students->flatMap(fn (Student $s) => $s->marks->pluck('subject_code'))->unique()->values()->all()),
            'logos' => $this->logos($faculty),
            'signatories' => $this->signatories($facultyInfo, $departmentInfo),
        ];
    }

    public function perPage(mixed $value): int
    {
        return max(4, min(40, (int) ($value ?: self::DEFAULT_PER_PAGE)));
    }

    /** @return Collection<int, string|int> */
    private function rosterIds(array $p, bool $isPrevious, bool $isSecondRound, ?User $user = null): Collection
    {
        $faculty = $p['faculty'];
        $batch = $p['batch'];
        $level = $p['level'];
        $department = $p['department'] ?? null;
        $special = $p['special'] ?? null;
        $scoped = fn (Builder $query): Builder => $user ? $user->constrainToAcademicScope($query) : $query;

        $recoms = $scoped(Recom::query())
            ->where('faculty_code', $faculty)
            ->where('level_code', $level)
            ->where('batch_code', $batch)
            ->where('examType', 1)
            ->when($department, fn ($q, $v) => $q->where('department_code', $v))
            ->when($special, fn ($q, $v) => $q->where('special_code', $v))
            ->when($isSecondRound, fn ($q) => $q->whereIn('recom_code', ShowAbsentCode::getRemedialValues()))
            ->pluck('university_no');

        if ($isSecondRound) {
            return $recoms->unique()->values();
        }

        $marks = $scoped(Mark::query())
            ->where('level_code', $level)
            ->where('batch_code', $batch)
            ->where('examType', 1)
            ->when($p['subjects'] ?? [], fn ($q, $v) => $q->whereIn('subject_code', $v))
            ->when($department, fn ($q, $v) => $q->where('department_code', $v))
            ->when($special, fn ($q, $v) => $q->where('special_code', $v))
            ->pluck('university_no');

        $ids = $recoms->merge($marks);

        if (! $isPrevious) {
            $ids = $ids->merge($scoped(Student::query())
                ->where('faculty_code', $faculty)
                ->where('level_code', $level)
                ->where('batch_code', $batch)
                ->when($department, fn ($q, $v) => $q->where('department_code', $v))
                ->when($special, fn ($q, $v) => $q->where('special_code', $v))
                ->pluck('university_no'));
        }

        return $ids->unique()->values();
    }

    private function eagerLoads(array $subjects, string $batch, mixed $level, mixed $department, mixed $special, int $examType, bool $isSecondRound): array
    {
        return [
            'marks' => fn ($q) => $q->where('batch_code', $batch)
                ->when($subjects, fn ($q) => $q->whereIn('subject_code', $subjects), fn ($q) => $q->where('level_code', $level))
                ->whereIn('examType', $isSecondRound ? [1, 2] : [1]),
            'marks.subject',
            'recom' => fn ($q) => $q->where('examType', $examType)
                ->where('level_code', $level)
                ->where('batch_code', $batch)
                ->when($department, fn ($q, $v) => $q->where('department_code', $v))
                ->when($special, fn ($q, $v) => $q->where('special_code', $v)),
            'recom.recomName',
        ];
    }

    /**
     * آخر نتيجة سابقة لكل طالب (آخر دفعة أقدم من الدفعة المطبوعة).
     *
     * @return Collection<string|int, array{batch: mixed, level: ?string, gpa: mixed, recom: ?string}>
     */
    private function history(Collection $students, string $batch): Collection
    {
        return Recom::query()
            ->with(['level', 'recomName'])
            ->whereIn('university_no', $students->pluck('university_no'))
            ->where('batch_code', '<', $batch)
            ->get()
            ->groupBy('university_no')
            ->map(function (Collection $rows) {
                $last = $rows->sortBy([['batch_code', 'desc'], ['id', 'desc']])->first();

                return [
                    'batch' => $last->batch_code,
                    'level' => $last->level?->name_ar,
                    'gpa' => $last->gpa,
                    'recom' => $last->recomName?->name_ar ?? $last->recom_code,
                ];
            });
    }

    /** عدد مرات الامتحان من الخارج (Ex) وإعادة المستوى (R) السابقة لكل طالب، باستعلامين فقط */
    private function priorCounts(Collection $students, string $batch): array
    {
        $count = fn (array $codes) => Recom::query()
            ->whereIn('university_no', $students->pluck('university_no'))
            ->whereIn('recom_code', $codes)
            ->where('batch_code', '<>', $batch)
            ->selectRaw('university_no, COUNT(*) as n')
            ->groupBy('university_no')
            ->pluck('n', 'university_no');

        return [
            'external' => $count(ShowAbsentCode::External()),
            'repeat' => $count(ShowAbsentCode::Repeat()),
        ];
    }

    private function stats(Collection $students, int $registered): array
    {
        $failed = $students->filter(fn (Student $s) => $s->marks->contains(
            fn ($m) => in_array($m->grade, ShowAbsentCode::getMarkValues(), true)
        ))->count();

        return [
            'registered' => $registered,
            'sitting' => $students->count(),
            'passed' => $students->count() - $failed,
            'failed' => $failed,
        ];
    }

    /** أعلى معدل فصلي بين الطلاب (null إن لم توجد توصيات) */
    private function highestGpa(Collection $students): ?float
    {
        $max = $students->map(fn (Student $s) => $s->sheetRecom?->gpa)->filter(fn ($gpa) => $gpa !== null)->max();

        return $max === null ? null : (float) $max;
    }

    /**
     * إحصائية القرارات: عدد الطلاب ونسبتهم لكل قرار، الأكثر تكراراً أولاً.
     *
     * @return array<int, array{code: mixed, name: string, count: int, percent: float}>
     */
    private function recomStats(Collection $students): array
    {
        $withRecom = $students->filter(fn (Student $s) => $s->sheetRecom !== null);
        $total = $withRecom->count();

        return $withRecom
            ->groupBy(fn (Student $s) => $s->sheetRecom->recom_code)
            ->map(fn (Collection $group, $code) => [
                'code' => $code,
                'name' => (string) ($group->first()->sheetRecom->recomName?->name_ar ?? $code),
                'count' => $group->count(),
                'percent' => $total ? round($group->count() / $total * 100, 2) : 0.0,
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * إحصائية كل مادة: الجالسون والناجحون والراسبون والبديل وأعلى/أقل درجة والمعدل ونسبة النجاح.
     *
     * @param  array<int, mixed>  $subjects  أكواد المواد بترتيب الطباعة
     * @return array<int, array{code: mixed, name: string, total: int, passed: int, failed: int, alternate: int, max: float|int, min: float|int, avg: float, rate: float}>
     */
    private function subjectPassStats(Collection $students, array $subjects): array
    {
        $failing = [...self::FAIL_GRADES, ...ShowAbsentCode::getMarkValues()];
        $names = Subject::query()->whereIn('code', $subjects)->pluck('name_ar', 'code');
        $rows = [];

        foreach ($subjects as $code) {
            $marks = $students->flatMap(fn (Student $s) => $s->marks->where('subject_code', $code))
                ->filter(fn ($m) => $m->mark !== null);

            $alternate = $marks->filter(fn ($m) => str_contains((string) $m->grade, 'بديل'))->count();
            $failed = $marks->filter(fn ($m) => in_array($m->grade, $failing, true))->count();
            $passed = $marks->count() - $failed - $alternate;

            $rows[] = [
                'code' => $code,
                'name' => (string) ($names[$code] ?? $code),
                'total' => $marks->count(),
                'passed' => max($passed, 0),
                'failed' => $failed,
                'alternate' => $alternate,
                'max' => $marks->max('mark') ?? 0,
                'min' => $marks->min('mark') ?? 0,
                'avg' => $marks->count() ? round($marks->avg('mark'), 2) : 0.0,
                'rate' => $marks->count() ? round(max($passed, 0) / $marks->count() * 100, 2) : 0.0,
            ];
        }

        return $rows;
    }

    /** @return array<string|int, array<string, int>> [كود المادة => [التقدير => العدد]] */
    private function subjectStats(Collection $students): array
    {
        $stats = [];

        foreach ($students as $student) {
            foreach ($student->marks as $mark) {
                $grade = $mark->grade ?? 'غير مسجل';
                $stats[$mark->subject_code][$grade] = ($stats[$mark->subject_code][$grade] ?? 0) + 1;
            }
        }

        return $stats;
    }

    public function logos(mixed $faculty): array
    {
        $find = function (array $files) {
            foreach ($files as $file) {
                if (is_file(public_path($file))) {
                    return asset($file);
                }
            }

            return null;
        };

        return [
            'university' => $find([config('university.logo')]),
            'faculty' => $find(["logos/fac{$faculty}.jpeg", "logos/fac{$faculty}.png", "logos/fac{$faculty}.jpg"]),
        ];
    }

    /**
     * نص اختياري من بيانات الكلية (metadata): header_title فوق عنوان الكشف، وfooter_title قبل التواقيع.
     * الفارغ يرجع null فلا يُطبع شيء.
     */
    public function metaText(?Faculty $faculty, string $key): ?string
    {
        return $this->meaningful($faculty?->metadata[$key] ?? null);
    }

    /**
     * النص بعد التنظيف، أو null إن كان فارغاً أو مجرد رموز حشو مثل "--" و"..." و"—".
     */
    private function meaningful(mixed $value): ?string
    {
        $text = trim((string) $value);

        return preg_match('/^[\s\-–—_.\/*]*$/u', $text) === 1 ? null : $text;
    }

    /** @return array<int, array{name: string, title: string}> */
    private function signatories(?Faculty $faculty, ?Department $department): array
    {
        $meta = (object) ($faculty?->metadata ?? []);
        $departmentMeta = (object) ($department?->metadata ?? []);

        return $this->withNames([
            ['name' => $meta->academics_name ?? null, 'title' => 'رئيس المكتب الأكاديمي'],
            ['name' => $meta->registrar_name ?? null, 'title' => 'المسجل'],
            ['name' => $departmentMeta->department_name ?? null, 'title' => 'رئيس القسم'],
            ['name' => $meta->dean_name ?? null, 'title' => 'عميد الكلية'],
        ]);
    }

    /**
     * يحذف خانات التوقيع التي لا اسم لها، فلا يُطبع مسماها ولا يُحجز لها مكان.
     *
     * @param  array<int, array{name: ?string, title: string}>  $people
     * @return array<int, array{name: string, title: string}>
     */
    public function withNames(array $people): array
    {
        return array_values(array_filter(
            array_map(fn (array $person): array => ['name' => (string) $this->meaningful($person['name']), 'title' => $person['title']], $people),
            fn (array $person): bool => $person['name'] !== '',
        ));
    }

    private function isFrozen(Student $student): bool
    {
        return str_starts_with((string) $student->status_code, self::FROZEN_STATUS_PREFIX);
    }

    /**
     * عرض المجمّدين في الكشف (تقدير "ت" وقرار التجميد) بالذاكرة فقط، دون أي كتابة في قاعدة البيانات.
     */
    public function overlayFrozen(Collection $students, array $subjects, ?LtRecom $recomName = null): void
    {
        foreach ($students->filter(fn (Student $s) => $this->isFrozen($s)) as $student) {
            $frozenMarks = collect($subjects)->map(fn ($code) => (new Mark)->forceFill([
                'subject_code' => $code, 'mark' => 0, 'grade' => ShowAbsentCode::Freezed->value,
            ]));

            $student->setRelation('marks', $student->marks
                ->reject(fn ($m) => in_array($m->subject_code, $subjects))
                ->merge($frozenMarks)
                ->values());

            $recom = (new Recom)->forceFill([
                'recom_code' => self::FROZEN_RECOM,
                'countSubject' => 0, 'sumMarks' => 0, 's_pass' => 0, 's_fail' => 0, 'crch_fail' => 0,
                's_abs' => 0, 'crch' => 0, 'points' => 0, 'gpa' => 0, 'cgpa' => 0,
            ]);
            $recom->setRelation('recomName', $recomName);

            $student->setRelation('recom', collect([$recom]));
        }
    }

    /**
     * إجراء صريح: يثبّت في قاعدة البيانات سجلات المجمّدين (درجة 0 وتقدير "ت" وقرار 1006)
     * لمجموعة الطلاب المطابقة لفلاتر الكشف. يرجع عدد الطلاب المثبَّتين.
     *
     * @param  array{faculty: mixed, department?: mixed, special?: mixed, level: mixed, batch: mixed, subjects?: array}  $p
     */
    public function persistFrozen(array $p): int
    {
        $batch = (string) $p['batch'];
        $subjects = array_values($p['subjects'] ?? []);

        $students = Student::query()
            ->whereIn('university_no', $this->rosterIds($p + ['absent' => 1], false, false, auth()->user()))
            ->get()
            ->filter(fn (Student $s) => $this->isFrozen($s))
            ->reject(fn (Student $s) => Mark::query()
                ->where('university_no', $s->university_no)
                ->where('batch_code', $batch)
                ->where('is_locked', true)
                ->exists());

        foreach ($students as $student) {
            foreach ($subjects as $code) {
                Mark::withTrashed()->updateOrCreate(
                    [
                        'university_no' => $student->university_no,
                        'subject_code' => $code,
                        'level_code' => $student->level_code,
                        'semester' => '1',
                        'batch_code' => $batch,
                        'examType' => '1',
                    ],
                    [
                        'faculty_code' => $student->faculty_code,
                        'department_code' => $student->department_code,
                        'special_code' => $student->special_code,
                        'mark' => 0,
                        'grade' => ShowAbsentCode::Freezed->value,
                        'reson_code' => null,
                        'deleted_at' => null,
                    ]
                );
            }

            Recom::withTrashed()->updateOrCreate(
                [
                    'university_no' => $student->university_no,
                    'faculty_code' => $student->faculty_code,
                    'department_code' => $student->department_code,
                    'batch_code' => $batch,
                    'level_code' => $student->level_code,
                    'semester' => 1,
                    'examType' => 1,
                ],
                [
                    'special_code' => $student->special_code,
                    'recom_code' => self::FROZEN_RECOM,
                    'countSubject' => 0, 'sumMarks' => 0, 's_pass' => 0, 's_fail' => 0, 'crch_fail' => 0,
                    's_abs' => 0, 'crch' => 0, 'points' => 0, 'gpa' => 0, 'cgpa' => 0,
                    'deleted_at' => null,
                ]
            );
        }

        return $students->count();
    }
}

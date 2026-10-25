<?php

namespace App\Services\Analytics;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Res\LtRecom;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Res\SubjectApprovalStatus;
use App\Models\Student;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use App\Services\Res\RegulationProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * مؤشرات لوحة القرار (الطلاب، النتائج، المالية، التقديرات) ضمن نطاق المستخدم الأكاديمي.
 * كل الاستعلامات تجميعية (GROUP BY) بلا حلقات، وتُخزَّن دقيقتين لكل مستخدم ومرشحات.
 */
class DashboardAnalyticsService
{
    public const PASS_RECOM = 1005;

    public const INCOMPLETE_RECOM = 9999;

    /** قرارات تعني الإعادة أو الفصل (طالب في خطر) */
    public const RISK_RECOMS = [2001, 2002, 4004];

    /** @deprecated القيمة الفعلية: regulations.pass_mark */
    public const PASS_MARK = 50;

    /** ترتيب عرض التقديرات (الخطة الافتراضية) ثم أي تقدير آخر */
    private const GRADE_ORDER = ['A', '+B', 'B', '+C', 'C', 'D', 'F'];

    /**
     * @param  array{batch?: mixed, faculty?: mixed, department?: mixed, level?: mixed, exam?: mixed}  $filters
     * @return array<string, mixed>
     */
    public function build(User $user, array $filters): array
    {
        $f = $this->normalize($user, $filters);
        $key = 'analytics:'.$user->getKey().':'.md5(json_encode($f));

        return Cache::remember($key, 120, fn (): array => $this->compute($user, $f) + ['filters' => $f]);
    }

    /**
     * كل ما تحتاجه صفحة اللوحة (مؤشرات + خيارات المرشحات + صلاحيات العرض).
     * أسماء الطلاب والمالية لمن له صلاحية عرض الطلاب فقط.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function pageData(User $user, array $params): array
    {
        $canViewStudents = $user->isSuperAdmin() || $user->can('ViewAny:Student');

        return $this->build($user, $params) + [
            'options' => $this->options($user, filled($params['faculty'] ?? null) ? (int) $params['faculty'] : null),
            'showNames' => $canViewStudents,
            'showFinance' => $canViewStudents,
            'userName' => $user->name,
        ];
    }

    /**
     * خيارات المرشحات المتاحة للمستخدم.
     *
     * @return array{batches: array<int|string, string>, faculties: array<int|string, string>, departments: array<int|string, string>, levels: array<int|string, string>}
     */
    public function options(User $user, ?int $faculty): array
    {
        return [
            'batches' => LtColumn::query()->where('grouped', 2003)->orderByDesc('code')->pluck('name_ar', 'code')->all(),
            'faculties' => Faculty::forAuthUser()->orderBy('name_ar')->pluck('name_ar', 'code')->all(),
            'departments' => Department::forAuthUser()
                ->when($faculty, fn (Builder $q, $v) => $q->where('faculty_code', $v))
                ->orderBy('name_ar')->pluck('name_ar', 'code')->all(),
            'levels' => Level::forAuthUser()->orderBy('code')->pluck('name_ar', 'code')->all(),
        ];
    }

    /**
     * @return array{batch: ?int, faculty: ?int, department: ?int, level: ?int, exam: int}
     */
    public function normalize(User $user, array $filters): array
    {
        $int = fn (string $key): ?int => filled($filters[$key] ?? null) ? (int) $filters[$key] : null;

        $explicit = $int('batch');

        $resultBatch = $explicit ?? $user->constrainToAcademicScope(Recom::query())
            ->where('batch_code', '<=', now()->year)->max('batch_code')
            ?? $user->constrainToAcademicScope(Recom::query())->max('batch_code');

        return [
            // المختار صراحةً: يقيّد الطلاب والنتائج معاً. فارغ = كل الطلاب، وآخر عام لديه نتائج (حتى السنة الحالية) للنتائج.
            'batch' => $explicit,
            'result_batch' => $resultBatch !== null ? (int) $resultBatch : null,
            'faculty' => $int('faculty'),
            'department' => $int('department'),
            'level' => $int('level'),
            'exam' => (int) ($filters['exam'] ?? 1) === 2 ? 2 : 1,
        ];
    }

    /**
     * @param  array{batch: ?int, faculty: ?int, department: ?int, level: ?int, exam: int}  $f
     * @return array<string, mixed>
     */
    private function compute(User $user, array $f): array
    {
        $recoms = fn (bool $withBatch = true): Builder => $this->recoms($user, $f, $withBatch);

        $completed = fn (Builder $q): Builder => $q->whereNotNull('recom_code')->where('recom_code', '!=', self::INCOMPLETE_RECOM);

        $aggregate = fn (string $column): Collection => $completed($recoms())
            ->selectRaw("{$column} AS k, COUNT(*) AS n, AVG(gpa) AS gpa, SUM(CASE WHEN recom_code = ".self::PASS_RECOM.' THEN 1 ELSE 0 END) AS passed')
            ->groupBy($column)
            ->get();

        $overall = $completed($recoms())
            ->selectRaw('COUNT(*) AS n, AVG(gpa) AS gpa, MAX(gpa) AS top, SUM(CASE WHEN recom_code = '.self::PASS_RECOM.' THEN 1 ELSE 0 END) AS passed')
            ->first();

        $facultyNames = Faculty::query()->pluck('name_ar', 'code');
        $departmentNames = Department::query()->pluck('name_ar', 'code');
        $levelNames = Level::query()->pluck('name_ar', 'code');

        $rank = fn (Collection $rows, Collection $names): array => $rows
            ->map(fn ($r): array => [
                'name' => (string) ($names[$r->k] ?? ($r->k ?: 'غير محدد')),
                'n' => (int) $r->n,
                'gpa' => round((float) $r->gpa, 2),
                'rate' => $r->n ? round($r->passed / $r->n * 100, 1) : 0.0,
            ])
            ->sortByDesc('rate')->values()->all();

        return [
            'students' => $this->students($user, $f),
            'profile' => $this->profile($user, $f),
            'installments' => $this->installments($user, $f),
            'finance' => $this->finance($user, $f),
            'fees' => $this->feeTable($user, $f),
            'kpi' => [
                'evaluated' => (int) ($overall->n ?? 0),
                'gpa' => round((float) ($overall->gpa ?? 0), 2),
                'top_gpa' => round((float) ($overall->top ?? 0), 2),
                'pass_rate' => ($overall->n ?? 0) ? round($overall->passed / $overall->n * 100, 1) : 0.0,
            ],
            'byFaculty' => $rank($aggregate('faculty_code'), $facultyNames),
            'byDepartment' => $rank($aggregate('department_code'), $departmentNames),
            'byLevel' => $rank($aggregate('level_code'), $levelNames),
            'trend' => $this->trend($user, $f),
            'recomMix' => $this->recomMix($completed($recoms())),
            'gpaBuckets' => $this->gpaBuckets($completed($recoms())),
            'grades' => $this->grades($user, $f),
            'markBuckets' => $this->markBuckets($user, $f),
            'subjects' => $this->subjects($user, $f),
            'top' => $this->topStudents($recoms()),
            'stages' => $this->stages($user, $f),
        ];
    }

    private function recoms(User $user, array $f, bool $withBatch = true): Builder
    {
        return $user->constrainToAcademicScope(Recom::query())
            ->where('examType', $f['exam'])
            ->when($withBatch && $f['result_batch'], fn (Builder $q) => $q->where('batch_code', $f['result_batch']))
            ->when($f['faculty'], fn (Builder $q, $v) => $q->where('faculty_code', $v))
            ->when($f['department'], fn (Builder $q, $v) => $q->where('department_code', $v))
            ->when($f['level'], fn (Builder $q, $v) => $q->where('level_code', $v));
    }

    private function marks(User $user, array $f): Builder
    {
        return $user->constrainToAcademicScope(Mark::query())
            ->where('examType', $f['exam'])
            ->when($f['result_batch'], fn (Builder $q, $v) => $q->where('batch_code', $v))
            ->when($f['faculty'], fn (Builder $q, $v) => $q->where('faculty_code', $v))
            ->when($f['department'], fn (Builder $q, $v) => $q->where('department_code', $v))
            ->when($f['level'], fn (Builder $q, $v) => $q->where('level_code', $v));
    }

    private function studentQuery(User $user, array $f): Builder
    {
        return $user->constrainToAcademicScope(Student::query())
            ->when($f['batch'], fn (Builder $q, $v) => $q->where('batch_code', $v))
            ->when($f['faculty'], fn (Builder $q, $v) => $q->where('faculty_code', $v))
            ->when($f['department'], fn (Builder $q, $v) => $q->where('department_code', $v))
            ->when($f['level'], fn (Builder $q, $v) => $q->where('level_code', $v));
    }

    /**
     * @return array{total: int, byFaculty: array<int, array{name: string, n: int}>, byLevel: array<int, array{name: string, n: int}>, byDepartment: array<int, array{name: string, n: int}>}
     */
    private function students(User $user, array $f): array
    {
        $count = fn (string $column, Collection $names): array => $this->studentQuery($user, $f)
            ->selectRaw("{$column} AS k, COUNT(*) AS n")->groupBy($column)->orderByDesc('n')->limit(12)->get()
            ->map(fn ($r): array => ['name' => (string) ($names[$r->k] ?? 'غير محدد'), 'n' => (int) $r->n])->all();

        return [
            'total' => $this->studentQuery($user, $f)->count(),
            'byFaculty' => $count('faculty_code', Faculty::query()->pluck('name_ar', 'code')),
            'byLevel' => $count('level_code', Level::query()->pluck('name_ar', 'code')),
            'byDepartment' => $count('department_code', Department::query()->pluck('name_ar', 'code')),
        ];
    }

    /**
     * ملخص مالي لكل عملة (رسوم، متحصل، متبقي، عدد المدينين) مع تفصيل الكليات.
     *
     * @return array{totals: array<int, array<string, mixed>>, byFaculty: array<int, array<string, mixed>>}
     */
    private function finance(User $user, array $f): array
    {
        $totals = $this->studentQuery($user, $f)
            ->selectRaw('currency, COUNT(*) AS n, SUM(all_fees) AS fees, SUM(pay) AS paid, SUM(xmod) AS due, SUM(CASE WHEN xmod > 0 THEN 1 ELSE 0 END) AS debtors')
            ->groupBy('currency')->get()
            ->map(fn ($r): array => [
                'currency' => (string) ($r->currency ?: 'SDG'),
                'students' => (int) $r->n,
                'fees' => (float) $r->fees,
                'paid' => (float) $r->paid,
                'due' => (float) $r->due,
                'debtors' => (int) $r->debtors,
                'rate' => $r->fees > 0 ? round($r->paid / $r->fees * 100, 1) : 0.0,
            ])->sortByDesc('fees')->values()->all();

        $names = Faculty::query()->pluck('name_ar', 'code');

        $byFaculty = $this->studentQuery($user, $f)
            ->selectRaw('faculty_code AS k, SUM(all_fees) AS fees, SUM(pay) AS paid, SUM(xmod) AS due')
            ->groupBy('faculty_code')->orderByDesc('fees')->limit(12)->get()
            ->map(fn ($r): array => [
                'name' => (string) ($names[$r->k] ?? 'غير محدد'),
                'fees' => (float) $r->fees,
                'paid' => (float) $r->paid,
                'due' => (float) $r->due,
                'rate' => $r->fees > 0 ? round($r->paid / $r->fees * 100, 1) : 0.0,
            ])->all();

        return ['totals' => $totals, 'byFaculty' => $byFaculty];
    }

    /**
     * نسبة النجاح والمعدل لآخر 6 أعوام دراسية (بغض النظر عن العام المختار).
     *
     * @return array<int, array{batch: string, rate: float, gpa: float, n: int}>
     */
    private function trend(User $user, array $f): array
    {
        $names = LtColumn::query()->where('grouped', 2003)->pluck('name_ar', 'code');

        return $this->recoms($user, $f, false)
            ->whereNotNull('recom_code')->where('recom_code', '!=', self::INCOMPLETE_RECOM)
            ->selectRaw('batch_code AS k, COUNT(*) AS n, AVG(gpa) AS gpa, SUM(CASE WHEN recom_code = '.self::PASS_RECOM.' THEN 1 ELSE 0 END) AS passed')
            ->groupBy('batch_code')->orderByDesc('batch_code')->limit(6)->get()->reverse()->values()
            ->map(fn ($r): array => [
                'batch' => (string) ($names[$r->k] ?? $r->k),
                'rate' => $r->n ? round($r->passed / $r->n * 100, 1) : 0.0,
                'gpa' => round((float) $r->gpa, 2),
                'n' => (int) $r->n,
            ])->all();
    }

    /**
     * @return array<int, array{name: string, n: int, percent: float}>
     */
    private function recomMix(Builder $query): array
    {
        $rows = $query->selectRaw('recom_code AS k, COUNT(*) AS n')->groupBy('recom_code')->orderByDesc('n')->get();
        $total = max($rows->sum('n'), 1);
        $names = LtRecom::query()->whereIn('code', $rows->pluck('k'))->pluck('name_ar', 'code');

        return $rows->map(fn ($r): array => [
            'name' => (string) ($names[$r->k] ?? 'قرار '.$r->k),
            'n' => (int) $r->n,
            'percent' => round($r->n / $total * 100, 1),
        ])->all();
    }

    /**
     * @return array<int, array{label: string, n: int}>
     */
    private function gpaBuckets(Builder $query): array
    {
        $labels = ['أقل من 1', '1 - 2', '2 - 2.5', '2.5 - 3', '3 - 3.5', '3.5 - 4'];

        $rows = $query->whereNotNull('gpa')
            ->selectRaw('CASE WHEN gpa < 1 THEN 0 WHEN gpa < 2 THEN 1 WHEN gpa < 2.5 THEN 2 WHEN gpa < 3 THEN 3 WHEN gpa < 3.5 THEN 4 ELSE 5 END AS bucket, COUNT(*) AS n')
            ->groupBy('bucket')->pluck('n', 'bucket');

        return collect($labels)->map(fn (string $label, int $i): array => ['label' => $label, 'n' => (int) ($rows[$i] ?? 0)])->all();
    }

    /**
     * @return array<int, array{grade: string, n: int, percent: float}>
     */
    private function grades(User $user, array $f): array
    {
        $rows = $this->marks($user, $f)->whereNotNull('grade')->where('grade', '!=', '')
            ->selectRaw('grade, COUNT(*) AS n')->groupBy('grade')->pluck('n', 'grade');

        $total = max($rows->sum(), 1);
        $order = collect(self::GRADE_ORDER);

        return $rows->sortBy(fn ($n, $grade) => ($index = $order->search($grade)) === false ? 100 + (int) $n * -1 : $index)
            ->take(10)
            ->map(fn ($n, $grade): array => [
                'grade' => (string) preg_replace('/^\+([A-Za-z])$/', '$1+', (string) $grade),
                'n' => (int) $n,
                'percent' => round($n / $total * 100, 1),
            ])->values()->all();
    }

    /**
     * @return array<int, array{label: string, n: int}>
     */
    private function markBuckets(User $user, array $f): array
    {
        $rows = $this->marks($user, $f)->whereNotNull('mark')
            ->selectRaw('LEAST(FLOOR(mark / 10), 9) AS bucket, COUNT(*) AS n')->groupBy('bucket')->pluck('n', 'bucket');

        return collect(range(0, 9))->map(fn (int $i): array => [
            'label' => $i === 9 ? '90+' : ($i * 10).'-'.($i * 10 + 9),
            'n' => (int) ($rows[$i] ?? 0),
        ])->all();
    }

    /**
     * أضعف 8 مواد بنسبة النجاح ثم أقواها (مع المتوسط والعدد).
     *
     * @return array<int, array{name: string, n: int, avg: float, rate: float}>
     */
    private function subjects(User $user, array $f): array
    {
        $rows = $this->marks($user, $f)->whereNotNull('mark')
            ->selectRaw('subject_code AS k, COUNT(*) AS n, AVG(mark) AS avg, SUM(CASE WHEN mark >= '.(float) RegulationProfile::current()->passMark().' THEN 1 ELSE 0 END) AS passed')
            ->groupBy('subject_code')->orderByDesc('n')->limit(60)->get();

        $names = Subject::query()->whereIn('code', $rows->pluck('k'))->pluck('name_ar', 'code');

        return $rows->map(fn ($r): array => [
            'name' => (string) ($names[$r->k] ?? $r->k),
            'n' => (int) $r->n,
            'avg' => round((float) $r->avg, 1),
            'rate' => $r->n ? round($r->passed / $r->n * 100, 1) : 0.0,
        ])->sortBy('rate')->take(10)->values()->all();
    }

    /**
     * @return array<int, array{name: string, gpa: float, meta: string}>
     */
    private function topStudents(Builder $query): array
    {
        return $query->where('gpa', '>', 0)->with(['student:university_no,name_ar,faculty_code', 'student.faculty:code,name_ar'])
            ->orderByDesc('gpa')->limit(5)->get()
            ->map(fn (Recom $r): array => [
                'name' => (string) ($r->student?->name_ar ?? $r->university_no),
                'gpa' => round((float) $r->gpa, 2),
                'meta' => (string) ($r->student?->faculty?->name_ar ?? ''),
            ])->all();
    }

    /**
     * أعداد الطلاب حسب: الدراسة (stady_code) والقبول (admission_code) والنوع (gender_code)
     * وسنة القبول (academic_year_code) وحالة التسجيل (xstatus). الأسماء من reg_ltcolumns.
     *
     * @return array<string, array<int, array{name: string, n: int, percent: float}>>
     */
    private function profile(User $user, array $f): array
    {
        $groups = [
            'stady' => ['stady_code', 2006],
            'admission' => ['admission_code', 2001],
            'gender' => ['gender_code', 1001],
            'admission_year' => ['academic_year_code', 2003],
            'xstatus' => ['xstatus', 2007],
        ];

        return collect($groups)->map(function (array $spec) use ($user, $f): array {
            [$column, $group] = $spec;

            $rows = $this->studentQuery($user, $f)
                ->selectRaw("{$column} AS k, COUNT(*) AS n")->groupBy($column)->orderByDesc('n')->limit(15)->get();

            $names = LtColumn::query()->where('grouped', $group)->whereIn('code', $rows->pluck('k')->filter())->pluck('name_ar', 'code');
            $total = max($rows->sum('n'), 1);

            return $rows->map(fn ($r): array => [
                'name' => (string) ($names[$r->k] ?? 'غير محدد'),
                'n' => (int) $r->n,
                'percent' => round($r->n / $total * 100, 1),
            ])->all();
        })->all();
    }

    /**
     * توزيع الطلاب حسب نظام الأقساط (installment_code) ونسبة من يدفعون بالتقسيط (غير «كامل»).
     *
     * @return array{rows: array<int, array{name: string, n: int, percent: float}>, rate: float, total: int}
     */
    private function installments(User $user, array $f): array
    {
        $rows = $this->studentQuery($user, $f)
            ->selectRaw('installment_code AS k, COUNT(*) AS n')->groupBy('installment_code')->orderByDesc('n')->get();

        $names = LtColumn::query()->where('grouped', 2005)->whereIn('code', $rows->pluck('k')->filter())->pluck('name_ar', 'code');
        $total = max($rows->sum('n'), 1);

        $isFull = fn ($r): bool => $r->k === null || str_contains((string) ($names[$r->k] ?? ''), 'كامل');
        $installment = $rows->reject($isFull)->sum('n');

        return [
            'rows' => $rows->map(fn ($r): array => [
                'name' => (string) ($names[$r->k] ?? 'غير محدد'),
                'n' => (int) $r->n,
                'percent' => round($r->n / $total * 100, 1),
            ])->all(),
            'rate' => round($installment / $total * 100, 1),
            'total' => (int) $rows->sum('n'),
        ];
    }

    /**
     * جملة الرسوب مبوبة لكل كلية على حدة وبكل عملة: التسجيل والدراسية وبقية البنود (غير الصفرية فقط).
     *
     * @return array<string, array{columns: array<string, string>, rows: array<int, array<string, mixed>>, total: array<string, float>}>
     */
    private function feeTable(User $user, array $f): array
    {
        $items = ['reg_fees' => 'regfees', 'fees' => 'fees'] + collect(range(1, 10))->mapWithKeys(fn (int $i): array => ["fees{$i}" => "fees{$i}"])->all();
        $columns = array_merge(array_keys($items), ['all_fees', 'pay', 'xmod']);

        $select = collect($columns)->map(fn (string $c): string => "SUM({$c}) AS {$c}")->implode(', ');
        $names = Faculty::query()->pluck('name_ar', 'code');

        $rows = $this->studentQuery($user, $f)
            ->selectRaw("currency, faculty_code AS k, COUNT(*) AS students, {$select}")
            ->groupBy('currency', 'faculty_code')->get()->groupBy(fn ($r) => (string) ($r->currency ?: 'SDG'));

        return $rows->map(function (Collection $group) use ($items, $columns, $names): array {
            $used = collect($items)->filter(fn ($label, $c): bool => $group->sum($c) > 0);

            return [
                'columns' => $used->map(fn (string $label): string => (string) trans('reg.'.$label, [], 'ar'))->all(),
                'rows' => $group->sortByDesc('all_fees')->map(function ($r) use ($columns, $names): array {
                    $line = ['name' => (string) ($names[$r->k] ?? 'غير محدد'), 'students' => (int) $r->students];
                    foreach ($columns as $c) {
                        $line[$c] = (float) $r->{$c};
                    }
                    $line['rate'] = $line['all_fees'] > 0 ? round($line['pay'] / $line['all_fees'] * 100, 1) : 0.0;

                    return $line;
                })->values()->all(),
                'total' => collect($columns)->mapWithKeys(fn (string $c): array => [$c => (float) $group->sum($c)])->all()
                    + ['students' => (int) $group->sum('students')],
            ];
        })->sortKeys()->all();
    }

    /**
     * عدد المواد في كل مرحلة اعتماد، وعدد المواد التي لم يبدأ اعتمادها.
     *
     * @return array{stages: array<int, array{value: string, label: string, n: int}>, subjects: int}
     */
    private function stages(User $user, array $f): array
    {
        $subjects = $user->constrainToAcademicScope(Subject::query()->active())
            ->when($f['faculty'], fn (Builder $q, $v) => $q->where('faculty_code', $v))
            ->when($f['department'], fn (Builder $q, $v) => $q->where('department_code', $v))
            ->when($f['level'], fn (Builder $q, $v) => $q->where('level_code', $v));

        $rows = SubjectApprovalStatus::query()
            ->when($f['result_batch'], fn (Builder $q, $v) => $q->where('batch_code', $v))
            ->whereIn('subject_code', (clone $subjects)->select('code'))
            ->get(['current_stage'])
            ->groupBy(fn ($row) => $row->current_stage?->value ?? 'professor');

        $stages = collect(MarkApprovalStage::cases())->map(fn ($case): array => [
            'value' => $case->value,
            'label' => (string) $case->getLabel(),
            'n' => $rows->get($case->value)?->count() ?? 0,
        ])->all();

        return ['stages' => $stages, 'subjects' => (clone $subjects)->count()];
    }
}

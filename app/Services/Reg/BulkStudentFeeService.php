<?php

namespace App\Services\Reg;

use App\Models\Student;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * إدخال رسم دراسي واحد (وتقسيطه) لكل طلاب كلية وقسم وفصل وعام دراسي دفعة واحدة.
 * الحساب كله يمر عبر StudentFeeCalculator وStudentFeeService (بمراقب Student) فلا تُكتب الأعمدة المحسوبة يدوياً.
 */
class BulkStudentFeeService
{
    public const PERMISSION = 'Apply:BulkStudentFees';

    /** الأعمدة التي يمكن إدخالها جماعياً (من حقول رسوم الطالب) */
    public const FEE_TYPES = ['reg_fees', 'fees', 'fees1', 'fees2', 'fees3', 'fees5', 'fees6', 'fees7', 'fees8', 'fees9'];

    /** الأعمدة التي تُسجَّل قبل التطبيق وبعده */
    private const TRACKED = [
        'reg_fees', 'fees', 'fees1', 'fees2', 'fees3', 'fees5', 'fees6', 'fees7', 'fees8', 'fees9',
        'installment_code', 'discount_percent',
        'all_fees', 'xmod', 'pay', 'fees10', 'pay1', 'pay2', 'pay3', 'pay4', 'xstatus',
    ];

    public function __construct(
        private readonly StudentFeeCalculator $calculator,
        private readonly StudentFeeService $fees,
    ) {}

    /**
     * الطلاب المطابقون للفلاتر ضمن نطاق المستخدم الأكاديمي.
     *
     * @param  array<string, mixed>  $filters  faculty_code, department_code, level_code, batch_code
     * @return Builder<Student>
     */
    public function query(User $user, array $filters): Builder
    {
        $this->assertAllowed($user);

        foreach (['faculty_code', 'department_code', 'level_code', 'batch_code'] as $key) {
            if (blank($filters[$key] ?? null)) {
                throw new DomainException(__('invoice.bulk_fees.filters_required'));
            }
        }

        return $user->constrainToAcademicScope(
            Student::query()
                ->where('faculty_code', $filters['faculty_code'])
                ->where('department_code', $filters['department_code'])
                ->where('level_code', $filters['level_code'])
                ->where('batch_code', $filters['batch_code'])
        )->orderBy('university_no');
    }

    /**
     * عدد الطلاب وأمثلة من القيم الناتجة دون أي حفظ.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $changes  fee_type, amount, installment_code
     * @return array{count: int, samples: list<array<string, mixed>>}
     */
    public function preview(User $user, array $filters, array $changes, int $sampleSize = 5): array
    {
        $attributes = $this->attributes($changes);
        $query = $this->query($user, $filters);
        $students = (clone $query)->limit($sampleSize)->get();
        $totals = $this->fees->totalsFor($students);

        return [
            'count' => (clone $query)->count(),
            'samples' => $students->map(function (Student $student) use ($attributes, $totals): array {
                $paid = $totals[(string) $student->university_no] ?? 0.0;
                $current = $student->only(self::TRACKED);
                $before = $this->calculator->calculate($current, $paid);
                $after = $this->calculator->calculate(array_merge($current, $attributes), $paid);

                return [
                    'university_no' => $student->university_no,
                    'name_ar' => $student->name_ar,
                    'before' => ['all_fees' => $before['all_fees'], 'xmod' => $before['xmod']],
                    'after' => array_intersect_key($after, array_flip(['all_fees', 'xmod', 'pay1', 'pay2', 'pay3', 'pay4'])),
                ];
            })->all(),
        ];
    }

    /**
     * يطبّق القيم على كل الطلاب المطابقين داخل transaction ويسجّل العملية، ويرجع عدد من تغيّر.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $changes  fee_type, amount, installment_code
     */
    public function apply(User $user, array $filters, array $changes): int
    {
        $attributes = $this->attributes($changes);
        $query = $this->query($user, $filters);

        return DB::transaction(function () use ($user, $filters, $changes, $attributes, $query): int {
            $rows = [];
            $matched = 0;

            $query->lockForUpdate()->get()->each(function (Student $student) use ($attributes, &$rows, &$matched): void {
                $before = $student->only(self::TRACKED);
                $student->update($attributes);
                $after = $student->fresh()->only(self::TRACKED);
                $matched++;

                $diff = array_filter($after, fn (mixed $value, string $key): bool => (float) $value !== (float) $before[$key], ARRAY_FILTER_USE_BOTH);

                if ($diff !== []) {
                    $rows[(string) $student->university_no] = ['before' => array_intersect_key($before, $diff), 'after' => $diff];
                }
            });

            activity('bulk_fees')
                ->causedBy($user)
                ->withProperties([
                    'filters' => $filters,
                    'changes' => $changes,
                    'matched' => $matched,
                    'changed' => count($rows),
                    'students' => $rows,
                ])
                ->log(__('invoice.bulk_fees.log'));

            return count($rows);
        });
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function attributes(array $changes): array
    {
        $type = $changes['fee_type'] ?? null;

        if (! in_array($type, self::FEE_TYPES, true)) {
            throw new DomainException(__('invoice.bulk_fees.invalid_fee_type'));
        }

        $amount = $changes['amount'] ?? null;

        if (! is_numeric($amount) || (float) $amount < 0) {
            throw new DomainException(__('invoice.bulk_fees.invalid_amount'));
        }

        $attributes = [$type => (float) $amount];

        if (filled($changes['installment_code'] ?? null)) {
            $attributes['installment_code'] = $changes['installment_code'];
        }

        return $attributes;
    }

    private function assertAllowed(User $user): void
    {
        if (! $user->can(self::PERMISSION)) {
            throw new DomainException(__('invoice.bulk_fees.forbidden'));
        }
    }
}

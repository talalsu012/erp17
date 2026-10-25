<?php

namespace App\Services\Reg;

use App\Models\Reg\OpenReg;
use App\Models\Reg\Payment;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * يحدّث حقول رسوم الطالب (all_fees, xmod, pay, fees10, pay1..pay4, xstatus) من رسومه ومدفوعاته.
 * يحلّ محل المشغّلات (after_student_update, payments_after_insert, payments_after_update) والدالة والإجراء المخزَّنَين.
 *
 * المدفوع المعتدّ به: مدفوعات الطالب غير المحذوفة التي يقع تاريخها داخل فترة تسجيله في open-regs
 * (أو من تاريخ فتح التسجيل له فردياً)، فمن لا فترة له لا يُحتسب له مدفوع.
 */
class StudentFeeService
{
    public function __construct(
        private readonly StudentFeeCalculator $calculator,
        private readonly RegistrationAccessService $access,
    ) {}

    /**
     * مجموع المدفوع المعتدّ به للطالب (بديل الدالة CalculateEarlyPayment).
     */
    public function totalPaid(Student $student): float
    {
        return $this->totalsFor(Collection::make([$student]))[(string) $student->university_no] ?? 0.0;
    }

    /**
     * مجاميع المدفوع لعدة طلاب باستعلامين فقط: فترات open-regs لكلياتهم ثم مدفوعاتهم.
     *
     * @param  Collection<int, Student>  $students
     * @return array<string, float> رقم الطالب ← مجموع المدفوع
     */
    public function totalsFor(Collection $students): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        $windows = OpenReg::query()
            ->whereIn('faculty_code', $students->pluck('faculty_code')->filter()->unique()->all())
            ->get()
            ->groupBy('faculty_code');

        $periods = $students->mapWithKeys(fn (Student $student): array => [
            (string) $student->university_no => $this->access->periodsFor($student, $windows->get($student->faculty_code, new Collection)),
        ])->filter();

        if ($periods->isEmpty()) {
            return [];
        }

        $totals = [];

        Payment::query()
            ->whereIn('university_no', $periods->keys()->all())
            ->get(['university_no', 'amount', 'bankdate'])
            ->each(function (Payment $payment) use ($periods, &$totals): void {
                $date = substr((string) $payment->getRawOriginal('bankdate'), 0, 10);
                $key = (string) $payment->university_no;

                foreach ($periods[$key] as [$start, $end]) {
                    if (($start === null || $start <= $date) && ($end === null || $end >= $date)) {
                        $totals[$key] = ($totals[$key] ?? 0.0) + (float) $payment->amount;

                        return;
                    }
                }
            });

        return $totals;
    }

    /**
     * يضع القيم المحسوبة على نموذج الطالب دون حفظه (للاستعمال داخل مراقب updating).
     */
    public function apply(Student $student, ?float $totalPaid = null): Student
    {
        $computed = $this->calculator->calculate(
            $student->only([
                'fees', 'reg_fees', 'fees1', 'fees2', 'fees3', 'fees6', 'fees7', 'fees9',
                'installment_code', 'discount_percent', 'pay1', 'pay2', 'pay3', 'pay4',
            ]),
            $totalPaid ?? $this->totalPaid($student),
        );

        return $student->forceFill($computed);
    }

    /**
     * يعيد حساب طالب بعد تغيّر مدفوعاته ويحفظه دون إطلاق المراقبات (فلا يُحسب مرتين).
     */
    public function refresh(int|string $universityNo): ?Student
    {
        $student = Student::query()->find($universityNo);

        if (! $student) {
            return null;
        }

        $this->apply($student)->saveQuietly();

        return $student;
    }

    /**
     * يعيد حساب عدة طلاب (مثلاً بعد ترحيل جماعي).
     *
     * @param  iterable<int, int|string>  $universityNos
     */
    public function refreshMany(iterable $universityNos): int
    {
        $count = 0;

        foreach (Collection::make($universityNos)->unique()->values()->chunk(500) as $chunk) {
            $count += $this->refreshStudents(Student::query()->whereIn('university_no', $chunk->all())->get());
        }

        return $count;
    }

    /**
     * يعيد حساب كل طلاب كلية (وقسم ومستوى ودفعة اختيارياً) على دفعات، عند تغيّر فترات open-regs.
     *
     * @param  array{department?: mixed, level?: mixed, batch?: mixed}  $scope
     */
    public function refreshFaculty(int|string $facultyCode, array $scope = []): int
    {
        $count = 0;

        Student::query()
            ->where('faculty_code', $facultyCode)
            ->when($scope['department'] ?? null, fn ($query, $value) => $query->where('department_code', $value))
            ->when($scope['level'] ?? null, fn ($query, $value) => $query->where('level_code', $value))
            ->when($scope['batch'] ?? null, fn ($query, $value) => $query->where('batch_code', $value))
            ->orderBy('university_no')
            ->chunk(500, function (Collection $students) use (&$count): void {
                $count += $this->refreshStudents($students);
            });

        return $count;
    }

    /**
     * @param  Collection<int, Student>  $students
     */
    private function refreshStudents(Collection $students): int
    {
        $totals = $this->totalsFor($students);

        $students->each(fn (Student $student) => $this->apply($student, $totals[(string) $student->university_no] ?? 0.0)->saveQuietly());

        return $students->count();
    }
}

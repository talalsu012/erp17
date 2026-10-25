<?php

namespace App\Services\Reg;

/**
 * حساب الرسوم والأقساط والمتبقي للطالب (بديل الإجراء المخزَّن CalculateInstallmentsWithDiscount).
 *
 * دالة نقية بلا قاعدة بيانات: تستلم حقول الرسوم والأقساط ومجموع المدفوع، وترجع الأعمدة المحسوبة.
 * القيم المالية تُقرَّب لمنزلتين عند كل خطوة كما كانت متغيرات DECIMAL(10,2) في الإجراء.
 */
class StudentFeeCalculator
{
    /** حالة الدفع (reg_ltcolumns): 20070001 مسدّد، 20070002 مسدّد جزئياً، 20070003 غير مسدّد */
    public const STATUS_PAID = 20070001;

    public const STATUS_PARTIAL = 20070002;

    public const STATUS_UNPAID = 20070003;

    /** نوع التقسيط اليدوي: آخر رقم في installment_code يساوي 7 */
    private const MANUAL_INSTALLMENT = 7;

    /**
     * @param  array{fees?: mixed, reg_fees?: mixed, fees1?: mixed, fees2?: mixed, fees3?: mixed, fees6?: mixed, fees7?: mixed, fees9?: mixed, installment_code?: mixed, discount_percent?: mixed, pay1?: mixed, pay2?: mixed, pay3?: mixed, pay4?: mixed}  $fees
     * @return array{all_fees: float, xmod: float, pay: float, fees10: float, pay1: float, pay2: float, pay3: float, pay4: float, xstatus: int}
     */
    public function calculate(array $fees, float $totalPaid): array
    {
        $regFees = $this->money(
            $this->number($fees['reg_fees'] ?? 0) + $this->number($fees['fees1'] ?? 0) + $this->number($fees['fees2'] ?? 0)
            + $this->number($fees['fees3'] ?? 0) + $this->number($fees['fees6'] ?? 0) + $this->number($fees['fees7'] ?? 0)
        );
        $originalFees = $this->money($this->number($fees['fees'] ?? 0));
        $installments = $this->lastDigits($fees['installment_code'] ?? null, 1);
        $discountPercent = (int) $this->lastDigits($fees['discount_percent'] ?? 0, 3);

        $paid = $this->money($totalPaid);
        $paidTotal = $paid;
        $discountedFees = $this->money($originalFees * (1 - $discountPercent / 100));
        $support = $this->money($originalFees - $discountedFees);
        $totalPayment = $this->money($regFees + $discountedFees);
        $remaining = $this->money(max($totalPayment - $paid, 0));

        [$pay1, $pay2, $pay3, $pay4] = $this->installments($installments, $regFees, $discountedFees, $totalPayment, $fees);

        [$pay1, $pay2, $pay3, $pay4] = $this->deductPayments($paid, $pay1, $pay2, $pay3, $pay4);

        if ($installments === self::MANUAL_INSTALLMENT) {
            $pay1 = $this->number($fees['fees9'] ?? 0);
        }

        return [
            'all_fees' => $totalPayment,
            'xmod' => $remaining,
            'pay' => $paidTotal,
            'fees10' => $support,
            'pay1' => $pay1,
            'pay2' => $pay2,
            'pay3' => $pay3,
            'pay4' => $pay4,
            'xstatus' => $this->status($totalPayment, $paidTotal),
        ];
    }

    /**
     * توزيع المبلغ على الأقساط: يدوي (7)، أربعة فأكثر، ثلاثة، قسطان، أو دفعة واحدة.
     *
     * @param  array<string, mixed>  $fees
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function installments(?int $count, float $regFees, float $discountedFees, float $totalPayment, array $fees): array
    {
        if ($count === self::MANUAL_INSTALLMENT) {
            return [
                $this->number($fees['pay1'] ?? 0), $this->number($fees['pay2'] ?? 0),
                $this->number($fees['pay3'] ?? 0), $this->number($fees['pay4'] ?? 0),
            ];
        }

        if ($count !== null && $count >= 4) {
            $amount = $this->money($discountedFees / 4);

            return [$this->money($regFees + $amount), $amount, $amount, $this->money($discountedFees - $amount * 3)];
        }

        if ($count === 3) {
            $amount = $this->money($discountedFees / 3);

            return [$this->money($regFees + $amount), $amount, $this->money($discountedFees - $amount * 2), 0.0];
        }

        if ($count === 2) {
            $amount = $this->money($discountedFees / 2);

            return [$this->money($regFees + $amount), $this->money($discountedFees - $amount), 0.0, 0.0];
        }

        return [$totalPayment, 0.0, 0.0, 0.0];
    }

    /**
     * خصم المدفوعات من الأقساط بالتوالي: القسط الأول ثم الثاني وهكذا، والباقي يُخصم من أول قسط لم يُسدَّد بالكامل.
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function deductPayments(float $paid, float $pay1, float $pay2, float $pay3, float $pay4): array
    {
        if ($paid <= 0) {
            return [$pay1, $pay2, $pay3, $pay4];
        }

        if ($paid < $pay1) {
            return [$this->money($pay1 - $paid), $pay2, $pay3, $pay4];
        }

        $paid = $this->money($paid - $pay1);

        if ($paid < $pay2) {
            return [0.0, $this->money($pay2 - $paid), $pay3, $pay4];
        }

        $paid = $this->money($paid - $pay2);

        if ($paid < $pay3) {
            return [0.0, 0.0, $this->money($pay3 - $paid), $pay4];
        }

        $paid = $this->money($paid - $pay3);

        return [0.0, 0.0, 0.0, $paid >= $pay4 ? 0.0 : $this->money($pay4 - $paid)];
    }

    private function status(float $totalPayment, float $paid): int
    {
        return match (true) {
            $totalPayment == 0.0 || $paid == 0.0 => self::STATUS_UNPAID,
            $paid < $totalPayment => self::STATUS_PARTIAL,
            default => self::STATUS_PAID,
        };
    }

    /** آخر $length رقم من القيمة كعدد (RIGHT في SQL)، أو null إن كانت القيمة فارغة */
    private function lastDigits(mixed $value, int $length): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) substr((string) (int) $value, -$length);
    }

    private function number(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function money(float $value): float
    {
        return round($value, 2);
    }
}

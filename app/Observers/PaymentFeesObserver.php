<?php

namespace App\Observers;

use App\Models\Reg\Payment;
use App\Services\Reg\StudentFeeService;

/**
 * يعيد حساب رسوم الطالب عند إضافة دفعة أو تعديلها أو حذفها أو استرجاعها
 * (بديل المشغّلين payments_after_insert وpayments_after_update).
 */
class PaymentFeesObserver
{
    public function __construct(private readonly StudentFeeService $fees) {}

    public function saved(Payment $payment): void
    {
        $this->fees->refresh($payment->university_no);

        if ($payment->wasChanged('university_no') && filled($payment->getOriginal('university_no'))) {
            $this->fees->refresh($payment->getOriginal('university_no'));
        }
    }

    public function deleted(Payment $payment): void
    {
        $this->fees->refresh($payment->university_no);
    }

    public function restored(Payment $payment): void
    {
        $this->fees->refresh($payment->university_no);
    }
}

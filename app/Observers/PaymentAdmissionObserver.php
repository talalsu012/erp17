<?php

namespace App\Observers;

use App\Models\Reg\Payment;
use App\Services\AdmissionWorkflowService;

class PaymentAdmissionObserver
{
    public function __construct(private readonly AdmissionWorkflowService $workflow) {}

    public function created(Payment $payment): void
    {
        $this->workflow->onPayment($payment);
    }
}

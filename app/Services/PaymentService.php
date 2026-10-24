<?php

namespace App\Services;

use App\Models\Invoice\Invoice;
use App\Models\Invoice\PaymentLogApi;
use App\Models\Reg\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function findPayer($id)
    {
        // البحث عن طالب
        if ($student = Student::where('university_no', $id)->with('faculty')->first()) {
            return ['type' => 'student', 'data' => $student];
        }

        // البحث عن فاتورة
        if ($invoice = Invoice::where('invoice_no', $id)->with(['faculty', 'systype'])->first()) {
            return ['type' => 'invoice', 'data' => $invoice];
        }

        return null;
    }

    public function processInvoicePayment($invoice, $request)
    {
        return DB::transaction(function () use ($invoice, $request) {
            $locked = Invoice::query()->whereKey($invoice->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->bank_pay == 1) {
                return false;
            }

            return $locked->update([
                'bank_pay' => 1,
                'bank' => $request->bank,
                'bankdate' => now(),
                'voucher_no' => $request->voucher_no,
                'status' => 'paid',
            ]);
        });
    }

    // داخل App\Services\PaymentService.php

    public function createStudentPayment($student, $request)
    {
        // هنا نضع منطق الإنشاء المباشر
        return Payment::create([
            'university_no' => $student->university_no,
            'amount' => $request->amount,
            'bank' => $request->bank,
            'bankdate' => now(),
            'voucher_no' => $request->voucher_no,
            'branch' => auth()->user()->name,
            'batch_code' => $student->batch_code,
            'level_code' => $student->level_code,
            'sys_type' => 'REG',
        ]);
    }

    public function logActivity($request, $message, $code)
    {
        PaymentLogApi::create([
            'invoice_no' => $request->university_no,
            'name' => $request->name,
            'amount' => $request->amount,
            'bankdate' => now(),
            'voucher_no' => $request->voucher_no,
            'bank' => $request->bank,
            'branch' => Auth::user()->name ?? null,
            'user_id' => Auth::user()->id ?? null,
            'response_msg' => $message,
            'response_code' => $code,
        ]);
    }
}

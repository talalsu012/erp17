<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePaymentRequest;
use App\Http\Resources\InvoiceResource; // افترضنا وجوده
use App\Http\Resources\StudentFeeResource;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class PaymentApiController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function getStudentFees($id)
    {
        $payer = $this->paymentService->findPayer($id);

        if (! $payer) {
            return response()->json(['status' => 'error', 'message' => 'غير موجود'], 404);
        }

        return $payer['type'] === 'student'
            ? new StudentFeeResource($payer['data'])
            : new InvoiceResource($payer['data']);
    }

    public function storePayment(StorePaymentRequest $request)
    {
        $id = (string) $request->validated('university_no');

        // 1. جلب البيانات (Payer) سواء كان طالب أو فاتورة
        $payer = $this->paymentService->findPayer($id);

        if (! $payer) {
            return $this->errorResponse($request, 'المعرف المدخل غير موجود في النظام', 404);
        }

        // 2. معالجة الفواتير (100000)
        if ($request->isInvoicePayment()) {
            if ($payer['type'] !== 'invoice') {
                return $this->errorResponse($request, 'عذراً، هذا المعرف ليس فاتورة', 422);
            }

            $updated = $this->paymentService->processInvoicePayment($payer['data'], $request);

            if (! $updated) {
                return $this->errorResponse($request, 'الفاتورة مدفوعة مسبقاً', 422);
            }

            $this->paymentService->logActivity($request, 'Invoice Paid Successfully', 200);

            return response()->json(['message' => 'تم تحديث الفاتورة بنجاح', 'data' => $payer['data']]);
        }

        // 3. معالجة دفع الطلاب (التحقق تم في StorePaymentRequest)
        $payment = DB::transaction(function () use ($payer, $request) {
            $payment = $this->paymentService->createStudentPayment($payer['data'], $request);
            $this->paymentService->logActivity($request, 'Student Payment Created', 201);

            return $payment;
        });

        return response()->json([
            'message' => 'تم تسجيل دفع الطالب بنجاح',
            'data' => [
                'UNIVERSITY ID' => $payment->university_no,
                'AMOUNT' => $payment->amount,
                'VOUCHER' => $payment->voucher_no,
                'DATE' => $payment->bankdate,
            ],
        ], 201);
    }

    private function errorResponse($request, $message, $code)
    {
        $this->paymentService->logActivity($request, $message, $code);

        return response()->json(['status' => 'error', 'message' => $message], $code);
    }
}

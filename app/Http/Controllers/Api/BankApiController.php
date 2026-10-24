<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\ApiUser;
use App\Models\Reg\Payment;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * واجهة البنوك الأخرى مطابقة لنظام api القديم (htdocs/api) حتى لا تتغير ربط البنوك:
 * login وregister ثم APITIQ بثلاث طرق: GET استعلام (ترويسة Stdid)، POST دفع، PUT فحص قسيمة.
 * أخطاء المصادقة تُرجع بنفس أشكال النظام القديم.
 */
class BankApiController extends BokApiController
{
    private const ALTERNATIVE_TOKEN_HEADERS = ['x-authorization', 'x-auth-token', 'access-token', 'token'];

    /**
     * يوزّع طلب APITIQ حسب الطريقة كما كان يفعل .htaccess القديم.
     */
    public function apitiq(Request $request): JsonResponse
    {
        return match ($request->method()) {
            'GET' => $this->studentInquiry($request),
            'POST' => $this->postPayment($request),
            'PUT' => $this->voucherCheck($request),
            default => $this->message(0, 404, 'Page Not Found!'),
        };
    }

    protected function unknownEmailMessage(): string
    {
        return 'Invalid Email Address!';
    }

    protected function studentInquiry(Request $request): JsonResponse
    {
        if ($request->header('Stdid') === null) {
            return $this->message(0, 422, 'Please Fill in all Required Fields!');
        }

        if ($failure = $this->tokenFailure($request)) {
            return $failure;
        }

        $user = $this->bankUser($request);

        if (! $user) {
            return $this->invalidToken($request);
        }

        $studentId = trim((string) $request->header('Stdid'));

        if ($studentId === '') {
            return $this->message(0, 422, 'Please Fill in all Required Fields!', ['fields' => ['Stdid']]);
        }

        $student = Student::query()->with('faculty')->has('faculty')->where('university_no', $studentId)->first();

        if (! $student) {
            return $this->message(0, 404, 'Student Not Found!');
        }

        return response()->json([
            'UNI' => $user->university,
            'SYSTYPE' => 'REGISTRATION',
            'FAC' => $student->faculty->name_ar,
            'FACID' => $student->faculty_code,
            'STDID' => $student->university_no,
            'NAME' => $student->name_ar,
            'AMT' => $this->amountDue($student),
        ]);
    }

    protected function postPayment(Request $request): JsonResponse
    {
        if ($failure = $this->tokenFailure($request)) {
            return $failure;
        }

        $user = $this->bankUser($request);

        if (! $user) {
            return $this->invalidToken($request);
        }

        $fields = ['STUDENTSID', 'AMOUNT', 'BANKDATE', 'VOUCHERNO', 'BRANCH'];

        if (collect($fields)->contains(fn (string $field): bool => trim((string) $request->input($field)) === '' || trim((string) $request->input($field)) === '0')) {
            return $this->message(0, 422, 'Please Fill in all Required Fields!', ['fields' => ['STUDENTSID', 'AMOUNT', 'VOUCHERNO', 'BANKDATE', 'branch']]);
        }

        try {
            $payment = Payment::query()->create([
                'university_no' => trim((string) $request->input('STUDENTSID')),
                'amount' => $request->input('AMOUNT'),
                'currency' => 'SDG',
                'bankdate' => now()->toDateString(),
                'bank' => $user->bank ?: 'LOC',
                'branch' => trim((string) $request->input('BRANCH')),
                'voucher_no' => trim((string) $request->input('VOUCHERNO')),
                'sys_type' => 'BANK_API',
            ]);
        } catch (QueryException) {
            return response()->json(['status' => 404, 'success' => 0, 'message' => 'Payment could not be posted']);
        }

        return $this->message(1, 200, 'You have successfully Payment.('.$payment->id.')');
    }

    protected function voucherCheck(Request $request): JsonResponse
    {
        if ($failure = $this->tokenFailure($request)) {
            return $failure;
        }

        $user = $this->bankUser($request);

        if (! $user) {
            return $this->invalidToken($request);
        }

        $voucher = trim((string) $request->input('VOUCHER'));

        if ($voucher === '') {
            return $this->message(0, 422, 'Please Fill in all Required Fields!', ['fields' => ['VOUCHERNO']]);
        }

        $payment = Payment::query()->where('bank', $user->bank)->where('voucher_no', $voucher)->first();

        if (! $payment) {
            return $this->message(0, 404, 'Payment Not Found!');
        }

        return response()->json([
            'ID' => $payment->id,
            'STUDENTID' => $payment->university_no,
            'AMT' => $payment->amount,
            'BANKDATE' => $payment->bankdate,
            'VOUCHER' => $payment->voucher_no,
            'BRANCH' => $payment->branch,
            'DONE' => 1,
            'BANK' => $payment->bank,
            'UNIVERSITY' => $user->university,
        ]);
    }

    /**
     * التوكن من Authorization أو من ترويسات بديلة لا تحذفها الوسائط الوسيطة.
     */
    private function bearerToken(Request $request): ?string
    {
        $header = (string) $request->header('Authorization');

        if ($header === '') {
            foreach (self::ALTERNATIVE_TOKEN_HEADERS as $alternative) {
                if ($value = (string) $request->header($alternative)) {
                    $header = preg_match('/^Bearer\s/i', $value) === 1 ? $value : 'Bearer '.$value;
                    break;
                }
            }
        }

        return preg_match('/Bearer\s(\S+)/', $header, $matches) === 1 ? $matches[1] : null;
    }

    private function tokenFailure(Request $request): ?JsonResponse
    {
        return $this->bearerToken($request) === null
            ? response()->json(['status' => 500, 'success' => 0, 'message' => 'Token not found in request'])
            : null;
    }

    private function bankUser(Request $request): ?ApiUser
    {
        $data = $this->jwt->decode((string) $this->bearerToken($request));

        return isset($data['user_id']) ? ApiUser::query()->find($data['user_id']) : null;
    }

    private function invalidToken(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 404,
            'success' => 0,
            'message' => $this->jwt->failureMessage((string) $this->bearerToken($request)),
        ]);
    }
}

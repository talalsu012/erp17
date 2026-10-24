<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiUser;
use App\Models\Reg\Payment;
use App\Models\Student;
use App\Services\Bok\BokJwt;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * واجهة البنك (بنك الخرطوم) مطابقة لنظام apiBOK القديم: نفس المسارات والحقول وأكواد الرد.
 * المصادقة عبر JWT يصدره login، وأكواد الحالة تُرجع في جسم الرد مع HTTP 200 كما في النظام القديم.
 */
class BokApiController extends Controller
{
    public function __construct(protected readonly BokJwt $jwt) {}

    public function login(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email'));
        $password = trim((string) $request->input('password'));

        if ($email === '' || $password === '') {
            return $this->message(0, 422, 'Please Fill in all Required Fields!', ['fields' => ['email', 'password']]);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->message(0, 422, 'Invalid Email Address!');
        }

        if (strlen($password) < 8) {
            return $this->message(0, 422, 'Your password must be at least 8 characters long!');
        }

        $user = ApiUser::query()->where('email', $email)->first();

        if (! $user) {
            return $this->message(0, 422, $this->unknownEmailMessage());
        }

        if (! Hash::check($password, $user->password)) {
            return $this->message(0, 422, 'Invalid Password!');
        }

        return response()->json([
            'success' => 1,
            'message' => 'You have successfully logged in.',
            'token' => $this->jwt->encode(url('/'), ['user_id' => $user->id]),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        if (! config('services.bok.allow_registration')) {
            return $this->message(0, 404, 'Page Not Found!');
        }

        $fields = ['name', 'email', 'password', 'university', 'bank', 'ip'];
        $values = collect($fields)->mapWithKeys(fn (string $field): array => [$field => trim((string) $request->input($field))]);

        if ($values->contains('')) {
            return $this->message(0, 422, 'Please Fill in all Required Fields!', ['fields' => $fields]);
        }

        if (! filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->message(0, 422, 'Invalid Email Address!');
        }

        if (strlen($values['password']) < 8) {
            return $this->message(0, 422, 'Your password must be at least 8 characters long!');
        }

        if (strlen($values['name']) < 3) {
            return $this->message(0, 422, 'Your name must be at least 3 characters long!');
        }

        if (ApiUser::query()->where('email', $values['email'])->exists()) {
            return $this->message(0, 422, 'This E-mail already in use!');
        }

        ApiUser::query()->create([
            ...$values->all(),
            'name' => htmlspecialchars(strip_tags($values['name'])),
            'password' => Hash::make($values['password']),
        ]);

        return $this->message(1, 201, 'You have successfully registered.');
    }

    public function inquiry(Request $request): JsonResponse
    {
        if (! $this->authenticatedUser($request)) {
            return $this->authFailure($request);
        }

        $studentId = trim((string) $request->input('unique_id'));

        $student = $studentId === ''
            ? null
            : Student::query()->with(['faculty', 'admission'])->has('faculty')->where('university_no', $studentId)->first();

        if (! $student) {
            return response()->json(['status' => '11', 'msg' => 'Invalid client ID']);
        }

        $amount = (string) $this->amountDue($student);

        return response()->json([
            'unique_id' => $studentId,
            'transaction_id' => trim((string) $request->input('transactionId')),
            'fac' => $student->faculty->name_ar,
            'facid' => $student->faculty_code,
            'name' => $student->name_ar,
            'admissionid' => $student->admission_code,
            'admission' => $student->admission?->name_ar,
            'amount' => $amount,
            'minAmount' => $amount,
            'maxAmount' => $amount,
            'status' => '00',
            'statusDescription' => 'Successful inquiry',
        ]);
    }

    public function payment(Request $request): JsonResponse
    {
        if (! $user = $this->authenticatedUser($request)) {
            return $this->authFailure($request);
        }

        $studentId = trim((string) $request->input('unique_id'));
        $rrn = trim((string) $request->input('RRN'));
        $transactionId = trim((string) $request->input('transactionId'));
        $amount = trim((string) $request->input('Amount'));

        if ($studentId === '' || $rrn === '' || $transactionId === '' || ! is_numeric($amount)) {
            return response()->json(['status' => '13', 'msg' => 'system error missing required fields']);
        }

        if (Payment::query()->where('voucher_no', $rrn)->exists()) {
            return response()->json(['status' => '12', 'msg' => 'This payment has already been made']);
        }

        try {
            Payment::query()->create([
                'university_no' => $studentId,
                'amount' => $amount,
                'currency' => 'SDG',
                'bankdate' => now()->toDateString(),
                'bank' => $user->bank ?: 'BOK',
                'branch' => 'BOK_APP',
                'voucher_no' => $rrn,
                'sys_type' => 'BOK_API',
                'transaction_id' => $transactionId,
            ]);
        } catch (QueryException) {
            return response()->json(['status' => '13', 'msg' => 'system error Pay7']);
        }

        return response()->json([
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => '00',
            'statusDescription' => 'Posted Successfully',
        ]);
    }

    public function reconcile(Request $request): JsonResponse
    {
        if (! $this->authenticatedUser($request)) {
            return $this->authFailure($request);
        }

        $rrn = trim((string) $request->input('RRN'));
        $transactionId = trim((string) $request->input('transactionId'));
        $studentId = trim((string) $request->input('unique_id'));
        $amount = trim((string) $request->input('Amount'));

        if ($rrn === '' || $transactionId === '') {
            return response()->json(['status' => '13', 'msg' => 'system error missing required fields']);
        }

        $payment = Payment::query()
            ->where('voucher_no', $rrn)
            ->where('transaction_id', $transactionId)
            ->when($studentId !== '', fn ($query) => $query->where('university_no', $studentId))
            ->when($amount !== '', fn ($query) => $query->where('amount', $amount))
            ->first();

        if (! $payment) {
            return response()->json(['status' => '14', 'msg' => 'Transaction not found']);
        }

        return response()->json([
            'unique_id' => (string) $payment->university_no,
            'transaction_id' => $payment->transaction_id,
            'RRN' => $payment->voucher_no,
            'amount' => (string) $payment->amount,
            'status' => '00',
            'statusDescription' => 'Transaction found',
        ]);
    }

    /**
     * فحص داخلي لقسيمة دفع (PUT) كما في checkPay.php.
     */
    public function checkPayment(Request $request): JsonResponse
    {
        if (! $this->authenticatedUser($request)) {
            return $this->authFailure($request);
        }

        $voucher = trim((string) $request->input('VOUCHER'));

        if ($voucher === '') {
            return $this->message(0, 422, 'Please Fill in all Required Fields!', ['fields' => ['VOUCHERNO']]);
        }

        $payment = Payment::query()->where('voucher_no', $voucher)->first();

        if (! $payment) {
            return $this->message(0, 404, 'Payment Not Found!');
        }

        return response()->json([
            'ID' => $payment->id,
            'STUDENTID' => $payment->university_no,
            'AMT' => $payment->amount,
            'BANKDATE' => $payment->bankdate,
            'VOUCHER' => $payment->voucher_no,
            'BANK' => $payment->bank,
        ]);
    }

    protected function unknownEmailMessage(): string
    {
        return 'Invalid Email Address!T';
    }

    protected function authenticatedUser(Request $request): ?ApiUser
    {
        if (! preg_match('/Bearer\s(\S+)/', (string) $request->header('Authorization'), $matches)) {
            return null;
        }

        $data = $this->jwt->decode($matches[1]);

        return isset($data['user_id']) ? ApiUser::query()->find($data['user_id']) : null;
    }

    protected function authFailure(Request $request): JsonResponse
    {
        $hasBearer = preg_match('/Bearer\s(\S+)/', (string) $request->header('Authorization')) === 1;

        return response()->json($hasBearer
            ? ['status' => '13', 'msg' => 'system error Bearer']
            : ['status' => '33', 'msg' => 'Unauthorized Request']);
    }

    protected function amountDue(Student $student): float
    {
        return match (true) {
            $student->pay1 > 0 => $student->pay1,
            $student->pay2 > 0 => $student->pay2,
            $student->pay3 > 0 => $student->pay3,
            $student->pay4 > 0 => $student->pay4,
            default => $student->reg_fees + $student->fees,
        };
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function message(int $success, int $status, string $message, array $extra = []): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'status' => $status,
            'message' => $message,
            ...$extra,
        ], $status);
    }
}

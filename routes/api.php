<?php

use App\Http\Controllers\Api\PaymentApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/login', function (Request $request) {
    // 1. التحقق من البيانات المدخلة بشكل صارم
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'nullable|string', // اختياري لتسمية التوكن حسب الجهاز
    ]);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        // 2. التحقق من صلاحيات Spatie قبل إصدار التوكن
        if (! $user->hasRole('BankApiAccess')) {
            return response()->json([
                'status' => 'error',
                'message' => 'ليس لديك صلاحية الوصول لنظام المدفوعات',
            ], 403);
        }

        // 3. تحديد وقت انتهاء التوكن (مثلاً بعد 24 ساعة)
        $expiresAt = now()->addHours(24);

        // 4. إنشاء التوكن مع تحديد تاريخ الانتهاء (Sanctum 3.x+)
        $tokenResult = $user->createToken(
            $request->device_name ?? 'web-access',
            ['payments:read', 'payments:write'], // القدرات (Abilities)
            $expiresAt
        );

        // 5. رد احترافي وشامل
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    // 'roles' => $user->getRoleNames(), // جلب أدوار Spatie
                ],
                'access_token' => $tokenResult->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toDateTimeString(),
                'expires_in_seconds' => now()->diffInSeconds($expiresAt),
            ],
        ], 200);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'بيانات الاعتماد غير صحيحة',
    ], 401);
})->middleware('throttle:5,1');

// المسارات المحمية
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/APITIQ/{university_no}', [PaymentApiController::class, 'getStudentFees']);
    Route::post('/APITIQ', [PaymentApiController::class, 'storePayment']);
});

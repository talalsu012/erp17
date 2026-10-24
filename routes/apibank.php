<?php

use App\Http\Controllers\Api\BankApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| واجهة البنوك الأخرى (نسخة من api القديم) على المسار /apibank
|--------------------------------------------------------------------------
| login.php وregister.php مسارا توافق مع الروابط القديمة للبنوك المرتبطة.
*/
Route::controller(BankApiController::class)->group(function () {
    Route::post('login', 'login')->middleware('throttle:5,1');
    Route::post('login.php', 'login')->middleware('throttle:5,1');
    Route::post('register', 'register')->middleware('throttle:5,1');
    Route::post('register.php', 'register')->middleware('throttle:5,1');
    Route::any('APITIQ', 'apitiq');
});

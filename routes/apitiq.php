<?php

use App\Http\Controllers\Api\BokApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| واجهة بنك الخرطوم (apiBOK) على المسار /apitiq بلا بادئة /api
|--------------------------------------------------------------------------
*/
Route::controller(BokApiController::class)->group(function () {
    Route::post('login', 'login')->middleware('throttle:5,1');
    Route::post('register', 'register')->middleware('throttle:5,1');
    Route::post('inquiry', 'inquiry');
    Route::post('payment', 'payment');
    Route::post('reconcile', 'reconcile');
    Route::put('checkPay', 'checkPayment');
});

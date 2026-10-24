<?php

use App\Http\Controllers\Student\AuthenticatedSessionController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\DocumentController;
use App\Http\Controllers\Student\PasswordResetController;
use App\Http\Controllers\Student\PhotoController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\RegisterController;
use App\Http\Middleware\SetStudentLocale;
use App\Http\Middleware\StudentSecurityHeaders;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| بوابة الطالب
|--------------------------------------------------------------------------
| تُخدم على نطاق مستقل إن ضُبط STUDENT_PORTAL_DOMAIN، وإلا تحت المسار /student.
| تُحمَّل قبل routes/web.php لتتقدّم مسارات النطاق على مسار «/» العام.
*/
$portal = Route::name('student.')->middleware([SetStudentLocale::class, StudentSecurityHeaders::class]);

if (config('student.domain')) {
    $portal->domain(config('student.domain'));
} else {
    $portal->prefix(config('student.path'));
}

$portal->group(function (): void {
    Route::get('/', fn () => redirect()->route('student.dashboard'))->name('home');

    Route::middleware('guest:student')->group(function (): void {
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register/verify', [RegisterController::class, 'verify'])
            ->middleware('throttle:student-verify')
            ->name('register.verify');
        Route::post('register', [RegisterController::class, 'store'])
            ->middleware('throttle:student-register')
            ->name('register.store');

        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

        Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
        Route::post('forgot-password', [PasswordResetController::class, 'email'])
            ->middleware('throttle:6,1')
            ->name('password.email');
        Route::get('reset-password/{token}', [PasswordResetController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [PasswordResetController::class, 'store'])->name('password.store');
    });

    Route::middleware('auth:student')->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('photo', [PhotoController::class, 'show'])->name('photo');
        Route::get('documents/{document}', [DocumentController::class, 'show'])->name('documents.show');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('password.update');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});

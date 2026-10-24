<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/student.php',
            __DIR__.'/../routes/web.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('api')->prefix('apitiq')->group(base_path('routes/apitiq.php'));
            Route::middleware('api')->prefix('apibank')->group(base_path('routes/apibank.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request): string => $request->routeIs('student.*')
            ? route('student.login')
            : route('filament.admin.auth.login'));

        $middleware->redirectUsersTo(fn (Request $request): string => $request->routeIs('student.*')
            ? route('student.dashboard')
            : '/admin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*', 'apitiq/*') || $request->expectsJson()
        );

        // سياق موحّد يُلحق بكل خطأ في السجل لتسهيل التتبع
        $exceptions->context(fn (): array => [
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);

        // استجابات JSON موحّدة لـ API: {"status": "error", "message": "..."}
        $exceptions->render(fn (AuthenticationException $e, Request $request): ?Response => $request->is('api/*')
            ? response()->json(['status' => 'error', 'message' => 'غير مصرّح، يرجى تسجيل الدخول.'], 401)
            : null);

        $exceptions->render(fn (ModelNotFoundException|NotFoundHttpException $e, Request $request): ?Response => $request->is('api/*')
            ? response()->json(['status' => 'error', 'message' => 'غير موجود'], 404)
            : null);

        $exceptions->render(fn (TooManyRequestsHttpException $e, Request $request): ?Response => $request->is('api/*') || ($request->expectsJson() && $request->routeIs('student.*'))
            ? response()->json(['status' => 'error', 'message' => 'عدد المحاولات كبير، حاول لاحقاً.'], 429, $e->getHeaders())
            : null);

        $exceptions->render(fn (InvalidSignatureException $e, Request $request): ?Response => $request->is('api/*')
            ? response()->json(['status' => 'error', 'message' => 'الرابط غير صالح أو منتهٍ.'], 403)
            : null);
    })->create();

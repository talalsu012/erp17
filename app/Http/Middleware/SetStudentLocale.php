<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * بوابة الطالب بالعربية افتراضياً (واجهة RTL)، بغض النظر عن لغة النظام العامة.
 */
class SetStudentLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale((string) config('student.locale'));

        return $next($request);
    }
}

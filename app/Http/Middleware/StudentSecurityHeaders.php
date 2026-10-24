<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * ترويسات أمان تُطبَّق على بوابة الطالب فقط (لا تمسّ لوحة Filament).
 * سياسة CSP بنمط المراقبة (Report-Only) إلى أن تُراجَع الأصول المستعملة ثم تُفرض.
 */
class StudentSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Cache-Control', 'no-store, private');
        $devServer = Vite::isRunningHot() ? trim((string) file_get_contents(Vite::hotFile())) : '';
        $devSources = $devServer !== '' ? ' '.$devServer.' '.preg_replace('/^http/', 'ws', $devServer) : '';

        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'".$devSources,
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com".$devSources,
            "font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self'".$devSources,
            "frame-ancestors 'none'",
        ]));

        return $response;
    }
}

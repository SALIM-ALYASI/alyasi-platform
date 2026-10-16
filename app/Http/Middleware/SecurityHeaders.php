<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * إضافة رؤوس حماية أساسية لكل استجابة.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // صفحة صحفي المؤتمر (/cj) تحتاج الميكروفون؛ باقي الموقع يبقى مقفول.
        $microphone = $request->is('cj') ? 'microphone=(self)' : 'microphone=()';
        $response->headers->set('Permissions-Policy', "camera=(), {$microphone}, geolocation=()");

        // نطاق ‎.dev كله HTTPS إجباري بالمتصفحات أصلًا؛ الترويسة تثبّت هذا صراحة.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // ما نكشف إصدار PHP للزوار.
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateManagerBot
{
    /**
     * التحقق من رمز مصادقة بوت إدارة المنصة (تلجرام).
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response|JsonResponse {
        $token = config('services.manager_bot.token');

        if (
            blank($token)
            || ! hash_equals($token, (string) $request->bearerToken())
        ) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح.',
            ], 401);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePublishWebhook
{
    /**
     * التحقق من رمز مصادقة خدمة النشر (webhook التحديثات).
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response|JsonResponse {
        $token = config('services.publish.webhook_token');

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

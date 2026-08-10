<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateArticleBot
{
    /**
     * التحقق من رمز مصادقة بوت المقالات.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response|JsonResponse {
        $token = config('services.article_bot.token');

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

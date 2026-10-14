<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * رمز مستقل للوكيل اللي يقترح أخبار (روتين Grok) -- غير رمز بوت الأخبار،
 * فلو تسرّب يقدر بس يقترح روابط، وما يقدر ينشر ولا يقرأ شي.
 */
class AuthenticateNewsSuggester
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $token = config('services.news_suggester.token');
        $given = (string) ($request->bearerToken() ?: $request->header('X-Api-Key'));

        if (blank($token) || ! hash_equals($token, $given)) {
            return response()->json(['success' => false, 'message' => 'غير مصرح.'], 401);
        }

        return $next($request);
    }
}

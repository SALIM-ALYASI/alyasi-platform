<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * رمز مستقل لكل وكيل يقترح أخبار (Grok، Manus) -- غير رمز بوت الأخبار، فلو
 * تسرّب يقدر بس يقترح روابط، وما يقدر ينشر ولا يقرأ شي. اسم الوكيل يوصل
 * للكنترولر عبر attribute "news_suggester".
 */
class AuthenticateNewsSuggester
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $given = (string) ($request->bearerToken() ?: $request->header('X-Api-Key'));

        foreach ((array) config('services.news_suggester.tokens') as $agent => $token) {
            if (filled($token) && filled($given) && hash_equals((string) $token, $given)) {
                $request->attributes->set('news_suggester', $agent);

                return $next($request);
            }
        }

        return response()->json(['success' => false, 'message' => 'غير مصرح.'], 401);
    }
}

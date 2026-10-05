<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateConferenceJournalist
{
    /**
     * صحفي المؤتمر: "user" لصفحة التسجيل، "relay" لمرحّل سيرفر البيت، "agent" لبوت الماك.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $role = 'user'
    ): Response|JsonResponse {
        $token = config("services.conference_journalist.{$role}_token");

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

<?php

namespace App\Http\Middleware;

use App\Jobs\ResolveVisitCountry;
use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    /**
     * أجزاء user agent الشائعة لبرامج الزحف/الروبوتات، لاستبعادها من الإحصائيات.
     */
    private const BOT_SIGNATURES = [
        'bot', 'spider', 'crawl', 'slurp', 'facebookexternalhit',
        'whatsapp', 'telegrambot', 'preview', 'headless', 'curl', 'python-requests',
    ];

    /**
     * تسجيل زيارات الصفحات العامة (بدون لوحة التحكم أو API) وتحديد
     * بلد الزائر بشكل غير متزامن حتى لا يبطئ الاستجابة.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 200 && $this->shouldTrack($request)) {
            $visit = PageVisit::query()->create([
                'path' => '/'.ltrim($request->path(), '/'),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);

            ResolveVisitCountry::dispatch($visit->id);
        }

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->is('admin*', 'api*', 'locale/*', 'up')) {
            return false;
        }

        $userAgent = mb_strtolower((string) $request->userAgent());

        if ($userAgent === '') {
            return false;
        }

        foreach (self::BOT_SIGNATURES as $signature) {
            if (str_contains($userAgent, $signature)) {
                return false;
            }
        }

        return true;
    }
}

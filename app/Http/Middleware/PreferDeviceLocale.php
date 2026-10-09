<?php

namespace App\Http\Middleware;

use App\View\Composers\ArticleLocaleLinksComposer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يفتح الموقع بلغة الزائر: اختياره المحفوظ (كوكي alyasi_lang) أولًا، وإلا
 * لغة جهازه (Accept-Language). لو الصفحة المطلوبة بلغة ثانية ولها نسخة
 * فعلية باللغة المفضلة يتحوّل لها.
 *
 * الزائر حر يغيّر: أزرار AR/EN بالرأس تحفظ اختياره بنفس الكوكي، فيصير
 * هو المرجع بدل لغة الجهاز. محركات البحث وبوتات المعاينة ما تتحوّل أبدًا
 * عشان تظل تشوف النسختين كما هي.
 */
class PreferDeviceLocale
{
    public const COOKIE = 'alyasi_lang';

    private const BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|facebot|embedly|preview|whatsapp|telegram|twitter|linkedin|discord|slack|skype|pinterest|lighthouse|headless|curl|wget|python|http-?client|okhttp|java\/|symfony/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return $response;
        }

        if ($response->getStatusCode() !== 200 || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        if (preg_match(self::BOT_PATTERN, (string) $request->userAgent())) {
            return $response;
        }

        $preferred = $this->preferredLocale($request);

        if ($preferred === null || $preferred === app()->getLocale()) {
            return $response;
        }

        // نحوّل فقط لصفحة مقابلة فعلية -- مو لـ /locale/{x} ولا لمحتوى غير مترجم.
        $target = app(ArticleLocaleLinksComposer::class)->links()[$preferred] ?? null;

        if ($target === null || str_contains($target, '/locale/')) {
            return $response;
        }

        if ($query = $request->getQueryString()) {
            $target .= (str_contains($target, '?') ? '&' : '?').$query;
        }

        return redirect()->to($target, 302)->withHeaders([
            'Cache-Control' => 'private, no-store',
            'Vary' => 'Accept-Language, Cookie',
        ]);
    }

    private function preferredLocale(Request $request): ?string
    {
        $chosen = $request->cookies->get(self::COOKIE);

        if (in_array($chosen, SetLocale::SUPPORTED_LOCALES, true)) {
            return $chosen;
        }

        // أول لغة بقائمة الجهاز: عربي = ar، أي لغة ثانية = en.
        $first = $request->getLanguages()[0] ?? null;

        if ($first === null || $first === '*') {
            return null;
        }

        return str_starts_with(strtolower($first), 'ar') ? 'ar' : 'en';
    }
}

<?php

namespace App\Support\Analytics;

use Illuminate\Http\Request;

/**
 * يصنّف الطلب بدون ما يحذف شي:
 *  - owner:    جهاز سالم (كوكي الاستبعاد)
 *  - internal: فحوصات السيرفر نفسه (Request::create بدون متصفح)
 *  - bot:      بوت يعلن عن نفسه بالـ user agent
 *  - suspect:  متصفح «عادي» بس فيه علامات أتمتة (تتضاف لاحقًا: datacenter/proxy)
 *  - human:    بشري مرجّح -- مرجّح مب مؤكد
 */
final class VisitClassifier
{
    public const OWNER = 'owner';

    public const INTERNAL = 'internal';

    public const BOT = 'bot';

    public const SUSPECT = 'suspect';

    public const HUMAN = 'human';

    private const BOT_SIGNATURES = [
        'bot', 'spider', 'crawl', 'slurp', 'facebookexternalhit',
        'whatsapp', 'telegrambot', 'preview', 'headless', 'curl', 'python-requests',
        'wget', 'go-http-client', 'axios', 'node-fetch', 'okhttp', 'java/', 'libwww',
        'scrapy', 'httpclient', 'lighthouse', 'pingdom', 'uptime',
    ];

    /**
     * هويات متصفح قديمة تتكرر عند برامج سحب المحتوى (iOS 13.2.3 من 2019).
     */
    private const KNOWN_FAKE_AGENTS = [
        'iPhone OS 13_2_3',
    ];

    /**
     * @return array{0: string, 1: list<string>} [التصنيف، الأسباب]
     */
    public static function classify(Request $request, bool $isPageView = true): array
    {
        if ($request->cookie(config('analytics.owner_cookie')) === '1') {
            return [self::OWNER, ['owner_cookie']];
        }

        $agent = (string) $request->userAgent();
        $lower = mb_strtolower($agent);

        if ($lower === 'symfony') {
            return [self::INTERNAL, ['server_check']];
        }

        if ($lower === '') {
            return [self::BOT, ['no_user_agent']];
        }

        foreach (self::BOT_SIGNATURES as $signature) {
            if (str_contains($lower, $signature)) {
                return [self::BOT, ['ua:'.$signature]];
            }
        }

        $reasons = [];

        foreach (self::KNOWN_FAKE_AGENTS as $fake) {
            if (str_contains($agent, $fake)) {
                $reasons[] = 'known_fake_ua';
            }
        }

        if (self::isOutdatedBrowser($agent)) {
            $reasons[] = 'outdated_browser';
        }

        // المتصفحات الحقيقية ترسل دايمًا لغة الجهاز.
        if (blank($request->header('Accept-Language'))) {
            $reasons[] = 'no_accept_language';
        }

        // فتح صفحة بمتصفح يطلب HTML؛ طلبات الـ beacon ما ينطبق عليها هذا.
        if ($isPageView && ! str_contains((string) $request->header('Accept'), 'text/html')) {
            $reasons[] = 'non_browser_accept';
        }

        return [$reasons ? self::SUSPECT : self::HUMAN, $reasons];
    }

    /**
     * أقدم من سنتين تقريبًا (أكتوبر 2026: Chrome 141، Safari 27).
     */
    private static function isOutdatedBrowser(string $agent): bool
    {
        if (preg_match('#(?:Chrome|CriOS)/(\d+)#', $agent, $m)) {
            return (int) $m[1] < 120;
        }

        if (preg_match('#Firefox/(\d+)#', $agent, $m)) {
            return (int) $m[1] < 115;
        }

        if (preg_match('#Version/(\d+)[.\d]* (?:Mobile/\S+ )?Safari#', $agent, $m)) {
            return (int) $m[1] < 16;
        }

        return false;
    }
}

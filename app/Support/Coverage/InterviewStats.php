<?php

namespace App\Support\Coverage;

use App\Models\PageEvent;
use App\Models\PageVisit;
use Illuminate\Support\Facades\Cache;

/**
 * أرقام صفحة المقابلة (تنعرض تحتها): الزيارات ومرات تشغيل الصوت والاستماع
 * للنهاية -- زيارات حقيقية بس (human)، بدون البوتات وأجهزة سالم والفحوصات.
 * تنحسب للصفحتين (العربية والإنجليزية) مع بعض، وتنحفظ 10 دقائق.
 */
final class InterviewStats
{
    /** @return array{visits: int, plays: int, completes: int} */
    public static function for(string $slug): array
    {
        return Cache::remember('interview-stats:'.$slug, now()->addMinutes(10), function () use ($slug) {
            $paths = [
                '/events/cyberx-oman-2026/interviews/'.$slug,
                '/en/events/cyberx-oman-2026/interviews/'.$slug,
            ];

            $events = fn (string $type) => PageEvent::query()
                ->whereIn('path', $paths)
                ->where('type', $type)
                ->where('traffic_class', 'human')
                ->count();

            return [
                'visits' => PageVisit::query()->whereIn('path', $paths)->where('traffic_class', 'human')->count(),
                'plays' => $events('audio_play'),
                'completes' => $events('audio_complete'),
            ];
        });
    }
}

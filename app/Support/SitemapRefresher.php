<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * يبقي sitemap.xml محدّثًا تلقائيًا: أي حفظ/حذف لمحتوى عام (مقال، خبر من
 * البوت، خدمة، عمل، مؤتمر، منشور مجتمع) يعلّم الخريطة «قديمة»، وتنبني من
 * جديد بعد ما ينرسل الرد (بدون ما يبطّئ الصفحة)، ومرة بالدقيقتين بالكثير
 * عشان دفعات الأخبار. ولو جاها قوقل وهي قديمة، تنبني قبل ما تنرسل له.
 */
class SitemapRefresher
{
    private const DIRTY = 'sitemap:dirty';

    private const LOCK = 'sitemap:regen-lock';

    private static bool $scheduled = false;

    public static function markDirty(): void
    {
        Cache::forever(self::DIRTY, now()->timestamp);

        if (self::$scheduled) {
            return;
        }

        self::$scheduled = true;
        app()->terminating(fn () => self::refreshIfDirty());
    }

    public static function refreshIfDirty(): void
    {
        if (! Cache::has(self::DIRTY) || ! Cache::add(self::LOCK, true, 120)) {
            return;
        }

        try {
            Cache::forget(self::DIRTY);
            Artisan::call('sitemap:generate');
        } catch (\Throwable $e) {
            Cache::forever(self::DIRTY, now()->timestamp);
            Log::warning('تعذّر تحديث sitemap تلقائيًا', ['error' => $e->getMessage()]);
        }
    }
}

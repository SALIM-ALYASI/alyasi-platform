<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use App\Models\Setting;
use App\Support\WhatsAppAlerts;
use Illuminate\Console\Command;

/**
 * يتحقق من إن بوت الأخبار (news-bot-v2) ما زال ينشر -- مكمّل لـ
 * home-server:check: ذاك يتحقق إن n8n/السيرفر المنزلي متاح عمومًا، بينما هذا
 * يكتشف تحديدًا لو news-bot-v2 تعطل داخليًا (حاوية شغالة لكن عالقة/منهارة)
 * رغم إن باقي السيرفر سليم. نمط النشر المعتاد دفعتين يوميًا تقريبًا
 * (~06:30 و~19:40)، فعتبة أطول من أطول فجوة طبيعية بينهما.
 */
class CheckNewsBotStaleness extends Command
{
    protected $signature = 'news:check-staleness';

    protected $description = 'ينبّه عبر واتساب لو ما فيه خبر منشور جديد منذ فترة غير طبيعية';

    private const STALE_HOURS = 18;

    public function handle(): int
    {
        $latest = NewsArticle::published()->latest('published_at')->first();

        $isStale = ! $latest || $latest->published_at->lt(now()->subHours(self::STALE_HOURS));
        $wasAlerted = Setting::get('news_bot_stale_alerted') === '1';

        if ($isStale) {
            if (! $wasAlerted) {
                $lastPublishedAt = $latest?->published_at?->toDateTimeString() ?? 'لا يوجد أخبار منشورة إطلاقًا';

                WhatsAppAlerts::send(
                    'بوت الأخبار ما نشر أي خبر جديد منذ أكثر من '.self::STALE_HOURS." ساعة.\nآخر خبر منشور: {$lastPublishedAt}"
                );

                Setting::set('news_bot_stale_alerted', '1');
            }

            return self::SUCCESS;
        }

        if ($wasAlerted) {
            WhatsAppAlerts::send('بوت الأخبار رجع ينشر بشكل طبيعي.');
            Setting::set('news_bot_stale_alerted', '0');
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;

/**
 * يشيل إشارات مصادر بوت الأخبار اللي تسرّبت للنص المنشور: (fact_1) أو
 * [fact_2] أو (fact_1, fact_3). البوت صار يشيلها قبل النشر (17 أكتوبر 2026)؛
 * هذا لتنظيف الأخبار القديمة.
 */
class StripFactMarkers extends Command
{
    protected $signature = 'news:strip-fact-markers {--dry-run : عرض التغييرات بدون حفظ}';

    protected $description = 'تنظيف إشارات (fact_N) المتسربة من نصوص الأخبار المنشورة.';

    private const FIELDS = ['excerpt_ar', 'excerpt_en', 'content_ar', 'content_en', 'seo_description_ar', 'seo_description_en'];

    public static function clean(?string $text): ?string
    {
        if ($text === null || ! preg_match('/fact_\d+/', $text)) {
            return $text;
        }

        $text = preg_replace('/\s*[\[(]\s*fact_\d+(?:\s*[,،]\s*fact_\d+)*\s*[\])]/u', '', $text);
        $text = preg_replace('/\s*,\s*\./u', '.', $text);

        return preg_replace('/[ \t]+([.,،؛:!?])/u', '$1', $text);
    }

    public function handle(): int
    {
        $changed = 0;

        NewsArticle::query()->withoutGlobalScopes()->orderBy('id')->each(function (NewsArticle $article) use (&$changed): void {
            $updates = [];
            foreach (self::FIELDS as $field) {
                $value = $article->getAttribute($field);
                $cleaned = self::clean($value);
                if ($cleaned !== $value) {
                    $updates[$field] = $cleaned;
                }
            }

            if (! $updates) {
                return;
            }

            $changed++;
            $this->line("#{$article->id} ".implode(',', array_keys($updates)).' | '.mb_substr((string) $article->title_ar, 0, 60));

            if (! $this->option('dry-run')) {
                // بدون أحداث الحفظ: تنظيف نص فقط، ما نغيّر updated_at ولا نعيد حساب شي.
                NewsArticle::withoutEvents(fn () => $article->forceFill($updates)->saveQuietly());
            }
        });

        $this->info(($this->option('dry-run') ? 'سيُنظَّف: ' : 'نُظِّف: ').$changed.' خبر');

        return self::SUCCESS;
    }
}

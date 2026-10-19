<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;

/**
 * يشيل إشارات مصادر بوت الأخبار اللي تسرّبت للنص المنشور: (fact_1) أو
 * [fact_2] أو (fact_1, fact_3)، والمقصوصة بآخر نص مختصر «(fact_1, fact_2»،
 * والبدون أقواس «fact_1». الخبر نفسه يشيلها عند الحفظ (NewsArticle::booted)؛
 * هذا لتنظيف المحفوظ قبل كذا.
 */
class StripFactMarkers extends Command
{
    protected $signature = 'news:strip-fact-markers {--dry-run : عرض التغييرات بدون حفظ}';

    protected $description = 'تنظيف إشارات (fact_N) المتسربة من نصوص الأخبار المنشورة.';

    public const FIELDS = [
        'title_ar', 'title_en', 'excerpt_ar', 'excerpt_en', 'content_ar', 'content_en',
        'seo_title_ar', 'seo_title_en', 'seo_description_ar', 'seo_description_en',
        'analysis_title_ar', 'analysis_ar', 'analysis_regional_angle_ar',
    ];

    public static function clean(?string $text): ?string
    {
        if ($text === null || ! preg_match('/fact_/i', $text)) {
            return $text;
        }

        // (fact_1) أو [fact_2] أو (fact_1, fact_3)
        $text = preg_replace('/\s*[\[(]\s*fact_\d+(?:\s*[,،]\s*fact_\d+)*\s*[\])]/iu', '', $text);
        // إشارة انقصّت مع اختصار النص بآخره: «(fact_1, fact_2» أو «(fact_1...» -- النص نفسه
        // مقصوص هنا، فينتهي بـ«…».
        $text = preg_replace('/\s*[\[(]\s*fact_[\d.…]*(?:\s*[,،]\s*(?:fact_?[\d.…]*)?)*\s*(?:\.{2,}|…)?\s*$/iu', '…', $text);
        // إشارة بدون أقواس: «... attacks fact_1. To ...»
        $text = preg_replace('/\s*\bfact_\d+\b/iu', '', $text);
        $text = preg_replace('/\s*,\s*\./u', '.', $text);

        return preg_replace('/[ \t]+([.,،؛:!?])/u', '$1', $text);
    }

    public function handle(): int
    {
        $changed = 0;

        NewsArticle::query()->withoutGlobalScopes()->withTrashed()->orderBy('id')->each(function (NewsArticle $article) use (&$changed): void {
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
                NewsArticle::query()->withoutGlobalScopes()->withTrashed()->whereKey($article->id)->toBase()->update($updates);
            }
        });

        $this->info(($this->option('dry-run') ? 'سيُنظَّف: ' : 'نُظِّف: ').$changed.' خبر');

        return self::SUCCESS;
    }
}

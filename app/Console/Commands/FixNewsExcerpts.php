<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use App\Support\Text\Excerpt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * يعيد بناء مقتطفات الأخبار المقصوصة بنص الكلمة (الطريقة القديمة: أول 200 حرف
 * + «...») من نص الخبر نفسه، بجمل كاملة. نسخة احتياطية قبل أي تعديل.
 */
class FixNewsExcerpts extends Command
{
    protected $signature = 'news:fix-excerpts {--dry-run : عرض التغييرات بدون حفظ}';

    protected $description = 'إعادة بناء مقتطفات الأخبار المقصوصة بجمل كاملة.';

    public function handle(): int
    {
        $backup = [];
        $changed = 0;

        NewsArticle::query()->withoutGlobalScopes()->withTrashed()->orderBy('id')->each(function (NewsArticle $article) use (&$backup, &$changed): void {
            $updates = [];

            foreach (['ar', 'en'] as $locale) {
                $content = $article->getAttribute('content_'.$locale);

                if (blank($content)) {
                    continue;
                }

                if (Excerpt::looksCut($article->getAttribute('excerpt_'.$locale))) {
                    $updates['excerpt_'.$locale] = Excerpt::make($content, 220);
                }

                if (Excerpt::looksCut($article->getAttribute('seo_description_'.$locale))) {
                    $updates['seo_description_'.$locale] = Excerpt::make($content, 160);
                }
            }

            $updates = array_filter($updates, fn ($value, $field) => $value !== $article->getAttribute($field), ARRAY_FILTER_USE_BOTH);

            if (! $updates) {
                return;
            }

            $backup[$article->id] = array_intersect_key($article->getAttributes(), $updates);
            $changed++;

            if ($this->option('dry-run')) {
                $this->line("#{$article->id} ".implode(',', array_keys($updates)).' | '.mb_substr((string) ($updates['excerpt_ar'] ?? ''), -60));

                return;
            }

            // تنظيف نص فقط: بدون أحداث الحفظ وبدون تغيير updated_at.
            NewsArticle::withoutEvents(fn () => $article->forceFill($updates)->saveQuietly());
        });

        if (! $this->option('dry-run') && $backup) {
            $path = 'backups/news-excerpts-'.now()->format('YmdHis').'.json';
            Storage::disk('local')->put($path, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->line('نسخة احتياطية: storage/app/private/'.$path);
        }

        $this->info(($this->option('dry-run') ? 'سيُصلح: ' : 'أُصلح: ').$changed.' خبر');

        return self::SUCCESS;
    }
}

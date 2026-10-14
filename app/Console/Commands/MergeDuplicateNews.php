<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * دمج خبر مكرر في خبر أصلي: نفس القصة انتشرت مرتين من مصدرين بعنوانين مختلفين.
 * المكرر ينحذف (soft delete) ويحفظ merged_into_id، فكل روابطه تتحوّل 301 للأصلي،
 * ومشاهداته تنضاف للأصلي.
 *
 *   php artisan news:merge 393 387          # 393 مكرر لـ 387
 *   php artisan news:merge 393 387 --dry-run
 */
class MergeDuplicateNews extends Command
{
    protected $signature = 'news:merge
        {duplicate : رقم الخبر المكرر (ينحذف ويتحوّل)}
        {original : رقم الخبر الأصلي (يبقى)}
        {--dry-run : اعرض بدون تنفيذ}';

    protected $description = 'دمج خبر مكرر في خبر أصلي مع تحويل روابطه 301';

    public function handle(): int
    {
        $duplicateId = (int) $this->argument('duplicate');
        $originalId = (int) $this->argument('original');

        if ($duplicateId === $originalId) {
            $this->error('الخبر ما ينضم لنفسه.');

            return self::FAILURE;
        }

        $duplicate = NewsArticle::query()->find($duplicateId);
        $original = NewsArticle::query()->find($originalId);

        if (! $duplicate || ! $original) {
            $this->error('واحد من الخبرين غير موجود (أو محذوف مسبقًا).');

            return self::FAILURE;
        }

        if ($original->merged_into_id) {
            $this->error("الخبر الأصلي #{$originalId} نفسه مدموج في #{$original->merged_into_id} -- ادمج فيه مباشرة.");

            return self::FAILURE;
        }

        $this->line("المكرر:  #{$duplicate->id} {$duplicate->title_ar} ({$duplicate->views_count} مشاهدة)");
        $this->line("الأصلي: #{$original->id} {$original->title_ar} ({$original->views_count} مشاهدة)");

        if ($this->option('dry-run')) {
            $this->info('تجربة فقط -- ما تغيّر شي.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($duplicate, $original): void {
            // أي خبر كان مدموج في المكرر يتحوّل للأصلي مباشرة (ما نخلي سلسلة تحويلات).
            NewsArticle::withTrashed()
                ->where('merged_into_id', $duplicate->id)
                ->update(['merged_into_id' => $original->id]);

            $original->increment('views_count', (int) $duplicate->views_count);

            $duplicate->forceFill(['merged_into_id' => $original->id])->save();
            $duplicate->delete();
        });

        $this->info("تم: #{$duplicate->id} صار يحوّل إلى #{$original->id}.");

        return self::SUCCESS;
    }
}

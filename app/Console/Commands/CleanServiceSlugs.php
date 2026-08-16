<?php

namespace App\Console\Commands;

use App\Support\ServiceSlugCleaner;
use Illuminate\Console\Command;

/**
 * نسخة CLI من نفس أداة تنظيف روابط الخدمات المتوفرة بلوحة التحكم
 * (لوحة التحكم > روابط الخدمات) — نفس المنطق عبر ServiceSlugCleaner.
 * تبقى هنا لمن يملك وصول SSH ويفضّل التشغيل من الطرفية.
 */
class CleanServiceSlugs extends Command
{
    protected $signature = 'services:clean-slugs {--dry-run : معاينة التغييرات بدون كتابتها فعليًا}';

    protected $description = 'تصحيح روابط الخدمات المرمّزة بالعربي (قبل قاعدة CleanSlug) إلى روابط إنجليزية نظيفة، مع تحويل 301 للرابط القديم';

    public function handle(ServiceSlugCleaner $cleaner): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $rows = $cleaner->preview();

        if ($rows->isEmpty()) {
            $this->info('لا يوجد أي رابط خدمة يحتاج تصحيح.');

            return self::SUCCESS;
        }

        foreach ($rows as $row) {
            if ($row['fixable']) {
                $this->line("خدمة #{$row['service_id']} [{$row['locale']}]: {$row['old_slug']} → {$row['new_slug']}");
            } else {
                $this->warn("تخطّي: خدمة #{$row['service_id']} ({$row['service_title_ar']}) — بلا عنوان إنجليزي، يحتاج تعديل يدوي عبر لوحة التحكم.");
            }
        }

        $this->newLine();

        if ($dryRun) {
            $fixableCount = $rows->where('fixable', true)->count();
            $skippedCount = $rows->where('fixable', false)->count();
            $this->info("[معاينة فقط — بدون كتابة] سيتم إصلاح {$fixableCount} رابط، وتخطّي {$skippedCount}.");

            return self::SUCCESS;
        }

        $result = $cleaner->execute();

        $this->info("تم إصلاح {$result['fixed']} رابط، وتخطّي {$result['skipped']} يحتاج تدخل يدوي.");

        return self::SUCCESS;
    }
}

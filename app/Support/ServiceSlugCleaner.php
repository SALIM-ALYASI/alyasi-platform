<?php

namespace App\Support;

use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * يصحّح روابط الخدمات المخزّنة قبل قاعدة CleanSlug (نص عربي خام يظهر
 * مرمّزًا %D8%... بالمشاركة) إلى slug إنجليزي نظيف، مبني على title_en
 * الخدمة نفسها — لا تخمين أو ترجمة آلية. الرابط القديم يُحفظ كتحويل
 * 301 عبر PermalinkRedirect (نفس آلية admin/services الحالية) حتى لا
 * تنكسر أي روابط سبق مشاركتها أو فهرستها.
 *
 * منطق واحد مشترك يستخدمه كل من أمر Artisan (services:clean-slugs)
 * وصفحة لوحة التحكم، تفاديًا لازدواج عملية تعدّل قاعدة البيانات.
 */
class ServiceSlugCleaner
{
    /**
     * معاينة فقط — بدون أي كتابة لقاعدة البيانات.
     *
     * @return Collection<int, array{
     *     permalink_id: int,
     *     service_id: int,
     *     service_title_ar: string,
     *     locale: string,
     *     old_slug: string,
     *     new_slug: ?string,
     *     fixable: bool,
     * }>
     */
    public function preview(): Collection
    {
        return $this->dirtyPermalinks()->map(function (Permalink $permalink) {
            $service = $permalink->linkable;
            $fixable = $service instanceof Service && filled($service->title_en);

            return [
                'permalink_id' => $permalink->id,
                'service_id' => $service?->id,
                'service_title_ar' => $service?->title_ar ?? '(خدمة محذوفة)',
                'locale' => $permalink->locale,
                'old_slug' => $permalink->slug,
                'new_slug' => $fixable
                    ? $this->generateUniqueSlug($service->title_en, $permalink->locale, $permalink->id)
                    : null,
                'fixable' => $fixable,
            ];
        })->values();
    }

    /**
     * التنفيذ الفعلي — يكتب كل التغييرات القابلة للإصلاح فقط.
     *
     * @return array{fixed: int, skipped: int}
     */
    public function execute(): array
    {
        $rows = $this->preview();

        $fixed = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (! $row['fixable']) {
                $skipped++;

                continue;
            }

            $permalink = Permalink::find($row['permalink_id']);

            if (! $permalink) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($permalink, $row): void {
                PermalinkRedirect::query()
                    ->where('locale', $row['locale'])
                    ->where('old_slug', $row['old_slug'])
                    ->delete();

                PermalinkRedirect::query()->create([
                    'permalink_id' => $permalink->id,
                    'locale' => $row['locale'],
                    'old_slug' => $row['old_slug'],
                ]);

                $permalink->update(['slug' => $row['new_slug']]);
            });

            $fixed++;
        }

        if ($fixed > 0) {
            Artisan::call('sitemap:generate');
        }

        return ['fixed' => $fixed, 'skipped' => $skipped];
    }

    /**
     * @return Collection<int, Permalink>
     */
    private function dirtyPermalinks(): Collection
    {
        return Permalink::query()
            ->where('linkable_type', 'service')
            ->with('linkable')
            ->get()
            ->filter(
                fn (Permalink $permalink) => ! preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $permalink->slug)
            )
            ->values();
    }

    private function generateUniqueSlug(string $source, string $locale, int $excludePermalinkId): string
    {
        $baseSlug = Str::slug($source);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Permalink::query()
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->where('id', '!=', $excludePermalinkId)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

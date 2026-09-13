<?php

namespace App\Console\Commands;

use App\Models\EventEdition;
use App\Services\AppleStorePricingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SyncAppleStorePrices extends Command
{
    protected $signature = 'apple:sync-prices
        {--event=apple-event-2026 : رابط/slug نسخة مؤتمر Apple المطلوب تحديثها}
        {--source=* : مصدر محدد للمزامنة (يمكن تكراره)}
        {--dry-run : جلب وفحص الأسعار بدون حفظها في قاعدة البيانات}';

    protected $description = 'Sync Apple UAE Store prices into the Apple event pricing table';

    public function handle(AppleStorePricingService $pricing): int
    {
        $slug = trim((string) $this->option('event'));

        $edition = EventEdition::query()
            ->whereHas('permalinks', fn ($query) => $query->where('slug', $slug))
            ->with('permalinks')
            ->first();

        if (! $edition) {
            $this->error("لم أجد نسخة مؤتمر بالرابط: {$slug}");

            return self::FAILURE;
        }

        $availableSources = $pricing->sources();
        $requestedSources = array_values(array_filter(array_map(
            fn ($value) => trim((string) $value),
            (array) $this->option('source'),
        )));

        $sourceKeys = $requestedSources ?: array_keys($availableSources);
        $unknown = array_values(array_diff($sourceKeys, array_keys($availableSources)));

        if ($unknown !== []) {
            $this->error('مصدر غير معروف: '.implode(', ', $unknown));
            $this->line('المصادر المتاحة: '.implode(', ', array_keys($availableSources)));

            return self::FAILURE;
        }

        /** @var Collection<int, array<string, mixed>> $current */
        $current = collect($edition->pricing_table ?? [])
            ->filter(fn ($row) => is_array($row))
            ->values();

        $summary = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($sourceKeys as $sourceKey) {
            try {
                $freshRows = $pricing->fetch($sourceKey);
                $current = $this->replaceSourceRows($current, $freshRows, $sourceKey);
                $successCount++;

                $summary[] = [
                    $sourceKey,
                    (string) count($freshRows),
                    'OK',
                ];
            } catch (Throwable $exception) {
                $failureCount++;
                $summary[] = [$sourceKey, '0', 'FAILED'];

                // لا نحذف الأسعار القديمة لهذا المصدر عند الفشل.
                Log::warning('Apple Store price sync source failed', [
                    'source' => $sourceKey,
                    'event_edition_id' => $edition->id,
                    'message' => $exception->getMessage(),
                ]);

                $this->warn("فشل {$sourceKey}: {$exception->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(['المصدر', 'عدد الصفوف', 'الحالة'], $summary);

        if ($successCount === 0) {
            $this->error('لم ينجح أي مصدر. لم يتم تعديل قاعدة البيانات.');

            return self::FAILURE;
        }

        if ((bool) $this->option('dry-run')) {
            $this->info('Dry run: تم الجلب والتحقق فقط، بدون حفظ أي تغيير.');

            return $failureCount > 0 ? self::FAILURE : self::SUCCESS;
        }

        $edition->pricing_table = $current->values()->all();
        $edition->save();

        $this->info(sprintf(
            'تم تحديث الأسعار في قاعدة البيانات: %d صفًا حاليًا.',
            $current->count(),
        ));

        if ($failureCount > 0) {
            $this->warn('بعض المصادر فشلت، وتم الاحتفاظ بآخر بيانات محفوظة لها.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * يستبدل صفوف المصدر الناجح فقط، ويحافظ على أي أسعار يدوية لمنتجات أخرى.
     * كما يزيل الصفوف اليدوية القديمة لنفس عائلة المنتج حتى لا تظهر تكرارات
     * بعد أول مزامنة آلية.
     *
     * @param  Collection<int, array<string, mixed>>  $current
     * @param  array<int, array<string, mixed>>  $freshRows
     * @return Collection<int, array<string, mixed>>
     */
    private function replaceSourceRows(Collection $current, array $freshRows, string $sourceKey): Collection
    {
        $families = collect($freshRows)
            ->map(fn (array $row) => $this->normalizeProductFamily(
                (string) ($row['product_en'] ?? $row['product_ar'] ?? ''),
            ))
            ->filter()
            ->unique()
            ->values();

        $kept = $current->reject(function (array $row) use ($sourceKey, $families) {
            if (($row['source'] ?? null) === AppleStorePricingService::SOURCE
                && ($row['source_key'] ?? null) === $sourceKey) {
                return true;
            }

            $name = $this->normalizeProductFamily(
                (string) ($row['product_en'] ?? $row['product_ar'] ?? ''),
            );

            if ($name === '') {
                return false;
            }

            return $families->contains(function (string $family) use ($name) {
                return $name === $family
                    || Str::startsWith($name, $family.' ')
                    || Str::startsWith($family, $name.' ');
            });
        });

        return $kept->concat($freshRows)->values();
    }

    private function normalizeProductFamily(string $value): string
    {
        $value = strtr($value, [
            '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5',
            '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٠' => '0',
        ]);
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+(?:[0-9]+(?:\.[0-9]+)?)\s*(?:gb|tb)\b/ui', '', $value) ?? $value;
        $value = preg_replace('/\s+[0-9]+\s*(?:جيجابايت|تيرابايت)$/u', '', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}

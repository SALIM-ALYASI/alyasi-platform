<?php

namespace App\Console\Commands;

use App\Models\EventEdition;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VerifyAppleEventDates extends Command
{
    /**
     * يفحص تواريخ الطلب المسبق والتوفر المحفوظة مقابل مصدر Apple الرسمي.
     * الفحص للقراءة فقط ولا يعدّل قاعدة البيانات تلقائيًا.
     */
    protected $signature = 'events:verify-apple-dates
        {--year=2026 : سنة نسخة المؤتمر}
        {--product=iPhone Duo : اسم المنتج كما هو محفوظ في label_en}';

    protected $description = 'Verify Apple product pre-order and availability dates against an official Apple source';

    /**
     * مصادر Apple الرسمية المدعومة حاليًا.
     * يمكن توسيع القائمة لاحقًا لبقية المنتجات والمؤتمرات.
     */
    private const OFFICIAL_SOURCES = [
        'iphone duo' => 'https://www.apple.com/newsroom/2026/09/apple-unveils-iphone-duo/',
    ];

    public function handle(): int
    {
        $year = (int) $this->option('year');
        $requestedProduct = trim((string) $this->option('product'));
        $productKey = Str::lower($requestedProduct);

        $edition = EventEdition::query()
            ->where('year', $year)
            ->whereHas('event', fn ($query) => $query->where('slug', 'apple'))
            ->first();

        if (! $edition) {
            $this->error("لم أجد نسخة Apple لسنة {$year} في قاعدة البيانات.");
            return self::FAILURE;
        }

        $product = collect($edition->announcements ?? [])->first(function (array $item) use ($productKey) {
            return Str::lower(trim((string) ($item['label_en'] ?? ''))) === $productKey;
        });

        if (! $product) {
            $this->error("لم أجد المنتج '{$requestedProduct}' داخل announcements.");
            return self::FAILURE;
        }

        $sourceUrl = $product['source_url'] ?? self::OFFICIAL_SOURCES[$productKey] ?? null;

        if (! $sourceUrl || ! str_contains(parse_url($sourceUrl, PHP_URL_HOST) ?? '', 'apple.com')) {
            $this->error('لا يوجد مصدر Apple رسمي معروف لهذا المنتج.');
            return self::FAILURE;
        }

        $this->line("المصدر الرسمي: {$sourceUrl}");
        $this->line('جاري فحص صفحة Apple...');

        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->withHeaders([
                    'User-Agent' => 'ALYASI Event Verifier/1.0',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($sourceUrl);
        } catch (\Throwable $exception) {
            $this->error('تعذر الاتصال بمصدر Apple: '.$exception->getMessage());
            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error('مصدر Apple رجع HTTP '.$response->status().'.');
            return self::FAILURE;
        }

        $text = html_entity_decode(strip_tags($response->body()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        [$officialPreorder, $officialAvailable] = $this->extractDates($text, $year);

        if (! $officialPreorder || ! $officialAvailable) {
            $this->error('تم فتح صفحة Apple، لكن لم أستطع استخراج التاريخين من النص. لم يتم تعديل أي بيانات.');
            return self::FAILURE;
        }

        $storedPreorder = $this->normalizeStoredDate($product['preorder_at'] ?? null);
        $storedAvailable = $this->normalizeStoredDate($product['available_at'] ?? null);

        $preorderMatches = $storedPreorder === $officialPreorder;
        $availableMatches = $storedAvailable === $officialAvailable;

        $this->newLine();
        $this->table(
            ['الحقل', 'المحفوظ', 'Apple الرسمي', 'الحالة'],
            [
                [
                    'الطلب المسبق',
                    $storedPreorder ?: '—',
                    $officialPreorder,
                    $preorderMatches ? '✓ مطابق' : '✗ مختلف',
                ],
                [
                    'التوفر',
                    $storedAvailable ?: '—',
                    $officialAvailable,
                    $availableMatches ? '✓ مطابق' : '✗ مختلف',
                ],
            ]
        );

        if ($preorderMatches && $availableMatches) {
            $this->info('✓ التواريخ المحفوظة مطابقة لمصدر Apple الرسمي.');
            $this->line('لم يتم تعديل قاعدة البيانات.');
            return self::SUCCESS;
        }

        $this->warn('⚠ يوجد اختلاف بين قاعدة البيانات ومصدر Apple. راجع التواريخ قبل اعتماد أي تعديل.');
        $this->line('لم يتم تعديل قاعدة البيانات تلقائيًا.');

        return self::FAILURE;
    }

    /**
     * يلتقط صيغة Apple Newsroom الحالية مثل:
     * Pre-orders begin Friday, October 16, with availability beginning Friday, October 23.
     */
    private function extractDates(string $text, int $year): array
    {
        $pattern = '/Pre-orders?\s+begin\s+Friday,\s+([A-Za-z]+)\s+(\d{1,2}).{0,180}?availability\s+beginning\s+Friday,\s+([A-Za-z]+)\s+(\d{1,2})/i';

        if (! preg_match($pattern, $text, $matches)) {
            return [null, null];
        }

        try {
            $preorder = Carbon::createFromFormat('F j Y', "{$matches[1]} {$matches[2]} {$year}")
                ->format('Y-m-d');

            $available = Carbon::createFromFormat('F j Y', "{$matches[3]} {$matches[4]} {$year}")
                ->format('Y-m-d');

            return [$preorder, $available];
        } catch (\Throwable) {
            return [null, null];
        }
    }

    private function normalizeStoredDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}

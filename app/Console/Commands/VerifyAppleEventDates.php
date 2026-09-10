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
     * يفحص تواريخ الطلب المسبق والتوفر المحفوظة مقابل مصادر Apple الرسمية.
     * الفحص للقراءة فقط ولا يعدّل قاعدة البيانات تلقائيًا.
     */
    protected $signature = 'events:verify-apple-dates
        {--year=2026 : سنة نسخة المؤتمر}
        {--product=iPhone Duo : اسم المنتج كما هو محفوظ في label_en}
        {--all : فحص جميع منتجات Apple المدعومة داخل النسخة}';

    protected $description = 'Verify Apple product pre-order and availability dates against official Apple sources';

    /**
     * مصادر Apple Newsroom الرسمية المدعومة حاليًا.
     * أكثر من منتج قد يشترك في نفس صفحة المصدر.
     */
    private const OFFICIAL_SOURCES = [
        'iphone 18 pro' => 'https://www.apple.com/newsroom/2026/09/apple-debuts-iphone-18-pro-and-iphone-18-pro-max/',
        'iphone 18 pro max' => 'https://www.apple.com/newsroom/2026/09/apple-debuts-iphone-18-pro-and-iphone-18-pro-max/',
        'iphone duo' => 'https://www.apple.com/newsroom/2026/09/apple-unveils-iphone-duo/',
        'airpods 5' => 'https://www.apple.com/newsroom/2026/09/apple-introduces-airpods-5-with-best-in-class-open-ear-active-noise-cancellation/',
        'apple watch series 12' => 'https://www.apple.com/newsroom/2026/09/introducing-apple-watch-series-12-with-the-all-new-health-sensing-system/',
        'apple watch ultra 4' => 'https://www.apple.com/newsroom/2026/09/apple-unveils-apple-watch-ultra-4/',
    ];

    /** @var array<string, string> */
    private array $pageCache = [];

    public function handle(): int
    {
        $year = (int) $this->option('year');

        $edition = EventEdition::query()
            ->where('year', $year)
            ->whereHas('event', fn ($query) => $query->where('slug', 'apple'))
            ->first();

        if (! $edition) {
            $this->error("لم أجد نسخة Apple لسنة {$year} في قاعدة البيانات.");
            return self::FAILURE;
        }

        if ($this->option('all')) {
            return $this->verifyAll($edition, $year);
        }

        $requestedProduct = trim((string) $this->option('product'));
        $productKey = Str::lower($requestedProduct);

        $product = $this->findProduct($edition, $productKey);

        if (! $product) {
            $this->error("لم أجد المنتج '{$requestedProduct}' داخل announcements.");
            return self::FAILURE;
        }

        $sourceUrl = $product['source_url'] ?? self::OFFICIAL_SOURCES[$productKey] ?? null;

        if (! $this->isOfficialAppleUrl($sourceUrl)) {
            $this->error('لا يوجد مصدر Apple رسمي معروف لهذا المنتج.');
            return self::FAILURE;
        }

        $result = $this->verifyProduct($requestedProduct, $product, $sourceUrl, $year, true);

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }

    private function verifyAll(EventEdition $edition, int $year): int
    {
        $rows = [];
        $sources = [];
        $hasFailure = false;
        $checked = 0;

        foreach ($edition->announcements ?? [] as $product) {
            $name = trim((string) ($product['label_en'] ?? ''));

            if ($name === '') {
                continue;
            }

            $key = Str::lower($name);
            $sourceUrl = $product['source_url'] ?? self::OFFICIAL_SOURCES[$key] ?? null;

            if (! $this->isOfficialAppleUrl($sourceUrl)) {
                $rows[] = [
                    $name,
                    $this->normalizeStoredDate($product['preorder_at'] ?? null) ?: '—',
                    '—',
                    'بدون مصدر',
                    $this->normalizeStoredDate($product['available_at'] ?? null) ?: '—',
                    '—',
                    'بدون مصدر',
                ];
                $hasFailure = true;
                continue;
            }

            $result = $this->verifyProduct($name, $product, $sourceUrl, $year, false);

            $rows[] = $result['row'];
            $sources[$sourceUrl] = true;
            $checked++;

            if (! $result['ok']) {
                $hasFailure = true;
            }
        }

        if ($checked === 0) {
            $this->error('لم أجد أي منتجات Apple مدعومة يمكن فحصها.');
            return self::FAILURE;
        }

        $this->table(
            [
                'المنتج',
                'طلب DB',
                'طلب Apple',
                'حالة الطلب',
                'توفر DB',
                'توفر Apple',
                'حالة التوفر',
            ],
            $rows
        );

        $this->newLine();
        $this->line('المصادر الرسمية المستخدمة:');
        foreach (array_keys($sources) as $url) {
            $this->line('• '.$url);
        }

        $this->newLine();

        if ($hasFailure) {
            $this->warn('⚠ انتهى الفحص مع اختلاف أو مصدر غير متاح. لم يتم تعديل قاعدة البيانات.');
            return self::FAILURE;
        }

        $this->info('✓ جميع التواريخ المحفوظة التي تم فحصها مطابقة لمصادر Apple الرسمية.');
        $this->line('لم يتم تعديل قاعدة البيانات.');

        return self::SUCCESS;
    }

    /**
     * @return array{ok: bool, row: array<int, string>}
     */
    private function verifyProduct(
        string $productName,
        array $product,
        string $sourceUrl,
        int $year,
        bool $verbose
    ): array {
        if ($verbose) {
            $this->line("المصدر الرسمي: {$sourceUrl}");
            $this->line('جاري فحص صفحة Apple...');
        }

        $text = $this->fetchPageText($sourceUrl);

        if ($text === null) {
            if ($verbose) {
                $this->error('تعذر فتح مصدر Apple. لم يتم تعديل أي بيانات.');
            }

            return [
                'ok' => false,
                'row' => [
                    $productName,
                    $this->normalizeStoredDate($product['preorder_at'] ?? null) ?: '—',
                    '—',
                    'خطأ مصدر',
                    $this->normalizeStoredDate($product['available_at'] ?? null) ?: '—',
                    '—',
                    'خطأ مصدر',
                ],
            ];
        }

        [$officialPreorder, $officialAvailable] = $this->extractDates($text, $year);

        if (! $officialAvailable) {
            if ($verbose) {
                $this->error('تم فتح صفحة Apple، لكن لم أستطع استخراج تاريخ التوفر. لم يتم تعديل أي بيانات.');
            }

            return [
                'ok' => false,
                'row' => [
                    $productName,
                    $this->normalizeStoredDate($product['preorder_at'] ?? null) ?: '—',
                    $officialPreorder ?: '—',
                    'تعذر التحقق',
                    $this->normalizeStoredDate($product['available_at'] ?? null) ?: '—',
                    '—',
                    'تعذر التحقق',
                ],
            ];
        }

        $storedPreorder = $this->normalizeStoredDate($product['preorder_at'] ?? null);
        $storedAvailable = $this->normalizeStoredDate($product['available_at'] ?? null);

        $preorderState = $this->compareDate($storedPreorder, $officialPreorder);
        $availableState = $this->compareDate($storedAvailable, $officialAvailable);

        $ok = $preorderState['ok'] && $availableState['ok'];

        if ($verbose) {
            $this->newLine();
            $this->table(
                ['الحقل', 'المحفوظ', 'Apple الرسمي', 'الحالة'],
                [
                    [
                        'الطلب المسبق',
                        $storedPreorder ?: '—',
                        $officialPreorder ?: '—',
                        $preorderState['label'],
                    ],
                    [
                        'التوفر',
                        $storedAvailable ?: '—',
                        $officialAvailable ?: '—',
                        $availableState['label'],
                    ],
                ]
            );

            if ($ok) {
                $this->info('✓ التواريخ المحفوظة مطابقة لمصدر Apple الرسمي.');
                $this->line('لم يتم تعديل قاعدة البيانات.');
            } else {
                $this->warn('⚠ يوجد اختلاف بين قاعدة البيانات ومصدر Apple. راجع التواريخ قبل اعتماد أي تعديل.');
                $this->line('لم يتم تعديل قاعدة البيانات تلقائيًا.');
            }
        }

        return [
            'ok' => $ok,
            'row' => [
                $productName,
                $storedPreorder ?: '—',
                $officialPreorder ?: '—',
                $preorderState['label'],
                $storedAvailable ?: '—',
                $officialAvailable ?: '—',
                $availableState['label'],
            ],
        ];
    }

    private function findProduct(EventEdition $edition, string $productKey): ?array
    {
        return collect($edition->announcements ?? [])->first(function (array $item) use ($productKey) {
            return Str::lower(trim((string) ($item['label_en'] ?? ''))) === $productKey;
        });
    }

    private function fetchPageText(string $sourceUrl): ?string
    {
        if (isset($this->pageCache[$sourceUrl])) {
            return $this->pageCache[$sourceUrl];
        }

        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->withHeaders([
                    'User-Agent' => 'ALYASI Event Verifier/1.1',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($sourceUrl);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $text = html_entity_decode(strip_tags($response->body()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return $this->pageCache[$sourceUrl] = $text;
    }

    /**
     * يدعم صيغ Apple Newsroom الحالية، ومنها:
     * - Pre-orders begin Friday, October 16, with availability beginning Friday, October 23.
     * - Pre-orders begin Saturday, September 12, with availability beginning Friday, September 18.
     * - available to pre-order today, with availability beginning Friday, September 18.
     * - pre-order starting today, with availability in stores beginning Friday, September 18.
     */
    private function extractDates(string $text, int $year): array
    {
        $preorder = null;
        $available = null;

        $explicitPattern = '/Pre-orders?\s+begin\s+(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),?\s+([A-Za-z]+)\s+(\d{1,2}).{0,220}?availability\s+beginning\s+(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),?\s+([A-Za-z]+)\s+(\d{1,2})/i';

        if (preg_match($explicitPattern, $text, $matches)) {
            $preorder = $this->buildDate($matches[1], $matches[2], $year);
            $available = $this->buildDate($matches[3], $matches[4], $year);

            return [$preorder, $available];
        }

        $preorderWithOnPattern = '/pre-order.{0,100}?on\s+(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),?\s+([A-Za-z]+)\s+(\d{1,2}).{0,220}?availability\s+beginning\s+(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),?\s+([A-Za-z]+)\s+(\d{1,2})/i';

        if (preg_match($preorderWithOnPattern, $text, $matches)) {
            $preorder = $this->buildDate($matches[1], $matches[2], $year);
            $available = $this->buildDate($matches[3], $matches[4], $year);

            return [$preorder, $available];
        }

        $availabilityPattern = '/availability(?:\s+in\s+stores)?\s+beginning\s+(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday),?\s+([A-Za-z]+)\s+(\d{1,2})/i';

        if (preg_match($availabilityPattern, $text, $matches)) {
            $available = $this->buildDate($matches[1], $matches[2], $year);
        }

        if ($available && preg_match('/pre-order(?:\s+starting|\s+available)?\s+today|available\s+to\s+pre-order\s+today/i', $text)) {
            $preorder = $this->extractPublicationDate($text, $year);
        }

        return [$preorder, $available];
    }

    private function extractPublicationDate(string $text, int $year): ?string
    {
        $patterns = [
            '/PRESS RELEASE\s+([A-Za-z]+)\s+(\d{1,2}),\s+('.$year.')/i',
            '/([A-Za-z]+)\s+(\d{1,2}),\s+('.$year.')\s+PRESS RELEASE/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->buildDate($matches[1], $matches[2], (int) $matches[3]);
            }
        }

        return null;
    }

    private function buildDate(string $month, string|int $day, int $year): ?string
    {
        try {
            return Carbon::createFromFormat('F j Y', "{$month} {$day} {$year}")
                ->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * الحقل غير المخزّن لا نعتبره خطأ؛ نعرض تاريخ Apple كمعلومة إضافية.
     */
    private function compareDate(?string $stored, ?string $official): array
    {
        if (! $official) {
            return ['ok' => false, 'label' => 'تعذر التحقق'];
        }

        if (! $stored) {
            return ['ok' => true, 'label' => 'غير مخزن'];
        }

        if ($stored === $official) {
            return ['ok' => true, 'label' => '✓ مطابق'];
        }

        return ['ok' => false, 'label' => '✗ مختلف'];
    }

    private function isOfficialAppleUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === 'apple.com' || str_ends_with($host, '.apple.com');
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

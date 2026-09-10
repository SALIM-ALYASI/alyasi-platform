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
     * افتراضيًا الفحص للقراءة فقط. خيار --fill-missing يملأ الحقول الفارغة
     * فقط بعد نجاح التحقق من المصدر الرسمي، ولا يستبدل أي قيمة موجودة.
     */
    protected $signature = 'events:verify-apple-dates
        {--year=2026 : سنة نسخة المؤتمر}
        {--product=iPhone Duo : اسم المنتج كما هو محفوظ في label_en}
        {--all : فحص جميع منتجات Apple المدعومة داخل النسخة}
        {--fill-missing : تعبئة التواريخ غير المخزنة فقط بعد التحقق من Apple}';

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
        $fillMissing = (bool) $this->option('fill-missing');

        $edition = EventEdition::query()
            ->where('year', $year)
            ->whereHas('event', fn ($query) => $query->where('slug', 'apple'))
            ->first();

        if (! $edition) {
            $this->error("لم أجد نسخة Apple لسنة {$year} في قاعدة البيانات.");
            return self::FAILURE;
        }

        if ($this->option('all')) {
            return $this->verifyAll($edition, $year, $fillMissing);
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

        if (! $result['ok']) {
            return self::FAILURE;
        }

        if ($fillMissing && $result['incomplete']) {
            $filled = $this->fillMissingDatesForProduct(
                $edition,
                $productKey,
                $result['official_preorder'],
                $result['official_available']
            );

            if ($filled > 0) {
                $this->info("✓ تمت تعبئة {$filled} قيمة ناقصة من مصدر Apple الرسمي بدون استبدال أي قيمة موجودة.");
            }
        }

        return self::SUCCESS;
    }

    private function verifyAll(EventEdition $edition, int $year, bool $fillMissing): int
    {
        $rows = [];
        $sources = [];
        $hasFailure = false;
        $hasWarning = false;
        $checked = 0;
        $filledCount = 0;
        $missingCount = 0;
        $announcements = $edition->announcements ?? [];

        foreach ($announcements as $index => $product) {
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
            $row = $result['row'];

            $sources[$sourceUrl] = true;
            $checked++;

            if (! $result['ok']) {
                $hasFailure = true;
            }

            $preorderWasMissing = $result['preorder_missing'];
            $availableWasMissing = $result['available_missing'];

            if ($preorderWasMissing) {
                $missingCount++;
            }

            if ($availableWasMissing) {
                $missingCount++;
            }

            if ($result['incomplete'] && ! $fillMissing) {
                $hasWarning = true;
            }

            if ($fillMissing && $result['ok']) {
                if ($preorderWasMissing && $result['official_preorder']) {
                    $announcements[$index]['preorder_at'] = $result['official_preorder'];
                    $row[1] = $result['official_preorder'];
                    $row[3] = '✓ تمت التعبئة';
                    $filledCount++;
                }

                if ($availableWasMissing && $result['official_available']) {
                    $announcements[$index]['available_at'] = $result['official_available'];
                    $row[4] = $result['official_available'];
                    $row[6] = '✓ تمت التعبئة';
                    $filledCount++;
                }
            }

            $rows[] = $row;
        }

        if ($checked === 0) {
            $this->error('لم أجد أي منتجات Apple مدعومة يمكن فحصها.');
            return self::FAILURE;
        }

        if ($fillMissing && $filledCount > 0 && ! $hasFailure) {
            $edition->announcements = array_values($announcements);
            $edition->save();
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
            $this->warn('⚠ انتهى الفحص مع اختلاف أو مصدر غير متاح. لم يتم استبدال أي قيمة موجودة في قاعدة البيانات.');
            return self::FAILURE;
        }

        if ($fillMissing && $filledCount > 0) {
            $this->info("✓ تمت تعبئة {$filledCount} قيمة ناقصة من مصادر Apple الرسمية.");
            $this->line('لم يتم استبدال أي تاريخ كان مخزنًا مسبقًا.');
            return self::SUCCESS;
        }

        if ($hasWarning) {
            $this->warn("⚠ التواريخ الموجودة مطابقة، لكن توجد {$missingCount} قيمة رسمية غير مخزنة في قاعدة البيانات.");
            $this->line('لملء الحقول الفارغة فقط بعد التحقق، شغّل: php artisan events:verify-apple-dates --all --fill-missing');
            return self::SUCCESS;
        }

        $this->info('✓ جميع التواريخ مكتملة ومطابقة لمصادر Apple الرسمية.');
        $this->line('لم يتم تعديل قاعدة البيانات.');

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     ok: bool,
     *     incomplete: bool,
     *     preorder_missing: bool,
     *     available_missing: bool,
     *     official_preorder: ?string,
     *     official_available: ?string,
     *     row: array<int, string>
     * }
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
                'incomplete' => false,
                'preorder_missing' => false,
                'available_missing' => false,
                'official_preorder' => null,
                'official_available' => null,
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
                'incomplete' => false,
                'preorder_missing' => false,
                'available_missing' => false,
                'official_preorder' => $officialPreorder,
                'official_available' => null,
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
        $incomplete = $preorderState['missing'] || $availableState['missing'];

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

            if (! $ok) {
                $this->warn('⚠ يوجد اختلاف بين قاعدة البيانات ومصدر Apple. راجع التواريخ قبل اعتماد أي تعديل.');
                $this->line('لم يتم تعديل قاعدة البيانات تلقائيًا.');
            } elseif ($incomplete) {
                $this->warn('⚠ التواريخ الموجودة مطابقة، لكن توجد قيمة رسمية غير مخزنة في قاعدة البيانات.');
                $this->line('يمكن تعبئة الحقول الفارغة فقط باستخدام --fill-missing.');
            } else {
                $this->info('✓ التواريخ المحفوظة مكتملة ومطابقة لمصدر Apple الرسمي.');
                $this->line('لم يتم تعديل قاعدة البيانات.');
            }
        }

        return [
            'ok' => $ok,
            'incomplete' => $incomplete,
            'preorder_missing' => $preorderState['missing'],
            'available_missing' => $availableState['missing'],
            'official_preorder' => $officialPreorder,
            'official_available' => $officialAvailable,
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

    private function fillMissingDatesForProduct(
        EventEdition $edition,
        string $productKey,
        ?string $officialPreorder,
        ?string $officialAvailable
    ): int {
        $announcements = $edition->announcements ?? [];
        $filled = 0;

        foreach ($announcements as $index => $item) {
            if (Str::lower(trim((string) ($item['label_en'] ?? ''))) !== $productKey) {
                continue;
            }

            if (blank($item['preorder_at'] ?? null) && $officialPreorder) {
                $announcements[$index]['preorder_at'] = $officialPreorder;
                $filled++;
            }

            if (blank($item['available_at'] ?? null) && $officialAvailable) {
                $announcements[$index]['available_at'] = $officialAvailable;
                $filled++;
            }

            break;
        }

        if ($filled > 0) {
            $edition->announcements = array_values($announcements);
            $edition->save();
        }

        return $filled;
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
                    'User-Agent' => 'ALYASI Event Verifier/1.2',
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
     * الحقل غير المخزّن يعتبر حالة ناقصة (تحذير)، وليس "مطابقًا" كاملًا.
     * نحتفظ بـ ok=true ما دام لا يوجد تعارض، حتى يمكن تعبئته بأمان عبر --fill-missing.
     */
    private function compareDate(?string $stored, ?string $official): array
    {
        if (! $official) {
            return ['ok' => false, 'missing' => false, 'label' => 'تعذر التحقق'];
        }

        if (! $stored) {
            return ['ok' => true, 'missing' => true, 'label' => '⚠ غير مخزن'];
        }

        if ($stored === $official) {
            return ['ok' => true, 'missing' => false, 'label' => '✓ مطابق'];
        }

        return ['ok' => false, 'missing' => false, 'label' => '✗ مختلف'];
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

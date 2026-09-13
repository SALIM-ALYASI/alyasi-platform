<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AppleStorePricingService
{
    public const SOURCE = 'apple_ae';

    private const AED_TO_OMR = 0.1047;

    /**
     * صفحات متجر Apple الإمارات الرسمية التي نعتمد عليها للأسعار.
     * لا يوجد API عام لأسعار Apple Store؛ لذلك نقرأ صفحات المتجر الرسمية
     * ونحوّل البيانات الظاهرة فيها إلى صفوف موحّدة لجدول pricing_table.
     */
    private const SOURCES = [
        'iphone-duo' => [
            'url' => 'https://www.apple.com/ae/shop/buy-iphone/iphone-duo',
            'expected_min' => 4,
            'parser' => 'parseIphoneDuo',
        ],
        'iphone-18-pro' => [
            'url' => 'https://www.apple.com/ae/shop/buy-iphone/iphone-18-pro',
            'expected_min' => 8,
            'parser' => 'parseIphone18Pro',
        ],
        'airpods-5' => [
            'url' => 'https://www.apple.com/ae/shop/buy-airpods/airpods-5',
            'expected_min' => 2,
            'parser' => 'parseAirPods5',
        ],
        'apple-watch' => [
            'url' => 'https://www.apple.com/ae/shop/buy-watch',
            'expected_min' => 2,
            'parser' => 'parseAppleWatch',
        ],
    ];

    /**
     * المصادر المتاحة للمزامنة.
     *
     * @return array<string, array{url:string, expected_min:int}>
     */
    public function sources(): array
    {
        return collect(self::SOURCES)
            ->map(fn (array $source) => [
                'url' => $source['url'],
                'expected_min' => $source['expected_min'],
            ])
            ->all();
    }

    /**
     * يجلب مصدرًا واحدًا ويعيد صفوف الأسعار بعد التحقق من الحد الأدنى المتوقع.
     * أي فشل يرمي استثناء ولا يلمس قاعدة البيانات؛ الأمر المسؤول عن المزامنة
     * يحتفظ عندها بآخر أسعار صحيحة محفوظة.
     *
     * @return array<int, array<string, string|null>>
     */
    public function fetch(string $sourceKey): array
    {
        $source = self::SOURCES[$sourceKey] ?? null;

        if (! $source) {
            throw new RuntimeException("Unknown Apple Store pricing source: {$sourceKey}");
        }

        $response = Http::accept('text/html,application/xhtml+xml')
            ->acceptLanguage('en-AE,en;q=0.9')
            ->withUserAgent('ALYASI-ApplePricingSync/1.0 (+https://alyasi.dev)')
            ->timeout(20)
            ->retry(2, 750)
            ->get($source['url']);

        $response->throw();

        $text = $this->normalizeHtml($response->body());
        $parser = $source['parser'];
        $rows = $this->{$parser}($text);

        if (count($rows) < $source['expected_min']) {
            throw new RuntimeException(sprintf(
                'Apple source %s returned only %d valid pricing rows; expected at least %d.',
                $sourceKey,
                count($rows),
                $source['expected_min'],
            ));
        }

        $syncedAt = now('UTC')->toIso8601String();

        return collect($rows)
            ->map(function (array $row) use ($sourceKey, $source, $syncedAt) {
                return array_merge($row, [
                    'source' => self::SOURCE,
                    'source_key' => $sourceKey,
                    'source_url' => $source['url'],
                    'synced_at' => $syncedAt,
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * iPhone Duo: نفس السعر يتكرر لكل لون؛ نحتفظ بسعر واحد لكل سعة.
     *
     * @return array<int, array<string, string|null>>
     */
    private function parseIphoneDuo(string $text): array
    {
        $segment = $this->sliceText(
            $text,
            ['Pre-order starting', 'Pre-order iPhone Duo', 'iPhone Duo'],
            ['Frequently Asked Questions', 'Product Information', 'Which iPhone is right for you?'],
        );

        $prices = $this->extractCapacityPrices($segment);

        return collect($prices)
            ->map(fn (array $amounts, string $capacity) => $this->priceRow(
                'iPhone Duo',
                'iPhone Duo',
                $capacity,
                $capacity,
                min($amounts),
            ))
            ->values()
            ->all();
    }

    /**
     * صفحة 18 Pro تجمع Pro وPro Max معًا. لكل سعة نأخذ السعر الأقل كـ Pro
     * والسعر الأعلى كـ Pro Max، بعد إزالة تكرار الألوان.
     *
     * @return array<int, array<string, string|null>>
     */
    private function parseIphone18Pro(string $text): array
    {
        $segment = $this->sliceText(
            $text,
            ['Pre-order starting', 'Shop iPhone 18 Pro', 'iPhone 18 Pro'],
            ['Frequently Asked Questions', 'Which iPhone is right for you?', 'Your new iPhone comes with'],
        );

        $prices = $this->extractCapacityPrices($segment);
        $rows = [];

        foreach ($prices as $capacity => $amounts) {
            $unique = array_values(array_unique(array_map('intval', $amounts)));
            sort($unique, SORT_NUMERIC);

            // نحتاج سعرين مميزين لنفس السعة حتى لا نخمن أيهما Pro وأيهما Max.
            if (count($unique) < 2) {
                continue;
            }

            $rows[] = $this->priceRow(
                'iPhone 18 Pro',
                'iPhone 18 Pro',
                $capacity,
                $capacity,
                $unique[0],
            );

            $rows[] = $this->priceRow(
                'iPhone 18 Pro Max',
                'iPhone 18 Pro Max',
                $capacity,
                $capacity,
                $unique[count($unique) - 1],
            );
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function parseAirPods5(string $text): array
    {
        $segment = $this->sliceText(
            $text,
            ['Pre-order AirPods 5', 'AirPods 5'],
            ['Product Information', 'Frequently Asked Questions'],
        );

        $rows = [];

        if (preg_match('/AirPods\s*5\s+AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $segment, $match)) {
            $rows[] = $this->priceRow(
                'AirPods 5',
                'AirPods 5',
                'النسخة الأساسية',
                'Standard',
                $this->parseAmount($match[1]),
            );
        }

        if (preg_match('/AirPods\s*5\s+with\s+Wireless\s+Charging\s+Case.{0,220}?AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $segment, $match)) {
            $rows[] = $this->priceRow(
                'AirPods 5',
                'AirPods 5',
                'علبة شحن لاسلكية',
                'Wireless Charging Case',
                $this->parseAmount($match[1]),
            );
        }

        return $rows;
    }

    /**
     * صفحة Apple Watch العامة تعرض "يبدأ من". نخزنها كسعر أساسي ولا نحاول
     * تخمين أسعار المقاسات/الأساور التي تحتاج صفحة configurator منفصلة.
     *
     * @return array<int, array<string, string|null>>
     */
    private function parseAppleWatch(string $text): array
    {
        $rows = [];

        if (preg_match('/Apple\s*Watch\s*Series\s*12.{0,120}?From\s+AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $text, $match)) {
            $rows[] = $this->priceRow(
                'Apple Watch Series 12',
                'Apple Watch Series 12',
                'يبدأ من',
                'Starting price',
                $this->parseAmount($match[1]),
            );
        }

        if (preg_match('/Apple\s*Watch\s*Ultra\s*4.{0,120}?From\s+AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $text, $match)) {
            $rows[] = $this->priceRow(
                'Apple Watch Ultra 4',
                'Apple Watch Ultra 4',
                'يبدأ من',
                'Starting price',
                $this->parseAmount($match[1]),
            );
        }

        return $rows;
    }

    /**
     * يستخرج السعات وأسعار AED القريبة منها، مع إزالة تكرار الألوان.
     *
     * @return array<string, array<int, int>>
     */
    private function extractCapacityPrices(string $text): array
    {
        preg_match_all(
            '/\b(256GB|512GB|1TB|2TB)\b.{0,140}?\bAED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu',
            $text,
            $matches,
            PREG_SET_ORDER,
        );

        $prices = [];

        foreach ($matches as $match) {
            $capacity = strtoupper($match[1]);
            $amount = $this->parseAmount($match[2]);

            if ($amount <= 0) {
                continue;
            }

            $prices[$capacity] ??= [];
            $prices[$capacity][] = $amount;
        }

        foreach ($prices as $capacity => $amounts) {
            $prices[$capacity] = array_values(array_unique($amounts));
        }

        $order = ['256GB', '512GB', '1TB', '2TB'];
        uksort($prices, fn (string $a, string $b) => array_search($a, $order, true) <=> array_search($b, $order, true));

        return $prices;
    }

    /**
     * @return array<string, string|null>
     */
    private function priceRow(
        string $productAr,
        string $productEn,
        string $variantAr,
        string $variantEn,
        int $amount,
    ): array {
        return [
            'product_ar' => $productAr,
            'product_en' => $productEn,
            'variant_ar' => $variantAr,
            'variant_en' => $variantEn,
            'official_price' => number_format($amount, 0, '.', ','),
            'official_currency' => 'AED',
            'omr_price' => (string) (int) ceil($amount * self::AED_TO_OMR),
        ];
    }

    private function parseAmount(string $value): int
    {
        return (int) round((float) str_replace(',', '', trim($value)));
    }

    private function normalizeHtml(string $html): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/isu', ' ', $html) ?? $html;
        $html = preg_replace('/<[^>]+>/u', ' ', $html) ?? $html;
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\u{00A0}", "\u{202F}"], ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function sliceText(string $text, array $startMarkers, array $endMarkers): string
    {
        $start = null;

        foreach ($startMarkers as $marker) {
            $position = mb_stripos($text, $marker);
            if ($position !== false && ($start === null || $position < $start)) {
                $start = $position;
            }
        }

        if ($start === null) {
            $start = 0;
        }

        $end = null;

        foreach ($endMarkers as $marker) {
            $position = mb_stripos($text, $marker, $start + 1);
            if ($position !== false && ($end === null || $position < $end)) {
                $end = $position;
            }
        }

        if ($end === null || $end <= $start) {
            return mb_substr($text, $start);
        }

        return mb_substr($text, $start, $end - $start);
    }
}

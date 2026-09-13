<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReliableAppleStorePricingService extends AppleStorePricingService
{
    private const AED_TO_OMR = 0.1047;

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

    public function sources(): array
    {
        return collect(self::SOURCES)
            ->map(fn (array $source) => [
                'url' => $source['url'],
                'expected_min' => $source['expected_min'],
            ])
            ->all();
    }

    public function fetch(string $sourceKey): array
    {
        $source = self::SOURCES[$sourceKey] ?? null;

        if (! $source) {
            throw new RuntimeException("Unknown Apple Store pricing source: {$sourceKey}");
        }

        $response = Http::withHeaders([
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en-AE,en;q=0.9',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0 Safari/537.36 ALYASI/1.1',
        ])
            ->timeout(20)
            ->retry(2, 750)
            ->get($source['url']);

        $response->throw();

        $text = $this->normalizeApplePayload($response->body());
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

    private function parseIphoneDuo(string $text): array
    {
        $prices = $this->extractCapacityPrices($this->sliceText(
            $text,
            ['Pre-order starting', 'Pre-order iPhone Duo', 'iPhone Duo'],
            ['Frequently Asked Questions', 'Product Information', 'Which iPhone is right for you?'],
        ));

        if (count($prices) < 4) {
            $prices = $this->mergeCapacityPrices($prices, $this->extractCapacityPrices($text));
        }

        return collect($prices)
            ->only(['256GB', '512GB', '1TB', '2TB'])
            ->filter(fn (array $amounts) => $amounts !== [])
            ->map(fn (array $amounts, string $capacity) => $this->priceRow(
                "iPhone Duo {$capacity}",
                "iPhone Duo {$capacity}",
                $capacity,
                $capacity,
                min($amounts),
            ))
            ->values()
            ->all();
    }

    private function parseIphone18Pro(string $text): array
    {
        $prices = $this->extractCapacityPrices($this->sliceText(
            $text,
            ['Pre-order starting', 'Shop iPhone 18 Pro', 'iPhone 18 Pro'],
            ['Frequently Asked Questions', 'Which iPhone is right for you?', 'Your new iPhone comes with'],
        ));

        if (! $this->hasTwoPricesPerCapacity($prices)) {
            $prices = $this->mergeCapacityPrices($prices, $this->extractCapacityPrices($text));
        }

        $rows = [];

        foreach (['256GB', '512GB', '1TB', '2TB'] as $capacity) {
            $unique = array_values(array_unique(array_map('intval', $prices[$capacity] ?? [])));
            sort($unique, SORT_NUMERIC);

            if (count($unique) < 2) {
                continue;
            }

            $rows[] = $this->priceRow(
                "iPhone 18 Pro {$capacity}",
                "iPhone 18 Pro {$capacity}",
                $capacity,
                $capacity,
                $unique[0],
            );

            $rows[] = $this->priceRow(
                "iPhone 18 Pro Max {$capacity}",
                "iPhone 18 Pro Max {$capacity}",
                $capacity,
                $capacity,
                $unique[count($unique) - 1],
            );
        }

        return $rows;
    }

    private function parseAirPods5(string $text): array
    {
        $segment = $this->sliceText(
            $text,
            ['Pre-order AirPods 5', 'AirPods 5'],
            ['Product Information', 'Frequently Asked Questions'],
        );

        $rows = [];

        if (preg_match('/AirPods\s*5\s+AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $segment, $match)) {
            $rows[] = $this->priceRow('AirPods 5', 'AirPods 5', 'النسخة الأساسية', 'Standard', $this->parseAmount($match[1]));
        }

        if (preg_match('/AirPods\s*5\s+with\s+Wireless\s+Charging\s+Case.{0,400}?AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $segment, $match)) {
            $rows[] = $this->priceRow(
                'AirPods 5 مع علبة شحن لاسلكية',
                'AirPods 5 with Wireless Charging Case',
                'علبة شحن لاسلكية',
                'Wireless Charging Case',
                $this->parseAmount($match[1]),
            );
        }

        return $rows;
    }

    private function parseAppleWatch(string $text): array
    {
        $rows = [];

        if (preg_match('/Apple\s*Watch\s*Series\s*12.{0,240}?From\s+AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $text, $match)) {
            $rows[] = $this->priceRow('Apple Watch Series 12', 'Apple Watch Series 12', 'يبدأ من', 'Starting price', $this->parseAmount($match[1]));
        }

        if (preg_match('/Apple\s*Watch\s*Ultra\s*4.{0,240}?From\s+AED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu', $text, $match)) {
            $rows[] = $this->priceRow('Apple Watch Ultra 4', 'Apple Watch Ultra 4', 'يبدأ من', 'Starting price', $this->parseAmount($match[1]));
        }

        return $rows;
    }

    /**
     * يستخرج كل السعات والأسعار بشكل منفصل ثم يربط كل سعر بأقرب سعة.
     * نتجنب هنا tempered-dot regex الكبير الذي سبب PCRE "regular expression is too large".
     *
     * @return array<string, array<int, int>>
     */
    private function extractCapacityPrices(string $text): array
    {
        preg_match_all(
            '/\b(256GB|512GB|1TB|2TB)\b/iu',
            $text,
            $capacityMatches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
        );

        preg_match_all(
            '/\bAED\s*([0-9][0-9,]*(?:\.[0-9]{1,2})?)/iu',
            $text,
            $priceMatches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
        );

        if ($capacityMatches === [] || $priceMatches === []) {
            return [];
        }

        $capacities = collect($capacityMatches)
            ->map(fn (array $match) => [
                'capacity' => strtoupper((string) $match[1][0]),
                'offset' => (int) $match[0][1],
            ])
            ->all();

        $prices = [];

        foreach ($priceMatches as $match) {
            $amount = $this->parseAmount((string) $match[1][0]);
            $priceOffset = (int) $match[0][1];

            // أسعار iPhone في متجر Apple الإمارات تقع ضمن هذا النطاق؛
            // يمنع التقاط Trade In والإكسسوارات والأقساط كأنها سعر جهاز.
            if ($amount < 3000 || $amount > 20000) {
                continue;
            }

            $nearest = null;
            $nearestDistance = PHP_INT_MAX;

            foreach ($capacities as $candidate) {
                $distance = abs($priceOffset - $candidate['offset']);

                if ($distance > 700) {
                    continue;
                }

                if ($distance < $nearestDistance) {
                    $nearest = $candidate;
                    $nearestDistance = $distance;
                    continue;
                }

                // عند التعادل نفضّل السعة التي تسبق السعر في النص.
                if (
                    $distance === $nearestDistance
                    && $candidate['offset'] <= $priceOffset
                    && ($nearest['offset'] ?? PHP_INT_MAX) > $priceOffset
                ) {
                    $nearest = $candidate;
                }
            }

            if (! is_array($nearest)) {
                continue;
            }

            $capacity = $nearest['capacity'];
            $prices[$capacity] ??= [];
            $prices[$capacity][] = $amount;
        }

        foreach ($prices as $capacity => $amounts) {
            $prices[$capacity] = array_values(array_unique(array_map('intval', $amounts)));
            sort($prices[$capacity], SORT_NUMERIC);
        }

        $order = ['256GB', '512GB', '1TB', '2TB'];
        uksort($prices, fn (string $a, string $b) => array_search($a, $order, true) <=> array_search($b, $order, true));

        return $prices;
    }

    private function mergeCapacityPrices(array $left, array $right): array
    {
        foreach ($right as $capacity => $amounts) {
            $left[$capacity] = array_values(array_unique(array_merge($left[$capacity] ?? [], array_map('intval', $amounts))));
            sort($left[$capacity], SORT_NUMERIC);
        }

        return $left;
    }

    private function hasTwoPricesPerCapacity(array $prices): bool
    {
        foreach (['256GB', '512GB', '1TB', '2TB'] as $capacity) {
            if (count(array_unique($prices[$capacity] ?? [])) < 2) {
                return false;
            }
        }

        return true;
    }

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

    private function normalizeApplePayload(string $html): string
    {
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/isu', ' ', $html) ?? $html;
        $html = preg_replace('/<[^>]+>/u', ' ', $html) ?? $html;
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = str_replace([
            '\\u00a0', '\\u00A0', '\\u202f', '\\u202F',
            '\\u0026', '\\u002F', '\\/', '\\"',
            "\u{00A0}", "\u{202F}",
        ], [
            ' ', ' ', ' ', ' ',
            '&', '/', '/', '"',
            ' ', ' ',
        ], $text);

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

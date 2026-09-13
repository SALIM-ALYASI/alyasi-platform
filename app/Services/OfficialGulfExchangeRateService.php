<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OfficialGulfExchangeRateService
{
    private const KWD_CACHE_KEY = 'gulf_fx:kwd-usd:v1';
    private const KWD_CACHE_TTL_HOURS = 24;
    private const KWD_FALLBACK = 0.30665;
    private const KWD_SOURCE_URL = 'https://www.cbk.gov.kw/en/monetary-policy/market-operations/exchange-rates/usd';

    /**
     * Currency units per 1 USD.
     *
     * OMR, SAR, AED, QAR and BHD are based on official central-bank pegs /
     * published official rates. KWD is market-linked to a currency basket,
     * so it is refreshed from the Central Bank of Kuwait instead of being
     * treated as a permanent fixed value.
     */
    private const FIXED_USD_RATES = [
        'USD' => 1.0,
        'OMR' => 1 / 2.6008,
        'SAR' => 3.75,
        'AED' => 3.6725,
        'QAR' => 3.64,
        'BHD' => 1 / 2.659,
    ];

    private const OFFICIAL_SOURCES = [
        'OMR' => 'https://cbo.gov.om/Pages/FixedPeg.aspx',
        'SAR' => 'https://www.sama.gov.sa/en-US/FinExc/Pages/Currency.aspx',
        'AED' => 'https://centralbank.ae/umbraco/Surface/Exchange/GetExchangeRateAllCurrency',
        'QAR' => 'https://www.qcb.gov.qa/en/Pages/MonetaryPolicyTools.aspx',
        'BHD' => 'https://www.cbb.gov.bh/',
        'KWD' => self::KWD_SOURCE_URL,
    ];

    public function rate(string $currency): float
    {
        $currency = strtoupper(trim($currency));

        if ($currency === 'KWD') {
            return $this->kwdInfo()['rate'];
        }

        if (! array_key_exists($currency, self::FIXED_USD_RATES)) {
            throw new RuntimeException("Unsupported Gulf currency: {$currency}");
        }

        return (float) self::FIXED_USD_RATES[$currency];
    }

    /**
     * @return array{currency:string, rate:float, source:string, rate_date:?string, fetched_at:?string, status:string}
     */
    public function info(string $currency): array
    {
        $currency = strtoupper(trim($currency));

        if ($currency === 'KWD') {
            return $this->kwdInfo();
        }

        if (! array_key_exists($currency, self::FIXED_USD_RATES)) {
            throw new RuntimeException("Unsupported Gulf currency: {$currency}");
        }

        return [
            'currency' => $currency,
            'rate' => (float) self::FIXED_USD_RATES[$currency],
            'source' => self::OFFICIAL_SOURCES[$currency] ?? '',
            'rate_date' => null,
            'fetched_at' => null,
            'status' => 'official_fixed',
        ];
    }

    /**
     * Force-refreshes Kuwait's official daily USD/KWD rate.
     *
     * @return array{currency:string, rate:float, source:string, rate_date:?string, fetched_at:?string, status:string}
     */
    public function refreshKwd(): array
    {
        $fresh = $this->fetchKwdFromCentralBank();
        Cache::put(self::KWD_CACHE_KEY, $fresh, now()->addDays(7));

        return $fresh;
    }

    /**
     * @return array{currency:string, rate:float, source:string, rate_date:?string, fetched_at:?string, status:string}
     */
    private function kwdInfo(): array
    {
        $cached = Cache::get(self::KWD_CACHE_KEY);

        if (is_array($cached) && $this->cacheIsFresh($cached)) {
            return $cached;
        }

        try {
            return $this->refreshKwd();
        } catch (Throwable) {
            if (is_array($cached) && isset($cached['rate'])) {
                $cached['status'] = 'official_stale_cache';

                return $cached;
            }

            return [
                'currency' => 'KWD',
                'rate' => self::KWD_FALLBACK,
                'source' => self::KWD_SOURCE_URL,
                'rate_date' => null,
                'fetched_at' => null,
                'status' => 'safe_fallback',
            ];
        }
    }

    private function cacheIsFresh(array $cached): bool
    {
        $fetchedAt = $cached['fetched_at'] ?? null;
        if (! is_string($fetchedAt) || $fetchedAt === '') {
            return false;
        }

        try {
            return CarbonImmutable::parse($fetchedAt)->greaterThan(now()->subHours(self::KWD_CACHE_TTL_HOURS));
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{currency:string, rate:float, source:string, rate_date:string, fetched_at:string, status:string}
     */
    private function fetchKwdFromCentralBank(): array
    {
        $response = Http::withHeaders([
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en-US,en;q=0.9',
            'User-Agent' => 'ALYASI-GulfFX/1.0 (+https://alyasi.dev)',
        ])
            ->timeout(15)
            ->retry(2, 500)
            ->get(self::KWD_SOURCE_URL);

        $response->throw();

        $text = $this->normalizeHtml($response->body());

        preg_match_all(
            '/(\d{2}\.\d{2}\.\d{4})\s+([0-9]{3}\.[0-9]{3})/u',
            $text,
            $matches,
            PREG_SET_ORDER,
        );

        if ($matches === []) {
            throw new RuntimeException('Central Bank of Kuwait response did not contain a USD/KWD rate.');
        }

        $latest = collect($matches)
            ->map(function (array $match) {
                $date = CarbonImmutable::createFromFormat('d.m.Y', $match[1], 'UTC');
                $published = (float) $match[2];

                return [
                    'date' => $date,
                    // CBK publishes the value in fils (e.g. 306.650 => 0.306650 KWD/USD).
                    'rate' => $published / 1000,
                ];
            })
            ->filter(fn (array $row) => $row['date'] instanceof CarbonImmutable && $row['rate'] > 0.2 && $row['rate'] < 0.5)
            ->sortByDesc(fn (array $row) => $row['date']->timestamp)
            ->first();

        if (! is_array($latest)) {
            throw new RuntimeException('Central Bank of Kuwait USD/KWD rate was outside the expected range.');
        }

        return [
            'currency' => 'KWD',
            'rate' => (float) $latest['rate'],
            'source' => self::KWD_SOURCE_URL,
            'rate_date' => $latest['date']->format('Y-m-d'),
            'fetched_at' => now('UTC')->toIso8601String(),
            'status' => 'official_live',
        ];
    }

    private function normalizeHtml(string $html): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/isu', ' ', $html) ?? $html;
        $html = preg_replace('/<[^>]+>/u', ' ', $html) ?? $html;
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}

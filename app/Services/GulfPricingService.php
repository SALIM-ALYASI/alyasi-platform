<?php

namespace App\Services;

use Illuminate\Http\Request;

class GulfPricingService
{
    /** @var array<string, string> */
    private const COUNTRY_CURRENCIES = [
        'OM' => 'OMR',
        'SA' => 'SAR',
        'AE' => 'AED',
        'QA' => 'QAR',
        'KW' => 'KWD',
        'BH' => 'BHD',
    ];

    public function __construct(
        private readonly OfficialGulfExchangeRateService $exchangeRates,
    ) {
    }

    /**
     * Cloudflare exposes only the visitor country code here; no precise
     * location is required. Any non-GCC country (or missing header) falls
     * back to OMR as requested.
     */
    public function countryCode(Request $request): string
    {
        $country = strtoupper(trim((string) $request->header('CF-IPCountry', '')));

        return array_key_exists($country, self::COUNTRY_CURRENCIES)
            ? $country
            : 'OM';
    }

    public function currencyForRequest(Request $request): string
    {
        return $this->currencyForCountry($this->countryCode($request));
    }

    public function currencyForCountry(?string $countryCode): string
    {
        $countryCode = strtoupper(trim((string) $countryCode));

        return self::COUNTRY_CURRENCIES[$countryCode] ?? 'OMR';
    }

    /**
     * Converts a persisted pricing row for presentation only.
     *
     * Database values stay unchanged. The public page receives:
     * - official_price / official_currency => USD
     * - omr_price => visitor-local estimated amount including currency code
     * - exchange_rate_* => source metadata for diagnostics/transparency
     *
     * @return array<string, mixed>
     */
    public function localizeRow(array $row, string $localCurrency): array
    {
        $localCurrency = strtoupper(trim($localCurrency));
        if (! in_array($localCurrency, ['OMR', 'SAR', 'AED', 'QAR', 'KWD', 'BHD'], true)) {
            $localCurrency = 'OMR';
        }

        $sourceCurrency = strtoupper(trim((string) ($row['official_currency'] ?? '')));
        $sourceAmount = $this->parseAmount($row['official_price'] ?? null);

        if ($sourceAmount <= 0) {
            return $row;
        }

        try {
            $sourceRate = $this->exchangeRates->rate($sourceCurrency);
            $localInfo = $this->exchangeRates->info($localCurrency);
        } catch (\RuntimeException) {
            return $row;
        }

        if ($sourceRate <= 0 || $localInfo['rate'] <= 0) {
            return $row;
        }

        $usdAmount = $sourceAmount / $sourceRate;
        $localAmount = $usdAmount * $localInfo['rate'];

        // If OMR was explicitly saved, keep that value for Oman/fallback
        // visitors instead of replacing an editor-approved conversion.
        if ($localCurrency === 'OMR' && filled($row['omr_price'] ?? null)) {
            $savedOmr = $this->parseAmount($row['omr_price']);
            if ($savedOmr > 0) {
                $localAmount = $savedOmr;
            }
        }

        $row['official_price'] = $this->formatAmount($usdAmount);
        $row['official_currency'] = 'USD';
        $row['omr_price'] = $this->formatAmount($localAmount).' '.$localCurrency;
        $row['display_currency'] = $localCurrency;
        $row['exchange_rate_source'] = $localInfo['source'];
        $row['exchange_rate_date'] = $localInfo['rate_date'];
        $row['exchange_rate_status'] = $localInfo['status'];

        return $row;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, mixed>
     */
    public function localizeRows(array $rows, string $localCurrency): array
    {
        return collect($rows)
            ->map(fn ($row) => is_array($row) ? $this->localizeRow($row, $localCurrency) : $row)
            ->all();
    }

    private function parseAmount(mixed $value): float
    {
        $amount = preg_replace('/[^0-9.]/', '', (string) $value);

        return is_numeric($amount) ? (float) $amount : 0.0;
    }

    private function formatAmount(float $amount): string
    {
        return number_format((int) round($amount), 0, '.', ',');
    }
}

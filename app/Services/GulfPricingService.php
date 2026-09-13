<?php

namespace App\Services;

use Illuminate\Http\Request;

class GulfPricingService
{
    /**
     * Currency units per 1 USD. These are display/conversion rates only.
     * Local prices remain clearly marked as estimates in the public UI.
     */
    private const USD_RATES = [
        'USD' => 1.0,
        'OMR' => 0.3845,
        'SAR' => 3.75,
        'AED' => 3.6725,
        'QAR' => 3.64,
        'KWD' => 0.307,
        'BHD' => 0.376,
    ];

    /** @var array<string, string> */
    private const COUNTRY_CURRENCIES = [
        'OM' => 'OMR',
        'SA' => 'SAR',
        'AE' => 'AED',
        'QA' => 'QAR',
        'KW' => 'KWD',
        'BH' => 'BHD',
    ];

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
     *
     * The legacy omr_price key is intentionally reused so the existing Blade
     * templates remain backwards compatible while the label becomes generic.
     *
     * @return array<string, mixed>
     */
    public function localizeRow(array $row, string $localCurrency): array
    {
        $localCurrency = strtoupper(trim($localCurrency));
        if (! isset(self::USD_RATES[$localCurrency]) || $localCurrency === 'USD') {
            $localCurrency = 'OMR';
        }

        $sourceCurrency = strtoupper(trim((string) ($row['official_currency'] ?? '')));
        $sourceAmount = $this->parseAmount($row['official_price'] ?? null);

        if ($sourceAmount <= 0 || ! isset(self::USD_RATES[$sourceCurrency])) {
            return $row;
        }

        $usdAmount = $sourceAmount / self::USD_RATES[$sourceCurrency];
        $localAmount = $usdAmount * self::USD_RATES[$localCurrency];

        // If OMR was explicitly saved, keep that value for Oman/fallback
        // visitors instead of replacing an editor-approved conversion.
        if ($localCurrency === 'OMR' && filled($row['omr_price'] ?? null)) {
            $savedOmr = $this->parseAmount($row['omr_price']);
            if ($savedOmr > 0) {
                $localAmount = $savedOmr;
            }
        }

        $row['official_price'] = $this->formatAmount($usdAmount, 'USD');
        $row['official_currency'] = 'USD';
        $row['omr_price'] = $this->formatAmount($localAmount, $localCurrency).' '.$localCurrency;
        $row['display_currency'] = $localCurrency;

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

    private function formatAmount(float $amount, string $currency): string
    {
        // Product prices are kept simple and readable. KWD/BHD/OMR can use
        // three decimals in accounting, but whole-unit estimates are clearer
        // for this comparison UI and match the existing event design.
        return number_format((int) round($amount), 0, '.', ',');
    }
}

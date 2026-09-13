<?php

namespace Tests\Unit;

use App\Services\GulfPricingService;
use App\Services\OfficialGulfExchangeRateService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class GulfPricingServiceTest extends TestCase
{
    private function service(): GulfPricingService
    {
        return new GulfPricingService(new OfficialGulfExchangeRateService());
    }

    public function test_it_selects_gcc_currency_from_cloudflare_country(): void
    {
        $service = $this->service();

        $expectations = [
            'OM' => 'OMR',
            'SA' => 'SAR',
            'AE' => 'AED',
            'QA' => 'QAR',
            'KW' => 'KWD',
            'BH' => 'BHD',
        ];

        foreach ($expectations as $country => $currency) {
            $request = Request::create('/events/apple-event-2026');
            $request->headers->set('CF-IPCountry', $country);

            $this->assertSame($currency, $service->currencyForRequest($request));
        }
    }

    public function test_non_gcc_and_missing_country_fall_back_to_omr(): void
    {
        $service = $this->service();

        $outsideGcc = Request::create('/events/apple-event-2026');
        $outsideGcc->headers->set('CF-IPCountry', 'US');

        $missingHeader = Request::create('/events/apple-event-2026');

        $this->assertSame('OMR', $service->currencyForRequest($outsideGcc));
        $this->assertSame('OMR', $service->currencyForRequest($missingHeader));
    }

    public function test_it_displays_usd_plus_saudi_riyal_from_an_aed_source_price(): void
    {
        $service = $this->service();

        $row = $service->localizeRow([
            'product_en' => 'iPhone Duo',
            'official_price' => '8,499',
            'official_currency' => 'AED',
            'omr_price' => null,
        ], 'SAR');

        $this->assertSame('2,314', $row['official_price']);
        $this->assertSame('USD', $row['official_currency']);
        $this->assertSame('8,678 SAR', $row['omr_price']);
        $this->assertSame('SAR', $row['display_currency']);
        $this->assertSame('official_fixed', $row['exchange_rate_status']);
    }

    public function test_it_uses_official_omr_rate_even_when_an_old_manual_value_exists(): void
    {
        $service = $this->service();

        $row = $service->localizeRow([
            'product_en' => 'iPhone Duo',
            'official_price' => '8,499',
            'official_currency' => 'AED',
            'omr_price' => '895',
        ], 'OMR');

        $this->assertSame('2,314', $row['official_price']);
        $this->assertSame('890 OMR', $row['omr_price']);
        $this->assertSame('official_fixed', $row['exchange_rate_status']);
    }
}

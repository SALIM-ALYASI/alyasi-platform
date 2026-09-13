<?php

namespace Tests\Unit;

use App\Services\GulfPricingService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class GulfPricingServiceTest extends TestCase
{
    public function test_it_selects_gcc_currency_from_cloudflare_country(): void
    {
        $service = new GulfPricingService();

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
        $service = new GulfPricingService();

        $outsideGcc = Request::create('/events/apple-event-2026');
        $outsideGcc->headers->set('CF-IPCountry', 'US');

        $missingHeader = Request::create('/events/apple-event-2026');

        $this->assertSame('OMR', $service->currencyForRequest($outsideGcc));
        $this->assertSame('OMR', $service->currencyForRequest($missingHeader));
    }

    public function test_it_displays_usd_plus_saudi_riyal_from_an_aed_source_price(): void
    {
        $service = new GulfPricingService();

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
    }

    public function test_it_keeps_editor_approved_omr_value_for_oman_and_fallback_visitors(): void
    {
        $service = new GulfPricingService();

        $row = $service->localizeRow([
            'product_en' => 'iPhone Duo',
            'official_price' => '8,499',
            'official_currency' => 'AED',
            'omr_price' => '895',
        ], 'OMR');

        $this->assertSame('2,314', $row['official_price']);
        $this->assertSame('895 OMR', $row['omr_price']);
    }
}

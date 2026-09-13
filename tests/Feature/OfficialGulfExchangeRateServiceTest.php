<?php

namespace Tests\Feature;

use App\Services\OfficialGulfExchangeRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OfficialGulfExchangeRateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('gulf_fx:kwd-usd:v1');
    }

    public function test_it_uses_official_fixed_gulf_rates(): void
    {
        $service = app(OfficialGulfExchangeRateService::class);

        $this->assertEqualsWithDelta(1 / 2.6008, $service->rate('OMR'), 0.0000001);
        $this->assertSame(3.75, $service->rate('SAR'));
        $this->assertSame(3.6725, $service->rate('AED'));
        $this->assertSame(3.64, $service->rate('QAR'));
        $this->assertEqualsWithDelta(1 / 2.659, $service->rate('BHD'), 0.0000001);
    }

    public function test_it_refreshes_kwd_from_central_bank_of_kuwait_and_caches_it(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://www.cbk.gov.kw/en/monetary-policy/market-operations/exchange-rates/usd' => Http::response(<<<'HTML'
                <html><body>
                    <table>
                        <tr><td>08.09.2026</td><td>306.650</td></tr>
                        <tr><td>09.09.2026</td><td>306.650</td></tr>
                        <tr><td>10.09.2026</td><td>306.650</td></tr>
                    </table>
                </body></html>
                HTML),
        ]);

        $service = app(OfficialGulfExchangeRateService::class);
        $info = $service->refreshKwd();

        $this->assertEqualsWithDelta(0.30665, $info['rate'], 0.0000001);
        $this->assertSame('2026-09-10', $info['rate_date']);
        $this->assertSame('official_live', $info['status']);
        $this->assertNotNull(Cache::get('gulf_fx:kwd-usd:v1'));
    }

    public function test_it_uses_last_official_kwd_cache_when_refresh_fails(): void
    {
        Cache::put('gulf_fx:kwd-usd:v1', [
            'currency' => 'KWD',
            'rate' => 0.30665,
            'source' => 'https://www.cbk.gov.kw/en/monetary-policy/market-operations/exchange-rates/usd',
            'rate_date' => '2026-09-10',
            'fetched_at' => now()->subDays(2)->toIso8601String(),
            'status' => 'official_live',
        ], now()->addDays(7));

        Http::preventStrayRequests();
        Http::fake([
            'https://www.cbk.gov.kw/en/monetary-policy/market-operations/exchange-rates/usd' => Http::response('upstream unavailable', 503),
        ]);

        $info = app(OfficialGulfExchangeRateService::class)->info('KWD');

        $this->assertEqualsWithDelta(0.30665, $info['rate'], 0.0000001);
        $this->assertSame('2026-09-10', $info['rate_date']);
        $this->assertSame('official_stale_cache', $info['status']);
    }
}

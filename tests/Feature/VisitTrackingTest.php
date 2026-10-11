<?php

namespace Tests\Feature;

use App\Jobs\ResolveVisitCountry;
use App\Models\PageVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    private const SAFARI = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1';

    private function browser(array $headers = []): static
    {
        return $this->withHeaders(array_merge([
            'User-Agent' => self::SAFARI,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'ar',
        ], $headers));
    }

    public function test_a_normal_browser_visit_is_recorded_as_likely_human(): void
    {
        Bus::fake();

        $response = $this->browser()->get('/')->assertOk();

        $this->assertDatabaseCount('page_visits', 1);

        $visit = PageVisit::query()->first();

        $this->assertSame('/', $visit->path);
        $this->assertSame('human', $visit->traffic_class);
        $this->assertNull($visit->class_reasons);
        $this->assertNotNull($visit->visitor_id);
        $response->assertCookie('alyasi_vid', $visit->visitor_id);

        Bus::assertDispatchedAfterResponse(ResolveVisitCountry::class);
    }

    public function test_returning_visitor_keeps_the_same_visitor_id(): void
    {
        Bus::fake();

        $this->browser()->withCookie('alyasi_vid', '01k7abcdefghjkmnpqrstvwxyz')->get('/')->assertOk();

        $this->assertSame('01k7abcdefghjkmnpqrstvwxyz', PageVisit::query()->value('visitor_id'));
    }

    public function test_admin_pages_are_not_tracked(): void
    {
        Bus::fake();

        $this->browser()->get(route('admin.login'));

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_declared_bots_are_kept_but_classified_as_bot(): void
    {
        Bus::fake();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get('/');

        $visit = PageVisit::query()->sole();

        $this->assertSame('bot', $visit->traffic_class);
        $this->assertNull($visit->visitor_id);
        Bus::assertNotDispatchedAfterResponse(ResolveVisitCountry::class);
    }

    public function test_server_side_checks_are_classified_internal(): void
    {
        Bus::fake();

        $this->withHeader('User-Agent', 'Symfony')->get('/');

        $this->assertSame('internal', PageVisit::query()->value('traffic_class'));
    }

    public function test_owner_device_is_classified_owner(): void
    {
        Bus::fake();

        $this->browser()->withCookie('alyasi_owner', '1')->get('/')->assertOk();

        $this->assertSame('owner', PageVisit::query()->value('traffic_class'));
        Bus::assertNotDispatchedAfterResponse(ResolveVisitCountry::class);
    }

    public function test_browser_without_language_or_with_known_fake_agent_is_suspect(): void
    {
        Bus::fake();

        $this->browser(['Accept-Language' => ''])->get('/');
        $this->browser(['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1'])->get('/');

        $visits = PageVisit::query()->orderBy('id')->get();

        $this->assertSame(['suspect', 'suspect'], $visits->pluck('traffic_class')->all());
        $this->assertStringContainsString('no_accept_language', $visits[0]->class_reasons);
        $this->assertStringContainsString('known_fake_ua', $visits[1]->class_reasons);
        $this->assertStringContainsString('outdated_browser', $visits[1]->class_reasons);
    }

    public function test_real_ip_is_taken_from_cloudflare_only(): void
    {
        Bus::fake();

        // من Cloudflare: أول عنوان «مزوّر» من الزائر يتجاهل، وناخذ اللي أضافه Cloudflare.
        $this->browser(['X-Forwarded-For' => '1.2.3.4, 5.36.10.20', 'CF-IPCountry' => 'OM'])
            ->withServerVariables(['REMOTE_ADDR' => '162.158.28.146'])
            ->get('/');

        // من عنوان عادي: X-Forwarded-For و CF-IPCountry ما ينصدقون.
        $this->browser(['X-Forwarded-For' => '5.36.10.20', 'CF-IPCountry' => 'OM'])
            ->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])
            ->get('/');

        [$viaCloudflare, $direct] = PageVisit::query()->orderBy('id')->get();

        $this->assertSame('5.36.10.20', $viaCloudflare->ip_address);
        $this->assertSame('162.158.28.146', $viaCloudflare->proxy_ip);
        $this->assertSame('OM', $viaCloudflare->country_code);

        $this->assertSame('9.9.9.9', $direct->ip_address);
        $this->assertNull($direct->proxy_ip);
        $this->assertNull($direct->country_code);
    }

    public function test_post_requests_are_not_tracked(): void
    {
        Bus::fake();

        $this->post('/');

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_resolve_visit_country_job_marks_local_ip_as_local(): void
    {
        $visit = PageVisit::query()->create([
            'path' => '/',
            'ip_address' => '127.0.0.1',
        ]);

        (new ResolveVisitCountry($visit->id))->handle();

        $this->assertSame('محلي', $visit->fresh()->country_name);
    }

    public function test_known_ip_reuses_its_country_without_calling_the_service(): void
    {
        Http::fake();

        PageVisit::query()->create(['path' => '/', 'ip_address' => '5.36.0.1', 'country_code' => 'OM', 'country_name' => 'Oman']);
        $visit = PageVisit::query()->create(['path' => '/news', 'ip_address' => '5.36.0.1']);

        (new ResolveVisitCountry($visit->id))->handle();

        $this->assertSame('Oman', $visit->fresh()->country_name);
        Http::assertNothingSent();
    }

    public function test_datacenter_address_turns_a_likely_human_visit_into_suspect(): void
    {
        Http::fake(['ip-api.com/*' => Http::response([
            'status' => 'success', 'country' => 'Singapore', 'countryCode' => 'SG', 'hosting' => true, 'proxy' => false,
        ])]);

        $visit = PageVisit::query()->create([
            'path' => '/', 'ip_address' => '8.8.4.4', 'country_code' => 'OM', 'traffic_class' => 'human',
        ]);

        (new ResolveVisitCountry($visit->id))->handle();

        $visit->refresh();

        $this->assertSame('suspect', $visit->traffic_class);
        $this->assertSame('datacenter', $visit->class_reasons);
        // بلد Cloudflare يبقى، والاسم يطابقه.
        $this->assertSame('OM', $visit->country_code);
        $this->assertSame('Oman', $visit->country_name);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PageEvent;
use App\Models\PageVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['analytics.owner_key' => 'test-owner-key']);
    }

    public function test_device_link_needs_the_right_key(): void
    {
        $this->get('/analytics/device')->assertNotFound();
        $this->get('/analytics/device?key=wrong')->assertNotFound();

        config(['analytics.owner_key' => null]);
        $this->get('/analytics/device?key=')->assertNotFound();
    }

    public function test_device_link_excludes_and_includes_this_device(): void
    {
        $this->get('/analytics/device?key=test-owner-key&do=exclude')
            ->assertOk()
            ->assertCookie('alyasi_owner', '1')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('هذا الجهاز مستبعد');

        $this->withCookie('alyasi_owner', '1')
            ->get('/analytics/device?key=test-owner-key&do=include')
            ->assertOk()
            ->assertCookieExpired('alyasi_owner')
            ->assertSee('هذا الجهاز ينحسب كزائر');
    }

    public function test_events_inherit_the_visitor_class(): void
    {
        PageVisit::query()->create([
            'path' => '/articles/x', 'ip_address' => '8.8.4.4', 'traffic_class' => 'suspect', 'visitor_id' => 'abcdefghijklmnopqrstuvwxyz',
        ]);

        $this->withCookie('alyasi_vid', 'abcdefghijklmnopqrstuvwxyz')
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Macintosh) AppleWebKit/605.1.15 Version/27.0 Safari/605.1.15', 'Accept-Language' => 'ar'])
            ->post('/analytics/event', [
                'type' => 'audio_play',
                'path' => '/events/cyberx-oman-2026/coverage',
                'label' => 'https://alyasi.dev/audio/coverage/cyberx-oman-2026/report.m4a',
                'seconds' => 12,
            ])
            ->assertNoContent();

        $event = PageEvent::query()->sole();

        $this->assertSame('suspect', $event->traffic_class);
        $this->assertSame('/audio/coverage/cyberx-oman-2026/report.m4a', $event->label);
        $this->assertSame(12, $event->seconds);
    }

    public function test_owner_events_are_marked_owner_and_unknown_types_rejected(): void
    {
        $this->withCookie('alyasi_owner', '1')
            ->post('/analytics/event', ['type' => 'reached_end', 'path' => '/articles/x'])
            ->assertNoContent();

        $this->assertSame('owner', PageEvent::query()->value('traffic_class'));

        $this->post('/analytics/event', ['type' => 'read_confirmed', 'path' => '/x'])
            ->assertStatus(422);

        $this->assertDatabaseCount('page_events', 1);
    }

    public function test_dashboard_shows_clean_and_legacy_numbers_separately(): void
    {
        PageVisit::query()->create(['path' => '/', 'ip_address' => '162.158.28.146', 'created_at' => '2026-09-01 10:00:00']);
        PageVisit::query()->create(['path' => '/', 'ip_address' => '5.36.10.20', 'traffic_class' => 'human', 'visitor_id' => 'abcdefghijklmnopqrstuvwxyz']);
        PageVisit::query()->create(['path' => '/', 'ip_address' => '5.36.10.21', 'traffic_class' => 'owner']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('جودة الزيارات والتفاعل')
            ->assertSee('إحصائيات قديمة غير منقّاة: 1 زيارة')
            ->assertSee('أجهزتك (مستبعدة)');
    }

    public function test_view_beacon_confirms_the_visit_and_unconfirmed_browsers_are_not_counted(): void
    {
        $this->travelTo('2026-10-30 10:00:00');
        $real = 'abcdefghijklmnopqrstuvwxyz';
        PageVisit::query()->create(['path' => '/news', 'ip_address' => '5.36.10.20', 'traffic_class' => 'human', 'visitor_id' => $real, 'country_name' => 'Oman']);
        // روبوت بهوية كروم: زيارة وحدة بدون أي إشارة من المتصفح.
        PageVisit::query()->create(['path' => '/', 'ip_address' => '36.137.1.1', 'traffic_class' => 'human', 'visitor_id' => 'zyxwvutsrqponmlkjihgfedcba', 'country_name' => 'China']);

        $this->withCookie('alyasi_vid', $real)
            ->post('/analytics/event', ['type' => 'view', 'path' => 'https://alyasi.dev/news'])
            ->assertNoContent();

        // بدون كوكي: ما يأكد شي.
        $this->post('/analytics/event', ['type' => 'view', 'path' => '/'])->assertNoContent();

        $this->assertNotNull(PageVisit::query()->where('visitor_id', $real)->value('confirmed_at'));
        $this->assertSame(0, PageEvent::query()->count()); // view مب حدث تفاعل

        $this->assertSame(1, \App\Support\Analytics\VisitStats::humans()['visitors']);
        $byClass = \App\Support\Analytics\VisitStats::byClass();
        $this->assertSame(1, $byClass['human']);
        $this->assertSame(1, $byClass['unconfirmed']);
        $this->assertSame(['Oman'], \App\Support\Analytics\VisitStats::topCountries()->pluck('country_name')->all());
    }

    public function test_visits_before_the_confirmation_date_still_count(): void
    {
        $this->travelTo('2026-10-28 10:00:00');
        PageVisit::query()->create(['path' => '/', 'ip_address' => '5.36.10.20', 'traffic_class' => 'human', 'visitor_id' => 'abcdefghijklmnopqrstuvwxyz']);

        $this->assertSame(1, \App\Support\Analytics\VisitStats::humans()['visitors']);
        $this->assertSame(0, \App\Support\Analytics\VisitStats::byClass()['unconfirmed']);
    }
}

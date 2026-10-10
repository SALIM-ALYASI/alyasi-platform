<?php

namespace Tests\Feature;

use App\Jobs\ResolveVisitCountry;
use App\Models\PageVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_a_public_page_records_a_visit(): void
    {
        Bus::fake();

        $this->get('/')->assertOk();

        $this->assertDatabaseCount('page_visits', 1);

        $visit = PageVisit::query()->first();

        $this->assertSame('/', $visit->path);

        Bus::assertDispatchedAfterResponse(ResolveVisitCountry::class);
    }

    public function test_admin_pages_are_not_tracked(): void
    {
        Queue::fake();

        $this->get(route('admin.login'));

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_bot_requests_are_not_tracked(): void
    {
        Queue::fake();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get('/');

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_post_requests_are_not_tracked(): void
    {
        Queue::fake();

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
        \Illuminate\Support\Facades\Http::fake();

        PageVisit::query()->create(['path' => '/', 'ip_address' => '5.36.0.1', 'country_code' => 'OM', 'country_name' => 'Oman']);
        $visit = PageVisit::query()->create(['path' => '/news', 'ip_address' => '5.36.0.1']);

        (new ResolveVisitCountry($visit->id))->handle();

        $this->assertSame('Oman', $visit->fresh()->country_name);
        \Illuminate\Support\Facades\Http::assertNothingSent();
    }
}

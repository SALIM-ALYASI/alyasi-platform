<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEdition;
use App\Support\Events\OfficialDateChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OfficialEventDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_the_common_official_date_formats(): void
    {
        [$s, $e] = OfficialDateChecker::extractRange('COMEX 2027 | 1 – 4 February 2027 · 10:00 – 21:00 daily', 2027);
        $this->assertSame(['2027-02-01', '2027-02-04'], [$s->toDateString(), $e->toDateString()]);

        [$s, $e] = OfficialDateChecker::extractRange('Dates: 28 Jan - 2 Feb 2027', 2027);
        $this->assertSame(['2027-01-28', '2027-02-02'], [$s->toDateString(), $e->toDateString()]);

        $this->assertNull(OfficialDateChecker::extractRange('Coming soon', 2027));
    }

    public function test_a_changed_official_date_updates_the_edition_and_keeps_the_hours(): void
    {
        Carbon::setTestNow('2026-10-16 10:00:00');
        Http::fake([
            'comex-global.com/*' => Http::response('<h1>COMEX 2027</h1><p>8 – 11 February 2027</p>'),
        ]);

        $event = Event::query()->create(['name' => 'COMEX', 'name_en' => 'COMEX Oman', 'slug' => 'comex-oman']);
        $edition = EventEdition::query()->create([
            'event_id' => $event->id,
            'year' => 2027,
            'title_ar' => 'COMEX عُمان 2027',
            'title_en' => 'COMEX Oman 2027',
            'coverage_type' => 'local_field',
            'date_status' => 'confirmed',
            'event_start_at' => Carbon::parse('2027-02-01 10:00', 'Asia/Muscat')->utc(),
            'event_end_at' => Carbon::parse('2027-02-04 19:00', 'Asia/Muscat')->utc(),
            'official_url' => 'https://comex-global.com/',
            'status' => 'published',
        ]);

        OfficialDateChecker::check($edition);

        $edition->refresh();
        $this->assertSame('2027-02-08 10:00', $edition->event_start_at->timezone('Asia/Muscat')->format('Y-m-d H:i'));
        $this->assertSame('2027-02-11 19:00', $edition->event_end_at->timezone('Asia/Muscat')->format('Y-m-d H:i'));
        $this->assertStringContainsString('تغيّر الموعد', $edition->official_check_note);

        Carbon::setTestNow();
    }
}

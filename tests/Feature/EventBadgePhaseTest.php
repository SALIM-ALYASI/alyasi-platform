<?php

namespace Tests\Feature;

use App\Models\EventEdition;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventBadgePhaseTest extends TestCase
{
    private function edition(array $attributes = []): EventEdition
    {
        return new EventEdition(array_merge([
            'event_start_at' => Carbon::parse('2026-10-12 05:00:00'),
            'event_end_at' => Carbon::parse('2026-10-14 13:00:00'),
            'livestream_url' => null,
            'attended' => false,
        ], $attributes));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_remote_coverage_without_stream_is_not_shown_as_live(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');

        $this->assertSame('live', $this->edition()->phase);
        $this->assertSame('remote', $this->edition()->badge_phase);
    }

    public function test_livestream_or_field_coverage_stays_live(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');

        $this->assertSame('live', $this->edition(['livestream_url' => 'https://youtube.com/live/x'])->badge_phase);
        $this->assertSame('live', $this->edition(['attended' => true])->badge_phase);
    }

    public function test_upcoming_and_concluded_are_unchanged(): void
    {
        Carbon::setTestNow('2026-10-01 10:00:00');
        $this->assertSame('upcoming', $this->edition()->badge_phase);

        Carbon::setTestNow('2026-10-20 10:00:00');
        $this->assertSame('concluded', $this->edition()->badge_phase);
    }
}

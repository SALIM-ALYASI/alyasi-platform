<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEdition;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCoverageTeamTest extends TestCase
{
    use RefreshDatabase;

    private function edition(?string $team): EventEdition
    {
        $event = Event::query()->create(['name' => 'مؤتمر سايسك', 'name_en' => 'CYSEC', 'slug' => 'cysec']);
        $edition = EventEdition::query()->create([
            'event_id' => $event->id, 'year' => 2026, 'title_ar' => 'CYSEC OMAN 2026', 'title_en' => 'CYSEC OMAN 2026',
            'event_start_at' => '2026-11-19 05:00:00', 'event_end_at' => '2026-11-19 13:00:00',
            'status' => 'published', 'published_at' => '2026-10-01 08:00:00', 'coverage_team' => $team,
        ]);
        foreach (['ar', 'en'] as $locale) {
            Permalink::query()->create(['linkable_type' => 'event_edition', 'linkable_id' => $edition->id, 'locale' => $locale, 'slug' => 'cysec-oman-2026']);
        }

        return $edition;
    }

    public function test_team_heading_and_profile_button_show_on_the_conference_page(): void
    {
        $this->travelTo('2026-10-28 12:00:00');
        $this->edition("سالم الحجري | Salem Al Hajri\nمروة ماهر | Marwa Maher | https://www.instagram.com/1almarwa/");

        $this->get('/events/cysec-oman-2026')->assertOk()
            ->assertSee('تابعوا المؤتمر مع تغطية سالم الحجري ومروة ماهر')
            ->assertSee('href="https://www.instagram.com/1almarwa/"', false)
            ->assertSee('حساب مروة ماهر على إنستغرام')
            ->assertDontSee('حساب سالم الحجري'); // ما له رابط = بدون زر

        $this->get('/en/events/cysec-oman-2026')->assertOk()
            ->assertSee('Follow the conference with coverage by Salem Al Hajri and Marwa Maher')
            ->assertSee('Marwa Maher on Instagram');
    }

    public function test_no_team_means_no_section_and_unsafe_links_are_dropped(): void
    {
        $this->travelTo('2026-10-28 12:00:00');
        $this->edition(null);
        $this->get('/events/cysec-oman-2026')->assertOk()->assertDontSee('تابعوا المؤتمر مع تغطية');

        $edition = EventEdition::query()->first();
        $edition->update(['coverage_team' => 'شخص | Person | javascript:alert(1)']);
        $this->get('/events/cysec-oman-2026')->assertOk()
            ->assertSee('تابعوا المؤتمر مع تغطية شخص')
            ->assertDontSee('javascript:alert', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEdition;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventStructureTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15',
            'Accept-Language' => 'ar',
        ];
    }

    private function edition(string $seriesSlug, string $slug, string $start, string $end): EventEdition
    {
        $event = Event::query()->create(['name' => 'مؤتمر '.$seriesSlug, 'name_en' => $seriesSlug, 'slug' => $seriesSlug]);

        $edition = EventEdition::query()->create([
            'event_id' => $event->id,
            'year' => 2026,
            'title_ar' => 'نسخة '.$slug,
            'title_en' => 'Edition '.$slug,
            'coverage_type' => 'global_remote',
            'date_status' => 'confirmed',
            'event_start_at' => $start,
            'event_end_at' => $end,
            'content_ar' => '<p>محتوى التغطية</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        foreach (['ar', 'en'] as $locale) {
            Permalink::query()->create(['linkable_type' => 'event_edition', 'linkable_id' => $edition->id, 'locale' => $locale, 'slug' => $slug]);
        }

        return $edition;
    }

    public function test_concluded_edition_shows_only_coverage_without_event_info(): void
    {
        $this->edition('past-conf', 'past-conf-2026', now()->subDays(10), now()->subDays(9));

        $this->withHeaders($this->headers())->get('/events/past-conf-2026')
            ->assertOk()
            ->assertSee('محتوى التغطية')
            ->assertDontSee('community-detail__info-grid', false)
            ->assertDontSee(__('events.held_on'));
    }

    public function test_upcoming_edition_still_shows_event_info(): void
    {
        $this->edition('next-conf', 'next-conf-2027', now()->addMonths(3), now()->addMonths(3)->addDays(2));

        $this->withHeaders($this->headers())->get('/events/next-conf-2027')
            ->assertOk()
            ->assertSee('community-detail__info-grid', false)
            ->assertSee(__('events.date_status.confirmed'));
    }

    public function test_series_page_lists_year_cards_only(): void
    {
        $this->edition('cyberx-oman', 'cyberx-like-2026', now()->subDays(10), now()->subDays(9));

        $this->withHeaders($this->headers())->get('/events/cyberx-oman')
            ->assertOk()
            ->assertSee('نسخة cyberx-like-2026')
            ->assertDontSee('coverage-links', false);
    }

    public function test_series_cover_only_on_list_card_and_posters_on_year_cards(): void
    {
        $edition = $this->edition('cysec-like', 'cysec-like-2026', now()->addMonth(), now()->addMonth()->addHours(8));
        $edition->event->update(['image' => 'uploads/events/partnership.jpg']);
        $edition->update(['image' => 'uploads/events/poster-ar.jpg', 'image_en' => 'uploads/events/poster-en.jpg']);

        $this->withHeaders($this->headers())->get('/events')
            ->assertOk()
            ->assertSee('uploads/events/partnership.jpg', false);

        $this->withHeaders($this->headers())->get('/events/cysec-like')
            ->assertOk()
            ->assertSee('uploads/events/poster-ar.jpg', false)
            ->assertDontSee('uploads/events/partnership.jpg', false);

        $this->withHeaders(['Accept-Language' => 'en'] + $this->headers())->get('/en/events/cysec-like')
            ->assertOk()
            ->assertSee('uploads/events/poster-en.jpg', false)
            ->assertDontSee('uploads/events/partnership.jpg', false);
    }

    public function test_english_conference_list_exists_and_old_community_links_follow_language(): void
    {
        $this->edition('any-conf', 'any-conf-2026', now()->subDays(10), now()->subDays(9));
        $en = ['Accept-Language' => 'en'] + $this->headers();

        $this->withHeaders($en)->get('/en/events')
            ->assertOk()
            ->assertSee('any-conf')
            ->assertSee('href="'.url('/en/events').'"', false);

        $this->withHeaders($en)->get('/en/community')->assertRedirect(url('/en/events'))->assertStatus(301);
        $this->withHeaders($this->headers())->get('/community')->assertRedirect(url('/events'));
    }
}

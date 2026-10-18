<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEdition;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsHubTest extends TestCase
{
    use RefreshDatabase;

    private function edition(string $series, string $slug, string $start, string $end, array $extra = []): EventEdition
    {
        $event = Event::query()->create(['name' => 'مؤتمر '.$series, 'name_en' => $series.' Conf', 'slug' => $series]);
        $edition = EventEdition::query()->create($extra + [
            'event_id' => $event->id,
            'year' => (int) substr($start, 0, 4),
            'title_ar' => 'نسخة '.$series,
            'title_en' => $series.' edition',
            'event_start_at' => $start,
            'event_end_at' => $end,
            'status' => 'published',
            'published_at' => '2026-09-01 08:00:00',
        ]);

        foreach (['ar', 'en'] as $locale) {
            Permalink::query()->create(['linkable_type' => 'event_edition', 'linkable_id' => $edition->id, 'locale' => $locale, 'slug' => $slug]);
        }

        return $edition;
    }

    public function test_hub_splits_upcoming_and_past_in_date_order_with_event_dates(): void
    {
        $this->travelTo('2026-10-20 12:00:00');

        $this->edition('later', 'later-2027', '2027-02-01 06:00:00', '2027-02-04 15:00:00');
        $this->edition('sooner', 'sooner-2026', '2026-11-19 05:00:00', '2026-11-19 13:00:00');
        $this->edition('older', 'older-2026', '2026-09-02 13:00:00', '2026-09-02 15:00:00');
        $this->edition('recent', 'recent-2026', '2026-10-06 04:00:00', '2026-10-06 11:30:00');

        $this->get('/events')
            ->assertOk()
            // القادمة (الأقرب أول) قبل المنتهية (الأحدث أول).
            ->assertSeeInOrder(['فعاليات قادمة', 'مؤتمر sooner', 'مؤتمر later', 'فعاليات منتهية', 'مؤتمر recent', 'مؤتمر older'])
            // موعد الانعقاد بتوقيت مسقط، وبالعربي «إلى» بدل الشرطة.
            ->assertSee('1 إلى 4 فبراير 2027')
            ->assertSee('/events/later-2027', false)
            ->assertDontSee('Samsung');

        $this->get('/en/events')
            ->assertOk()
            ->assertSee('1–4 February 2027')
            ->assertSee('Upcoming events');
    }

    public function test_edition_page_separates_event_date_from_publish_date_and_has_event_schema(): void
    {
        $this->travelTo('2026-10-20 12:00:00');
        $this->edition('cysec-oman', 'cysec-oman-2026', '2026-11-19 05:00:00', '2026-11-19 13:00:00');

        $this->get('/events/cysec-oman-2026')
            ->assertOk()
            ->assertSeeInOrder(['موعد المؤتمر', '19 نوفمبر 2026', 'نُشرت الصفحة', '1 سبتمبر 2026'])
            ->assertSee('"@type":"Event"', false)
            ->assertSee('Sheraton Oman Hotel', false);

        // نسخة مكانها غير مؤكد: بدون بيانات Event.
        $this->edition('unknown', 'unknown-2026', '2026-12-01 05:00:00', '2026-12-01 13:00:00');
        $this->get('/events/unknown-2026')->assertOk()->assertDontSee('"@type":"Event"', false);
    }

    public function test_apple_archive_card_shows_the_event_day_in_muscat_time(): void
    {
        // 22:00 بمسقط = 18:00 غرينتش: البطاقة تبقى 9 سبتمبر مب 10.
        $this->edition('apple', 'apple-event-2026', '2026-09-09 18:00:00', '2026-09-09 18:51:00');

        $this->get('/events/apple')
            ->assertOk()
            ->assertSee('9 سبتمبر 2026')
            ->assertDontSee('10.09.2026');
    }
}

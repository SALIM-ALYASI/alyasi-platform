<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEdition;
use App\Models\EventInterview;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsFeaturedTest extends TestCase
{
    use RefreshDatabase;

    private function edition(string $series, string $start, array $extra = []): EventEdition
    {
        $event = Event::query()->create(['name' => 'مؤتمر '.$series, 'name_en' => $series, 'slug' => $series]);
        $edition = EventEdition::query()->create($extra + [
            'event_id' => $event->id, 'year' => 2026, 'title_ar' => 'نسخة '.$series, 'title_en' => $series.' 2026',
            'event_start_at' => $start, 'event_end_at' => $start, 'status' => 'published', 'published_at' => '2026-09-01 08:00:00',
        ]);
        foreach (['ar', 'en'] as $locale) {
            Permalink::query()->create(['linkable_type' => 'event_edition', 'linkable_id' => $edition->id, 'locale' => $locale, 'slug' => $series.'-2026']);
        }

        return $edition;
    }

    public function test_featured_is_the_latest_published_exclusive_coverage_not_the_newest_event(): void
    {
        $this->travelTo('2026-10-20 12:00:00');

        // مؤتمر أحدث بالتاريخ بس بدون محتوى حصري منشور: ما يصير مميز.
        $this->edition('newer', '2026-10-15 05:00:00', ['attended' => true]);
        // شريك إعلامي قادم بدون تغطية: شارة الشراكة بس.
        $this->edition('partner', '2026-11-19 05:00:00', ['is_media_partner' => true]);
        $field = $this->edition('field', '2026-10-06 05:00:00', [
            'attended' => true, 'coverage_url' => '/events/field-2026/coverage', 'interviews_url' => '/events/field-2026/interviews',
            'exclusive_published_at' => '2026-10-08 04:00:00',
        ]);
        EventInterview::query()->create(['event_edition_id' => $field->id, 'slug' => 'guest', 'name_ar' => 'ضيف', 'is_published' => true, 'published_at' => '2026-10-10 04:00:00']);
        EventInterview::query()->create(['event_edition_id' => $field->id, 'slug' => 'draft', 'name_ar' => 'مسودة', 'is_published' => false]);

        $response = $this->get('/events')->assertOk()
            ->assertSeeInOrder(['تغطية مميزة', 'نسخة field', 'تغطية ميدانية', 'مقابلة حصرية', 'فعاليات قادمة'])
            ->assertSee(url('/events/field-2026/coverage'), false)
            ->assertSee(url('/events/field-2026/interviews'), false);

        // «newer»: حضرنا بس ما نُشرت التغطية -- بدون شارة «تغطية ميدانية».
        $html = $response->getContent();
        $newerCard = substr($html, strpos($html, 'مؤتمر newer'), 1500);
        $this->assertStringNotContainsString('تغطية ميدانية', $newerCard);

        $partnerCard = substr($html, strpos($html, 'مؤتمر partner'), 1500);
        $this->assertStringContainsString('شريك إعلامي', $partnerCard);
        $this->assertStringNotContainsString('مقابلة حصرية', $partnerCard);

        // الإنجليزي: الرابط الداخلي ينضاف له /en.
        $this->get('/en/events')->assertOk()->assertSee(url('/en/events/field-2026/coverage'), false);
    }

    public function test_manual_pin_overrides_the_automatic_choice(): void
    {
        $this->travelTo('2026-10-20 12:00:00');
        $this->edition('auto', '2026-10-06 05:00:00', ['attended' => true, 'exclusive_published_at' => '2026-10-08 04:00:00']);
        $this->edition('pinned', '2026-09-02 05:00:00', ['is_featured' => true]);

        $this->get('/events')->assertOk()->assertSeeInOrder(['تغطية مميزة', 'نسخة pinned', 'فعاليات منتهية']);
    }

    public function test_home_and_events_hub_show_the_same_featured_coverage(): void
    {
        $this->travelTo('2026-10-20 12:00:00');
        $this->edition('auto', '2026-10-06 05:00:00', ['attended' => true, 'exclusive_published_at' => '2026-10-08 04:00:00']);
        $this->edition('pinned', '2026-09-02 05:00:00', ['is_featured' => true]);
        $this->edition('partner', '2026-11-19 05:00:00', ['is_media_partner' => true]);

        $this->get('/')->assertOk()->assertSeeInOrder(['تغطية مميزة', 'نسخة pinned', 'المؤتمرات والمقابلات', 'نسخة partner']);
        $this->get('/en')->assertOk()->assertSeeInOrder(['Featured coverage', 'pinned 2026', 'Conferences &amp; interviews', 'Media partner', 'partner 2026'], false);
    }

    public function test_goals_show_on_the_edition_page(): void
    {
        $this->travelTo('2026-10-20 12:00:00');
        $this->edition('goals', '2026-11-19 05:00:00', ['goals_ar' => "- مقابلات مع المتحدثين\nنقل الجلسات للجمهور العُماني"]);

        $this->get('/events/goals-2026')->assertOk()
            ->assertSeeInOrder(['أهداف ALYASI من هذه التغطية', 'مقابلات مع المتحدثين', 'نقل الجلسات للجمهور العُماني']);
    }
}

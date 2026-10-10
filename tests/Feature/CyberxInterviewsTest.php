<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CyberxInterviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_lists_only_published_guests_in_both_languages(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews')
            ->assertOk()
            ->assertSee('د. هيثم الحجري')
            ->assertSee(url('/events/cyberx-oman-2026/interviews/haitham-al-hajri'), false)
            ->assertDontSee('يحيى العزري')
            ->assertDontSee('قريبًا');

        $this->get('/en/events/cyberx-oman-2026/interviews')
            ->assertOk()
            ->assertSee('Dr. Haitham Al Hajri')
            ->assertDontSee('In Arabic')
            ->assertSee('href="'.url('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri').'"', false)
            ->assertDontSee('Coming soon');
    }

    public function test_interview_page_shows_questions_timed_answers_and_audio(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews/haitham-al-hajri')
            ->assertOk()
            ->assertSee('كيف يوازن البنك بين تسهيل الخدمات الرقمية للعملاء وحمايتها؟')
            ->assertSee('data-t="3.28"', false)
            ->assertSee('haitham\/a1.m4a', false)
            ->assertSee('href="'.url('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri').'" data-lang-choice="en"', false)
            ->assertSee('haitham-al-hajri-og.jpg', false);
    }

    public function test_english_page_shows_the_guests_own_written_answers(): void
    {
        $this->get('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri')
            ->assertOk()
            ->assertSee('Thank you, Salem, for this important question.')
            ->assertSee('AI-generated voice, with Dr. Haitham’s consent')
            ->assertSee('Original recording in Arabic')
            ->assertSee('haitham\/en-a1.m4a', false)
            ->assertSee('haitham\/a1.m4a', false);
    }

    public function test_coverage_page_renders_with_audio_report_cues(): void
    {
        $this->get('/events/cyberx-oman-2026/coverage')
            ->assertOk()
            ->assertSee('coverageListenPlay', false)
            ->assertSee('data-cue="0"', false)
            ->assertSee('coverageDock', false);

        $this->get('/en/events/cyberx-oman-2026/coverage')->assertOk();
    }

    public function test_unpublished_or_unknown_guest_is_not_found(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews/yahya-al-azri')->assertNotFound();
        $this->get('/events/cyberx-oman-2026/interviews/nobody')->assertNotFound();
    }
}

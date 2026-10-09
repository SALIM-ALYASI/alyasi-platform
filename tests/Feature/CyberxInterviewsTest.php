<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CyberxInterviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_lists_published_and_upcoming_guests_in_both_languages(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews')
            ->assertOk()
            ->assertSee('د. هيثم الحجري')
            ->assertSee(url('/events/cyberx-oman-2026/interviews/haitham-al-hajri'), false)
            ->assertSee('قريبًا');

        $this->get('/en/events/cyberx-oman-2026/interviews')
            ->assertOk()
            ->assertSee('Dr. Haitham Al Hajri')
            ->assertSee('In Arabic')
            ->assertSee('href="'.url('/events/cyberx-oman-2026/interviews/haitham-al-hajri').'"', false)
            ->assertSee('Coming soon');
    }

    public function test_interview_page_shows_questions_timed_answers_and_audio(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews/haitham-al-hajri')
            ->assertOk()
            ->assertSee('كيف يوازن البنك بين تسهيل الخدمات الرقمية للعملاء وحمايتها؟')
            ->assertSee('data-t="3.28"', false)
            ->assertSee('haitham\/a1.m4a', false)
            ->assertDontSee('data-lang-choice="en"', false);
    }

    public function test_arabic_only_interview_sends_english_links_to_the_arabic_page(): void
    {
        $this->get('/en/events/cyberx-oman-2026/interviews/haitham-al-hajri')
            ->assertRedirect(url('/events/cyberx-oman-2026/interviews/haitham-al-hajri'));
    }

    public function test_unpublished_or_unknown_guest_is_not_found(): void
    {
        $this->get('/events/cyberx-oman-2026/interviews/yahya-al-azri')->assertNotFound();
        $this->get('/events/cyberx-oman-2026/interviews/nobody')->assertNotFound();
    }
}

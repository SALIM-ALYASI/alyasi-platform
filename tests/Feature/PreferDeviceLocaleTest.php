<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferDeviceLocaleTest extends TestCase
{
    use RefreshDatabase;

    private const AR = '/events/cyberx-oman-2026/coverage';

    private const EN = '/en/events/cyberx-oman-2026/coverage';

    private const SAFARI = 'Mozilla/5.0 (iPhone; CPU iPhone OS 19_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/19.0 Mobile/15E148 Safari/604.1';

    public function test_english_device_is_sent_to_the_english_page(): void
    {
        $this->withHeaders(['Accept-Language' => 'en-GB,en;q=0.9', 'User-Agent' => self::SAFARI])
            ->get(self::AR)
            ->assertRedirect(url(self::EN));
    }

    public function test_arabic_device_is_sent_to_the_arabic_page(): void
    {
        $this->withHeaders(['Accept-Language' => 'ar-OM,ar;q=0.9,en;q=0.8', 'User-Agent' => self::SAFARI])
            ->get(self::EN)
            ->assertRedirect(url(self::AR));

        $this->withHeaders(['Accept-Language' => 'ar-OM', 'User-Agent' => self::SAFARI])
            ->get(self::AR)
            ->assertOk();
    }

    public function test_saved_choice_wins_over_device_language(): void
    {
        $this->withUnencryptedCookie('alyasi_lang', 'ar')
            ->withHeaders(['Accept-Language' => 'en-US', 'User-Agent' => self::SAFARI])
            ->get(self::AR)
            ->assertOk();
    }

    public function test_search_engines_are_never_redirected(): void
    {
        $this->withHeaders(['Accept-Language' => 'en-US', 'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get(self::AR)
            ->assertOk();
    }

    public function test_query_string_is_kept(): void
    {
        $this->withHeaders(['Accept-Language' => 'en', 'User-Agent' => self::SAFARI])
            ->get(self::AR.'?utm_source=whatsapp')
            ->assertRedirect(url(self::EN).'?utm_source=whatsapp');
    }

    public function test_locale_switch_route_saves_the_choice(): void
    {
        $this->get('/locale/en?redirect='.urlencode(self::EN))
            ->assertRedirect(self::EN)
            ->assertCookie('alyasi_lang', 'en', false);
    }
}

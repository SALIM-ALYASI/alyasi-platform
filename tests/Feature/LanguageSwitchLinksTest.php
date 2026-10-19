<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * زر AR/EN يودّي لنسخة نفس الصفحة باللغة الثانية، مو /locale/{locale}
 * (اللي يرجّع الزائر لنفس الرابط بنفس اللغة).
 */
class LanguageSwitchLinksTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            'founder' => ['/about/founder', '/en/about/founder'],
            'events' => ['/events', '/en/events'],
            'oman pulse' => ['/news/oman', '/en/news/oman'],
        ];
    }

    #[DataProvider('pages')]
    public function test_switcher_links_to_the_other_language_version(string $ar, string $en): void
    {
        $this->get($ar)->assertOk()->assertSee('href="'.url($en).'"', false);
        $this->get($en)->assertOk()->assertSee('href="'.url($ar).'"', false);
    }

    public function test_events_year_filter_survives_the_switch(): void
    {
        $this->get('/events?year=2026')->assertOk()->assertSee('href="'.url('/en/events?year=2026').'"', false);
    }
}

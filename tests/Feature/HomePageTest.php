<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الرئيسية: الإعلام التقني أولًا ثم الخدمات، باللغتين.
 */
class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_home_leads_with_media_and_links_correctly(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('<title>ALYASI — Omani Tech Media &amp; Digital Solutions</title>', false)
            ->assertSee('Technology Stories Worth Telling.')
            ->assertSee('Explore Our Coverage')
            ->assertSee('href="'.url('/en/events').'"', false)
            ->assertSee('href="'.url('/en/services').'"', false)
            ->assertSee('href="'.url('/en/about/founder').'"', false)
            ->assertSee('hreflang="ar"', false)
            ->assertDontSee('We build your website or system');
    }

    public function test_arabic_home_has_the_same_structure(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('قصص تقنية تستحق أن تُروى')
            ->assertSee('استكشف تغطياتنا')
            ->assertSee('href="'.url('/events').'"', false)
            ->assertSee('href="'.url('/about/founder').'"', false)
            ->assertSee('تعرّف على المؤسس');
    }

    public function test_home_shows_four_core_services_and_links_to_all(): void
    {
        foreach (range(1, 6) as $i) {
            Service::query()->create([
                'title_ar' => "خدمة {$i}", 'title_en' => "Service {$i}",
                'description_ar' => 'وصف', 'description_en' => 'Description',
                'is_active' => true, 'sort_order' => $i * 10, 'price_from' => 10,
            ]);
        }

        $response = $this->get('/en')->assertOk();

        foreach (range(1, 4) as $i) {
            $response->assertSee("Service {$i}");
        }
        $response->assertDontSee('Service 5')->assertSee('(6)');
    }
}

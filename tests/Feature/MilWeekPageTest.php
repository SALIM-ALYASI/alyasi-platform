<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * صفحة مبادرة «قبل أن تشارك الخبر… تحقّق» (رابطها في استمارة اليونسكو): لازم تشتغل باللغتين.
 */
class MilWeekPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_page_renders_with_seo_and_disclaimer(): void
    {
        $response = $this->get('/mil-week-2026')->assertOk();
        $flat = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertStringContainsString('rel="canonical" href="'.url('/mil-week-2026').'"', $flat);
        $this->assertStringContainsString('hreflang="en" href="'.url('/en/mil-week-2026').'"', $flat);

        $response
            ->assertSee('dir="rtl"', false)
            ->assertSee('قبل أن تشارك الخبر… تحقّق')
            ->assertSee('خمس خطوات قبل مشاركة أي خبر')
            ->assertSee('بدية، محافظة شمال الشرقية')
            ->assertSee('26–31 أكتوبر 2026')
            ->assertSee('ولا تمثل شراكة أو اعتمادًا رسميًا من اليونسكو')
            ->assertSee('images/initiatives/mil-week-2026-og-ar.jpg', false)
            ->assertSee('"@type":"Event"', false)
            ->assertSee('قريبًا');
    }

    public function test_english_page_renders_ltr_and_switches_back(): void
    {
        $this->get('/en/mil-week-2026')->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Verify Before You Share')
            ->assertSee('Bidiyah, North Al Sharqiyah Governorate')
            ->assertSee('does not represent a partnership with, or official endorsement by, UNESCO')
            ->assertSee('href="'.url('/mil-week-2026').'" hreflang="ar"', false)
            ->assertSee('images/initiatives/mil-week-2026-og-en.jpg', false);
    }

    public function test_material_becomes_a_link_once_its_url_is_set(): void
    {
        Setting::set('mil_week_2026_article_url', 'https://alyasi.dev/articles/verify-news-ai');

        $this->get('/mil-week-2026')->assertOk()
            ->assertSee('href="https://alyasi.dev/articles/verify-news-ai"', false);
    }

    public function test_youtube_video_is_embedded_once_its_url_is_set(): void
    {
        $this->get('/mil-week-2026')->assertOk()->assertDontSee('youtube-nocookie.com/embed', false);

        Setting::set('mil_week_2026_video_url', 'https://youtube.com/shorts/kw0oZk_ERfA');

        $this->get('/mil-week-2026')->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/kw0oZk_ERfA', false)
            ->assertSee('الفيديو يتضمن مشاهد تمثيلية مُنشأة بالذكاء الاصطناعي.');

        $this->get('/en/mil-week-2026')->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/kw0oZk_ERfA', false)
            ->assertSee('This video includes AI-generated dramatized scenes.');
    }

    public function test_non_youtube_video_link_is_not_embedded(): void
    {
        Setting::set('mil_week_2026_video_url', 'https://example.com/watch?v=kw0oZk_ERfA');

        $this->get('/mil-week-2026')->assertOk()->assertDontSee('youtube-nocookie.com/embed', false);
    }
}

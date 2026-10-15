<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsAnalysisLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function headers(string $lang): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15',
            'Accept-Language' => $lang,
        ];
    }

    private function article(): NewsArticle
    {
        $category = NewsCategory::query()->create(['name_ar' => 'ذكاء اصطناعي', 'name_en' => 'AI', 'slug' => 'ai', 'is_active' => true]);

        $article = NewsArticle::factory()->create([
            'news_category_id' => $category->id,
            'publication_date' => '2026-10-02',
            'daily_sequence' => 1,
            'analysis_status' => 'ready',
            'analysis_title_ar' => 'لماذا يهم هذا الخبر',
            'analysis_ar' => 'نص التحليل العربي الكامل',
            'analysis_regional_angle_ar' => 'أثره على السوق الخليجي',
            'youtube_video_url' => 'https://youtu.be/example',
        ]);

        foreach (['ar' => 'story', 'en' => 'story-en'] as $locale => $slug) {
            Permalink::query()->create(['linkable_type' => 'news_article', 'linkable_id' => $article->id, 'locale' => $locale, 'slug' => $slug]);
        }

        return $article;
    }

    public function test_english_page_links_to_arabic_analysis_instead_of_showing_it(): void
    {
        $this->article();

        $this->withHeaders($this->headers('en'))->get('/en/news/2026/10/02/001/story-en')
            ->assertOk()
            ->assertSee('ALYASI Analysis')
            ->assertSee('Read the analysis in Arabic')
            ->assertSee(url('/news/2026/10/02/001/story').'#analysis', false)
            ->assertSee('Watch the news video')
            ->assertDontSee('نص التحليل العربي الكامل')
            ->assertDontSee('زاوية الخليج')
            ->assertDontSee('شاهد فيديو الخبر');
    }

    public function test_arabic_page_still_shows_full_analysis(): void
    {
        $this->article();

        $this->withHeaders($this->headers('ar'))->get('/news/2026/10/02/001/story')
            ->assertOk()
            ->assertSee('تحليل ALYASI')
            ->assertSee('نص التحليل العربي الكامل')
            ->assertSee('زاوية الخليج')
            ->assertSee('شاهد فيديو الخبر')
            ->assertDontSee('Read the analysis in Arabic');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechHistoryLocaleTest extends TestCase
{
    use RefreshDatabase;

    private function makeEpisode(): Article
    {
        $category = ArticleCategory::query()->create(['name_ar' => 'تاريخ التقنية', 'name_en' => 'Tech History', 'slug' => 'tech-history', 'is_active' => true]);

        $article = Article::query()->create([
            'article_category_id' => $category->id,
            'title_ar' => 'التلفزيون',
            'title_en' => 'Television',
            'content_ar' => '<p>نص عربي</p>',
            'content_en' => '<p>English text</p>',
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        Permalink::query()->create(['linkable_type' => 'article', 'linkable_id' => $article->id, 'locale' => 'ar', 'slug' => 'tv-ar']);
        Permalink::query()->create(['linkable_type' => 'article', 'linkable_id' => $article->id, 'locale' => 'en', 'slug' => 'television']);

        return $article;
    }

    public function test_english_index_and_episode_render_in_english(): void
    {
        $this->makeEpisode();

        $this->get('/en/tech-history')
            ->assertOk()
            ->assertSee('Television')
            ->assertSee(url('/en/tech-history/television'), false);

        $this->get('/en/tech-history/television')
            ->assertOk()
            ->assertSee('English text', false)
            ->assertDontSee('نص عربي', false);
    }

    public function test_language_switch_stays_inside_tech_history(): void
    {
        $this->makeEpisode();

        $this->get('/tech-history/tv-ar')
            ->assertOk()
            ->assertSee('href="'.url('/en/tech-history/television').'" data-lang-choice="en"', false);

        $this->get('/tech-history')
            ->assertSee('href="'.url('/en/tech-history').'" data-lang-choice="en"', false);
    }
}

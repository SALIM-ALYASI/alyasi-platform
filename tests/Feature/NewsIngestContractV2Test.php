<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsIngestContractV2Test extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title_en' => 'Complete English title',
            'title_ar' => 'عنوان عربي كامل للخبر',
            'content_en' => '<p>This is a complete English article body for the ingest contract test.</p>',
            'content_ar' => '<p>هذا نص عربي كامل لاختبار عقد استقبال الأخبار الصارم في المنصة.</p>',
            'category_slug' => 'artificial-intelligence',
            'slug' => 'complete-news-contract-test',
            'link' => 'https://example.com/source-story',
            'image' => 'https://example.com/news.jpg',
            'source' => 'Example News',
            'author' => 'Example Author',
            'published_at' => '2026-08-23T02:00:00Z',
            'is_published' => true,
        ], $overrides);
    }

    private function createCategory(): NewsCategory
    {
        return NewsCategory::query()->create([
            'name_ar' => 'الذكاء الاصطناعي',
            'name_en' => 'Artificial Intelligence',
            'slug' => 'artificial-intelligence',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_incomplete_payload_is_rejected_before_article_is_saved(): void
    {
        $this->createCategory();

        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/news', $this->payload(['image' => null]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);

        $this->assertDatabaseCount('news_articles', 0);
    }

    public function test_category_must_exist_and_be_active(): void
    {
        NewsCategory::query()->create([
            'name_ar' => 'غير نشط',
            'name_en' => 'Inactive',
            'slug' => 'inactive-category',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/news', $this->payload([
                'category_slug' => 'inactive-category',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_slug']);

        $this->assertDatabaseCount('news_articles', 0);
    }

    public function test_complete_payload_saves_category_and_requested_slug(): void
    {
        $category = $this->createCategory();

        $response = $this
            ->withoutMiddleware()
            ->postJson('/api/news', $this->payload());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('slug', 'complete-news-contract-test')
            ->assertJsonPath('category_slug', 'artificial-intelligence');

        $article = NewsArticle::query()->firstOrFail();

        $this->assertSame($category->id, $article->news_category_id);
        $this->assertSame('Example Author', $article->author_name);
        $this->assertSame('https://example.com/news.jpg', $article->image);

        $this->assertDatabaseHas('permalinks', [
            'linkable_type' => 'news_article',
            'linkable_id' => $article->id,
            'locale' => 'ar',
            'slug' => 'complete-news-contract-test',
        ]);

        $this->assertDatabaseHas('permalinks', [
            'linkable_type' => 'news_article',
            'linkable_id' => $article->id,
            'locale' => 'en',
            'slug' => 'complete-news-contract-test',
        ]);
    }
}

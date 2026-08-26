<?php

namespace Tests\Feature\Api;

use App\Models\NewsArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class NewsIngestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.news_bot.token', 'test-secret-token');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title_en' => 'Example headline',
            'title_ar' => 'عنوان تجريبي',
            'content_en' => '<p>Body</p><script>alert(1)</script>',
            'content_ar' => '<p>محتوى</p><script>alert(1)</script>',
            'link' => 'https://example.com/article-1',
            'source' => 'TechCrunch',
        ], $overrides);
    }

    public function test_request_without_token_is_rejected(): void
    {
        $this->postJson('/api/new', $this->payload())
            ->assertStatus(401);

        $this->assertDatabaseCount('news_articles', 0);
    }

    public function test_request_with_wrong_token_is_rejected(): void
    {
        $this->withHeader('Authorization', 'Bearer wrong-token')
            ->postJson('/api/new', $this->payload())
            ->assertStatus(401);

        $this->assertDatabaseCount('news_articles', 0);
    }

    public function test_valid_request_creates_article(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer test-secret-token')
            ->postJson('/api/new', $this->payload());

        $response->assertOk()->assertJson([
            'success' => true,
            'created' => true,
        ]);

        $this->assertDatabaseCount('news_articles', 1);
    }

    public function test_script_tags_are_stripped_from_content(): void
    {
        $this->withHeader('Authorization', 'Bearer test-secret-token')
            ->postJson('/api/new', $this->payload())
            ->assertOk();

        $article = NewsArticle::query()->firstOrFail();

        $this->assertStringNotContainsString('<script>', $article->content_en);
        $this->assertStringNotContainsString('<script>', $article->content_ar);
    }

    public function test_repeated_link_updates_existing_article_instead_of_duplicating(): void
    {
        $this->withHeader('Authorization', 'Bearer test-secret-token')
            ->postJson('/api/new', $this->payload())
            ->assertOk();

        $response = $this->withHeader('Authorization', 'Bearer test-secret-token')
            ->postJson('/api/new', $this->payload([
                'title_en' => 'Updated headline',
            ]));

        $response->assertOk()->assertJson([
            'success' => true,
            'created' => false,
        ]);

        $this->assertDatabaseCount('news_articles', 1);
        $this->assertSame(
            'Updated headline',
            NewsArticle::query()->firstOrFail()->title_en
        );
    }

    public function test_legacy_news_endpoint_remains_available(): void
    {
        $this->withHeader('Authorization', 'Bearer test-secret-token')
            ->postJson('/api/news', $this->payload())
            ->assertOk()
            ->assertJson([
                'success' => true,
                'created' => true,
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsMergeTest extends TestCase
{
    use RefreshDatabase;

    private function headers(string $lang = 'ar'): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Safari/605.1.15',
            'Accept-Language' => $lang,
        ];
    }

    private function article(int $sequence, string $slug, int $views = 0): NewsArticle
    {
        $category = NewsCategory::query()->firstOrCreate(
            ['slug' => 'ai'],
            ['name_ar' => 'ذكاء اصطناعي', 'name_en' => 'AI', 'is_active' => true]
        );

        $article = NewsArticle::factory()->create([
            'news_category_id' => $category->id,
            'publication_date' => '2026-10-02',
            'daily_sequence' => $sequence,
            'published_at' => '2026-10-02 06:30:00',
            'views_count' => $views,
        ]);

        foreach (['ar' => $slug, 'en' => $slug.'-en'] as $locale => $value) {
            Permalink::query()->create([
                'linkable_type' => 'news_article',
                'linkable_id' => $article->id,
                'locale' => $locale,
                'slug' => $value,
            ]);
        }

        return $article;
    }

    public function test_merged_duplicate_redirects_to_original_in_both_languages(): void
    {
        $original = $this->article(1, 'white-house-accord', 30);
        $duplicate = $this->article(2, 'accord-self-regulation', 25);

        $this->artisan('news:merge', ['duplicate' => $duplicate->id, 'original' => $original->id])
            ->assertSuccessful();

        $this->assertSoftDeleted($duplicate);
        $this->assertSame($original->id, $duplicate->fresh()?->merged_into_id ?? NewsArticle::withTrashed()->find($duplicate->id)->merged_into_id);
        $this->assertSame(55, $original->fresh()->views_count);

        $this->withHeaders($this->headers('ar'))
            ->get('/news/2026/10/02/002/accord-self-regulation')
            ->assertRedirect(url('/news/2026/10/02/001/white-house-accord'))
            ->assertStatus(301);

        $this->withHeaders($this->headers('ar'))
            ->get('/news/accord-self-regulation')
            ->assertRedirect(url('/news/2026/10/02/001/white-house-accord'));

        $this->withHeaders($this->headers('en'))
            ->get('/en/news/2026/10/02/002/accord-self-regulation-en')
            ->assertRedirect(url('/en/news/2026/10/02/001/white-house-accord-en'));
    }

    public function test_plain_deleted_article_still_returns_gone(): void
    {
        $article = $this->article(3, 'removed-story');
        $article->delete();

        $this->withHeaders($this->headers('ar'))
            ->get('/news/2026/10/02/003/removed-story')
            ->assertStatus(410);
    }

    public function test_merge_refuses_self_and_chained_targets(): void
    {
        $a = $this->article(4, 'story-a');
        $b = $this->article(5, 'story-b');
        $c = $this->article(6, 'story-c');

        $this->artisan('news:merge', ['duplicate' => $a->id, 'original' => $a->id])->assertFailed();

        $this->artisan('news:merge', ['duplicate' => $b->id, 'original' => $a->id])->assertSuccessful();
        // a ما انحذف؛ دمج c في b (المدموج) مرفوض لأن b محذوف.
        $this->artisan('news:merge', ['duplicate' => $c->id, 'original' => $b->id])->assertFailed();

        $this->artisan('news:merge', ['duplicate' => $c->id, 'original' => $a->id, '--dry-run' => true])
            ->assertSuccessful();
        $this->assertNotSoftDeleted($c);
    }
}

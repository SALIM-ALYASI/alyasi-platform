<?php

namespace Tests\Feature\Api;

use App\Models\NewsArticle;
use App\Models\NewsSuggestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class NewsSuggestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.news_suggester.token', 'grok-secret');
        Config::set('services.news_bot.token', 'bot-secret');
    }

    private function suggest(array $body, string $token = 'grok-secret')
    {
        return $this->withToken($token)->postJson('/api/news-suggestions', $body);
    }

    public function test_agent_can_queue_a_link_once(): void
    {
        $body = [
            'url' => 'https://example.com/ai/big-launch?utm_source=x',
            'title' => 'إطلاق كبير',
            'summary' => 'ملخص',
            'why_important' => 'يهم المطورين',
            'source_name' => 'Example',
        ];

        $this->suggest($body)->assertStatus(202)->assertJsonPath('status', 'queued');

        // نفس الرابط بدون التتبع = نفس الخبر.
        $this->suggest(['url' => 'https://www.example.com/ai/big-launch/'])
            ->assertOk()
            ->assertJsonPath('status', 'duplicate');

        $this->assertSame(1, NewsSuggestion::query()->count());
    }

    public function test_already_published_source_is_not_queued(): void
    {
        NewsArticle::factory()->create(['source_url' => 'https://example.com/news/known-story']);

        $this->suggest(['url' => 'https://example.com/news/known-story?utm_medium=feed'])
            ->assertOk()
            ->assertJsonPath('status', 'already_published');

        $this->assertSame(0, NewsSuggestion::query()->count());
    }

    public function test_tokens_are_separate(): void
    {
        $this->suggest(['url' => 'https://example.com/a'], 'wrong')->assertStatus(401);
        // رمز بوت الأخبار ما يكفي للاقتراح، ورمز الوكيل ما يقرأ الطابور.
        $this->suggest(['url' => 'https://example.com/a'], 'bot-secret')->assertStatus(401);
        $this->withToken('grok-secret')->getJson('/api/news-suggestions/pending')->assertStatus(401);
    }

    public function test_country_is_normalized(): void
    {
        $this->suggest(['url' => 'https://omannews.example/a', 'country' => 'om'])->assertStatus(202);
        $this->suggest(['url' => 'https://gulf.example/b', 'country' => 'GCC'])->assertStatus(202);
        $this->suggest(['url' => 'https://x.example/c', 'country' => 'Oman'])->assertStatus(422);

        $this->assertSame(['OM', 'XG'], NewsSuggestion::query()->orderBy('id')->pluck('country')->all());
    }

    public function test_invalid_url_is_rejected(): void
    {
        $this->suggest(['url' => 'not a url'])->assertStatus(422);
        $this->suggest(['title' => 'بدون رابط'])->assertStatus(422);
    }

    public function test_bot_pulls_pending_and_acknowledges(): void
    {
        $this->suggest(['url' => 'https://example.com/one'])->assertStatus(202);
        $this->suggest(['url' => 'https://example.com/two'])->assertStatus(202);

        $pending = $this->withToken('bot-secret')->getJson('/api/news-suggestions/pending')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $pending);

        $this->withToken('bot-secret')
            ->patchJson('/api/news-suggestions/'.$pending[0]['id'], ['status' => 'fetched', 'candidate_id' => 4600])
            ->assertOk();

        $this->assertDatabaseHas('news_suggestions', ['id' => $pending[0]['id'], 'status' => 'fetched', 'candidate_id' => 4600]);
        $this->assertCount(1, $this->withToken('bot-secret')->getJson('/api/news-suggestions/pending')->json('data'));

        $this->withToken('bot-secret')
            ->patchJson('/api/news-suggestions/'.$pending[1]['id'], ['status' => 'published'])
            ->assertStatus(422);
    }
}

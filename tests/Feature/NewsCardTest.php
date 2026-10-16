<?php

namespace Tests\Feature;

use App\Models\NewsCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NewsCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.news_bot.token', 'bot-secret');
        Config::set('services.news_suggester.tokens', ['grok' => 'grok-secret']);
    }

    protected function tearDown(): void
    {
        foreach (NewsCard::query()->get() as $card) {
            File::delete(array_filter([public_path($card->image_ar), $card->image_en ? public_path($card->image_en) : null]));
        }
        parent::tearDown();
    }

    private function upload(array $extra = [])
    {
        return $this->withToken('bot-secret')->post('/api/news-cards', $extra + [
            'source_type' => 'news',
            'source_id' => 522,
            'title_ar' => 'GitHub تتيح ميزة AI Scan للطلبات البرمجية بشكل عام',
            'title_en' => 'GitHub makes AI Scan for pull requests generally available',
            'url' => 'https://alyasi.dev/news/2026/10/16/004/github-ai-scan-ga',
            'image_ar' => UploadedFile::fake()->image('ar.png', 1080, 1350),
            'image_en' => UploadedFile::fake()->image('en.png', 1080, 1350),
        ], ['Accept' => 'application/json']);
    }

    public function test_bot_uploads_card_and_grok_reads_it_then_reports_the_tweet(): void
    {
        $this->upload()->assertCreated();
        $this->upload()->assertOk()->assertJsonPath('idempotent', true); // نفس الخبر مرة ثانية ما يتكرر

        $pending = $this->withToken('grok-secret')->getJson('/api/news-cards/pending?platform=x')
            ->assertOk()->assertJsonCount(1, 'data')->json('data.0');

        $this->assertStringEndsWith('https://alyasi.dev/news/2026/10/16/004/github-ai-scan-ga', $pending['text']);
        $this->assertStringContainsString('GitHub makes AI Scan', $pending['text']);
        $this->assertStringContainsString('/uploads/news-cards/', $pending['image_url']);
        $this->assertNotNull($pending['image_url_en']);

        $this->withToken('grok-secret')->postJson('/api/news-cards/'.$pending['id'].'/result', [
            'status' => 'posted',
            'tweet_url' => 'https://x.com/alyasi_mbrmj/status/123',
        ])->assertOk();

        // اللي انتشر ما يرجع لـGrok مرة ثانية
        $this->withToken('grok-secret')->getJson('/api/news-cards/pending')->assertJsonCount(0, 'data');
    }

    public function test_tokens_are_separated_and_tweet_urls_are_checked(): void
    {
        $this->withToken('grok-secret')->post('/api/news-cards', [], ['Accept' => 'application/json'])->assertUnauthorized();
        $this->withToken('bot-secret')->getJson('/api/news-cards/pending')->assertUnauthorized();

        $this->upload()->assertCreated();
        $id = NewsCard::query()->value('id');

        $this->withToken('grok-secret')->postJson("/api/news-cards/{$id}/result", [
            'status' => 'posted',
            'tweet_url' => 'https://evil.example/status/1',
        ])->assertUnprocessable();
    }

    public function test_x_text_stays_within_the_limit(): void
    {
        $text = NewsCard::buildXText(str_repeat('عنوان طويل جدًا ', 40), str_repeat('very long title ', 30), 'https://alyasi.dev/news/x');
        [$body] = explode("\n\nhttps://", $text);
        $this->assertLessThanOrEqual(280 - 23 - 2, mb_strlen($body));
        $this->assertStringEndsWith('https://alyasi.dev/news/x', $text);
    }
}

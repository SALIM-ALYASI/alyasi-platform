<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تنبيه توقف بوت الأخبار: دورة نشر فاضية وحدة (فجوة ~24 ساعة) ما تنبّه،
 * التنبيه بعد 36 ساعة بدون خبر.
 */
class NewsBotStalenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_empty_publishing_cycle_does_not_alert(): void
    {
        NewsArticle::factory()->create(['status' => NewsArticle::STATUS_PUBLISHED, 'published_at' => now()->subHours(26)]);

        $this->artisan('news:check-staleness')->assertSuccessful();

        $this->assertNotSame('1', Setting::get('news_bot_stale_alerted'));
    }

    public function test_alerts_after_36_hours_without_news(): void
    {
        NewsArticle::factory()->create(['status' => NewsArticle::STATUS_PUBLISHED, 'published_at' => now()->subHours(37)]);

        $this->artisan('news:check-staleness')->assertSuccessful();

        $this->assertSame('1', Setting::get('news_bot_stale_alerted'));
    }
}

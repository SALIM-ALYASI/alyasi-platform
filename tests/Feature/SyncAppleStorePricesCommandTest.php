<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventEdition;
use App\Models\Permalink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncAppleStorePricesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_replaces_only_the_synced_product_family_and_preserves_other_prices(): void
    {
        $event = Event::query()->create([
            'name' => 'Apple Event',
            'slug' => 'apple',
            'organizer' => 'Apple',
        ]);

        $edition = EventEdition::query()->create([
            'event_id' => $event->id,
            'year' => 2026,
            'title_ar' => 'مؤتمر آبل 2026',
            'title_en' => 'Apple Event 2026',
            'status' => 'published',
            'published_at' => now(),
            'pricing_table' => [
                [
                    'product_ar' => 'منتج يدوي',
                    'product_en' => 'Manual Product',
                    'official_price' => '100',
                    'official_currency' => 'OMR',
                    'omr_price' => '100',
                ],
                [
                    'product_ar' => 'iPhone Duo 256GB',
                    'product_en' => 'iPhone Duo 256GB',
                    'official_price' => '1',
                    'official_currency' => 'AED',
                    'omr_price' => '1',
                ],
            ],
        ]);

        Permalink::query()->create([
            'locale' => 'ar',
            'slug' => 'apple-event-2026',
            'linkable_type' => 'event_edition',
            'linkable_id' => $edition->id,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://www.apple.com/ae/shop/buy-iphone/iphone-duo' => Http::response(<<<'HTML'
                <html><body>
                <h1>iPhone Duo</h1>
                <div>256GB Star White AED 8,499.00</div>
                <div>512GB Star White AED 9,349.00</div>
                <div>1TB Star White AED 11,049.00</div>
                <div>2TB Star White AED 13,599.00</div>
                <h2>Frequently Asked Questions</h2>
                </body></html>
                HTML),
        ]);

        $this->artisan('apple:sync-prices', [
            '--event' => 'apple-event-2026',
            '--source' => ['iphone-duo'],
        ])->assertExitCode(0);

        $rows = collect($edition->fresh()->pricing_table);

        $this->assertCount(5, $rows);
        $this->assertTrue($rows->contains(fn (array $row) => ($row['product_en'] ?? null) === 'Manual Product'));

        $duo256 = $rows->first(fn (array $row) => ($row['product_en'] ?? null) === 'iPhone Duo 256GB');

        $this->assertNotNull($duo256);
        $this->assertSame('8,499', $duo256['official_price']);
        $this->assertSame('AED', $duo256['official_currency']);
        $this->assertSame('apple_ae', $duo256['source']);
        $this->assertSame('iphone-duo', $duo256['source_key']);
    }
}

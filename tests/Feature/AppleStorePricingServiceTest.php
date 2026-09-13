<?php

namespace Tests\Feature;

use App\Services\AppleStorePricingService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AppleStorePricingServiceTest extends TestCase
{
    public function test_it_parses_supported_apple_uae_store_prices(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://www.apple.com/ae/shop/buy-iphone/iphone-duo' => Http::response(<<<'HTML'
                <html><body>
                <h1>iPhone Duo</h1>
                <p>Pre-order starting at 4:00 p.m.</p>
                <div>256GB Star White AED 8,499.00</div>
                <div>256GB Night Sky AED 8,499.00</div>
                <div>512GB Star White AED 9,349.00</div>
                <div>1TB Star White AED 11,049.00</div>
                <div>2TB Star White AED 13,599.00</div>
                <h2>Frequently Asked Questions</h2>
                </body></html>
                HTML),
            'https://www.apple.com/ae/shop/buy-iphone/iphone-18-pro' => Http::response(<<<'HTML'
                <html><body>
                <h1>Shop iPhone 18 Pro</h1>
                <p>Pre-order starting at 4:00 p.m.</p>
                <div>256GB Glacier AED 5,099.00</div>
                <div>256GB Burgundy AED 5,499.00</div>
                <div>512GB Glacier AED 5,949.00</div>
                <div>512GB Burgundy AED 6,349.00</div>
                <div>1TB Glacier AED 7,649.00</div>
                <div>1TB Burgundy AED 8,049.00</div>
                <div>2TB Glacier AED 10,199.00</div>
                <div>2TB Burgundy AED 10,599.00</div>
                <h2>Which iPhone is right for you?</h2>
                </body></html>
                HTML),
            'https://www.apple.com/ae/shop/buy-airpods/airpods-5' => Http::response(<<<'HTML'
                <html><body>
                <h1>Pre-order AirPods 5</h1>
                <div>AirPods 5 AED 549.00</div>
                <div>AirPods 5 with Wireless Charging Case Includes volume swipe AED 629.00</div>
                <h2>Product Information</h2>
                </body></html>
                HTML),
            'https://www.apple.com/ae/shop/buy-watch' => Http::response(<<<'HTML'
                <html><body>
                <div>Apple Watch Series 12 Take a closer look From AED 1,599</div>
                <div>Apple Watch Ultra 4 Take a closer look From AED 2,800</div>
                </body></html>
                HTML),
        ]);

        $service = app(AppleStorePricingService::class);

        $duo = $service->fetch('iphone-duo');
        $this->assertCount(4, $duo);
        $this->assertSame('8,499', $duo[0]['official_price']);
        $this->assertSame('890', $duo[0]['omr_price']);
        $this->assertSame('256GB', $duo[0]['variant_en']);
        $this->assertSame(AppleStorePricingService::SOURCE, $duo[0]['source']);

        $pro = $service->fetch('iphone-18-pro');
        $this->assertCount(8, $pro);
        $this->assertSame('iPhone 18 Pro', $pro[0]['product_en']);
        $this->assertSame('5,099', $pro[0]['official_price']);
        $this->assertSame('iPhone 18 Pro Max', $pro[1]['product_en']);
        $this->assertSame('5,499', $pro[1]['official_price']);

        $airPods = $service->fetch('airpods-5');
        $this->assertCount(2, $airPods);
        $this->assertSame('549', $airPods[0]['official_price']);
        $this->assertSame('629', $airPods[1]['official_price']);

        $watch = $service->fetch('apple-watch');
        $this->assertCount(2, $watch);
        $this->assertSame('1,599', $watch[0]['official_price']);
        $this->assertSame('2,800', $watch[1]['official_price']);
    }

    public function test_it_rejects_incomplete_source_data_instead_of_overwriting_good_prices(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://www.apple.com/ae/shop/buy-iphone/iphone-duo' => Http::response(
                '<html><body><h1>iPhone Duo</h1><div>256GB Star White AED 8,499.00</div></body></html>'
            ),
        ]);

        $this->expectException(RuntimeException::class);

        app(AppleStorePricingService::class)->fetch('iphone-duo');
    }
}

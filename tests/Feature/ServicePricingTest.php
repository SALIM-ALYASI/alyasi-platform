<?php

namespace Tests\Feature;

use App\Models\Permalink;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePricingTest extends TestCase
{
    use RefreshDatabase;

    private function service(array $attributes, string $slugAr, string $slugEn): Service
    {
        $service = Service::query()->create($attributes + [
            'description_ar' => 'وصف الخدمة التجريبي للاختبار.',
            'description_en' => 'Test service description.',
            'image' => 'uploads/services/test.jpg',
            'is_active' => true,
        ]);

        Permalink::query()->create(['linkable_type' => 'service', 'linkable_id' => $service->id, 'locale' => 'ar', 'slug' => $slugAr]);
        Permalink::query()->create(['linkable_type' => 'service', 'linkable_id' => $service->id, 'locale' => 'en', 'slug' => $slugEn]);

        return $service;
    }

    public function test_services_page_lists_prices_from_cheapest_with_starting_label(): void
    {
        $this->service([
            'title_ar' => 'تصميم وتطوير المواقع الإلكترونية',
            'title_en' => 'Website Design & Development',
            'price_from' => 60,
            'price_unit_ar' => 'لموقع تعريفي بسيط من صفحة واحدة',
            'price_unit_en' => 'for a simple one-page website',
            'price_note_ar' => 'الصفحات والميزات الإضافية تُسعّر منفصلًا.',
        ], 'web-ar', 'web');
        $this->service([
            'title_ar' => 'الاستشارات والحلول التقنية',
            'title_en' => 'Technical Consulting',
            'price_from' => 2.5,
            'price_is_starting' => false,
        ], 'consult-ar', 'consult');
        $this->service(['title_ar' => 'خدمة بدون سعر', 'title_en' => 'Unpriced service'], 'none-ar', 'none');

        $this->get('/services')
            ->assertOk()
            ->assertSee('id="pricing"', false)
            ->assertSeeInOrder(['2.50 ر.ع', 'يبدأ من 60 ر.ع', 'لموقع تعريفي بسيط من صفحة واحدة'])
            ->assertDontSee('يبدأ من 2.50 ر.ع')
            ->assertSee('الصفحات والميزات الإضافية تُسعّر منفصلًا.')
            ->assertSee('السعر النهائي يعتمد على تفاصيل الطلب');

        $this->get('/en/services')
            ->assertOk()
            ->assertSee('From OMR 60')
            ->assertSee('for a simple one-page website')
            // ما فيه ترجمة للملاحظة: الصفحة الإنجليزية ما تعرض النص العربي.
            ->assertDontSee('الصفحات والميزات الإضافية');
    }

    public function test_service_page_shows_price_and_offer_schema(): void
    {
        $this->service([
            'title_ar' => 'صيانة أجهزة ماك وآيفون برمجيًا',
            'title_en' => 'Mac & iPhone Software Support',
            'price_from' => 3,
            'price_note_ar' => 'الحالات المعقدة واستعادة البيانات تُقيّم منفصلًا.',
        ], 'mac-ar', 'mac');

        $this->get('/services/mac-ar')
            ->assertOk()
            ->assertSee('يبدأ من 3 ر.ع')
            ->assertSee('الحالات المعقدة واستعادة البيانات تُقيّم منفصلًا.')
            ->assertSee('"priceCurrency":"OMR"', false)
            ->assertSee('"minPrice":3', false);
    }
}

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
            // الرمز الرسمي يسار الرقم (dir=ltr)، والاستشارة سعر ثابت بدون «يبدأ من».
            ->assertSeeInOrder(['aria-label="ريال عُماني"></span> 2.50</bdi>', 'يبدأ من <bdi class="omr" dir="ltr"><span class="omr__sign" role="img" aria-label="ريال عُماني"></span> 60</bdi>', 'لموقع تعريفي بسيط من صفحة واحدة'], false)
            ->assertDontSee('يبدأ من <bdi class="omr" dir="ltr"><span class="omr__sign" role="img" aria-label="ريال عُماني"></span> 2.50', false)
            ->assertDontSee('ر.ع')
            ->assertSee('الصفحات والميزات الإضافية تُسعّر منفصلًا.')
            ->assertSee('السعر النهائي يعتمد على تفاصيل الطلب');

        $this->get('/en/services')
            ->assertOk()
            ->assertSee('From <bdi class="omr" dir="ltr"><span class="omr__sign" role="img" aria-label="Omani rial"></span> 60</bdi>', false)
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
            'price_note_ar' => 'الحالات المعقدة تُقيّم منفصلًا، و7 ر.ع للحالات الخاصة.',
            'deliverables_ar' => "تشخيص المشكلة عن بُعد\nحل مشكلة برمجية واحدة",
            'deliverables_en' => "Remote diagnosis\nA fix for one software issue",
            'support_ar' => '3 أيام لنفس المشكلة',
        ], 'mac-ar', 'mac');

        $this->get('/services/mac-ar')
            ->assertOk()
            ->assertSee('يبدأ من <bdi class="omr" dir="ltr"><span class="omr__sign" role="img" aria-label="ريال عُماني"></span> 3</bdi>', false)
            // «7 ر.ع» داخل الملاحظة يتحول للرمز.
            ->assertSee('و<bdi class="omr" dir="ltr"><span class="omr__sign" role="img" aria-label="ريال عُماني"></span> 7</bdi> للحالات الخاصة.', false)
            ->assertSee('"priceCurrency":"OMR"', false)
            ->assertSee('"minPrice":3', false)
            // ماذا تستلم؟ والمدة نص عام، وشرط الدفعة بالرمز الرسمي.
            ->assertSeeInOrder(['ماذا', 'تشخيص المشكلة عن بُعد', 'حل مشكلة برمجية واحدة', '3 أيام لنفس المشكلة', 'تُحدَّد بعد مراجعة تفاصيل الطلب'])
            ->assertSee('المشاريع من <bdi class="omr" dir="ltr"><span class="omr__sign" role="img" aria-label="ريال عُماني"></span> 25</bdi> فأكثر', false);

        // الصفحة الإنجليزية بنصوص حقيقية، مو مفاتيح ترجمة خام.
        $this->get('/en/services/mac')
            ->assertOk()
            ->assertSee('Why choose')
            ->assertSee('Request this service')
            ->assertSee('A fix for one software issue')
            // الدعم بدون ترجمة إنجليزية: ما يطلع نص عربي.
            ->assertDontSee('3 أيام لنفس المشكلة')
            ->assertDontSee('services.show.')
            ->assertSee('<title>Mac &amp; iPhone Software Support — ALYASI</title>', false)
            ->assertDontSee('&amp;amp;', false)
            // زر الطلب ينقل اسم الخدمة لنموذج التواصل.
            ->assertSee('/en/contact?subject=Service%20request%3A%20Mac%20%26%20iPhone%20Software%20Support', false);

        $this->get('/en/contact?subject='.rawurlencode('Service request: Mac & iPhone Software Support'))
            ->assertOk()
            ->assertSee('value="Service request: Mac &amp; iPhone Software Support"', false);
    }
}

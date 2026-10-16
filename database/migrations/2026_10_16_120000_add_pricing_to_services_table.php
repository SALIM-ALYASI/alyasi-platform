<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * أسعار الخدمات بالريال العُماني: سعر ابتدائي («يبدأ من») أو ثابت،
     * مع وحدة السعر (للدقيقة، للبوت البسيط...) وملاحظة عن الرسوم المنفصلة.
     */
    private const PRICES = [
        'تصميم وتطوير المواقع الإلكترونية' => [60, true,
            'لموقع تعريفي بسيط من صفحة واحدة', 'for a simple one-page website',
            'الصفحات والميزات الإضافية تُسعّر منفصلًا.', 'Additional pages and features are priced separately.'],
        'تطوير الأنظمة ولوحات التحكم' => [80, true, null, null, null, null],
        'أتمتة الأعمال وربط الأنظمة' => [10, true,
            'لأتمتة بسيطة واحدة', 'for one simple automation',
            'البرمجة ضمن السعر، ورسوم الأدوات والخدمات الخارجية على العميل.', 'Development is included; third-party tool and service fees are paid by the client.'],
        'تطوير البوتات والمساعدات الذكية' => [10, true,
            'للبوت البسيط', 'for a simple bot',
            'البرمجة ضمن السعر، ورسوم الشركات والمنصات والاستضافة على العميل.', 'Development is included; provider, platform and hosting fees are paid by the client.'],
        'تصميم الهوية الرقمية' => [25, true, null, null, null, null],
        'تصميم ومونتاج الفيديو' => [5, true,
            'للدقيقة (مونتاج أساسي)', 'per minute (basic editing)',
            '7 ر.ع للدقيقة باستخدام أدوات الذكاء الاصطناعي. التصوير والمؤثرات الخاصة منفصلة.', 'OMR 7 per minute with AI tools. Filming and special effects are priced separately.'],
        'حلول الذكاء الاصطناعي' => [5, true, null, null,
            'رسوم الأدوات والخدمات الخارجية منفصلة، والحلول المخصصة تُسعّر حسب المشروع.', 'Third-party tool and service fees are separate; custom solutions are priced per project.'],
        'الاستشارات والحلول التقنية' => [2.5, false,
            'للعميل الجديد — استشارة عن بُعد مدتها 30 دقيقة', 'for new clients — a 30-minute remote consultation',
            'مجانًا لمن طلب خدمة من ALYASI خلال آخر 12 شهرًا.', 'Free for clients who ordered an ALYASI service in the last 12 months.'],
        'صيانة أجهزة ماك وآيفون برمجيًا' => [3, true, null, null,
            'الحالات المعقدة واستعادة البيانات تُقيّم منفصلًا.', 'Complex cases and data recovery are assessed separately.'],
        'إدارة السيرفرات والاستضافة والنشر' => [10, true,
            'للإعداد والنشر البسيط', 'for a simple setup and deployment',
            'تكلفة السيرفر والنطاق منفصلة.', 'Server and domain costs are separate.'],
        'تصميم محتوى وإعلانات التواصل الاجتماعي' => [2.5, true,
            'لتصميم بنر رقمي', 'per digital banner design',
            null, null],
    ];

    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->decimal('price_from', 8, 3)->nullable()->after('image');
            $table->boolean('price_is_starting')->default(true)->after('price_from');
            $table->string('price_unit_ar')->nullable()->after('price_is_starting');
            $table->string('price_unit_en')->nullable()->after('price_unit_ar');
            $table->string('price_note_ar', 500)->nullable()->after('price_unit_en');
            $table->string('price_note_en', 500)->nullable()->after('price_note_ar');
        });

        foreach (self::PRICES as $title => [$price, $starting, $unitAr, $unitEn, $noteAr, $noteEn]) {
            DB::table('services')->where('title_ar', $title)->update([
                'price_from' => $price,
                'price_is_starting' => $starting,
                'price_unit_ar' => $unitAr,
                'price_unit_en' => $unitEn,
                'price_note_ar' => $noteAr,
                'price_note_en' => $noteEn,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn([
                'price_from',
                'price_is_starting',
                'price_unit_ar',
                'price_unit_en',
                'price_note_ar',
                'price_note_en',
            ]);
        });
    }
};

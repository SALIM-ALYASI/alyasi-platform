<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «ماذا تستلم؟» لكل خدمة: المخرجات (سطر لكل بند)، وجولات التعديل، والدعم بعد
     * التسليم. مدة التنفيذ ما تنحفظ رقمًا: تُحدَّد بعد مراجعة الطلب (نص عام).
     *
     * [مخرجات عربي, مخرجات إنجليزي, تعديلات عربي, تعديلات إنجليزي, دعم عربي, دعم إنجليزي]
     */
    private const SCOPE = [
        'تصميم وتطوير المواقع الإلكترونية' => [
            ['صفحة واحدة متجاوبة مع الجوال والكمبيوتر', 'نموذج تواصل وربط مع واتساب', 'تهيئة أساسية لمحركات البحث', 'نشر الموقع على استضافة العميل'],
            ['One responsive page for mobile and desktop', 'Contact form and WhatsApp link', 'Basic search engine optimization', 'Deployment on the client\'s hosting'],
            'جولتا تعديل', 'Two revision rounds',
            '30 يومًا لإصلاح الأخطاء', '30 days of bug fixes',
        ],
        'تطوير الأنظمة ولوحات التحكم' => [
            ['لوحة تحكم بتسجيل دخول', 'إدارة نوع بيانات واحد (إضافة وتعديل وحذف)', 'مستويان من الصلاحيات', 'قاعدة بيانات ونشر على الاستضافة'],
            ['Admin dashboard with login', 'Management of one data type (create, edit, delete)', 'Two permission levels', 'Database and deployment on hosting'],
            'جولتا تعديل', 'Two revision rounds',
            '30 يومًا لإصلاح الأخطاء', '30 days of bug fixes',
        ],
        'أتمتة الأعمال وربط الأنظمة' => [
            ['أتمتة واحدة تربط بين نظامين', 'شرح مختصر لطريقة عملها'],
            ['One automation connecting two systems', 'A short explanation of how it works'],
            'جولة تعديل واحدة', 'One revision round',
            '14 يومًا', '14 days',
        ],
        'تطوير البوتات والمساعدات الذكية' => [
            ['بوت على منصة واحدة', 'حتى 10 ردود أو أوامر', 'تشغيله على حساب العميل'],
            ['A bot on one platform', 'Up to 10 replies or commands', 'Set up on the client\'s account'],
            'جولة تعديل واحدة', 'One revision round',
            '14 يومًا', '14 days',
        ],
        'تصميم الهوية الرقمية' => [
            ['شعار بمقترحين للاختيار', 'الألوان والخطوط المعتمدة', 'الملفات بصيغ PNG وSVG وPDF'],
            ['A logo with two concepts to choose from', 'Approved colors and fonts', 'Files in PNG, SVG and PDF'],
            'جولتا تعديل', 'Two revision rounds',
            null, null,
        ],
        'تصميم ومونتاج الفيديو' => [
            ['فيديو بدقة 1080p', 'قص وانتقالات ونصوص', 'موسيقى مرخّصة'],
            ['A 1080p video', 'Cuts, transitions and text', 'Licensed music'],
            'جولتا تعديل', 'Two revision rounds',
            null, null,
        ],
        'حلول الذكاء الاصطناعي' => [
            ['إعداد أداة أو سير عمل واحد', 'شرح طريقة الاستخدام'],
            ['Setup of one tool or workflow', 'A walkthrough of how to use it'],
            'جولة تعديل واحدة', 'One revision round',
            '7 أيام', '7 days',
        ],
        'الاستشارات والحلول التقنية' => [
            ['جلسة عن بُعد مدتها 30 دقيقة', 'ملخص مكتوب بالتوصيات بعد الجلسة'],
            ['A 30-minute remote session', 'A written summary of recommendations afterwards'],
            null, null,
            null, null,
        ],
        'صيانة أجهزة ماك وآيفون برمجيًا' => [
            ['تشخيص المشكلة عن بُعد', 'حل مشكلة برمجية واحدة'],
            ['Remote diagnosis', 'A fix for one software issue'],
            null, null,
            '3 أيام لنفس المشكلة', '3 days for the same issue',
        ],
        'إدارة السيرفرات والاستضافة والنشر' => [
            ['تجهيز الاستضافة', 'ربط النطاق', 'شهادة الأمان SSL', 'نشر الموقع'],
            ['Hosting setup', 'Domain connection', 'SSL certificate', 'Website deployment'],
            null, null,
            '7 أيام', '7 days',
        ],
        'تصميم محتوى وإعلانات التواصل الاجتماعي' => [
            ['تصميم واحد بمقاس واحد', 'ملف PNG بجودة عالية'],
            ['One design in one size', 'A high-quality PNG file'],
            'جولة تعديل واحدة', 'One revision round',
            null, null,
        ],
    ];

    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->text('deliverables_ar')->nullable()->after('price_note_en');
            $table->text('deliverables_en')->nullable()->after('deliverables_ar');
            $table->string('revisions_ar')->nullable()->after('deliverables_en');
            $table->string('revisions_en')->nullable()->after('revisions_ar');
            $table->string('support_ar')->nullable()->after('revisions_en');
            $table->string('support_en')->nullable()->after('support_ar');
        });

        foreach (self::SCOPE as $title => [$itemsAr, $itemsEn, $revAr, $revEn, $supAr, $supEn]) {
            DB::table('services')->where('title_ar', $title)->update([
                'deliverables_ar' => implode("\n", $itemsAr),
                'deliverables_en' => implode("\n", $itemsEn),
                'revisions_ar' => $revAr,
                'revisions_en' => $revEn,
                'support_ar' => $supAr,
                'support_en' => $supEn,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['deliverables_ar', 'deliverables_en', 'revisions_ar', 'revisions_en', 'support_ar', 'support_en']);
        });
    }
};

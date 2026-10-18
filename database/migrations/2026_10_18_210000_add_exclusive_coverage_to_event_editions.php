<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تغطية ALYASI الحصرية لكل نسخة مؤتمر -- تُدار من لوحة التحكم بدل الكود:
     * - is_media_partner: صفة الشراكة الإعلامية (منفصلة عن وجود محتوى فعلي).
     * - coverage_url / interviews_url: روابط التغطية الكاملة وصفحة المقابلات.
     * - exclusive_published_at: متى نُشرت التغطية الحصرية فعلًا (للاختيار التلقائي).
     * - is_featured: تثبيت يدوي كتغطية مميزة (الافتراضي اختيار تلقائي).
     * - goals_ar / goals_en: أهداف منصة ALYASI من تغطية المؤتمر.
     *
     * وجدول event_interviews: فهرس عام لمقابلات كل نسخة (الاسم والصورة والرابط
     * وحالة النشر). محتوى المقابلة التفصيلي يبقى بمصدره (ملف CyberX حاليًا)،
     * وأي مؤتمر جديد يضيف صفوفه هنا بدون تعديل صفحة الفعاليات.
     */
    public function up(): void
    {
        Schema::table('event_editions', function (Blueprint $table): void {
            $table->boolean('is_media_partner')->default(false)->after('attended');
            $table->string('coverage_url', 500)->nullable()->after('is_media_partner');
            $table->string('interviews_url', 500)->nullable()->after('coverage_url');
            $table->timestamp('exclusive_published_at')->nullable()->after('interviews_url');
            $table->boolean('is_featured')->default(false)->after('exclusive_published_at');
            // أهداف ALYASI من تغطية المؤتمر (هدف بكل سطر) -- تنعرض قائمة بصفحة النسخة.
            $table->text('goals_ar')->nullable()->after('short_description_en');
            $table->text('goals_en')->nullable()->after('goals_ar');
        });

        Schema::create('event_interviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_edition_id')->constrained('event_editions')->cascadeOnDelete();
            $table->string('slug', 120);
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('role_ar')->nullable();
            $table->string('role_en')->nullable();
            $table->string('photo', 500)->nullable();
            // رابط صفحة المقابلة بكل لغة (null = ما لها صفحة بهذي اللغة).
            $table->string('url_ar', 500)->nullable();
            $table->string('url_en', 500)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['event_edition_id', 'slug']);
        });

        // البيانات الحالية: CyberX Oman 2026 (تغطية ميدانية من 8 أكتوبر + المقابلات)،
        // وCYSEC Oman 2026 (شريك إعلامي، والتغطية لسا ما نُشرت).
        $editionId = fn (string $slug) => DB::table('permalinks')
            ->where('linkable_type', 'event_edition')->where('locale', 'ar')->where('slug', $slug)
            ->value('linkable_id');

        if ($id = $editionId('cyberx-oman-2026')) {
            DB::table('event_editions')->where('id', $id)->update([
                'coverage_url' => '/events/cyberx-oman-2026/coverage',
                'interviews_url' => '/events/cyberx-oman-2026/interviews',
                'exclusive_published_at' => '2026-10-08 03:53:07', // أول نشر لتغطية «خمس محطات»
            ]);
        }

        if ($id = $editionId('cysec-oman-2026')) {
            DB::table('event_editions')->where('id', $id)->update(['is_media_partner' => true]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_interviews');

        Schema::table('event_editions', function (Blueprint $table): void {
            $table->dropColumn(['is_media_partner', 'coverage_url', 'interviews_url', 'exclusive_published_at', 'is_featured', 'goals_ar', 'goals_en']);
        });
    }
};

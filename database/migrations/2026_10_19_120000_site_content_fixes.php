<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /** ترتيب الخدمات حسب الأهمية (بالعنوان العربي): المواقع والأنظمة والذكاء الاصطناعي أول. */
    private const SERVICE_ORDER = [
        'تصميم وتطوير المواقع الإلكترونية',
        'تطوير الأنظمة ولوحات التحكم',
        'تطوير البوتات والمساعدات الذكية',
        'حلول الذكاء الاصطناعي',
        'أتمتة الأعمال وربط الأنظمة',
        'إدارة السيرفرات والاستضافة والنشر',
        'تصميم الهوية الرقمية',
        'تصميم ومونتاج الفيديو',
        'تصميم محتوى وإعلانات التواصل الاجتماعي',
        'الاستشارات والحلول التقنية',
        'صيانة أجهزة ماك وآيفون برمجيًا',
    ];

    /**
     * الأسماء الرسمية للمؤتمرات: بالعربي الاسم العربي مع الرسمي بين قوسين، وبالإنجليزي
     * الرسمي. [slug المؤتمر => [اسم السلسلة ar, en, عنوان النسخة ar, en]]
     */
    private const OFFICIAL_NAMES = [
        'apple' => ['حدث آبل (Apple Event)', 'Apple Event', 'حدث آبل «Surprise and Shine» – سبتمبر 2026', null],
        'comex-oman' => ['كومكس عُمان (COMEX Oman)', 'COMEX Oman', 'كومكس عُمان 2027 (COMEX Oman 2027)', 'COMEX Oman 2027'],
        'cyberx-oman' => ['سايبر إكس عُمان (CyberX Oman)', 'CyberX Oman', 'سايبر إكس عُمان 2026 (CyberX Oman 2026)', 'CyberX Oman 2026'],
        'huawei' => ['إطلاقات هواوي (Huawei Launch Events)', 'Huawei Launch Events', 'هواوي Chase the Wild 2026', 'Huawei Chase the Wild 2026'],
        'cysec-oman' => ['CYSEC عُمان (CYSEC Oman)', 'CYSEC Oman', 'CYSEC عُمان 2026 (CYSEC Oman 2026)', 'CYSEC Oman 2026'],
        'oman-electricity-energy-conference' => ['مؤتمر عُمان للكهرباء والطاقة', 'Oman Electricity & Energy Conference', 'مؤتمر عُمان للكهرباء والطاقة 2026 (IEEE PowerTalks)', 'Oman Electricity & Energy Conference 2026 (IEEE PowerTalks)'],
    ];

    /** روابط مقالات عربية كانت حروفًا لاتينية غير مفهومة => رابط إنجليزي واضح (القديم يصير 301). */
    private const ARTICLE_SLUGS = [
        'fgoty-alty-orthha-ghyry' => 'i-never-learned-english',
        'altlfzyon-mn-thlal-aal-algdar-al-shash-thky' => 'television-shadows-to-smart-screen',
        'kbl-an-ygtmaa-alhras-rhl-al-alaaalm-almthlm-althy-yharbonh' => 'before-the-guardians-gather',
    ];

    public function up(): void
    {
        Storage::disk('local')->put('backups/site-content-before-fixes-'.now()->format('YmdHis').'.json', json_encode([
            'services' => DB::table('services')->get(['id', 'title_ar']),
            'works' => DB::table('works')->whereIn('id', [2, 6])->get(),
            'events' => DB::table('events')->get(['id', 'slug', 'name', 'name_en']),
            'event_editions' => DB::table('event_editions')->get(['id', 'event_id', 'title_ar', 'title_en']),
            'social_links' => DB::table('social_links')->where('platform', 'whatsapp')->get(),
            'article_permalinks' => DB::table('permalinks')->whereIn('slug', array_keys(self::ARTICLE_SLUGS))->get(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // ١) ترتيب الخدمات
        Schema::table('services', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(100)->after('is_active');
        });
        foreach (self::SERVICE_ORDER as $i => $title) {
            DB::table('services')->where('title_ar', $title)->update(['sort_order' => ($i + 1) * 10]);
        }

        // ٢) «الحرفيون» (رابطه harfiyoon.com) بدل «المحترفون»، وتصنيف النظام = تطبيق.
        foreach (DB::table('works')->where('slug', 'harfiyoon')->get() as $work) {
            $fix = fn (?string $v) => $v === null ? null : str_replace(['المحترفون', 'Al Muhtarifoon', 'Muhtarifoon'], ['الحرفيون', 'Al Harfiyoon', 'Harfiyoon'], $v);
            DB::table('works')->where('id', $work->id)->update([
                'title' => $fix($work->title), 'title_en' => $fix($work->title_en),
                'client_name' => $fix($work->client_name), 'client_name_en' => $fix($work->client_name_en),
                'description' => $fix($work->description), 'description_en' => $fix($work->description_en),
                'short_description' => $fix($work->short_description), 'short_description_en' => $fix($work->short_description_en),
                'type' => 'application',
            ]);
        }
        // ALYASI AI Voice: أداة ذكاء اصطناعي، مب «تصميم».
        DB::table('works')->where('title_en', 'ALYASI AI Voice')->where('type', 'design')->update(['type' => 'application']);

        // ٣) واتساب: رابط مباشر للرقم (رابط QR ما يفتح المحادثة على الكمبيوتر).
        DB::table('social_links')->where('platform', 'whatsapp')->update(['url' => 'https://wa.me/96898881054']);

        // ٤) الأسماء الرسمية للمؤتمرات (الروابط ما تتغير).
        foreach (self::OFFICIAL_NAMES as $slug => [$seriesAr, $seriesEn, $titleAr, $titleEn]) {
            $event = DB::table('events')->where('slug', $slug)->first();
            if (! $event) {
                continue;
            }
            DB::table('events')->where('id', $event->id)->update(['name' => $seriesAr, 'name_en' => $seriesEn]);
            $edition = DB::table('event_editions')->where('event_id', $event->id)->orderByDesc('year')->first();
            if ($edition) {
                DB::table('event_editions')->where('id', $edition->id)->update(array_filter([
                    'title_ar' => $titleAr,
                    'title_en' => $titleEn,
                ]));
            }
        }

        // ٥) روابط المقالات: الجديد + تحويل 301 من القديم (نفس آلية تغيير الرابط من لوحة التحكم).
        foreach (self::ARTICLE_SLUGS as $old => $new) {
            $permalink = DB::table('permalinks')->where('slug', $old)->first();
            if (! $permalink || DB::table('permalinks')->where('slug', $new)->exists()) {
                continue;
            }
            DB::table('permalink_redirects')->updateOrInsert(
                ['locale' => $permalink->locale, 'old_slug' => $old],
                ['permalink_id' => $permalink->id, 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('permalinks')->where('id', $permalink->id)->update(['slug' => $new, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
        // باقي التعديلات تُرجَع من النسخة الاحتياطية (backups/site-content-before-fixes-*.json).
    }
};

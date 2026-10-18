<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * وصف خاص لكل مؤتمر (بدل جملة «المنتجات والأسعار ومواعيد التوفر» العامة
     * اللي كانت تطلع حتى لمؤتمرات الأمن السيبراني والطاقة).
     *
     * [slug => [عربي, إنجليزي]]
     */
    private const DESCRIPTIONS = [
        'apple' => [
            'تغطية مؤتمرات آبل لإطلاق الأجهزة: ما أُعلن، والأسعار الرسمية مع تحويلها للريال العُماني، ومواعيد التوفر.',
            'Coverage of Apple’s launch events: what was announced, official prices with Omani rial conversions, and availability dates.',
        ],
        'huawei' => [
            'تغطية مؤتمرات هواوي لإطلاق الأجهزة: الساعات والأجهزة اللوحية والجوالات، مع الأسعار الرسمية وتحويلها للريال العُماني.',
            'Coverage of Huawei’s launch events: watches, tablets and phones, with official prices and Omani rial conversions.',
        ],
        'comex-oman' => [
            'معرض كومكس عُمان، المعرض التقني الأبرز في السلطنة: الذكاء الاصطناعي والحوسبة السحابية والأمن السيبراني والتحول الرقمي.',
            'COMEX Oman, the Sultanate’s leading technology exhibition: artificial intelligence, cloud computing, cybersecurity and digital transformation.',
        ],
        'cyberx-oman' => [
            'مؤتمر سايبر إكس عُمان للأمن السيبراني: تغطية ALYASI الميدانية، ومقابلات حصرية مع المتحدثين وقادة الأمن السيبراني.',
            'CyberX Oman, the cybersecurity conference: ALYASI’s on-site coverage and exclusive interviews with speakers and security leaders.',
        ],
        'cysec-oman' => [
            'مؤتمر CYSEC عُمان للأمن السيبراني، ومنصة ALYASI شريكه الإعلامي: الحوكمة والبنية الأساسية الحيوية والذكاء الاصطناعي والمرونة السيبرانية.',
            'CYSEC Oman, the cybersecurity conference with ALYASI as media partner: governance, critical infrastructure, AI and cyber resilience.',
        ],
        'oman-electricity-energy-conference' => [
            'مؤتمر عُمان للكهرباء والطاقة: أمن الإمدادات، والطاقة المتجددة، والشبكات الذكية، والتحول في قطاع الكهرباء بالسلطنة.',
            'The Oman Electricity & Energy Conference: security of supply, renewable energy, smart grids and the transformation of Oman’s power sector.',
        ],
    ];

    public function up(): void
    {
        // نسخة احتياطية قبل أي تعديل على المحتوى المنشور.
        Storage::disk('local')->put('backups/events-before-hub-fixes-'.now()->format('YmdHis').'.json', json_encode([
            'events' => DB::table('events')->get(),
            'event_editions' => DB::table('event_editions')->get(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        Schema::table('events', function (Blueprint $table): void {
            $table->text('description_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description_ar');
        });

        foreach (self::DESCRIPTIONS as $slug => [$ar, $en]) {
            DB::table('events')->where('slug', $slug)->update(['description_ar' => $ar, 'description_en' => $en]);
        }

        // منظّم COMEX حسب موقعه الرسمي (© OITE Global) -- مركز المعارض هو المكان مب المنظّم.
        DB::table('events')->where('slug', 'comex-oman')->where('organizer', 'Oman Convention & Exhibition Centre')->update(['organizer' => 'OITE Global']);

        $this->fixComexMonth();
        $this->fixAppleDate();
        $this->tidyHuawei();
    }

    /**
     * COMEX Oman 2027: الموقع الرسمي (comex-global.com) يقول 1–4 فبراير 2027،
     * والموعد بقاعدة البيانات صحيح أصلًا، بس الوصف المكتوب كان يقول يناير.
     */
    private function fixComexMonth(): void
    {
        $edition = DB::table('event_editions')
            ->join('events', 'events.id', '=', 'event_editions.event_id')
            ->where('events.slug', 'comex-oman')
            ->where('event_editions.year', 2027)
            ->select('event_editions.id', 'event_editions.short_description_ar', 'event_editions.short_description_en')
            ->first();

        if (! $edition) {
            return;
        }

        DB::table('event_editions')->where('id', $edition->id)->update([
            'short_description_ar' => str_replace('من 1 إلى 4 يناير 2027', 'من 1 إلى 4 فبراير 2027', (string) $edition->short_description_ar),
            'short_description_en' => str_replace(
                ['from 1 to 4 January 2027', 'the new January 2027 dates'],
                ['from 1 to 4 February 2027', 'the new dates of 1–4 February 2027'],
                (string) $edition->short_description_en
            ),
        ]);
    }

    /**
     * Apple 2026: الصفحة تقول 9 سبتمبر الساعة 22:00 بتوقيت مسقط، بس الموعد كان
     * محفوظ 20:00 بتوقيت غرينتش (= منتصف الليل بمسقط) فبطاقة الأرشيف تقول 10.09.
     */
    private function fixAppleDate(): void
    {
        $id = DB::table('event_editions')
            ->join('events', 'events.id', '=', 'event_editions.event_id')
            ->where('events.slug', 'apple')
            ->where('event_editions.year', 2026)
            ->where('event_editions.event_start_at', '2026-09-09 20:00:00')
            ->value('event_editions.id');

        if ($id) {
            DB::table('event_editions')->where('id', $id)->update([
                'event_start_at' => '2026-09-09 18:00:00',
                'event_end_at' => '2026-09-09 18:51:00',
            ]);
        }
    }

    /**
     * Huawei Chase the Wild 2026: الأسعار كانت تتكرر ثلاث مرات (باليورو داخل
     * النص، وببطاقة كل منتج، وبجدول كامل تحت). النص يبقى عن الحدث ومصادره،
     * وبطاقات المنتجات فيها الوصف والسعر. وصفوف السعر بدون اسم خيار صار لها اسم.
     */
    private function tidyHuawei(): void
    {
        $edition = DB::table('event_editions')
            ->join('events', 'events.id', '=', 'event_editions.event_id')
            ->where('events.slug', 'huawei')
            ->where('event_editions.year', 2026)
            ->select('event_editions.id', 'event_editions.pricing_table', 'event_editions.content_ar', 'event_editions.content_en')
            ->first();

        if (! $edition) {
            return;
        }

        $rows = json_decode((string) $edition->pricing_table, true) ?: [];
        $seen = [];

        foreach ($rows as &$row) {
            $name = $row['product_en'] ?? '';
            $seen[$name] = ($seen[$name] ?? 0) + 1;

            if ($name === 'HUAWEI WATCH 6') {
                // من المحتوى المنشور: 449/489 يورو للمقاسين 42/46 ملم.
                [$row['variant_ar'], $row['variant_en']] = $seen[$name] === 1 ? ['42 ملم', '42mm'] : ['46 ملم', '46mm'];
            }

            if ($name === 'HUAWEI MatePad Pro 12') {
                // المؤكد بالمحتوى المنشور الإصدار الأساسي بس (12/256GB مع غطاء Folio).
                [$row['variant_ar'], $row['variant_en']] = $seen[$name] === 1
                    ? ['12/256GB مع غطاء Folio', '12/256GB with Folio Cover']
                    : ['إصدار أعلى', 'Higher configuration'];
            }
        }
        unset($row);

        DB::table('event_editions')->where('id', $edition->id)->update([
            'pricing_table' => json_encode($rows, JSON_UNESCAPED_UNICODE),
            'content_ar' => $this->keepEventSections((string) $edition->content_ar, 'ar'),
            'content_en' => $this->keepEventSections((string) $edition->content_en, 'en'),
        ]);
    }

    /**
     * يبقي «عن الحدث» و«المعلومات الأساسية» و«المصدر»، ويشيل قوائم المنتجات
     * المكررة (نفس نص البطاقات + الأسعار باليورو)، مع سطر عن مصدر الأسعار.
     */
    private function keepEventSections(string $html, string $locale): string
    {
        $parts = preg_split('/(?=<h2>)/u', $html, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $keep = $locale === 'ar' ? ['عن الحدث', 'المعلومات الأساسية', 'المصدر'] : ['About the Event', 'Key Information', 'Source'];
        $out = [];

        foreach ($parts as $part) {
            if (preg_match('/^<h2>(.*?)<\/h2>/u', $part, $m) && in_array(trim(strip_tags($m[1])), $keep, true)) {
                $out[] = trim($part);
            }
        }

        if (count($out) < 2) {
            return $html; // شكل غير متوقع: نخلي المحتوى مثل ما هو.
        }

        $note = $locale === 'ar'
            ? '<p><em>تفاصيل كل منتج وسعره في بطاقات المنتجات أدناه. الأسعار محوّلة تقريبيًا من الأسعار الرسمية الأوروبية (باليورو) عند الإطلاق.</em></p>'
            : '<p><em>Each product’s details and price are in the product cards below. Prices are approximate conversions from the official European (EUR) launch prices.</em></p>';

        // السطر قبل «المصدر».
        array_splice($out, count($out) - 1, 0, [$note]);

        return implode("\n", $out);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['description_ar', 'description_en']);
        });
        // تعديلات المحتوى تُرجَع من النسخة الاحتياطية في storage/app/private/backups عند الحاجة.
    }
};

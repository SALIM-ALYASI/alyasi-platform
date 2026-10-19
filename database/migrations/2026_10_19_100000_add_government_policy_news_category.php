<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * تصنيف «حكومات وتشريعات» + تصحيح تصنيف أخبار كانت بقسم غلط (مراجعة يدوية
     * للعناوين، 19 أكتوبر 2026). روابط الأخبار ما فيها التصنيف، فما يتغير أي رابط.
     *
     * [رقم الخبر => [التصنيف الحالي المتوقع، التصنيف الصحيح]] -- يتغير بس لو
     * التصنيف الحالي مثل المتوقع (ما نلغي تعديل يدوي صار بعدين).
     */
    private const MOVES = [
        // حكومات وتشريعات: قوانين، جهات تنظيمية، تحقيقات برلمانية، مبادرات حكومية.
        157 => ['big-tech', 'government-policy'],          // الرياض الرقمية (الهيئة الملكية)
        179 => ['artificial-intelligence', 'government-policy'], // المفوضية الأوروبية وقواعد DSA
        180 => ['big-tech', 'government-policy'],          // FTC و22 ولاية تقاضي أمازون
        187 => ['big-tech', 'government-policy'],          // أمريكا ترفض تنظيم الذكاء الاصطناعي بقمة العشرين
        203 => ['artificial-intelligence', 'government-policy'], // مشروع قانون أمريكي
        220 => ['big-tech', 'government-policy'],          // FTC تعاقب شركة مدفوعات
        237 => ['artificial-intelligence', 'government-policy'], // مشرّعون أمريكيون يحققون مع OpenAI
        245 => ['cybersecurity', 'government-policy'],     // تحقيق مجلس الشيوخ
        273 => ['big-tech', 'government-policy'],          // مقترح قانون KIDS الأوروبي
        290 => ['big-tech', 'government-policy'],          // قرار رئاسي أمريكي عن الذكاء الاصطناعي
        299 => ['artificial-intelligence', 'government-policy'], // أمر تنفيذي من حاكم كاليفورنيا
        341 => ['big-tech', 'government-policy'],          // مطالبة الأمم المتحدة بتنظيم عالمي
        359 => ['artificial-intelligence', 'government-policy'], // استدعاء لمجلس الشيوخ الأسترالي
        362 => ['big-tech', 'government-policy'],          // دعوة لتشريعات إلزامية
        380 => ['programming', 'government-policy'],       // تغييرات تشريعية وطلبات الإزالة
        387 => ['big-tech', 'government-policy'],          // اتفاق البيت الأبيض
        388 => ['artificial-intelligence', 'government-policy'], // اتفاق البيت الأبيض
        409 => ['artificial-intelligence', 'government-policy'], // FTC تدقق في OpenAI وAnthropic
        489 => ['big-tech', 'government-policy'],          // لجنة علمية لمخاطر الذكاء الاصطناعي
        518 => ['big-tech', 'government-policy'],          // سدايا تعتمد 23 معيارًا وطنيًا
        520 => ['big-tech', 'government-policy'],          // أستراليا: مشروع قانون الخوارزميات
        527 => ['big-tech', 'government-policy'],          // أبوظبي: TAMM Global للخدمات الحكومية
        528 => ['big-tech', 'government-policy'],          // سدايا: حملة توعية
        531 => ['big-tech', 'government-policy'],          // شرطة أبوظبي

        // الأمن السيبراني
        151 => ['programming', 'cybersecurity'],           // تشفير Ring افتراضيًا
        232 => ['big-tech', 'cybersecurity'],              // DNSSEC مقاوم للحوسبة الكمية
        242 => ['programming', 'cybersecurity'],           // Cloudflare CASB
        263 => ['artificial-intelligence', 'cybersecurity'], // حماية المتاجر من السكربتات الخفية
        345 => ['big-tech', 'cybersecurity'],              // Turnstile
        482 => ['artificial-intelligence', 'cybersecurity'], // حماية الأسرار في GitHub
        530 => ['big-tech', 'cybersecurity'],              // OAuth وDPoP

        // الذكاء الاصطناعي
        148 => ['big-tech', 'artificial-intelligence'],    // نموذج استرجاع طبي
        189 => ['big-tech', 'artificial-intelligence'],    // وكلاء ذكاء اصطناعي
        229 => ['big-tech', 'artificial-intelligence'],    // Workflow1111 (توليد الصور)
        334 => ['big-tech', 'artificial-intelligence'],    // LiquidAI
        351 => ['big-tech', 'artificial-intelligence'],    // تدريب OLMo
        484 => ['big-tech', 'artificial-intelligence'],    // EmbeddingGemma 2
        510 => ['programming', 'artificial-intelligence'], // نماذج Muse من ميتا

        // كبرى الشركات
        195 => ['programming', 'big-tech'],                // إنفيديا تستحوذ على Hugging Face
        228 => ['artificial-intelligence', 'big-tech'],    // هواوي ومعيار الاتصالات الضوئية
        231 => ['programming', 'big-tech'],                // أدوات قياس من Google
        404 => ['artificial-intelligence', 'big-tech'],    // إعلانات يوتيوب
        524 => ['artificial-intelligence', 'big-tech'],    // ميزة شحن جماعي في Shopify

        // الشركات الناشئة
        212 => ['big-tech', 'startups'],                   // Velents تدخل البحرين
        275 => ['big-tech', 'startups'],                   // تمويل المنشآت الصغيرة
    ];

    public function up(): void
    {
        if (! DB::table('news_categories')->where('slug', 'government-policy')->exists()) {
            DB::table('news_categories')->insert([
                'name_ar' => 'حكومات وتشريعات',
                'name_en' => 'Government & Policy',
                'slug' => 'government-policy',
                'description_ar' => 'القوانين والجهات التنظيمية والمبادرات الحكومية في التقنية والذكاء الاصطناعي.',
                'description_en' => 'Laws, regulators and government initiatives in technology and AI.',
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $ids = DB::table('news_categories')->pluck('id', 'slug');
        $articles = DB::table('news_articles')->whereIn('id', array_keys(self::MOVES))->get(['id', 'news_category_id']);

        Storage::disk('local')->put('backups/news-categories-'.now()->format('YmdHis').'.json', json_encode(
            $articles->pluck('news_category_id', 'id'), JSON_PRETTY_PRINT
        ));

        foreach ($articles as $article) {
            [$from, $to] = self::MOVES[$article->id];

            if (($ids[$from] ?? null) === $article->news_category_id && isset($ids[$to])) {
                DB::table('news_articles')->where('id', $article->id)->update(['news_category_id' => $ids[$to]]);
            }
        }
    }

    public function down(): void
    {
        // التصنيفات القديمة بالنسخة الاحتياطية في storage/app/private/backups (news-categories-*.json).
    }
};

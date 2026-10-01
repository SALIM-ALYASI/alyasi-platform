<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * إعادة تصنيف الأخبار المدفونة كلها تحت "كبرى الشركات التقنية" (كانت 171
 * من 217، 78.8%) للتصنيفات الأدق الموجودة أصلاً لكن شبه فاضية -- السبب
 * الجذري: event_type_to_category_slug بـnews-bot-v2 يحوّل 6 من 10 أنواع
 * أحداث لـ"big-tech" كـfallback، وما فيه تحويل لـ"programming" إطلاقًا.
 * هذا الأمر علاج رجعي بالكلمات المفتاحية؛ العلاج الجذري المستقبلي بملف
 * publish.py نفسه بـnews-bot-v2.
 */
class ReclassifyBigTechArticles extends Command
{
    protected $signature = 'news:reclassify-categories {--apply : نفّذ التحديث فعليًا بدل العرض فقط}';

    protected $description = 'يعيد تصنيف أخبار "كبرى الشركات التقنية" للتصنيفات الأدق بالكلمات المفتاحية';

    /**
     * ترتيب الأولوية مهم عند تعادل عدد الكلمات المطابقة -- الأكثر تحديدًا أولاً.
     */
    private const KEYWORDS = [
        'cybersecurity' => [
            'اختراق', 'ثغرة', 'هجوم سيبراني', 'برمجية خبيثة', 'تسريب بيانات',
            'خرق بيانات', 'فدية', 'تصيد احتيالي', 'hack', 'breach', 'vulnerability',
            'malware', 'ransomware', 'phishing', 'exploit', 'cyberattack',
        ],
        'artificial-intelligence' => [
            'ذكاء اصطناعي', 'نموذج لغوي', 'تعلم آلي', 'روبوت محادثة', 'نموذج توليدي',
            'LLM', 'GPT', 'Gemini', 'Claude', 'OpenAI', 'Anthropic', 'chatbot',
            'machine learning', 'neural network', 'AI model', 'generative AI',
        ],
        'gadgets' => [
            'هاتف ذكي', 'آيفون', 'آيباد', 'ساعة ذكية', 'سماعة لاسلكية', 'لابتوب',
            'تابلت', 'سيارة كهربائية', 'smartphone', 'iPhone', 'wearable',
            'tablet', 'headphone', 'earbuds', 'smartwatch',
        ],
        'programming' => [
            'Laravel', 'GitHub', 'مطورين', 'لغة برمجة', 'مكتبة برمجية', 'إطار عمل',
            'framework', 'مفتوح المصدر', 'open source', 'repository', 'npm',
            'package manager', 'SDK', 'compiler', 'JavaScript', 'TypeScript',
            'Python', 'API جديد', 'open-source',
        ],
        'startups' => [
            'جولة تمويلية', 'شركة ناشئة', 'رأس مال مخاطر', 'funding round',
            'startup', 'venture capital', 'Series A', 'Series B', 'Seed round',
        ],
    ];

    public function handle(): int
    {
        $categories = NewsCategory::query()->pluck('id', 'slug');
        $bigTechId = $categories['big-tech'] ?? null;

        if (! $bigTechId) {
            $this->error('تصنيف big-tech غير موجود.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');

        $articles = NewsArticle::query()
            ->where('news_category_id', $bigTechId)
            ->get(['id', 'title_ar', 'excerpt_ar', 'content_ar']);

        $moves = [];

        foreach ($articles as $article) {
            $haystack = $article->title_ar.' '.$article->excerpt_ar.' '.strip_tags((string) $article->content_ar);

            $bestSlug = null;
            $bestScore = 0;

            foreach (self::KEYWORDS as $slug => $keywords) {
                $score = 0;
                foreach ($keywords as $keyword) {
                    if (Str::contains($haystack, $keyword, ignoreCase: true)) {
                        $score++;
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestSlug = $slug;
                }
            }

            if ($bestSlug === null || ! isset($categories[$bestSlug])) {
                continue;
            }

            $moves[$bestSlug][] = $article;

            if ($apply) {
                $article->news_category_id = $categories[$bestSlug];
                $article->save();
            }
        }

        $this->line($apply ? 'تم التحديث فعليًا:' : 'معاينة فقط (استخدم --apply للتنفيذ):');
        $this->newLine();

        foreach ($moves as $slug => $items) {
            $this->line("[{$slug}] ".count($items).' خبر');
            foreach ($items as $item) {
                $this->line("  #{$item->id} {$item->title_ar}");
            }
        }

        $moved = array_sum(array_map('count', $moves));
        $this->newLine();
        $this->line("الإجمالي: {$moved} من {$articles->count()} بقوا بـbig-tech أو تحركوا.");

        return self::SUCCESS;
    }
}

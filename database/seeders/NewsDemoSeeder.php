<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Permalink;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = $this->createCategories();

            $articles = [
                [
                    'category' => 'artificial-intelligence',
                    'title_ar' => 'كيف يغيّر الذكاء الاصطناعي مستقبل الأعمال؟',
                    'title_en' => 'How Artificial Intelligence Is Changing Business',
                    'excerpt_ar' => 'أصبح الذكاء الاصطناعي جزءًا أساسيًا من تطوير الأعمال وتحسين تجربة العملاء.',
                    'excerpt_en' => 'Artificial intelligence is becoming essential for business development and customer experience.',
                    'content_ar' => 'يساعد الذكاء الاصطناعي المؤسسات على تحليل البيانات، وأتمتة المهام، واتخاذ قرارات أكثر دقة. ومع تطور التقنيات ستظهر فرص جديدة للشركات والمطورين.',
                    'content_en' => 'Artificial intelligence helps organizations analyze data, automate tasks, and make better decisions. As the technology develops, new opportunities will emerge.',
                    'slug_ar' => 'مستقبل-الذكاء-الاصطناعي-في-الأعمال',
                    'slug_en' => 'future-of-artificial-intelligence-in-business',
                    'is_featured' => true,
                    'is_breaking' => false,
                    'published_at' => now()->subHours(2),
                    'views_count' => 125,
                ],
                [
                    'category' => 'programming',
                    'title_ar' => 'لماذا يبقى Laravel خيارًا قويًا لتطوير المنصات؟',
                    'title_en' => 'Why Laravel Remains a Strong Choice for Platforms',
                    'excerpt_ar' => 'يوفر Laravel أدوات متكاملة تساعد على بناء تطبيقات آمنة وقابلة للتطوير.',
                    'excerpt_en' => 'Laravel provides integrated tools for building secure and scalable applications.',
                    'content_ar' => 'يتميز Laravel بتنظيم واضح، ونظام مسارات مرن، وأدوات قوية لقواعد البيانات والمهام المجدولة وواجهات API، مما يجعله مناسبًا للمنصات الحديثة.',
                    'content_en' => 'Laravel offers clear structure, flexible routing, database tools, scheduled jobs, and API support, making it suitable for modern platforms.',
                    'slug_ar' => 'لارافيل-لتطوير-المنصات-الحديثة',
                    'slug_en' => 'laravel-for-modern-platform-development',
                    'is_featured' => true,
                    'is_breaking' => false,
                    'published_at' => now()->subDay(),
                    'views_count' => 98,
                ],
                [
                    'category' => 'cybersecurity',
                    'title_ar' => 'خطوات بسيطة لحماية حساباتك الرقمية',
                    'title_en' => 'Simple Steps to Protect Your Digital Accounts',
                    'excerpt_ar' => 'كلمة مرور قوية والتحقق بخطوتين يقللان من مخاطر اختراق الحسابات.',
                    'excerpt_en' => 'Strong passwords and two-factor authentication reduce the risk of account compromise.',
                    'content_ar' => 'ابدأ باستخدام كلمة مرور مختلفة لكل حساب، وفعّل التحقق بخطوتين، وتجنب فتح الروابط غير الموثوقة أو مشاركة رموز التحقق مع الآخرين.',
                    'content_en' => 'Use a different password for each account, enable two-factor authentication, and avoid untrusted links or sharing verification codes.',
                    'slug_ar' => 'خطوات-حماية-الحسابات-الرقمية',
                    'slug_en' => 'steps-to-protect-digital-accounts',
                    'is_featured' => false,
                    'is_breaking' => true,
                    'published_at' => now()->subDays(2),
                    'views_count' => 210,
                ],
                [
                    'category' => 'artificial-intelligence',
                    'title_ar' => 'الذكاء الاصطناعي يساعد المطورين على زيادة الإنتاجية',
                    'title_en' => 'AI Helps Developers Increase Productivity',
                    'excerpt_ar' => 'أدوات الذكاء الاصطناعي تختصر الوقت في البرمجة والاختبار وتوثيق المشاريع.',
                    'excerpt_en' => 'AI tools save time in coding, testing, and project documentation.',
                    'content_ar' => 'يمكن للمطور استخدام أدوات الذكاء الاصطناعي للمساعدة في اكتشاف الأخطاء، وكتابة الاختبارات، واقتراح الحلول، مع ضرورة مراجعة النتائج قبل اعتمادها.',
                    'content_en' => 'Developers can use AI to find bugs, write tests, and suggest solutions while reviewing the results before adoption.',
                    'slug_ar' => 'الذكاء-الاصطناعي-وإنتاجية-المطورين',
                    'slug_en' => 'ai-and-developer-productivity',
                    'is_featured' => false,
                    'is_breaking' => false,
                    'published_at' => now()->subDays(3),
                    'views_count' => 76,
                ],
                [
                    'category' => 'programming',
                    'title_ar' => 'أهمية واجهات API في ربط الأنظمة الحديثة',
                    'title_en' => 'The Importance of APIs in Modern Systems',
                    'excerpt_ar' => 'تسمح واجهات API للتطبيقات والخدمات المختلفة بتبادل البيانات بصورة منظمة.',
                    'excerpt_en' => 'APIs allow different applications and services to exchange data in an organized way.',
                    'content_ar' => 'تستخدم واجهات API لربط المواقع بتطبيقات الهاتف والبوتات والخدمات الخارجية، مع توفير حماية وصلاحيات مناسبة لكل عملية.',
                    'content_en' => 'APIs connect websites with mobile applications, bots, and external services while providing suitable protection and permissions.',
                    'slug_ar' => 'أهمية-واجهات-api-في-ربط-الأنظمة',
                    'slug_en' => 'importance-of-apis-in-modern-systems',
                    'is_featured' => false,
                    'is_breaking' => false,
                    'published_at' => now()->subDays(4),
                    'views_count' => 64,
                ],
                [
                    'category' => 'cybersecurity',
                    'title_ar' => 'النسخ الاحتياطي أساس استمرارية المشاريع الرقمية',
                    'title_en' => 'Backups Are Essential for Digital Projects',
                    'excerpt_ar' => 'خطة النسخ الاحتياطي تحمي البيانات عند حدوث خطأ أو عطل مفاجئ.',
                    'excerpt_en' => 'A backup plan protects data when unexpected errors or failures occur.',
                    'content_ar' => 'يجب حفظ نسخ احتياطية منتظمة من قاعدة البيانات والملفات المهمة، مع اختبار استعادتها وعدم الاعتماد على نسخة واحدة فقط.',
                    'content_en' => 'Databases and important files should be backed up regularly, with restoration tests and more than one backup copy.',
                    'slug_ar' => 'النسخ-الاحتياطي-للمشاريع-الرقمية',
                    'slug_en' => 'backups-for-digital-projects',
                    'is_featured' => false,
                    'is_breaking' => false,
                    'published_at' => now()->subDays(5),
                    'views_count' => 52,
                ],
            ];

            foreach ($articles as $data) {
                $category = $categories[$data['category']];

                $article = NewsArticle::query()->updateOrCreate(
                    [
                        'title_ar' => $data['title_ar'],
                    ],
                    [
                        'news_category_id' => $category->id,
                        'title_en' => $data['title_en'],
                        'excerpt_ar' => $data['excerpt_ar'],
                        'excerpt_en' => $data['excerpt_en'],
                        'content_ar' => $data['content_ar'],
                        'content_en' => $data['content_en'],

                        'image' => 'images/logo/logo-dark.png',
                        'image_alt_ar' => $data['title_ar'],
                        'image_alt_en' => $data['title_en'],

                        'source_name' => 'منصة ALYASI',
                        'source_url' => null,
                        'author_name' => 'فريق ALYASI',

                        'status' => NewsArticle::STATUS_PUBLISHED,
                        'is_breaking' => $data['is_breaking'],
                        'is_featured' => $data['is_featured'],
                        'published_at' => $data['published_at'],
                        'views_count' => $data['views_count'],

                        'seo_title_ar' => $data['title_ar'],
                        'seo_title_en' => $data['title_en'],
                        'seo_description_ar' => $data['excerpt_ar'],
                        'seo_description_en' => $data['excerpt_en'],
                    ]
                );

                $this->savePermalink(
                    article: $article,
                    locale: 'ar',
                    slug: $data['slug_ar'],
                );

                $this->savePermalink(
                    article: $article,
                    locale: 'en',
                    slug: $data['slug_en'],
                );
            }
        });
    }

    /**
     * إنشاء تصنيفات الأخبار التجريبية.
     */
    private function createCategories(): array
    {
        $categories = [
            'artificial-intelligence' => [
                'name_ar' => 'الذكاء الاصطناعي',
                'name_en' => 'Artificial Intelligence',
                'slug' => 'artificial-intelligence',
                'description_ar' => 'أخبار وتطورات الذكاء الاصطناعي.',
                'description_en' => 'Artificial intelligence news and developments.',
                'icon' => 'fa-solid fa-brain',
                'sort_order' => 1,
            ],
            'programming' => [
                'name_ar' => 'البرمجة والتطوير',
                'name_en' => 'Programming',
                'slug' => 'programming',
                'description_ar' => 'أخبار البرمجة وتطوير المواقع والتطبيقات.',
                'description_en' => 'Programming and application development news.',
                'icon' => 'fa-solid fa-code',
                'sort_order' => 2,
            ],
            'cybersecurity' => [
                'name_ar' => 'الأمن السيبراني',
                'name_en' => 'Cybersecurity',
                'slug' => 'cybersecurity',
                'description_ar' => 'أخبار ونصائح الأمن والحماية الرقمية.',
                'description_en' => 'Digital security news and advice.',
                'icon' => 'fa-solid fa-shield-halved',
                'sort_order' => 3,
            ],
        ];

        $createdCategories = [];

        foreach ($categories as $key => $data) {
            $createdCategories[$key] = NewsCategory::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'is_active' => true,
                ]
            );
        }

        return $createdCategories;
    }

    /**
     * إنشاء الرابط العربي أو الإنجليزي للخبر.
     */
    private function savePermalink(
        NewsArticle $article,
        string $locale,
        string $slug,
    ): void {
        $permalink = Permalink::query()
            ->where('linkable_type', 'news')
            ->where('linkable_id', $article->id)
            ->where('locale', $locale)
            ->first();

        $permalink ??= new Permalink();

        $permalink->linkable_type = 'news';
        $permalink->linkable_id = $article->id;
        $permalink->locale = $locale;
        $permalink->slug = $slug;
        $permalink->save();
    }
}
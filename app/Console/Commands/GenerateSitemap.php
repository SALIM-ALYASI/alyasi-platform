<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\CommunityPost;
use App\Models\NewsArticle;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL as UrlFacade;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'توليد public/sitemap.xml مع الروابط الثابتة والمقالات والأخبار والأعمال والخدمات بلغاتها';

    /**
     * أسماء الراوتات (بدون لاحقة .en) للصفحات الثابتة اللي لها فعليًا
     * نسخة /en/ (عبر localized_route()) — تُضاف مرتين بكل لغة مع
     * hreflang alternate بينهما، بنفس أسلوب المقالات/الخدمات أدناه.
     */
    private const STATIC_LOCALIZED_ROUTES = [
        'home',
        'about',
        'careers',
        'contact',
        'services.index',
        'works.index',
        'news.index',
        'community.index',
        'social-links.index',
        'privacy',
        'terms',
    ];

    /**
     * صفحات مستقلة بلا نسخة لغة ثانية (تصاميم/هويات خاصة بحسابات
     * بعينها) — تُضاف كما هي بدون alternates.
     */
    private const STATIC_SINGLE_PATHS = [
        '/markify',
        '/alyasicarwash',
    ];

    /**
     * Hosts محلية لا يجوز إطلاقًا إنتاج sitemap.xml بها ببيئة production —
     * كانت هذي بالضبط المشكلة اللي كسرت الخريطة سابقًا (تولّدت من جهاز
     * محلي وانرفعت للإنتاج بروابط 127.0.0.1 بدل alyasi.dev).
     */
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1', '::1'];

    public function handle(): int
    {
        /*
        |----------------------------------------------------------------
        | Base URL صريح وثابت — بغض النظر عن كيف/من وين اتنفّذ الأمر
        |----------------------------------------------------------------
        |
        | نعتمد على config('app.url') فقط (قيمة APP_URL بملف .env)،
        | مو Host الخاص بالطلب الحالي — سواء جا التنفيذ من زر لوحة
        | التحكم (HTTP)، أو Queue، أو Cron، أو CLI مباشرة، النتيجة
        | نفسها دايمًا. URL::forceRootUrl() تفرض هذا الجذر على كل
        | route()/url() المستخدمة بباقي الأمر (وبالـ helpers article_route/
        | localized_route المبنية فوقها).
        */
        $baseUrl = rtrim((string) config('app.url'), '/');
        $host = parse_url($baseUrl, PHP_URL_HOST);

        if (app()->environment('production') && in_array($host, self::LOCAL_HOSTS, true)) {
            $this->error(
                "رُفض توليد sitemap.xml: APP_URL الحالي ({$baseUrl}) عنوان محلي بينما البيئة production. "
                .'تحقق من قيمة APP_URL بملف .env على السيرفر — لن يتم تعديل sitemap.xml الحالي.'
            );

            return self::FAILURE;
        }

        UrlFacade::forceRootUrl($baseUrl);

        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap);
        $this->addArticles($sitemap);
        $this->addNews($sitemap);
        $this->addServices($sitemap);
        $this->addWorks($sitemap);
        $this->addCommunityPosts($sitemap);

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info("تم توليد sitemap.xml بنجاح ({$baseUrl}).");

        return self::SUCCESS;
    }

    private function addStaticPages(Sitemap $sitemap): void
    {
        foreach (self::STATIC_LOCALIZED_ROUTES as $routeName) {
            $arUrl = localized_route($routeName, [], 'ar');
            $enUrl = localized_route($routeName, [], 'en');

            $sitemap->add(
                Url::create($arUrl)
                    ->addAlternate($enUrl, 'en')
                    ->addAlternate($arUrl, 'ar')
            );

            $sitemap->add(
                Url::create($enUrl)
                    ->addAlternate($arUrl, 'ar')
                    ->addAlternate($enUrl, 'en')
            );
        }

        foreach (self::STATIC_SINGLE_PATHS as $path) {
            $sitemap->add(Url::create($path));
        }
    }

    /**
     * منشورات المجتمع محتوى بلغة واحدة فقط (بدون حقول ar/en منفصلة)،
     * فتُضاف برابط واحد بدون alternates.
     */
    private function addCommunityPosts(Sitemap $sitemap): void
    {
        CommunityPost::query()
            ->active()
            ->published()
            ->orderBy('id')
            ->chunk(100, function ($posts) use ($sitemap) {
                foreach ($posts as $post) {
                    $sitemap->add(
                        Url::create(route('community.show', $post))
                            ->setLastModificationDate($post->updated_at)
                    );
                }
            });
    }

    private function addArticles(Sitemap $sitemap): void
    {
        Article::query()
            ->published()
            ->with('permalinks')
            ->availableIn('ar')
            ->orderBy('id')
            ->chunk(100, function ($articles) use ($sitemap) {
                foreach ($articles as $article) {
                    $arSlug = $article->translatedSlug('ar');
                    $enSlug = $article->translatedSlug('en');

                    if (! $arSlug) {
                        continue;
                    }

                    $arUrl = article_route('show', [$arSlug], 'ar');

                    $url = Url::create($arUrl)
                        ->setLastModificationDate($article->updated_at);

                    if ($enSlug) {
                        $enUrl = article_route('show', [$enSlug], 'en');
                        $url->addAlternate($enUrl, 'en');
                        $url->addAlternate($arUrl, 'ar');
                    }

                    $sitemap->add($url);

                    if ($enSlug) {
                        $enUrl = article_route('show', [$enSlug], 'en');

                        $sitemap->add(
                            Url::create($enUrl)
                                ->setLastModificationDate($article->updated_at)
                                ->addAlternate($arUrl, 'ar')
                                ->addAlternate($enUrl, 'en')
                        );
                    }
                }
            });
    }

    private function addNews(Sitemap $sitemap): void
    {
        NewsArticle::query()
            ->published()
            ->with('permalinks')
            ->orderBy('id')
            ->chunk(100, function ($articles) use ($sitemap) {
                foreach ($articles as $article) {
                    $arSlug = $article->permalinks->firstWhere('locale', 'ar')?->slug;
                    $enSlug = $article->permalinks->firstWhere('locale', 'en')?->slug;

                    if (! $arSlug) {
                        continue;
                    }

                    $arUrl = route('news.show', ['slug' => $arSlug]);

                    $url = Url::create($arUrl)
                        ->setLastModificationDate($article->updated_at);

                    if ($enSlug) {
                        $enUrl = localized_route('news.show', ['slug' => $enSlug], 'en');
                        $url->addAlternate($enUrl, 'en');
                        $url->addAlternate($arUrl, 'ar');
                    }

                    $sitemap->add($url);

                    if ($enSlug) {
                        $enUrl = localized_route('news.show', ['slug' => $enSlug], 'en');

                        $sitemap->add(
                            Url::create($enUrl)
                                ->setLastModificationDate($article->updated_at)
                                ->addAlternate($arUrl, 'ar')
                                ->addAlternate($enUrl, 'en')
                        );
                    }
                }
            });
    }

    private function addServices(Sitemap $sitemap): void
    {
        Service::query()
            ->where('is_active', true)
            ->with('permalinks')
            ->orderBy('id')
            ->chunk(100, function ($services) use ($sitemap) {
                foreach ($services as $service) {
                    $arSlug = $service->permalinks->firstWhere('locale', 'ar')?->slug;
                    $enSlug = $service->permalinks->firstWhere('locale', 'en')?->slug;

                    if (! $arSlug) {
                        continue;
                    }

                    $arUrl = route('services.show', ['slug' => $arSlug]);

                    $url = Url::create($arUrl)
                        ->setLastModificationDate($service->updated_at);

                    if ($enSlug) {
                        $enUrl = localized_route('services.show', ['slug' => $enSlug], 'en');
                        $url->addAlternate($enUrl, 'en');
                        $url->addAlternate($arUrl, 'ar');
                    }

                    $sitemap->add($url);

                    if ($enSlug) {
                        $enUrl = localized_route('services.show', ['slug' => $enSlug], 'en');

                        $sitemap->add(
                            Url::create($enUrl)
                                ->setLastModificationDate($service->updated_at)
                                ->addAlternate($arUrl, 'ar')
                                ->addAlternate($enUrl, 'en')
                        );
                    }
                }
            });
    }

    private function addWorks(Sitemap $sitemap): void
    {
        Work::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->chunk(100, function ($works) use ($sitemap) {
                foreach ($works as $work) {
                    $arUrl = route('works.show', $work);
                    $enUrl = localized_route('works.show', ['work' => $work], 'en');

                    $sitemap->add(
                        Url::create($arUrl)
                            ->setLastModificationDate($work->updated_at)
                            ->addAlternate($enUrl, 'en')
                            ->addAlternate($arUrl, 'ar')
                    );

                    $sitemap->add(
                        Url::create($enUrl)
                            ->setLastModificationDate($work->updated_at)
                            ->addAlternate($arUrl, 'ar')
                            ->addAlternate($enUrl, 'en')
                    );
                }
            });
    }
}

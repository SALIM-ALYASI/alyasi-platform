<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\NewsArticle;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'توليد public/sitemap.xml مع الروابط الثابتة والمقالات والأخبار والأعمال والخدمات بلغاتها';

    /**
     * الروابط الثابتة الحالية (كانت مكتوبة يدويًا في public/sitemap.xml).
     */
    private const STATIC_PATHS = [
        '/',
        '/about',
        '/contact',
        '/services',
        '/works',
        '/news',
        '/community',
        '/social-links',
        '/privacy',
        '/terms',
        '/markify',
        '/alyasicarwash',
    ];

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        foreach (self::STATIC_PATHS as $path) {
            $sitemap->add(Url::create($path));
        }

        $this->addArticles($sitemap);
        $this->addNews($sitemap);
        $this->addServices($sitemap);
        $this->addWorks($sitemap);

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('تم توليد sitemap.xml بنجاح.');

        return self::SUCCESS;
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

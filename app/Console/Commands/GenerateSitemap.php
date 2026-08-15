<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'توليد public/sitemap.xml مع الروابط الثابتة ومقالات "مقالاتي" بلغتيها';

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

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('تم توليد sitemap.xml بنجاح.');

        return self::SUCCESS;
    }
}

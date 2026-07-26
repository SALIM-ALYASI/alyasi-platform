<?php

namespace App\Providers;

use App\Models\NewsArticle;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'service' => Service::class,
            'news_article' => NewsArticle::class,

            /*
             * نضيف الموديلات لاحقًا بعد إنشائها فعليًا.
             *
             * 'project'   => Project::class,
             * 'page'      => Page::class,
             * 'community' => Community::class,
             */
        ]);
    }
}
<?php

namespace App\Providers;

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

            // نضيفها عند إنشاء الموديلات
            // 'news'      => News::class,
            // 'project'   => Project::class,
            // 'page'      => Page::class,
            // 'community' => Community::class,
        ]);
    }
}
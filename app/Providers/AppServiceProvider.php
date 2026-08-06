<?php

namespace App\Providers;

use App\Models\CommunityCategory;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\NewsArticle;
use App\Models\Review;
use App\Models\Service;
use App\Models\Work;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;
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
            'work' => Work::class,
            'news_article' => NewsArticle::class,
            'community_category' => CommunityCategory::class,
            'community_post' => CommunityPost::class,

            /*
             * نضيف الموديلات لاحقًا بعد إنشائها فعليًا.
             *
             * 'project' => Project::class,
             * 'page'    => Page::class,
             */
        ]);

        View::composer('admin.components.sidebar', function ($view) {
            $view->with([
                'pendingCommunityCommentsCount' => CommunityComment::query()->pending()->count(),
                'pendingReviewsCount' => Review::query()->pending()->count(),
            ]);
        });
    }
}

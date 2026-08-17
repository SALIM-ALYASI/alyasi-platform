<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\CommunityCategory;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\ContactMessage;
use App\Models\NewsArticle;
use App\Models\Review;
use App\Models\Service;
use App\Models\Work;
use App\View\Composers\ArticleLocaleLinksComposer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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
        /*
         * نفرض جذر الروابط من APP_URL دايمًا (بدل الاعتماد على Host الخاص
         * بالطلب الحالي)، حتى لو وصل الطلب عبر www أو /public أو أي مضيف
         * غير معتمد — كل route()/url() مولّدة بالموقع (canonical، hreflang،
         * الروابط الداخلية، sitemap) تبقى دايمًا على الدومين المعتمد.
         */
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');

        Relation::enforceMorphMap([
            'service' => Service::class,
            'work' => Work::class,
            'news_article' => NewsArticle::class,
            'article' => Article::class,
            'community_category' => CommunityCategory::class,
            'community_post' => CommunityPost::class,

            /*
             * نضيف الموديلات لاحقًا بعد إنشائها فعليًا.
             *
             * 'project' => Project::class,
             * 'page'    => Page::class,
             */
        ]);

        View::composer('components.header', ArticleLocaleLinksComposer::class);

        View::composer('admin.components.sidebar', function ($view) {
            $view->with([
                'pendingCommunityCommentsCount' => CommunityComment::query()->pending()->count(),
                'pendingReviewsCount' => Review::query()->pending()->count(),
                'newMessagesCount' => ContactMessage::query()->new()->count(),
            ]);
        });
    }
}

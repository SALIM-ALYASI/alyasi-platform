<?php

use App\Http\Controllers\Admin\ArticleCategoryController;
/*
|--------------------------------------------------------------------------
| Public Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlockedVisitorController;
use App\Http\Controllers\Admin\CommunityCommentController as AdminCommunityCommentController;
use App\Http\Controllers\Admin\CommunityPostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\MarketerController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PronunciationQueueController;
use App\Http\Controllers\Admin\PublishController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ServerInfoController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ServiceSlugCleanupController;
use App\Http\Controllers\Admin\SettingController;
/*
|--------------------------------------------------------------------------
| Admin Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\VoiceStudioController;
use App\Http\Controllers\Admin\WorkController as AdminWorkController;
use App\Http\Controllers\Admin\YoutubeAuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommunityCommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\WorkController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->middleware('force.locale:ar')
    ->name('home');

Route::get('/en', [HomeController::class, 'index'])
    ->middleware('force.locale:en')
    ->name('home.en');

/*
|--------------------------------------------------------------------------
| Language Switch
|--------------------------------------------------------------------------
*/

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (in_array($locale, SetLocale::SUPPORTED_LOCALES, true)) {
        session(['locale' => $locale]);
    }

    /*
     * الصفحات التي لا يوجد لها راوت جاهز بلغة أخرى (مثل Markify ومغسلة
     * الياسي) تمرر رابط الصفحة الحالية صراحة (عادة url()->current()،
     * أي رابط مطلق) بدل الاعتماد على Referer (غير موثوق دائمًا، وقد
     * يرجّع المستخدم للرئيسية بالخطأ). نتحقق أن مضيفه هو نفس مضيف
     * الموقع المعتمد قبل التحويل إليه لمنع Open Redirect، سواء جاء
     * كمسار نسبي أو رابط مطلق.
     */
    $redirectTo = $request->string('redirect')->toString();

    $target = null;

    if ($redirectTo !== '') {
        if (str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')) {
            $target = $redirectTo;
        } else {
            $parsed = parse_url($redirectTo);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if ($parsed !== false && ($parsed['host'] ?? null) !== null && $parsed['host'] === $appHost) {
                $target = ($parsed['path'] ?? '/').(isset($parsed['query']) ? '?'.$parsed['query'] : '');
            }
        }
    }

    if ($target !== null) {
        return redirect()->to($target);
    }

    return redirect()->back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Public Services
|--------------------------------------------------------------------------
*/

Route::middleware('force.locale:ar')
    ->prefix('services')
    ->name('services.')
    ->controller(ServiceController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show');
    });

Route::middleware('force.locale:en')
    ->prefix('en/services')
    ->name('services.')
    ->controller(ServiceController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index.en');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show.en');
    });

Route::post(
    'services/{service}/reviews',
    [ReviewController::class, 'storeForService']
)
    ->middleware('throttle:5,1')
    ->name('services.reviews.store');

/*
|--------------------------------------------------------------------------
| Public News
|--------------------------------------------------------------------------
*/

// Canonical Arabic news URLs: /ar/news/...
Route::middleware('force.locale:ar')
    ->prefix('ar/news')
    ->name('news.')
    ->controller(NewsController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{year}/{month}/{day}/{sequence}/{slug}', 'showSmart')
            ->where([
                'year' => '[0-9]{4}',
                'month' => '[0-9]{2}',
                'day' => '[0-9]{2}',
                'sequence' => '[0-9]{3,}',
                'slug' => '[^/]+',
            ])
            ->name('show.smart');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show');
    });

// Legacy Arabic news URLs: /news/... -> controller returns 301.
Route::middleware('force.locale:ar')
    ->prefix('news')
    ->name('news.legacy.')
    ->controller(NewsController::class)
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('news.index', [], 301);
        })->name('index');

        Route::get('/{year}/{month}/{day}/{sequence}/{slug}', 'showSmart')
            ->where([
                'year' => '[0-9]{4}',
                'month' => '[0-9]{2}',
                'day' => '[0-9]{2}',
                'sequence' => '[0-9]{3,}',
                'slug' => '[^/]+',
            ])
            ->name('show.smart');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show');
    });

Route::middleware('force.locale:en')
    ->prefix('en/news')
    ->name('news.')
    ->controller(NewsController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index.en');

        Route::get('/{year}/{month}/{day}/{sequence}/{slug}', 'showSmart')
            ->where([
                'year' => '[0-9]{4}',
                'month' => '[0-9]{2}',
                'day' => '[0-9]{2}',
                'sequence' => '[0-9]{3,}',
                'slug' => '[^/]+',
            ])
            ->name('show.smart.en');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show.en');
    });

/*
|--------------------------------------------------------------------------
| Public Articles
|--------------------------------------------------------------------------
*/

Route::middleware('force.locale:ar')
    ->prefix('articles')
    ->name('articles.')
    ->controller(ArticleController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show');
    });

Route::middleware('force.locale:en')
    ->prefix('en/articles')
    ->name('articles.')
    ->controller(ArticleController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index.en');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show.en');
    });

/*
|--------------------------------------------------------------------------
| Public Events
|--------------------------------------------------------------------------
*/

Route::get('/events', [EventController::class, 'index'])
    ->name('events.index');

/*
|--------------------------------------------------------------------------
| Public Community
|--------------------------------------------------------------------------
*/

Route::middleware('force.locale:ar')
    ->get('/community', [CommunityController::class, 'index'])
    ->name('community.index');

Route::middleware('force.locale:en')
    ->get('/en/community', [CommunityController::class, 'index'])
    ->name('community.index.en');

Route::get('/community/{communityPost:slug}', [CommunityController::class, 'show'])
    ->name('community.show');

Route::post(
    'community/{communityPost:slug}/comments',
    [CommunityCommentController::class, 'store']
)
    ->middleware('throttle:5,1')
    ->name('community.comments.store');

/*
|--------------------------------------------------------------------------
| Public Static Pages
|--------------------------------------------------------------------------
*/

Route::middleware('force.locale:ar')
    ->get('/contact', [PageController::class, 'contact'])
    ->name('contact');

Route::middleware('force.locale:en')
    ->get('/en/contact', [PageController::class, 'contact'])
    ->name('contact.en');

Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::middleware('force.locale:ar')
    ->get('/about', [PageController::class, 'about'])
    ->name('about');

Route::middleware('force.locale:en')
    ->get('/en/about', [PageController::class, 'about'])
    ->name('about.en');

Route::middleware('force.locale:ar')
    ->get('/careers', [PageController::class, 'careers'])
    ->name('careers');

Route::middleware('force.locale:en')
    ->get('/en/careers', [PageController::class, 'careers'])
    ->name('careers.en');

Route::middleware('force.locale:ar')
    ->get('/social-links', [SocialLinkController::class, 'index'])
    ->name('social-links.index');

Route::middleware('force.locale:en')
    ->get('/en/social-links', [SocialLinkController::class, 'index'])
    ->name('social-links.index.en');

/*
|--------------------------------------------------------------------------
| Legal Pages
|--------------------------------------------------------------------------
*/

Route::middleware('force.locale:ar')
    ->get('/privacy', [PageController::class, 'privacy'])
    ->name('privacy');

Route::middleware('force.locale:en')
    ->get('/en/privacy', [PageController::class, 'privacy'])
    ->name('privacy.en');

Route::middleware('force.locale:ar')
    ->get('/terms', [PageController::class, 'terms'])
    ->name('terms');

Route::middleware('force.locale:en')
    ->get('/en/terms', [PageController::class, 'terms'])
    ->name('terms.en');

/*
|--------------------------------------------------------------------------
| Markify (صفحة مستقلة بتصميم خاص)
|--------------------------------------------------------------------------
*/

Route::get('/markify', [PageController::class, 'markify'])
    ->name('markify');

Route::get('/sitemap.xml', [PageController::class, 'sitemap'])
    ->name('sitemap');

/*
|--------------------------------------------------------------------------
| مغسلة الياسي
|--------------------------------------------------------------------------
*/

Route::get('/alyasicarwash', [PageController::class, 'carWash'])
    ->name('car-wash');

/*
|--------------------------------------------------------------------------
| منزل محمد بن سالم الحجري (راعي صويط) — صفحة مستقلة
|--------------------------------------------------------------------------
*/

Route::get('/Ra3iSwait', [PageController::class, 'ra3iSwait'])
    ->name('ra3i-swait');

/*
|--------------------------------------------------------------------------
| Public Works
|--------------------------------------------------------------------------
*/

Route::middleware('force.locale:ar')
    ->prefix('works')
    ->name('works.')
    ->controller(WorkController::class)
    ->group(function () {

        Route::get('/', 'index')
            ->name('index');

        Route::get('/{work:slug}', 'show')
            ->name('show');

    });

Route::middleware('force.locale:en')
    ->prefix('en/works')
    ->name('works.')
    ->controller(WorkController::class)
    ->group(function () {

        Route::get('/', 'index')
            ->name('index.en');

        Route::get('/{work:slug}', 'show')
            ->name('show.en');

    });

Route::post(
    'works/{work:slug}/reviews',
    [ReviewController::class, 'storeForWork']
)
    ->middleware('throttle:5,1')
    ->name('works.reviews.store');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |------------------------------------------------------------------
        | Guest Admin Routes
        |------------------------------------------------------------------
        */

        Route::middleware('guest:admin')
            ->group(function () {
                Route::get('/login', [AuthController::class, 'showLoginForm'])
                    ->name('login');

                Route::post('/login', [AuthController::class, 'login'])
                    ->middleware('throttle:6,1')
                    ->name('login.submit');

                Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
                    ->name('password.request');

                Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
                    ->middleware('throttle:6,1')
                    ->name('password.email');

                Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
                    ->name('password.reset');

                Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
                    ->middleware('throttle:6,1')
                    ->name('password.update');
            });

        /*
        |------------------------------------------------------------------
        | Protected Admin Routes
        |------------------------------------------------------------------
        */

        Route::middleware('admin.auth')
            ->group(function () {

                /*
                |----------------------------------------------------------
                | Dashboard
                |----------------------------------------------------------
                */

                Route::get('/', [DashboardController::class, 'index'])
                    ->name('dashboard');

                /*
                |----------------------------------------------------------
                | Services Management
                |----------------------------------------------------------
                */

                Route::resource('services', AdminServiceController::class);

                Route::prefix('service-slug-cleanup')
                    ->name('service-slug-cleanup.')
                    ->controller(ServiceSlugCleanupController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::post('/execute', 'execute')
                            ->name('execute');
                    });

                /*
|--------------------------------------------------------------------------
| Works Management
|--------------------------------------------------------------------------
*/

                Route::prefix('works')
                    ->name('works.')
                    ->controller(AdminWorkController::class)
                    ->group(function () {

                        Route::patch(
                            '/{work}/toggle-status',
                            'toggleStatus'
                        )->name('toggle-status');

                        Route::patch(
                            '/{work}/toggle-featured',
                            'toggleFeatured'
                        )->name('toggle-featured');

                        Route::delete(
                            '/{work}/images/{image}',
                            'destroyImage'
                        )->name('images.destroy');

                        Route::get('/', 'index')
                            ->name('index');

                        Route::get('/create', 'create')
                            ->name('create');

                        Route::post('/', 'store')
                            ->name('store');

                        Route::get('/{work}', 'show')
                            ->name('show');

                        Route::get('/{work}/edit', 'edit')
                            ->name('edit');

                        Route::put('/{work}', 'update')
                            ->name('update');

                        Route::delete('/{work}', 'destroy')
                            ->name('destroy');

                    });

                /*
                |----------------------------------------------------------
                | News Management
                |----------------------------------------------------------
                */

                Route::patch(
                    'news/{news}/toggle-status',
                    [AdminNewsController::class, 'toggleStatus']
                )->name('news.toggle-status');

                Route::patch(
                    'news/{news}/toggle-featured',
                    [AdminNewsController::class, 'toggleFeatured']
                )->name('news.toggle-featured');

                Route::patch(
                    'news/{news}/toggle-breaking',
                    [AdminNewsController::class, 'toggleBreaking']
                )->name('news.toggle-breaking');

                Route::resource(
                    'news',
                    AdminNewsController::class
                )->except('show');

                /*
                |----------------------------------------------------------
                | Articles Management (مقالاتي)
                |----------------------------------------------------------
                */

                Route::patch(
                    'articles/{article}/toggle-status',
                    [AdminArticleController::class, 'toggleStatus']
                )->name('articles.toggle-status');

                Route::patch(
                    'articles/{article}/toggle-featured',
                    [AdminArticleController::class, 'toggleFeatured']
                )->name('articles.toggle-featured');

                Route::resource(
                    'articles',
                    AdminArticleController::class
                )->except('show');

                Route::patch(
                    'article-categories/{articleCategory}/toggle-status',
                    [ArticleCategoryController::class, 'toggleStatus']
                )->name('article-categories.toggle-status');

                Route::resource(
                    'article-categories',
                    ArticleCategoryController::class
                )->except('show');

                /*
                |----------------------------------------------------------
                | Technologies Management
                |----------------------------------------------------------
                */

                Route::patch(
                    'technologies/{technology}/toggle-status',
                    [TechnologyController::class, 'toggleStatus']
                )->name('technologies.toggle-status');

                Route::resource(
                    'technologies',
                    TechnologyController::class
                );

                /*
                |----------------------------------------------------------
                | Community Management
                |----------------------------------------------------------
                */

                Route::resource(
                    'community',
                    CommunityPostController::class
                )->except('show');

                /*
                |----------------------------------------------------------
                | Community Comments Moderation
                |----------------------------------------------------------
                */

                Route::prefix('community-comments')
                    ->name('community-comments.')
                    ->controller(AdminCommunityCommentController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::patch('/{communityComment}/approve', 'approve')
                            ->name('approve');

                        Route::patch('/{communityComment}/reject', 'reject')
                            ->name('reject');

                        Route::patch('/{communityComment}/block', 'block')
                            ->name('block');

                        Route::delete('/{communityComment}', 'destroy')
                            ->name('destroy');
                    });

                /*
                |----------------------------------------------------------
                | Pronunciation Suggestions Queue
                |----------------------------------------------------------
                */

                Route::prefix('pronunciation-queue')
                    ->name('pronunciation-queue.')
                    ->controller(PronunciationQueueController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::post('/{id}/accept', 'accept')
                            ->name('accept');

                        Route::post('/{id}/reject', 'reject')
                            ->name('reject');
                    });

                /*
                |----------------------------------------------------------
                | Blocked Visitors
                |----------------------------------------------------------
                */

                Route::prefix('blocked-visitors')
                    ->name('blocked-visitors.')
                    ->controller(BlockedVisitorController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::delete('/{blockedVisitor}', 'destroy')
                            ->name('destroy');
                    });

                /*
                |----------------------------------------------------------
                | Reviews Moderation
                |----------------------------------------------------------
                */

                Route::prefix('reviews')
                    ->name('reviews.')
                    ->controller(AdminReviewController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::patch('/{review}/approve', 'approve')
                            ->name('approve');

                        Route::patch('/{review}/reject', 'reject')
                            ->name('reject');

                        Route::patch('/{review}/block', 'block')
                            ->name('block');

                        Route::delete('/{review}', 'destroy')
                            ->name('destroy');
                    });

                /*
                |----------------------------------------------------------
                | Social Links Management
                |----------------------------------------------------------
                */

                Route::resource(
                    'social-links',
                    AdminSocialLinkController::class
                )->except('show');

                /*
                |----------------------------------------------------------
                | FAQs Management
                |----------------------------------------------------------
                */

                Route::patch(
                    'faqs/{faq}/toggle-status',
                    [FaqController::class, 'toggleStatus']
                )->name('faqs.toggle-status');

                Route::resource(
                    'faqs',
                    FaqController::class
                )->except('show');

                /*
                |----------------------------------------------------------
                | Contact Messages
                |----------------------------------------------------------
                */

                Route::prefix('messages')
                    ->name('messages.')
                    ->controller(AdminMessageController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::get('/{message}', 'show')
                            ->name('show');

                        Route::patch('/{message}/mark-replied', 'markReplied')
                            ->name('mark-replied');

                        Route::delete('/{message}', 'destroy')
                            ->name('destroy');
                    });

                /*
                |----------------------------------------------------------
                | Marketers (Referral Program)
                |----------------------------------------------------------
                */

                Route::patch(
                    'marketers/{marketer}/toggle-status',
                    [MarketerController::class, 'toggleStatus']
                )->name('marketers.toggle-status');

                Route::post(
                    'marketers/{marketer}/customers',
                    [MarketerController::class, 'storeCustomer']
                )->name('marketers.customers.store');

                Route::delete(
                    'marketers/{marketer}/customers/{customer}',
                    [MarketerController::class, 'destroyCustomer']
                )->name('marketers.customers.destroy');

                Route::resource(
                    'marketers',
                    MarketerController::class
                )->except('show');

                /*
                |----------------------------------------------------------
                | Voice Studio (SoundInk)
                |----------------------------------------------------------
                */

                Route::get(
                    'voice-studio',
                    [VoiceStudioController::class, 'index']
                )->name('voice-studio.index');

                Route::post(
                    'voice-studio/generate',
                    [VoiceStudioController::class, 'generate']
                )->name('voice-studio.generate');

                Route::get(
                    'voice-studio/download/{filename}',
                    [VoiceStudioController::class, 'download']
                )->name('voice-studio.download');

                /*
                |----------------------------------------------------------
                | النشر الذكي (Publish API)
                |----------------------------------------------------------
                */

                Route::get(
                    'publish',
                    [PublishController::class, 'index']
                )->name('publish.index');

                Route::post(
                    'publish/generate',
                    [PublishController::class, 'generate']
                )->name('publish.generate');

                Route::get(
                    'publish/{job}/status',
                    [PublishController::class, 'status']
                )->name('publish.status');

                Route::post(
                    'publish/{job}/approve',
                    [PublishController::class, 'approve']
                )->name('publish.approve');

                /*
                |----------------------------------------------------------
                | Settings
                |----------------------------------------------------------
                */

                Route::get(
                    'settings',
                    [SettingController::class, 'index']
                )->name('settings.index');

                Route::patch(
                    'settings/toggle-maintenance',
                    [SettingController::class, 'toggleMaintenance']
                )->name('settings.toggle-maintenance');

                Route::patch(
                    'settings/toggle-community-events',
                    [SettingController::class, 'toggleCommunityEvents']
                )->name('settings.toggle-community-events');

                Route::patch(
                    'settings/toggle-articles',
                    [SettingController::class, 'toggleArticles']
                )->name('settings.toggle-articles');

                Route::post(
                    'settings/regenerate-sitemap',
                    [SettingController::class, 'regenerateSitemap']
                )->name('settings.regenerate-sitemap');

                Route::patch(
                    'settings/profile',
                    [SettingController::class, 'updateProfile']
                )->name('settings.update-profile');

                Route::patch(
                    'settings/password',
                    [SettingController::class, 'updatePassword']
                )->name('settings.update-password');

                Route::patch(
                    'settings/contact-info',
                    [SettingController::class, 'updateContactInfo']
                )->name('settings.update-contact-info');

                /*
                |----------------------------------------------------------
                | YouTube Integration
                |----------------------------------------------------------
                */

                Route::prefix('youtube')
                    ->name('youtube.')
                    ->controller(YoutubeAuthController::class)
                    ->group(function () {
                        Route::get('/', 'index')
                            ->name('index');

                        Route::get('/connect', 'connect')
                            ->name('connect');

                        Route::get('/callback', 'callback')
                            ->name('callback');

                        Route::delete('/', 'disconnect')
                            ->name('disconnect');
                    });

                /*
                |----------------------------------------------------------
                | Server Info
                |----------------------------------------------------------
                */

                Route::get(
                    'server-info',
                    [ServerInfoController::class, 'index']
                )->name('server-info.index');

                /*
                |----------------------------------------------------------
                | Logout
                |----------------------------------------------------------
                */

                Route::post('/logout', [AuthController::class, 'logout'])
                    ->name('logout');
            });
    });

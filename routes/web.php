<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\WorkController;

/*
|--------------------------------------------------------------------------
| Admin Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CommunityPostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\VoiceStudioController;
use App\Http\Controllers\Admin\WorkController as AdminWorkController;

/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| Language Switch
|--------------------------------------------------------------------------
*/

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::SUPPORTED_LOCALES, true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Public Services
|--------------------------------------------------------------------------
*/

Route::prefix('services')
    ->name('services.')
    ->controller(ServiceController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| Public News
|--------------------------------------------------------------------------
*/

Route::prefix('news')
    ->name('news.')
    ->controller(NewsController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{slug}', 'show')
            ->where('slug', '[^/]+')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| Public Community
|--------------------------------------------------------------------------
*/

Route::prefix('community')
    ->name('community.')
    ->controller(CommunityController::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{communityPost:slug}', 'show')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| Public Social Links
|--------------------------------------------------------------------------
*/

Route::get('/social-links', [SocialLinkController::class, 'index'])
    ->name('social-links.index');

/*
|--------------------------------------------------------------------------
| Public Static Pages
|--------------------------------------------------------------------------
*/

Route::get('/contact', [PageController::class, 'contact'])
    ->name('contact');

Route::get('/about', [PageController::class, 'about'])
    ->name('about');

/*
|--------------------------------------------------------------------------
| Legal Pages
|--------------------------------------------------------------------------
*/

Route::get('/privacy', [PageController::class, 'privacy'])
    ->name('privacy');

Route::get('/terms', [PageController::class, 'terms'])
    ->name('terms');

    /*
|--------------------------------------------------------------------------
| Public Works
|--------------------------------------------------------------------------
*/

Route::prefix('works')
    ->name('works.')
    ->controller(WorkController::class)
    ->group(function () {

        Route::get('/', 'index')
            ->name('index');

        Route::get('/{work:slug}', 'show')
            ->name('show');

    });
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
                    ->name('login.submit');
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

                /*
                |----------------------------------------------------------
                | Logout
                |----------------------------------------------------------
                */

                Route::post('/logout', [AuthController::class, 'logout'])
                    ->name('logout');
            });
    });
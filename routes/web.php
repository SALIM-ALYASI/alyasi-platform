<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\CommunityController;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\CommunityPostController;

/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

/*
|--------------------------------------------------------------------------
| Public Services
|--------------------------------------------------------------------------
*/

Route::prefix('services')
    ->name('services.')
    ->controller(ServiceController::class)
    ->group(function () {

        Route::get(
            '/',
            'index'
        )->name('index');

        Route::get(
            '/{slug}',
            'show'
        )
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

        Route::get(
            '/',
            'index'
        )->name('index');

        Route::get(
            '/{slug}',
            'show'
        )
            ->where('slug', '[^/]+')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| Community
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
| Contact
|--------------------------------------------------------------------------
*/

Route::view(
    '/contact',
    'contact.index'
)->name('contact');

/*
|--------------------------------------------------------------------------
| Informational Pages
|--------------------------------------------------------------------------
*/

Route::view(
    '/about',
    'about.index'
)->name('about');

/*
|--------------------------------------------------------------------------
| Legal Pages
|--------------------------------------------------------------------------
*/

Route::view(
    '/privacy',
    'legal.privacy'
)->name('privacy');

Route::view(
    '/terms',
    'legal.terms'
)->name('terms');

 
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Guest Admin Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware('guest:admin')
            ->group(function () {

                Route::get(
                    '/login',
                    [AuthController::class, 'showLoginForm']
                )->name('login');

                Route::post(
                    '/login',
                    [AuthController::class, 'login']
                )->name('login.submit');
            });

        /*
        |--------------------------------------------------------------------------
        | Protected Admin Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware('admin.auth')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/',
                    [DashboardController::class, 'index']
                )->name('dashboard');

                /*
                |--------------------------------------------------------------------------
                | Services Management
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'services',
                    AdminServiceController::class
                );

                /*
                |--------------------------------------------------------------------------
                | Community Management
                |--------------------------------------------------------------------------
                */

                Route::resource(
                    'community',
                    CommunityPostController::class
                )->except('show');

                /*
                |--------------------------------------------------------------------------
                | Logout
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/logout',
                    [AuthController::class, 'logout']
                )->name('logout');
            });
    });
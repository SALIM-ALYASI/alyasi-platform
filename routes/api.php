<?php

use App\Http\Controllers\Api\NewsIngestController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| News Bot Ingest
|--------------------------------------------------------------------------
*/

Route::post('news', [NewsIngestController::class, 'store'])
    ->middleware(['news-bot.auth', 'throttle:30,1'])
    ->name('api.news.store');

/*
|--------------------------------------------------------------------------
| Services (Public Read API)
|--------------------------------------------------------------------------
*/

Route::prefix('services')
    ->name('api.services.')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/', [ServiceController::class, 'index'])
            ->name('index');

        Route::get('/{slug}', [ServiceController::class, 'show'])
            ->name('show');
    });

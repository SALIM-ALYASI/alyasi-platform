<?php

use App\Http\Controllers\Api\NewsIngestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| News Bot Ingest
|--------------------------------------------------------------------------
*/

Route::post('news', [NewsIngestController::class, 'store'])
    ->middleware('news-bot.auth')
    ->name('api.news.store');

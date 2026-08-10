<?php

use App\Http\Controllers\Api\ArticleIngestController;
use App\Http\Controllers\Api\EventIngestController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NewsIngestController;
use App\Http\Controllers\Api\PublishWebhookController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| News Bot Ingest
|--------------------------------------------------------------------------
*/

Route::post('news', [NewsIngestController::class, 'store'])
    ->middleware(['news-bot.auth', 'throttle:30,1'])
    ->name('api.news.store');

Route::get('news/pending-social', [NewsIngestController::class, 'pendingSocial'])
    ->middleware(['news-bot.auth', 'throttle:60,1'])
    ->name('api.news.pending-social');

Route::patch('news/{newsArticle}/mark-social-sent', [NewsIngestController::class, 'markSocialSent'])
    ->middleware(['news-bot.auth', 'throttle:60,1'])
    ->name('api.news.mark-social-sent');

Route::get('news/daily-digest', [NewsIngestController::class, 'dailyDigest'])
    ->middleware(['news-bot.auth', 'throttle:60,1'])
    ->name('api.news.daily-digest');

/*
|--------------------------------------------------------------------------
| Event Bot Ingest
|--------------------------------------------------------------------------
*/

Route::post('community/events', [EventIngestController::class, 'store'])
    ->middleware(['event-bot.auth', 'throttle:30,1'])
    ->name('api.community.events.store');

Route::get('community/events', [EventIngestController::class, 'index'])
    ->middleware(['event-bot.auth', 'throttle:60,1'])
    ->name('api.community.events.index');

/*
|--------------------------------------------------------------------------
| Article Bot Ingest (n8n — مقالاتي)
|--------------------------------------------------------------------------
*/

Route::post('articles', [ArticleIngestController::class, 'store'])
    ->middleware(['article-bot.auth', 'throttle:30,1'])
    ->name('api.articles.store');

Route::get('articles', [ArticleIngestController::class, 'index'])
    ->middleware(['article-bot.auth', 'throttle:60,1'])
    ->name('api.articles.index');

/*
|--------------------------------------------------------------------------
| Manager Bot (Telegram admin control)
|--------------------------------------------------------------------------
*/

Route::post('community/events/{id}/image', [EventIngestController::class, 'updateImage'])
    ->whereNumber('id')
    ->middleware(['manager-bot.auth', 'throttle:30,1'])
    ->name('api.community.events.update-image');

/*
|--------------------------------------------------------------------------
| Publish Webhook (Smart Publish status updates)
|--------------------------------------------------------------------------
*/

Route::post('publish-webhook', [PublishWebhookController::class, 'update'])
    ->middleware(['publish-webhook.auth', 'throttle:60,1'])
    ->name('api.publish-webhook.update');

/*
|--------------------------------------------------------------------------
| WhatsApp Cloud API Webhook
|--------------------------------------------------------------------------
*/

Route::get('whatsapp-webhook', [WhatsAppWebhookController::class, 'verify'])
    ->name('api.whatsapp-webhook.verify');

Route::post('whatsapp-webhook', [WhatsAppWebhookController::class, 'receive'])
    ->middleware('throttle:60,1')
    ->name('api.whatsapp-webhook.receive');

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

/*
|--------------------------------------------------------------------------
| News (Public Read API) — قائمة وتفاصيل الأخبار المنشورة فقط للقراءة
|--------------------------------------------------------------------------
*/

Route::prefix('news-articles')
    ->name('api.news-articles.')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/', [NewsController::class, 'index'])
            ->name('index');

        Route::get('/{slug}', [NewsController::class, 'show'])
            ->name('show');
    });

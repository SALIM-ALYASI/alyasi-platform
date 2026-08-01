<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AuthenticateNewsBot;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
            'news-bot.auth' => AuthenticateNewsBot::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
            CheckMaintenanceMode::class,
            TrackVisit::class,
        ]);

        $middleware->append(SecurityHeaders::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

    })
    ->create();

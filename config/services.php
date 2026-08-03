<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'news_bot' => [
        'token' => env('NEWS_BOT_TOKEN'),
    ],

    'soundink' => [
        'url' => env('SOUNDINK_API_URL', 'http://127.0.0.1:5050'),
        'key' => env('SOUNDINK_API_KEY'),
    ],

    'publish' => [
        'url' => env('PUBLISH_API_URL', 'http://167.233.163.230:6062'),
        'key' => env('PUBLISH_API_KEY'),
        'webhook_token' => env('PUBLISH_WEBHOOK_TOKEN'),
    ],

];

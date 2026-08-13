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

    'n8n' => [
        'news_webhook_url' => env('N8N_NEWS_WEBHOOK_URL'),
    ],

    'event_bot' => [
        'token' => env('EVENT_BOT_TOKEN'),
    ],

    'article_bot' => [
        'token' => env('ARTICLE_BOT_TOKEN'),
    ],

    'manager_bot' => [
        'token' => env('MANAGER_BOT_TOKEN'),
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

    'whatsapp_cloud' => [
        'token' => env('WHATSAPP_CLOUD_TOKEN'),
        'phone_number_id' => env('WHATSAPP_CLOUD_PHONE_NUMBER_ID'),
        'webhook_verify_token' => env('WHATSAPP_CLOUD_WEBHOOK_VERIFY_TOKEN'),
    ],


    'facebook' => [
        'webhook_verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],

    'instagram' => [
    'webhook_verify_token' => env('INSTAGRAM_WEBHOOK_VERIFY_TOKEN'),
    ],

];
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
        // نفس workflow "ALYASI — Gmail Send" اللي يستخدمه بوت الأخبار (نفس
        // N8N_GMAIL_WEBHOOK_SECRET = PUBLISHER_INTERNAL_SECRET بـnews-bot-v2).
        'gmail_webhook_url' => env('N8N_GMAIL_WEBHOOK_URL', 'https://n8n.alyasi.dev/webhook/alyasi-gmail-send'),
        'gmail_webhook_secret' => env('N8N_GMAIL_WEBHOOK_SECRET'),
        'analysis_digest_recipients' => env('ANALYSIS_DIGEST_RECIPIENTS', 'r.m.alyasi@gmail.com,alyasi8blus256@gmail.com'),
    ],

    'event_bot' => [
        'token' => env('EVENT_BOT_TOKEN'),
    ],

    'product_watch' => [
        'token' => env('PRODUCT_WATCH_TOKEN'),
    ],

    'article_bot' => [
        'token' => env('ARTICLE_BOT_TOKEN'),
    ],

    'conference_journalist' => [
        'user_token' => env('CJ_USER_TOKEN'),
        'relay_token' => env('CJ_RELAY_TOKEN'),
        'agent_token' => env('CJ_AGENT_TOKEN'),
    ],

    'manager_bot' => [
        'token' => env('MANAGER_BOT_TOKEN'),
        'chat_id' => env('MANAGER_BOT_CHAT_ID'),
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

    'smart_content' => [
        'url' => env('SMART_CONTENT_API_URL', 'https://smart-content.alyasi.dev/api'),
        'key' => env('SMART_CONTENT_API_KEY'),
        // مفتاح منفصل بصلاحية قراءة /jobs/{id}/platforms -- نفس المفتاح
        // المستخدم أصلاً بعقدة n8n "Forward — Smart Content"، مفتاح
        // smart_content.key أعلاه ما له صلاحية هذا المسار.
        'jobs_api_key' => env('SMART_CONTENT_JOBS_API_KEY'),
    ],

    'whatsapp_cloud' => [
        'token' => env('WHATSAPP_CLOUD_TOKEN'),
        'phone_number_id' => env('WHATSAPP_CLOUD_PHONE_NUMBER_ID'),
        'webhook_verify_token' => env('WHATSAPP_CLOUD_WEBHOOK_VERIFY_TOKEN'),
    ],

    // جسر واتساب غير رسمي (WhatsApp Web session) لتنبيهات المالك الفورية —
    // منفصل تمامًا عن whatsapp_cloud أعلاه (API الرسمي، غير مفعّل حاليًا).
    'whatsapp_notify' => [
        'base_url' => env('WHATSAPP_NOTIFY_BASE_URL'),
        'api_key' => env('WHATSAPP_NOTIFY_API_KEY'),
        'number' => env('WHATSAPP_NOTIFY_NUMBER'),
    ],


    'facebook' => [
        'webhook_verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],

    'instagram' => [
    'webhook_verify_token' => env('INSTAGRAM_WEBHOOK_VERIFY_TOKEN'),
    ],

    'meta_whatsapp' => [
    'webhook_verify_token' => env('META_WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'phone_id' => env('META_WHATSAPP_PHONE_ID'),
    'token' => env('META_WHATSAPP_TOKEN'),
    ],

    // رقم التنبيهات (92378452) -- توكن منتهي، بوت أخبار متوقف، خدمات سيرفر
    // البيت متوقفة، أخطاء Laravel حرجة. منفصل عن meta_whatsapp (رقم الأخبار).
    'meta_whatsapp_alerts' => [
    'phone_id' => env('META_WHATSAPP_ALERTS_PHONE_ID'),
    'token' => env('META_WHATSAPP_ALERTS_TOKEN'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

];
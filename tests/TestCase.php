<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // الاختبارات ما ترسل تنبيهات حقيقية (واتساب/تلجرام) ولا تطلع للإنترنت:
        // اختبار أرسل مرة تنبيه موعد COMEX وهمي لواتساب سالم.
        config([
            'services.whatsapp_notify.base_url' => null,
            'services.whatsapp_notify.api_key' => null,
            'services.whatsapp_notify.number' => null,
            'services.meta_whatsapp_alerts.phone_id' => null,
            'services.meta_whatsapp_alerts.token' => null,
            'services.manager_bot.token' => null,
            'services.manager_bot.chat_id' => null,
        ]);

        Http::preventStrayRequests();
    }
}

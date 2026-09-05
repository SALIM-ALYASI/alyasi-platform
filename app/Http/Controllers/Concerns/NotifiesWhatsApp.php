<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait NotifiesWhatsApp
{
    /**
     * إرسال رسالة واتساب فورية عبر جسر واتساب الخاص (مثلاً عند نشر خبر جديد).
     * لا يوقف العملية الأصلية إن فشل الإرسال أو كانت الإعدادات ناقصة.
     */
    protected function notifyWhatsApp(string $message, ?string $number = null): void
    {
        $baseUrl = config('services.whatsapp_notify.base_url');
        $apiKey = config('services.whatsapp_notify.api_key');
        $number ??= config('services.whatsapp_notify.number');

        if (blank($baseUrl) || blank($apiKey) || blank($number)) {
            return;
        }

        try {
            Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(10)
                ->post(rtrim($baseUrl, '/').'/send-message', [
                    'number' => $number,
                    'message' => $message,
                ]);
        } catch (\Throwable $e) {
            Log::warning('تعذّر إرسال رسالة واتساب.', ['error' => $e->getMessage()]);
        }
    }
}

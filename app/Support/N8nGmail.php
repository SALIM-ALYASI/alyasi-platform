<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * إرسال إيميل عبر workflow "ALYASI — Gmail Send" الجاهز بـn8n (نفس الآلية
 * اللي يستخدمها بوت الأخبار لإشعارات FYI) -- بدل تكرار إعداد SMTP/Gmail API
 * من جديد داخل Laravel نفسه.
 */
class N8nGmail
{
    public static function send(string $to, string $subject, string $message): bool
    {
        $url = config('services.n8n.gmail_webhook_url');
        $secret = config('services.n8n.gmail_webhook_secret');

        if (blank($url) || blank($secret)) {
            Log::warning('إرسال إيميل عبر n8n متوقف: الإعدادات ناقصة.');

            return false;
        }

        try {
            $response = Http::withHeaders(['X-Internal-Secret' => $secret])
                ->timeout(15)
                ->post($url, [
                    'to' => $to,
                    'subject' => $subject,
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('فشل إرسال إيميل عبر n8n.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('تعذّر الاتصال بـn8n لإرسال إيميل.', ['error' => $e->getMessage()]);

            return false;
        }
    }
}

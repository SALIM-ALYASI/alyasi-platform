<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * منطق إرسال التنبيه نفسه (رقم التنبيهات الرسمي 92378452، مع رجوع تلقائي
 * للجسر المجاني عند الفشل) -- مستقل عن أي Controller/trait عشان يُستدعى من
 * سياقات بلا Controller مثل bootstrap/app.php (معالج الأخطاء الحرجة).
 */
class WhatsAppAlerts
{
    public static function send(string $message): void
    {
        $text = '🚨 '.$message;

        $phoneId = config('services.meta_whatsapp_alerts.phone_id');
        $token = config('services.meta_whatsapp_alerts.token');
        $recipient = config('services.whatsapp_notify.number');

        if (filled($phoneId) && filled($token) && filled($recipient)) {
            try {
                $response = Http::withToken($token)
                    ->timeout(10)
                    ->post("https://graph.facebook.com/v24.0/{$phoneId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to' => $recipient,
                        'type' => 'text',
                        'text' => ['body' => $text],
                    ]);

                if ($response->successful()) {
                    return;
                }

                Log::warning('فشل إرسال تنبيه عبر رقم التنبيهات الرسمي.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('تعذّر الاتصال برقم التنبيهات الرسمي.', ['error' => $e->getMessage()]);
            }
        }

        self::sendViaBridge($text);
    }

    private static function sendViaBridge(string $text): void
    {
        $baseUrl = config('services.whatsapp_notify.base_url');
        $apiKey = config('services.whatsapp_notify.api_key');
        $number = config('services.whatsapp_notify.number');

        if (blank($baseUrl) || blank($apiKey) || blank($number)) {
            return;
        }

        try {
            Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(10)
                ->post(rtrim($baseUrl, '/').'/send-message', [
                    'number' => $number,
                    'message' => $text,
                ]);
        } catch (\Throwable $e) {
            Log::warning('تعذّر إرسال تنبيه عبر الجسر.', ['error' => $e->getMessage()]);
        }
    }
}

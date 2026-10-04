<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
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
                    self::rememberFallback($response->json('messages.0.id'), $text);

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

    /**
     * ميتا تقبل الرسالة فورًا (200) وترفضها بعدين عبر webhook الحالات لو
     * نافذة الـ24 ساعة مقفلة (خطأ 131047) -- فالرجوع المتزامن للجسر ما
     * يتفعّل أبدًا بهذي الحالة. نحفظ نص الرجوع بمعرّف الرسالة، وwebhook
     * الحالات (MetaWhatsAppWebhookController) يرسله عبر الجسر لو فشلت.
     */
    public static function rememberFallback(?string $messageId, string $fallbackText): void
    {
        if (blank($messageId)) {
            return;
        }

        Cache::put(self::fallbackKey($messageId), $fallbackText, now()->addDay());
    }

    /**
     * يُستدعى لكل حالة رسالة واردة من webhook ميتا. يرجع true لو كانت
     * رسالة فاشلة لها نص رجوع محفوظ وأُرسل عبر الجسر.
     */
    public static function handleStatus(array $status): bool
    {
        if (($status['status'] ?? null) !== 'failed' || blank($status['id'] ?? null)) {
            return false;
        }

        $fallbackText = Cache::pull(self::fallbackKey($status['id']));

        if (blank($fallbackText)) {
            return false;
        }

        Log::info('رسالة واتساب رسمية فشلت بعد القبول -- أُرسلت عبر الجسر بدلها.', [
            'error_code' => $status['errors'][0]['code'] ?? null,
        ]);

        self::sendViaBridge($fallbackText);

        return true;
    }

    private static function fallbackKey(string $messageId): string
    {
        return 'wa-fallback:'.$messageId;
    }

    public static function sendViaBridge(string $text): void
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

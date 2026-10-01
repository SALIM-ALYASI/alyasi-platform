<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    /**
     * رسالة خبر بزر "اقرأ الخبر" (صورة + نص + زر CTA يفتح رابط الخبر) عبر
     * رقم الأخبار الرسمي (94443706، واتساب Cloud API الرسمي) -- نفس أسلوب
     * رسائل الأعمال الجاهزة (مثل "Manage subscription" من Hostinger).
     *
     * هذا النوع يحتاج نافذة محادثة مفتوحة (24 ساعة من آخر رسالة من المستلم
     * لهذا الرقم)، وإلا ترفضه ميتا. فشل الإرسال هنا -- لأي سبب -- يرجع
     * تلقائيًا لرسالة نصية عادية عبر الجسر المجاني، عشان الخبر يوصل دايمًا.
     */
    protected function notifyWhatsAppNewsCta(
        string $bodyText,
        ?string $imageUrl,
        string $articleUrl,
        string $fallbackMessage,
    ): void {
        $phoneId = config('services.meta_whatsapp.phone_id');
        $token = config('services.meta_whatsapp.token');
        $recipient = config('services.whatsapp_notify.number');

        if (blank($phoneId) || blank($token) || blank($recipient)) {
            $this->notifyWhatsApp($fallbackMessage);

            return;
        }

        $interactive = [
            'type' => 'cta_url',
            'body' => ['text' => Str::limit($bodyText, 1024)],
            'action' => [
                'name' => 'cta_url',
                'parameters' => [
                    'display_text' => 'اقرأ الخبر',
                    'url' => $articleUrl,
                ],
            ],
        ];

        if (filled($imageUrl)) {
            $interactive['header'] = [
                'type' => 'image',
                'image' => ['link' => $imageUrl],
            ];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post("https://graph.facebook.com/v24.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $recipient,
                    'type' => 'interactive',
                    'interactive' => $interactive,
                ]);

            if (! $response->successful()) {
                Log::warning('فشل إرسال رسالة الخبر بزر CTA عبر واتساب الرسمي.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->notifyWhatsApp($fallbackMessage);
            }
        } catch (\Throwable $e) {
            Log::warning('تعذّر الاتصال بواتساب الرسمي لإرسال خبر بزر CTA.', ['error' => $e->getMessage()]);
            $this->notifyWhatsApp($fallbackMessage);
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendWhatsAppTestMessage extends Command
{
    protected $signature = 'whatsapp:send-test {number=96898881054 : Recipient number with country code, digits only} {--text= : Send free-form text instead of the hello_world template (requires an open 24h session)}';

    protected $description = 'يرسل رسالة اختبار عبر WhatsApp Cloud API إلى رقم محدد';

    public function handle(): int
    {
        $token = config('services.whatsapp_cloud.token');
        $phoneNumberId = config('services.whatsapp_cloud.phone_number_id');

        if (! $token || ! $phoneNumberId) {
            $this->error('WHATSAPP_CLOUD_TOKEN أو WHATSAPP_CLOUD_PHONE_NUMBER_ID غير موجودة في ملف .env');

            return self::FAILURE;
        }

        $to = preg_replace('/\D/', '', (string) $this->argument('number'));
        $text = $this->option('text');

        $payload = $text
            ? [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $text],
            ]
            : [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => 'hello_world',
                    'language' => ['code' => 'en_US'],
                ],
            ];

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", $payload);

        if ($response->failed()) {
            $this->error('فشل الإرسال: '.$response->body());

            return self::FAILURE;
        }

        $this->info('تم إرسال الرسالة إلى '.$to);
        $this->line($response->body());

        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait NotifiesManagerBot
{
    /**
     * إرسال إشعار فوري لبوت تلجرام الإداري (مثلاً عند تعليق أو تقييم جديد بانتظار
     * المراجعة). لا يوقف حفظ السجل الأصلي إن فشل الإرسال.
     */
    protected function notifyManagerBot(string $text): void
    {
        $token = config('services.manager_bot.token');
        $chatId = config('services.manager_bot.chat_id');

        if (blank($token) || blank($chatId)) {
            return;
        }

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::warning('تعذّر إرسال إشعار تلجرام.', ['error' => $e->getMessage()]);
        }
    }
}

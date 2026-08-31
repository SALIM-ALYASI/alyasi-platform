<?php

namespace App\Console\Commands;

use App\Http\Controllers\Concerns\NotifiesManagerBot;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * فحص خارجي لتوفر السيرفر المنزلي (n8n + SoundInk + بوت الأخبار)، يعمل من
 * سيرفر الموقع بهوستنجر -- شبكة منفصلة تمامًا. مراقب n8n الداخلي
 * (Server Watchdog) لا يقدر ينبّه لو السيرفر المنزلي نفسه سقط بالكامل، لأنه
 * يعمل على نفس الجهاز المفروض يراقبه؛ هذا الأمر يسد تلك الثغرة تحديدًا.
 */
class CheckHomeServerHealth extends Command
{
    use NotifiesManagerBot;

    protected $signature = 'home-server:check';

    protected $description = 'يتحقق من توفر السيرفر المنزلي عبر n8n.alyasi.dev وينبّه بتلجرام عند الانقطاع أو التعافي';

    public function handle(): int
    {
        $healthy = false;

        try {
            $response = Http::timeout(10)->get('https://n8n.alyasi.dev/healthz');
            $healthy = $response->successful();
        } catch (\Throwable) {
            $healthy = false;
        }

        $wasDown = Setting::get('home_server_down_alerted') === '1';

        if (! $healthy) {
            $failCount = (int) Setting::get('home_server_fail_count', '0') + 1;
            Setting::set('home_server_fail_count', (string) $failCount);

            // فشلان متتاليان (~10 دقايق بفحص كل 5 دقايق) قبل التنبيه -- يتجنب
            // إنذار كاذب من عطل شبكة أو تجاوز مهلة لحظي.
            if ($failCount >= 2 && ! $wasDown) {
                $this->notifyManagerBot('🔴 السيرفر المنزلي غير متاح (تعذّر الوصول لـ n8n.alyasi.dev لمحاولتين متتاليتين). هذا يعني توقف n8n وSoundInk وبوت الأخبار جميعًا.');
                Setting::set('home_server_down_alerted', '1');
                Setting::set('home_server_down_since', now()->toDateTimeString());
            }

            return self::SUCCESS;
        }

        Setting::set('home_server_fail_count', '0');

        if ($wasDown) {
            $downSince = Setting::get('home_server_down_since');
            $this->notifyManagerBot(
                '🟢 السيرفر المنزلي رجع يشتغل'.($downSince ? " (كان متوقف منذ {$downSince})" : '').'.'
            );
            Setting::set('home_server_down_alerted', '0');
            Setting::set('home_server_down_since', null);
        }

        return self::SUCCESS;
    }
}

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// مزامنة أسعار متجر Apple الإمارات أربع مرات يوميًا.
// withoutOverlapping يمنع تشغيل نسختين من المهمة في الوقت نفسه.
Schedule::command('apple:sync-prices')
    ->cron('17 */6 * * *')
    ->withoutOverlapping(30);

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

// تحديث سعر USD/KWD يوميًا من بنك الكويت المركزي.
// بقية عملات الخليج المستخدمة هنا مرتبطة بالدولار رسميًا، لذلك لا تحتاج
// طلب شبكة يومي. الخدمة نفسها تحتفظ بآخر سعر كويتي رسمي صالح إذا فشل المصدر.
Schedule::command('fx:refresh-gulf-rates')
    ->dailyAt('05:10')
    ->timezone('Asia/Muscat')
    ->withoutOverlapping(15);

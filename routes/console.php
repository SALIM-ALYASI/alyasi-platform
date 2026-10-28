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
    ->cron('15 */6 * * *') // الدقيقة 15: الجدولة تنطلق من سيرفر البيت كل 5 دقايق
    ->withoutOverlapping(30);

// تحديث سعر USD/KWD يوميًا من بنك الكويت المركزي.
// بقية عملات الخليج المستخدمة هنا مرتبطة بالدولار رسميًا، لذلك لا تحتاج
// طلب شبكة يومي. الخدمة نفسها تحتفظ بآخر سعر كويتي رسمي صالح إذا فشل المصدر.
Schedule::command('fx:refresh-gulf-rates')
    ->dailyAt('05:10')
    ->timezone('Asia/Muscat')
    ->withoutOverlapping(15);

// طلبات العملاء: اللي ما انختارت بالأفضل 5 تنحذف بعد 8 أيام، والباقي بعد 60 يومًا.
Schedule::command('model:prune', ['--model' => [\App\Models\ServiceLead::class]])
    ->dailyAt('04:20')
    ->timezone('Asia/Muscat');

// أفضل 5 طلبات عملاء بالأسبوع: رسالة واتساب وحدة كل أحد 9 الصبح (بدل التنبيهات اليومية).
Artisan::command('leads:weekly-top', function (): void {
    $sent = \App\Support\Leads\LeadNotifier::sendWeeklyTop();
    $this->info("أُرسل: {$sent}");
})->purpose('أفضل 5 طلبات عملاء هذا الأسبوع لواتساب');

Schedule::command('leads:weekly-top')
    ->weeklyOn(0, '9:00')
    ->timezone('Asia/Muscat');

// سجلات Telescope (أخطاء وطلبات فاشلة) تنحذف بعد 7 أيام بدل ما تتراكم للأبد.
Schedule::command('telescope:prune', ['--hours' => 168])
    ->dailyAt('04:40')
    ->timezone('Asia/Muscat');

// وكيل نبض عُمان: خلاصات وكالة الأنباء العُمانية كل نص ساعة (أخبار تقنية فقط).
Schedule::command('oman-pulse:scan')
    ->everyThirtyMinutes()
    ->withoutOverlapping(10);

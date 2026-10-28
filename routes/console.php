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

// طلبات العملاء (Grok) تنحذف بعد 60 يومًا.
Schedule::command('model:prune', ['--model' => [\App\Models\ServiceLead::class]])
    ->dailyAt('04:20')
    ->timezone('Asia/Muscat');

// قائمة مشاريع مستقل وخمسات اليومية (احتياط لو ما وصلت دفعة Grok المسائية بعد 7).
Artisan::command('leads:marketplace-digest', function (): void {
    \App\Support\Leads\LeadNotifier::marketplaceDigestIfDue();
})->purpose('إرسال قائمة مشاريع مستقل وخمسات اليومية لواتساب');

Schedule::command('leads:marketplace-digest')
    ->dailyAt('21:30')
    ->timezone('Asia/Muscat');

// سجلات Telescope (أخطاء وطلبات فاشلة) تنحذف بعد 7 أيام بدل ما تتراكم للأبد.
Schedule::command('telescope:prune', ['--hours' => 168])
    ->dailyAt('04:40')
    ->timezone('Asia/Muscat');

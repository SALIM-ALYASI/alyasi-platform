<?php

namespace App\Support\Leads;

use App\Models\ServiceLead;
use App\Support\WhatsAppAlerts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * من 28 أكتوبر (قرار سالم): بدل تنبيه لكل دفعة وقائمة يومية، رسالة وحدة بالأسبوع
 * فيها أفضل 5 طلبات (weeklyTop). notify وmarketplaceDigestIfDue باقيين للاستخدام
 * اليدوي بس، ما ينادَون تلقائيًا.
 */
final class LeadNotifier
{
    public static function notify(Collection $leads): void
    {
        if ($leads->isEmpty()) {
            return;
        }

        $sorted = $leads->sortByDesc('score')->values();

        foreach ($sorted->chunk(5) as $chunkIndex => $chunk) {
            $lines = [$chunkIndex === 0 ? 'طلبات عملاء جديدة ('.$sorted->count().')' : 'تكملة الطلبات'];

            foreach ($chunk->values() as $i => $lead) {
                $lines[] = '';
                $lines[] = ($chunkIndex * 5 + $i + 1).') '.$lead->countryLabel().($lead->city ? ' – '.$lead->city : '').' · '.$lead->categoryLabel().' · '.$lead->platformLabel();
                $lines[] = Str::limit($lead->summary ?: preg_replace('/\s+/u', ' ', $lead->text), 220);
                if ($lead->budget) {
                    $lines[] = 'الميزانية: '.$lead->budget;
                }
                if ($lead->deadline) {
                    $lines[] = 'الموعد: '.$lead->deadline;
                }
                $lines[] = $lead->url;
            }

            $lines[] = '';
            $lines[] = 'كل الطلبات: '.route('admin.service-leads.index');

            WhatsAppAlerts::send(implode("\n", $lines), '🎯 ');
        }

        ServiceLead::query()->whereKey($sorted->pluck('id'))->update(['notified_at' => now()]);
    }

    /**
     * قائمة مشاريع مستقل وخمسات: رسالة وحدة باليوم بعد 7 المسا (مسقط) -- مع أول
     * دفعة من Grok بعد هالوقت، أو من المهمة المجدولة 9:30 كاحتياط.
     */
    public static function marketplaceDigestIfDue(): void
    {
        $now = now('Asia/Muscat');

        if ($now->hour < 19) {
            return;
        }

        $leads = ServiceLead::query()
            ->where('scope', 'marketplace')
            ->where('status', 'new')
            ->whereNull('notified_at')
            ->where('created_at', '>=', now()->subDays(3))
            ->orderByDesc('budget_usd')
            ->take(8)
            ->get();

        // مرة وحدة باليوم، وما نقفل اليوم لو ما فيه شي (يمكن توصل دفعة بعدين).
        if ($leads->isEmpty() || ! Cache::add('leads:marketplace-digest:'.$now->toDateString(), true, $now->copy()->endOfDay())) {
            return;
        }

        $lines = ['أفضل مشاريع مستقل وخمسات اليوم ('.$leads->count().')'];

        foreach ($leads->values() as $i => $lead) {
            $lines[] = '';
            $lines[] = ($i + 1).') '.$lead->platformLabel().' · '.$lead->categoryLabel().' · $'.number_format($lead->budget_usd).($lead->budget ? ' ('.$lead->budget.')' : '');
            $lines[] = Str::limit($lead->summary ?: preg_replace('/\s+/u', ' ', $lead->text), 200);
            $lines[] = $lead->url;
        }

        $lines[] = '';
        $lines[] = 'كل الطلبات: '.route('admin.service-leads.index', ['scope' => 'marketplace']);

        WhatsAppAlerts::send(implode("\n", $lines), '📋 ');

        ServiceLead::query()->whereKey($leads->pluck('id'))->update(['notified_at' => now()]);
    }

    public const WEEKLY_COUNT = 5;

    /**
     * أفضل 5 طلبات من آخر 7 أيام: الميزانية بالدولار، مع أفضلية للخليج (+300)
     * وعُمان (+300) لأن العميل القريب أسهل يتحوّل لمشروع.
     */
    public static function weeklyTop(): Collection
    {
        return ServiceLead::query()
            ->where('status', 'new')
            ->whereNull('notified_at')
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->sortByDesc(fn (ServiceLead $lead) => (int) $lead->budget_usd
                + ($lead->scope === 'gcc' ? 300 : 0)
                + ($lead->country === 'OM' ? 300 : 0))
            ->take(self::WEEKLY_COUNT)
            ->values();
    }

    public static function sendWeeklyTop(): int
    {
        $now = now('Asia/Muscat');
        $leads = self::weeklyTop();

        if ($leads->isEmpty() || ! Cache::add('leads:weekly-top:'.$now->format('o-W'), true, $now->copy()->endOfWeek())) {
            return 0;
        }

        $lines = ['أفضل '.$leads->count().' طلبات عملاء هذا الأسبوع'];

        foreach ($leads as $i => $lead) {
            $where = $lead->scope === 'gcc'
                ? $lead->countryLabel().($lead->city ? ' – '.$lead->city : '')
                : $lead->platformLabel();
            $lines[] = '';
            $lines[] = ($i + 1).') '.$where.' · '.$lead->categoryLabel().($lead->budget_usd ? ' · $'.number_format($lead->budget_usd) : ($lead->budget ? ' · '.$lead->budget : ''));
            $lines[] = Str::limit($lead->summary ?: preg_replace('/\s+/u', ' ', $lead->text), 200);
            $lines[] = $lead->url;
        }

        WhatsAppAlerts::send(implode("\n", $lines), '🎯 ');

        ServiceLead::query()->whereKey($leads->pluck('id'))->update(['notified_at' => now()]);

        return $leads->count();
    }
}

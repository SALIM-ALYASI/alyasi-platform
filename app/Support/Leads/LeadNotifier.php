<?php

namespace App\Support\Leads;

use App\Models\ServiceLead;
use App\Support\WhatsAppAlerts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * رسالة واتساب وحدة لكل دفعة طلبات (الأعلى أولوية أول)، 5 طلبات بالرسالة
 * كحد أقصى عشان تظل مقروءة.
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
}

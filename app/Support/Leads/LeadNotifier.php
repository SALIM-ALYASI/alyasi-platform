<?php

namespace App\Support\Leads;

use App\Models\ServiceLead;
use App\Support\WhatsAppAlerts;
use Illuminate\Support\Collection;
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
                $lines[] = ($chunkIndex * 5 + $i + 1).') '.$lead->countryLabel().($lead->city ? ' – '.$lead->city : '').' · '.$lead->categoryLabel();
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
}

<?php

namespace App\Support\Events;

use App\Models\Event;
use Illuminate\Support\Collection;

/**
 * بيانات مركز المؤتمرات المشتركة بين صفحة /events والرئيسية: المؤتمرات المنشورة
 * بنسخها، و«التغطية المميزة»، وصفوف العرض مع شاراتها.
 */
final class EventsHub
{
    /**
     * المؤتمرات اللي لها نسخة منشورة، ونسخها من الأحدث.
     *
     * @return Collection<int, Event>
     */
    public static function events(): Collection
    {
        InterviewIndex::syncIfStale();

        return Event::query()
            ->whereHas('editions', fn ($query) => $query->published())
            ->with([
                'editions' => fn ($query) => $query
                    ->published()
                    ->with(['permalinks', 'interviews'])
                    ->orderByDesc('year')
                    ->orderByDesc('event_start_at'),
            ])
            ->get();
    }

    /**
     * صف لكل مؤتمر: نسخة السنة المطلوبة، أو آخر نسخة.
     *
     * @return Collection<int, object{event: Event, edition: \App\Models\EventEdition, start: ?\Illuminate\Support\Carbon, highlights: array}>
     */
    public static function rows(Collection $events, ?int $year = null): Collection
    {
        return $events->map(function (Event $event) use ($year) {
            $edition = $year
                ? $event->editions->firstWhere('year', $year)
                : $event->editions->first();

            return $edition ? (object) [
                'event' => $event,
                'edition' => $edition,
                'start' => $edition->event_start_at,
                'highlights' => EventHighlights::for($edition),
            ] : null;
        })->filter()->values();
    }

    /**
     * التغطية المميزة: النسخة المثبّتة يدويًا من لوحة التحكم لو فيه، وإلا تلقائيًا
     * أحدث نسخة نُشر لها محتوى حصري فعلي (تغطية ميدانية أو مقابلات) -- مب أحدث
     * مؤتمر بالتاريخ. أي نسخة من أي مؤتمر (مب آخر نسخة بس).
     */
    public static function featured(Collection $events): ?object
    {
        $candidates = $events->flatMap(fn (Event $event) => $event->editions->map(fn ($edition) => (object) [
            'event' => $event,
            'edition' => $edition,
            'start' => $edition->event_start_at,
            'highlights' => EventHighlights::for($edition),
        ]));

        $pinned = $candidates->filter(fn ($row) => $row->edition->is_featured)
            ->sortByDesc(fn ($row) => $row->edition->updated_at?->getTimestamp() ?? 0)
            ->first();

        if ($pinned) {
            return $pinned;
        }

        return $candidates->filter(fn ($row) => $row->highlights['exclusive_at'] !== null)
            ->sortByDesc(fn ($row) => $row->highlights['exclusive_at']->getTimestamp())
            ->first();
    }

    /**
     * مؤتمرات للرئيسية (غير التغطية المميزة): الشراكات الإعلامية أول، بعدها اللي
     * عليها تغطية أو مقابلات، بعدها القادمة الأقرب، بعدها الأحدث.
     *
     * @return Collection<int, object>
     */
    public static function forHome(Collection $events, ?object $featured, int $limit = 3): Collection
    {
        return self::rows($events)
            ->reject(fn ($row) => $featured && $row->event->is($featured->event))
            ->sortBy(fn ($row) => [
                $row->edition->is_media_partner ? 0 : 1,
                $row->highlights['tags'] ? 0 : 1,
                $row->edition->phase === 'concluded' ? 1 : 0,
                $row->edition->phase === 'concluded'
                    ? -($row->start?->getTimestamp() ?? 0)
                    : ($row->start?->getTimestamp() ?? PHP_INT_MAX),
            ])
            ->take($limit)
            ->values();
    }
}

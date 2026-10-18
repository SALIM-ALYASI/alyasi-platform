<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\Events\EventHighlights;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * مركز المؤتمرات: تغطيات ALYASI الحصرية أولًا، بعدها الفعاليات القادمة
     * (الأقرب موعدًا أول)، بعدها المنتهية (الأحدث أول). موعد كل مؤتمر = آخر
     * نسخة منشورة منه.
     */
    public function index(): View
    {
        $events = Event::query()
            ->whereHas('editions', fn ($query) => $query->published())
            ->with([
                'editions' => fn ($query) => $query
                    ->published()
                    ->with('permalinks')
                    ->orderByDesc('year')
                    ->orderByDesc('event_start_at'),
            ])
            ->get()
            ->map(function (Event $event) {
                $edition = $event->editions->first();

                return (object) [
                    'event' => $event,
                    'edition' => $edition,
                    'start' => $edition?->event_start_at,
                    'highlights' => EventHighlights::for($event, $edition),
                ];
            });

        [$upcoming, $past] = $events->partition(fn ($row) => $row->edition && $row->edition->phase !== 'concluded');

        $upcoming = $upcoming->sortBy(fn ($row) => $row->start?->getTimestamp() ?? PHP_INT_MAX)->values();
        $past = $past->sortByDesc(fn ($row) => $row->start?->getTimestamp() ?? 0)->values();

        $partner = $events->first(fn ($row) => $row->event->slug === 'cysec-oman' && $row->edition?->phase !== 'concluded');

        return view('events.index', [
            'upcoming' => $upcoming,
            'past' => $past,
            'interviews' => EventHighlights::publishedInterviews(),
            'partner' => $partner,
        ]);
    }
}

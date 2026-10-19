<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\Events\EventsHub;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * مركز المؤتمرات: «تغطية مميزة» أولًا، بعدها الفعاليات القادمة (الأقرب
     * موعدًا أول)، بعدها المنتهية (الأحدث أول). موعد كل مؤتمر = آخر نسخة منشورة.
     */
    public function index(\Illuminate\Http\Request $request): View
    {
        $events = EventsHub::events();

        // شريط السنوات: كل سنة فيها نسخة منشورة، والأحدث أول. ?year= يعرض
        // مؤتمرات تلك السنة بنسختها، وبدونه «كل المؤتمرات» بآخر نسخة لكل مؤتمر.
        $years = $events->flatMap(fn (Event $event) => $event->editions->pluck('year'))->unique()->sortDesc()->values();
        $year = $years->contains((int) $request->query('year')) ? (int) $request->query('year') : null;

        [$upcoming, $past] = EventsHub::rows($events, $year)
            ->partition(fn ($row) => $row->edition && $row->edition->phase !== 'concluded');

        return view('events.index', [
            'years' => $years,
            'year' => $year,
            'featured' => $year ? null : EventsHub::featured($events),
            'upcoming' => $upcoming->sortBy(fn ($row) => $row->start?->getTimestamp() ?? PHP_INT_MAX)->values(),
            'past' => $past->sortByDesc(fn ($row) => $row->start?->getTimestamp() ?? 0)->values(),
        ]);
    }
}

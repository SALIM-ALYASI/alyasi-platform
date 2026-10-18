<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\Events\EventHighlights;
use App\Support\Events\InterviewIndex;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * مركز المؤتمرات: «تغطية مميزة» أولًا، بعدها الفعاليات القادمة (الأقرب
     * موعدًا أول)، بعدها المنتهية (الأحدث أول). موعد كل مؤتمر = آخر نسخة منشورة.
     */
    public function index(\Illuminate\Http\Request $request): View
    {
        InterviewIndex::syncIfStale();

        $events = Event::query()
            ->whereHas('editions', fn ($query) => $query->published())
            ->with([
                'editions' => fn ($query) => $query
                    ->published()
                    ->with(['permalinks', 'interviews'])
                    ->orderByDesc('year')
                    ->orderByDesc('event_start_at'),
            ])
            ->get();

        // شريط السنوات: كل سنة فيها نسخة منشورة، والأحدث أول. ?year= يعرض
        // مؤتمرات تلك السنة بنسختها، وبدونه «كل المؤتمرات» بآخر نسخة لكل مؤتمر.
        $years = $events->flatMap(fn (Event $event) => $event->editions->pluck('year'))->unique()->sortDesc()->values();
        $year = $years->contains((int) $request->query('year')) ? (int) $request->query('year') : null;

        $rows = $events->map(function (Event $event) use ($year) {
            $edition = $year
                ? $event->editions->firstWhere('year', $year)
                : $event->editions->first();

            return $edition ? (object) [
                'event' => $event,
                'edition' => $edition,
                'start' => $edition->event_start_at,
                'highlights' => EventHighlights::for($edition),
            ] : null;
        })->filter();

        [$upcoming, $past] = $rows->partition(fn ($row) => $row->edition && $row->edition->phase !== 'concluded');

        return view('events.index', [
            'years' => $years,
            'year' => $year,
            'featured' => $year ? null : $this->featured($events),
            'upcoming' => $upcoming->sortBy(fn ($row) => $row->start?->getTimestamp() ?? PHP_INT_MAX)->values(),
            'past' => $past->sortByDesc(fn ($row) => $row->start?->getTimestamp() ?? 0)->values(),
        ]);
    }

    /**
     * التغطية المميزة: النسخة المثبّتة يدويًا من لوحة التحكم لو فيه، وإلا تلقائيًا
     * أحدث نسخة نُشر لها محتوى حصري فعلي (تغطية ميدانية أو مقابلات) -- مب أحدث
     * مؤتمر بالتاريخ. أي نسخة من أي مؤتمر (مب آخر نسخة بس).
     */
    private function featured($events): ?object
    {
        $candidates = $events->flatMap(fn (Event $event) => $event->editions->map(fn ($edition) => (object) [
            'event' => $event,
            'edition' => $edition,
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
}

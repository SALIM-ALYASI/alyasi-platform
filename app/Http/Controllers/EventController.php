<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventEdition;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * صفحة مركز المؤتمرات: تعرض السلاسل الدائمة (Apple / Samsung / Huawei / COMEX...)
     * بدل خلطها مع منشورات المجتمع.
     */
    public function index(): View
    {
        // الترتيب بموعد المؤتمر (مو بالاسم أو تاريخ الإضافة): القادم أولًا
        // والأقرب موعدًا قبل، بعده المنتهي والأحدث قبل. الموعد = آخر نسخة منشورة.
        $latestStart = EventEdition::query()
            ->selectRaw('MAX(event_start_at)')
            ->whereColumn('event_editions.event_id', 'events.id')
            ->published();

        $events = Event::query()
            ->select('events.*')
            ->selectSub($latestStart, 'latest_start_at')
            ->whereHas('editions', fn ($query) => $query->published())
            ->with([
                'editions' => fn ($query) => $query
                    ->published()
                    ->with('permalinks')
                    ->orderByDesc('year')
                    ->orderByDesc('event_start_at'),
            ])
            ->orderByRaw('CASE WHEN (latest_start_at) >= ? THEN 0 ELSE 1 END', [now()])
            ->orderByRaw('CASE WHEN (latest_start_at) >= ? THEN latest_start_at END ASC', [now()])
            ->orderByDesc('latest_start_at')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', compact('events'));
    }
}

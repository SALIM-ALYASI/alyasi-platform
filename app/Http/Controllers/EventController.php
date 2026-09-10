<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * صفحة مركز المؤتمرات: تعرض السلاسل الدائمة (Apple / Samsung / Huawei / COMEX...)
     * بدل خلطها مع منشورات المجتمع.
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
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', compact('events'));
    }
}

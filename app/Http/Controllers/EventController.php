<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * صفحة الفعاليات (فعاليات المجتمع من نوع "event").
     */
    public function index(): View
    {
        $events = CommunityPost::query()
            ->with('category')
            ->active()
            ->published()
            ->where('type', 'event')
            ->orderByDesc('event_start_at')
            ->paginate(9)
            ->withQueryString();

        return view('events.index', compact('events'));
    }
}

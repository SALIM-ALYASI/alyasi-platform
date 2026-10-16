<?php

namespace App\Http\Controllers;

use App\Models\OmanPulseItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * «نبض عُمان»: أخبار رسمية عُمانية بنصّها الأصلي ومنسوبة لمصدرها.
 */
class OmanPulseController extends Controller
{
    public function index(Request $request): View
    {
        $tag = $request->string('tag')->toString();
        $tag = array_key_exists($tag, OmanPulseItem::TAGS) ? $tag : null;

        $items = OmanPulseItem::query()
            ->published()
            ->when($tag, fn ($query) => $query->where('tag', $tag))
            ->orderByRaw('COALESCE(source_published_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('news.oman-pulse', [
            'items' => $items,
            'tags' => OmanPulseItem::TAGS,
            'activeTag' => $tag,
        ]);
    }

    /**
     * صفحة قراءة الخبر: النص الرسمي كامل كما هو، مع رابط البيان الأصلي.
     */
    public function show(int $item): View
    {
        $item = OmanPulseItem::query()->published()->findOrFail($item);

        $related = OmanPulseItem::query()
            ->published()
            ->whereKeyNot($item->id)
            ->orderByRaw('COALESCE(source_published_at, created_at) DESC')
            ->take(3)
            ->get();

        return view('news.oman-pulse-show', compact('item', 'related'));
    }
}

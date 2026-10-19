<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\OmanPulseItem;
use Illuminate\Pagination\LengthAwarePaginator;
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

        // بيانات رسمية (بنصها) + أخبار ALYASI اللي تخص عُمان (تبقى بصفحة الأخبار بعد).
        $official = OmanPulseItem::query()
            ->published()
            ->when($tag, fn ($query) => $query->where('tag', $tag))
            ->get()
            ->map(fn (OmanPulseItem $item) => [
                'kind' => 'official',
                'date' => $item->source_published_at ?? $item->created_at,
                'item' => $item,
            ]);

        // أخبار المنصة ما لها وسوم نبض عُمان؛ تظهر بـ«الكل» و«تقنية».
        $news = in_array($tag, [null, 'tech'], true)
            ? NewsArticle::query()
                ->with('permalinks')
                ->published()
                ->where('is_oman', true)
                ->whereNull('merged_into_id')
                ->when(app()->getLocale() === 'en', fn ($query) => $query->whereNotNull('title_en')->where('title_en', '!=', ''))
                ->latest('published_at')
                ->take(200)
                ->get()
                ->map(fn (NewsArticle $article) => [
                    'kind' => 'news',
                    'date' => $article->published_at,
                    'item' => $article,
                ])
            : collect();

        $entries = $official->concat($news)->sortByDesc(fn ($entry) => $entry['date']?->getTimestamp() ?? 0)->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = (new LengthAwarePaginator(
            $entries->forPage($page, 20)->values(),
            $entries->count(),
            20,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        ));

        return view('news.oman-pulse', [
            'items' => $items,
            'tags' => app()->getLocale() === 'en' ? OmanPulseItem::TAGS_EN : OmanPulseItem::TAGS,
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

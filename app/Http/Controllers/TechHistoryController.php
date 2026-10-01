<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * "تاريخ التقنية" -- حلقة شهرية تتبع تطور جهاز واحد، بصوت سالم الشخصي. مبني
 * فوق نفس نظام Article/ArticleCategory (نفس لوحة كتابة "مقالاتي" اللي
 * يعرفها الكاتب أصلاً، يكفي يختار تصنيف "تاريخ التقنية")، بمسار/واجهة عامة
 * مستقلة عن articles.* (ما يظهر مختلطًا بمقالاته الشخصية) لكن بنفس تصميم
 * وألوان باقي الموقع -- لا هوية بصرية منفصلة.
 */
class TechHistoryController extends Controller
{
    private const CATEGORY_SLUG = 'tech-history';

    public function index(): View
    {
        $stories = Article::query()
            ->with(['permalinks'])
            ->published()
            ->availableIn('ar')
            ->whereHas('category', fn ($q) => $q->where('slug', self::CATEGORY_SLUG))
            ->ordered()
            ->paginate(12);

        abort_if_page_out_of_range($stories);

        return view('tech-history.index', compact('stories'));
    }

    public function show(string $slug): View|RedirectResponse
    {
        $permalink = Permalink::query()
            ->with('linkable')
            ->where('linkable_type', 'article')
            ->where('locale', 'ar')
            ->where('slug', $slug)
            ->first();

        if (! $permalink) {
            return $this->redirectFromOldSlug($slug);
        }

        $article = $permalink->linkable;

        abort_unless($article instanceof Article, 404);
        abort_unless($article->category?->slug === self::CATEGORY_SLUG, 404);

        $isPublished = Article::query()->published()->whereKey($article->getKey())->exists();
        abort_unless($isPublished, 404);

        $article->registerView();
        $article->refresh();

        $otherStories = Article::query()
            ->with('permalinks')
            ->published()
            ->availableIn('ar')
            ->whereHas('category', fn ($q) => $q->where('slug', self::CATEGORY_SLUG))
            ->whereKeyNot($article->getKey())
            ->ordered()
            ->limit(3)
            ->get();

        return view('tech-history.show', compact('article', 'otherStories'));
    }

    private function redirectFromOldSlug(string $slug): RedirectResponse
    {
        $redirect = PermalinkRedirect::query()
            ->with('permalink.linkable')
            ->where('locale', 'ar')
            ->where('old_slug', $slug)
            ->first();

        abort_unless($redirect?->permalink, 404);

        $article = $redirect->permalink->linkable;

        abort_unless(
            $article instanceof Article && $article->category?->slug === self::CATEGORY_SLUG,
            404
        );

        return redirect()->to(
            route('tech-history.show', ['slug' => $redirect->permalink->slug]),
            301
        );
    }
}

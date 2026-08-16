<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * عرض الأخبار المنشورة.
     */
    public function index(Request $request): View
    {
        $categories = NewsCategory::query()
            ->active()
            ->ordered()
            ->get();

        $articlesQuery = NewsArticle::query()
            ->with([
                'category',
                'permalinks',
            ])
            ->published();

        if ($request->filled('category')) {
            $articlesQuery->whereHas(
                'category',
                fn ($query) => $query->where(
                    'slug',
                    $request->string('category')->toString()
                )
            );
        }

        $articles = $articlesQuery
            ->latestPublished()
            ->paginate(12)
            ->withQueryString();

        return view('news.index', compact('articles', 'categories'));
    }

    /**
     * عرض تفاصيل خبر منشور.
     */
    public function show(string $slug): View|RedirectResponse
    {
        /*
         * البحث عن الرابط المطابق للغة الحالية أولًا.
         */
        $permalink = Permalink::query()
            ->with('linkable')
            ->where('locale', app()->getLocale())
            ->where('slug', $slug)
            ->first();

        /*
         * عند عدم وجود رابط باللغة الحالية،
         * نبحث عن الرابط بأي لغة.
         */
        $permalink ??= Permalink::query()
            ->with('linkable')
            ->where('slug', $slug)
            ->first();

        /*
         * لا رابط حالي مطابق إطلاقًا — نجرّب رابطًا قديمًا محوّلاً
         * قبل الاستسلام بـ 404.
         */
        if (! $permalink) {
            return $this->redirectFromOldSlug($slug, app()->getLocale());
        }

        $article = $permalink->linkable;

        /*
         * التأكد من أن الرابط يعود إلى خبر.
         */
        abort_unless($article instanceof NewsArticle, 404);

        /*
         * التأكد من أن الخبر منشور ومتاح للعامة.
         */
        $isPublished = NewsArticle::query()
            ->published()
            ->whereKey($article->getKey())
            ->exists();

        abort_unless($isPublished, 404);

        /*
         * تحميل بيانات التصنيف والروابط.
         */
        $article->load([
            'category',
            'permalinks',
        ]);

        /*
         * تسجيل مشاهدة الخبر.
         */
        $article->registerView();

        /*
         * تحديث بيانات الخبر بعد تسجيل المشاهدة
         * حتى يظهر العدد الجديد داخل الصفحة.
         */
        $article->refresh();

        $article->load([
            'category',
            'permalinks',
        ]);

        /*
         * جلب الأخبار ذات الصلة.
         */
        $relatedArticles = $this->getRelatedArticles($article);

        return view('news.show', compact(
            'article',
            'relatedArticles'
        ));
    }

    /**
     * جلب ثلاثة أخبار ذات صلة بالخبر الحالي.
     */
    private function getRelatedArticles(
        NewsArticle $article
    ): Collection {
        /*
         * نبدأ بالأخبار الموجودة في نفس التصنيف.
         */
        $relatedArticles = NewsArticle::query()
            ->with([
                'category',
                'permalinks',
            ])
            ->published()
            ->where('id', '!=', $article->getKey())
            ->when(
                $article->category_id,
                fn ($query) => $query->where(
                    'category_id',
                    $article->category_id
                )
            )
            ->latestPublished()
            ->limit(3)
            ->get();

        /*
         * إذا كان عدد أخبار التصنيف أقل من ثلاثة،
         * نكمل العدد من أحدث الأخبار المنشورة.
         */
        if ($relatedArticles->count() < 3) {
            $additionalArticles = NewsArticle::query()
                ->with([
                    'category',
                    'permalinks',
                ])
                ->published()
                ->where('id', '!=', $article->getKey())
                ->whereNotIn(
                    'id',
                    $relatedArticles->pluck('id')
                )
                ->latestPublished()
                ->limit(3 - $relatedArticles->count())
                ->get();

            $relatedArticles = $relatedArticles
                ->concat($additionalArticles);
        }

        return new Collection(
            $relatedArticles->take(3)->values()->all()
        );
    }

    /**
     * تحويل الروابط القديمة إلى الرابط الحالي للخبر.
     */
    private function redirectFromOldSlug(string $slug, string $locale): RedirectResponse
    {
        $redirect = PermalinkRedirect::query()
            ->with('permalink.linkable')
            ->where('old_slug', $slug)
            ->orderByRaw(
                'CASE WHEN locale = ? THEN 0 ELSE 1 END',
                [$locale]
            )
            ->first();

        abort_unless($redirect?->permalink, 404);

        $article = $redirect->permalink->linkable;

        abort_unless($article instanceof NewsArticle, 404);

        $isPublished = NewsArticle::query()
            ->published()
            ->whereKey($article->getKey())
            ->exists();

        abort_unless($isPublished, 404);

        return redirect()->route(
            'news.show',
            ['slug' => $redirect->permalink->slug],
            301
        );
    }
}

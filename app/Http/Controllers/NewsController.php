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

        abort_if_page_out_of_range($articles);

        return view('news.index', compact('articles', 'categories'));
    }

    /**
     * عرض تفاصيل خبر منشور.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $locale = app()->getLocale();

        /*
         * البحث عن الرابط المطابق للغة الحالية فقط — تقييد صارم حتى لا
         * يظهر slug اللغة الأخرى بنفس المسار بردّ 200 مكرر.
         */
        $permalink = Permalink::query()
            ->with('linkable')
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();

        if (! $permalink) {
            /*
             * الرابط موجود لكن بلغة أخرى — نحوّل 301 إلى مساره الصحيح
             * بدل عرضه على مسار اللغة الخاطئة.
             */
            $otherLocale = $locale === 'ar' ? 'en' : 'ar';

            $otherLocalePermalink = Permalink::query()
                ->where('locale', $otherLocale)
                ->where('slug', $slug)
                ->first();

            if ($otherLocalePermalink) {
                return redirect()->route(
                    $otherLocale === 'en' ? 'news.show.en' : 'news.show',
                    ['slug' => $otherLocalePermalink->slug],
                    301
                );
            }

            /*
             * لا رابط حالي مطابق إطلاقًا — نجرّب رابطًا قديمًا محوّلاً
             * قبل الاستسلام بـ 404.
             */
            return $this->redirectFromOldSlug($slug, $locale);
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
         * الرابط القديم /news/{slug}
         * يتحول دائماً 301 إلى الرابط الذكي عندما تكون الهوية متوفرة.
         */
        if (
            $article->publication_date
            && $article->daily_sequence
        ) {
            return redirect()->to(
                $permalink->url(),
                301
            );
        }

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
     * عرض الخبر عبر الرابط الذكي:
     * /news/YYYY/MM/DD/007/slug
     *
     * هوية الخبر الحقيقية هنا هي:
     * publication_date + daily_sequence
     *
     * الـ slug وصيغة التاريخ/الرقم يتم تصحيحهما بـ 301.
     */
    public function showSmart(
        string $year,
        string $month,
        string $day,
        string $sequence,
        string $slug
    ): View|RedirectResponse {
        $locale = app()->getLocale();

        $date = "{$year}-{$month}-{$day}";
        $sequenceNumber = (int) $sequence;

        // رفض تاريخ غير صالح مثل 2026/13/40.
        try {
            $parsedDate = \Carbon\Carbon::createFromFormat(
                '!Y-m-d',
                $date,
                'Asia/Muscat'
            );
        } catch (\Throwable) {
            abort(404);
        }

        abort_unless(
            $parsedDate->format('Y-m-d') === $date,
            404
        );

        // البحث لا يعتمد على slug إطلاقاً.
        $article = NewsArticle::query()
            ->published()
            ->whereDate('publication_date', $date)
            ->where('daily_sequence', $sequenceNumber)
            ->first();

        abort_unless($article, 404);

        $article->load([
            'category',
            'permalinks',
        ]);

        $permalink = $article->permalinks
            ->firstWhere('locale', $locale);

        abort_unless($permalink, 404);

        $canonicalSequence = str_pad(
            (string) $article->daily_sequence,
            3,
            '0',
            STR_PAD_LEFT
        );

        $canonicalDate = $article->publication_date
            ->format('Y/m/d');

        [$canonicalYear, $canonicalMonth, $canonicalDay] =
            explode('/', $canonicalDate);

        /*
         * أي اختلاف في:
         * - slug
         * - 7 بدل 007
         * - التاريخ
         *
         * يتحول 301 للرابط الرسمي.
         */
        if (
            $slug !== $permalink->slug
            || $sequence !== $canonicalSequence
            || $year !== $canonicalYear
            || $month !== $canonicalMonth
            || $day !== $canonicalDay
        ) {
            return redirect()->route(
                $locale === 'en'
                    ? 'news.show.smart.en'
                    : 'news.show.smart',
                [
                    'year' => $canonicalYear,
                    'month' => $canonicalMonth,
                    'day' => $canonicalDay,
                    'sequence' => $canonicalSequence,
                    'slug' => $permalink->slug,
                ],
                301
            );
        }

        $article->registerView();

        $article->refresh();

        $article->load([
            'category',
            'permalinks',
        ]);

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

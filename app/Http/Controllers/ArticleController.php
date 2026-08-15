<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Permalink;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * صفحة مقالاتي الرئيسية.
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        $categories = ArticleCategory::query()
            ->active()
            ->ordered()
            ->get();

        $articlesQuery = Article::query()
            ->with(['category', 'permalinks'])
            ->published()
            ->availableIn($locale);

        if ($request->filled('category')) {
            $articlesQuery->whereHas(
                'category',
                fn ($query) => $query->where(
                    'slug',
                    $request->string('category')->toString()
                )
            );
        }

        $featuredArticles = Article::query()
            ->with(['category', 'permalinks'])
            ->published()
            ->availableIn($locale)
            ->featured()
            ->ordered()
            ->limit(3)
            ->get();

        $articles = $articlesQuery
            ->ordered()
            ->paginate(9)
            ->withQueryString();

        return view('articles.index', compact(
            'categories',
            'featuredArticles',
            'articles'
        ));
    }

    /**
     * صفحة تفاصيل المقال.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $permalink = Permalink::query()
            ->with('linkable')
            ->where('linkable_type', 'article')
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();

        abort_if($permalink === null, 404);

        $article = $permalink->linkable;

        abort_unless($article instanceof Article, 404);

        $isPublished = Article::query()
            ->published()
            ->whereKey($article->getKey())
            ->exists();

        abort_unless($isPublished, 404);

        $article->load(['category', 'permalinks']);
        $article->registerView();
        $article->refresh();
        $article->load(['category', 'permalinks']);

        $relatedArticles = Article::query()
            ->with(['category', 'permalinks'])
            ->published()
            ->availableIn($locale)
            ->whereKeyNot($article->getKey())
            ->when(
                $article->article_category_id,
                fn ($query) => $query->where(
                    'article_category_id',
                    $article->article_category_id
                )
            )
            ->ordered()
            ->limit(3)
            ->get();

        return view('articles.show', compact(
            'article',
            'relatedArticles'
        ));
    }
}

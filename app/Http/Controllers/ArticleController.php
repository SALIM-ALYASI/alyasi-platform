<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * صفحة مقالاتي الرئيسية.
     */
    public function index(Request $request): View
    {
        $categories = ArticleCategory::query()
            ->active()
            ->ordered()
            ->get();

        $articlesQuery = Article::query()
            ->with('category')
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

        $featuredArticles = Article::query()
            ->with('category')
            ->published()
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
    public function show(Article $article): View
    {
        if ($article->status !== Article::STATUS_PUBLISHED) {
            abort(404);
        }

        if (! $article->published_at || $article->published_at->greaterThan(now())) {
            abort(404);
        }

        $article->load('category');
        $article->registerView();
        $article->refresh();

        $relatedArticles = Article::query()
            ->with('category')
            ->published()
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

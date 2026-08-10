<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsArticleResource;
use App\Models\NewsArticle;
use App\Models\Permalink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * قائمة الأخبار المنشورة (للقراءة فقط)، مع دعم فلترة بالتصنيف والبحث.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = NewsArticle::query()
            ->with(['category', 'permalinks'])
            ->published()
            ->when(
                $request->filled('category'),
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($query) => $query->where('slug', $request->string('category')->toString())
                )
            )
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->string('search')->toString());

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('title_ar', 'like', "%{$search}%")
                            ->orWhere('title_en', 'like', "%{$search}%");
                    });
                }
            )
            ->latestPublished()
            ->paginate(min($request->integer('per_page', 12), 50))
            ->withQueryString();

        return NewsArticleResource::collection($articles)->response();
    }

    /**
     * تفاصيل خبر واحد منشور بواسطة رابطه الدائم (عربي أو إنجليزي).
     */
    public function show(string $slug): JsonResponse
    {
        $permalink = Permalink::query()
            ->with('linkable')
            ->where('slug', $slug)
            ->whereHasMorph(
                'linkable',
                [NewsArticle::class],
                fn ($query) => $query->published()
            )
            ->first();

        abort_unless($permalink, 404);

        $article = $permalink->linkable;

        $article->loadMissing(['category', 'permalinks']);

        return (new NewsArticleResource($article))->response();
    }
}

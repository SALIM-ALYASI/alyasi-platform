<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ArticleIngestController extends Controller
{
    /**
     * قائمة مختصرة بالمقالات الموجودة - تفيد أتمتة n8n قبل الإرسال
     * عشان تتفادى اقتراح مقالات مكرّرة أصلاً منشورة.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = Article::query()
            ->orderByDesc('id')
            ->limit(min($request->integer('limit', 50), 200))
            ->get(['id', 'title', 'slug', 'status']);

        return response()->json(['data' => $articles]);
    }

    /**
     * استقبال مقال من أتمتة خارجية (n8n) وحفظه بقسم "مقالاتي". المقال
     * يُطابَق أولاً بالرابط المختصر (slug) لمنع التكرار - لو موجود يُحدَّث
     * بدل ما يُنشأ من جديد.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:280'],
            'category' => ['nullable', 'string', 'max:150'],
            'featured_image_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        $title = trim($validated['title']);
        $slug = $this->resolveSlug($validated['slug'] ?? null, $title);

        $article = Article::query()->firstOrNew(['slug' => $slug]);

        $status = $validated['status'] ?? 'published';

        $article->fill([
            'article_category_id' => $this->resolveCategoryId($validated['category'] ?? null),
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'status' => $status,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'published_at' => $status === 'published'
                ? ($validated['published_at'] ?? $article->published_at ?? now())
                : ($validated['published_at'] ?? null),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ]);

        if (! $article->exists && filled($validated['featured_image_url'] ?? null)) {
            $article->featured_image = $this->downloadImage($validated['featured_image_url']);
        }

        $article->save();

        return response()->json([
            'success' => true,
            'id' => $article->id,
            'slug' => $article->slug,
            'created' => $article->wasRecentlyCreated,
        ]);
    }

    /**
     * تحديد الرابط المختصر النهائي، مع ضمان فرادته.
     */
    private function resolveSlug(?string $requestedSlug, string $title): string
    {
        $baseSlug = Str::slug($requestedSlug ?: $title);

        if ($baseSlug === '') {
            $baseSlug = 'article-'.Str::lower(Str::random(8));
        }

        // لو الـslug يطابق مقالًا موجودًا فعلاً، نعتبره تحديثًا لنفس المقال
        // (مو تكرارًا)، فنرجّعه كما هو بدل توليد نسخة جديدة.
        if (Article::query()->where('slug', $baseSlug)->exists()) {
            return $baseSlug;
        }

        $slug = $baseSlug;
        $counter = 2;

        while (Article::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * جلب معرّف التصنيف من اسمه، وإنشاؤه لو ما كان موجودًا.
     */
    private function resolveCategoryId(?string $categoryName): ?int
    {
        if (blank($categoryName)) {
            return null;
        }

        return ArticleCategory::query()
            ->firstOrCreate(
                ['name' => trim($categoryName)],
                [
                    'slug' => Str::slug($categoryName),
                    'is_active' => true,
                ]
            )
            ->id;
    }

    /**
     * تنزيل صورة المقال من رابط خارجي وحفظها محليًا داخل public/uploads/articles.
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $extension = match ($response->header('Content-Type')) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $directory = public_path('uploads/articles');
            @mkdir($directory, 0755, true);

            $fileName = now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.'.$extension;

            file_put_contents($directory.'/'.$fileName, $response->body());

            return 'uploads/articles/'.$fileName;
        } catch (\Throwable) {
            return null;
        }
    }
}

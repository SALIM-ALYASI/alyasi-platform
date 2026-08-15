<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Permalink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class ArticleIngestController extends Controller
{
    /**
     * الوسوم المسموح بها داخل محتوى المقال — مطابقة لِـ Admin\ArticleController
     * وللقائمة المعروضة فعليًا في articles/show.blade.php.
     */
    private const ALLOWED_CONTENT_TAGS = 'p,br,strong,em,b,i,a[href|title|target|rel],ul,ol,li,h2,h3,h4,blockquote,hr';

    /**
     * قائمة مختصرة بالمقالات الموجودة - تفيد أتمتة n8n قبل الإرسال
     * عشان تتفادى اقتراح مقالات مكرّرة أصلاً منشورة. المقالات هنا كلها
     * عربية (اللغة الوحيدة التي يرسلها البوت حاليًا)، لذا الرابط المُعاد
     * هو الرابط الدائم العربي.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = Article::query()
            ->with('permalinks')
            ->orderByDesc('id')
            ->limit(min($request->integer('limit', 50), 200))
            ->get(['id', 'title_ar', 'status'])
            ->map(fn (Article $article) => [
                'id' => $article->id,
                'title' => $article->title_ar,
                'slug' => $article->translatedSlug('ar'),
                'status' => $article->status,
            ]);

        return response()->json(['data' => $articles]);
    }

    /**
     * استقبال مقال من أتمتة خارجية (n8n) وحفظه بقسم "مقالاتي" باللغة
     * العربية (اللغة الوحيدة التي يرسلها البوت حاليًا). المقال يُطابَق
     * أولاً بالرابط المختصر (عبر جدول permalinks) لمنع التكرار - لو
     * موجود يُحدَّث بدل ما يُنشأ من جديد.
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

        $article = DB::transaction(function () use ($validated, $title, $slug) {
            $existingPermalink = Permalink::query()
                ->where('linkable_type', 'article')
                ->where('locale', 'ar')
                ->where('slug', $slug)
                ->first();

            $article = $existingPermalink?->linkable instanceof Article
                ? $existingPermalink->linkable
                : new Article;

            $status = $validated['status'] ?? 'published';

            $article->fill([
                'article_category_id' => $this->resolveCategoryId($validated['category'] ?? null),
                'title_ar' => $title,
                'excerpt_ar' => $validated['excerpt'] ?? null,
                'content_ar' => Purifier::clean($validated['content'], ['HTML.Allowed' => self::ALLOWED_CONTENT_TAGS]),
                'status' => $status,
                'is_featured' => (bool) ($validated['is_featured'] ?? false),
                'published_at' => $status === 'published'
                    ? ($validated['published_at'] ?? $article->published_at ?? now())
                    : ($validated['published_at'] ?? null),
                'meta_title_ar' => $validated['meta_title'] ?? null,
                'meta_description_ar' => $validated['meta_description'] ?? null,
                'meta_keywords_ar' => $validated['meta_keywords'] ?? null,
            ]);

            if (! $article->exists && filled($validated['featured_image_url'] ?? null)) {
                $article->featured_image = $this->downloadImage($validated['featured_image_url']);
            }

            $article->save();

            if (! $existingPermalink) {
                Permalink::query()->create([
                    'linkable_type' => 'article',
                    'linkable_id' => $article->id,
                    'locale' => 'ar',
                    'slug' => $slug,
                ]);
            }

            return $article;
        });

        return response()->json([
            'success' => true,
            'id' => $article->id,
            'slug' => $article->translatedSlug('ar'),
            'created' => $article->wasRecentlyCreated,
        ]);
    }

    /**
     * تحديد الرابط المختصر النهائي، مع ضمان فرادته عالميًا عبر جدول permalinks.
     */
    private function resolveSlug(?string $requestedSlug, string $title): string
    {
        $baseSlug = Str::slug($requestedSlug ?: $title);

        if ($baseSlug === '') {
            $baseSlug = 'article-'.Str::lower(Str::random(8));
        }

        // لو الـslug يطابق مقالًا موجودًا فعلاً، نعتبره تحديثًا لنفس المقال
        // (مو تكرارًا)، فنرجّعه كما هو بدل توليد نسخة جديدة.
        $matchesExistingArticle = Permalink::query()
            ->where('linkable_type', 'article')
            ->where('locale', 'ar')
            ->where('slug', $baseSlug)
            ->exists();

        if ($matchesExistingArticle) {
            return $baseSlug;
        }

        $slug = $baseSlug;
        $counter = 2;

        while (Permalink::query()->where('slug', $slug)->exists()) {
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
                ['name_ar' => trim($categoryName)],
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

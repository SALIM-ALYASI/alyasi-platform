<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use App\Rules\CleanSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * عرض الأخبار.
     */
    public function index(Request $request): View
    {
        $articles = NewsArticle::query()
            ->with('category:id,name_ar,name_en')
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
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('category'),
                fn ($query) => $query->where('news_category_id', $request->integer('category'))
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = NewsCategory::query()
            ->ordered()
            ->get(['id', 'name_ar', 'name_en']);

        return view(
            'admin.news.index',
            compact('articles', 'categories')
        );
    }

    /**
     * صفحة إضافة خبر جديد.
     */
    public function create(): View
    {
        $categories = NewsCategory::query()
            ->active()
            ->ordered()
            ->get(['id', 'name_ar', 'name_en']);

        return view('admin.news.create', compact('categories'));
    }

    /**
     * حفظ خبر جديد.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $manualSlugs = ['ar' => $validated['slug_ar'] ?? null, 'en' => $validated['slug_en'] ?? null];
        unset($validated['slug_ar'], $validated['slug_en']);

        $validated['status'] = $request->boolean('is_published')
            ? NewsArticle::STATUS_PUBLISHED
            : NewsArticle::STATUS_DRAFT;

        $validated['is_breaking'] = $request->boolean('is_breaking');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($validated['status'] === NewsArticle::STATUS_PUBLISHED && blank($validated['published_at'] ?? null)) {
            $validated['published_at'] = now();
        }

        unset($validated['is_published']);

        $article = NewsArticle::query()->create($validated);

        $this->syncPermalinks($article, $manualSlugs);

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'تمت إضافة الخبر بنجاح.');
    }

    /**
     * صفحة تعديل الخبر.
     */
    public function edit(NewsArticle $news): View
    {
        $categories = NewsCategory::query()
            ->ordered()
            ->get(['id', 'name_ar', 'name_en']);

        return view('admin.news.edit', [
            'article' => $news,
            'categories' => $categories,
        ]);
    }

    /**
     * تحديث الخبر.
     */
    public function update(Request $request, NewsArticle $news): RedirectResponse
    {
        $validated = $this->validateArticle($request, $news);
        $manualSlugs = ['ar' => $validated['slug_ar'] ?? null, 'en' => $validated['slug_en'] ?? null];
        unset($validated['slug_ar'], $validated['slug_en']);

        $validated['status'] = $request->boolean('is_published')
            ? NewsArticle::STATUS_PUBLISHED
            : NewsArticle::STATUS_DRAFT;

        $validated['is_breaking'] = $request->boolean('is_breaking');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($validated['status'] === NewsArticle::STATUS_PUBLISHED && blank($validated['published_at'] ?? null)) {
            $validated['published_at'] = now();
        }

        unset($validated['is_published']);

        $news->update($validated);

        $this->syncPermalinks($news, $manualSlugs);

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'تم تحديث الخبر بنجاح.');
    }

    /**
     * حذف الخبر.
     */
    public function destroy(NewsArticle $news): RedirectResponse
    {
        $news->delete();

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'تم حذف الخبر بنجاح.');
    }

    /**
     * تغيير حالة النشر.
     */
    public function toggleStatus(NewsArticle $news): RedirectResponse
    {
        $isPublished = $news->status === NewsArticle::STATUS_PUBLISHED;

        $news->update([
            'status' => $isPublished ? NewsArticle::STATUS_DRAFT : NewsArticle::STATUS_PUBLISHED,
            'published_at' => $isPublished ? $news->published_at : ($news->published_at ?? now()),
        ]);

        $this->regenerateSitemap();

        $message = $isPublished ? 'تم تحويل الخبر إلى مسودة.' : 'تم نشر الخبر.';

        return back()->with('success', $message);
    }

    /**
     * تغيير حالة التمييز.
     */
    public function toggleFeatured(NewsArticle $news): RedirectResponse
    {
        $news->update(['is_featured' => ! $news->is_featured]);

        $message = $news->is_featured ? 'تم تمييز الخبر.' : 'تم إلغاء تمييز الخبر.';

        return back()->with('success', $message);
    }

    /**
     * تغيير حالة الخبر العاجل.
     */
    public function toggleBreaking(NewsArticle $news): RedirectResponse
    {
        $news->update(['is_breaking' => ! $news->is_breaking]);

        $message = $news->is_breaking ? 'تم تعيين الخبر كعاجل.' : 'تم إلغاء تصنيف الخبر كعاجل.';

        return back()->with('success', $message);
    }

    /**
     * التحقق من بيانات الخبر.
     */
    private function validateArticle(Request $request, ?NewsArticle $news = null): array
    {
        return $request->validate([
            'news_category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
            'title_ar' => ['required', 'string', 'max:500'],
            'title_en' => ['required', 'string', 'max:500'],
            'excerpt_ar' => ['nullable', 'string', 'max:2000'],
            'excerpt_en' => ['nullable', 'string', 'max:2000'],
            'content_ar' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:2000'],
            'source_name' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:2000'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'is_breaking' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'slug_ar' => ['nullable', 'string', 'max:180', new CleanSlug],
            'slug_en' => ['nullable', 'string', 'max:180', new CleanSlug],
        ], [
            'title_ar.required' => 'العنوان بالعربية مطلوب.',
            'title_en.required' => 'العنوان بالإنجليزية مطلوب.',
            'source_url.url' => 'رابط المصدر غير صحيح.',
        ]);
    }

    /**
     * إنشاء الروابط الدائمة الناقصة للخبر باللغتين، أو تحديثها لو المدير
     * كتب رابطًا يدويًا مختلفًا عن الحالي — مع حفظ القديم كتحويل 301
     * تلقائي في permalink_redirects بدل ما ينكسر.
     */
    private function syncPermalinks(NewsArticle $article, array $manualSlugs = []): void
    {
        foreach (['ar' => $article->title_ar, 'en' => $article->title_en] as $locale => $title) {
            if (blank($title)) {
                continue;
            }

            $manualSlug = filled($manualSlugs[$locale] ?? null) ? trim($manualSlugs[$locale]) : null;

            $permalink = Permalink::query()
                ->where('linkable_type', 'news_article')
                ->where('linkable_id', $article->id)
                ->where('locale', $locale)
                ->first();

            if (! $permalink) {
                Permalink::query()->create([
                    'linkable_type' => 'news_article',
                    'linkable_id' => $article->id,
                    'locale' => $locale,
                    'slug' => $this->generateUniqueSlug($manualSlug ?: $title),
                ]);

                continue;
            }

            if ($manualSlug === null || $manualSlug === $permalink->slug) {
                continue;
            }

            $this->movePermalinkSlug($permalink, $manualSlug);
        }
    }

    /**
     * تحديث رابط دائم موجود إلى slug جديد، مع حفظ القديم كتحويل 301.
     */
    private function movePermalinkSlug(Permalink $permalink, string $newRequestedSlug): void
    {
        $newSlug = $this->generateUniqueSlug($newRequestedSlug);
        $oldSlug = $permalink->slug;

        DB::transaction(function () use ($permalink, $newSlug, $oldSlug): void {
            PermalinkRedirect::query()
                ->where('locale', $permalink->locale)
                ->where('old_slug', $oldSlug)
                ->delete();

            PermalinkRedirect::query()->create([
                'permalink_id' => $permalink->id,
                'locale' => $permalink->locale,
                'old_slug' => $oldSlug,
            ]);

            $permalink->update(['slug' => $newSlug]);
        });
    }

    /**
     * إنشاء Slug فريد.
     */
    private function generateUniqueSlug(string $title): string
    {
        $title = trim($title);

        /*
         * Slug عربي مقروء مع الاحتفاظ بأسماء الشركات
         * والمنتجات والمصطلحات الإنجليزية.
         */
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $title)) {
            $clean = preg_replace(
                '/[^\p{Arabic}\p{Latin}\p{N}\s-]+/u',
                ' ',
                $title
            );

            $clean = preg_replace('/\s+/u', ' ', trim($clean));

            $words = preg_split('/\s+/u', $clean);

            $words = array_slice(
                array_values(array_filter($words)),
                0,
                8
            );

            $baseSlug = implode('-', $words);
            $baseSlug = mb_strtolower($baseSlug, 'UTF-8');
            $baseSlug = preg_replace('/-+/u', '-', $baseSlug);
            $baseSlug = trim($baseSlug, '-');
        } else {
            $baseSlug = Str::slug($title);

            $parts = array_values(
                array_filter(explode('-', $baseSlug))
            );

            $baseSlug = implode(
                '-',
                array_slice($parts, 0, 10)
            );
        }

        if ($baseSlug === '') {
            $baseSlug = 'news';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Permalink::query()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * إعادة توليد sitemap.xml ليبقى متزامنًا مع حالة نشر الأخبار.
     */
    private function regenerateSitemap(): void
    {
        Artisan::call('sitemap:generate');
    }
}

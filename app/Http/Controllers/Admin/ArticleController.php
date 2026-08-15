<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Permalink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

class ArticleController extends Controller
{
    /**
     * الوسوم المسموح بها داخل محتوى المقال — تطابق تمامًا القائمة
     * المسموحة عند العرض في الواجهة العامة (articles/show.blade.php).
     */
    private const ALLOWED_CONTENT_TAGS = 'p,br,strong,em,b,i,a[href|title|target|rel],ul,ol,li,h2,h3,h4,blockquote,hr';

    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with(['category', 'author', 'permalinks'])
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
                $request->filled('article_category_id'),
                fn ($query) => $query->where('article_category_id', $request->integer('article_category_id'))
            )
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = ArticleCategory::query()->ordered()->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create(): View
    {
        $categories = ArticleCategory::query()->active()->ordered()->get();
        $authors = Admin::query()->orderBy('name')->get();

        return view('admin.articles.create', compact('categories', 'authors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateArticle($request);
        $validated = $this->prepareArticleData($request, $validated);

        $storedImage = null;

        if ($request->hasFile('featured_image')) {
            $storedImage = $this->storeImage($request);
            $validated['featured_image'] = $storedImage;
        }

        try {
            DB::transaction(function () use ($validated): void {
                $article = Article::query()->create($validated);

                $this->syncPermalinks($article);
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($storedImage);
            throw $exception;
        }

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'تمت إضافة المقال بنجاح.');
    }

    public function edit(Article $article): View
    {
        $article->load('permalinks');

        $categories = ArticleCategory::query()->active()->ordered()->get();
        $authors = Admin::query()->orderBy('name')->get();

        return view('admin.articles.edit', compact('article', 'categories', 'authors'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $this->validateArticle($request, $article);
        $validated = $this->prepareArticleData($request, $validated);

        $oldImage = $article->featured_image;
        $newImage = null;

        if ($request->hasFile('featured_image')) {
            $newImage = $this->storeImage($request);
            $validated['featured_image'] = $newImage;
        }

        try {
            DB::transaction(function () use ($article, $validated): void {
                $article->update($validated);

                $this->syncPermalinks($article);
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($newImage);
            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null && $oldImage !== $newImage) {
            $this->deleteImage($oldImage);
        }

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'تم تحديث المقال بنجاح.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $image = $article->featured_image;

        DB::transaction(function () use ($article): void {
            $article->permalinks()->delete();
            $article->delete();
        });

        $this->deleteImage($image);

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'تم حذف المقال بنجاح.');
    }

    public function toggleStatus(Article $article): RedirectResponse
    {
        if ($article->status === Article::STATUS_PUBLISHED) {
            $article->update(['status' => Article::STATUS_DRAFT]);
            $message = 'تم تحويل المقال إلى مسودة.';
        } else {
            $article->update([
                'status' => Article::STATUS_PUBLISHED,
                'published_at' => $article->published_at ?? now(),
            ]);
            $message = 'تم نشر المقال.';
        }

        $this->regenerateSitemap();

        return back()->with('success', $message);
    }

    public function toggleFeatured(Article $article): RedirectResponse
    {
        $article->update(['is_featured' => ! $article->is_featured]);

        $message = $article->is_featured
            ? 'تمت إضافة المقال إلى المقالات المميزة.'
            : 'تمت إزالة المقال من المقالات المميزة.';

        return back()->with('success', $message);
    }

    private function validateArticle(Request $request, ?Article $article = null): array
    {
        return $request->validate([
            'article_category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:admins,id'],
            'title_ar' => ['required', 'string', 'max:500'],
            'title_en' => ['nullable', 'string', 'max:500'],
            'excerpt_ar' => ['nullable', 'string', 'max:500'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'content_ar' => ['required', 'string'],
            'content_en' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['required', \Illuminate\Validation\Rule::in([
                Article::STATUS_DRAFT,
                Article::STATUS_PUBLISHED,
                Article::STATUS_ARCHIVED,
            ])],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title_ar' => ['nullable', 'string', 'max:255'],
            'meta_title_en' => ['nullable', 'string', 'max:255'],
            'meta_description_ar' => ['nullable', 'string', 'max:500'],
            'meta_description_en' => ['nullable', 'string', 'max:500'],
            'meta_keywords_ar' => ['nullable', 'string', 'max:255'],
            'meta_keywords_en' => ['nullable', 'string', 'max:255'],
        ], [
            'title_ar.required' => 'عنوان المقال بالعربية مطلوب.',
            'content_ar.required' => 'محتوى المقال بالعربية مطلوب.',
            'featured_image.image' => 'الملف المحدد يجب أن يكون صورة.',
            'featured_image.mimes' => 'صيغة الصورة يجب أن تكون JPG أو JPEG أو PNG أو WEBP.',
            'featured_image.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت.',
        ]);
    }

    private function prepareArticleData(Request $request, array $validated): array
    {
        $validated['title_ar'] = trim($validated['title_ar']);
        $validated['title_en'] = filled($validated['title_en'] ?? null) ? trim($validated['title_en']) : null;

        $validated['content_ar'] = Purifier::clean($validated['content_ar'], ['HTML.Allowed' => self::ALLOWED_CONTENT_TAGS]);
        $validated['content_en'] = filled($validated['content_en'] ?? null)
            ? Purifier::clean($validated['content_en'], ['HTML.Allowed' => self::ALLOWED_CONTENT_TAGS])
            : null;

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['author_id'] = $validated['author_id'] ?? Auth::guard('admin')->id();

        if ($validated['status'] === Article::STATUS_PUBLISHED && blank($validated['published_at'] ?? null)) {
            $validated['published_at'] = now();
        }

        unset($validated['featured_image']);

        return $validated;
    }

    /**
     * إنشاء الروابط الدائمة الناقصة للمقال (عربي إلزامي، إنجليزي عند توفر عنوانه).
     *
     * لا نُعدّل رابطًا موجودًا مسبقًا حتى لا نكسر روابط منشورة سابقًا،
     * لكن خلافًا لـ News نُعيد الفحص عند كل تحديث حتى تُنشأ الترجمة
     * الإنجليزية تلقائيًا إن أُضيفت لاحقًا بعد إنشاء المقال.
     */
    private function syncPermalinks(Article $article): void
    {
        foreach (['ar' => $article->title_ar, 'en' => $article->title_en] as $locale => $title) {
            if (blank($title)) {
                continue;
            }

            $exists = Permalink::query()
                ->where('linkable_type', 'article')
                ->where('linkable_id', $article->id)
                ->where('locale', $locale)
                ->exists();

            if ($exists) {
                continue;
            }

            Permalink::query()->create([
                'linkable_type' => 'article',
                'linkable_id' => $article->id,
                'locale' => $locale,
                'slug' => $this->generateUniqueSlug($title),
            ]);
        }
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'article-'.Str::lower(Str::random(8));
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
     * إعادة توليد sitemap.xml ليبقى متزامنًا مع حالة نشر المقالات.
     */
    private function regenerateSitemap(): void
    {
        Artisan::call('sitemap:generate');
    }

    /**
     * حفظ صورة المقال مباشرة داخل public/uploads/articles
     * بدون الحاجة إلى php artisan storage:link.
     */
    private function storeImage(Request $request): string
    {
        $image = $request->file('featured_image');
        $directory = public_path('uploads/articles');

        File::ensureDirectoryExists($directory, 0755, true);

        $extension = strtolower($image->getClientOriginalExtension());
        $fileName = now()->format('YmdHis').'-'.Str::lower(Str::random(16)).'.'.$extension;

        $image->move($directory, $fileName);

        return 'uploads/articles/'.$fileName;
    }

    /**
     * حذف صورة المقال من public/uploads/articles فقط.
     */
    private function deleteImage(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if (! Str::startsWith($normalizedPath, 'uploads/articles/')) {
            return;
        }

        $fullPath = public_path($normalizedPath);

        if (File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}

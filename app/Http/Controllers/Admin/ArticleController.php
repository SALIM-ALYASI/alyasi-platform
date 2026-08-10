<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with(['category', 'author'])
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where('title', 'like', '%'.trim($request->string('search')->toString()).'%')
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
                Article::query()->create($validated);
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($storedImage);
            throw $exception;
        }

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'تمت إضافة المقال بنجاح.');
    }

    public function edit(Article $article): View
    {
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
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($newImage);
            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null && $oldImage !== $newImage) {
            $this->deleteImage($oldImage);
        }

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'تم تحديث المقال بنجاح.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $image = $article->featured_image;

        $article->delete();

        $this->deleteImage($image);

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
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:280',
                Rule::unique('articles', 'slug')->ignore($article?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['required', Rule::in([
                Article::STATUS_DRAFT,
                Article::STATUS_PUBLISHED,
                Article::STATUS_ARCHIVED,
            ])],
            'is_featured' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'عنوان المقال مطلوب.',
            'content.required' => 'محتوى المقال مطلوب.',
            'slug.unique' => 'الرابط المختصر مستخدم مسبقًا.',
            'featured_image.image' => 'الملف المحدد يجب أن يكون صورة.',
            'featured_image.mimes' => 'صيغة الصورة يجب أن تكون JPG أو JPEG أو PNG أو WEBP.',
            'featured_image.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت.',
        ]);
    }

    private function prepareArticleData(Request $request, array $validated): array
    {
        $validated['title'] = trim($validated['title']);
        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['title'],
            $request->route('article')?->id
        );

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['author_id'] = $validated['author_id'] ?? Auth::guard('admin')->id();

        if ($validated['status'] === Article::STATUS_PUBLISHED && blank($validated['published_at'] ?? null)) {
            $validated['published_at'] = now();
        }

        unset($validated['featured_image']);

        return $validated;
    }

    private function generateUniqueSlug(?string $requestedSlug, string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($requestedSlug ?: $title);

        if ($baseSlug === '') {
            $baseSlug = 'article-'.Str::lower(Str::random(8));
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Article::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
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

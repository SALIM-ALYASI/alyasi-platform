<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleCategoryController extends Controller
{
    /**
     * عرض تصنيفات المقالات.
     */
    public function index(Request $request): View
    {
        $categories = ArticleCategory::query()
            ->withCount('articles')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->string('search')->toString());

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.article-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.article-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name_ar']
        );

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        ArticleCategory::create($validated);

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'تمت إضافة تصنيف المقالات بنجاح.');
    }

    public function edit(ArticleCategory $articleCategory): View
    {
        return view('admin.article-categories.edit', [
            'category' => $articleCategory,
        ]);
    }

    public function update(Request $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $validated = $this->validateCategory($request, $articleCategory);

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name_ar'],
            $articleCategory->id
        );

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $articleCategory->update($validated);

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'تم تحديث تصنيف المقالات بنجاح.');
    }

    public function destroy(ArticleCategory $articleCategory): RedirectResponse
    {
        $articleCategory->delete();

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'تم حذف تصنيف المقالات بنجاح.');
    }

    public function toggleStatus(ArticleCategory $articleCategory): RedirectResponse
    {
        $articleCategory->update([
            'is_active' => ! $articleCategory->is_active,
        ]);

        $message = $articleCategory->is_active
            ? 'تم تفعيل التصنيف.'
            : 'تم إخفاء التصنيف.';

        return back()->with('success', $message);
    }

    private function validateCategory(Request $request, ?ArticleCategory $category = null): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                Rule::unique('article_categories', 'slug')->ignore($category?->id),
            ],
            'description_ar' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name_ar.required' => 'اسم التصنيف بالعربية مطلوب.',
            'slug.unique' => 'الرابط المختصر مستخدم مسبقًا.',
        ]);
    }

    private function generateUniqueSlug(?string $requestedSlug, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($requestedSlug ?: $name);

        if ($baseSlug === '') {
            $baseSlug = 'article-category';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            ArticleCategory::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

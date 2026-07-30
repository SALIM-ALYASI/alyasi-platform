<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technology;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TechnologyController extends Controller
{
    /**
     * عرض التقنيات.
     */
    public function index(Request $request): View
    {
        $technologies = Technology::query()
            ->withCount('works')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        $request->string('search')->toString()
                    );

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $status = $request
                        ->string('status')
                        ->toString();

                    if ($status === 'active') {
                        $query->where('is_active', true);
                    }

                    if ($status === 'inactive') {
                        $query->where('is_active', false);
                    }
                }
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.technologies.index',
            compact('technologies')
        );
    }

    /**
     * صفحة إضافة تقنية.
     */
    public function create(): View
    {
        return view('admin.technologies.create');
    }

    /**
     * حفظ التقنية.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTechnology($request);

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        Technology::create($validated);

        return redirect()
            ->route('admin.technologies.index')
            ->with('success', 'تمت إضافة التقنية بنجاح.');
    }

    /**
     * عرض تفاصيل التقنية.
     */
    public function show(Technology $technology): View
    {
        $technology->loadCount('works');

        return view(
            'admin.technologies.show',
            compact('technology')
        );
    }

    /**
     * صفحة تعديل التقنية.
     */
    public function edit(Technology $technology): View
    {
        return view(
            'admin.technologies.edit',
            compact('technology')
        );
    }

    /**
     * تحديث التقنية.
     */
    public function update(
        Request $request,
        Technology $technology
    ): RedirectResponse {
        $validated = $this->validateTechnology(
            $request,
            $technology
        );

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['name'],
            $technology->id
        );

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $technology->update($validated);

        return redirect()
            ->route('admin.technologies.index')
            ->with('success', 'تم تحديث التقنية بنجاح.');
    }

    /**
     * حذف التقنية.
     */
    public function destroy(Technology $technology): RedirectResponse
    {
        $technology->works()->detach();
        $technology->delete();

        return redirect()
            ->route('admin.technologies.index')
            ->with('success', 'تم حذف التقنية بنجاح.');
    }

    /**
     * تغيير حالة التقنية.
     */
    public function toggleStatus(
        Technology $technology
    ): RedirectResponse {
        $technology->update([
            'is_active' => ! $technology->is_active,
        ]);

        $message = $technology->is_active
            ? 'تم تفعيل التقنية.'
            : 'تم إخفاء التقنية.';

        return back()->with('success', $message);
    }

    /**
     * التحقق من البيانات.
     */
    private function validateTechnology(
        Request $request,
        ?Technology $technology = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('technologies', 'slug')
                    ->ignore($technology?->id),
            ],

            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'name.required' => 'اسم التقنية مطلوب.',
            'name.max' => 'اسم التقنية يجب ألا يتجاوز 255 حرفًا.',

            'slug.unique' => 'الرابط المختصر مستخدم مسبقًا.',
            'slug.max' => 'الرابط المختصر يجب ألا يتجاوز 255 حرفًا.',

            'icon.max' => 'كود الأيقونة طويل جدًا.',

            'sort_order.integer' => 'الترتيب يجب أن يكون رقمًا صحيحًا.',
            'sort_order.min' => 'الترتيب يجب ألا يقل عن صفر.',
        ]);
    }

    /**
     * إنشاء Slug فريد.
     */
    private function generateUniqueSlug(
        ?string $requestedSlug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug(
            $requestedSlug ?: $name
        );

        if ($baseSlug === '') {
            $baseSlug = 'technology';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Technology::query()
                ->when(
                    $ignoreId,
                    fn ($query) => $query->whereKeyNot($ignoreId)
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

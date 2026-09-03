<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Technology;
use App\Models\Work;
use App\Rules\CleanSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkController extends Controller
{
    /**
     * عرض قائمة الأعمال.
     */
    public function index(Request $request): View
    {
        $works = Work::query()
            ->with([
                'service:id,title_ar,title_en',
                'technologies:id,name,icon',
            ])
            ->when(
                $request->filled('search'),
                function ($query) use ($request): void {
                    $search = trim(
                        $request->string('search')->toString()
                    );

                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere(
                                    'client_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'short_description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where(
                    'type',
                    $request->string('type')->toString()
                )
            )
            ->when(
                $request->filled('service_id'),
                fn ($query) => $query->where(
                    'service_id',
                    $request->integer('service_id')
                )
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request): void {
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
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $services = Service::query()
            ->ordered()
            ->get([
                'id',
                'title_ar',
                'title_en',
            ]);

        $types = $this->workTypes();

        return view(
            'admin.works.index',
            compact(
                'works',
                'services',
                'types'
            )
        );
    }

    /**
     * عرض صفحة إضافة عمل جديد.
     */
    public function create(): View
    {
        $services = Service::query()
            ->active()
            ->ordered()
            ->get([
                'id',
                'title_ar',
                'title_en',
            ]);

        $technologies = Technology::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'icon',
            ]);

        $types = $this->workTypes();

        return view(
            'admin.works.create',
            compact(
                'services',
                'technologies',
                'types'
            )
        );
    }

    /**
     * حفظ عمل جديد.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWork($request);

        DB::transaction(
            function () use ($request, $validated): void {
                $technologyIds = $validated['technology_ids'] ?? [];
                $galleryImages = $validated['gallery_images'] ?? [];

                unset(
                    $validated['technology_ids'],
                    $validated['gallery_images']
                );

                $validated['slug'] = $this->generateUniqueSlug(
                    requestedSlug: $validated['slug'] ?? null,
                    title: $validated['title']
                );

                $validated['is_featured'] = $request->boolean(
                    'is_featured'
                );

                $validated['is_active'] = $request->boolean(
                    'is_active'
                );

                $validated['sort_order'] = $validated[
                    'sort_order'
                ] ?? 0;

                if ($request->hasFile('cover_image')) {
                    $validated['cover_image'] = $request
                        ->file('cover_image')
                        ->store(
                            'works/covers',
                            'public'
                        );
                }

                $work = Work::query()->create($validated);

                $work
                    ->technologies()
                    ->sync($technologyIds);

                $this->storeGalleryImages(
                    work: $work,
                    images: $galleryImages,
                    altText: $work->title
                );
            }
        );

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.works.index')
            ->with(
                'success',
                'تمت إضافة العمل بنجاح.'
            );
    }

    /**
     * عرض تفاصيل العمل.
     */
    public function show(Work $work): View
    {
        $work->load([
            'service:id,title_ar,title_en',
            'technologies:id,name,icon',
            'images',
        ]);

        $types = $this->workTypes();

        return view(
            'admin.works.show',
            compact(
                'work',
                'types'
            )
        );
    }

    /**
     * عرض صفحة تعديل العمل.
     */
    public function edit(Work $work): View
    {
        $work->load([
            'service:id,title_ar,title_en',
            'technologies:id,name,icon',
            'images',
        ]);

        $services = Service::query()
            ->ordered()
            ->get([
                'id',
                'title_ar',
                'title_en',
            ]);

        $technologies = Technology::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'icon',
            ]);

        $types = $this->workTypes();

        return view(
            'admin.works.edit',
            compact(
                'work',
                'services',
                'technologies',
                'types'
            )
        );
    }

    /**
     * تحديث العمل.
     */
    public function update(
        Request $request,
        Work $work
    ): RedirectResponse {
        $validated = $this->validateWork(
            request: $request,
            work: $work
        );

        DB::transaction(
            function () use (
                $request,
                $validated,
                $work
            ): void {
                $technologyIds = $validated['technology_ids'] ?? [];
                $galleryImages = $validated['gallery_images'] ?? [];

                unset(
                    $validated['technology_ids'],
                    $validated['gallery_images']
                );

                $validated['slug'] = $this->generateUniqueSlug(
                    requestedSlug: $validated['slug'] ?? null,
                    title: $validated['title'],
                    ignoreId: $work->id
                );

                $validated['is_featured'] = $request->boolean(
                    'is_featured'
                );

                $validated['is_active'] = $request->boolean(
                    'is_active'
                );

                $validated['sort_order'] = $validated[
                    'sort_order'
                ] ?? 0;

                $oldCoverImage = null;

                if ($request->hasFile('cover_image')) {
                    $oldCoverImage = $work->cover_image;

                    $validated['cover_image'] = $request
                        ->file('cover_image')
                        ->store(
                            'works/covers',
                            'public'
                        );
                }

                $work->update($validated);

                $work
                    ->technologies()
                    ->sync($technologyIds);

                $this->storeGalleryImages(
                    work: $work,
                    images: $galleryImages,
                    altText: $work->title
                );

                if ($oldCoverImage !== null) {
                    $this->deleteStoredFile($oldCoverImage);
                }
            }
        );

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.works.index')
            ->with(
                'success',
                'تم تحديث العمل بنجاح.'
            );
    }

    /**
     * حذف العمل.
     */
    public function destroy(Work $work): RedirectResponse
    {
        $work->load([
            'images',
            'technologies',
        ]);

        $coverImage = $work->cover_image;

        $galleryImages = $work
            ->images
            ->pluck('image')
            ->filter()
            ->values()
            ->all();

        DB::transaction(
            function () use ($work): void {
                $work
                    ->technologies()
                    ->detach();

                $work
                    ->images()
                    ->delete();

                $work->delete();
            }
        );

        $this->deleteStoredFile($coverImage);

        foreach ($galleryImages as $galleryImage) {
            $this->deleteStoredFile($galleryImage);
        }

        $this->regenerateSitemap();

        return redirect()
            ->route('admin.works.index')
            ->with(
                'success',
                'تم حذف العمل بنجاح.'
            );
    }

    /**
     * حذف صورة من معرض العمل.
     */
    public function destroyImage(
        Work $work,
        int $image
    ): RedirectResponse {
        $workImage = $work
            ->images()
            ->whereKey($image)
            ->firstOrFail();

        $imagePath = $workImage->image;

        $workImage->delete();

        $this->deleteStoredFile($imagePath);

        return back()->with(
            'success',
            'تم حذف الصورة بنجاح.'
        );
    }

    /**
     * تغيير حالة نشر العمل.
     */
    public function toggleStatus(
        Work $work
    ): RedirectResponse {
        $work->update([
            'is_active' => ! $work->is_active,
        ]);

        $this->regenerateSitemap();

        $message = $work->is_active
            ? 'تم نشر العمل بنجاح.'
            : 'تم إخفاء العمل بنجاح.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
     * تغيير حالة العمل المميز.
     */
    public function toggleFeatured(
        Work $work
    ): RedirectResponse {
        $work->update([
            'is_featured' => ! $work->is_featured,
        ]);

        $message = $work->is_featured
            ? 'تمت إضافة العمل إلى الأعمال المميزة.'
            : 'تمت إزالة العمل من الأعمال المميزة.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
     * التحقق من بيانات العمل.
     */
    private function validateWork(
        Request $request,
        ?Work $work = null
    ): array {
        return $request->validate([
            'service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:180',
                new CleanSlug,
                Rule::unique(
                    'works',
                    'slug'
                )->ignore($work?->id),
            ],

            'type' => [
                'required',
                Rule::in(
                    array_keys($this->workTypes())
                ),
            ],

            'short_description' => [
                'required',
                'string',
                'max:500',
            ],

            'outcome' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'client_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'work_url' => [
                'nullable',
                'url',
                'max:1000',
            ],

            'completed_at' => [
                'nullable',
                'date',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'cover_image' => [
                $work ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'technology_ids' => [
                'nullable',
                'array',
            ],

            'technology_ids.*' => [
                'integer',
                'distinct',
                'exists:technologies,id',
            ],

            'gallery_images' => [
                'nullable',
                'array',
                'max:15',
            ],

            'gallery_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'service_id.exists' => 'الخدمة المحددة غير موجودة.',

            'title.required' => 'عنوان العمل مطلوب.',
            'title.max' => 'عنوان العمل يجب ألا يتجاوز 255 حرفًا.',

            'slug.unique' => 'الرابط المختصر مستخدم مسبقًا.',
            'slug.max' => 'الرابط المختصر يجب ألا يتجاوز 255 حرفًا.',

            'type.required' => 'نوع العمل مطلوب.',
            'type.in' => 'نوع العمل المحدد غير صحيح.',

            'short_description.required' => 'الوصف المختصر مطلوب.',
            'short_description.max' => 'الوصف المختصر يجب ألا يتجاوز 500 حرف.',

            'outcome.max' => 'سطر النتيجة يجب ألا يتجاوز 255 حرفًا.',

            'client_name.max' => 'اسم العميل يجب ألا يتجاوز 255 حرفًا.',

            'work_url.url' => 'رابط العمل غير صحيح.',
            'work_url.max' => 'رابط العمل طويل جدًا.',

            'completed_at.date' => 'تاريخ الإنجاز غير صحيح.',

            'sort_order.integer' => 'الترتيب يجب أن يكون رقمًا صحيحًا.',
            'sort_order.min' => 'الترتيب يجب ألا يقل عن صفر.',

            'cover_image.required' => 'صورة الغلاف مطلوبة.',
            'cover_image.image' => 'ملف الغلاف يجب أن يكون صورة.',
            'cover_image.mimes' => 'صيغة صورة الغلاف غير مسموحة.',
            'cover_image.max' => 'حجم صورة الغلاف يجب ألا يتجاوز 5 ميجابايت.',

            'technology_ids.array' => 'بيانات التقنيات غير صحيحة.',
            'technology_ids.*.exists' => 'إحدى التقنيات المحددة غير موجودة.',

            'gallery_images.array' => 'بيانات صور المعرض غير صحيحة.',
            'gallery_images.max' => 'يمكن إضافة 15 صورة كحد أقصى.',
            'gallery_images.*.image' => 'يجب أن تكون جميع ملفات المعرض صورًا.',
            'gallery_images.*.mimes' => 'إحدى صور المعرض بصيغة غير مسموحة.',
            'gallery_images.*.max' => 'حجم كل صورة يجب ألا يتجاوز 5 ميجابايت.',
        ]);
    }

    /**
     * إنشاء رابط مختصر فريد.
     */
    private function generateUniqueSlug(
        ?string $requestedSlug,
        string $title,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug(
            $requestedSlug ?: $title
        );

        if ($baseSlug === '') {
            $baseSlug = 'work';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Work::query()
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $ignoreId
                    )
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * حفظ صور معرض العمل.
     */
    private function storeGalleryImages(
        Work $work,
        array $images,
        string $altText
    ): void {
        if (empty($images)) {
            return;
        }

        $lastOrder = (int) $work
            ->images()
            ->max('sort_order');

        foreach ($images as $index => $image) {
            $path = $image->store(
                'works/gallery',
                'public'
            );

            $work->images()->create([
                'image' => $path,
                'alt_text' => $altText,
                'sort_order' => $lastOrder + $index + 1,
            ]);
        }
    }

    /**
     * حذف ملف من قرص التخزين العام.
     */
    private function deleteStoredFile(
        ?string $path
    ): void {
        if (! filled($path)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * أنواع الأعمال المتاحة.
     */
    private function workTypes(): array
    {
        return [
            'website' => 'موقع إلكتروني',
            'application' => 'تطبيق',
            'design' => 'تصميم',
            'advertisement' => 'إعلان',
            'video' => 'فيديو ومونتاج',
            'photography' => 'تصوير',
            'event' => 'فعالية أو مشاركة',
            'personal' => 'عمل شخصي',
            'other' => 'أعمال أخرى',
        ];
    }

    /**
     * إعادة توليد sitemap.xml ليبقى متزامنًا مع حالة نشر الأعمال.
     */
    private function regenerateSitemap(): void
    {
        Artisan::call('sitemap:generate');
    }
}

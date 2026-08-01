<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permalink;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()
            ->with('permalinks')
            ->search($request->string('search')->toString())
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateServiceRequest($request);
        $validated = $this->prepareServiceData($request, $validated);

        $storedImage = null;

        if ($request->hasFile('image')) {
            $storedImage = $this->storeImage($request);
            $validated['image'] = $storedImage;
        }

        try {
            DB::transaction(function () use ($validated): void {
                $service = Service::query()->create($validated);
                $this->ensureServicePermalinksExist($service);
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($storedImage);
            throw $exception;
        }

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تمت إضافة الخدمة وإنشاء روابطها الدائمة بنجاح.');
    }

    // public function show(Service $service): RedirectResponse
    // {
    //     return redirect()->route('admin.services.edit', $service);
    // }

    public function edit(Service $service): View
    {
        $service->load('permalinks');

        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $this->validateServiceRequest($request);
        $validated = $this->prepareServiceData($request, $validated);

        $oldImage = $service->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->storeImage($request);
            $validated['image'] = $newImage;
        }

        try {
            DB::transaction(function () use ($service, $validated): void {
                $service->update($validated);
                $this->ensureServicePermalinksExist($service->fresh());
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($newImage);
            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null && $oldImage !== $newImage) {
            $this->deleteImage($oldImage);
        }

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم تحديث الخدمة بنجاح مع الحفاظ على روابطها الدائمة.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $image = $service->image;

        DB::transaction(function () use ($service): void {
            $service->permalinks()->delete();
            $service->delete();
        });

        $this->deleteImage($image);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'تم حذف الخدمة وروابطها الدائمة بنجاح.');
    }

    private function validateServiceRequest(Request $request): array
    {
        return $request->validate(
            [
                'title_ar' => ['required', 'string', 'max:255'],
                'description_ar' => ['required', 'string', 'min:10', 'max:5000'],
                'title_en' => ['nullable', 'string', 'max:255', 'required_with:description_en'],
                'description_en' => ['nullable', 'string', 'min:10', 'max:5000', 'required_with:title_en'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'is_active' => ['nullable', 'boolean'],
            ],
            [
                'title_ar.required' => 'عنوان الخدمة بالعربية مطلوب.',
                'title_ar.max' => 'عنوان الخدمة بالعربية يجب ألا يتجاوز 255 حرفًا.',
                'description_ar.required' => 'وصف الخدمة بالعربية مطلوب.',
                'description_ar.min' => 'وصف الخدمة بالعربية يجب ألا يقل عن 10 أحرف.',
                'title_en.required_with' => 'العنوان الإنجليزي مطلوب عند كتابة الوصف الإنجليزي.',
                'description_en.required_with' => 'الوصف الإنجليزي مطلوب عند كتابة العنوان الإنجليزي.',
                'description_en.min' => 'الوصف الإنجليزي يجب ألا يقل عن 10 أحرف.',
                'image.image' => 'الملف المحدد يجب أن يكون صورة.',
                'image.mimes' => 'صيغة الصورة يجب أن تكون JPG أو JPEG أو PNG أو WEBP.',
                'image.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت.',
            ]
        );
    }

    private function prepareServiceData(Request $request, array $validated): array
    {
        $validated['title_ar'] = trim($validated['title_ar']);
        $validated['description_ar'] = trim($validated['description_ar']);
        $validated['title_en'] = filled($validated['title_en'] ?? null)
            ? trim($validated['title_en'])
            : null;
        $validated['description_en'] = filled($validated['description_en'] ?? null)
            ? trim($validated['description_en'])
            : null;
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['image']);

        return $validated;
    }

    /**
     * حفظ الصورة مباشرة داخل public/uploads/services
     * بدون الحاجة إلى php artisan storage:link.
     */
    private function storeImage(Request $request): string
    {
        $image = $request->file('image');
        $directory = public_path('uploads/services');

        File::ensureDirectoryExists($directory, 0755, true);

        $extension = strtolower($image->getClientOriginalExtension());
        $fileName = now()->format('YmdHis')
            .'-'
            .Str::lower(Str::random(16))
            .'.'
            .$extension;

        $image->move($directory, $fileName);

        return 'uploads/services/'.$fileName;
    }

    private function ensureServicePermalinksExist(Service $service): void
    {
        $this->createPermalinkIfMissing(
            service: $service,
            locale: 'ar',
            title: $service->title_ar
        );

        if (filled($service->title_en)) {
            $this->createPermalinkIfMissing(
                service: $service,
                locale: 'en',
                title: $service->title_en
            );
        }
    }

    private function createPermalinkIfMissing(
        Service $service,
        string $locale,
        string $title
    ): void {
        $exists = $service
            ->permalinks()
            ->where('locale', $locale)
            ->exists();

        if ($exists) {
            return;
        }

        $service->permalinks()->create([
            'locale' => $locale,
            'slug' => $this->generateUniqueSlug($title, $locale),
        ]);
    }

    private function generateUniqueSlug(string $title, string $locale): string
    {
        $baseSlug = $this->formatSlug($title, $locale);

        if ($baseSlug === '') {
            $baseSlug = 'service-'.Str::lower(Str::random(8));
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Permalink::query()
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function formatSlug(string $value, string $locale): string
    {
        $value = trim($value);

        if ($locale === 'en') {
            return Str::slug($value);
        }

        $value = mb_strtolower($value);
        $value = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s_-]+/u', '', $value);
        $value = preg_replace('/[\s_-]+/u', '-', (string) $value);

        return trim((string) $value, '-');
    }

    /**
     * حذف صورة الخدمة من public/uploads/services فقط.
     */
    private function deleteImage(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if (! Str::startsWith($normalizedPath, 'uploads/services/')) {
            return;
        }

        $fullPath = public_path($normalizedPath);

        if (File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}

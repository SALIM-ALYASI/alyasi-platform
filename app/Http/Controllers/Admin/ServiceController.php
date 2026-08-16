<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permalink;
use App\Models\PermalinkRedirect;
use App\Models\Service;
use App\Rules\CleanSlug;
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
        $manualSlugs = ['ar' => $validated['slug_ar'] ?? null, 'en' => $validated['slug_en'] ?? null];
        $validated = $this->prepareServiceData($request, $validated);

        $storedImage = null;

        if ($request->hasFile('image')) {
            $storedImage = $this->storeImage($request);
            $validated['image'] = $storedImage;
        }

        try {
            DB::transaction(function () use ($validated, $manualSlugs): void {
                $service = Service::query()->create($validated);
                $this->syncServicePermalinks($service, $manualSlugs);
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
        $manualSlugs = ['ar' => $validated['slug_ar'] ?? null, 'en' => $validated['slug_en'] ?? null];
        $validated = $this->prepareServiceData($request, $validated);

        $oldImage = $service->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->storeImage($request);
            $validated['image'] = $newImage;
        }

        try {
            DB::transaction(function () use ($service, $validated, $manualSlugs): void {
                $service->update($validated);
                $this->syncServicePermalinks($service->fresh(), $manualSlugs);
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
                'slug_ar' => ['required', 'string', 'max:180', new CleanSlug],
                'slug_en' => ['nullable', 'string', 'max:180', new CleanSlug, 'required_with:title_en'],
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
                'slug_ar.required' => 'الرابط المختصر بالعربية (الإنجليزي الشكل) مطلوب — لا يوجد توليد تلقائي بعد الآن.',
                'slug_en.required_with' => 'الرابط المختصر الإنجليزي مطلوب عند كتابة عنوان إنجليزي.',
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

        unset($validated['image'], $validated['slug_ar'], $validated['slug_en']);

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

    /**
     * إنشاء الروابط الدائمة الناقصة للخدمة، أو تحديثها لو المدير كتب
     * رابطًا يدويًا مختلفًا عن الحالي — مع حفظ القديم كتحويل 301 تلقائي.
     *
     * لا يوجد أي توليد/تخمين تلقائي للرابط هنا — العربي إلزامي دائمًا
     * والإنجليزي إلزامي عند وجود عنوان إنجليزي (يُمنع الحفظ بدونه أصلًا
     * عبر التحقق في validateServiceRequest())، تفاديًا لمشكلة الروابط
     * العربية الخام المرمّزة (%D8%...) التي كانت تظهر سابقًا.
     */
    private function syncServicePermalinks(Service $service, array $manualSlugs): void
    {
        $this->createOrUpdateServicePermalink($service, 'ar', $manualSlugs['ar'] ?? null);

        if (filled($service->title_en)) {
            $this->createOrUpdateServicePermalink($service, 'en', $manualSlugs['en'] ?? null);
        }
    }

    private function createOrUpdateServicePermalink(Service $service, string $locale, ?string $manualSlug): void
    {
        $manualSlug = filled($manualSlug) ? trim($manualSlug) : null;

        $permalink = $service->permalinks()->where('locale', $locale)->first();

        if (! $permalink) {
            $service->permalinks()->create([
                'locale' => $locale,
                'slug' => $this->generateUniqueSlug($manualSlug, $locale),
            ]);

            return;
        }

        if ($manualSlug === null || $manualSlug === $permalink->slug) {
            return;
        }

        $this->movePermalinkSlug($permalink, $manualSlug, $locale);
    }

    /**
     * تحديث رابط دائم موجود إلى slug جديد، مع حفظ القديم كتحويل 301.
     */
    private function movePermalinkSlug(Permalink $permalink, string $newRequestedSlug, string $locale): void
    {
        $newSlug = $this->generateUniqueSlug($newRequestedSlug, $locale);
        $oldSlug = $permalink->slug;

        DB::transaction(function () use ($permalink, $newSlug, $oldSlug, $locale): void {
            PermalinkRedirect::query()
                ->where('locale', $locale)
                ->where('old_slug', $oldSlug)
                ->delete();

            PermalinkRedirect::query()->create([
                'permalink_id' => $permalink->id,
                'locale' => $locale,
                'old_slug' => $oldSlug,
            ]);

            $permalink->update(['slug' => $newSlug]);
        });
    }

    private function generateUniqueSlug(string $requestedSlug, string $locale): string
    {
        $baseSlug = Str::slug($requestedSlug);

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

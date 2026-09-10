<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventEdition;
use App\Models\Permalink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventEditionController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->with([
                'editions' => fn ($query) => $query
                    ->with('permalinks')
                    ->orderByDesc('year')
                    ->orderByDesc('event_start_at'),
            ])
            ->withCount('editions')
            ->orderBy('name')
            ->paginate(12);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        $events = Event::query()->orderBy('name')->get();

        return view('admin.events.create', compact('events'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $eventId = $validated['event_id'];
        if ($eventId === 'new') {
            $eventName = $validated['new_event_name'];

            $event = Event::query()->create([
                'name' => $eventName,
                'slug' => $this->uniqueEventSlug($eventName),
                'organizer' => $validated['new_event_organizer'] ?? null,
            ]);
            $eventId = $event->id;
        }
        unset($validated['new_event_name'], $validated['new_event_organizer']);
        $validated['event_id'] = $eventId;

        $storedImage = null;
        if ($request->hasFile('image')) {
            $storedImage = $this->storeUpload($request->file('image'));
            $validated['image'] = $storedImage;
        }

        $validated['gallery'] = $this->mergeGallery($request, []);

        $slug = Str::slug($validated['title_ar']).'-'.$validated['year'];

        try {
            $edition = DB::transaction(function () use ($validated, $slug) {
                $edition = EventEdition::query()->create($validated);

                foreach (['ar', 'en'] as $locale) {
                    Permalink::query()->create([
                        'locale' => $locale,
                        'slug' => $slug,
                        'linkable_type' => 'event_edition',
                        'linkable_id' => $edition->id,
                    ]);
                }

                return $edition;
            });
        } catch (\Throwable $exception) {
            $this->deleteUpload($storedImage);
            throw $exception;
        }

        return redirect()
            ->route('admin.events.edit', $edition)
            ->with('success', 'تمت إضافة نسخة المؤتمر بنجاح.');
    }

    public function edit(EventEdition $event): View
    {
        $event->load('event', 'permalinks');

        return view('admin.events.edit', ['edition' => $event]);
    }

    public function update(Request $request, EventEdition $event): RedirectResponse
    {
        $validated = $this->validatedData($request);
        unset($validated['event_id'], $validated['new_event_name'], $validated['new_event_organizer'], $validated['year']);

        $oldImage = $event->image;
        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeUpload($request->file('image'));
        }

        $validated['gallery'] = $this->mergeGallery($request, $event->gallery ?? []);

        $event->update($validated);

        if (! empty($validated['image']) && $oldImage && $oldImage !== $validated['image']) {
            $this->deleteUpload($oldImage);
        }

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('success', 'تم تحديث نسخة المؤتمر بنجاح.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'event_id' => ['required', 'string'],
            'new_event_name' => ['required_if:event_id,new', 'nullable', 'string', 'max:255'],
            'new_event_organizer' => ['nullable', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'coverage_type' => ['required', 'in:global_remote,gulf_analysis,local_field'],
            'attended' => ['nullable', 'boolean'],
            'date_status' => ['required', 'in:confirmed,expected,unknown'],
            'event_start_at' => ['nullable', 'date'],
            'event_end_at' => ['nullable', 'date', 'after_or_equal:event_start_at'],
            'livestream_url' => ['nullable', 'url', 'max:1000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'kept_gallery' => ['nullable', 'array'],
            'kept_gallery.*' => ['nullable', 'string'],
            'new_gallery_images' => ['nullable', 'array'],
            'new_gallery_images.*' => ['nullable', 'image', 'max:5120'],
            'short_description_ar' => ['nullable', 'string', 'max:2000'],
            'short_description_en' => ['nullable', 'string', 'max:2000'],
            'announcements' => ['nullable', 'array'],
            'announcements.*.label_ar' => ['nullable', 'string', 'max:255'],
            'announcements.*.label_en' => ['nullable', 'string', 'max:255'],
            'announcements.*.note_ar' => ['nullable', 'string', 'max:2000'],
            'announcements.*.note_en' => ['nullable', 'string', 'max:2000'],
            'announcements.*.preorder_at' => ['nullable', 'date'],
            'announcements.*.available_at' => ['nullable', 'date'],
            'announcements.*.confidence' => ['nullable', 'in:confirmed,expected,rumored'],
            'announcements.*.image' => ['nullable', 'string'],
            'announcement_images' => ['nullable', 'array'],
            'announcement_images.*' => ['nullable', 'image', 'max:5120'],
            'pricing_table' => ['nullable', 'array'],
            'pricing_table.*.product_ar' => ['nullable', 'string', 'max:255'],
            'pricing_table.*.product_en' => ['nullable', 'string', 'max:255'],
            'pricing_table.*.official_price' => ['nullable', 'string', 'max:50'],
            'pricing_table.*.official_currency' => ['nullable', 'string', 'max:10'],
            'pricing_table.*.omr_price' => ['nullable', 'string', 'max:50'],
            'upgrade_verdict' => ['nullable', 'in:yes,no,specific_segment'],
            'upgrade_verdict_text' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data['attended'] = $request->boolean('attended');

        $announcementImages = $request->file('announcement_images', []);

        $data['announcements'] = collect($data['announcements'] ?? [])
            ->filter(fn (array $row) => filled($row['label_ar'] ?? null) || filled($row['label_en'] ?? null))
            ->map(function (array $row, int|string $key) use ($announcementImages) {
                $uploaded = $announcementImages[$key] ?? null;
                if ($uploaded instanceof UploadedFile && $uploaded->isValid()) {
                    $row['image'] = $this->storeUpload($uploaded);
                }

                return $row;
            })
            ->values()
            ->all();

        $data['pricing_table'] = collect($data['pricing_table'] ?? [])
            ->filter(fn (array $row) => filled($row['product_ar'] ?? null) || filled($row['product_en'] ?? null))
            ->values()
            ->all();

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        unset($data['kept_gallery'], $data['new_gallery_images'], $data['announcement_images']);

        return $data;
    }

    private function uniqueEventSlug(string $name): string
    {
        $normalized = mb_strtolower(trim($name));

        $base = match (true) {
            str_contains($normalized, 'apple'), str_contains($normalized, 'آبل'), str_contains($normalized, 'ابل') => 'apple',
            str_contains($normalized, 'samsung'), str_contains($normalized, 'سامسونج') => 'samsung',
            str_contains($normalized, 'huawei'), str_contains($normalized, 'هواوي') => 'huawei',
            str_contains($normalized, 'google'), str_contains($normalized, 'جوجل'), str_contains($normalized, 'غوغل') => 'google',
            str_contains($normalized, 'microsoft'), str_contains($normalized, 'مايكروسوفت') => 'microsoft',
            str_contains($normalized, 'comex') => 'comex-oman',
            default => Str::slug($name),
        };

        if ($base === '') {
            $base = 'event-'.Str::lower(Str::random(8));
        }

        $slug = $base;
        $counter = 2;

        while (Event::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function storeUpload(UploadedFile $file): string
    {
        $directory = public_path('uploads/events');

        File::ensureDirectoryExists($directory, 0755, true);

        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = now()->format('YmdHis').'-'.Str::lower(Str::random(16)).'.'.$extension;

        $file->move($directory, $fileName);

        return 'uploads/events/'.$fileName;
    }

    private function mergeGallery(Request $request, array $fallback): array
    {
        $kept = $request->input('kept_gallery');
        $paths = is_array($kept) ? array_values(array_filter($kept)) : $fallback;

        foreach ($request->file('new_gallery_images', []) as $image) {
            if ($image instanceof UploadedFile && $image->isValid()) {
                $paths[] = $this->storeUpload($image);
            }
        }

        return $paths;
    }

    private function deleteUpload(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if (! Str::startsWith($normalizedPath, 'uploads/events/')) {
            return;
        }

        $fullPath = public_path($normalizedPath);

        if (File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}

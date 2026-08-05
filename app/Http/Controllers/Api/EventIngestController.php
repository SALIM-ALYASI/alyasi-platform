<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventIngestController extends Controller
{
    /**
     * قائمة مختصرة بالفعاليات الموجودة - يستخدمها بوت الفعاليات (n8n) قبل
     * البحث عشان يتفادى اقتراح فعاليات مكرّرة أصلاً منشورة.
     */
    public function index(Request $request): JsonResponse
    {
        $events = CommunityPost::query()
            ->where('type', 'event')
            ->where(function ($query) {
                $query
                    ->whereNull('event_end_at')
                    ->orWhere('event_end_at', '>=', now());
            })
            ->orderBy('event_start_at')
            ->limit(min($request->integer('limit', 100), 200))
            ->get(['title', 'registration_url', 'event_start_at'])
            ->map(fn (CommunityPost $post) => [
                'title' => $post->title,
                'source_url' => $post->registration_url,
                'event_start_at' => $post->event_start_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $events]);
    }

    /**
     * استقبال فعالية من بوت الفعاليات (n8n) وحفظها بقسم المجتمع. الفعالية
     * تُطابَق أولاً برابط مصدرها (source_url) لمنع التكرار - لو موجودة
     * تُحدَّث بدل ما تُنشأ من جديد.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_start_at' => ['required', 'date'],
            'event_end_at' => ['nullable', 'date', 'after_or_equal:event_start_at'],
            'registration_url' => ['nullable', 'url', 'max:2048'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_base64' => ['nullable', 'string'],
            'source_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $title = trim($validated['title']);

        $post = CommunityPost::query()
            ->when(
                filled($validated['source_url'] ?? null),
                fn ($query) => $query->where('registration_url', $validated['source_url'])
            )
            ->firstOrNew([
                'slug' => $this->generateUniqueSlug($title),
            ]);

        $post->fill([
            'community_category_id' => $this->eventsCategoryId(),
            'title' => $title,
            'short_description' => Str::limit(strip_tags($validated['description']), 500, ''),
            'content' => $validated['description'],
            'type' => 'event',
            'location' => $validated['location'] ?? null,
            'event_start_at' => $validated['event_start_at'],
            'event_end_at' => $validated['event_end_at'] ?? null,
            'registration_url' => $validated['registration_url'] ?? $validated['source_url'] ?? null,
            'is_active' => true,
            'published_at' => $post->published_at ?? now(),
        ]);

        if (! $post->exists) {
            if (filled($validated['image_base64'] ?? null)) {
                $post->image = $this->storeBase64Image($validated['image_base64']);
            } elseif (filled($validated['image_url'] ?? null)) {
                $post->image = $this->downloadImage($validated['image_url']);
            }
        }

        $post->save();

        return response()->json([
            'success' => true,
            'id' => $post->id,
            'created' => $post->wasRecentlyCreated,
        ]);
    }

    /**
     * تحديث صورة فعالية موجودة - يستخدمها بوت إدارة المنصة (تلجرام) لما
     * يستقبل صورة مصمَّمة يدوياً بالرد على تنبيه فعالية معيّنة.
     */
    public function updateImage(Request $request, int $id): JsonResponse
    {
        $event = CommunityPost::query()
            ->where('type', 'event')
            ->findOrFail($id);

        $validated = $request->validate([
            'image_base64' => ['required', 'string'],
        ]);

        $newPath = $this->storeBase64Image($validated['image_base64']);

        if (! $newPath) {
            return response()->json([
                'success' => false,
                'message' => 'تعذّر حفظ الصورة.',
            ], 422);
        }

        $oldImage = $event->image;

        $event->update(['image' => $newPath]);

        if (filled($oldImage) && Storage::disk('public')->exists($oldImage)) {
            Storage::disk('public')->delete($oldImage);
        }

        return response()->json(['success' => true]);
    }

    /**
     * جلب معرّف تصنيف "الفعاليات" (يُنشئه لو ما كان موجوداً).
     */
    private function eventsCategoryId(): int
    {
        return CommunityCategory::query()
            ->firstOrCreate(
                ['name' => 'الفعاليات'],
                [
                    'slug' => Str::slug('الفعاليات'),
                    'description' => 'الفعاليات والمؤتمرات وورش العمل.',
                    'icon' => 'fa-solid fa-calendar-days',
                    'is_active' => true,
                ]
            )
            ->id;
    }

    /**
     * حفظ صورة فعالية مُرسلة كـbase64 (صور Gemini المولَّدة والمُبرَّزة
     * بلوجو ALYASI عبر خدمة النشر بالسيرفر).
     */
    private function storeBase64Image(string $base64): ?string
    {
        try {
            $cleaned = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $bytes = base64_decode($cleaned, true);

            if ($bytes === false) {
                return null;
            }

            $path = 'community/'.now()->format('Ymd_His').'_'.Str::random(6).'.jpg';

            Storage::disk('public')->put($path, $bytes);

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * تنزيل صورة الفعالية من رابط خارجي وحفظها محلياً.
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

            $path = 'community/'.now()->format('Ymd_His').'_'.Str::random(6).'.'.$extension;

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * توليد رابط مختصر فريد للفعالية.
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'event-'.Str::lower(Str::random(8));
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            CommunityPost::query()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

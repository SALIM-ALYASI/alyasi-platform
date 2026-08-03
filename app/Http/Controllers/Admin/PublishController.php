<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublishJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PublishController extends Controller
{
    /**
     * عرض صفحة النشر الذكي وآخر عمليات النشر.
     */
    public function index(): View
    {
        $jobs = PublishJob::query()
            ->latest()
            ->take(15)
            ->get();

        return view('admin.publish.index', compact('jobs'));
    }

    /**
     * إرسال المحتوى لخدمة النشر الموحّد (تنشره على 4 منصات بتوقيت ذكي لكل منصة).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:90'],
            'text' => ['required', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:10240'],
            'video' => ['nullable', 'mimes:mp4,mov,quicktime', 'max:51200'],
        ], [
            'text.required' => 'يرجى كتابة نص المنشور.',
            'image.image' => 'الملف المرفوع لازم يكون صورة.',
            'video.mimes' => 'الفيديو لازم يكون بصيغة mp4 أو mov.',
        ]);

        if (! $request->hasFile('image') && ! $request->hasFile('video')) {
            return back()
                ->withInput()
                ->with('error', 'لازم ترفع صورة أو فيديو على الأقل.');
        }

        $payload = [
            'text' => $validated['text'],
            'title' => $validated['title'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $payload['image_base64'] = 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $payload['video_base64'] = 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
        }

        $baseUrl = rtrim((string) config('services.publish.url'), '/');
        $apiKey = config('services.publish.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(120)
                ->post("{$baseUrl}/publish", $payload);
        } catch (\Throwable $e) {
            Log::warning('Publish API unreachable', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'تعذر الاتصال بخدمة النشر. تأكد من أنها شغّالة.');
        }

        if (! $response->successful()) {
            return back()
                ->withInput()
                ->with('error', 'فشل إرسال المحتوى للنشر: '.($response->json('error') ?? $response->status()));
        }

        $jobData = $response->json();

        PublishJob::query()->create([
            'job_id' => $jobData['job_id'],
            'title' => $validated['title'] ?? null,
            'text' => $validated['text'],
            'platforms' => $jobData['platforms'] ?? [],
        ]);

        return redirect()
            ->route('admin.publish.index')
            ->with('success', 'تم إرسال المحتوى بنجاح! جاري النشر على كل منصة بتوقيتها المناسب، وراح تتحدث الحالة هنا تلقائياً.');
    }

    /**
     * ترويسة المصادقة مع خدمة النشر.
     */
    private function authHeaders(?string $apiKey): array
    {
        return $apiKey ? ['x-api-key' => $apiKey] : [];
    }
}

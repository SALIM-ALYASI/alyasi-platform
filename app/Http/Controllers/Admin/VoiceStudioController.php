<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoiceStudioController extends Controller
{
    /**
     * عرض صفحة تحويل النص إلى صوت.
     */
    public function index(): View
    {
        $voices = $this->fetchVoices();

        $history = collect(Storage::disk('public')->files('voice-studio'))
            ->sortByDesc(fn (string $path) => Storage::disk('public')->lastModified($path))
            ->take(10)
            ->map(fn (string $path) => [
                'url' => Storage::disk('public')->url($path),
                'name' => basename($path),
                'created_at' => Storage::disk('public')->lastModified($path),
            ])
            ->values();

        return view('admin.voice-studio.index', [
            'voices' => $voices['voices'] ?? [],
            'defaultVoiceId' => $voices['default_voice']['id'] ?? null,
            'serviceAvailable' => $voices['available'],
            'history' => $history,
        ]);
    }

    /**
     * توليد مقطع صوتي من النص - عبر طابور narrate بالسيرفر، اللي يمرّره
     * لجهاز محلي أقوى بدل تحميل SoundInk الضعيف على السيرفر (نفس آلية بوت
     * الفيديو). أبطأ شوي بسبب طابور الانتظار، لكن أسرع بكثير من التوليد
     * المباشر على معالج السيرفر (كان يوصل 3-6 أضعاف الوقت الحقيقي).
     */
    public function generate(Request $request): RedirectResponse
    {
        // انتظار الطابور + التوليد الفعلي قد يأخذ دقيقة أو أكثر، فنرفع حد
        // تنفيذ PHP الافتراضي (30 ثانية) لهذا الطلب تحديدًا.
        set_time_limit(200);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
            'voice_id' => ['nullable', 'string'],
        ], [
            'text.required' => 'يرجى كتابة النص المراد تحويله لصوت.',
            'text.max' => 'النص يجب ألا يتجاوز 1000 حرف.',
        ]);

        $baseUrl = rtrim((string) config('services.publish.url'), '/');
        $apiKey = config('services.publish.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(180)
                ->post("{$baseUrl}/narrate", [
                    'text' => $validated['text'],
                    'voice_id' => $validated['voice_id'] ?? null,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Narration bridge unreachable', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'تعذر الاتصال بخدمة توليد الصوت. تأكد من أن الجسر والجهاز المحلي شغّالين.');
        }

        if (! $response->successful()) {
            return back()
                ->withInput()
                ->with('error', 'فشل توليد الصوت: '.$response->status());
        }

        $filename = 'voice-studio/'.now()->format('Ymd_His').'_'.Str::random(6).'.wav';
        Storage::disk('public')->put($filename, $response->body());

        return redirect()
            ->route('admin.voice-studio.index')
            ->with('success', 'تم توليد الصوت بنجاح.');
    }

    /**
     * تنزيل مقطع صوتي بإجبار المتصفح على الحفظ (بعض المتصفحات مثل Safari
     * تتجاهل خاصية download في وسم <a> وتفتح ملفات الصوت مباشرة بدلًا من تنزيلها).
     */
    public function download(string $filename): StreamedResponse
    {
        $safeName = basename($filename);
        $path = "voice-studio/{$safeName}";

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path);
    }

    /**
     * جلب قائمة الأصوات المتاحة من SoundInk.
     */
    private function fetchVoices(): array
    {
        $baseUrl = rtrim((string) config('services.soundink.url'), '/');
        $apiKey = config('services.soundink.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(5)
                ->get("{$baseUrl}/api/v1/voices");

            if (! $response->successful()) {
                return ['available' => false];
            }

            return [...$response->json(), 'available' => true];
        } catch (\Throwable $e) {
            return ['available' => false];
        }
    }

    private function authHeaders(?string $apiKey): array
    {
        return $apiKey ? ['X-API-Key' => $apiKey] : [];
    }
}

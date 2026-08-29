<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
     * توليد مقطع صوتي من النص - مباشرة عبر SoundInk (نفس سيرفر بوت الفيديو،
     * دايمًا شغّال، بدل جسر narrate القديم اللي كان يمرّر الطلب لجهاز شخصي
     * ثاني ممكن يكون مطفي). التوليد المباشر أبطأ (قد يوصل عدة دقايق)، فنستخدم
     * نظام طابور SoundInk نفسه (jobs/speak) ونتابع حالته بدل طلب واحد طويل
     * يتعرّض لتوقف PHP لو أخذ وقت أطول من المتوقع.
     */
    public function generate(Request $request): RedirectResponse
    {
        // الانتظار لحد اكتمال المهمة قد يوصل عدة دقايق بحالة نص طويل، فنرفع
        // حد تنفيذ PHP الافتراضي (30 ثانية) لهذا الطلب تحديدًا.
        set_time_limit(600);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
            'voice_id' => ['nullable', 'string'],
            'speed' => ['nullable', 'numeric', 'min:0.8', 'max:1.3'],
        ], [
            'text.required' => 'يرجى كتابة النص المراد تحويله لصوت.',
            'text.max' => 'النص يجب ألا يتجاوز 1000 حرف.',
        ]);

        $baseUrl = rtrim((string) config('services.soundink.url'), '/');
        $apiKey = Setting::get('soundink_api_key', (string) config('services.soundink.key'));
        $jobId = (string) Str::uuid();

        try {
            $created = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(15)
                ->post("{$baseUrl}/api/v1/jobs/speak", [
                    'job_id' => $jobId,
                    'text' => $validated['text'],
                    'voice_id' => $validated['voice_id'] ?? 'salem_podcast',
                    'speed' => (float) ($validated['speed'] ?? 1.0),
                ]);

            if (! $created->successful()) {
                return back()
                    ->withInput()
                    ->with('error', 'فشل بدء توليد الصوت: '.$created->status());
            }

            // متابعة حالة المهمة كل ٣ ثواني لين تكتمل أو تفشل، بحد أقصى ٨ دقايق.
            $deadline = now()->addMinutes(8);

            do {
                sleep(3);

                $status = Http::withHeaders($this->authHeaders($apiKey))
                    ->timeout(10)
                    ->get("{$baseUrl}/api/v1/jobs/speak/{$jobId}");

                if (! $status->successful()) {
                    return back()
                        ->withInput()
                        ->with('error', 'تعذّر متابعة حالة التوليد: '.$status->status());
                }

                $jobStatus = $status->json('status');

                if ($jobStatus === 'failed') {
                    return back()
                        ->withInput()
                        ->with('error', 'فشل توليد الصوت: '.($status->json('error') ?? 'خطأ غير معروف'));
                }
            } while ($jobStatus !== 'completed' && now()->lessThan($deadline));

            if ($jobStatus !== 'completed') {
                return back()
                    ->withInput()
                    ->with('error', 'التوليد يأخذ وقت أطول من المتوقع. حاول بنص أقصر أو أعد المحاولة لاحقًا.');
            }

            $audio = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(30)
                ->get("{$baseUrl}/api/v1/jobs/speak/{$jobId}/audio");

            if (! $audio->successful()) {
                return back()
                    ->withInput()
                    ->with('error', 'اكتمل التوليد لكن تعذّر تحميل الملف الصوتي.');
            }
        } catch (\Throwable $e) {
            Log::warning('SoundInk generation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'تعذر الاتصال بخدمة توليد الصوت.');
        }

        $filename = 'voice-studio/'.now()->format('Ymd_His').'_'.Str::random(6).'.wav';
        Storage::disk('public')->put($filename, $audio->body());

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
        $apiKey = Setting::get('soundink_api_key', (string) config('services.soundink.key'));

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

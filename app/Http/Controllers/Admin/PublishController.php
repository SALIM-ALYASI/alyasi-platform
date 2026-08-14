<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublishJob;
use Illuminate\Http\JsonResponse;
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

        foreach ($jobs as $job) {
            if ($job->source === 'smart_content' && $this->needsRefresh($job)) {
                $this->refreshFromSmartContent($job);
            }
        }

        return view('admin.publish.index', compact('jobs'));
    }

    /**
     * هل هذا الإعلان لسا فيه شيء متحرك يستاهل نجيب له آخر حالة من smart-content؟
     * (لسا يتولّد، أو فيه منصة مجدولة/قيد النشر/تُعاد محاولتها).
     */
    private function needsRefresh(PublishJob $job): bool
    {
        if (! in_array($job->status, ['ready', 'failed'], true)) {
            return true;
        }

        foreach ($job->platforms ?? [] as $info) {
            if (in_array($info['status'] ?? null, ['scheduled', 'publishing', 'retrying'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * يجيب أحدث حالة توليد + حالة كل منصة من smart-content ويحفظها محلياً.
     */
    private function refreshFromSmartContent(PublishJob $job): void
    {
        $baseUrl = rtrim((string) config('services.smart_content.url'), '/');
        $apiKey = config('services.smart_content.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(15)
                ->get("{$baseUrl}/jobs/{$job->job_id}");
        } catch (\Throwable $e) {
            return;
        }

        if (! $response->successful()) {
            return;
        }

        $data = $response->json();

        $job->update([
            'status' => $data['status'] ?? $job->status,
            'title' => $data['copy']['title'] ?? $job->title,
            'smart_content_data' => $data,
            'platforms' => $data['platforms'] ?? $job->platforms,
        ]);
    }

    /**
     * توليد إعلان (صورة مربعة + Story + فيديو 15 ثانية) بالذكاء الاصطناعي من نص مختصر،
     * عبر خدمة smart-content المستقلة. لا يُنشر شيء تلقائياً — فقط توليد للمراجعة.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'min:5', 'max:1000'],
            'contact' => ['nullable', 'string', 'max:100'],
            'media_type' => ['required', 'in:image,video,both'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:instagram,facebook,linkedin,youtube,telegram'],
        ], [
            'text.required' => 'يرجى كتابة نص مختصر عن الإعلان.',
            'text.min' => 'النص قصير جداً.',
            'platforms.required' => 'يرجى اختيار منصة واحدة على الأقل.',
            'platforms.min' => 'يرجى اختيار منصة واحدة على الأقل.',
        ]);

        $baseUrl = rtrim((string) config('services.smart_content.url'), '/');
        $apiKey = config('services.smart_content.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(30)
                ->post("{$baseUrl}/create-ad", [
                    'text' => $validated['text'],
                    'contact' => $validated['contact'] ?? 'alyasi.dev',
                    'media_type' => $validated['media_type'],
                    'platforms' => $validated['platforms'],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Smart Content API unreachable', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'تعذر الاتصال بخدمة توليد الإعلانات. تأكد من أنها شغّالة.');
        }

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'فشل بدء توليد الإعلان: '.($response->json('error') ?? $response->status()));
        }

        $data = $response->json();

        PublishJob::query()->create([
            'job_id' => $data['job_id'],
            'source' => 'smart_content',
            'status' => $data['status'] ?? 'queued',
            'title' => null,
            'text' => $validated['text'],
            'platforms' => [],
            'smart_content_data' => ['platforms_requested' => $data['platforms_requested'] ?? []],
        ]);

        return redirect()
            ->route('admin.publish.index')
            ->with('success', 'بدأ توليد الإعلان. عادة يأخذ دقيقة أو أقل — أعد تحميل الصفحة لمتابعة الحالة.');
    }

    /**
     * جلب أحدث حالة لمهمة توليد من smart-content وتحديثها محلياً (يُستدعى عبر JS اختياري
     * أو عند تحميل الصفحة). يُرجع JSON فقط.
     */
    public function status(string $job): JsonResponse
    {
        $publishJob = PublishJob::query()->where('job_id', $job)->firstOrFail();

        if ($publishJob->source === 'smart_content' && $this->needsRefresh($publishJob)) {
            $this->refreshFromSmartContent($publishJob);
        }

        return response()->json([
            'status' => $publishJob->status,
            'platforms' => $publishJob->platforms,
            'smart_content_data' => $publishJob->smart_content_data,
        ]);
    }

    /**
     * اعتماد إعلان مُولَّد ونشره فعلياً على المنصات المختارة (بعد المراجعة اليدوية فقط).
     */
    public function approve(Request $request, string $job): RedirectResponse
    {
        $validated = $request->validate([
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:instagram,facebook,linkedin,youtube,telegram'],
        ], [
            'platforms.required' => 'يرجى اختيار منصة واحدة على الأقل.',
            'platforms.min' => 'يرجى اختيار منصة واحدة على الأقل.',
        ]);

        $publishJob = PublishJob::query()->where('job_id', $job)->firstOrFail();

        if ($publishJob->status !== 'ready') {
            return back()->with('error', 'الإعلان لسا ما جاهز للنشر (الحالة الحالية: '.$publishJob->status.').');
        }

        $baseUrl = rtrim((string) config('services.smart_content.url'), '/');
        $apiKey = config('services.smart_content.key');

        try {
            // نوع الوسائط لكل منصة محسوم خلاص وقت التوليد — هنا فقط نأكد أي منها ينشر الآن.
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(180)
                ->post("{$baseUrl}/publish", [
                    'job_id' => $job,
                    'platforms' => $validated['platforms'],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Smart Content publish unreachable', ['error' => $e->getMessage()]);

            return back()->with('error', 'تعذر الاتصال بخدمة النشر.');
        }

        if (! $response->successful()) {
            return back()->with('error', 'فشل النشر: '.($response->json('error') ?? $response->status()));
        }

        // النشر ما يصير فورياً — كل منصة تدخل طابور مجدول بتوقيتها المناسب (بوت النشر
        // نفسه قرر التوقيت ونوع الوسائط المتاح فعلياً لكل منصة).
        $publishJob->update(['platforms' => $response->json('platforms', $publishJob->platforms ?? [])]);

        return redirect()
            ->route('admin.publish.index')
            ->with('success', 'تم اعتماد الإعلان — بوت النشر بيوزّعه على كل منصة بالتوقيت الأنسب لها (قد يأخذ حتى 24 ساعة). راجع الحالة بالأسفل.');
    }

    /**
     * ترويسة المصادقة مع خدمة النشر.
     */
    private function authHeaders(?string $apiKey): array
    {
        return $apiKey ? ['x-api-key' => $apiKey] : [];
    }
}

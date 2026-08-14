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

        return view('admin.publish.index', compact('jobs'));
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
        ], [
            'text.required' => 'يرجى كتابة نص مختصر عن الإعلان.',
            'text.min' => 'النص قصير جداً.',
        ]);

        $baseUrl = rtrim((string) config('services.smart_content.url'), '/');
        $apiKey = config('services.smart_content.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(30)
                ->post("{$baseUrl}/create-ad", [
                    'text' => $validated['text'],
                    'contact' => $validated['contact'] ?? 'alyasi.dev',
                    'publish' => false,
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

        if ($publishJob->source !== 'smart_content' || in_array($publishJob->status, ['ready', 'failed'], true)) {
            return response()->json(['status' => $publishJob->status, 'smart_content_data' => $publishJob->smart_content_data]);
        }

        $baseUrl = rtrim((string) config('services.smart_content.url'), '/');
        $apiKey = config('services.smart_content.key');

        try {
            $response = Http::withHeaders($this->authHeaders($apiKey))
                ->timeout(15)
                ->get("{$baseUrl}/jobs/{$job}");
        } catch (\Throwable $e) {
            return response()->json(['status' => $publishJob->status, 'error' => 'تعذر الاتصال بالخدمة']);
        }

        if ($response->successful()) {
            $data = $response->json();

            $publishJob->update([
                'status' => $data['status'] ?? $publishJob->status,
                'title' => $data['copy']['title'] ?? $publishJob->title,
                'smart_content_data' => $data,
            ]);
        }

        return response()->json(['status' => $publishJob->status, 'smart_content_data' => $publishJob->smart_content_data]);
    }

    /**
     * اعتماد إعلان مُولَّد ونشره فعلياً على المنصات المختارة (بعد المراجعة اليدوية فقط).
     */
    public function approve(Request $request, string $job): RedirectResponse
    {
        $validated = $request->validate([
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:instagram,linkedin,youtube,telegram'],
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

        $results = $response->json('platforms', []);
        $mapped = $publishJob->platforms ?? [];

        foreach ($results as $platform => $result) {
            $mapped[$platform] = [
                'status' => $result['status'] === 'success' ? 'done' : 'failed',
                'result' => ['url' => $result['url'] ?? null],
                'error' => $result['error'] ?? ($result['status'] === 'skipped' ? 'غير مُفعّل لهذه المنصة' : null),
            ];
        }

        $publishJob->update(['platforms' => $mapped]);

        return redirect()
            ->route('admin.publish.index')
            ->with('success', 'تم إرسال طلب النشر. راجع حالة كل منصة بالأسفل.');
    }

    /**
     * ترويسة المصادقة مع خدمة النشر.
     */
    private function authHeaders(?string $apiKey): array
    {
        return $apiKey ? ['x-api-key' => $apiKey] : [];
    }
}

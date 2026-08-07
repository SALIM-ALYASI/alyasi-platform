<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PronunciationQueueController extends Controller
{
    /**
     * عرض قائمة اقتراحات تصحيح النطق المستخرجة من تعليقات يوتيوب، بانتظار
     * اعتماد المدير (بديل ويب لأزرار القبول/الرفض بتلجرام).
     */
    public function index(): View
    {
        $items = [];
        $error = null;

        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(15)
                ->get("{$this->baseUrl()}/pronunciation-queue");

            if ($response->successful()) {
                $items = $response->json('items', []);
            } else {
                $error = 'تعذّر جلب القائمة من السيرفر.';
            }
        } catch (\Throwable $e) {
            Log::warning('تعذّر الاتصال بقائمة انتظار النطق.', ['error' => $e->getMessage()]);
            $error = 'تعذّر الاتصال بخدمة النشر. تأكد من أنها شغّالة.';
        }

        return view('admin.pronunciation-queue.index', compact('items', 'error'));
    }

    /**
     * قبول الاقتراح وإضافته لقاموس نطق SoundInk الرسمي.
     */
    public function accept(string $id): RedirectResponse
    {
        return $this->act($id, 'accept', 'تمت الإضافة لقاموس النطق.');
    }

    /**
     * رفض الاقتراح وحذفه من قائمة الانتظار.
     */
    public function reject(string $id): RedirectResponse
    {
        return $this->act($id, 'reject', 'تم رفض الاقتراح.');
    }

    private function act(string $id, string $action, string $successMessage): RedirectResponse
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(15)
                ->post("{$this->baseUrl()}/pronunciation-queue/{$id}/{$action}");
        } catch (\Throwable $e) {
            Log::warning('تعذّر تنفيذ إجراء بقائمة انتظار النطق.', ['error' => $e->getMessage()]);

            return back()->with('error', 'تعذّر الاتصال بخدمة النشر.');
        }

        if (! $response->successful()) {
            return back()->with('error', 'فشل تنفيذ الإجراء: '.($response->json('error') ?? $response->status()));
        }

        return back()->with('success', $successMessage);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.publish.url'), '/');
    }

    private function authHeaders(): array
    {
        $key = config('services.publish.key');

        return $key ? ['x-api-key' => $key] : [];
    }
}

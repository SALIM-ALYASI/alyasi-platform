<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    public function index(): View
    {
        $baseUrl = rtrim((string) config('services.whatsapp_notify.base_url'), '/');
        $apiKey = (string) config('services.whatsapp_notify.api_key');
        $configured = $baseUrl !== '' && $apiKey !== '';

        $status = null;
        $qrImage = null;

        if ($configured) {
            try {
                $response = Http::timeout(5)->get($baseUrl.'/status');
                $status = $response->json();
            } catch (\Throwable) {
                $status = null;
            }

            if (! ($status['connected'] ?? false)) {
                try {
                    $qrResponse = Http::withHeaders(['x-api-key' => $apiKey])
                        ->timeout(5)
                        ->get($baseUrl.'/qr');

                    if ($qrResponse->successful()) {
                        $qrImage = 'data:image/png;base64,'.base64_encode($qrResponse->body());
                    }
                } catch (\Throwable) {
                    $qrImage = null;
                }
            }
        }

        return view('admin.whatsapp.index', [
            'baseUrl' => $baseUrl,
            'configured' => $configured,
            'status' => $status,
            'qrImage' => $qrImage,
        ]);
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $baseUrl = rtrim((string) config('services.whatsapp_notify.base_url'), '/');
        $apiKey = (string) config('services.whatsapp_notify.api_key');

        if ($baseUrl === '' || $apiKey === '') {
            return back()->with('error', 'إعدادات ربط واتساب غير مكتملة في ملف .env.');
        }

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(10)
                ->post($baseUrl.'/send-message', $validated);

            if ($response->successful() && ($response->json('success') ?? false)) {
                return back()->with('success', 'تم إرسال الرسالة بنجاح.');
            }

            return back()->with('error', 'فشل الإرسال: '.($response->json('error') ?? $response->body()));
        } catch (\Throwable $e) {
            return back()->with('error', 'تعذّر الاتصال بخدمة واتساب: '.$e->getMessage());
        }
    }
}

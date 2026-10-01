<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MetaWhatsAppWebhookController extends Controller
{
    /**
     * التحقق من WhatsApp Webhook من ميتا (نفس نمط InstagramWebhookController).
     */
    public function verify(Request $request): Response|JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = (string) config('services.meta_whatsapp.webhook_verify_token');

        if (
            $mode === 'subscribe' &&
            $verifyToken !== '' &&
            hash_equals($verifyToken, (string) $token)
        ) {
            Log::info('Meta WhatsApp Webhook verified successfully.');

            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta WhatsApp Webhook verification failed.', [
            'mode' => $mode,
            'has_token' => !empty($token),
        ]);

        return response()->json([
            'message' => 'Meta WhatsApp webhook verification failed.',
        ], 403);
    }

    /**
     * استقبال أحداث واتساب (رسائل، حالات تسليم...).
     *
     * ما فيه منطق أعمال بعد -- بس نستقبل ونسجّل ونخزّن للتشخيص، لين يتحدد
     * الغرض الفعلي من هذا الرقم.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Meta WhatsApp Webhook received.', [
            'object' => $payload['object'] ?? null,
            'entries' => count($payload['entry'] ?? []),
        ]);

        Storage::disk('local')->put(
            'meta-whatsapp/last_webhook.json',
            json_encode([
                'received_at' => now()->toIso8601String(),
                'payload' => $payload,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                foreach ($value['messages'] ?? [] as $message) {
                    $safeId = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($message['id'] ?? uniqid()));

                    Storage::disk('local')->put(
                        sprintf('meta-whatsapp/queue/%s_%s.json', now()->format('Ymd_His_u'), $safeId),
                        json_encode([
                            'received_at' => now()->toIso8601String(),
                            'from' => $message['from'] ?? null,
                            'text' => $message['text']['body'] ?? null,
                            'type' => $message['type'] ?? null,
                            'raw' => $message,
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    );
                }
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    /**
     * التحقق من Facebook Webhook.
     *
     * Meta يرسل:
     * hub.mode
     * hub.verify_token
     * hub.challenge
     */
    public function verify(Request $request): Response|JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = (string) config('services.facebook.webhook_verify_token');

        if (
            $mode === 'subscribe' &&
            $verifyToken !== '' &&
            hash_equals($verifyToken, (string) $token)
        ) {
            Log::info('Facebook Webhook verified successfully.');

            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Facebook Webhook verification failed.', [
            'mode' => $mode,
            'has_token' => !empty($token),
        ]);

        return response()->json([
            'message' => 'Facebook webhook verification failed.',
        ], 403);
    }

    /**
     * استقبال أحداث Facebook Page.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Facebook Webhook received.', [
            'object' => $payload['object'] ?? null,
            'entries' => count($payload['entry'] ?? []),
        ]);

        // Meta يتوقع استجابة سريعة 200.
        // لاحقاً نضيف هنا معالجة التعليقات وإرسالها إلى Manager Bot.
        return response()->json([
            'status' => 'EVENT_RECEIVED',
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class InstagramWebhookController extends Controller
{
    /**
     * التحقق من Instagram Webhook من Meta.
     */
    public function verify(Request $request): Response|JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = (string) config(
            'services.instagram.webhook_verify_token'
        );

        if (
            $mode === 'subscribe' &&
            $verifyToken !== '' &&
            hash_equals($verifyToken, (string) $token)
        ) {
            Log::info('Instagram Webhook verified successfully.');

            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Instagram Webhook verification failed.', [
            'mode' => $mode,
            'has_token' => !empty($token),
        ]);

        return response()->json([
            'message' => 'Instagram webhook verification failed.',
        ], 403);
    }

    /**
     * استقبال أحداث Instagram.
     *
     * لاحقاً نعالج هنا:
     * - Comments
     * - Mentions
     * - Messages / DM
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Instagram Webhook received.', [
            'object' => $payload['object'] ?? null,
            'entries' => count($payload['entry'] ?? []),
        ]);

        // Meta يحتاج استجابة سريعة 200.
        // المعالجة الفعلية للتعليقات والرسائل سنضيفها لاحقاً.

        return response()->json([
            'status' => 'EVENT_RECEIVED',
        ], 200);
    }
}

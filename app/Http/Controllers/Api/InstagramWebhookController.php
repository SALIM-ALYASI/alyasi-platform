<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Instagram Webhook received.', [
            'object' => $payload['object'] ?? null,
            'entries' => count($payload['entry'] ?? []),
        ]);

        /*
        |--------------------------------------------------------------------------
        | حفظ نسخة من آخر Webhook
        |--------------------------------------------------------------------------
        */

        Storage::disk('local')->put(
            'instagram/last_webhook.json',
            json_encode([
                'received_at' => now()->toIso8601String(),
                'payload' => $payload,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        /*
        |--------------------------------------------------------------------------
        | معالجة Events
        |--------------------------------------------------------------------------
        */

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {

                $field = $change['field'] ?? null;
                $value = $change['value'] ?? [];

                /*
                |--------------------------------------------------------------------------
                | Comments
                |--------------------------------------------------------------------------
                */

                if ($field === 'comments') {
                    $comment = [
                        'received_at' => now()->toIso8601String(),

                        'instagram_account_id' => $entry['id'] ?? null,

                        'comment_id' => $value['id'] ?? null,

                        'parent_id' => $value['parent_id'] ?? null,

                        'username' => $value['from']['username'] ?? null,

                        'user_id' => $value['from']['id'] ?? null,

                        'self_ig_scoped_id' =>
                            $value['from']['self_ig_scoped_id'] ?? null,

                        'text' => $value['text'] ?? null,

                        'media_id' => $value['media']['id'] ?? null,

                        'media_product_type' =>
                            $value['media']['media_product_type'] ?? null,
                    ];

                    Storage::disk('local')->put(
                        'instagram/last_comment.json',
                        json_encode(
                            $comment,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                        )
                    );

                    Log::info('Instagram comment received.', [
                        'comment_id' => $comment['comment_id'],
                        'username' => $comment['username'],
                        'media_id' => $comment['media_id'],
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Meta يحتاج HTTP 200 بسرعة
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => 'EVENT_RECEIVED',
        ], 200);
    }
}
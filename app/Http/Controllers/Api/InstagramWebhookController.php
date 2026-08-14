<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        | حفظ آخر Webhook للتشخيص
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
        | معالجة الأحداث
        |--------------------------------------------------------------------------
        */

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {

                if (($change['field'] ?? null) !== 'comments') {
                    continue;
                }

                $value = $change['value'] ?? [];

                $commentId = $value['id'] ?? null;
                $text = trim((string) ($value['text'] ?? ''));

                /*
                |--------------------------------------------------------------------------
                | تجاهل Event ناقص
                |--------------------------------------------------------------------------
                */

                if (!$commentId || $text === '') {
                    Log::warning('Instagram comment ignored: incomplete event.', [
                        'has_comment_id' => !empty($commentId),
                        'has_text' => $text !== '',
                    ]);

                    continue;
                }

                $comment = [
                    'received_at' => now()->toIso8601String(),

                    'instagram_account_id' => $entry['id'] ?? null,

                    'comment_id' => $commentId,

                    'parent_id' => $value['parent_id'] ?? null,

                    'username' => $value['from']['username'] ?? null,

                    'user_id' => $value['from']['id'] ?? null,

                    'self_ig_scoped_id' =>
                        $value['from']['self_ig_scoped_id'] ?? null,

                    'text' => $text,

                    'media_id' => $value['media']['id'] ?? null,

                    'media_product_type' =>
                        $value['media']['media_product_type'] ?? null,

                    'status' => 'pending',
                ];

                /*
                |--------------------------------------------------------------------------
                | آخر تعليق - للتشخيص فقط
                |--------------------------------------------------------------------------
                */

                Storage::disk('local')->put(
                    'instagram/last_comment.json',
                    json_encode(
                        $comment,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    )
                );

                /*
                |--------------------------------------------------------------------------
                | Queue للبوت
                |
                | كل تعليق له ملف مستقل حتى لا يضيع تعليق إذا وصل تعليقان معًا.
                |--------------------------------------------------------------------------
                */

                $safeCommentId = preg_replace(
                    '/[^A-Za-z0-9_-]/',
                    '_',
                    (string) $commentId
                );

                $queueFile = sprintf(
                    'instagram/queue/%s_%s.json',
                    now()->format('Ymd_His_u'),
                    $safeCommentId ?: Str::uuid()->toString()
                );

                Storage::disk('local')->put(
                    $queueFile,
                    json_encode(
                        $comment,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    )
                );

                Log::info('Instagram comment queued.', [
                    'comment_id' => $comment['comment_id'],
                    'username' => $comment['username'],
                    'media_id' => $comment['media_id'],
                    'queue_file' => $queueFile,
                ]);
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
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * حلقة الوصل مع وكيل الاكتشاف (روتين Grok كل ساعة):
 *  - store: الوكيل يرسل رابط خبر مميز (رمز news_suggester).
 *  - pending/ack: بوت الأخبار يسحب الروابط الجديدة ويرجّع النتيجة (رمز news_bot).
 * الرابط بس يدخل الأنابيب؛ العنوان والملخص من الوكيل للمراجعة وما ينشرون.
 */
class NewsSuggestionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required', 'string', 'url:https,http', 'max:2000'],
            'title' => ['nullable', 'string', 'max:500'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'why_important' => ['nullable', 'string', 'max:1000'],
            'source_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 'invalid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $hash = NewsSuggestion::hashUrl($data['url']);

        // منشور عندنا أصلًا (حتى لو مدموج أو محذوف) -- ما يحتاج يدخل الأنابيب.
        $published = NewsArticle::withTrashed()->where('source_url', $data['url'])->exists()
            || NewsArticle::withTrashed()
                ->where('created_at', '>=', now()->subDays(60))
                ->whereNotNull('source_url')
                ->pluck('source_url')
                ->contains(fn ($url) => NewsSuggestion::hashUrl($url) === $hash);

        if ($published) {
            return response()->json(['success' => true, 'status' => 'already_published'], 200);
        }

        $existing = NewsSuggestion::query()->where('url_hash', $hash)->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'status' => 'duplicate',
                'id' => $existing->id,
            ], 200);
        }

        $suggestion = NewsSuggestion::query()->create([
            ...$data,
            'url_hash' => $hash,
            'suggested_by' => 'grok',
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'status' => 'queued',
            'id' => $suggestion->id,
        ], 202);
    }

    public function pending(): JsonResponse
    {
        $items = NewsSuggestion::query()
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit(20)
            ->get(['id', 'url', 'title', 'source_name', 'created_at']);

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function ack(Request $request, NewsSuggestion $newsSuggestion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['fetched', 'duplicate', 'failed'])],
            'note' => ['nullable', 'string', 'max:500'],
            'candidate_id' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $newsSuggestion->update([
            'status' => $data['status'],
            'status_note' => $data['note'] ?? null,
            'candidate_id' => $data['candidate_id'] ?? null,
            'processed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}

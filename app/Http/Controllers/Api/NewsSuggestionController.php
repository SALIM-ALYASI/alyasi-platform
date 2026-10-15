<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOmanPulseSuggestion;
use App\Models\NewsArticle;
use App\Models\NewsSuggestion;
use App\Models\OmanPulseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * حلقة الوصل مع وكلاء الاكتشاف (Grok، Manus):
 *  - store: الوكيل يرسل رابط خبر مميز أو قائمة (رمز خاص بكل وكيل).
 *  - pending/ack: بوت الأخبار يسحب الروابط الجديدة ويرجّع النتيجة (رمز news_bot).
 * الرابط بس يدخل الأنابيب؛ العنوان والملخص من الوكيل للمراجعة وما ينشرون.
 */
class NewsSuggestionController extends Controller
{
    /**
     * خبر واحد (Grok) أو قائمة {"items": [...]} حتى 10 (Manus) -- نفس المنطق
     * لكل خبر، والرد يفصّل نتيجة كل واحد. الوكيل يُعرف من رمزه (الـ middleware).
     */
    public function store(Request $request): JsonResponse
    {
        $agent = (string) $request->attributes->get('news_suggester', 'grok');

        if (! $request->has('items')) {
            $result = $this->ingest($request->all(), $agent);

            return response()->json(
                ['success' => $result['status'] !== 'invalid', ...$result],
                match ($result['status']) {
                    'invalid' => 422,
                    'queued' => 202,
                    default => 200,
                }
            );
        }

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1', 'max:10'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 'invalid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $results = [];
        foreach (array_values($request->input('items')) as $index => $item) {
            $results[] = ['index' => $index, ...$this->ingest(is_array($item) ? $item : [], $agent)];
        }

        $counts = collect($results)->countBy('status');

        return response()->json([
            'success' => true,
            'accepted' => true,
            'received' => count($results),
            'queued' => $counts->get('queued', 0),
            'duplicates' => $counts->get('duplicate', 0),
            'already_published' => $counts->get('already_published', 0),
            'invalid' => $counts->get('invalid', 0),
            'results' => $results,
        ]);
    }

    /**
     * @return array{status: string, id?: int, url?: string, errors?: mixed}
     */
    private function ingest(array $input, string $agent): array
    {
        $validator = Validator::make($input, [
            'url' => ['required', 'string', 'url:https,http', 'max:2000'],
            'title' => ['nullable', 'string', 'max:500'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'why_important' => ['nullable', 'string', 'max:1000'],
            'source_name' => ['nullable', 'string', 'max:255'],
            // رمز الدولة (ISO، مثل OM أو AE)؛ GCC/MENA للخبر الإقليمي العام.
            'country' => ['nullable', 'string', 'regex:/^([A-Za-z]{2}|GCC|MENA)$/'],
            'published_at' => ['nullable', 'date'],
            'importance' => ['nullable', 'string', 'max:20'],
            // «نبض عُمان»: بيان رسمي يُنشر بنصّه الأصلي (ما يمر على البوت والذكاء الاصطناعي).
            'section' => ['nullable', Rule::in(['oman-pulse'])],
            'category' => ['nullable', Rule::in(array_keys(OmanPulseItem::TAGS))],
        ]);

        if ($validator->fails()) {
            return ['status' => 'invalid', 'url' => $input['url'] ?? null, 'errors' => $validator->errors()];
        }

        $data = $validator->validated();
        $hash = NewsSuggestion::hashUrl($data['url']);

        if (isset($data['country'])) {
            // الإقليمي العام ينحفظ XG (خليجي) أو XM (المنطقة) ليناسب عمود من حرفين.
            $data['country'] = match (strtoupper($data['country'])) {
                'GCC' => 'XG',
                'MENA' => 'XM',
                default => strtoupper($data['country']),
            };
        }

        // منشور عندنا أصلًا (حتى لو مدموج أو محذوف) -- ما يحتاج يدخل الأنابيب.
        $isPulse = ($data['section'] ?? null) === 'oman-pulse';

        $published = OmanPulseItem::query()->where('source_url_hash', $hash)->exists()
            || NewsArticle::withTrashed()->where('source_url', $data['url'])->exists()
            || NewsArticle::withTrashed()
                ->where('created_at', '>=', now()->subDays(60))
                ->whereNotNull('source_url')
                ->pluck('source_url')
                ->contains(fn ($url) => NewsSuggestion::hashUrl($url) === $hash);

        if ($published) {
            return ['status' => 'already_published', 'url' => $data['url']];
        }

        $existing = NewsSuggestion::query()->where('url_hash', $hash)->first();

        if ($existing) {
            return ['status' => 'duplicate', 'id' => $existing->id, 'url' => $data['url']];
        }

        $suggestion = NewsSuggestion::query()->create([
            'url' => $data['url'],
            'url_hash' => $hash,
            'title' => $data['title'] ?? null,
            'summary' => $data['summary'] ?? null,
            'why_important' => $data['why_important'] ?? null,
            'source_name' => $data['source_name'] ?? null,
            'country' => $data['country'] ?? null,
            'source_published_at' => $data['published_at'] ?? null,
            'importance' => $data['importance'] ?? null,
            'suggested_by' => $agent,
            'section' => $isPulse ? 'oman-pulse' : null,
            'category' => $data['category'] ?? null,
            'status' => 'pending',
        ]);

        if ($isPulse) {
            ProcessOmanPulseSuggestion::dispatchAfterResponse($suggestion->id);
        }

        return ['status' => 'queued', 'id' => $suggestion->id, 'url' => $data['url']];
    }

    public function pending(): JsonResponse
    {
        $items = NewsSuggestion::query()
            ->where('status', 'pending')
            ->whereNull('section') // نبض عُمان ما يمر على البوت
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

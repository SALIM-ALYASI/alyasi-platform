<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceLead;
use App\Support\Leads\LeadScreener;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Grok يرسل طلبات خدمات منشورة علنًا (مطلوب مصمم/مونتير/موقع) في عُمان والخليج.
 * السيرفر يفرزها (LeadScreener) ويرسل المقبول لواتساب سالم برسالة وحدة لكل دفعة.
 * نفس رمز اقتراح الأخبار (news-suggester.auth): يقدر يضيف طلبات بس.
 */
class ServiceLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $agent = (string) $request->attributes->get('news_suggester', 'grok');
        $items = $request->has('items') ? $request->input('items') : [$request->all()];

        if (! is_array($items) || $items === [] || count($items) > 20) {
            return response()->json(['success' => false, 'message' => 'أرسل طلبًا واحدًا أو items من 1 إلى 20.'], 422);
        }

        $results = [];
        $accepted = collect();

        foreach (array_values($items) as $index => $item) {
            $result = $this->ingest(is_array($item) ? $item : [], $agent);
            $results[] = ['index' => $index, 'status' => $result['status'], 'reason' => $result['reason'] ?? null, 'errors' => $result['errors'] ?? null];

            if ($result['status'] === 'accepted') {
                $accepted->push($result['lead']);
            }
        }

        // ما فيه تنبيه لكل دفعة: أفضل 5 بالأسبوع توصل برسالة وحدة (leads:weekly-top).

        $counts = collect($results)->countBy('status');

        return response()->json([
            'success' => true,
            'received' => count($results),
            'accepted' => $counts->get('accepted', 0),
            'rejected' => $counts->get('rejected', 0),
            'duplicates' => $counts->get('duplicate', 0),
            'invalid' => $counts->get('invalid', 0),
            'results' => $results,
        ]);
    }

    /**
     * @return array{status: string, reason?: ?string, errors?: mixed, lead?: ServiceLead}
     */
    private function ingest(array $input, string $agent): array
    {
        $input['country'] = filled($input['country'] ?? null) ? strtoupper(trim((string) $input['country'])) : null;
        $input['platform'] = strtolower(trim((string) ($input['platform'] ?? 'x')));
        $input['scope'] = ($input['scope'] ?? 'gcc') === 'marketplace' ? 'marketplace' : 'gcc';

        $validator = Validator::make($input, [
            'url' => ['required', 'url:http,https', 'max:500'],
            'platform' => ['required', Rule::in(ServiceLead::PLATFORMS)],
            'text' => ['required', 'string', 'min:10', 'max:3000'],
            'summary' => ['nullable', 'string', 'max:400'],
            'category' => ['required', Rule::in(array_keys(ServiceLead::CATEGORIES))],
            'scope' => ['required', Rule::in(array_keys(ServiceLead::SCOPES))],
            'country' => [Rule::requiredIf($input['scope'] === 'gcc'), 'nullable', 'string', 'size:2'],
            'budget_usd' => [Rule::requiredIf($input['scope'] === 'marketplace'), 'nullable', 'integer', 'min:0', 'max:1000000'],
            'city' => ['nullable', 'string', 'max:80'],
            'author' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:120'],
            'deadline' => ['nullable', 'string', 'max:120'],
            'posted_at' => ['nullable', 'date'],
            'post_type' => ['nullable', Rule::in(['request', 'offer'])],
        ]);

        if ($validator->fails()) {
            return ['status' => 'invalid', 'errors' => $validator->errors()];
        }

        $data = $validator->validated();

        if (ServiceLead::query()->where('url_key', LeadScreener::urlKey($data['url']))->exists()) {
            return ['status' => 'duplicate'];
        }

        $screen = LeadScreener::screen($data);

        $lead = ServiceLead::query()->create([
            'agent' => $agent,
            'scope' => $data['scope'],
            'platform' => $data['platform'],
            'url' => $data['url'],
            'url_key' => $screen['url_key'],
            'text_hash' => $screen['text_hash'],
            'author' => $data['author'] ?? null,
            'text' => $data['text'],
            'summary' => $data['summary'] ?? null,
            'category' => $data['category'],
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'budget' => $data['budget'] ?? null,
            'budget_usd' => $data['budget_usd'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'posted_at' => $data['posted_at'] ?? null,
            'score' => $screen['score'],
            'status' => $screen['status'],
            'reject_reason' => $screen['reason'],
        ]);

        return $screen['status'] === 'new'
            ? ['status' => 'accepted', 'lead' => $lead]
            : ['status' => 'rejected', 'reason' => $screen['reason']];
    }
}

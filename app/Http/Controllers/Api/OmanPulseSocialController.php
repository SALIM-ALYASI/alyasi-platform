<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsCard;
use App\Models\OmanPulseItem;
use Illuminate\Http\JsonResponse;

/**
 * أخبار «نبض عُمان» اللي تحتاج بطاقة نشر (سيرفر البيت يسوّيها وينشرها): المنشورة
 * خلال آخر 24 ساعة وما لها بطاقة بعد (news_cards بنوع oman_pulse). البطاقة نفسها
 * علامة «تم» -- وتروح لـ Grok لمنصة X من نفس المسار.
 */
class OmanPulseSocialController extends Controller
{
    public function pending(): JsonResponse
    {
        $carded = NewsCard::query()->where('source_type', 'oman_pulse')->pluck('source_id');

        $items = OmanPulseItem::query()
            ->published()
            ->where('created_at', '>=', now()->subHours(24))
            ->whereNotIn('id', $carded)
            ->whereNotNull('image')
            ->orderBy('id')
            ->take(5)
            ->get();

        return response()->json(['data' => $items->map(fn (OmanPulseItem $item) => [
            'id' => $item->id,
            'title' => $item->title,
            'summary' => self::summary((string) $item->lead),
            'image_url' => asset($item->image),
            'source_name' => $item->source_name,
            'source_url' => $item->source_url,
            'page_url' => route('news.oman.show', $item->id),
            'published_at' => ($item->source_published_at ?? $item->created_at)?->toIso8601String(),
            'tag' => $item->tag,
            'tag_label' => OmanPulseItem::TAGS[$item->tag] ?? 'تقنية',
        ])->values()]);
    }

    /**
     * ملخص البطاقة: بدون سطر «مسقط في … /العُمانية/»، وينتهي بجملة كاملة (≤ 260 حرف).
     */
    public static function summary(string $lead, int $max = 260): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', preg_replace('#^.*?/العُ?مانية/\s*#u', '', $lead)));

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max);
        $end = max(mb_strrpos($cut, '.') ?: 0, mb_strrpos($cut, '،') ?: 0);

        return $end > $max * 0.5
            ? rtrim(mb_substr($cut, 0, $end), '،').'.'
            : rtrim(mb_substr($cut, 0, mb_strrpos($cut, ' ') ?: $max)).'…';
    }
}

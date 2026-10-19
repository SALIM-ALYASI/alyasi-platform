<?php

namespace App\Support\Leads;

use App\Models\ServiceLead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * فرز طلبات الخدمات اللي يرسلها Grok -- قواعد ثابتة بدون ذكاء اصطناعي:
 * الخليج بس، طلب مب عرض، حديث، مب مكرر. وترتيب بالأولوية (عُمان، ميزانية،
 * استعجال، حداثة).
 */
final class LeadScreener
{
    /** عبارات من يعرض خدماته (نرفضها لو ما فيها عبارة طلب). */
    private const OFFER_PATTERNS = [
        'نقدم', 'نقدّم', 'نوفر', 'نوفّر', 'متوفر', 'متوفرة', 'خدماتنا', 'احجز', 'اطلب الآن', 'اطلبها الآن',
        'بأسعار', 'أسعار مناسبة', 'عرض خاص', 'خصم', 'للتواصل والطلب', 'تصاميمنا', 'أعمالنا',
        'for hire', 'available for', 'dm for', 'dm me for', 'our services', 'we offer', 'order now', 'discount',
        'portfolio in bio', 'open for commissions',
    ];

    /** عبارات طلب واضحة. */
    private const REQUEST_PATTERNS = [
        'مطلوب', 'ابحث عن', 'أبحث عن', 'نبحث عن', 'ابي ', 'أبي ', 'ابغى', 'أبغى', 'ابغي', 'نحتاج', 'احتاج', 'أحتاج',
        'من يعرف', 'تعرفون', 'أحد يعرف', 'احد يعرف', 'تنصحوني', 'يسوي لي', 'يصمم لي', 'يصمم لنا',
        'looking for', 'hiring', 'need a', 'need an', 'we need', 'i need', 'seeking', 'recommend a', 'wanted',
    ];

    private const URGENT_PATTERNS = ['عاجل', 'مستعجل', 'ضروري', 'اليوم', 'بكرة', 'باكر', 'urgent', 'asap', 'today', 'tomorrow'];

    /**
     * @param  array{platform: string, url: string, text: string, category: string, country: string, posted_at?: ?string, post_type?: ?string, budget?: ?string, deadline?: ?string}  $lead
     * @return array{status: string, reason: ?string, score: int, url_key: string, text_hash: string}
     */
    public static function screen(array $lead): array
    {
        $urlKey = self::urlKey($lead['url']);
        $textHash = self::textHash($lead['text']);
        $postedAt = filled($lead['posted_at'] ?? null) ? Carbon::parse($lead['posted_at']) : null;
        $marketplace = ($lead['scope'] ?? 'gcc') === 'marketplace';

        $reason = match (true) {
            $marketplace && ! in_array($lead['platform'], ['mostaql', 'khamsat'], true) => 'not_marketplace',
            $marketplace && (int) ($lead['budget_usd'] ?? 0) < ServiceLead::MARKETPLACE_MIN_BUDGET_USD => 'low_budget',
            ! $marketplace && ! array_key_exists((string) ($lead['country'] ?? ''), ServiceLead::COUNTRIES) => 'outside_region',
            self::isOffer($lead['text'], $lead['post_type'] ?? null) => 'offer',
            $postedAt !== null && $postedAt->lt(now()->subDays(7)) => 'stale',
            ServiceLead::query()->where('text_hash', $textHash)->where('created_at', '>=', now()->subDays(14))->exists() => 'duplicate_text',
            default => null,
        };

        return [
            'status' => $reason ? 'rejected' : 'new',
            'reason' => $reason,
            'score' => $reason ? 0 : ($marketplace ? min(65535, (int) $lead['budget_usd']) : self::score($lead, $postedAt)),
            'url_key' => $urlKey,
            'text_hash' => $textHash,
        ];
    }

    /**
     * رابط موحّد للتكرار: بدون بروتوكول وwww واستعلامات، وtwitter.com = x.com.
     */
    public static function urlKey(string $url): string
    {
        $parts = parse_url(trim($url));
        $host = Str::lower(preg_replace('/^(www\.|mobile\.|m\.)/i', '', $parts['host'] ?? ''));
        $host = $host === 'twitter.com' ? 'x.com' : $host;
        $path = rtrim($parts['path'] ?? '', '/');

        return hash('sha256', $host.$path);
    }

    public static function textHash(string $text): string
    {
        $normalized = Str::lower(preg_replace(['~https?://\S+~u', '/[^\p{L}\p{N}]+/u'], ['', ' '], $text));

        return hash('sha256', trim(preg_replace('/\s+/u', ' ', $normalized)));
    }

    private static function isOffer(string $text, ?string $postType): bool
    {
        if ($postType === 'offer') {
            return true;
        }

        $lower = Str::lower($text);

        return self::contains($lower, self::OFFER_PATTERNS) && ! self::contains($lower, self::REQUEST_PATTERNS);
    }

    private static function score(array $lead, ?Carbon $postedAt): int
    {
        $text = Str::lower($lead['text'].' '.($lead['deadline'] ?? ''));

        return 10
            + (($lead['country'] ?? null) === 'OM' ? 40 : 0)
            + (filled($lead['budget'] ?? null) ? 20 : 0)
            + (filled($lead['deadline'] ?? null) || self::contains($text, self::URGENT_PATTERNS) ? 15 : 0)
            + ($postedAt === null || $postedAt->gte(now()->subDay()) ? 15 : 0);
    }

    private static function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, Str::lower($needle))) {
                return true;
            }
        }

        return false;
    }
}

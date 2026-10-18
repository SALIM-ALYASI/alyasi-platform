<?php

namespace App\Support\Events;

use App\Models\EventEdition;

/**
 * ما يميّز تغطية ALYASI لكل نسخة مؤتمر -- من بياناتها بلوحة التحكم، لأي مؤتمر.
 * كل شارة تعكس شي موجود فعلًا:
 * - «شريك إعلامي»: صفة الشراكة (حتى قبل ما ننشر أي تغطية).
 * - «تغطية ميدانية»: حضرنا ونُشرت تغطيتنا.
 * - «مقابلات حصرية»: فيه مقابلات منشورة فعلًا.
 */
final class EventHighlights
{
    /**
     * @return array{tags: list<string>, links: list<array{label: string, url: string}>, interviews: \Illuminate\Support\Collection, exclusive_at: ?\Illuminate\Support\Carbon}
     */
    public static function for(?EventEdition $edition): array
    {
        $empty = ['tags' => [], 'links' => [], 'interviews' => collect(), 'exclusive_at' => null];

        if (! $edition) {
            return $empty;
        }

        $interviews = $edition->relationLoaded('interviews')
            ? $edition->interviews->where('is_published', true)->values()
            : $edition->interviews()->published()->get();

        $tags = [];
        $links = [];

        if ($edition->is_media_partner) {
            $tags[] = __('events.tag_partner');
        }

        if ($edition->hasOnsiteCoverage()) {
            $tags[] = __('events.tag_onsite');
        } elseif ($edition->coverage_type === 'global_remote' && $edition->phase === 'concluded') {
            $tags[] = __('events.tag_remote');
        }

        if ($interviews->isNotEmpty()) {
            $tags[] = trans_choice('events.tag_interviews', $interviews->count(), ['count' => $interviews->count()]);
        }

        // رابط التغطية الكاملة يطلع بس لما تكون التغطية منشورة فعلًا.
        if (($url = $edition->coverageLink()) && $edition->exclusive_published_at?->isPast()) {
            $links[] = ['label' => __('events.coverage_full'), 'url' => $url];
        }

        if (($url = $edition->interviewsLink()) && $interviews->isNotEmpty()) {
            $links[] = ['label' => __('events.interviews_link'), 'url' => $url];
        }

        return [
            'tags' => $tags,
            'links' => $links,
            'interviews' => $interviews,
            'exclusive_at' => $edition->latestExclusiveAt($interviews->count(), $interviews->max('published_at')),
        ];
    }
}

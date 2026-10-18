<?php

namespace App\Support\Events;

use App\Models\Event;
use App\Models\EventEdition;
use App\Support\Coverage\CyberxInterviews;
use App\Support\Coverage\CyberxOman2026;

/**
 * ما يميّز تغطية ALYASI لكل مؤتمر (يطلع على بطاقته بصفحة الفعاليات):
 * تغطية ميدانية، شراكة إعلامية، مقابلات حصرية، وروابط مباشرة لها.
 * المعلومات من المحتوى المنشور فقط -- ما نضيف شي غير مؤكد.
 */
final class EventHighlights
{
    /** المؤتمرات اللي ALYASI شريكها الإعلامي (مذكور بصفحاتها المنشورة). */
    private const MEDIA_PARTNER = ['cysec-oman'];

    /**
     * @return array{tags: list<string>, links: list<array{label: string, url: string}>}
     */
    public static function for(Event $event, ?EventEdition $edition): array
    {
        $tags = [];
        $links = [];

        if (in_array($event->slug, self::MEDIA_PARTNER, true)) {
            $tags[] = __('events.tag_partner');
        }

        if ($edition?->attended) {
            $tags[] = __('events.tag_onsite');
        } elseif ($edition && $edition->coverage_type === 'global_remote') {
            $tags[] = __('events.tag_remote');
        }

        if ($edition && $edition->slug('ar') === CyberxOman2026::EVENT_SLUG) {
            $interviews = count(self::publishedInterviews());

            if ($interviews > 0) {
                $tags[] = trans_choice('events.tag_interviews', $interviews, ['count' => $interviews]);
                $links[] = ['label' => __('events.interviews_link'), 'url' => localized_route('event_coverage.cyberx_oman_2026.interviews')];
            }

            $links[] = ['label' => __('events.coverage_full'), 'url' => localized_route('event_coverage.cyberx_oman_2026')];
        }

        return ['tags' => $tags, 'links' => $links];
    }

    /** @return list<array<string, mixed>> */
    public static function publishedInterviews(): array
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

        return array_values(array_filter(
            CyberxInterviews::guests($locale),
            fn (array $guest) => $guest['published']
        ));
    }
}

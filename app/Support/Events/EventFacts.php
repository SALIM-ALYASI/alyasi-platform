<?php

namespace App\Support\Events;

use App\Models\EventEdition;

/**
 * مكان انعقاد كل نسخة للبيانات المنظمة (schema.org Event) -- من المحتوى
 * المنشور أو الموقع الرسمي فقط. نسخة ما مكانها مؤكد ما تطلع لها بيانات Event.
 */
final class EventFacts
{
    /** [slug النسخة => [اسم المكان، المدينة، رمز الدولة، رابط البث أو null]] */
    private const LOCATIONS = [
        'apple-event-2026' => ['Apple Park', 'Cupertino', 'US', 'https://www.apple.com/apple-events/'],
        'huawei-chase-the-wild-2026' => ['SHOWPALAST', 'Munich', 'DE', null],
        'cyberx-oman-2026' => ['Mövenpick Hotel and Apartments Ghala', 'Muscat', 'OM', null],
        'oman-electricity-energy-conference-2026' => ['Oman Convention & Exhibition Centre', 'Muscat', 'OM', null],
        'cysec-oman-2026' => ['Sheraton Oman Hotel', 'Muscat', 'OM', null],
        'comex-oman-2027' => ['Oman Convention & Exhibition Centre (OCEC), Madinat Al Irfan', 'Muscat', 'OM', null],
    ];

    /** @return array<string, mixed>|null */
    public static function schema(EventEdition $edition, string $description, string $image): ?array
    {
        $place = self::LOCATIONS[$edition->slug('ar')] ?? null;

        if (! $place || ! $edition->event_start_at) {
            return null;
        }

        [$name, $city, $country, $stream] = $place;
        $physical = [
            '@type' => 'Place',
            'name' => $name,
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => $city, 'addressCountry' => $country],
        ];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $edition->title,
            'description' => $description,
            'startDate' => $edition->event_start_at->toIso8601String(),
            'endDate' => ($edition->event_end_at ?? $edition->event_start_at)->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => $stream ? 'https://schema.org/MixedEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
            'location' => $stream ? [$physical, ['@type' => 'VirtualLocation', 'url' => $stream]] : $physical,
            'image' => [$image],
            'url' => $edition->permalink()?->url(),
            'inLanguage' => app()->getLocale(),
        ];

        if ($organizer = $edition->event?->organizer) {
            $schema['organizer'] = ['@type' => 'Organization', 'name' => $organizer];
        }

        return $schema;
    }
}

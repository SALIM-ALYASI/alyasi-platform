<?php

namespace App\Support\Events;

use App\Models\EventInterview;
use App\Models\Permalink;
use App\Support\Coverage\CyberxInterviews;
use Illuminate\Support\Facades\Cache;

/**
 * يحدّث فهرس المقابلات العام (event_interviews) من مصادر المقابلات الحالية.
 * المصدر الوحيد حاليًا ملف CyberX Oman 2026؛ أي مؤتمر جديد يضيف مصدره هنا
 * (أو صفوفه مباشرة)، وصفحة الفعاليات ما تحتاج تعديل.
 *
 * ما ينحذف أي صف: الضيف اللي انشال من النشر يصير is_published = false.
 */
final class InterviewIndex
{
    private const CYBERX_EDITION_SLUG = 'cyberx-oman-2026';

    private const CYBERX_DATA = 'data/interviews/cyberx-oman-2026.json';

    /** يحدّث الفهرس لو ملف المقابلات تغيّر من آخر مرة (فحص رخيص بكل طلب). */
    public static function syncIfStale(): void
    {
        $file = resource_path(self::CYBERX_DATA);
        $stamp = is_file($file) ? filemtime($file).':'.filesize($file) : null;

        if ($stamp === null || Cache::get('interview-index-stamp') === $stamp) {
            return;
        }

        self::sync();
        Cache::forever('interview-index-stamp', $stamp);
    }

    public static function sync(): int
    {
        $editionId = Permalink::query()
            ->where('linkable_type', 'event_edition')
            ->where('locale', 'ar')
            ->where('slug', self::CYBERX_EDITION_SLUG)
            ->value('linkable_id');

        if (! $editionId) {
            return 0;
        }

        $ar = CyberxInterviews::guests('ar');
        $en = collect(CyberxInterviews::guests('en'))->keyBy('slug');
        $count = 0;

        foreach ($ar as $order => $guest) {
            $guestEn = $en[$guest['slug']] ?? [];
            $languages = $guest['languages'] ?? ['ar'];
            // صفحة بلغة ما لها نسخة تودّي للغة الأصلية (مثل خالد وفاطمة).
            $urlFor = fn (string $locale) => $guest['published']
                ? route($locale === 'en' ? 'event_coverage.cyberx_oman_2026.interview.en' : 'event_coverage.cyberx_oman_2026.interview', ['guest' => $guest['slug']])
                : null;

            $row = EventInterview::query()->firstOrNew(['event_edition_id' => $editionId, 'slug' => $guest['slug']]);
            $row->fill([
                'name_ar' => $guest['name'],
                'name_en' => $guestEn['name'] ?? null,
                'role_ar' => $guest['role'],
                'role_en' => $guestEn['role'] ?? null,
                'photo' => $guest['photo'] ? parse_url($guest['photo'], PHP_URL_PATH) : null,
                'url_ar' => in_array('ar', $languages, true) || ($guest['inline_translation'] ?? null) === 'ar' ? $urlFor('ar') : null,
                'url_en' => in_array('en', $languages, true) || ($guest['inline_translation'] ?? null) === 'en' ? $urlFor('en') : null,
                'is_published' => (bool) $guest['published'],
                'sort_order' => $order,
            ]);

            // تاريخ النشر أول مرة ينشاف منشور، ويبقى ثابت بعدها.
            if ($row->is_published && ! $row->published_at) {
                $row->published_at = now();
            }

            $row->save();
            $count++;
        }

        return $count;
    }
}

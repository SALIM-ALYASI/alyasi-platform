<?php

namespace App\Support\Coverage;

/**
 * اللقاءات الصحفية مع ضيوف CyberX Oman 2026 -- المحتوى (نص الإجابات بتوقيت
 * كل جملة، وترجمتها، وروابط الصوت) في resources/data/interviews/، والصفحات
 * تعرضه بلغة الرابط.
 */
class CyberxInterviews
{
    private const DATA = 'data/interviews/cyberx-oman-2026.json';

    /** @return list<array<string, mixed>> */
    public static function guests(string $locale): array
    {
        $data = json_decode((string) file_get_contents(resource_path(self::DATA)), true);

        return array_map(fn (array $guest) => self::localize($guest, $locale), $data['guests']);
    }

    /** @return array<string, mixed>|null */
    public static function guest(string $slug, string $locale): ?array
    {
        foreach (self::guests($locale) as $guest) {
            if ($guest['slug'] === $slug) {
                return $guest;
            }
        }

        return null;
    }

    /**
     * لغات صفحة المقابلة نفسها (حسب لغة إجابات الضيف). صفحة الضيوف تبقى باللغتين.
     *
     * @return list<string>
     */
    public static function languagesOf(string $slug): array
    {
        return self::guest($slug, 'ar')['languages'] ?? ['ar'];
    }

    /** @return list<array<string, mixed>> */
    public static function published(): array
    {
        return array_values(array_filter(self::guests('ar'), fn ($guest) => $guest['published']));
    }

    private static function localize(array $guest, string $locale): array
    {
        $pick = fn (string $key) => $guest[$key.'_'.$locale] ?? $guest[$key.'_ar'] ?? null;

        // سؤال بلغة وحدة (مثلًا جواب بدون نسخة إنجليزية من الضيف) ما يطلع باللغة الثانية.
        $guestItems = array_values(array_filter(
            $guest['items'] ?? [],
            fn (array $item) => in_array($locale, $item['languages'] ?? ['ar', 'en'], true)
        ));

        // بطاقة بعنوان (مثل «تعريف الضيف») ما تنحسب ضمن ترقيم الأسئلة.
        $number = 0;

        $items = array_map(function (array $item) use ($locale) {
            // نسخة بلغة الصفحة غير لغة التسجيل الأصلي: صوت الضيف المولّد (بموافقته)
            // ونصّه فقرات (ما تتزامن مع الصوت الأصلي)، والتسجيل الأصلي يبقى متاحًا.
            $alt = $item['a_audio_'.$locale] ?? null;
            $paragraphs = $item[$locale.'_paragraphs'] ?? [];

            return [
                'label' => $item['label_'.$locale] ?? $item['label_ar'] ?? null,
                'number' => null,
                'question' => $item['q_'.$locale] ?? $item['q_ar'],
                'q_audio' => asset(ltrim($item['q_audio'][$locale] ?? $item['q_audio']['ar'] ?? reset($item['q_audio']), '/')),
                'a_audio' => asset(ltrim($alt ? $alt['src'] : $item['a_audio'], '/')),
                'a_audio_original' => $alt ? asset(ltrim($item['a_audio'], '/')) : null,
                'ai_voice' => ! empty($alt['ai_voice']),
                'duration' => $alt['duration'] ?? $item['duration'],
                'paragraphs' => $paragraphs,
                'segments' => $paragraphs
                    ? []
                    : array_map(fn (array $s) => ['t' => $s['t'], 'text' => $s[$locale] ?? $s['ar']], $item['segments']),
            ];
        }, $guestItems);

        foreach ($items as &$item) {
            $item['number'] = $item['label'] === null ? ++$number : null;
        }
        unset($item);

        // زر «ترجمة النص» بالصفحة العربية (مثل خالد): الترجمة بيانات داخل
        // الصفحة تنعرض بالضغط فقط -- مب نسخة إنجليزية منشورة، والصوت يبقى الأصلي.
        $translation = null;

        if ($locale === 'ar' && ($guest['inline_translation'] ?? null) === 'en') {
            $translation = [
                'intro' => $guest['intro_en'] ?? null,
                'items' => array_map(fn (array $source, array $shown) => [
                    'question_label' => ($source['label_en'] ?? 'Question '.$shown['number']).' · Salem Al Hajri',
                    'answer_label' => ($source['label_en'] ?? 'Answer '.$shown['number']).' · '.($guest['name_en'] ?? ''),
                    'question' => $source['q_en'] ?? '',
                    'paragraphs' => $source['en_paragraphs'] ?? [],
                ], $guestItems, $items),
            ];
        }

        return [
            'slug' => $guest['slug'],
            'published' => (bool) $guest['published'],
            'languages' => $guest['languages'] ?? ['ar'],
            // لغة جاهزة بس تنتظر مراجعة (مثلًا صوت مولّد لسا ما وافق عليه الضيف):
            // تنفتح برابط المعاينة الموقّع فقط، وما تطلع بالـ hreflang ولا الخريطة.
            'preview_languages' => $guest['preview_languages'] ?? [],
            'ai_voice_label' => $guest['ai_voice_label_'.$locale] ?? null,
            // لغة التسجيل الأصلي (أغلب الضيوف عربي، علي اللواتي إنجليزي).
            'original_language' => $guest['original_language'] ?? 'ar',
            // ملاحظة فوق الإجابات بلغة الصفحة (مثلًا: ترجمة بصوت مولّد بموافقة الضيف).
            'note' => $guest['note_'.$locale] ?? null,
            'translation' => $translation,
            'name' => $pick('name'),
            'role' => $pick('role'),
            'topic' => $pick('topic'),
            'quote' => $pick('quote'),
            'intro' => $pick('intro'),
            // المقدمة بصوت سالم (اختيارية) -- أول ما يشتغل بـ«استمع للمقابلة كاملة».
            'intro_audio' => ! empty($guest['intro_audio'][$locale] ?? null)
                ? asset(ltrim($guest['intro_audio'][$locale], '/'))
                : null,
            // خاتمة سالم بعد آخر إجابة (اختيارية) -- آخر ما يشتغل بـ«استمع للمقابلة كاملة».
            'outro' => $pick('outro'),
            'outro_audio' => ! empty($guest['outro_audio'][$locale] ?? null)
                ? asset(ltrim($guest['outro_audio'][$locale], '/'))
                : null,
            'photo' => $guest['photo'] ? asset(ltrim($guest['photo'], '/')) : null,
            // صورة المشاركة (1200×630): الضيف واسمه واقتباسه -- تظهر لما ينرسل رابط مقابلته.
            'og_image' => ($ogImage = $guest['og_image_'.$locale] ?? $guest['og_image'] ?? null) ? asset(ltrim($ogImage, '/')) : null,
            'initials' => $guest['initials'][$locale] ?? $guest['initials']['ar'] ?? '',
            'items' => $items,
            'questions' => count(array_filter($items, fn (array $item) => $item['number'] !== null)),
            'minutes' => (int) max(1, round(array_sum(array_column($items, 'duration')) / 60)),
        ];
    }
}

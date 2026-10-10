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

        $items = array_map(fn (array $item) => [
            'question' => $item['q_'.$locale] ?? $item['q_ar'],
            'q_audio' => asset(ltrim($item['q_audio'][$locale] ?? $item['q_audio']['ar'], '/')),
            // بالإنجليزي: صوت الضيف المولّد (بموافقته) لو موجود، والتسجيل العربي الأصلي يبقى متاحًا.
            'a_audio' => asset(ltrim($locale === 'en' && ! empty($item['a_audio_en']) ? $item['a_audio_en']['src'] : $item['a_audio'], '/')),
            'a_audio_original' => $locale === 'en' && ! empty($item['a_audio_en']) ? asset(ltrim($item['a_audio'], '/')) : null,
            'ai_voice' => $locale === 'en' && ! empty($item['a_audio_en']['ai_voice']),
            'duration' => $locale === 'en' && ! empty($item['a_audio_en']) ? $item['a_audio_en']['duration'] : $item['duration'],
            // النسخة الإنجليزية: نص الضيف نفسه (فقرات) لو كتبها -- ما تتزامن مع الصوت العربي.
            'paragraphs' => $locale === 'en' && ! empty($item['en_paragraphs']) ? $item['en_paragraphs'] : [],
            'segments' => $locale === 'en' && ! empty($item['en_paragraphs'])
                ? []
                : array_map(fn (array $s) => ['t' => $s['t'], 'text' => $s[$locale] ?? $s['ar']], $item['segments']),
        ], $guestItems);

        return [
            'slug' => $guest['slug'],
            'published' => (bool) $guest['published'],
            'languages' => $guest['languages'] ?? ['ar'],
            'name' => $pick('name'),
            'role' => $pick('role'),
            'topic' => $pick('topic'),
            'quote' => $pick('quote'),
            'intro' => $pick('intro'),
            // المقدمة بصوت سالم (اختيارية) -- أول ما يشتغل بـ«استمع للمقابلة كاملة».
            'intro_audio' => ! empty($guest['intro_audio'][$locale] ?? null)
                ? asset(ltrim($guest['intro_audio'][$locale], '/'))
                : null,
            'photo' => $guest['photo'] ? asset(ltrim($guest['photo'], '/')) : null,
            // صورة المشاركة (1200×630): الضيف واسمه واقتباسه -- تظهر لما ينرسل رابط مقابلته.
            'og_image' => ! empty($guest['og_image']) ? asset(ltrim($guest['og_image'], '/')) : null,
            'initials' => $guest['initials'][$locale] ?? $guest['initials']['ar'] ?? '',
            'items' => $items,
            'minutes' => (int) max(1, round(array_sum(array_column($items, 'duration')) / 60)),
        ];
    }
}

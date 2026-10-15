<?php

namespace App\Support\OmanPulse;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Carbon;

/**
 * استخراج حرفي من صفحة بيان رسمي (بدون ذكاء اصطناعي): العنوان والفقرات
 * والتاريخ والصورة كما هي في الصفحة. الفقرات من العنصر اللي يحوي أطول نص
 * فقرات (جسم الخبر)، مو من القوائم الجانبية والأخبار ذات الصلة.
 */
final class OfficialPageExtractor
{
    private const MAX_PARAGRAPHS = 6; // مقتطف منسوب، والبيان كامل برابط المصدر

    private const AR_MONTHS = [
        'يناير' => 1, 'فبراير' => 2, 'مارس' => 3, 'أبريل' => 4, 'ابريل' => 4, 'مايو' => 5, 'يونيو' => 6,
        'يوليو' => 7, 'أغسطس' => 8, 'اغسطس' => 8, 'سبتمبر' => 9, 'أكتوبر' => 10, 'اكتوبر' => 10,
        'نوفمبر' => 11, 'ديسمبر' => 12,
    ];

    /**
     * @return array{title: ?string, paragraphs: list<string>, image: ?string, published_at: ?Carbon}
     */
    public static function extract(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $meta = fn (string $property) => self::clean(
            $xpath->evaluate("string(//meta[@property='{$property}' or @name='{$property}']/@content)")
        );

        $title = $meta('og:title')
            ?: self::clean($xpath->evaluate('string((//h1)[last()])'))
            ?: self::clean($xpath->evaluate('string(//title)'));

        $paragraphs = self::paragraphs($xpath);

        $published = self::parseDate($meta('article:published_time'))
            ?? self::parseDate($meta('datePublished'))
            ?? self::parseArabicDate($paragraphs[0] ?? '');

        return [
            'title' => $title ?: null,
            'paragraphs' => $paragraphs,
            'image' => $meta('og:image') ?: null,
            'published_at' => $published,
        ];
    }

    /**
     * @return list<string>
     */
    private static function paragraphs(DOMXPath $xpath): array
    {
        $groups = [];

        foreach ($xpath->query('//p') as $p) {
            $text = self::clean($p->textContent);
            if (mb_strlen($text) < 30 || ! $p->parentNode instanceof DOMElement) {
                continue;
            }

            // مسار العنصر الأب (ثابت)؛ مُعرّف الكائن يتغيّر مع كل وصول للعقدة.
            $key = $p->parentNode->getNodePath();
            $groups[$key]['texts'][] = $text;
            $groups[$key]['length'] = ($groups[$key]['length'] ?? 0) + mb_strlen($text);
        }

        if (! $groups) {
            return [];
        }

        usort($groups, fn ($a, $b) => $b['length'] <=> $a['length']);

        return array_slice(array_values(array_unique($groups[0]['texts'])), 0, self::MAX_PARAGRAPHS);
    }

    private static function parseDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * «مسقط في 12 أكتوبر 2026 /العُمانية/» -- صيغة وكالة الأنباء العُمانية.
     */
    private static function parseArabicDate(string $text): ?Carbon
    {
        $months = implode('|', array_keys(self::AR_MONTHS));

        if (! preg_match('/(\d{1,2})\s+('.$months.')\s+(\d{4})/u', $text, $m)) {
            return null;
        }

        return Carbon::create((int) $m[3], self::AR_MONTHS[$m[2]], (int) $m[1], 9, 0, 0, 'Asia/Muscat');
    }

    private static function clean(mixed $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $text));
    }
}

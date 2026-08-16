<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * يحوّل روابط الفيديو/الصوت المعروفة داخل محتوى المقال (يوتيوب،
 * فيميو، سبوتيفاي، ساوندكلاود، بودكاست) إلى زر CTA بأيقونة مناسبة
 * تلقائيًا وقت العرض، بدل رابط نصي عادي — بدون أي تعديل على المحتوى
 * المخزّن بقاعدة البيانات، وبدون أي وسم/صنف يدوي يكتبه المدير (أصلًا
 * غير ممكن لأن Purifier لا يسمح بخاصية class على <a>).
 *
 * أي رابط آخر لا يطابق منصة معروفة يبقى كما هو (رابط عادي منسّق
 * بواسطة CSS فقط).
 */
class RichContentRenderer
{
    private const MEDIA_HOSTS = [
        'youtube' => ['youtube.com', 'youtu.be'],
        'vimeo' => ['vimeo.com'],
        'spotify' => ['spotify.com'],
        'soundcloud' => ['soundcloud.com'],
        'podcast' => ['podcasts.apple.com', 'anchor.fm'],
    ];

    private const MEDIA_ICONS = [
        'youtube' => 'fa-brands fa-youtube',
        'vimeo' => 'fa-brands fa-vimeo-v',
        'spotify' => 'fa-brands fa-spotify',
        'soundcloud' => 'fa-brands fa-soundcloud',
        'podcast' => 'fa-solid fa-podcast',
    ];

    public static function render(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);

        if (! $root) {
            return $html;
        }

        foreach (iterator_to_array($root->getElementsByTagName('a')) as $anchor) {
            if ($anchor instanceof DOMElement) {
                self::enhanceIfMediaLink($dom, $anchor);
            }
        }

        $output = '';

        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        return $output;
    }

    private static function enhanceIfMediaLink(DOMDocument $dom, DOMElement $anchor): void
    {
        $href = $anchor->getAttribute('href');

        if ($href === '') {
            return;
        }

        $platform = self::detectPlatform($href);

        if ($platform === null) {
            return;
        }

        $existingClass = trim($anchor->getAttribute('class'));
        $anchor->setAttribute(
            'class',
            trim($existingClass.' article-btn article-btn--'.$platform)
        );

        $icon = $dom->createElement('i');
        $icon->setAttribute('class', self::MEDIA_ICONS[$platform] ?? 'fa-solid fa-play');
        $icon->setAttribute('aria-hidden', 'true');

        $anchor->insertBefore($icon, $anchor->firstChild);
    }

    private static function detectPlatform(string $href): ?string
    {
        $host = parse_url($href, PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        $host = strtolower((string) preg_replace('/^www\./', '', $host));

        foreach (self::MEDIA_HOSTS as $platform => $domains) {
            foreach ($domains as $domain) {
                if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                    return $platform;
                }
            }
        }

        return null;
    }
}

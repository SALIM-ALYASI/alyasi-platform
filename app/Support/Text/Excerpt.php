<?php

namespace App\Support\Text;

use App\Console\Commands\StripFactMarkers;

/**
 * مقتطف نظيف من نص طويل: جمل كاملة لين الحد، بدل قص أول N حرف بنص الكلمة.
 * لو أول جملة نفسها أطول من الحد: تنقص عند آخر كلمة كاملة وتنتهي بـ«…».
 */
final class Excerpt
{
    public static function make(?string $text, int $max = 220): ?string
    {
        if (blank($text)) {
            return null;
        }

        // ترميز السيرفر الافتراضي مب دايمًا UTF-8: كل دوال mb_ هنا بترميز صريح.
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = StripFactMarkers::clean($text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }

        $sentences = preg_split('/(?<=[.!?؟])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = '';

        foreach ($sentences as $sentence) {
            $candidate = $out === '' ? $sentence : $out.' '.$sentence;

            if (mb_strlen($candidate, 'UTF-8') > $max) {
                break;
            }

            $out = $candidate;
        }

        if ($out !== '') {
            return $out;
        }

        // أول جملة أطول من الحد: آخر كلمة كاملة + «…».
        $cut = mb_substr($text, 0, $max, 'UTF-8');
        $space = mb_strrpos($cut, ' ', 0, 'UTF-8');

        // preg بـ/u مب rtrim: rtrim يشتغل بالبايت ويقص نص حرف عربي.
        return preg_replace('/[\s,،;؛:\-–—]+$/u', '', mb_substr($cut, 0, $space ?: $max, 'UTF-8')).'…';
    }

    /** هل المقتطف مقصوص بنص كلمة أو جملة (من الطريقة القديمة)؟ */
    public static function looksCut(?string $excerpt): bool
    {
        $excerpt = trim((string) $excerpt);

        return $excerpt !== '' && (preg_match('/(\.\.\.|…)$/u', $excerpt) || ! preg_match('/[.!?؟»"”)]$/u', $excerpt));
    }
}

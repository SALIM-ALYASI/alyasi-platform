<?php

namespace App\Support\Analytics;

/**
 * وصف مختصر للجهاز من الـ user agent («iPhone · iOS 18.7 · Safari 27»).
 * تقريبي: الـ user agent يرسله المتصفح وممكن يتزوّر، فهو مؤشر مب إثبات.
 */
final class DeviceSummary
{
    public static function describe(?string $agent): string
    {
        $agent = (string) $agent;

        if ($agent === '') {
            return 'غير معروف (بدون user agent)';
        }

        return implode(' · ', array_filter([self::device($agent), self::os($agent), self::browser($agent)]));
    }

    private static function device(string $agent): ?string
    {
        return match (true) {
            str_contains($agent, 'iPhone') => 'iPhone',
            str_contains($agent, 'iPad') => 'iPad',
            str_contains($agent, 'Android') => preg_match('#Android [\d.]+; ([^;)]+)\)#', $agent, $m) && trim($m[1]) !== 'K' ? 'Android ('.trim($m[1]).')' : 'Android',
            str_contains($agent, 'Macintosh') => 'Mac',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Linux') => 'Linux',
            default => null,
        };
    }

    private static function os(string $agent): ?string
    {
        return match (true) {
            (bool) preg_match('#(?:iPhone|CPU) OS (\d+)_(\d+)#', $agent, $m) => 'iOS '.$m[1].'.'.$m[2],
            (bool) preg_match('#Android (\d+(?:\.\d+)?)#', $agent, $m) => 'Android '.$m[1],
            (bool) preg_match('#Windows NT (\d+\.\d+)#', $agent, $m) => 'Windows NT '.$m[1],
            default => null,
        };
    }

    private static function browser(string $agent): ?string
    {
        $patterns = [
            'WhatsApp' => '#WhatsApp/([\d.]+)#',
            'Instagram' => '#Instagram ([\d.]+)#',
            'Facebook' => '#FBAV/([\d.]+)#',
            'Edge' => '#Edg(?:iOS|A)?/(\d+)#',
            'Firefox' => '#(?:Firefox|FxiOS)/(\d+)#',
            'Chrome' => '#(?:CriOS|Chrome)/(\d+)#',
            'Safari' => '#Version/(\d+)[.\d]* (?:Mobile/\S+ )?Safari#',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $agent, $m)) {
                return $name.' '.$m[1];
            }
        }

        return null;
    }
}

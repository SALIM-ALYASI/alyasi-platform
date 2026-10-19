<?php

namespace App\Support\Reviews;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * روابط صفحة طلب التقييم المؤقتة (عربي/إنجليزي) -- موقّعة وتنتهي.
 */
final class ReviewLink
{
    /**
     * @return array{ar: string, en: string}
     */
    public static function make(string $slug, Carbon $expires): array
    {
        return [
            'ar' => url(URL::temporarySignedRoute('review.request', $expires, ['work' => $slug], absolute: false)),
            'en' => url(URL::temporarySignedRoute('review.request.en', $expires, ['work' => $slug], absolute: false)),
        ];
    }

    /**
     * @return array{ar: ?string, en: ?string}
     */
    public static function alternates(string $slug, int $expiresAt): array
    {
        if ($slug === '' || $expiresAt <= time()) {
            return ['ar' => null, 'en' => null];
        }

        return self::make($slug, Carbon::createFromTimestamp($expiresAt));
    }
}

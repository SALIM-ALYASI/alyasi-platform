<?php

namespace App\Support\Interviews;

use App\Models\PageEvent;
use Illuminate\Support\Carbon;

/**
 * روابط ضيوف أداة مقابلات CyberX: الضيف اللي ما فتح رابطه خلال 10 أيام من
 * بدء تسجيل الفتح (16 أكتوبر 2026)، وما سجّل أي إجابة، ينشال من الأداة
 * (يختفي تبويبه ويتعطل رابطه وما تنقبل منه إجابات). بياناته تبقى بملف
 * data.json، فإرجاعه ممكن بتمديد المهلة أو إضافته للمستثنين.
 */
final class GuestLinkExpiry
{
    public const TRACKING_STARTED = '2026-10-16 00:00:00';

    public const DAYS = 10;

    /** مقابلاتهم منشورة -- ما تنطبق عليهم المهلة. */
    public const EXEMPT = ['yahya', 'haitham', 'ali', 'khalid'];

    public static function deadline(): Carbon
    {
        return Carbon::parse(self::TRACKING_STARTED, 'Asia/Muscat')->addDays(self::DAYS);
    }

    public static function isExpired(string $guestId): bool
    {
        if (in_array($guestId, self::EXEMPT, true) || now()->lt(self::deadline())) {
            return false;
        }

        return ! self::opened($guestId) && ! self::answered($guestId);
    }

    public static function opened(string $guestId): bool
    {
        return PageEvent::query()
            ->where('type', 'guest_open')
            ->where('label', $guestId)
            ->exists();
    }

    public static function answered(string $guestId): bool
    {
        // ملفات الإجابات: {question_id}-{وقت}-{عشوائي}.{امتداد}، وquestion_id يبدأ بمعرّف الضيف.
        $pattern = storage_path('app/private/cyberx-answers-meta/*/'.$guestId.'-*.json');

        return (bool) glob($pattern);
    }
}

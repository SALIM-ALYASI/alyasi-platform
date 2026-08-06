<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BlockedVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

trait ProtectsAgainstAbuse
{
    /**
     * اسم كوكي معرّف الجهاز المستخدم لمكافحة الإزعاج.
     */
    private const DEVICE_COOKIE = 'alyasi_device_id';

    /**
     * مدة صلاحية كوكي معرّف الجهاز بالدقائق (5 سنوات).
     */
    private const DEVICE_COOKIE_MINUTES = 5 * 365 * 24 * 60;

    /**
     * قراءة معرّف الجهاز من الكوكي، أو توليد واحد جديد.
     */
    protected function resolveDeviceToken(Request $request): string
    {
        return $request->cookie(self::DEVICE_COOKIE)
            ?: (string) Str::uuid();
    }

    /**
     * التحقق إن كان الزائر (بمعرّف جهازه أو الـ IP) محظورًا.
     */
    protected function isVisitorBlocked(
        ?string $deviceToken,
        ?string $ipAddress
    ): bool {
        return BlockedVisitor::query()
            ->matching($deviceToken, $ipAddress)
            ->exists();
    }

    /**
     * إنشاء كوكي معرّف الجهاز لإرفاقه بالاستجابة.
     */
    protected function deviceTokenCookie(string $deviceToken): Cookie
    {
        return cookie(
            self::DEVICE_COOKIE,
            $deviceToken,
            self::DEVICE_COOKIE_MINUTES
        );
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * إغلاق الصفحات العامة أثناء وضع الصيانة، مع إبقاء لوحة التحكم
     * وواجهة API متاحتين حتى يقدر المدير يعيد تفعيل الموقع.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenanceMode = Setting::get('maintenance_mode', '0') === '1';

        $isExempt = $request->is('admin*')
            || $request->is('api*')
            || $request->is('locale/*');

        if ($isMaintenanceMode && ! $isExempt) {
            abort(503);
        }

        return $next($request);
    }
}

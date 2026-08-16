<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ForceLocale
{
    /**
     * فرض لغة الطلب من الرابط نفسه (بغض النظر عن جلسة المستخدم الحالية)،
     * ثم حفظها بالجلسة حتى تبقى بقية الموقع (الصفحات التي ما زالت
     * تعتمد على الجلسة فقط مثل /contact و/about) متسقة مع نفس اللغة
     * بعد التنقل من صفحة برابط لغوي إلى صفحة بدونه.
     */
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        App::setLocale($locale);

        $request->session()->put('locale', $locale);

        return $next($request);
    }
}

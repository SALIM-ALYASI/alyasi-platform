<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ForceLocale
{
    /**
     * فرض لغة الطلب من الرابط نفسه (بغض النظر عن جلسة المستخدم).
     *
     * تُستخدم لمجموعات روابط ثابتة اللغة مثل /articles و /en/articles،
     * حيث يجب أن تُعرض الصفحة دائمًا بلغة الرابط، لا بلغة الجلسة.
     */
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        App::setLocale($locale);

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ServerInfoController extends Controller
{
    /**
     * رابط أداة بيانات السيرفر الخارجية. الأداة تُخدَّم عبر HTTP عادي بدون
     * HTTPS، فيمنع المتصفح تحميلها مباشرة داخل iframe بصفحة HTTPS (Mixed
     * Content). الحل: تُجلب من طرف السيرفر (لا يخضع لقيد المتصفح) ثم تُعرض
     * محليًا عبر iframe[srcdoc] بدل iframe[src].
     */
    private const URL = 'http://167.233.163.230:6060';

    public function index(): View
    {
        $html = null;

        try {
            $response = Http::timeout(5)->get(self::URL);

            if ($response->successful()) {
                $html = $response->body();
            }
        } catch (\Throwable) {
            $html = null;
        }

        return view('admin.server-info.index', [
            'url' => self::URL,
            'html' => $html,
        ]);
    }
}

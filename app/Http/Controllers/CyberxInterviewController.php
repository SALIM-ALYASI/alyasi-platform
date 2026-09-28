<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CyberxInterviewController extends Controller
{
    /**
     * أداة داخلية لمقابلات CyberX عُمان 2026 -- سالم يشغّل سؤالاً مسجّلاً
     * صوتيًا بدل نطقه، ثم يسجّل إجابة الضيف مباشرة من المتصفح. ما تُستخدم
     * كصفحة محتوى عامة، فما تمتد من layouts.app (بدون هيدر/فوتر/سبلاش).
     */
    public function index(): View
    {
        $path = public_path('audio/cyberx-interview-2026/data.json');

        abort_unless(is_file($path), 500, 'ملف بيانات المقابلة غير موجود.');

        $data = json_decode(file_get_contents($path), true);

        abort_if(
            json_last_error() !== JSON_ERROR_NONE,
            500,
            'ملف بيانات المقابلة تالف: '.json_last_error_msg()
        );

        return view('tools.cyberx-interview', ['data' => $data]);
    }
}

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
        $questions = json_decode(
            file_get_contents(public_path('audio/cyberx-interview/manifest.json')),
            true
        );

        return view('tools.cyberx-interview', ['questions' => $questions]);
    }
}

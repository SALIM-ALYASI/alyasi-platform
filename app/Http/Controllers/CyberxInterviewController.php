<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesWhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CyberxInterviewController extends Controller
{
    use NotifiesWhatsApp;

    /**
     * امتدادات الملف حسب نوع التسجيل اللي يطلعه المتصفح (آيفون mp4،
     * كروم/أندرويد webm).
     */
    private const AUDIO_EXTENSIONS = [
        'audio/webm' => 'webm',
        'video/webm' => 'webm',
        'audio/mp4' => 'm4a',
        'video/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac',
        'audio/ogg' => 'ogg',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
    ];

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

    /**
     * إجابة ضيف مسجّلة من الأداة: تنحفظ على السيرفر (نسخة ما تضيع لو
     * انسكرت الصفحة) وتوصل لسالم بالواتساب المجاني مع السؤال ورابط
     * الإجابة -- الجسر يرسل نص بس، فالصوت يوصل كرابط يتشغّل مباشرة.
     */
    public function storeAnswer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'audio' => ['required', 'file', 'max:51200'],
            'question_id' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            'guest' => ['nullable', 'string', 'max:120'],
            'question' => ['nullable', 'string', 'max:600'],
        ]);

        $file = $request->file('audio');
        $mime = strtolower(explode(';', (string) $file->getMimeType())[0]);
        $extension = self::AUDIO_EXTENSIONS[$mime] ?? null;

        if ($extension === null) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم.'], 422);
        }

        $now = now('Asia/Muscat');
        // جزء عشوائي بالاسم عشان الرابط العام ما يكون قابل للتخمين.
        $name = $data['question_id'].'-'.$now->format('His').'-'.Str::lower(Str::random(10)).'.'.$extension;
        $path = $file->storeAs('cyberx-answers/'.$now->format('Y-m-d'), $name, 'public');

        if ($path === false) {
            return response()->json(['success' => false, 'message' => 'تعذّر حفظ التسجيل.'], 500);
        }

        $url = Storage::disk('public')->url($path);

        $lines = ['🎙 إجابة جديدة — CyberX Oman 2026'];

        if (filled($data['guest'] ?? null)) {
            $lines[] = 'الضيف: '.$data['guest'];
        }

        if (filled($data['question'] ?? null)) {
            $lines[] = 'السؤال: '.$data['question'];
        }

        $lines[] = '';
        $lines[] = '🔗 '.$url;

        $this->notifyWhatsApp(implode("\n", $lines));

        return response()->json(['success' => true, 'url' => $url]);
    }
}

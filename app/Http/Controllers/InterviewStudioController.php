<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesWhatsApp;
use App\Support\Analytics\DeviceSummary;
use App\Support\Analytics\VisitClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * استوديو المقابلات (لأي مؤتمر): أسئلة بصوت سالم، والضيف يسجّل إجابته من
 * المتصفح، يسمعها، وبعدها يقرر يرسلها. أول استخدام: CYSEC OMAN 2026.
 *
 * - بيانات المؤتمر: resources/data/interview-studio/{event}.json (أسئلة وأسماء).
 * - الروابط: لكل ضيف رمز HMAC من APP_KEY (ما ينحفظ بالكود العام ولا ينخمن)،
 *   ورمز ثاني لوضع المضيف (سالم بالمؤتمر).
 * - الإرسال مرة وحدة لكل تسجيل (take_id): ضغطتين أو إعادة محاولة بعد انقطاع
 *   النت ما تحفظ نسخة ثانية ولا ترسل واتساب ثاني.
 */
class InterviewStudioController extends Controller
{
    use NotifiesWhatsApp;

    private const AUDIO_EXTENSIONS = [
        'audio/webm' => 'webm', 'video/webm' => 'webm',
        'audio/mp4' => 'm4a', 'video/mp4' => 'm4a', 'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac', 'audio/ogg' => 'ogg', 'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav', 'audio/x-wav' => 'wav',
    ];

    public const MAX_UPLOAD_KB = 95 * 1024;

    public function show(string $event, string $token): View
    {
        $data = self::load($event);
        [$mode, $guest] = self::resolve($event, $token, $data);

        if (isset($data['closes_at']) && now()->gte($data['closes_at'])) {
            abort(410);
        }

        return view('tools.interview-studio', [
            'data' => $data,
            'event' => $event,
            'token' => $token,
            'mode' => $mode,
            'guest' => $guest,
            'audioBase' => asset('audio/interviews/'.$event).'/',
        ]);
    }

    public function storeAnswer(Request $request, string $event, string $token): JsonResponse
    {
        $data = self::load($event);
        [$mode, $guest] = self::resolve($event, $token, $data);

        $input = $request->validate([
            'audio' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB],
            'question_id' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'take_id' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{8,64}$/'],
        ]);

        // نفس التسجيل وصل قبل؟ نرجّع نفس الرد بدون حفظ ثاني ولا واتساب ثاني.
        $takeKey = 'interview-take:'.$event.':'.$input['take_id'];
        if ($previous = Cache::get($takeKey)) {
            return response()->json(['success' => true, 'url' => $previous, 'duplicate' => true]);
        }

        [$owner, $question] = self::question($data, $input['question_id'], $mode === 'guest' ? $guest : null);
        if ($question === null) {
            return response()->json(['success' => false, 'message' => 'السؤال غير معروف.'], 422);
        }

        $file = $request->file('audio');
        $mime = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()));
        $extension = self::AUDIO_EXTENSIONS[$mime] ?? null;
        if ($extension === null) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم.'], 422);
        }

        // قفل قصير: ضغطتين بنفس اللحظة (قبل ما يتسجّل الأول) ما يحفظون مرتين.
        $lock = Cache::lock($takeKey.':lock', 30);
        if (! $lock->get()) {
            return response()->json(['success' => false, 'message' => 'الإرسال جارٍ، انتظر لحظة.'], 409);
        }

        try {
            $now = now('Asia/Muscat');
            $name = $input['question_id'].'-'.$now->format('His').'-'.Str::lower(Str::random(10)).'.'.$extension;
            $path = $file->storeAs('interview-answers/'.$event.'/'.$now->format('Y-m-d'), $name, 'public');
            if ($path === false) {
                return response()->json(['success' => false, 'message' => 'تعذّر حفظ التسجيل.'], 500);
            }
            $url = Storage::disk('public')->url($path);

            [$class, $reasons] = VisitClassifier::classify($request, isPageView: false);
            $device = DeviceSummary::describe($request->userAgent());

            Storage::disk('local')->put('interview-answers-meta/'.$event.'/'.$now->format('Y-m-d').'/'.$name.'.json', json_encode([
                'file' => $path, 'event' => $event, 'mode' => $mode, 'guest' => $owner['id'] ?? null,
                'question_id' => $input['question_id'], 'take_id' => $input['take_id'],
                'received_at' => $now->toIso8601String(), 'ip' => $request->ip(),
                'user_agent' => $request->userAgent(), 'device' => $device, 'mime' => $mime,
                'size_bytes' => $file->getSize(), 'traffic_class' => $class, 'class_reasons' => $reasons,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            Cache::put($takeKey, $url, now()->addDays(30));
        } finally {
            $lock->release();
        }

        $lines = ['🎙 إجابة جديدة — '.($data['event']['name'] ?? $event)];
        $lines[] = 'الضيف: '.($owner['name_ar'] ?? 'وضع المضيف');
        $lines[] = 'السؤال: '.$question;
        $lines[] = '📱 '.$device.($mode === 'host' ? ' — من وضع المضيف' : '');
        if ($class !== VisitClassifier::HUMAN && $class !== VisitClassifier::OWNER) {
            $lines[] = '⚠️ ملاحظة: '.implode('، ', $reasons ?: [$class]);
        }
        $lines[] = '';
        $lines[] = '🔗 '.$url;
        $this->notifyWhatsApp(implode("\n", $lines));

        return response()->json(['success' => true, 'url' => $url]);
    }

    /** رمز رابط الضيف أو المضيف: HMAC من APP_KEY، 16 حرف. */
    public static function token(string $event, string $who): string
    {
        return substr(hash_hmac('sha256', 'interview-studio:'.$event.':'.$who, (string) config('app.key')), 0, 16);
    }

    public static function load(string $event): array
    {
        abort_unless((bool) preg_match('/^[a-z0-9-]{3,60}$/', $event), 404);
        $path = resource_path('data/interview-studio/'.$event.'.json');
        abort_unless(is_file($path), 404);

        return json_decode((string) file_get_contents($path), true) ?: abort(500, 'ملف بيانات المقابلات تالف.');
    }

    /**
     * @return array{0: string, 1: ?array} [guest|host، بيانات الضيف]
     */
    private static function resolve(string $event, string $token, array $data): array
    {
        abort_unless((bool) preg_match('/^[a-f0-9]{16}$/', $token), 404);

        if (hash_equals(self::token($event, 'host'), $token)) {
            return ['host', null];
        }

        foreach ($data['guests'] ?? [] as $guest) {
            if (hash_equals(self::token($event, $guest['id']), $token)) {
                return ['guest', $guest];
            }
        }

        abort(404);
    }

    /**
     * question_id: «<الضيف>-q<رقم>»، أو «<الضيف>-extra»، أو زر سريع («intro»…) للمضيف.
     * الضيف ما يقدر يرسل إلا لأسئلته هو.
     *
     * @return array{0: ?array, 1: ?string} [صاحب السؤال، نص السؤال]
     */
    private static function question(array $data, string $id, ?array $onlyGuest): array
    {
        foreach ($data['guests'] ?? [] as $guest) {
            if ($onlyGuest && $guest['id'] !== $onlyGuest['id']) {
                continue;
            }
            foreach ($guest['questions'] ?? [] as $i => $q) {
                if ($id === $guest['id'].'-q'.($i + 1)) {
                    return [$guest, $q['ar']];
                }
            }
            if (! empty($guest['extra']) && $id === $guest['id'].'-extra') {
                return [$guest, $data['extra']['ar'] ?? null];
            }
        }

        return [null, null];
    }
}

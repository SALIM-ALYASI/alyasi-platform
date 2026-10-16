<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NotifiesWhatsApp;
use App\Support\Analytics\CloudflareIps;
use App\Support\Analytics\DeviceSummary;
use App\Support\Analytics\VisitClassifier;
use App\Support\Interviews\GuestLinkExpiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
    public function index(): View|Response
    {
        // شهر بعد المؤتمر: الأداة انقفلت.
        if (GuestLinkExpiry::toolClosed()) {
            return response()->view('tools.cyberx-interview-closed', [], 410);
        }

        $path = public_path('audio/cyberx-interview-2026/data.json');

        abort_unless(is_file($path), 500, 'ملف بيانات المقابلة غير موجود.');

        $data = json_decode(file_get_contents($path), true);

        abort_if(
            json_last_error() !== JSON_ERROR_NONE,
            500,
            'ملف بيانات المقابلة تالف: '.json_last_error_msg()
        );

        // الضيف اللي انتهت مهلة رابطه (10 أيام بدون فتح ولا إجابة) ينشال من الأداة.
        // ما نرسل بياناته للصفحة أصلًا (لا تبويب ولا أسئلة ولا رسالة).
        foreach (['priority_guests', 'additional_guests'] as $group) {
            $data[$group] = array_values(array_filter(
                $data[$group] ?? [],
                fn (array $guest) => ! (isset($guest['id']) && GuestLinkExpiry::isExpired($guest['id']))
            ));
        }

        return view('tools.cyberx-interview', ['data' => $data]);
    }

    /**
     * إجابة ضيف مسجّلة من الأداة: تنحفظ على السيرفر (نسخة ما تضيع لو
     * انسكرت الصفحة) وتوصل لسالم بالواتساب المجاني مع السؤال ورابط
     * الإجابة -- الجسر يرسل نص بس، فالصوت يوصل كرابط يتشغّل مباشرة.
     *
     * الضيوف خبراء أمن وممكن يجرّبون الأداة: اسم الضيف ونص السؤال
     * ياخذهم السيرفر من data.json حسب question_id (ما نثق بنص من المتصفح
     * يوصل لواتساب سالم)، وبيانات الجهاز تنحفظ بملف خاص غير عام.
     */
    public function storeAnswer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'audio' => ['required', 'file', 'max:51200'],
            'question_id' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $guestId = strtok($data['question_id'], '-') ?: '';
        if ($guestId !== '' && GuestLinkExpiry::isExpired($guestId)) {
            return response()->json(['success' => false, 'message' => 'انتهت صلاحية رابط المقابلة.'], 410);
        }

        $file = $request->file('audio');
        // النوع من محتوى الملف نفسه (finfo) -- مب من الاسم ولا من اللي يقوله المتصفح.
        $mime = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()));
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
        [$guest, $question] = $this->lookupQuestion($data['question_id']);
        [$class, $reasons] = VisitClassifier::classify($request, isPageView: false);
        $device = DeviceSummary::describe($request->userAgent());
        $country = $this->country($request);

        // سجل الجهاز بمكان خاص (مب public) -- فيه العنوان والـ user agent كامل.
        Storage::disk('local')->put('cyberx-answers-meta/'.$now->format('Y-m-d').'/'.$name.'.json', json_encode([
            'file' => $path,
            'question_id' => $data['question_id'],
            'received_at' => $now->toIso8601String(),
            'ip' => $request->ip(),
            'proxy_ip' => $request->server('REMOTE_ADDR'),
            'country' => $country,
            'user_agent' => $request->userAgent(),
            'device' => $device,
            'accept_language' => $request->header('Accept-Language'),
            'mime' => $mime,
            'size_bytes' => $file->getSize(),
            'traffic_class' => $class,
            'class_reasons' => $reasons,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $lines = ['🎙 إجابة جديدة — CyberX Oman 2026'];
        $lines[] = 'الضيف: '.($guest ?? '⚠️ غير معروف ('.$data['question_id'].')');

        if ($question !== null) {
            $lines[] = 'السؤال: '.$question;
        }

        $lines[] = '📱 '.$device.($country ? ' — '.$country : '');

        if ($class !== VisitClassifier::HUMAN && $class !== VisitClassifier::OWNER) {
            $lines[] = '⚠️ ملاحظة: '.implode('، ', $reasons ?: [$class]);
        }

        $lines[] = '';
        $lines[] = '🔗 '.$url;

        $this->notifyWhatsApp(implode("\n", $lines));

        return response()->json(['success' => true, 'url' => $url]);
    }

    /**
     * question_id = «<الضيف>-<معرّف السؤال>» كما تبنيه الأداة. نرجّع اسم الضيف
     * ونص السؤال من data.json، أو null لو ما انعرف.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function lookupQuestion(string $questionId): array
    {
        $data = json_decode((string) @file_get_contents(public_path('audio/cyberx-interview-2026/data.json')), true) ?: [];

        $guests = collect(array_merge($data['priority_guests'] ?? [], $data['additional_guests'] ?? []));
        $guest = $guests->first(fn ($g) => str_starts_with($questionId, $g['id'].'-'));
        $prefix = $guest['id'] ?? (str_starts_with($questionId, 'panel-') ? 'panel' : null);
        $rest = $prefix ? substr($questionId, strlen($prefix) + 1) : $questionId;

        $guestName = $guest['name_ar'] ?? ($prefix === 'panel' ? 'سؤال الجلسة القيادية' : null);

        if ($rest === 'panel_question') {
            return [$guestName, $data['panel_question']['ar'] ?? null];
        }

        if ($guest && $rest === $guest['id'].'-last') {
            // ضيف من خارج عُمان له سؤال أخير مختلف (final_question: "foreign").
            $rest = ($guest['final_question'] ?? null) === 'foreign' ? 'shared-last-foreign' : 'shared-last';
        }

        // كل بطاقة سؤال بالملف (أسئلة الضيوف، الأزرار السريعة، المتابعات) معرّفها = audio.
        $questions = [];
        $collect = function ($node) use (&$collect, &$questions) {
            if (! is_array($node)) {
                return;
            }

            if (isset($node['audio'], $node['ar']) && is_string($node['audio'])) {
                $questions[$node['audio']] ??= $node['ar'];
            }

            array_map($collect, $node);
        };
        $collect($data);

        return [$guestName, $questions[$rest] ?? null];
    }

    /**
     * البلد من Cloudflare (موثوق فقط لو الطلب جاي منها فعلًا).
     */
    private function country(Request $request): ?string
    {
        $code = strtoupper((string) $request->header('CF-IPCountry'));

        if (! CloudflareIps::contains($request->server('REMOTE_ADDR')) || ! preg_match('/^[A-Z]{2}$/', $code) || in_array($code, ['XX', 'T1'], true)) {
            return null;
        }

        return (locale_get_display_region('-'.$code, 'ar') ?: $code).' ('.$code.')';
    }
}

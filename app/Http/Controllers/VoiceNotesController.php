<?php

namespace App\Http\Controllers;

use App\Models\VoiceDictionaryEntry;
use App\Models\VoiceNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * «صوتي»: صفحة خاصة لسالم (رابط سري HMAC من APP_KEY). يسجّل، والتسجيل
 * ينحفظ بجهازه لين يوصل، والماك يحوّله لنص (VoiceAgentController). لما
 * يعدّل النص، الصفحة تقترح التصحيحات وتنحفظ بقاموسه الشخصي.
 */
class VoiceNotesController extends Controller
{
    private const DISK = 'local';

    private const MAX_UPLOAD_KB = 50 * 1024;

    private const AUDIO_EXTENSIONS = [
        'audio/webm' => 'webm', 'video/webm' => 'webm',
        'audio/mp4' => 'm4a', 'video/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'video/quicktime' => 'mov',
        'audio/aac' => 'aac', 'audio/ogg' => 'ogg', 'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/flac' => 'flac', 'audio/x-caf' => 'caf',
    ];

    public static function token(): string
    {
        return substr(hash_hmac('sha256', 'voice-notes:owner', (string) config('app.key')), 0, 24);
    }

    public function show(string $token): View
    {
        $this->guard($token);

        return view('tools.voice-notes', ['token' => $token]);
    }

    public function index(string $token): JsonResponse
    {
        $this->guard($token);

        return response()->json([
            'notes' => VoiceNote::latest()->limit(30)->get()->map->toPublic(),
            'training' => ['minutes' => VoiceNote::trainingMinutes(), 'goal' => 30, 'notes' => VoiceNote::whereNotNull('approved_at')->count()],
            'dictionary' => VoiceDictionaryEntry::orderByDesc('updated_at')->limit(300)
                ->get(['id', 'kind', 'language', 'wrong', 'right', 'uses']),
        ]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        $this->guard($token);
        $input = $request->validate([
            'audio' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB],
            'take_id' => ['required', 'string', 'regex:/^[A-Za-z0-9-]{8,64}$/'],
            'language' => ['nullable', 'in:ar,en'],
        ]);

        // نفس التسجيل وصل قبل (إعادة إرسال بعد انقطاع): نرجّع نفس الملاحظة.
        if ($existing = VoiceNote::where('uuid', $input['take_id'])->first()) {
            return response()->json(['success' => true, 'note' => $existing->toPublic(), 'duplicate' => true]);
        }

        $file = $request->file('audio');
        $mime = strtolower((string) (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()));
        $extension = self::AUDIO_EXTENSIONS[$mime] ?? null;
        if ($extension === null) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم ('.$mime.').'], 422);
        }

        $path = $file->storeAs('voice-notes/'.now('Asia/Muscat')->format('Y-m'), $input['take_id'].'.'.$extension, self::DISK);
        abort_if($path === false, 500, 'تعذّر حفظ التسجيل.');

        $note = VoiceNote::firstOrCreate(['uuid' => $input['take_id']], [
            'status' => VoiceNote::QUEUED,
            'language' => $input['language'] ?? 'ar',
            'original_path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
        ]);

        return response()->json(['success' => true, 'note' => $note->toPublic()]);
    }

    public function audio(string $token, string $uuid): StreamedResponse
    {
        $this->guard($token);
        $note = VoiceNote::where('uuid', $uuid)->firstOrFail();
        abort_unless(Storage::disk(self::DISK)->exists($note->original_path), 404);

        return Storage::disk(self::DISK)->response($note->original_path, null, ['Content-Type' => $note->mime_type ?: 'application/octet-stream']);
    }

    /** يحفظ النص بعد التعديل، ويتعلّم التصحيحات اللي وافق عليها سالم. */
    public function saveText(Request $request, string $token, string $uuid): JsonResponse
    {
        $this->guard($token);
        $note = VoiceNote::where('uuid', $uuid)->firstOrFail();
        $input = $request->validate([
            'text' => ['required', 'string', 'max:100000'],
            'learn' => ['array', 'max:50'],
            'learn.*.wrong' => ['required', 'string', 'max:120'],
            'learn.*.right' => ['required', 'string', 'max:120'],
        ]);

        // حفظ النص = سالم أكّد إنه صحيح، فيصير درس (صوته + النص الصحيح) لتدريب النموذج.
        $note->update(['edited_text' => $input['text'], 'approved_at' => now()]);
        $learned = [];
        foreach ($input['learn'] ?? [] as $pair) {
            if ($entry = VoiceDictionaryEntry::remember(VoiceDictionaryEntry::CORRECTION, $note->language, $pair['wrong'], $pair['right'])) {
                $learned[] = $entry->only(['id', 'wrong', 'right']);
            }
        }

        return response()->json(['success' => true, 'learned' => count($learned), 'entries' => $learned, 'note' => $note->toPublic()]);
    }

    /** سالم يحذف ملاحظة: السجل والتسجيل من السيرفر (وتطلع من مادة التدريب). */
    public function destroy(string $token, string $uuid): JsonResponse
    {
        $this->guard($token);
        $note = VoiceNote::where('uuid', $uuid)->firstOrFail();
        Storage::disk(self::DISK)->delete($note->original_path);
        $note->delete();

        return response()->json(['success' => true]);
    }

    public function retry(string $token, string $uuid): JsonResponse
    {
        $this->guard($token);
        $note = VoiceNote::where('uuid', $uuid)->firstOrFail();
        $note->update(['status' => VoiceNote::QUEUED, 'error' => null, 'claimed_at' => null]);

        return response()->json(['success' => true, 'note' => $note->toPublic()]);
    }

    public function addTerm(Request $request, string $token): JsonResponse
    {
        $this->guard($token);
        $input = $request->validate([
            'kind' => ['required', 'in:term,correction'],
            'language' => ['required', 'in:ar,en'],
            'wrong' => ['nullable', 'string', 'max:120'],
            'right' => ['required', 'string', 'max:120'],
        ]);

        $entry = VoiceDictionaryEntry::remember($input['kind'], $input['language'], (string) ($input['wrong'] ?? ''), $input['right']);
        abort_if($entry === null, 422, 'المدخل فاضي أو التصحيح نفس الكلمة.');

        return response()->json(['success' => true, 'entry' => $entry->only(['id', 'kind', 'language', 'wrong', 'right', 'uses'])]);
    }

    public function removeTerm(string $token, VoiceDictionaryEntry $entry): JsonResponse
    {
        $this->guard($token);
        $entry->delete();

        return response()->json(['success' => true]);
    }

    private function guard(string $token): void
    {
        abort_unless(strlen($token) === 24 && hash_equals(self::token(), $token), 404);
    }
}

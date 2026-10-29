<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoiceDictionaryEntry;
use App\Models\VoiceNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * طابور «صوتي» لبوت الماك (نفس توكن agent حق صحفي المؤتمر). كل ملاحظة تجي
 * معها كلمات القاموس (prompt) والتصحيحات عشان الماك يطبقها.
 */
class VoiceAgentController extends Controller
{
    private const STALE_MINUTES = 15;

    private const MAX_ATTEMPTS = 3;

    public function next(): JsonResponse
    {
        VoiceNote::where('status', VoiceNote::TRANSCRIBING)
            ->where('claimed_at', '<', now()->subMinutes(self::STALE_MINUTES))
            ->update(['status' => VoiceNote::QUEUED]);

        $note = VoiceNote::where('status', VoiceNote::QUEUED)->orderBy('created_at')->first();
        if ($note === null) {
            return response()->json(['note' => null]);
        }

        $claimed = VoiceNote::whereKey($note->id)->where('status', VoiceNote::QUEUED)->update([
            'status' => VoiceNote::TRANSCRIBING,
            'claimed_at' => now(),
            'attempts' => $note->attempts + 1,
        ]);
        if ($claimed !== 1) {
            return response()->json(['note' => null]);
        }

        return response()->json(['note' => [
            'id' => $note->uuid,
            'language' => $note->language,
            'mime_type' => $note->mime_type,
            'audio_url' => '/'.$note->uuid.'/audio',
            'prompt' => VoiceDictionaryEntry::promptFor($note->language),
            'corrections' => VoiceDictionaryEntry::correctionsFor($note->language),
        ]]);
    }

    /** دروس التدريب: كل ملاحظة أكّد سالم نصها (الصوت يتحمّل من audio). */
    public function dataset(): JsonResponse
    {
        return response()->json(['items' => VoiceNote::whereNotNull('approved_at')->orderBy('id')->get()
            ->map(fn (VoiceNote $n) => ['id' => $n->uuid, 'language' => $n->language, 'duration' => $n->duration_seconds, 'text' => $n->edited_text, 'approved_at' => $n->approved_at?->toIso8601String()])]);
    }

    public function audio(string $uuid): StreamedResponse
    {
        $note = VoiceNote::where('uuid', $uuid)->firstOrFail();
        abort_unless(Storage::disk('local')->exists($note->original_path), 404);

        return Storage::disk('local')->download($note->original_path, basename($note->original_path), [
            'Content-Type' => $note->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function complete(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate([
            'transcript' => ['required', 'string'],
            'transcript_raw' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'numeric'],
        ]);

        VoiceNote::where('uuid', $uuid)->firstOrFail()->update([
            'status' => VoiceNote::DONE,
            'transcript' => $data['transcript'],
            'transcript_raw' => $data['transcript_raw'] ?? $data['transcript'],
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'error' => null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function fail(Request $request, string $uuid): JsonResponse
    {
        $data = $request->validate(['error' => ['required', 'string']]);
        $note = VoiceNote::where('uuid', $uuid)->firstOrFail();
        $note->update([
            'status' => $note->attempts >= self::MAX_ATTEMPTS ? VoiceNote::FAILED : VoiceNote::QUEUED,
            'error' => mb_substr($data['error'], 0, 2000),
        ]);

        return response()->json(['ok' => true, 'status' => $note->status]);
    }
}

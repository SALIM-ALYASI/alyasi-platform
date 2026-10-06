<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConferenceRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * صحفي المؤتمر (ConferenceJournalist) -- صفحة /cj ترفع التسجيل هنا لأن
 * سيرفر البيت ما له رابط عام. مرحّل على سيرفر البيت يسحب الجديد (اتصال صادر
 * فقط) ويسلّمه لـ Server Bot، ثم يرجّع رقم الـ Job وحالتها عشان الصفحة تعرضها.
 */
class ConferenceRecordingController extends Controller
{
    private const DISK = 'local';

    private const DIRECTORY = 'conference-journalist/original';

    private const PREPARED_DIRECTORY = 'conference-journalist/prepared';

    private const SHARED_DIRECTORY = 'conference-journalist/shared';

    private const ALLOWED_EXTENSIONS = ['webm', 'm4a', 'mp4', 'wav', 'mp3', 'ogg', 'aac', 'caf'];

    private const MAX_UPLOAD_KB = 500 * 1024;

    private const TRACKING_DAYS = 7;

    // تسجيل انقطع (صفحة انقفلت أو بطارية خلصت) يتحوّل بالجزء اللي وصل.
    private const ABANDONED_MINUTES = 10;

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB],
        ]);

        $file = $request->file('audio');
        abort_if($file->getSize() === 0, 422, 'Recording is empty');
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'webm';
        }

        $relayId = 'CJR-'.now('UTC')->format('Ymd-His').'-'.Str::upper(Str::random(4));
        $path = $file->storeAs(self::DIRECTORY, "{$relayId}.{$extension}", self::DISK);

        $recording = ConferenceRecording::create([
            'relay_id' => $relayId,
            'status' => ConferenceRecording::STATUS_RECEIVED,
            'original_path' => $path,
            'original_filename' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime_type' => Str::limit((string) $file->getClientMimeType(), 100, ''),
            'size_bytes' => $file->getSize(),
        ]);

        return response()->json([
            'ok' => true,
            'relay_id' => $recording->relay_id,
            'status' => $recording->status,
        ]);
    }

    /**
     * رفع على دفعات: الصفحة تفتح تسجيل، ترسل جزء كل 30 ثانية أثناء التسجيل،
     * ثم تقفله. أجزاء MediaRecorder تتجمع بالترتيب في ملف واحد صالح، فأي
     * انقطاع يضيّع آخر جزء بس. upload_key يمنع أحد غير الصفحة يضيف للتسجيل.
     */
    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'extension' => ['nullable', 'string', 'max:10'],
            'mime_type' => ['nullable', 'string', 'max:100'],
        ]);

        $extension = strtolower($data['extension'] ?? '');
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'm4a';
        }

        $relayId = 'CJR-'.now('UTC')->format('Ymd-His').'-'.Str::upper(Str::random(4));
        $path = self::DIRECTORY."/{$relayId}.{$extension}";
        Storage::disk(self::DISK)->put($path, '');

        $recording = ConferenceRecording::create([
            'relay_id' => $relayId,
            'upload_key' => Str::random(40),
            'status' => ConferenceRecording::STATUS_RECORDING,
            'original_path' => $path,
            'original_filename' => "{$relayId}.{$extension}",
            'mime_type' => $data['mime_type'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'relay_id' => $recording->relay_id,
            'upload_key' => $recording->upload_key,
        ]);
    }

    public function chunk(Request $request, string $relayId): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string'],
            'seq' => ['required', 'integer', 'min:0'],
            // iOS Safari may ignore the 30 s timeslice and hand over the whole
            // recording as one chunk on stop, so a chunk can be the full size.
            'chunk' => ['required', 'file', 'max:'.self::MAX_UPLOAD_KB],
        ]);

        $recording = $this->ownedRecording($request, $relayId);
        $seq = (int) $request->input('seq');

        // إعادة إرسال جزء وصل قبل (الرد ضاع بالشبكة) -- نتجاهله.
        if ($seq < $recording->chunks_received) {
            return response()->json(['ok' => true, 'next_seq' => $recording->chunks_received]);
        }
        if ($seq > $recording->chunks_received) {
            return response()->json(['ok' => false, 'next_seq' => $recording->chunks_received], 409);
        }
        abort_unless($recording->status === ConferenceRecording::STATUS_RECORDING, 409, 'Recording is closed');

        $size = $request->file('chunk')->getSize();
        abort_if($recording->size_bytes + $size > self::MAX_UPLOAD_KB * 1024, 413, 'Recording too large');

        $input = fopen($request->file('chunk')->getRealPath(), 'rb');
        $output = fopen(Storage::disk(self::DISK)->path($recording->original_path), 'ab');
        stream_copy_to_stream($input, $output);
        fclose($input);
        fclose($output);

        $recording->update([
            'chunks_received' => $seq + 1,
            'size_bytes' => $recording->size_bytes + $size,
        ]);

        return response()->json(['ok' => true, 'next_seq' => $seq + 1]);
    }

    public function finish(Request $request, string $relayId): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'chunks' => ['required', 'integer', 'min:0'],
        ]);

        $recording = $this->ownedRecording($request, $relayId);

        if ($recording->status === ConferenceRecording::STATUS_RECORDING) {
            abort_if($data['chunks'] > $recording->chunks_received, 409, 'Missing chunks');
            abort_if($recording->chunks_received === 0, 422, 'Recording is empty');
            $recording->update(['status' => ConferenceRecording::STATUS_RECEIVED]);
        }

        return response()->json([
            'ok' => true,
            'relay_id' => $recording->relay_id,
            'status' => $recording->status,
        ]);
    }

    /**
     * ملف مشارك عبر رابط موقّع مؤقت (URL::temporarySignedRoute) -- الصوت
     * والنص يوصلون بالإيميل كروابط لأن المرفقات تتجاوز حد Gmail.
     */
    public function sharedFile(string $name): StreamedResponse
    {
        $path = self::SHARED_DIRECTORY.'/'.basename($name);
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->download($path, basename($name), [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function ownedRecording(Request $request, string $relayId): ConferenceRecording
    {
        $recording = ConferenceRecording::where('relay_id', $relayId)->firstOrFail();
        abort_unless(
            $recording->upload_key && hash_equals($recording->upload_key, (string) $request->input('key')),
            403,
        );

        return $recording;
    }

    public function status(string $relayId): JsonResponse
    {
        $recording = ConferenceRecording::where('relay_id', $relayId)->firstOrFail();

        return response()->json([
            'relay_id' => $recording->relay_id,
            'status' => $recording->status,
            'job_id' => $recording->home_job_id,
            'job_status' => $recording->displayStatus(),
            'error' => $recording->job_error,
        ]);
    }

    public function pending(): JsonResponse
    {
        ConferenceRecording::where('status', ConferenceRecording::STATUS_RECORDING)
            ->where('updated_at', '<', now()->subMinutes(self::ABANDONED_MINUTES))
            ->where('chunks_received', '>', 0)
            ->update(['status' => ConferenceRecording::STATUS_RECEIVED]);

        $pending = ConferenceRecording::where('status', ConferenceRecording::STATUS_RECEIVED)
            ->orderBy('created_at')
            ->limit(10)
            ->get()
            ->map(fn (ConferenceRecording $recording) => [
                'id' => $recording->relay_id,
                'filename' => $recording->original_filename ?: basename($recording->original_path),
                'extension' => pathinfo($recording->original_path, PATHINFO_EXTENSION),
                'mime_type' => $recording->mime_type,
                'size_bytes' => $recording->size_bytes,
            ]);

        $tracking = ConferenceRecording::where('status', ConferenceRecording::STATUS_FORWARDED)
            ->where(fn ($query) => $query
                ->whereNull('job_status')
                ->orWhereNotIn('job_status', ConferenceRecording::FINAL_JOB_STATUSES))
            ->where('created_at', '>=', now()->subDays(self::TRACKING_DAYS))
            ->get(['relay_id', 'home_job_id'])
            ->map(fn (ConferenceRecording $recording) => [
                'id' => $recording->relay_id,
                'job_id' => $recording->home_job_id,
            ]);

        // نصوص خلّصها الماك وما وصلت لسيرفر البيت بعد.
        $transcripts = ConferenceRecording::where('agent_status', ConferenceRecording::AGENT_COMPLETED)
            ->where('transcript_synced', false)
            ->limit(10)
            ->get()
            ->map(fn (ConferenceRecording $recording) => [
                'id' => $recording->relay_id,
                'job_id' => $recording->home_job_id,
                'transcript' => $recording->transcript,
                'language' => $recording->transcript_language,
                'model' => $recording->transcript_model,
                'duration_seconds' => $recording->duration_seconds,
            ]);

        return response()->json([
            'pending' => $pending,
            'tracking' => $tracking,
            'transcripts' => $transcripts,
        ]);
    }

    public function audio(string $relayId): StreamedResponse
    {
        $recording = ConferenceRecording::where('relay_id', $relayId)->firstOrFail();

        abort_unless(Storage::disk(self::DISK)->exists($recording->original_path), 404, 'Original audio not found');

        return Storage::disk(self::DISK)->download(
            $recording->original_path,
            basename($recording->original_path),
            ['Content-Type' => $recording->mime_type ?: 'application/octet-stream'],
        );
    }

    public function forwarded(Request $request, string $relayId): JsonResponse
    {
        $data = $request->validate([
            'job_id' => ['required', 'string', 'max:40'],
        ]);

        $recording = ConferenceRecording::where('relay_id', $relayId)->firstOrFail();

        $recording->update([
            'status' => ConferenceRecording::STATUS_FORWARDED,
            'home_job_id' => $data['job_id'],
            'job_status' => $recording->job_status ?? 'processing',
            'forwarded_at' => $recording->forwarded_at ?? now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function prepared(Request $request, string $relayId): JsonResponse
    {
        $request->validate([
            'audio' => ['required', 'file', 'max:'.(1024 * 1024)],
        ]);

        $recording = ConferenceRecording::where('relay_id', $relayId)->firstOrFail();
        abort_if(blank($recording->home_job_id), 409, 'Recording has not been forwarded yet');

        $path = $request->file('audio')->storeAs(
            self::PREPARED_DIRECTORY,
            "{$recording->home_job_id}.wav",
            self::DISK,
        );

        $recording->update([
            'prepared_path' => $path,
            'agent_status' => $recording->agent_status ?? ConferenceRecording::AGENT_READY,
        ]);

        return response()->json(['ok' => true, 'agent_status' => $recording->agent_status]);
    }

    public function transcriptSynced(string $relayId): JsonResponse
    {
        ConferenceRecording::where('relay_id', $relayId)->firstOrFail()
            ->update(['transcript_synced' => true]);

        return response()->json(['ok' => true]);
    }

    public function syncStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'updates' => ['required', 'array', 'max:100'],
            'updates.*.id' => ['required', 'string', 'max:40'],
            'updates.*.job_status' => ['required', 'string', 'max:20'],
            'updates.*.error' => ['nullable', 'string', 'max:2000'],
        ]);

        foreach ($data['updates'] as $update) {
            ConferenceRecording::where('relay_id', $update['id'])->update([
                'job_status' => $update['job_status'],
                'job_error' => $update['error'] ?? null,
            ]);
        }

        return response()->json(['ok' => true, 'updated' => count($data['updates'])]);
    }
}

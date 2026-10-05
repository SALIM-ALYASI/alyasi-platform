<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConferenceRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * طابور الماك لصحفي المؤتمر -- نفس عقد Server Bot (/api/jobs/next ...) عشان
 * الماك يشتغل من أي شبكة بمجرد تغيير SERVER_URL، بدون ما يوصل لسيرفر البيت.
 * رقم الـ Job هو رقم سيرفر البيت (home_job_id) عشان ملفات الماك تبقى بنفس الاسم.
 */
class ConferenceAgentController extends Controller
{
    private const DISK = 'local';

    private const STALE_MINUTES = 30;

    public function next(): JsonResponse
    {
        // Job عالقة عند ماك توقف -- ترجع للطابور.
        ConferenceRecording::where('agent_status', ConferenceRecording::AGENT_TRANSCRIBING)
            ->where('agent_claimed_at', '<', now()->subMinutes(self::STALE_MINUTES))
            ->update(['agent_status' => ConferenceRecording::AGENT_READY]);

        $candidate = ConferenceRecording::where('agent_status', ConferenceRecording::AGENT_READY)
            ->orderBy('created_at')
            ->first();

        if ($candidate === null) {
            return response()->json(['job' => null]);
        }

        // تحديث مشروط: وكيلان يطلبون بنفس اللحظة، واحد بس ياخذها.
        $claimed = ConferenceRecording::where('id', $candidate->id)
            ->where('agent_status', ConferenceRecording::AGENT_READY)
            ->update([
                'agent_status' => ConferenceRecording::AGENT_TRANSCRIBING,
                'agent_claimed_at' => now(),
            ]);

        if ($claimed !== 1) {
            return response()->json(['job' => null]);
        }

        return response()->json([
            'job' => [
                'id' => $candidate->home_job_id,
                'created_at' => $candidate->created_at?->toIso8601String(),
                'mime_type' => 'audio/wav',
                'audio_url' => "/api/jobs/{$candidate->home_job_id}/audio",
            ],
        ]);
    }

    public function audio(string $jobId): StreamedResponse
    {
        $recording = $this->findJob($jobId);

        abort_unless($recording->agent_status === ConferenceRecording::AGENT_TRANSCRIBING, 409, "Job status is {$recording->agent_status}");
        abort_unless(
            $recording->prepared_path && Storage::disk(self::DISK)->exists($recording->prepared_path),
            404,
            'Prepared audio not found',
        );

        return Storage::disk(self::DISK)->download($recording->prepared_path, "{$jobId}.wav", [
            'Content-Type' => 'audio/wav',
        ]);
    }

    public function complete(Request $request, string $jobId): JsonResponse
    {
        $data = $request->validate([
            'transcript' => ['required', 'string'],
            'language' => ['nullable', 'string', 'max:10'],
            'model' => ['nullable', 'string', 'max:200'],
            'duration_seconds' => ['nullable', 'numeric'],
        ]);

        $this->findJob($jobId)->update([
            'agent_status' => ConferenceRecording::AGENT_COMPLETED,
            'transcript' => $data['transcript'],
            'transcript_language' => $data['language'] ?? null,
            'transcript_model' => $data['model'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? null,
            'transcript_synced' => false,
            'job_error' => null,
        ]);

        return response()->json(['ok' => true, 'job_id' => $jobId, 'status' => 'completed']);
    }

    public function fail(Request $request, string $jobId): JsonResponse
    {
        $data = $request->validate([
            'error' => ['required', 'string'],
        ]);

        $this->findJob($jobId)->update([
            'agent_status' => ConferenceRecording::AGENT_READY,
            'job_error' => mb_substr($data['error'], 0, 2000),
        ]);

        return response()->json(['ok' => true, 'job_id' => $jobId, 'status' => 'ready']);
    }

    private function findJob(string $jobId): ConferenceRecording
    {
        return ConferenceRecording::where('home_job_id', $jobId)->firstOrFail();
    }
}

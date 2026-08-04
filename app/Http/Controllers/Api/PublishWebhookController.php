<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublishJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublishWebhookController extends Controller
{
    /**
     * استقبال تحديث حالة نشر منصة واحدة من خدمة النشر (Node) فور اكتمالها.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_id' => ['required', 'string'],
            'platform' => ['required', 'string', 'in:instagram,linkedin,youtube,tiktok,telegram'],
            'status' => ['required', 'string', 'in:publishing,done,failed'],
            'result' => ['nullable', 'array'],
            'error' => ['nullable', 'string'],
        ]);

        $job = PublishJob::query()
            ->where('job_id', $validated['job_id'])
            ->first();

        if (! $job) {
            return response()->json(['success' => false, 'message' => 'job not found'], 404);
        }

        $job->updatePlatform($validated['platform'], [
            'status' => $validated['status'],
            'result' => $validated['result'] ?? null,
            'error' => $validated['error'] ?? null,
            'finishedAt' => now()->toIso8601String(),
        ]);

        return response()->json(['success' => true]);
    }
}

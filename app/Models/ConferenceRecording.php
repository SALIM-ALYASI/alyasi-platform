<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * تسجيل صوتي من صفحة صحفي المؤتمر (/cj) -- يبقى هنا لين يسحبه مرحّل سيرفر
 * البيت ويسلّمه لـ Server Bot (ConferenceJournalist). الملف الأصلي ما ينحذف.
 *
 * بعد ما يجهّز سيرفر البيت الصوت يرفعه هنا (prepared_path)، والماك يسحبه من
 * هنا عشان يشتغل من أي شبكة (agent_status)، ثم المرحّل يرجّع النص لسيرفر البيت.
 */
class ConferenceRecording extends Model
{
    public const STATUS_RECORDING = 'recording';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_FORWARDED = 'forwarded';

    public const FINAL_JOB_STATUSES = ['completed', 'failed'];

    public const AGENT_READY = 'ready';

    public const AGENT_TRANSCRIBING = 'transcribing';

    public const AGENT_COMPLETED = 'completed';

    protected $fillable = [
        'relay_id',
        'upload_key',
        'status',
        'original_path',
        'prepared_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'chunks_received',
        'home_job_id',
        'job_status',
        'job_error',
        'agent_status',
        'agent_claimed_at',
        'transcript',
        'transcript_language',
        'transcript_model',
        'duration_seconds',
        'transcript_synced',
        'forwarded_at',
    ];

    protected $hidden = ['upload_key'];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'chunks_received' => 'integer',
            'duration_seconds' => 'float',
            'transcript_synced' => 'boolean',
            'agent_claimed_at' => 'datetime',
            'forwarded_at' => 'datetime',
        ];
    }

    /**
     * الحالة اللي تشوفها صفحة التسجيل: مرحلة الماك إذا بدأت، وإلا مرحلة سيرفر البيت.
     */
    public function displayStatus(): ?string
    {
        return $this->agent_status ?? $this->job_status;
    }
}

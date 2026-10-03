<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * تسجيل صوتي من صفحة صحفي المؤتمر (/cj) -- يبقى هنا لين يسحبه مرحّل سيرفر
 * البيت ويسلّمه لـ Server Bot (ConferenceJournalist). الملف الأصلي ما ينحذف.
 */
class ConferenceRecording extends Model
{
    public const STATUS_RECEIVED = 'received';

    public const STATUS_FORWARDED = 'forwarded';

    public const FINAL_JOB_STATUSES = ['completed', 'failed'];

    protected $fillable = [
        'relay_id',
        'status',
        'original_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'home_job_id',
        'job_status',
        'job_error',
        'forwarded_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'forwarded_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** ملاحظة صوتية من صفحة «صوتي»، يحوّلها بوت الماك لنص. */
class VoiceNote extends Model
{
    public const QUEUED = 'queued';

    public const TRANSCRIBING = 'transcribing';

    public const DONE = 'done';

    public const FAILED = 'failed';

    protected $fillable = [
        'uuid', 'status', 'language', 'original_path', 'mime_type', 'size_bytes', 'duration_seconds',
        'transcript_raw', 'transcript', 'edited_text', 'approved_at', 'claimed_at', 'attempts', 'error',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'duration_seconds' => 'float',
            'claimed_at' => 'datetime',
            'approved_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /** دقائق صوت سالم مع نص أكّده (مادة تدريب النموذج على صوته). */
    public static function trainingMinutes(): float
    {
        return round(((float) self::whereNotNull('approved_at')->sum('duration_seconds')) / 60, 1);
    }

    /** اللي يشوفه سالم بالصفحة. */
    public function toPublic(): array
    {
        return [
            'id' => $this->uuid,
            'status' => $this->status,
            'language' => $this->language,
            'duration' => $this->duration_seconds,
            'text' => $this->edited_text ?? $this->transcript,
            'machine_text' => $this->transcript,
            'edited' => $this->edited_text !== null,
            'approved' => $this->approved_at !== null,
            'error' => $this->status === self::FAILED ? $this->error : null,
            'created_at' => $this->created_at?->timezone('Asia/Muscat')->format('Y-m-d H:i'),
        ];
    }
}

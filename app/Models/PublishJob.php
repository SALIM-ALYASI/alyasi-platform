<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishJob extends Model
{
    protected $fillable = [
        'job_id',
        'source',
        'status',
        'title',
        'text',
        'platforms',
        'smart_content_data',
    ];

    protected $casts = [
        'platforms' => 'array',
        'smart_content_data' => 'array',
    ];

    /**
     * تحديث حالة منصة واحدة داخل عمود platforms (JSON) عند وصول تحديث من خدمة النشر.
     */
    public function updatePlatform(string $platform, array $data): void
    {
        $platforms = $this->platforms ?? [];

        $platforms[$platform] = array_merge($platforms[$platform] ?? [], $data);

        $this->update(['platforms' => $platforms]);
    }
}

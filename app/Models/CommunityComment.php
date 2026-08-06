<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityComment extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'community_post_id',
        'name',
        'email',
        'body',
        'followed_confirmation',
        'status',
        'ip_address',
        'device_token',
    ];

    /**
     * التحويلات.
     */
    protected $casts = [
        'followed_confirmation' => 'boolean',
    ];

    /**
     * منشور المجتمع المرتبط.
     */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class);
    }

    /**
     * التعليقات المعتمدة فقط.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * التعليقات بانتظار المراجعة.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * التعليقات المطابقة لمعرّف جهاز أو IP معيّن.
     */
    public function scopeMatching(
        Builder $query,
        ?string $deviceToken,
        ?string $ipAddress
    ): Builder {
        return $query->where(function (Builder $query) use ($deviceToken, $ipAddress) {
            if ($deviceToken) {
                $query->orWhere('device_token', $deviceToken);
            }

            if ($ipAddress) {
                $query->orWhere('ip_address', $ipAddress);
            }
        });
    }
}

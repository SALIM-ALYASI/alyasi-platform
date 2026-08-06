<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'name',
        'email',
        'rating',
        'body',
        'status',
        'ip_address',
        'device_token',
    ];

    /**
     * التحويلات.
     */
    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * العنصر المُقيَّم (خدمة أو عمل).
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * التقييمات المعتمدة فقط.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * التقييمات بانتظار المراجعة.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * التقييمات المطابقة لمعرّف جهاز أو IP معيّن.
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

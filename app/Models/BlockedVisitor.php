<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlockedVisitor extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'device_token',
        'ip_address',
        'reason',
    ];

    /**
     * تحقق إن كان معرّف الجهاز أو الـ IP محظورًا.
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'ip_address',
        'device_token',
    ];

    /**
     * الرسائل الجديدة (غير المقروءة).
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }
}

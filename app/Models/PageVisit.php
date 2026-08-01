<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'path',
        'ip_address',
        'country_code',
        'country_name',
        'user_agent',
    ];

    /**
     * الزيارات اللي تحدد بلدها فعليًا (استبعاد المجهولة/المحلية).
     */
    public function scopeWithKnownCountry(Builder $query): Builder
    {
        return $query->whereNotNull('country_name');
    }
}

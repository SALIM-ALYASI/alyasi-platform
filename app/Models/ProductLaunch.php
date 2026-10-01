<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductLaunch extends Model
{
    protected $fillable = [
        'company',
        'title',
        'link',
        'link_hash',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function scopeOfCompany(Builder $query, ?string $company): Builder
    {
        return $company ? $query->where('company', $company) : $query;
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }
}

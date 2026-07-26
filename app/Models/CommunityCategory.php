<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityCategory extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    /**
     * التحويلات.
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    /**
     * استخدام الـ Slug في روابط Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * منشورات التصنيف.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)
            ->ordered();
    }

    /**
     * التصنيفات النشطة.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * ترتيب التصنيفات.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
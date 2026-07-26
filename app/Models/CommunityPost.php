<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPost extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'community_category_id',
        'title',
        'slug',
        'short_description',
        'content',
        'type',
        'image',
        'location',
        'event_start_at',
        'event_end_at',
        'registration_url',
        'status',
        'sort_order',
        'is_featured',
        'is_active',
        'published_at',
    ];

    /**
     * التحويلات.
     */
    protected $casts = [
        'event_start_at' => 'datetime',
        'event_end_at'   => 'datetime',
        'published_at'   => 'datetime',

        'sort_order'  => 'integer',

        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
    ];

    /**
     * استخدام الـ Slug في روابط Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * التصنيف.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CommunityCategory::class);
    }

    /**
     * المنشورات النشطة.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * المنشورات المنشورة.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * المنشورات المميزة.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * ترتيب العرض.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
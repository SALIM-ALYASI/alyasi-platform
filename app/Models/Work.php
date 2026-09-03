<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'title',
        'slug',
        'type',
        'short_description',
        'outcome',
        'description',
        'cover_image',
        'client_name',
        'work_url',
        'completed_at',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'date',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(WorkImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * جميع التقييمات (Reviews) لهذا العمل.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * التقييمات المعتمدة فقط، من الأحدث للأقدم.
     */
    public function approvedReviews(): MorphMany
    {
        return $this->reviews()
            ->approved()
            ->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->latest('id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'website' => 'موقع إلكتروني',
            'design' => 'تصميم',
            'advertisement' => 'إعلان',
            'video' => 'فيديو ومونتاج',
            'photography' => 'تصوير',
            'event' => 'فعالية أو مشاركة',
            'personal' => 'عمل شخصي',
            default => 'أعمال أخرى',
        };
    }
}

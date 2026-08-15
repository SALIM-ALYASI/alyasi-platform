<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'sort_order',
        'is_active',
    ];

    /**
     * التحويلات.
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * استخدام الـ Slug في روابط Laravel.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * مقالات التصنيف.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)
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
            ->orderBy('name_ar');
    }

    public function getNameAttribute(): string
    {
        return $this->localizedValue('name') ?? '';
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->localizedValue('description');
    }

    private function localizedValue(string $field): ?string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $fallbackLocale = $locale === 'ar' ? 'en' : 'ar';

        return $this->getAttribute("{$field}_{$locale}")
            ?: $this->getAttribute("{$field}_{$fallbackLocale}");
    }
}

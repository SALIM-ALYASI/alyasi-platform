<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('name_ar');
    }

    public function getNameAttribute(): string
    {
        return $this->localizedValue('name');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->localizedValue('description');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    private function localizedValue(string $field): ?string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $fallbackLocale = $locale === 'ar' ? 'en' : 'ar';

        return $this->getAttribute("{$field}_{$locale}")
            ?: $this->getAttribute("{$field}_{$fallbackLocale}");
    }
}

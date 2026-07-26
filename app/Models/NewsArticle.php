<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsArticle extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'news_category_id',
        'title_ar',
        'title_en',
        'excerpt_ar',
        'excerpt_en',
        'content_ar',
        'content_en',
        'image',
        'image_alt_ar',
        'image_alt_en',
        'source_name',
        'source_url',
        'author_name',
        'status',
        'is_breaking',
        'is_featured',
        'published_at',
        'views_count',
        'seo_title_ar',
        'seo_title_en',
        'seo_description_ar',
        'seo_description_en',
    ];

    protected function casts(): array
    {
        return [
            'is_breaking' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            NewsCategory::class,
            'news_category_id'
        );
    }

    public function permalinks(): MorphMany
    {
        return $this->morphMany(
            Permalink::class,
            'linkable'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                self::STATUS_PUBLISHED,
                self::STATUS_SCHEDULED,
            ])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeBreaking(Builder $query): Builder
    {
        return $query->where('is_breaking', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Localized Accessors
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute(): string
    {
        return $this->localizedValue('title') ?? '';
    }

    public function getExcerptAttribute(): ?string
    {
        return $this->localizedValue('excerpt');
    }

    public function getContentAttribute(): ?string
    {
        return $this->localizedValue('content');
    }

    public function getImageAltAttribute(): string
    {
        return $this->localizedValue('image_alt')
            ?: $this->title;
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->localizedValue('seo_title')
            ?: $this->title;
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->localizedValue('seo_description')
            ?: $this->excerpt;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function registerView(): void
    {
        $this->increment('views_count');

        $this->views_count = (int) $this->views_count + 1;
    }

    private function localizedValue(string $field): ?string
    {
        $locale = app()->getLocale() === 'ar'
            ? 'ar'
            : 'en';

        $fallbackLocale = $locale === 'ar'
            ? 'en'
            : 'ar';

        $localizedValue = $this->getAttribute(
            "{$field}_{$locale}"
        );

        if (filled($localizedValue)) {
            return $localizedValue;
        }

        $fallbackValue = $this->getAttribute(
            "{$field}_{$fallbackLocale}"
        );

        return filled($fallbackValue)
            ? $fallbackValue
            : null;
    }
}


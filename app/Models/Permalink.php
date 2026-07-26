<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Permalink extends Model
{
    /**
     * الحقول المسموح بحفظها جماعيًا.
     */
    protected $fillable = [
        'linkable_type',
        'linkable_id',
        'locale',
        'slug',
    ];

    /**
     * تحويل أنواع الحقول.
     */
    protected function casts(): array
    {
        return [
            'linkable_id' => 'integer',
        ];
    }

    /**
     * العنصر المرتبط بالرابط الدائم.
     *
     * قد يكون:
     * Service
     * NewsArticle
     * Project
     * Page
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * جميع الروابط القديمة المرتبطة بهذا الرابط.
     */
    public function redirects(): HasMany
    {
        return $this->hasMany(
            PermalinkRedirect::class,
            'permalink_id'
        );
    }

    /**
     * جلب الروابط حسب اللغة.
     */
    public function scopeLocale(
        Builder $query,
        ?string $locale = null
    ): Builder {
        $locale ??= app()->getLocale();

        return $query->where(
            'locale',
            $locale
        );
    }

    /**
     * البحث عن رابط دائم حسب اللغة والـ slug.
     */
    public function scopeForSlug(
        Builder $query,
        string $slug,
        ?string $locale = null
    ): Builder {
        $locale ??= app()->getLocale();

        return $query
            ->where('locale', $locale)
            ->where('slug', $slug);
    }

    /**
     * إنشاء المسار النسبي للرابط.
     */
    public function path(): string
    {
        return route(
            'news.show',
            [
                'slug' => $this->slug,
            ],
            false
        );
    }

    /**
     * إنشاء الرابط الكامل.
     */
    public function url(): string
    {
        return route(
            'news.show',
            [
                'slug' => $this->slug,
            ]
        );
    }
}
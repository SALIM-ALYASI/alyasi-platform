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
        'locale',
        'slug',
    ];

    /**
     * العنصر المرتبط بالرابط الدائم.
     *
     * قد يكون:
     * Service
     * News
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
            PermalinkRedirect::class
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

        return $query->where('locale', $locale);
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
        return '/' . $this->locale . '/' . $this->slug;
    }

    /**
     * إنشاء الرابط الكامل.
     */
    public function url(): string
    {
        return url($this->path());
    }
}
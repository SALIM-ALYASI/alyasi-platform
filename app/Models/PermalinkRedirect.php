<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermalinkRedirect extends Model
{
    /**
     * الحقول المسموح بحفظها جماعيًا.
     */
    protected $fillable = [
        'permalink_id',
        'locale',
        'old_slug',
    ];

    /**
     * الرابط الدائم الحالي.
     */
    public function permalink(): BelongsTo
    {
        return $this->belongsTo(
            Permalink::class
        );
    }

    /**
     * البحث حسب اللغة.
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
     * البحث عن رابط قديم.
     */
    public function scopeForSlug(
        Builder $query,
        string $slug,
        ?string $locale = null
    ): Builder {
        $locale ??= app()->getLocale();

        return $query
            ->where('locale', $locale)
            ->where('old_slug', $slug);
    }

    /**
     * الرابط الحالي المرتبط بهذا التحويل.
     */
    public function currentUrl(): ?string
    {
        return $this->permalink?->url();
    }

    /**
     * المسار الحالي المرتبط بهذا التحويل.
     */
    public function currentPath(): ?string
    {
        return $this->permalink?->path();
    }
}
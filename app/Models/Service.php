<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    /**
     * الحقول المسموح بحفظها جماعيًا.
     */
    protected $fillable = [
        'title_ar',
        'description_ar',
        'title_en',
        'description_en',
        'image',
        'is_active',
    ];

    /**
     * تحويل أنواع الحقول تلقائيًا.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * جلب الخدمات النشطة فقط.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * ترتيب الخدمات من الأحدث إلى الأقدم.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('id');
    }

    /**
     * البحث في بيانات الخدمة العربية والإنجليزية.
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($search): void {
            $subQuery
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")
                ->orWhere('description_ar', 'like', "%{$search}%")
                ->orWhere('description_en', 'like', "%{$search}%");
        });
    }

    /**
     * جميع الروابط الدائمة التابعة للخدمة.
     */
    public function permalinks(): MorphMany
    {
        return $this->morphMany(
            Permalink::class,
            'linkable'
        );
    }

    /**
     * جلب الرابط الدائم حسب اللغة.
     */
    public function permalink(
        ?string $locale = null
    ): ?Permalink {
        $locale ??= app()->getLocale();

        return $this->permalinks()
            ->where('locale', $locale)
            ->first();
    }

    /**
     * جلب نص الرابط الدائم حسب اللغة.
     */
    public function slug(
        ?string $locale = null
    ): ?string {
        return $this->permalink($locale)?->slug;
    }

    /**
     * جلب عنوان الخدمة حسب لغة الموقع.
     */
    public function localizedTitle(
        ?string $locale = null
    ): string {
        $locale ??= app()->getLocale();

        if (
            $locale === 'en' &&
            filled($this->title_en)
        ) {
            return $this->title_en;
        }

        return $this->title_ar;
    }

    /**
     * جلب وصف الخدمة حسب لغة الموقع.
     */
    public function localizedDescription(
        ?string $locale = null
    ): string {
        $locale ??= app()->getLocale();

        if (
            $locale === 'en' &&
            filled($this->description_en)
        ) {
            return $this->description_en;
        }

        return $this->description_ar;
    }
     public function works(): HasMany
{
    return $this->hasMany(Work::class)
        ->orderBy('sort_order')
        ->latest('id');
}
}
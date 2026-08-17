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
        return $this->buildUrl(absolute: false);
    }

    /**
     * إنشاء الرابط الكامل.
     */
    public function url(): string
    {
        return $this->buildUrl(absolute: true);
    }

    /**
     * بناء الرابط حسب نوع العنصر المرتبط (linkable_type)، بدل افتراض
     * أنه دائمًا خبر — كل نوع له اسم راوت مختلف، والمقالات تحتاج أيضًا
     * اختيار الراوت الصحيح حسب لغة الرابط نفسه.
     */
    private function buildUrl(bool $absolute): string
    {
        return match ($this->linkable_type) {
            'article' => $absolute
                ? article_route('show', ['slug' => $this->slug], $this->locale)
                : route(
                    $this->locale === 'en' ? 'articles.show.en' : 'articles.show',
                    ['slug' => $this->slug],
                    false
                ),
            'service' => route(
                $this->locale === 'en' ? 'services.show.en' : 'services.show',
                ['slug' => $this->slug],
                $absolute
            ),
            default => route(
                $this->locale === 'en' ? 'news.show.en' : 'news.show',
                ['slug' => $this->slug],
                $absolute
            ),
        };
    }
}

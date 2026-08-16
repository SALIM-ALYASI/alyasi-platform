<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * متوسط عدد الكلمات المقروءة بالدقيقة، لحساب مدة القراءة تلقائيًا.
     */
    private const WORDS_PER_MINUTE = 200;

    protected $fillable = [
        'article_category_id',
        'author_id',
        'title_ar',
        'title_en',
        'excerpt_ar',
        'excerpt_en',
        'content_ar',
        'content_en',
        'featured_image_ar',
        'featured_image_en',
        'status',
        'is_featured',
        'published_at',
        'meta_title_ar',
        'meta_title_en',
        'meta_description_ar',
        'meta_description_en',
        'meta_keywords_ar',
        'meta_keywords_en',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'views' => 'integer',
            'reading_time' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if ($article->isDirty('content_ar') || $article->isDirty('content_en')) {
                $article->reading_time = static::calculateReadingTime(
                    $article->content_ar ?: $article->content_en
                );
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Permalinks
    |--------------------------------------------------------------------------
    */

    public function permalinks(): MorphMany
    {
        return $this->morphMany(Permalink::class, 'linkable');
    }

    /**
     * جلب الرابط الدائم حسب اللغة (يفضّل اللغة الحالية، ثم أي لغة أخرى).
     */
    public function permalink(?string $locale = null): ?Permalink
    {
        $locale ??= app()->getLocale();

        return $this->permalinks->firstWhere('locale', $locale)
            ?? $this->permalinks->first();
    }

    /**
     * جلب نص الرابط الدائم حسب اللغة.
     */
    public function slug(?string $locale = null): ?string
    {
        return $this->permalink($locale)?->slug;
    }

    /**
     * جلب نص الرابط الدائم للغة محددة فقط، بدون أي تراجع للغة أخرى.
     *
     * على عكس slug()، تُعيد null صراحة إن لم توجد ترجمة بهذه اللغة —
     * ضرورية لأي مكان يحتاج التأكد الفعلي من وجود الترجمة (hreflang،
     * زر تبديل اللغة)، حيث تراجع slug() التلقائي يُعطي نتيجة خاطئة.
     */
    public function translatedSlug(string $locale): ?string
    {
        return $this->permalinks->firstWhere('locale', $locale)?->slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * تصفية المقالات المتوفرة بلغة معيّنة (لها رابط دائم بهذه اللغة).
     */
    public function scopeAvailableIn(Builder $query, string $locale): Builder
    {
        return $query->whereHas(
            'permalinks',
            fn (Builder $query) => $query->where('locale', $locale)
        );
    }

    /**
     * تسجيل مشاهدة جديدة للمقال.
     */
    public function registerView(): void
    {
        $this->increment('views');
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

    public function getMetaTitleAttribute(): ?string
    {
        return $this->localizedValue('meta_title');
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->localizedValue('meta_description');
    }

    /**
     * صورة المقال المعروضة أعلى صفحة تفاصيله فقط — تتبع لغة الصفحة
     * الحالية مع رجوع لصورة العربي لو ما فيه صورة إنجليزية بعد.
     * باقي الاستخدامات (بطاقات الفهرس، og:image، JSON-LD، المقالات
     * المشابهة) تبقى عمدًا على الصورة العربية دائمًا — ما تتغيّر باللغة.
     */
    public function displayImage(): ?string
    {
        if (app()->getLocale() === 'en' && filled($this->featured_image_en)) {
            return $this->featured_image_en;
        }

        return $this->featured_image_ar;
    }

    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->localizedValue('meta_keywords');
    }

    /**
     * حساب مدة القراءة التقريبية بالدقائق من عدد كلمات المحتوى.
     *
     * نستخدم تقسيمًا على الفراغات بدل str_word_count() لأن الأخيرة مبنية
     * على أنماط حروف لاتينية ولا تعدّ الكلمات العربية بشكل صحيح.
     */
    public static function calculateReadingTime(?string $content): int
    {
        $plainText = trim(strip_tags((string) $content));

        $wordCount = $plainText === ''
            ? 0
            : count(preg_split('/\s+/u', $plainText));

        return max(1, (int) ceil($wordCount / self::WORDS_PER_MINUTE));
    }

    private function localizedValue(string $field): ?string
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $fallbackLocale = $locale === 'ar' ? 'en' : 'ar';

        $localizedValue = $this->getAttribute("{$field}_{$locale}");

        if (filled($localizedValue)) {
            return $localizedValue;
        }

        $fallbackValue = $this->getAttribute("{$field}_{$fallbackLocale}");

        return filled($fallbackValue) ? $fallbackValue : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
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
            if ($article->isDirty('content')) {
                $article->reading_time = static::calculateReadingTime($article->content);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

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
     * تسجيل مشاهدة جديدة للمقال.
     */
    public function registerView(): void
    {
        $this->increment('views');
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Work extends Model
{
    use HasFactory;

    /**
     * حقول لها نسخة إنجليزية اختيارية ({field}_en).
     */
    public const TRANSLATABLE = [
        'title',
        'short_description',
        'description',
        'outcome',
    ];

    /**
     * حالات المشروع (null = عمل منجز، العرض الافتراضي).
     */
    public const STATUSES = [
        'in_development',
    ];

    protected $fillable = [
        'service_id',
        'title',
        'title_en',
        'slug',
        'type',
        'short_description',
        'short_description_en',
        'outcome',
        'outcome_en',
        'description',
        'description_en',
        'cover_image',
        'cover_is_illustrative',
        'client_name',
        'work_url',
        'completed_at',
        'status',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'date',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'cover_is_illustrative' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(WorkImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * جميع التقييمات (Reviews) لهذا العمل.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * التقييمات المعتمدة فقط، من الأحدث للأقدم.
     */
    public function approvedReviews(): MorphMany
    {
        return $this->reviews()
            ->approved()
            ->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->latest('id');
    }

    /**
     * نص الحقل بلغة الصفحة: النسخة الإنجليزية لو موجودة بالصفحة الإنجليزية،
     * وإلا العربي. للواجهة العامة فقط -- لوحة التحكم تقرأ الحقول نفسها
     * عشان ما تنحفظ الإنجليزية مكان العربية لو كانت اللوحة بالإنجليزي.
     */
    public function localized(string $field): ?string
    {
        if (
            app()->getLocale() === 'en'
            && in_array($field, self::TRANSLATABLE, true)
            && filled($this->getAttribute($field.'_en'))
        ) {
            return $this->getAttribute($field.'_en');
        }

        return $this->getAttribute($field);
    }

    /**
     * الوصف مقسّم فقرات (سطر فاضي بين كل فقرة).
     */
    public function localizedParagraphs(string $field = 'description'): array
    {
        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\R\s*\R/u', (string) $this->localized($field))
        )));
    }

    public function getTypeLabelAttribute(): string
    {
        $key = in_array($this->type, array_keys(trans('works.types')), true)
            ? $this->type
            : 'other';

        return __("works.types.{$key}");
    }

    public function getStatusLabelAttribute(): ?string
    {
        return in_array($this->status, self::STATUSES, true)
            ? __("works.status.{$this->status}")
            : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EventEdition extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'event_id',
        'year',
        'title_ar',
        'title_en',
        'coverage_type',
        'attended',
        'date_status',
        'event_start_at',
        'event_end_at',
        'livestream_url',
        'image',
        'gallery',
        'short_description_ar',
        'short_description_en',
        'announcements',
        'pricing_table',
        'upgrade_verdict',
        'upgrade_verdict_text',
        'status',
        'published_at',
    ];

    /**
     * التحويلات.
     */
    protected $casts = [
        'year' => 'integer',
        'attended' => 'boolean',
        'event_start_at' => 'datetime',
        'event_end_at' => 'datetime',
        'announcements' => 'array',
        'pricing_table' => 'array',
        'gallery' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * المؤتمر الدائم اللي تتبعه هذي النسخة.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * الروابط الدائمة (رابط لكل لغة).
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
     * العنوان حسب اللغة الحالية (يرجع للعربي لو ما فيه ترجمة إنجليزية).
     */
    public function getTitleAttribute(): string
    {
        if (app()->getLocale() === 'en' && filled($this->title_en)) {
            return $this->title_en;
        }

        return $this->title_ar;
    }

    /**
     * الوصف المختصر حسب اللغة الحالية.
     */
    public function getShortDescriptionAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && filled($this->short_description_en)) {
            return $this->short_description_en;
        }

        return $this->short_description_ar;
    }

    /**
     * حالة الحدث الفعلية (upcoming / live / concluded) بناءً على التاريخ -
     * تُحسب لا تُخزَّن، عشان تتبدّل تلقائياً بلا تدخل يدوي (نفس نمط
     * CommunityPost::getEventStatusAttribute()).
     */
    public function getPhaseAttribute(): string
    {
        if (! $this->event_start_at) {
            return 'upcoming';
        }

        $now = now();
        $end = $this->event_end_at ?? $this->event_start_at;

        if ($now->lessThan($this->event_start_at)) {
            return 'upcoming';
        }

        if ($now->greaterThan($end)) {
            return 'concluded';
        }

        return 'live';
    }

    /**
     * النسخ المنشورة.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}

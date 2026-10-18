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
        'official_url',
        'image',
        'image_en',
        'gallery',
        'short_description_ar',
        'short_description_en',
        'content_ar',
        'content_en',
        'announcements',
        'pricing_table',
        'upgrade_verdict',
        'upgrade_verdict_text',
        'upgrade_verdict_text_ar',
        'upgrade_verdict_text_en',
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
        'official_checked_at' => 'datetime',
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
     * المحتوى التفصيلي (HTML منقّى) حسب لغة الصفحة، مع الرجوع للعربي.
     */
    public function getContentAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && filled($this->content_en)) {
            return $this->content_en;
        }

        return $this->content_ar;
    }

    /**
     * تفصيل حكم الترقية حسب لغة الصفحة.
     * الإنجليزية لا ترجع تلقائيًا للنص العربي حتى لا يظهر محتوى عربي
     * داخل /en. العربية ترجع للحقل القديم فقط كدعم انتقالي للبيانات القديمة.
     */
    public function getLocalizedUpgradeVerdictTextAttribute(): ?string
    {
        if (app()->getLocale() === 'en') {
            return filled($this->upgrade_verdict_text_en)
                ? $this->upgrade_verdict_text_en
                : null;
        }

        if (filled($this->upgrade_verdict_text_ar)) {
            return $this->upgrade_verdict_text_ar;
        }

        return $this->upgrade_verdict_text;
    }

    /**
     * الصورة الرئيسية حسب لغة الصفحة الحالية — ترجع للصورة العربية
     * (الحقل القديم) لو ما فيه صورة إنجليزية بعد، نفس نمط Article::displayImage().
     */
    public function displayImage(): ?string
    {
        if (app()->getLocale() === 'en' && filled($this->image_en)) {
            return $this->image_en;
        }

        return $this->image;
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
     * الشارة المعروضة: مثل phase، بس وقت الحدث لو ما عندنا بث مباشر ولا
     * حضور ميداني تصير «تغطية عن بُعد» بدل «مباشر الآن» -- محتوانا وقتها
     * من المصادر المنشورة، مب تغطية لحظية.
     */
    public function getBadgePhaseAttribute(): string
    {
        $phase = $this->phase;

        if ($phase === 'live' && blank($this->livestream_url) && ! $this->attended) {
            return 'remote';
        }

        return $phase;
    }

    /**
     * موعد انعقاد المؤتمر بتوقيت مسقط: «6 أكتوبر 2026» ليوم واحد،
     * «12 إلى 14 أكتوبر 2026» لأيام بنفس الشهر (12–14 October بالإنجليزي).
     */
    public function dateRangeLabel(?string $locale = null): ?string
    {
        if (! $this->event_start_at) {
            return null;
        }

        $locale ??= app()->getLocale();
        $start = $this->event_start_at->copy()->timezone('Asia/Muscat')->locale($locale);
        $end = ($this->event_end_at ?? $this->event_start_at)->copy()->timezone('Asia/Muscat')->locale($locale);

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('j F Y');
        }

        // بالعربي «إلى» بدل الشرطة: «1–4» بصفحة من اليمين لليسار تنقلب بصريًا لـ«4–1».
        $to = $locale === 'ar' ? ' إلى ' : '–';

        if ($start->isSameMonth($end)) {
            return $start->format('j').$to.$end->translatedFormat('j F Y');
        }

        return $start->translatedFormat($start->isSameYear($end) ? 'j F' : 'j F Y').($locale === 'ar' ? ' إلى ' : ' – ').$end->translatedFormat('j F Y');
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

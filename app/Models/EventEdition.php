<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_media_partner',
        'coverage_url',
        'interviews_url',
        'exclusive_published_at',
        'is_featured',
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
        'goals_ar',
        'goals_en',
        'coverage_team',
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
        'is_media_partner' => 'boolean',
        'is_featured' => 'boolean',
        'exclusive_published_at' => 'datetime',
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
     * أهداف منصة ALYASI من تغطية هذا المؤتمر بلغة الصفحة (هدف بكل سطر)،
     * وترجع للعربي لو ما فيه إنجليزي.
     *
     * @return list<string>
     */
    public function localizedGoals(): array
    {
        $text = app()->getLocale() === 'en' ? ($this->goals_en ?: $this->goals_ar) : ($this->goals_ar ?: $this->goals_en);

        return array_values(array_filter(array_map(
            fn ($line) => trim(preg_replace('/^[\s\-•*·\d.)]+/u', '', $line)),
            preg_split('/\R/u', (string) $text)
        )));
    }

    /**
     * فريق تغطية المؤتمر من حقل coverage_team (شخص بكل سطر):
     * «الاسم بالعربي | English name | رابط الحساب» — الإنجليزي والرابط اختياريين.
     * الرابط ينقبل بس لو https، والمنصة تنعرف من النطاق (لنص الزر وأيقونته).
     *
     * @return list<array{name: string, url: ?string, platform: ?string}>
     */
    public function coverageTeam(): array
    {
        $en = app()->getLocale() === 'en';
        $platforms = [
            'instagram.com' => 'instagram', 'x.com' => 'x', 'twitter.com' => 'x',
            'tiktok.com' => 'tiktok', 'linkedin.com' => 'linkedin', 'youtube.com' => 'youtube', 'snapchat.com' => 'snapchat',
        ];

        $team = [];
        foreach (preg_split('/\R/u', (string) $this->coverage_team) as $line) {
            $parts = array_map('trim', explode('|', $line));
            if (($parts[0] ?? '') === '') {
                continue;
            }

            $url = $parts[2] ?? null;
            $url = $url && preg_match('#^https://#i', $url) ? $url : null;
            $host = $url ? preg_replace('/^www\./', '', strtolower((string) parse_url($url, PHP_URL_HOST))) : null;

            $team[] = [
                'name' => $en && filled($parts[1] ?? null) ? $parts[1] : $parts[0],
                'url' => $url,
                'platform' => $host ? ($platforms[$host] ?? null) : null,
            ];
        }

        return $team;
    }

    /**
     * فهرس مقابلات هذي النسخة (أي مؤتمر، مب CyberX بس).
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(EventInterview::class)->orderBy('sort_order');
    }

    /**
     * رابط من لوحة التحكم بلغة الصفحة: المسار الداخلي (/events/...) ينضاف له
     * /en بالإنجليزي، والرابط الكامل (https://) يبقى مثل ما هو.
     */
    public static function localizedPath(?string $path, ?string $locale = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $locale ??= app()->getLocale();
        $path = '/'.ltrim($path, '/');

        return url(($locale === 'en' && ! str_starts_with($path, '/en/') ? '/en' : '').$path);
    }

    /**
     * صفحة هذه النسخة باللغة الحالية (أو المتاحة)، وإلا صفحة المؤتمر.
     */
    public function publicUrl(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return ($this->permalink($locale) ?? $this->permalink())?->url() ?? $this->event->url($locale);
    }

    public function coverageLink(): ?string
    {
        return self::localizedPath($this->coverage_url);
    }

    public function interviewsLink(): ?string
    {
        return self::localizedPath($this->interviews_url);
    }

    /**
     * تغطية ميدانية فعلية: حضرنا، ونُشرت تغطيتنا (مب مجرد نية حضور).
     */
    public function hasOnsiteCoverage(): bool
    {
        return (bool) $this->attended && $this->exclusive_published_at !== null && $this->exclusive_published_at->isPast();
    }

    /**
     * آخر وقت نُشر فيه محتوى حصري لهذي النسخة (تغطية ميدانية أو مقابلة)، أو null.
     */
    public function latestExclusiveAt(int $publishedInterviews, ?\Illuminate\Support\Carbon $latestInterviewAt): ?\Illuminate\Support\Carbon
    {
        $times = array_filter([
            $this->hasOnsiteCoverage() ? $this->exclusive_published_at : null,
            $publishedInterviews > 0 ? ($latestInterviewAt ?? $this->exclusive_published_at) : null,
        ]);

        return $times ? max($times) : null;
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

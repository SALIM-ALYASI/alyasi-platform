<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * فهرس مقابلات نسخة مؤتمر (الاسم والصورة والرابط وحالة النشر). المحتوى
 * التفصيلي للمقابلة بمصدره؛ هذا الفهرس اللي تقراه صفحة الفعاليات لأي مؤتمر.
 */
class EventInterview extends Model
{
    protected $fillable = [
        'event_edition_id', 'slug', 'name_ar', 'name_en', 'role_ar', 'role_en', 'photo',
        'url_ar', 'url_en', 'is_published', 'published_at', 'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(EventEdition::class, 'event_edition_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'en' && filled($this->name_en) ? $this->name_en : $this->name_ar;
    }

    /** بيانات تكفي لـ events.coverage.interviews._avatar */
    public function avatar(): array
    {
        $name = $this->localizedName();
        $words = preg_split('/\s+/u', trim($name)) ?: [];

        return [
            'photo' => $this->photo ? asset(ltrim($this->photo, '/')) : null,
            'initials' => mb_substr($words[0] ?? '', 0, 1).(app()->getLocale() === 'en' ? mb_substr($words[count($words) - 1] ?? '', 0, 1) : ''),
            'name' => $name,
        ];
    }
}

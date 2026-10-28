<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * طلب خدمة منشور علنًا (يلقطه Grok). ما نتواصل مع أحد تلقائيًا: سالم يشوف
 * الطلب ويقرر. يتحذف بعد 60 يوم (Prunable).
 */
class ServiceLead extends Model
{
    use Prunable;

    public const COUNTRIES = [
        'OM' => ['عُمان', '🇴🇲'],
        'AE' => ['الإمارات', '🇦🇪'],
        'SA' => ['السعودية', '🇸🇦'],
        'QA' => ['قطر', '🇶🇦'],
        'BH' => ['البحرين', '🇧🇭'],
        'KW' => ['الكويت', '🇰🇼'],
    ];

    public const CATEGORIES = [
        'graphic_design' => 'تصميم جرافيك',
        'video' => 'فيديو ومونتاج',
        'web' => 'مواقع وأنظمة',
    ];

    /** gcc: طلبات عُمان والخليج (تنبيه فوري). marketplace: مستقل وخمسات من أي بلد (قائمة يومية). */
    public const SCOPES = [
        'gcc' => 'عُمان والخليج',
        'marketplace' => 'مستقل وخمسات',
    ];

    /** أقل ميزانية لمشاريع المنصات (بالدولار). */
    public const MARKETPLACE_MIN_BUDGET_USD = 100;

    public const PLATFORMS = ['x', 'instagram', 'linkedin', 'facebook', 'tiktok', 'threads', 'snapchat', 'mostaql', 'khamsat', 'web'];

    public const STATUSES = [
        'new' => 'جديد',
        'contacted' => 'تواصلت',
        'dismissed' => 'غير مناسب',
        'rejected' => 'مرفوض تلقائيًا',
    ];

    public const REJECT_REASONS = [
        'outside_region' => 'خارج عُمان والخليج',
        'offer' => 'عرض خدمات مب طلب',
        'stale' => 'قديم (أكثر من 7 أيام)',
        'duplicate_text' => 'نفس نص طلب سابق',
        'low_budget' => 'ميزانية أقل من 100$ أو غير مذكورة',
        'not_marketplace' => 'مب من مستقل أو خمسات',
    ];

    protected $fillable = [
        'agent', 'scope', 'platform', 'url', 'url_key', 'text_hash', 'author', 'text', 'summary',
        'category', 'country', 'city', 'budget', 'budget_usd', 'deadline', 'posted_at', 'score',
        'status', 'reject_reason', 'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'notified_at' => 'datetime',
            'score' => 'integer',
            'budget_usd' => 'integer',
        ];
    }

    public function prunable(): Builder
    {
        // اللي ما انختار بأفضل 5 الأسبوعية ولا اشتغل عليه سالم: يروح بعد 8 أيام.
        return static::where('created_at', '<', now()->subDays(60))
            ->orWhere(fn (Builder $q) => $q->where('created_at', '<', now()->subDays(8))
                ->whereNull('notified_at')
                ->whereIn('status', ['new', 'rejected']));
    }

    public function countryLabel(): string
    {
        if ($this->country === null) {
            return '🌍';
        }

        [$name, $flag] = self::COUNTRIES[$this->country] ?? [$this->country, ''];

        return trim($flag.' '.$name);
    }

    public function platformLabel(): string
    {
        return ['mostaql' => 'مستقل', 'khamsat' => 'خمسات'][$this->platform] ?? strtoupper($this->platform);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}

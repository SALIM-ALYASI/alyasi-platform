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
    ];

    protected $fillable = [
        'agent', 'platform', 'url', 'url_key', 'text_hash', 'author', 'text', 'summary',
        'category', 'country', 'city', 'budget', 'deadline', 'posted_at', 'score',
        'status', 'reject_reason', 'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'notified_at' => 'datetime',
            'score' => 'integer',
        ];
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(60));
    }

    public function countryLabel(): string
    {
        [$name, $flag] = self::COUNTRIES[$this->country] ?? [$this->country, ''];

        return trim($flag.' '.$name);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'is_retained',
        'ip_address',
        'device_token',
    ];

    /**
     * مدة الاحتفاظ برسائل التواصل غير المرتبطة بعقد (سياسة الخصوصية، البند 14).
     */
    public const RETENTION_MONTHS = 12;

    protected function casts(): array
    {
        return [
            'is_retained' => 'boolean',
        ];
    }

    /**
     * حذف رسائل من مرّ على آخر تواصل لهم 12 شهرًا، إلا المرتبطة بعقد أو مشروع
     * (is_retained) وكل رسائل نفس المرسل معها. يُستدعى عند وصول رسالة جديدة
     * وعند فتح صندوق الرسائل، فما يعتمد على جدولة السيرفر.
     */
    public static function pruneExpired(): int
    {
        $cutoff = now()->subMonths(self::RETENTION_MONTHS);

        $activeOrRetained = static::query()
            ->select('email')
            ->where(fn (Builder $query) => $query
                ->where('created_at', '>=', $cutoff)
                ->orWhere('is_retained', true));

        return static::query()
            ->where('created_at', '<', $cutoff)
            ->where('is_retained', false)
            ->whereNotIn('email', $activeOrRetained)
            ->delete();
    }

    /**
     * الرسائل الجديدة (غير المقروءة).
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }
}

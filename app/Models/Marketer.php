<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marketer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'code',
        'base_commission_rate',
        'tier_increment_rate',
        'customers_per_tier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_commission_rate' => 'decimal:2',
            'tier_increment_rate' => 'decimal:2',
            'customers_per_tier' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(MarketerCustomer::class);
    }

    /**
     * توليد كود فريد من 9 خانات رقمية لمسوّق جديد.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = (string) random_int(100000000, 999999999);
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * نسبة العمولة الحالية بعد احتساب الشرائح (كل customers_per_tier
     * عميل تزيد النسبة بمقدار tier_increment_rate).
     */
    public function currentCommissionRate(): float
    {
        return $this->commissionRateForCustomerCount($this->customers()->count());
    }

    /**
     * نسبة العمولة المستحقة عند وصول عدد العملاء إلى $count (تُستخدم
     * كـ "لقطة" وقت تسجيل عميل جديد حتى لا تتأثر عمولات سابقة).
     */
    public function commissionRateForCustomerCount(int $count): float
    {
        $tiers = $this->customers_per_tier > 0
            ? intdiv($count, $this->customers_per_tier)
            : 0;

        return (float) $this->base_commission_rate + ($tiers * (float) $this->tier_increment_rate);
    }

    /**
     * إجمالي العمولات المستحقة لهذا المسوّق من كل عملائه.
     */
    public function totalCommission(): float
    {
        return (float) $this->customers()->sum('commission_amount');
    }
}

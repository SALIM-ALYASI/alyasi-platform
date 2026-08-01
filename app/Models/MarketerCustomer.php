<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketerCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketer_id',
        'customer_name',
        'customer_phone',
        'deal_amount',
        'commission_rate',
        'commission_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deal_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function marketer(): BelongsTo
    {
        return $this->belongsTo(Marketer::class);
    }
}

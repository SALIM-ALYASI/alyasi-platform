<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketContext extends Model
{
    protected $table = 'market_context';

    protected $fillable = [
        'key',
        'value',
        'unit',
        'note',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'verified_at' => 'datetime',
        ];
    }
}

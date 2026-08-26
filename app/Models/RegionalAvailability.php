<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionalAvailability extends Model
{
    protected $table = 'regional_availability';

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_GRAY_MARKET = 'gray_market';

    public const STATUS_GEO_BLOCKED = 'geo_blocked';

    public const STATUS_UNKNOWN = 'unknown';

    protected $fillable = [
        'entity',
        'entity_type',
        'status',
        'has_local_warranty',
        'local_reseller',
        'note',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'has_local_warranty' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}

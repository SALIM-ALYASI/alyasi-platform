<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSlugRedirect extends Model
{
    protected $fillable = [
        'work_id',
        'old_slug',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }
}

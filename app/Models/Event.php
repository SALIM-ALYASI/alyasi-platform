<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'name',
        'organizer',
    ];

    /**
     * نسخ الحدث عبر السنين.
     */
    public function editions(): HasMany
    {
        return $this->hasMany(EventEdition::class);
    }
}

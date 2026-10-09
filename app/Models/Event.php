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
        'name_en',
        'slug',
        'organizer',
    ];

    /**
     * اسم المؤتمر بلغة الصفحة: الإنجليزي بالصفحات الإنجليزية لو موجود.
     */
    public function localizedName(): string
    {
        return app()->getLocale() === 'en' && filled($this->name_en)
            ? $this->name_en
            : $this->name;
    }

    /**
     * نسخ الحدث عبر السنين.
     */
    public function editions(): HasMany
    {
        return $this->hasMany(EventEdition::class);
    }
}

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
        'image',
        'description_ar',
        'description_en',
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
     * وصف المؤتمر حسب مجاله (أعلى صفحة أرشيفه) بلغة الصفحة.
     */
    public function localizedDescription(): ?string
    {
        $value = app()->getLocale() === 'en' ? ($this->description_en ?: $this->description_ar) : ($this->description_ar ?: $this->description_en);

        return filled($value) ? $value : null;
    }

    /**
     * نسخ الحدث عبر السنين.
     */
    public function editions(): HasMany
    {
        return $this->hasMany(EventEdition::class);
    }
}

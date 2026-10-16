<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OmanPulseItem extends Model
{
    /**
     * وسوم القسم (العرض بالعربي).
     */
    public const TAGS = [
        'services' => 'خدمات',
        'tech' => 'تقنية',
        'economy' => 'اقتصاد',
        'cyber' => 'أمن سيبراني',
        'society' => 'مجتمع',
    ];

    protected $fillable = [
        'suggestion_id',
        'title',
        'lead',
        'paragraphs',
        'source_name',
        'source_url',
        'source_url_hash',
        'source_published_at',
        'image',
        'tag',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'paragraphs' => 'array',
            'source_published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function url(): string
    {
        return route('news.oman.show', ['item' => $this->id]);
    }

    public function getTagLabelAttribute(): string
    {
        return self::TAGS[$this->tag] ?? self::TAGS['services'];
    }
}

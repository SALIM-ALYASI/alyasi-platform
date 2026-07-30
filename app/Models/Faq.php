<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_ar',
        'question_en',
        'answer_ar',
        'answer_en',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function localizedQuestion(): string
    {
        if (app()->isLocale('en') && filled($this->question_en)) {
            return $this->question_en;
        }

        return $this->question_ar;
    }

    public function localizedAnswer(): string
    {
        if (app()->isLocale('en') && filled($this->answer_en)) {
            return $this->answer_en;
        }

        return $this->answer_ar;
    }
}

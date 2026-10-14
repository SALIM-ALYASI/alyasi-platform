<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsSuggestion extends Model
{
    public const STATUSES = ['pending', 'fetched', 'duplicate', 'failed'];

    protected $fillable = [
        'url',
        'url_hash',
        'title',
        'summary',
        'why_important',
        'source_name',
        'suggested_by',
        'status',
        'status_note',
        'candidate_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'candidate_id' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * بصمة الرابط بدون أجزاء التتبع (utm_* وغيرها) -- نفس الخبر برابطين
     * يختلفون بالتتبع بس ينحسب رابط واحد.
     */
    public static function hashUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        $query = array_filter(
            $query,
            fn ($key) => ! preg_match('/^(utm_|fbclid|gclid|ref$|s$|si$)/i', (string) $key),
            ARRAY_FILTER_USE_KEY
        );
        ksort($query);

        $normalized = strtolower($parts['host'] ?? '')
            .rtrim($parts['path'] ?? '', '/')
            .($query ? '?'.http_build_query($query) : '');

        return hash('sha256', preg_replace('/^www\./', '', $normalized));
    }
}

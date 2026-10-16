<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsCard extends Model
{
    public const SOURCES = ['news', 'oman_pulse'];

    public const X_STATUSES = ['pending', 'posted', 'failed', 'skipped'];

    /** X يحسب أي رابط 23 حرف مهما طال. */
    private const X_LIMIT = 280;

    private const X_URL_WEIGHT = 23;

    protected $fillable = [
        'source_type',
        'source_id',
        'title_ar',
        'title_en',
        'url',
        'image_ar',
        'image_en',
        'text_x',
        'x_status',
        'x_tweet_url',
        'x_note',
        'x_result_at',
    ];

    protected function casts(): array
    {
        return [
            'x_result_at' => 'datetime',
        ];
    }

    /**
     * نص التغريدة: العنوان العربي (والإنجليزي لو يتسع) + رابط واحد للخبر --
     * الموقع يفتح بلغة جهاز الزائر. بدون أي إضافة على نص الخبر.
     */
    public static function buildXText(string $titleAr, ?string $titleEn, string $url): string
    {
        $room = self::X_LIMIT - self::X_URL_WEIGHT - 2;
        $titleAr = trim(preg_replace('/\s+/u', ' ', $titleAr));
        $titleEn = $titleEn ? trim(preg_replace('/\s+/u', ' ', $titleEn)) : null;

        $body = $titleAr;
        if ($titleEn && mb_strlen($titleAr) + 1 + mb_strlen($titleEn) <= $room) {
            $body .= "\n".$titleEn;
        }

        // «…» نفسها حرف، فنقص قبلها بحرف.
        return (mb_strlen($body) > $room ? Str::limit($body, $room - 1, '…') : $body)."\n\n".$url;
    }
}

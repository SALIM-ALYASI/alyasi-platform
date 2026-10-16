<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * تفاعل داخل الصفحة: وصول لنهاية المقال، تشغيل صوت، إكمال صوت.
 * الوصول للنهاية مؤشر تمرير فقط -- مب دليل إن الزائر قرأ.
 */
class PageEvent extends Model
{
    public const TYPES = ['reached_end', 'audio_play', 'audio_complete', 'guest_open'];

    protected $fillable = [
        'visitor_id',
        'path',
        'type',
        'label',
        'seconds',
        'traffic_class',
    ];
}

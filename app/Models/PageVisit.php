<?php

namespace App\Models;

use App\Support\Analytics\VisitClassifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'path',
        'ip_address',
        'proxy_ip',
        'country_code',
        'country_name',
        'user_agent',
        'traffic_class',
        'class_reasons',
        'visitor_id',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    /**
     * الزيارات المصنّفة (من بداية القياس المنقّى). القديمة traffic_class = null.
     */
    public function scopeClassified(Builder $query): Builder
    {
        return $query->whereNotNull('traffic_class');
    }

    /**
     * يضيف سبب اشتباه ويحوّل «بشري مرجّح» إلى «مشتبه به» (ما يمس البوت/سالم).
     */
    public function markSuspect(string $reason): void
    {
        $reasons = array_filter(explode(',', (string) $this->class_reasons));

        if (! in_array($reason, $reasons, true)) {
            $reasons[] = $reason;
        }

        $this->class_reasons = implode(',', $reasons);

        if ($this->traffic_class === VisitClassifier::HUMAN) {
            $this->traffic_class = VisitClassifier::SUSPECT;
        }
    }

    /**
     * زيارات بشرية تنحسب بالأرقام: «بشري مرجّح» قبل analytics.confirm_since،
     * وبعده لازم تكون مؤكدة بإشارة view من المتصفح (confirmed_at).
     */
    public function scopeCountedHuman(Builder $query): Builder
    {
        return $query->where('traffic_class', VisitClassifier::HUMAN)
            ->where(fn (Builder $q) => $q->where('created_at', '<', \App\Support\Analytics\VisitStats::confirmSince())
                ->orWhereNotNull('confirmed_at'));
    }

    /**
     * الزيارات اللي تحدد بلدها فعليًا (استبعاد المجهولة/المحلية).
     */
    public function scopeWithKnownCountry(Builder $query): Builder
    {
        return $query->whereNotNull('country_name');
    }
}

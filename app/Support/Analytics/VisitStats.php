<?php

namespace App\Support\Analytics;

use App\Models\PageEvent;
use App\Models\PageVisit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * أرقام لوحة التحكم من القياس المنقّى فقط (من analytics.clean_since).
 * «زائر» = معرّف كوكي، فالأرقام تقريبية: نفس الشخص على جهازين = زائرين،
 * ومسح الكوكي أو التصفح الخاص يحسب الراجع كجديد.
 */
final class VisitStats
{
    public static function cleanSince(): Carbon
    {
        return Carbon::parse(config('analytics.clean_since'), 'Asia/Muscat')->startOfDay();
    }

    /**
     * @return array<string, int> عدد الزيارات لكل تصنيف
     */
    public static function byClass(): array
    {
        $counts = PageVisit::query()
            ->classified()
            ->where('created_at', '>=', self::cleanSince())
            ->selectRaw('traffic_class, count(*) as total')
            ->groupBy('traffic_class')
            ->pluck('total', 'traffic_class');

        return collect([
            VisitClassifier::HUMAN, VisitClassifier::SUSPECT, VisitClassifier::BOT,
            VisitClassifier::OWNER, VisitClassifier::INTERNAL,
        ])->mapWithKeys(fn ($class) => [$class => (int) ($counts[$class] ?? 0)])->all();
    }

    /**
     * @return array{visits: int, visitors: int, visits_today: int, visitors_today: int, returning: int}
     */
    public static function humans(): array
    {
        $base = fn () => PageVisit::query()
            ->where('traffic_class', VisitClassifier::HUMAN)
            ->where('created_at', '>=', self::cleanSince());

        $today = now('Asia/Muscat')->startOfDay();

        // زار بيومين مختلفين (بتوقيت عُمان) أو أكثر.
        $returning = $base()
            ->whereNotNull('visitor_id')
            ->get(['visitor_id', 'created_at'])
            ->groupBy('visitor_id')
            ->filter(fn ($visits) => $visits->map(fn ($visit) => $visit->created_at->setTimezone('Asia/Muscat')->toDateString())->unique()->count() >= 2)
            ->count();

        return [
            'visits' => $base()->count(),
            'visitors' => $base()->distinct()->count('visitor_id'),
            'visits_today' => $base()->where('created_at', '>=', $today)->count(),
            'visitors_today' => $base()->where('created_at', '>=', $today)->distinct()->count('visitor_id'),
            'returning' => $returning,
        ];
    }

    /**
     * @return array<string, int> زوار مختلفين لكل نوع تفاعل (بشري مرجّح فقط)
     */
    public static function engagement(): array
    {
        return collect(PageEvent::TYPES)->mapWithKeys(fn ($type) => [
            $type => PageEvent::query()
                ->where('type', $type)
                ->where('traffic_class', VisitClassifier::HUMAN)
                ->where('created_at', '>=', self::cleanSince())
                ->get(['id', 'visitor_id', 'path', 'label'])
                ->unique(fn ($event) => ($event->visitor_id ?? 'e'.$event->id).'|'.$event->path.'|'.$event->label)
                ->count(),
        ])->all();
    }

    public static function topPages(int $limit = 6): Collection
    {
        return PageVisit::query()
            ->where('traffic_class', VisitClassifier::HUMAN)
            ->where('created_at', '>=', self::cleanSince())
            ->selectRaw('path, count(distinct visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('visitors')
            ->limit($limit)
            ->get();
    }

    public static function topCountries(int $limit = 5): Collection
    {
        return PageVisit::query()
            ->where('traffic_class', VisitClassifier::HUMAN)
            ->where('created_at', '>=', self::cleanSince())
            ->withKnownCountry()
            ->selectRaw('country_name, country_code, count(distinct visitor_id) as visits_count')
            ->groupBy('country_name', 'country_code')
            ->orderByDesc('visits_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{visits: int, from: ?string, to: ?string}
     */
    public static function legacy(): array
    {
        $row = PageVisit::query()
            ->whereNull('traffic_class')
            ->selectRaw('count(*) as visits, min(created_at) as first_at, max(created_at) as last_at')
            ->first();

        return [
            'visits' => (int) $row->visits,
            'from' => $row->first_at,
            'to' => $row->last_at,
        ];
    }
}

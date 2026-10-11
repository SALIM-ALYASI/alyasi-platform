<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\NewsArticle;
use App\Models\Service;
use App\Support\Analytics\VisitStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $servicesCount = Service::query()
            ->count();

        $activeServicesCount = Service::query()
            ->where('is_active', true)
            ->count();

        $inactiveServicesCount = Service::query()
            ->where('is_active', false)
            ->count();

        $newsCount = NewsArticle::query()
            ->published()
            ->count();

        $communityCount = CommunityPost::query()
            ->active()
            ->count();

        // الأرقام من القياس المنقّى فقط (بشري مرجّح)؛ القديمة تنعرض منفصلة كغير منقّاة.
        $humanStats = VisitStats::humans();
        $visitsCount = $humanStats['visits'];
        $visitsTodayCount = $humanStats['visits_today'];
        $topCountries = VisitStats::topCountries();
        $trafficByClass = VisitStats::byClass();
        $engagement = VisitStats::engagement();
        $topPages = VisitStats::topPages();
        $legacyVisits = VisitStats::legacy();
        $cleanSince = VisitStats::cleanSince();

        return view(
            'admin.dashboard.index',
            compact(
                'servicesCount',
                'activeServicesCount',
                'inactiveServicesCount',
                'newsCount',
                'communityCount',
                'visitsCount',
                'visitsTodayCount',
                'topCountries',
                'humanStats',
                'trafficByClass',
                'engagement',
                'topPages',
                'legacyVisits',
                'cleanSince'
            )
        );
    }
}

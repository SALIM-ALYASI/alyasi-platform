<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\NewsArticle;
use App\Models\Service;
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

        return view(
            'admin.dashboard.index',
            compact(
                'servicesCount',
                'activeServicesCount',
                'inactiveServicesCount',
                'newsCount',
                'communityCount'
            )
        );
    }
}

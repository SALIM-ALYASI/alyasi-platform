<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\CommunityPost;
use App\Models\Faq;
use App\Models\NewsArticle;
use App\Models\PageVisit;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Technology;
use App\Models\Work;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * الصفحة الرئيسية.
     */
    public function index(): View
    {
        $services = Service::query()
            ->with('permalinks')
            ->active()
            ->ordered()
            ->take(6)
            ->get();

        $works = Work::query()
            ->active()
            ->ordered()
            ->take(3)
            ->get();

        $latestNews = NewsArticle::query()
            ->with('permalinks')
            ->published()
            ->latestPublished()
            ->take(3)
            ->get();

        $technologies = Technology::query()
            ->active()
            ->ordered()
            ->get();

        $faqs = Faq::query()
            ->active()
            ->inRandomOrder()
            ->get();

        $showCommunityEvents = Setting::get('show_community_events', '1') === '1';
        $showArticles = Setting::get('show_articles', '1') === '1';

        $communityHighlights = $showCommunityEvents
            ? CommunityPost::query()
                ->with('category')
                ->active()
                ->published()
                ->ordered()
                ->take(3)
                ->get()
            : collect();

        $latestArticles = $showArticles
            ? Article::query()
                ->with('category')
                ->published()
                ->ordered()
                ->take(3)
                ->get()
            : collect();

        $visitorsCount = PageVisit::query()
            ->distinct('ip_address')
            ->count('ip_address');

        $yearsOfExperience = now()->year - 2019;

        return view(
            'home.index',
            compact(
                'services',
                'works',
                'latestNews',
                'technologies',
                'faqs',
                'communityHighlights',
                'latestArticles',
                'showCommunityEvents',
                'showArticles',
                'visitorsCount',
                'yearsOfExperience'
            )
        );
    }
}

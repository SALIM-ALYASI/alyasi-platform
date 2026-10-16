<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Faq;
use App\Models\NewsArticle;
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
            ->take(4) // خدمة مميزة + 3 بطاقات؛ الباقي برابط «جميع الخدمات»
            ->get();

        $servicesCount = Service::query()->active()->count();

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

        // قسم المجتمع العام تم تقاعده لصالح مركز /events الموحد.
        // نبقي المتغيرات متاحة للـ view مؤقتًا حتى لا نكسر القالب القديم.
        $showCommunityEvents = false;
        $communityHighlights = collect();

        $showArticles = Setting::get('show_articles', '1') === '1';

        $latestArticles = $showArticles
            ? Article::query()
                ->with(['category', 'permalinks'])
                ->published()
                ->availableIn(app()->getLocale())
                ->excludingTechHistory()
                ->ordered()
                ->take(3)
                ->get()
            : collect();

        return view(
            'home.index',
            compact(
                'services',
                'servicesCount',
                'works',
                'latestNews',
                'technologies',
                'faqs',
                'communityHighlights',
                'latestArticles',
                'showCommunityEvents',
                'showArticles'
            )
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Work;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * صفحة من نحن.
     */
    public function about(): View
    {
        $yearsOfExperience = now()->year - 2019;

        $projectsCount = Work::query()
            ->where('is_active', true)
            ->count();

        $servicesCount = Service::query()
            ->where('is_active', true)
            ->count();

        return view('about.index', compact(
            'yearsOfExperience',
            'projectsCount',
            'servicesCount'
        ));
    }

    /**
     * صفحة الوظائف (عامة، بلا قائمة شواغر — دعوة للتواصل).
     */
    public function careers(): View
    {
        $contactEmail = Setting::get('contact_email');

        return view('careers.index', compact('contactEmail'));
    }

    /**
     * صفحة تواصل معنا.
     */
    public function contact(): View
    {
        $socialLinks = SocialLink::forDisplay();

        $contactEmail = Setting::get('contact_email');
        $contactPhone = Setting::get('contact_phone');

        return view('contact.index', compact(
            'socialLinks',
            'contactEmail',
            'contactPhone'
        ));
    }

    /**
     * صفحة سياسة الخصوصية.
     */
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    /**
     * صفحة الشروط والأحكام.
     */
    public function terms(): View
    {
        return view('legal.terms');
    }

    /**
     * صفحة تعريف تطبيق ALYASI YouTube Publisher — مطلوبة من Google لإثبات
     * الملكية والربط بين اسم تطبيق OAuth والموقع أثناء طلب التحقق
     * (Brand Verification)، ولازم تكون عامة بلا تسجيل دخول.
     */
    public function youtubePublisher(): View
    {
        return view('legal.youtube-publisher');
    }

    /**
     * صفحة Markify المستقلة (تصميم وهوية بصرية خاصة بالحساب).
     *
     * توزَّع صور المعرض المحلية عشوائيًا في كل زيارة على أماكن الصفحة
     * الأربعة، من بين 10 صور حقيقية من حساب @markifyprints. الصور محفوظة
     * محليًا (لا تعتمد على تحميل سكربتات خارجية) لضمان ظهورها دائمًا لكل
     * الزوار بلا استثناء.
     */
    public function markify(): View
    {
        $featureImages = collect(range(1, 10))
            ->map(fn (int $index): string => sprintf('post-%02d.jpg', $index))
            ->shuffle()
            ->take(4)
            ->values();

        return view('markify', compact('featureImages'));
    }

    /**
     * صفحة مغسلة الياسي المستقلة (بألوان وخطوط ALYASI الفعلية).
     */
    public function carWash(): View
    {
        return view('car-wash');
    }

    /**
     * صفحة منزل محمد بن سالم الحجري (راعي صويط) المستقلة.
     */
    public function ra3iSwait(): View
    {
        return view('ra3i-swait');
    }


    public function sitemap()
{
    return response()
        ->file(public_path('sitemap.xml'), [
            'Content-Type' => 'application/xml',
        ]);
}

    /**
     * خريطة Google News -- تُبنى وقت الطلب مباشرة (لا ملف ثابت) لأن
     * المعيار يشترط آخر 48 ساعة فقط: نافذة متحركة، ملف ثابت يحتاج تحديث
     * يدوي كل مرة يفوّت الخبر النافذة أو يدخلها.
     */
    public function sitemapNews(): Response
    {
        $articles = NewsArticle::query()
            ->published()
            ->where('published_at', '>=', now()->subHours(48))
            ->with('permalinks')
            ->orderByDesc('published_at')
            ->get();

        return response()
            ->view('sitemap-news', compact('articles'))
            ->header('Content-Type', 'application/xml');
    }
}

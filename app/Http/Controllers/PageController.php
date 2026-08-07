<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * صفحة من نحن.
     */
    public function about(): View
    {
        return view('about.index');
    }

    /**
     * صفحة تواصل معنا.
     */
    public function contact(): View
    {
        $socialLinks = SocialLink::query()
            ->active()
            ->ordered()
            ->get();

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
}

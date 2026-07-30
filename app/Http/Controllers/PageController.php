<?php

namespace App\Http\Controllers;

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

        return view('contact.index', compact('socialLinks'));
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
}

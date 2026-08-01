<?php

namespace App\Http\Controllers;

use App\Models\SocialLink;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        $socialLinks = SocialLink::query()
            ->active()
            ->ordered()
            ->get();

        return view('social-links.index', compact('socialLinks'));
    }
}

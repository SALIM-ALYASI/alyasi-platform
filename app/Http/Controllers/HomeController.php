<?php

namespace App\Http\Controllers;

use App\Models\Service;
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

        return view(
            'home.index',
            compact('services')
        );
    }
}
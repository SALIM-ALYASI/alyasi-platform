<?php

namespace App\Http\Controllers;

use App\Models\ProductLaunch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductLaunchController extends Controller
{
    /**
     * فهرس عام لإطلاقات المنتجات المكتشفة تلقائيًا (product_watch.py، سيرفر
     * البيت) -- بطاقة خبر مختصرة لكل إطلاق، مختلف تمامًا عن صفحات المؤتمرات
     * اليدوية الكاملة (events)، ومكمّل لها لا بديل عنها.
     */
    public function index(Request $request): View
    {
        $companies = ProductLaunch::query()
            ->select('company')
            ->distinct()
            ->orderBy('company')
            ->pluck('company');

        $selectedCompany = $request->string('company')->toString();

        if ($selectedCompany !== '' && ! $companies->contains($selectedCompany)) {
            $selectedCompany = '';
        }

        $launches = ProductLaunch::query()
            ->ofCompany($selectedCompany ?: null)
            ->latestFirst()
            ->paginate(24)
            ->withQueryString();

        abort_if_page_out_of_range($launches);

        return view('product-launches.index', [
            'launches' => $launches,
            'companies' => $companies,
            'selectedCompany' => $selectedCompany ?: null,
        ]);
    }
}

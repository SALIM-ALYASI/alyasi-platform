<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ServerInfoController extends Controller
{
    /**
     * رابط أداة بيانات السيرفر الخارجية، معروضة داخل اللوحة عبر iframe.
     */
    private const URL = 'http://167.233.163.230:6060';

    public function index(): View
    {
        return view('admin.server-info.index', [
            'url' => self::URL,
        ]);
    }
}

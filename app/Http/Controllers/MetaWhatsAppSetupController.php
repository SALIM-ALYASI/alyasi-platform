<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * صفحة إدخال توكن واتساب (ميتا) لرقم "الياسي للبرمجيات" -- مؤقتة، تُحذف
 * بعد الإعداد. منفصلة تمامًا عن إعدادات رقم الباب على سيرفر البيت.
 */
class MetaWhatsAppSetupController extends Controller
{
    public function index(): View
    {
        return view('tools.meta-whatsapp-setup');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone_id' => ['required', 'string', 'max:64'],
            'meta_token' => ['required', 'string', 'max:2000'],
        ]);

        $this->setEnvValue('META_WHATSAPP_PHONE_ID', $validated['phone_id']);
        $this->setEnvValue('META_WHATSAPP_TOKEN', $validated['meta_token']);

        Artisan::call('config:clear');

        return redirect()
            ->route('tools.meta-whatsapp-setup')
            ->with('success', 'تم الحفظ. تقدر تسكّر هذي الصفحة الحين.');
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        $escaped = str_contains($value, ' ') || str_contains($value, '#')
            ? '"'.str_replace('"', '\\"', $value).'"'
            : $value;

        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $key.'='.$escaped, $content);
        } else {
            $content = rtrim($content)."\n".$key.'='.$escaped."\n";
        }

        file_put_contents($envPath, $content);
    }
}

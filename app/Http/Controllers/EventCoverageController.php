<?php

namespace App\Http\Controllers;

use App\Support\Coverage\CyberxOman2026;
use Illuminate\View\View;

class EventCoverageController extends Controller
{
    /**
     * تغطية «خمس محطات» الميدانية لمؤتمر CyberX Oman 2026 -- صفحة تابعة
     * لصفحة المؤتمر (/events/cyberx-oman-2026/coverage) بنفس هوية المنصة.
     */
    public function cyberxOman2026(): View
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

        return view('events.coverage.cyberx-oman-2026', [
            'coverage' => CyberxOman2026::content($locale),
            'isEn' => $locale === 'en',
            'eventUrl' => url(($locale === 'en' ? '/en' : '').'/events/'.CyberxOman2026::EVENT_SLUG),
            'urlAr' => url('/events/'.CyberxOman2026::EVENT_SLUG.'/coverage'),
            'urlEn' => url('/en/events/'.CyberxOman2026::EVENT_SLUG.'/coverage'),
        ]);
    }
}

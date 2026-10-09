<?php

namespace App\Http\Controllers;

use App\Support\Coverage\CyberxInterviews;
use App\Support\Coverage\CyberxOman2026;
use Illuminate\Http\RedirectResponse;
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

    /**
     * صفحة اللقاءات الصحفية مع الضيوف: المنشور منها يفتح، والباقي «قريبًا».
     */
    public function cyberxOman2026Interviews(): View
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

        return view('events.coverage.interviews.index', [
            'guests' => CyberxInterviews::guests($locale),
            'isEn' => $locale === 'en',
            'coverageUrl' => localized_route('event_coverage.cyberx_oman_2026'),
        ]);
    }

    /**
     * مقابلة ضيف واحد: سؤال سالم بصوته، وإجابة الضيف بصوته ونصها بتوقيت كل جملة.
     */
    public function cyberxOman2026Interview(string $guest): View|RedirectResponse
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $data = CyberxInterviews::guest($guest, $locale);

        abort_unless($data && $data['published'], 404);

        // المقابلة بلغة إجابات الضيف فقط -- رابط بلغة ثانية يروح للنسخة الموجودة.
        if (! in_array($locale, $data['languages'], true)) {
            $fallback = $data['languages'][0];

            return redirect()->to($fallback === 'en'
                ? route('event_coverage.cyberx_oman_2026.interview.en', ['guest' => $guest])
                : route('event_coverage.cyberx_oman_2026.interview', ['guest' => $guest]));
        }

        return view('events.coverage.interviews.show', [
            'guest' => $data,
            'others' => array_values(array_filter(CyberxInterviews::guests($locale), fn ($g) => $g['slug'] !== $guest)),
            'isEn' => $locale === 'en',
            'host' => CyberxOman2026::content($locale)['host'],
            'hubUrl' => localized_route('event_coverage.cyberx_oman_2026.interviews'),
            'coverageUrl' => localized_route('event_coverage.cyberx_oman_2026'),
            'urlAr' => route('event_coverage.cyberx_oman_2026.interview', ['guest' => $guest]),
            'urlEn' => route('event_coverage.cyberx_oman_2026.interview.en', ['guest' => $guest]),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Support\Coverage\CyberxInterviews;
use App\Support\Coverage\CyberxOman2026;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
            // اللي ما ردّ ما يظهر أبدًا -- بس المقابلات المنشورة، وبلغة الصفحة
            // (مقابلة بالعربي فقط، مثل خالد، ما تطلع بالنسخة الإنجليزية).
            'guests' => array_values(array_filter(
                CyberxInterviews::guests($locale),
                fn ($g) => $g['published'] && in_array($locale, $g['languages'], true)
            )),
            'isEn' => $locale === 'en',
            'coverageUrl' => localized_route('event_coverage.cyberx_oman_2026'),
        ]);
    }

    /**
     * مقابلة ضيف واحد: سؤال سالم بصوته، وإجابة الضيف بصوته ونصها بتوقيت كل جملة.
     */
    public function cyberxOman2026Interview(string $guest): View|RedirectResponse|Response
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $data = CyberxInterviews::guest($guest, $locale);

        // مقابلة مخفية، أو لغة لسا تحت المراجعة، تنفتح برابط معاينة موقّع
        // (URL::signedRoute بدون absolute -- التوقيع ما يتأثر بـ https خلف Cloudflare).
        $preview = $data
            && (! $data['published'] || ! in_array($locale, $data['languages'], true))
            && request()->hasValidRelativeSignature();

        abort_unless($data && ($data['published'] || $preview), 404);

        $languages = $preview ? [...$data['languages'], ...$data['preview_languages']] : $data['languages'];

        // المقابلة بلغة إجابات الضيف فقط -- رابط بلغة ثانية يروح للنسخة الموجودة.
        if (! in_array($locale, $languages, true)) {
            $fallback = $data['languages'][0];

            return redirect()->to($fallback === 'en'
                ? route('event_coverage.cyberx_oman_2026.interview.en', ['guest' => $guest])
                : route('event_coverage.cyberx_oman_2026.interview', ['guest' => $guest]));
        }

        $view = view('events.coverage.interviews.show', [
            'guest' => $data,
            'others' => array_values(array_filter(
                CyberxInterviews::guests($locale),
                fn ($g) => $g['published'] && $g['slug'] !== $guest && in_array($locale, $g['languages'], true)
            )),
            'isEn' => $locale === 'en',
            'host' => CyberxOman2026::content($locale)['host'],
            'hubUrl' => localized_route('event_coverage.cyberx_oman_2026.interviews'),
            'coverageUrl' => localized_route('event_coverage.cyberx_oman_2026'),
            'urlAr' => route('event_coverage.cyberx_oman_2026.interview', ['guest' => $guest]),
            'urlEn' => route('event_coverage.cyberx_oman_2026.interview.en', ['guest' => $guest]),
        ]);

        return $preview
            ? response($view)->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'no-store, private')
            : $view;
    }
}

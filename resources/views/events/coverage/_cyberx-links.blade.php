{{-- تغطية الياسي لسايبر إكس عُمان 2026 داخل صفحة المؤتمر نفسه: «خمس محطات» واللقاءات --}}
@php($isEn = app()->getLocale() === 'en')
<section class="container coverage-links" data-reveal>
    <div class="section-head">
        <div class="section-head__eyebrow">{{ $isEn ? 'ALYASI coverage · 2026' : 'تغطية الياسي · 2026' }}</div>
        <h2 class="section-head__title">{{ $isEn ? 'From CyberX Oman 2026' : 'من سايبر إكس عُمان 2026' }}</h2>
    </div>

    <div class="coverage-links__grid">
        <a class="coverage-links__card" href="{{ localized_route('event_coverage.cyberx_oman_2026') }}">
            <span class="coverage-links__media">
                <img src="{{ asset('images/events/cyberx-2026/coverage-hero.jpg') }}" alt="" loading="lazy" width="1672" height="941">
            </span>
            <span class="coverage-links__body">
                <span class="coverage-links__badge">{{ $isEn ? 'Field coverage' : 'تغطية ميدانية' }}</span>
                <strong>{{ $isEn ? 'Five Stops from CyberX Oman 2026' : 'خمس محطات من سايبر إكس عُمان 2026' }}</strong>
                <span class="coverage-links__desc">{{ $isEn
                    ? 'Breach readiness, continuity, healthcare security, and AI and its governance.'
                    : 'الاستعداد للاختراق، والاستمرارية، وأمن القطاع الصحي، والذكاء الاصطناعي وحوكمته.' }}</span>
                <span class="coverage-links__cta">{{ $isEn ? 'Read the coverage →' : 'اقرأ التغطية ←' }}</span>
            </span>
        </a>

        <a class="coverage-links__card" href="{{ localized_route('event_coverage.cyberx_oman_2026.interviews') }}">
            <span class="coverage-links__media">
                <img src="{{ asset('images/interviews/cyberx-oman-2026/haitham-al-hajri-og.jpg') }}" alt="" loading="lazy" width="1200" height="630">
            </span>
            <span class="coverage-links__body">
                <span class="coverage-links__badge">{{ $isEn ? 'Interviews' : 'لقاءات صحفية' }}</span>
                <strong>{{ $isEn ? 'Conversations with the guests' : 'لقاءات مع الضيوف' }}</strong>
                <span class="coverage-links__desc">{{ $isEn
                    ? 'My questions in my voice, their answers in theirs. Listen or read.'
                    : 'أسئلتي بصوتي، وإجاباتهم بأصواتهم. استمع أو اقرأ.' }}</span>
                <span class="coverage-links__cta">{{ $isEn ? 'Listen to the interviews →' : 'استمع للقاءات ←' }}</span>
            </span>
        </a>
    </div>
</section>

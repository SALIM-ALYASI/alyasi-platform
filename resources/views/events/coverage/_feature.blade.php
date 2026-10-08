{{-- بطاقة تعريف بتغطية «خمس محطات» -- تظهر بالرئيسية وصفحة الفعاليات --}}
@php($isEn = app()->getLocale() === 'en')
<section class="container coverage-feature" data-reveal>
    <a class="coverage-feature__card" href="{{ localized_route('event_coverage.cyberx_oman_2026') }}">
        <span class="coverage-feature__media">
            <img src="{{ asset('images/events/cyberx-2026/hall.jpg') }}" alt="" loading="lazy" width="1449" height="2576">
        </span>
        <span class="coverage-feature__body">
            <span class="coverage-feature__badge">{{ $isEn ? 'Field coverage · CyberX Oman 2026' : 'تغطية ميدانية · سايبر إكس عُمان 2026' }}</span>
            <strong class="coverage-feature__title">{{ $isEn ? 'Five Stops from CyberX Oman 2026' : 'خمس محطات من سايبر إكس عُمان 2026' }}</strong>
            <span class="coverage-feature__desc">{{ $isEn
                ? 'Breach readiness, continuity, healthcare security, and AI and its governance — field notes from Muscat.'
                : 'الاستعداد للاختراق، والاستمرارية، وأمن القطاع الصحي، والذكاء الاصطناعي وحوكمته — ملاحظات ميدانية من مسقط.' }}</span>
            <span class="coverage-feature__cta">{{ $isEn ? 'Read the coverage →' : 'اقرأ التغطية ←' }}</span>
        </span>
    </a>
</section>

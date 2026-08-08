@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = [
        'main_navigation' => $isArabic ? 'التنقل الرئيسي' : 'Main navigation',
        'home' => $isArabic ? 'الرئيسية' : 'Home',
        'services' => $isArabic ? 'الخدمات' : 'Services',
        'works' => $isArabic ? 'أعمالي' : 'My Works',
        'news' => $isArabic ? 'الأخبار' : 'News',
        'community' => $isArabic ? 'فعاليات المجتمع الرقمي' : 'Digital Community Events',
        'faq' => $isArabic ? 'الأسئلة الشائعة' : 'FAQ',
        'start_now' => $isArabic ? 'ابدأ الآن' : 'Get Started',
        'privacy' => $isArabic ? 'الخصوصية' : 'Privacy',
        'terms' => $isArabic ? 'الشروط' : 'Terms',
        'contact' => $isArabic ? 'التواصل' : 'Contact',
        'back_home' => $isArabic ? 'العودة إلى الصفحة الرئيسية' : 'Back to homepage',
        'open_menu' => $isArabic ? 'فتح القائمة' : 'Open menu',
    ];
@endphp

{{-- Background effects --}}
<svg class="grain" width="100%" height="100%" aria-hidden="true">
    <filter id="grainNoise">
        <feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="5" stitchTiles="stitch" />
        <feColorMatrix type="saturate" values="0" />
    </filter>
    <rect width="100%" height="100%" filter="url(#grainNoise)" />
</svg>

<div class="atmosphere" aria-hidden="true"></div>

<header class="site-header" id="siteHeader">
    <div class="site-header__inner">

        <a href="{{ route('home') }}" class="brand" aria-label="{{ $labels['back_home'] }}">
            <span class="brand__mark">A</span>
            <span class="brand__name">ALYASI</span>
        </a>

        <nav class="main-nav" aria-label="{{ $labels['main_navigation'] }}">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}" @if (request()->routeIs('home')) aria-current="page" @endif>{{ $labels['home'] }}</a>
            <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'is-active' : '' }}" @if (request()->routeIs('services.*')) aria-current="page" @endif>{{ $labels['services'] }}</a>
            <a href="{{ route('works.index') }}" class="nav-link {{ request()->routeIs('works.*') ? 'is-active' : '' }}" @if (request()->routeIs('works.*')) aria-current="page" @endif>{{ $labels['works'] }}</a>
            <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.*') ? 'is-active' : '' }}" @if (request()->routeIs('news.*')) aria-current="page" @endif>{{ $labels['news'] }}</a>
            <a href="{{ route('community.index') }}" class="nav-link {{ request()->routeIs('community.*') ? 'is-active' : '' }}" @if (request()->routeIs('community.*')) aria-current="page" @endif>{{ $labels['community'] }}</a>
        </nav>

        <div class="header-actions">
            <div class="lang-switch">
                <a href="{{ route('locale.switch', 'ar') }}" class="{{ $isArabic ? 'is-active' : '' }}">AR</a>
                <span>/</span>
                <a href="{{ route('locale.switch', 'en') }}" class="{{ ! $isArabic ? 'is-active' : '' }}">EN</a>
            </div>

            <a href="{{ route('home') }}#contact" class="btn btn--primary btn--sm">{{ $labels['start_now'] }}</a>

            <button type="button" class="nav-toggle" id="navToggle" aria-label="{{ $labels['open_menu'] }}" aria-expanded="false" aria-controls="mobileMenu">
                <span></span>
                <span></span>
            </button>
        </div>

    </div>
</header>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <a href="{{ route('home') }}" class="mobile-menu__link {{ request()->routeIs('home') ? 'is-active' : '' }}">{{ $labels['home'] }}</a>
    <a href="{{ route('services.index') }}" class="mobile-menu__link {{ request()->routeIs('services.*') ? 'is-active' : '' }}">{{ $labels['services'] }}</a>
    <a href="{{ route('works.index') }}" class="mobile-menu__link {{ request()->routeIs('works.*') ? 'is-active' : '' }}">{{ $labels['works'] }}</a>
    <a href="{{ route('news.index') }}" class="mobile-menu__link {{ request()->routeIs('news.*') ? 'is-active' : '' }}">{{ $labels['news'] }}</a>
    <a href="{{ route('community.index') }}" class="mobile-menu__link {{ request()->routeIs('community.*') ? 'is-active' : '' }}">{{ $labels['community'] }}</a>
    <a href="{{ route('home') }}#faq" class="mobile-menu__link">{{ $labels['faq'] }}</a>
    <a href="{{ route('home') }}#contact" class="btn btn--primary">{{ $labels['start_now'] }}</a>

    <div class="mobile-menu__footer">
        <a href="{{ route('locale.switch', 'ar') }}" class="{{ $isArabic ? 'is-active' : '' }}">العربية</a>
        <a href="{{ route('locale.switch', 'en') }}" class="{{ ! $isArabic ? 'is-active' : '' }}">English</a>
    </div>
</div>

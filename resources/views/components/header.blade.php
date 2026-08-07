@php
    $isArabic = app()->getLocale() === 'ar';

    $labels = [
        'main_navigation' => $isArabic
            ? 'التنقل الرئيسي'
            : 'Main navigation',

        'home' => $isArabic
            ? 'الرئيسية'
            : 'Home',

        'services' => $isArabic
            ? 'الخدمات'
            : 'Services',

        'works' => $isArabic
            ? 'أعمالي'
            : 'My Works',

        'news' => $isArabic
            ? 'الأخبار'
            : 'News',

        'community' => $isArabic
            ? 'فعاليات المجتمع الرقمي'
            : 'Digital Community Events',

        'technology' => $isArabic
            ? 'التقنيات'
            : 'Technologies',

        'faq' => $isArabic
            ? 'الأسئلة الشائعة'
            : 'FAQ',

        'start_now' => $isArabic
            ? 'ابدأ الآن'
            : 'Get Started',

        'privacy' => $isArabic
            ? 'الخصوصية'
            : 'Privacy',

        'terms' => $isArabic
            ? 'الشروط'
            : 'Terms',

        'contact' => $isArabic
            ? 'التواصل'
            : 'Contact',

        'back_home' => $isArabic
            ? 'العودة إلى الصفحة الرئيسية'
            : 'Back to homepage',

        'open_menu' => $isArabic
            ? 'فتح القائمة'
            : 'Open menu',
    ];
@endphp

{{-- Background effects --}}
<svg
    class="grain"
    width="100%"
    height="100%"
    aria-hidden="true"
>
    <filter id="grainNoise">

        <feTurbulence
            type="fractalNoise"
            baseFrequency="0.65"
            numOctaves="5"
            stitchTiles="stitch"
        />

        <feColorMatrix
            type="saturate"
            values="0"
        />

    </filter>

    <rect
        width="100%"
        height="100%"
        filter="url(#grainNoise)"
    />
</svg>

<div
    class="atmosphere"
    aria-hidden="true"
></div>

{{-- Main Navbar --}}
<nav
    class="top-nav"
    id="topNav"
    aria-label="{{ $labels['main_navigation'] }}"
>
    <a
        href="{{ route('home') }}"
        class="nav-brand"
        aria-label="{{ $labels['back_home'] }}"
    >
        ALYASI

        <span>
            Platform
        </span>
    </a>

    <ul class="nav-links">

        {{-- Home --}}
        <li>
            <a
                href="{{ route('home') }}"
                class="{{ request()->routeIs('home') ? 'active' : '' }}"
                @if (request()->routeIs('home'))
                    aria-current="page"
                @endif
            >
                {{ $labels['home'] }}
            </a>
        </li>

        {{-- Services --}}
        <li>
            <a
                href="{{ route('services.index') }}"
                class="{{ request()->routeIs('services.*') ? 'active' : '' }}"
                @if (request()->routeIs('services.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['services'] }}
            </a>
        </li>

        {{-- Works --}}
        <li>
            <a
                href="{{ route('works.index') }}"
                class="{{ request()->routeIs('works.*') ? 'active' : '' }}"
                @if (request()->routeIs('works.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['works'] }}
            </a>
        </li>

        {{-- News --}}
        <li>
            <a
                href="{{ route('news.index') }}"
                class="{{ request()->routeIs('news.*') ? 'active' : '' }}"
                @if (request()->routeIs('news.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['news'] }}
            </a>
        </li>

        {{-- Community --}}
        <li>
            <a
                href="{{ route('community.index') }}"
                class="{{ request()->routeIs('community.*') ? 'active' : '' }}"
                @if (request()->routeIs('community.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['community'] }}
            </a>
        </li>

        {{-- Technologies --}}
        <li>
            <a
                href="{{ route('home') }}#technology"
                class="{{ request()->routeIs('home')
                    && request()->is('*#technology')
                        ? 'active'
                        : '' }}"
            >
                {{ $labels['technology'] }}
            </a>
        </li>

        {{-- FAQ --}}
        <li>
            <a href="{{ route('home') }}#faq">
                {{ $labels['faq'] }}
            </a>
        </li>

    </ul>

    {{-- Language Switch --}}
    <div class="nav-lang-switch">

        <a
            href="{{ route('locale.switch', 'ar') }}"
            class="{{ $isArabic ? 'active' : '' }}"
        >
            AR
        </a>

        <span>/</span>

        <a
            href="{{ route('locale.switch', 'en') }}"
            class="{{ ! $isArabic ? 'active' : '' }}"
        >
            EN
        </a>

    </div>

    {{-- CTA --}}
    <a
        href="{{ route('home') }}#contact"
        class="nav-cta"
    >
        {{ $labels['start_now'] }}
    </a>

    {{-- Mobile Menu Toggle --}}
    <button
        type="button"
        class="nav-toggle"
        id="navToggle"
        aria-label="{{ $labels['open_menu'] }}"
        aria-expanded="false"
        aria-controls="mobileMenu"
    >
        <span class="toggle-bar"></span>
        <span class="toggle-bar"></span>
    </button>
</nav>

{{-- Mobile Menu --}}
<div
    class="mobile-menu"
    id="mobileMenu"
    aria-hidden="true"
>
    <div class="mobile-menu-inner">

        <div class="mobile-menu-links">

            {{-- Home --}}
            <a
                href="{{ route('home') }}"
                class="mobile-menu-link {{ request()->routeIs('home')
                    ? 'active'
                    : '' }}"
                data-index="01"
                @if (request()->routeIs('home'))
                    aria-current="page"
                @endif
            >
                {{ $labels['home'] }}
            </a>

            {{-- Services --}}
            <a
                href="{{ route('services.index') }}"
                class="mobile-menu-link {{ request()->routeIs('services.*')
                    ? 'active'
                    : '' }}"
                data-index="02"
                @if (request()->routeIs('services.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['services'] }}
            </a>

            {{-- Works --}}
            <a
                href="{{ route('works.index') }}"
                class="mobile-menu-link {{ request()->routeIs('works.*')
                    ? 'active'
                    : '' }}"
                data-index="03"
                @if (request()->routeIs('works.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['works'] }}
            </a>

            {{-- News --}}
            <a
                href="{{ route('news.index') }}"
                class="mobile-menu-link {{ request()->routeIs('news.*')
                    ? 'active'
                    : '' }}"
                data-index="04"
                @if (request()->routeIs('news.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['news'] }}
            </a>

            {{-- Community --}}
            <a
                href="{{ route('community.index') }}"
                class="mobile-menu-link {{ request()->routeIs('community.*')
                    ? 'active'
                    : '' }}"
                data-index="05"
                @if (request()->routeIs('community.*'))
                    aria-current="page"
                @endif
            >
                {{ $labels['community'] }}
            </a>

            {{-- Technologies --}}
            <a
                href="{{ route('home') }}#technology"
                class="mobile-menu-link"
                data-index="06"
            >
                {{ $labels['technology'] }}
            </a>

            {{-- FAQ --}}
            <a
                href="{{ route('home') }}#faq"
                class="mobile-menu-link"
                data-index="07"
            >
                {{ $labels['faq'] }}
            </a>

            {{-- CTA --}}
            <a
                href="{{ route('home') }}#contact"
                class="mobile-menu-link"
                data-index="08"
            >
                {{ $labels['start_now'] }}
            </a>

        </div>

        <div class="mobile-menu-lang">

            <a
                href="{{ route('locale.switch', 'ar') }}"
                class="{{ $isArabic ? 'active' : '' }}"
            >
                العربية
            </a>

            <a
                href="{{ route('locale.switch', 'en') }}"
                class="{{ ! $isArabic ? 'active' : '' }}"
            >
                English
            </a>

        </div>

        <div class="mobile-menu-footer">

            <a href="{{ route('privacy') }}">
                {{ $labels['privacy'] }}
            </a>

            <a href="{{ route('terms') }}">
                {{ $labels['terms'] }}
            </a>

            <a href="{{ route('contact') }}">
                {{ $labels['contact'] }}
            </a>

        </div>

    </div>
</div>
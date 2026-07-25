{{-- Background effects --}}
<svg class="grain" width="100%" height="100%" aria-hidden="true">
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

<div class="atmosphere" aria-hidden="true"></div>

{{-- Main Navbar --}}
<nav
    class="top-nav"
    id="topNav"
    aria-label="التنقل الرئيسي"
>
    <a
        href="{{ route('home') }}"
        class="nav-brand"
        aria-label="العودة إلى الصفحة الرئيسية"
    >
        ALYASI
        <span>Platform</span>
    </a>

    <ul class="nav-links">
        <li>
            <a
                href="{{ route('home') }}"
                class="{{ request()->routeIs('home') ? 'active' : '' }}"
            >
                الرئيسية
            </a>
        </li>

        <li>
            <a
                href="{{ route('services.index') }}"
                class="{{ request()->routeIs('services.*') ? 'active' : '' }}"
            >
                الخدمات
            </a>
        </li>

        <li>
            <a
                href="{{ route('news.index') }}"
                class="{{ request()->routeIs('news.*') ? 'active' : '' }}"
            >
                الأخبار
            </a>
        </li>

        <li>
            <a
                href="{{ route('community.index') }}"
                class="{{ request()->routeIs('community.*') ? 'active' : '' }}"
            >
                المجتمع
            </a>
        </li>

        <li>
            <a href="{{ route('home') }}#technology">
                التقنيات
            </a>
        </li>

        <li>
            <a href="{{ route('home') }}#faq">
                الأسئلة الشائعة
            </a>
        </li>
    </ul>

    <a
        href="{{ route('home') }}#contact"
        class="nav-cta"
    >
        ابدأ الآن
    </a>

    <button
        type="button"
        class="nav-toggle"
        id="navToggle"
        aria-label="فتح القائمة"
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

            <a
                href="{{ route('home') }}"
                class="mobile-menu-link"
                data-index="01"
            >
                الرئيسية
            </a>

            <a
                href="{{ route('services.index') }}"
                class="mobile-menu-link"
                data-index="02"
            >
                الخدمات
            </a>

            <a
                href="{{ route('news.index') }}"
                class="mobile-menu-link"
                data-index="03"
            >
                الأخبار
            </a>

            <a
                href="{{ route('community.index') }}"
                class="mobile-menu-link"
                data-index="04"
            >
                المجتمع
            </a>

            <a
                href="{{ route('home') }}#technology"
                class="mobile-menu-link"
                data-index="05"
            >
                التقنيات
            </a>

            <a
                href="{{ route('home') }}#faq"
                class="mobile-menu-link"
                data-index="06"
            >
                الأسئلة الشائعة
            </a>

            <a
                href="{{ route('home') }}#contact"
                class="mobile-menu-link"
                data-index="07"
            >
                ابدأ الآن
            </a>

        </div>

        <div class="mobile-menu-footer">
            <a href="#">الخصوصية</a>
            <a href="#">الشروط</a>
            <a href="{{ route('home') }}#contact">التواصل</a>
        </div>

    </div>
</div>
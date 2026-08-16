<!DOCTYPE html>

@php
    $isArabic = app()->getLocale() === 'ar';

    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    */

    $pageTitle = trim($__env->yieldContent('title')) ?: 'ALYASI';

    $pageDescription = trim($__env->yieldContent('meta_description'))
        ?: __('home.hero_description');

    $seoCanonical = trim($__env->yieldContent('canonical'))
        ?: url()->current();

    $seoTitle = trim($__env->yieldContent('og_title'))
        ?: $pageTitle;

    $seoDescription = trim($__env->yieldContent('og_description'))
        ?: $pageDescription;

    $seoType = trim($__env->yieldContent('og_type'))
        ?: 'website';

    $seoUrl = trim($__env->yieldContent('og_url'))
        ?: $seoCanonical;

    $seoImage = trim($__env->yieldContent('og_image'))
        ?: asset('images/logo/favicon-navy.png');

    $seoLocale = $isArabic ? 'ar_OM' : 'en_US';
@endphp


<html
    lang="{{ $isArabic ? 'ar' : 'en' }}"
    dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
>

<head>

    {{-- =====================================================
         Basic Meta
    ====================================================== --}}

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $pageTitle }}</title>

    <meta
        name="description"
        content="{{ $pageDescription }}"
    >

    <meta
        name="theme-color"
        content="#0B1F3A"
    >


    {{-- =====================================================
         Canonical
    ====================================================== --}}

    <link
        rel="canonical"
        href="{{ $seoCanonical }}"
    >


    {{-- =====================================================
         hreflang
    ======================================================

         ملاحظة:
         عموم الموقع يستخدم Session لتبديل اللغة وليس URL منفصل،
         لذلك افتراضيًا نعلن لغة الصفحة الحالية فقط + x-default.

         الصفحات التي لها فعليًا رابط مستقل لكل لغة (مثل المقالات)
         تمرر hreflang_ar / hreflang_en عبر @@section فتُستبدل
         بروابط AR/EN حقيقية + x-default يشير للعربية.
    ====================================================== --}}

    @php
        $hreflangAr = trim($__env->yieldContent('hreflang_ar'));
        $hreflangEn = trim($__env->yieldContent('hreflang_en'));
    @endphp

    @if ($hreflangAr)

        <link
            rel="alternate"
            hreflang="ar"
            href="{{ $hreflangAr }}"
        >

        @if ($hreflangEn)
            <link
                rel="alternate"
                hreflang="en"
                href="{{ $hreflangEn }}"
            >
        @endif

        <link
            rel="alternate"
            hreflang="x-default"
            href="{{ $hreflangAr }}"
        >

    @else

        <link
            rel="alternate"
            hreflang="{{ $isArabic ? 'ar-OM' : 'en' }}"
            href="{{ $seoCanonical }}"
        >

        <link
            rel="alternate"
            hreflang="x-default"
            href="{{ $seoCanonical }}"
        >

    @endif


    {{-- =====================================================
         Open Graph
         WhatsApp / LinkedIn / Facebook
    ====================================================== --}}

    <meta
        property="og:type"
        content="{{ $seoType }}"
    >

    <meta
        property="og:site_name"
        content="ALYASI"
    >

    <meta
        property="og:title"
        content="{{ $seoTitle }}"
    >

    <meta
        property="og:description"
        content="{{ $seoDescription }}"
    >

    <meta
        property="og:url"
        content="{{ $seoUrl }}"
    >

    <meta
        property="og:image"
        content="{{ $seoImage }}"
    >

    <meta
        property="og:image:alt"
        content="{{ $seoTitle }}"
    >

    @hasSection('og_image_width')
        <meta
            property="og:image:width"
            content="@yield('og_image_width')"
        >
    @endif

    @hasSection('og_image_height')
        <meta
            property="og:image:height"
            content="@yield('og_image_height')"
        >
    @endif

    <meta
        property="og:locale"
        content="{{ $seoLocale }}"
    >


    {{-- =====================================================
         Article Open Graph
         يظهر فقط في صفحات المقالات/الأخبار
    ====================================================== --}}

    @if ($seoType === 'article')

        @hasSection('article_published_time')
            <meta
                property="article:published_time"
                content="@yield('article_published_time')"
            >
        @endif

        @hasSection('article_modified_time')
            <meta
                property="article:modified_time"
                content="@yield('article_modified_time')"
            >
        @endif

    @endif


    {{-- =====================================================
         Twitter / X Card
    ====================================================== --}}

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="{{ $seoTitle }}"
    >

    <meta
        name="twitter:description"
        content="{{ $seoDescription }}"
    >

    <meta
        name="twitter:image"
        content="{{ $seoImage }}"
    >

    <meta
        name="twitter:image:alt"
        content="{{ $seoTitle }}"
    >


    {{-- =====================================================
         Favicon
    ====================================================== --}}

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/logo/favicon-navy.png') }}"
    >


    {{-- =====================================================
         Fonts
    ====================================================== --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@500;700;800&family=Tajawal:wght@400;500;700;900&display=swap"
        rel="stylesheet"
    >


    {{-- =====================================================
         Font Awesome
    ====================================================== --}}

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        referrerpolicy="no-referrer"
    >


    {{-- =====================================================
         Shared Styles
    ====================================================== --}}

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/shared/tokens.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/shared/base.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/shared/header.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/shared/footer.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/shared/pagination.css') }}"
    >


    {{-- =====================================================
         Bing Webmaster Verification
    ====================================================== --}}

    <meta
        name="msvalidate.01"
        content="6AB900B487D367C05F065BE5B2B1A6D1"
    >


    {{-- =====================================================
         Page Specific Styles
    ====================================================== --}}

    @stack('styles')


    {{-- =====================================================
         Structured Data / JSON-LD
         Organization + WebSite ثابتة بكل صفحة، تُستخدم كمرجع (@id)
         من أي مخطط ثاني بالصفحة (مثل Article) بدل تكرار الكائن كامل.
    ====================================================== --}}

    @php
        $organizationId = url('/').'#organization';
        $websiteId = url('/').'#website';

        $organizationSchema = [
            '@'.'type' => 'Organization',
            '@'.'id' => $organizationId,
            'name' => 'ALYASI',
            'url' => url('/'),
            'logo' => asset('images/logo/logo-icon-navy.png'),
        ];

        $organizationSocialLinks = \App\Models\SocialLink::forDisplay()
            ->pluck('url')
            ->filter()
            ->values();

        if ($organizationSocialLinks->isNotEmpty()) {
            $organizationSchema['sameAs'] = $organizationSocialLinks;
        }

        $websiteSchema = [
            '@'.'type' => 'WebSite',
            '@'.'id' => $websiteId,
            'name' => 'ALYASI',
            'url' => url('/'),
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@'.'id' => $organizationId],
        ];
    @endphp

    <script type="application/ld+json">
    {!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@'.'graph' => [$organizationSchema, $websiteSchema],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @yield('schema')

</head>


<body dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

    <x-header />


    <main>

        @yield('content')

    </main>


    <x-footer />


    {{-- =====================================================
         Shared Scripts
    ====================================================== --}}

    <script
        src="{{ versioned_asset('js/shared/header.js') }}"
        defer
    ></script>

    <script
        src="{{ versioned_asset('js/shared/reveal.js') }}"
        defer
    ></script>


    {{-- =====================================================
         Page Specific Scripts
    ====================================================== --}}

    @stack('scripts')

</body>

</html>
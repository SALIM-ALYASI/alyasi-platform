<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="@yield('description', 'منصة ALYASI للخدمات الرقمية والأخبار التقنية والمجتمع.')"
    >

    <meta name="theme-color" content="#5E2324">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>@yield('title', __('home.brand'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/logo-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@700;800&display=swap"
        rel="stylesheet"
    >

    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        referrerpolicy="no-referrer"
    >

    {{-- ALYASI design system (2026-08 redesign) --}}
    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/alyasi.css') }}"
    >

    @stack('styles')
</head>

<body>

    <x-header />

    <main class="main">
        @yield('content')
    </main>

    <x-footer />

    <script src="{{ versioned_asset('js/alyasi.js') }}"></script>

    @stack('scripts')

</body>

</html>
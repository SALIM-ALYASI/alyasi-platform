<!DOCTYPE html>
@php $isArabic = app()->getLocale() === 'ar'; @endphp
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ALYASI')</title>
    <meta name="description" content="@yield('meta_description', __('home.hero_description'))">
    <meta name="theme-color" content="#0B1F3A">
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon-navy.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@500;700;800&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="{{ versioned_asset('css/shared/tokens.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/base.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/header.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/footer.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/pagination.css') }}">

    @stack('styles')
</head>

<body dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

    <x-header />

    <main>
        @yield('content')
    </main>

    <x-footer />

    <script src="{{ versioned_asset('js/shared/header.js') }}" defer></script>
    <script src="{{ versioned_asset('js/shared/reveal.js') }}" defer></script>

    @stack('scripts')
</body>

</html>

@props([
    'code' => '',
    'icon' => 'fa-solid fa-triangle-exclamation',
    'titleAr' => '',
    'titleEn' => '',
    'messageAr' => '',
    'messageEn' => '',
    'showHomeButton' => true,
])

@php
    $isArabic = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} — {{ $isArabic ? $titleAr : $titleEn }} | ALYASI</title>
    <meta name="theme-color" content="#0B1F3A">
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon-navy.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Noto+Kufi+Arabic:wght@500;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="{{ versioned_asset('css/shared/tokens.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/base.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/error-page.css') }}">
</head>

<body dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

    <div class="error-page">

        <div class="error-page-icon">
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </div>

        @if ($code !== '')
            <div class="error-page-code">{{ $code }}</div>
        @endif

        <h1>{{ $isArabic ? $titleAr : $titleEn }}</h1>

        <p>{{ $isArabic ? $messageAr : $messageEn }}</p>

        @if ($showHomeButton)
            <a href="{{ localized_route('home') }}" class="btn btn--primary">
                {{ $isArabic ? 'العودة إلى الرئيسية' : 'Back to Homepage' }}
            </a>
        @endif

    </div>

</body>

</html>

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
    <meta name="theme-color" content="#180B0F">
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="{{ versioned_asset('luminary/templatemo-621-luminary-style.css') }}">

    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 24px;
        }

        .error-page-icon {
            display: grid;
            place-items: center;
            width: 88px;
            height: 88px;
            margin-bottom: 32px;
            border-radius: 28px;
            border: 1px solid rgba(208, 170, 106, 0.18);
            background: rgba(42, 19, 25, 0.55);
            backdrop-filter: blur(18px);
            font-size: 34px;
            color: var(--gold-light);
        }

        .error-page-code {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1;
            background: linear-gradient(135deg, var(--ivory), var(--gold-light), var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
        }

        .error-page h1 {
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 500;
            color: var(--ivory);
            margin-bottom: 16px;
        }

        .error-page p {
            max-width: 480px;
            color: var(--slate-light);
            line-height: 1.9;
            font-size: 1rem;
            margin-bottom: 36px;
        }
    </style>
</head>

<body>

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
            <a href="{{ route('home') }}" class="btn-primary">
                {{ $isArabic ? 'العودة إلى الرئيسية' : 'Back to Homepage' }}
            </a>
        @endif

    </div>

</body>

</html>

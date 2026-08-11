@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'Markify | اصنع بصمتك' : 'Markify | Make Your Mark',

        'description' => $isArabic
            ? 'Markify — حلول طباعة وهوية بصرية: بطاقات أعمال، هدايا مخصصة، وأكثر.'
            : 'Markify — printing and visual identity solutions: business cards, custom gifts, and more.',

        'hero_quote' => $isArabic
            ? '&ldquo;ليست الطباعة مجرد حبر على ورق، بل بصمة تحمل هويتك وتترك أثرًا يبقى.&rdquo;'
            : '&ldquo;Printing isn&rsquo;t just ink on paper — it&rsquo;s a mark that carries your identity and leaves a lasting impression.&rdquo;',

        'brand_logo_alt' => $isArabic ? 'شعار Markify' : 'Markify logo',
        'brand_name' => 'Markify',
        'brand_tagline' => 'Make Your Mark',

        'feature_1_body' => $isArabic
            ? 'من بطاقات الأعمال إلى الهدايا الشخصية، نحوّل أفكارك إلى تصاميم مطبوعة بجودة احترافية تعكس هويتك التجارية.'
            : 'From business cards to personalized gifts, we turn your ideas into professionally printed designs that reflect your brand identity.',

        'learn_more' => $isArabic ? 'اعرف أكثر' : 'Learn More',
        'feature_image_alt' => $isArabic ? 'من أعمال Markify' : 'From Markify\'s work',

        'footer_logo_alt' => 'Markify',
        'footer_copyright' => $isArabic
            ? 'جميع الحقوق محفوظة &copy; Markify 2026'
            : '&copy; 2026 Markify. All rights reserved.',
        'footer_follow' => $isArabic ? 'تابعنا على' : 'Follow us on',

        'about_title' => $isArabic ? 'Markify | اصنع بصمتك' : 'Markify | Make Your Mark',
        'about_body' => $isArabic
            ? 'هواية طباعية تحوّلت إلى أعمال مميزة. نقدّم حلول طباعة وهوية بصرية متكاملة: بطاقات أعمال، دفاتر، هدايا مخصصة وأكثر — بعناية تليق باسمك.'
            : 'A printing hobby that grew into distinctive work. We offer complete printing and visual identity solutions: business cards, notebooks, custom gifts, and more — with care that lives up to your name.',
        'about_follow' => $isArabic ? 'تابعنا' : 'Follow us',
        'about_follow_suffix' => $isArabic ? 'على Instagram لمشاهدة أحدث التصاميم.' : 'on Instagram to see our latest designs.',

        'qr_alt' => $isArabic
            ? 'امسح رمز QR لزيارة حساب Markify على Instagram — @markifyprints'
            : 'Scan the QR code to visit Markify\'s Instagram — @markifyprints',
    ];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $t['title'] }}</title>
    <meta name="description" content="{{ $t['description'] }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('markify-assets/img/markify/profile.jpg') }}">
<!--
Markify — مبني على قالب Tooplate 2110 Character
http://www.tooplate.com/view/2110-character
-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600">
    <!-- https://fonts.google.com/specimen/Open+Sans -->
    <link rel="stylesheet" href="{{ versioned_asset('markify-assets/css/all.min.css') }}">
    <!-- https://fontawesome.com/ -->
    <link rel="stylesheet" href="{{ versioned_asset('markify-assets/css/tooplate-style.css') }}">
<meta name="msvalidate.01" content="6AB900B487D367C05F065BE5B2B1A6D1" />
    <style>
        .tm-lang-switch {
            position: fixed;
            top: 20px;
            inset-inline-end: 20px;
            z-index: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 100px;
            background: rgba(20, 20, 20, 0.75);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .tm-lang-switch a {
            color: #cfc6b8;
            text-decoration: none;
        }

        .tm-lang-switch a.active {
            color: #cdb27e;
        }

        .tm-lang-switch span {
            color: #cfc6b8;
            opacity: 0.5;
        }
    </style>
</head>

<body class="tm-bg-dark">

    <div class="tm-lang-switch">
        <a href="{{ route('locale.switch', 'ar') }}" class="{{ $isArabic ? 'active' : '' }}">AR</a>
        <span>/</span>
        <a href="{{ route('locale.switch', 'en') }}" class="{{ ! $isArabic ? 'active' : '' }}">EN</a>
    </div>

    <main class="tm-container masonry">
        <div class="item tm-bg-white tm-block tm-block-left" data-desktop-seq-no="1" data-mobile-seq-no="1">
            <p class="tm-hero-text" @if ($isArabic) lang="ar" @endif>{!! $t['hero_quote'] !!}</p>
            <header class="tm-block-brand">
                <div class="tm-bg-primary-dark tm-text-white tm-block-brand-inner">
                    <img src="{{ asset('markify-assets/img/markify/profile.jpg') }}" alt="{{ $t['brand_logo_alt'] }}" class="tm-brand-logo">
                    <div>
                        <h1 class="tm-brand-name">{{ $t['brand_name'] }}</h1>
                        <p class="tm-brand-tagline tm-text-uppercase">{{ $t['brand_tagline'] }}</p>
                    </div>
                </div>
            </header>
        </div>
        <div class="item" data-desktop-seq-no="2" data-mobile-seq-no="4">
            <img src="{{ asset('markify-assets/img/markify/' . $featureImages[0]) }}" alt="{{ $t['feature_image_alt'] }}" class="tm-img-left">
        </div>
        <div class="item tm-bg-secondary tm-text-white tm-block tm-block-wider tm-block-pad tm-block-left-2" data-desktop-seq-no="3"
            data-mobile-seq-no="5">
            <i class="fas fa-stamp fa-4x tm-block-icon"></i>
            <p @if ($isArabic) lang="ar" @endif>{{ $t['feature_1_body'] }}</p>
            <div class="tm-text-right">
                <a href="https://www.instagram.com/markifyprints" target="_blank" rel="noopener noreferrer" class="tm-btn tm-btn-small tm-btn-primary tm-mt" @if ($isArabic) lang="ar" @endif>{{ $t['learn_more'] }}</a>
            </div>
        </div>
        <div class="item" data-desktop-seq-no="4" data-mobile-seq-no="8">
            <img src="{{ asset('markify-assets/img/markify/' . $featureImages[1]) }}" alt="{{ $t['feature_image_alt'] }}" class="tm-img-left">
        </div>
        <div class="tm-footer" id="tmFooter" data-desktop-seq-no="5" data-mobile-seq-no="9">
            <img src="{{ asset('markify-assets/img/markify/profile.jpg') }}" alt="{{ $t['footer_logo_alt'] }}" class="tm-img-qr">
            <div>
                <p class="tm-mb-small" @if ($isArabic) lang="ar" @endif>{!! $t['footer_copyright'] !!}</p>
                <p @if ($isArabic) lang="ar" @endif>{{ $t['footer_follow'] }} <a rel="noopener noreferrer" target="_blank" href="https://www.instagram.com/markifyprints">Instagram</a></p>
            </div>
        </div>
        <div class="item" data-desktop-seq-no="6" data-mobile-seq-no="2">
            <img src="{{ asset('markify-assets/img/markify/' . $featureImages[2]) }}" alt="{{ $t['feature_image_alt'] }}">
        </div>
        <div class="item tm-block-right" data-desktop-seq-no="7" data-mobile-seq-no="3">
            <div class="tm-block-right-inner tm-bg-primary-light tm-text-white tm-block tm-block-wider tm-block-pad">
                <header>
                    <h2 class="tm-text-uppercase" @if ($isArabic) lang="ar" @endif>
                        {{ $t['about_title'] }}
                    </h2>
                </header>
                <p @if ($isArabic) lang="ar" @endif>{{ $t['about_body'] }}</p>
                <p class="tm-mt tm-mb-small" @if ($isArabic) lang="ar" @endif>{{ $t['about_follow'] }} <a href="https://www.instagram.com/markifyprints" target="_blank" rel="noopener noreferrer" class="tm-ig-handle">@markifyprints</a> {{ $t['about_follow_suffix'] }}
                </p>
                <!-- -->
            </div>
        </div>

        <div class="item" data-desktop-seq-no="8" data-mobile-seq-no="6">
            <img src="{{ asset('markify-assets/img/markify/' . $featureImages[3]) }}" alt="{{ $t['feature_image_alt'] }}">
        </div>

        <div class="item tm-bg-white tm-block tm-form-section" data-desktop-seq-no="9" data-mobile-seq-no="7">
            <a href="https://www.instagram.com/markifyprints" target="_blank" rel="noopener noreferrer" class="tm-ig-qr-link">
                <img src="{{ asset('markify-assets/img/markify/download.webp') }}" alt="{{ $t['qr_alt'] }}" class="tm-ig-qr-image">
            </a>
        </div>

    </main>
    <script src="{{ versioned_asset('markify-assets/js/jquery-3.3.1.min.js') }}"></script>
    <!-- https://jquery.com/download/ -->
    <script>

        let callAdjustLayout;
        let currentLayout = "desktop",
            nextLayout = "desktop";

        /**
         * detect IE
         * returns version of IE or false, if browser is not Internet Explorer
         */
        function detectIE() {
            var ua = window.navigator.userAgent;

            var msie = ua.indexOf('MSIE ');
            if (msie > 0) {
                // IE 10 or older => return version number
                return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
            }

            var trident = ua.indexOf('Trident/');
            if (trident > 0) {
                // IE 11 => return version number
                var rv = ua.indexOf('rv:');
                return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
            }

            var edge = ua.indexOf('Edge/');
            if (edge > 0) {
                // Edge (IE 12+) => return version number
                return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
            }

            // other browser
            return false;
        }

        // Adjust layout based on the browser width
        function adjustLayout() {
            let block1, block2, block3, block4, block5, block6, block7, block8, block9;

            if (window.innerWidth <= 1199) {
                // Mobile layout
                nextLayout = "mobile";
                block1 = $("div[data-mobile-seq-no='1']");
                block2 = $("div[data-mobile-seq-no='2']");
                block3 = $("div[data-mobile-seq-no='3']");
                block4 = $("div[data-mobile-seq-no='4']");
                block5 = $("div[data-mobile-seq-no='5']");
                block6 = $("div[data-mobile-seq-no='6']");
                block7 = $("div[data-mobile-seq-no='7']");
                block8 = $("div[data-mobile-seq-no='8']");
                block9 = $("div[data-mobile-seq-no='9']");
            } else {
                // Desktop layout
                nextLayout = "desktop";
                block1 = $("div[data-desktop-seq-no='1']");
                block2 = $("div[data-desktop-seq-no='2']");
                block3 = $("div[data-desktop-seq-no='3']");
                block4 = $("div[data-desktop-seq-no='4']");
                block5 = $("div[data-desktop-seq-no='5']");
                block6 = $("div[data-desktop-seq-no='6']");
                block7 = $("div[data-desktop-seq-no='7']");
                block8 = $("div[data-desktop-seq-no='8']");
                block9 = $("div[data-desktop-seq-no='9']");
            }

            if (nextLayout !== currentLayout) {
                // Reorder blocks based on their seq no
                block1.after(block2.detach());
                block2.after(block3.detach());
                block3.after(block4.detach());
                block4.after(block5.detach());
                block5.after(block6.detach());
                block6.after(block7.detach());
                block7.after(block8.detach());
                block8.after(block9.detach());
                currentLayout = nextLayout;
            }
        }

        // Adjust layout upon window resize
        window.onresize = function () {
            clearTimeout(callAdjustLayout);
            callAdjustLayout = setTimeout(adjustLayout, 100);
        }

        // DOM is ready
        $(function () {
            if (detectIE()) {
                alert('Please use the latest version of Chrome or Firefox for best browsing experience.');
            }

            adjustLayout();
        })
    </script>
</body>

</html>

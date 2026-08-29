@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic
            ? 'مغسلة الياسي | Alyasi Car Wash'
            : 'Alyasi Car Wash',

        'description' => $isArabic
            ? 'مغسلة الياسي — غسيل وتلميع سيارات باحترافية. مفتوحون يوميًا من 7 صباحًا حتى 10 مساءً في بدية.'
            : 'Alyasi Car Wash — professional car washing and detailing. Open daily from 7 AM to 10 PM in Bidiyah.',

        'brand_alt' => $isArabic ? 'مغسلة الياسي' : 'Alyasi Car Wash',

        'badge' => $isArabic
            ? 'محطة غسل سيارات · بدية'
            : 'Car Wash Station · Bidiyah',

        'title_line_1' => $isArabic ? 'مغسلة الياسي' : 'Alyasi',
        'title_line_2' => $isArabic ? 'نلمّع سيارتك بعناية فائقة' : 'Car Wash',

        'hero_sub' => $isArabic
            ? 'غسيل خارجي وداخلي، تلميع، وتعقيم — بأيدٍ محترفة وأسعار تناسب الجميع. نستقبلكم يوميًا في بدية.'
            : 'Exterior and interior wash, polishing, and sanitizing — by skilled hands at prices for everyone. Open daily in Bidiyah.',

        'get_directions' => $isArabic ? 'احصل على الاتجاهات' : 'Get Directions',
        'our_services' => $isArabic ? 'خدماتنا' : 'Our Services',

        'metric_rating_label' => $isArabic ? 'تقييم الزوار' : 'Visitor Rating',
        'metric_hours_label' => $isArabic ? 'ساعات العمل يوميًا' : 'Daily Hours',
        'metric_location_label' => $isArabic ? 'الموقع' : 'Location',
        'metric_location_value' => $isArabic ? 'بدية' : 'Bidiyah',

        'services_tag' => $isArabic ? 'خدماتنا' : 'Our Services',
        'services_title_1' => $isArabic ? 'كل ما تحتاجه سيارتك' : 'Everything your car needs',
        'services_title_2' => $isArabic ? 'في مكان واحد' : 'in one place',
        'services_body' => $isArabic
            ? 'خدمات غسيل وعناية شاملة بسيارتك، بجودة تليق بها.'
            : 'Complete wash and care services for your car, with quality it deserves.',

        'service_exterior' => $isArabic ? 'غسيل خارجي' : 'Exterior Wash',
        'service_interior' => $isArabic ? 'تنظيف داخلي' : 'Interior Cleaning',
        'service_polish' => $isArabic ? 'تلميع وشمع' : 'Polish & Wax',
        'service_engine' => $isArabic ? 'غسيل المحرك' : 'Engine Wash',
        'service_sanitize' => $isArabic ? 'تعطير وتعقيم' : 'Sanitizing & Fragrance',
        'service_tires' => $isArabic ? 'تلميع الإطارات' : 'Tire Shine',

        'visit_tag' => $isArabic ? 'زورونا' : 'Visit Us',
        'visit_title_1' => $isArabic ? 'نستقبلكم' : 'We welcome you',
        'visit_title_2' => $isArabic ? 'يوميًا' : 'daily',
        'visit_body' => $isArabic
            ? 'من الساعة 7:00 صباحًا حتى 10:00 مساءً — بدية، سلطنة عُمان.'
            : 'From 7:00 AM to 10:00 PM — Bidiyah, Sultanate of Oman.',
        'map_title' => $isArabic
            ? 'موقع مغسلة الياسي على الخريطة'
            : 'Alyasi Car Wash location on the map',

        'cta_tag' => $isArabic ? 'مغسلة الياسي' : 'Alyasi Car Wash',
        'cta_title_1' => $isArabic ? 'سيارتك تستاهل' : 'Your car deserves',
        'cta_title_2' => $isArabic ? 'أفضل عناية' : 'the best care',
        'cta_body' => $isArabic
            ? 'مفتوحون يوميًا من 7 صباحًا حتى 10 مساءً في بدية — بانتظاركم.'
            : 'Open daily from 7 AM to 10 PM in Bidiyah — we look forward to seeing you.',
        'visit_alyasi' => $isArabic ? 'زيارة موقع ALYASI' : 'Visit ALYASI Website',

        'footer_credit' => $isArabic
            ? 'جميع الحقوق محفوظة © مغسلة الياسي 2026'
            : '© 2026 Alyasi Car Wash. All rights reserved.',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $t['title'] }}</title>
    <meta name="description" content="{{ $t['description'] }}">
    <meta name="theme-color" content="#0B1F3A">
    <link rel="canonical" href="{{ route('car-wash') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/car-wash/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="{{ versioned_asset('css/shared/tokens.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/base.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/car-wash.css') }}">
<meta name="msvalidate.01" content="6AB900B487D367C05F065BE5B2B1A6D1" />
    <style>
        /* هوية مغسلة الياسي — تعديلات محلية (ألوان مطابقة لهوية ALYASI الكحلية الجديدة) */

        .cw-hero__logo {
            display: block;
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            border-radius: 16px;
            overflow: hidden;
        }

        .cw-hero__logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .metric__value--text {
            font-size: clamp(1.25rem, 1.8vw, 1.6rem);
            font-weight: 500;
        }

        .cw-services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 8px;
        }

        .cw-service-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            padding: 32px 20px;
            border-radius: 18px;
            border: 1px solid var(--panel-border);
            background: var(--panel-bg);
            text-align: center;
            transition: transform .3s var(--ease), border-color .3s var(--ease);
        }

        .cw-service-card:hover {
            transform: translateY(-4px);
            border-color: rgba(159, 176, 206, .4);
        }

        .cw-service-card__icon {
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(159, 176, 206, .1);
            color: var(--gold-light);
            font-size: 20px;
        }

        .cw-service-card__name {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--cream);
        }

        .cw-map-card {
            padding: 24px;
            border-radius: 24px;
            border: 1px solid var(--panel-border);
            background: var(--panel-bg);
        }

        .cw-map-card iframe {
            display: block;
            width: 100%;
            height: 360px;
            border: 0;
            border-radius: 16px;
        }

        .cw-footer {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 32px 60px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 12.5px;
            color: var(--slate-warm);
        }

        .cw-footer a {
            color: var(--slate-light);
            text-decoration: none;
        }

        .cw-footer a:hover {
            color: var(--gold-light);
        }

        @media (max-width: 640px) {
            .cw-services-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* مبدّل اللغة */
        .lang-switch {
            position: fixed;
            top: 20px;
            inset-inline-end: 20px;
            z-index: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 100px;
            background: rgba(11, 31, 58, 0.72);
            border: 1px solid rgba(159, 176, 206, 0.16);
            backdrop-filter: blur(20px);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
        }

        .lang-switch a {
            color: var(--slate-warm);
            text-decoration: none;
            transition: color 0.4s ease;
        }

        .lang-switch a.active {
            color: var(--gold-light);
        }

        .lang-switch span {
            color: var(--slate-warm);
            opacity: 0.5;
        }
    </style>
</head>

<body>

    <div class="lang-switch">
        <a href="{{ route('locale.switch', ['locale' => 'ar', 'redirect' => url()->current()]) }}" class="{{ $isArabic ? 'active' : '' }}">AR</a>
        <span>/</span>
        <a href="{{ route('locale.switch', ['locale' => 'en', 'redirect' => url()->current()]) }}" class="{{ ! $isArabic ? 'active' : '' }}">EN</a>
    </div>

    <main class="main">

        {{-- ══════════════ Hero ══════════════ --}}
        <section class="hero" id="hero">

            <div class="hero__grid"></div>

            <a href="{{ localized_route('home') }}" class="cw-hero__logo" aria-label="{{ $t['brand_alt'] }}">
                <img src="{{ asset('images/car-wash/logo.png') }}" alt="{{ $t['brand_alt'] }}">
            </a>

            <span class="pill">
                <span class="pill__dot"></span>
                {{ $t['badge'] }}
            </span>

            <h1 class="hero__title">
                {{ $t['title_line_1'] }}
                <br>
                <em>{{ $t['title_line_2'] }}</em>
            </h1>

            <p class="hero__desc">
                {{ $t['hero_sub'] }}
            </p>

            <div class="hero__ctas">
                <a
                    href="https://www.google.com/maps/dir/?api=1&destination=22.4498281,58.8103445"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn--primary"
                >
                    {{ $t['get_directions'] }}
                </a>

                <a href="#services" class="btn btn--secondary">
                    {{ $t['our_services'] }}
                </a>
            </div>

            <div class="hero__metrics">

                <div class="metric">
                    <div class="metric__value">
                        4.3<span>★</span>
                    </div>
                    <div class="metric__label">{{ $t['metric_rating_label'] }}</div>
                </div>

                <div class="metric">
                    <div class="metric__value metric__value--text" dir="ltr">7:00 – 10:00</div>
                    <div class="metric__label">{{ $t['metric_hours_label'] }}</div>
                </div>

                <div class="metric">
                    <div class="metric__value metric__value--text">{{ $t['metric_location_value'] }}</div>
                    <div class="metric__label">{{ $t['metric_location_label'] }}</div>
                </div>

            </div>

        </section>

        {{-- ══════════════ Services ══════════════ --}}
        <section class="section" id="services">
            <div class="section__inner">

                @php
                    $services = [
                        ['icon' => 'fa-solid fa-car-side', 'name' => $t['service_exterior']],
                        ['icon' => 'fa-solid fa-broom', 'name' => $t['service_interior']],
                        ['icon' => 'fa-solid fa-soap', 'name' => $t['service_polish']],
                        ['icon' => 'fa-solid fa-gears', 'name' => $t['service_engine']],
                        ['icon' => 'fa-solid fa-spray-can-sparkles', 'name' => $t['service_sanitize']],
                        ['icon' => 'fa-solid fa-circle-dot', 'name' => $t['service_tires']],
                    ];
                @endphp

                <div class="section-head">
                    <span class="eyebrow">{{ $t['services_tag'] }}</span>

                    <h2 class="section-title">
                        {{ $t['services_title_1'] }}
                        <em>{{ $t['services_title_2'] }}</em>
                    </h2>

                    <p class="section-body">
                        {{ $t['services_body'] }}
                    </p>
                </div>

                <div class="cw-services-grid">
                    @foreach ($services as $service)
                        <article class="cw-service-card">
                            <div class="cw-service-card__icon">
                                <i class="{{ $service['icon'] }}" aria-hidden="true"></i>
                            </div>

                            <div class="cw-service-card__name">
                                {{ $service['name'] }}
                            </div>
                        </article>
                    @endforeach
                </div>

            </div>
        </section>

        {{-- ══════════════ Hours & Map ══════════════ --}}
        <section class="section" id="visit">
            <div class="section__inner">

                <div class="section-head">
                    <span class="eyebrow">{{ $t['visit_tag'] }}</span>

                    <h2 class="section-title">
                        {{ $t['visit_title_1'] }} <em>{{ $t['visit_title_2'] }}</em>
                    </h2>

                    <p class="section-body">
                        {{ $t['visit_body'] }}
                    </p>
                </div>

                <div class="cw-map-card">
                    <iframe
                        src="https://www.google.com/maps?q=22.4498281,58.8103445&hl={{ $isArabic ? 'ar' : 'en' }}&z=16&output=embed"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="{{ $t['map_title'] }}"
                    ></iframe>

                    <div class="hero__ctas" style="margin-top: 28px; justify-content: center;">
                        <a
                            href="https://maps.app.goo.gl/tunEtY2GrQZiyzsn6"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn--primary"
                        >
                            {{ $t['get_directions'] }}
                        </a>
                    </div>
                </div>

            </div>
        </section>

        {{-- ══════════════ CTA ══════════════ --}}
        <section class="section section--tight" id="contact">
            <div class="section__inner">
                <div class="cta-band cta-band--card">
                    <span class="eyebrow">{{ $t['cta_tag'] }}</span>

                    <h2 class="cta-band__title">
                        {{ $t['cta_title_1'] }}
                        <em>{{ $t['cta_title_2'] }}</em>
                    </h2>

                    <p class="cta-band__desc">
                        {{ $t['cta_body'] }}
                    </p>

                    <div class="hero__ctas">
                        <a
                            href="https://www.google.com/maps/dir/?api=1&destination=22.4498281,58.8103445"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn--primary"
                        >
                            {{ $t['get_directions'] }}
                        </a>

                        <a href="{{ localized_route('home') }}" class="btn btn--secondary">
                            {{ $t['visit_alyasi'] }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <footer class="cw-footer">
            <span>{{ $t['footer_credit'] }}</span>

            <div>
                <a href="{{ localized_route('home') }}">ALYASI Platform</a>
            </div>
        </footer>

    </main>

</body>

</html>

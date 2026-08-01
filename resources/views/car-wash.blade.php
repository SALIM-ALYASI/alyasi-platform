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
    <meta name="theme-color" content="#180B0F">
    <link rel="icon" type="image/png" href="{{ asset('images/car-wash/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">

    {{-- Luminary / ALYASI Style --}}
    <link rel="stylesheet" href="{{ asset('luminary/templatemo-621-luminary-style.css') }}">

    <style>
        /* تصحيح شبكة الخدمات لهذه الصفحة فقط — قواعد الصفحة الرئيسية
           مضبوطة على عدد بطاقات التقنية (17) وتكسر شكل شبكة الـ 6 بطاقات هنا. */
        #services .integrations-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        #services .integration-item:nth-last-child(4) {
            grid-column-start: auto;
        }

        @media (max-width: 768px) {
            #services .integrations-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* توحيد حجم النصوص غير الرقمية في شريط المقاييس (الساعات والموقع)
           بدل نمط "رقم كبير + وحدة صغيرة" المصمم لأرقام قصيرة فقط. */
        .metric-value.metric-value-text {
            font-size: clamp(1.25rem, 1.8vw, 1.6rem);
            font-weight: 400;
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
            background: rgba(24, 11, 15, 0.72);
            border: 1px solid rgba(208, 170, 106, 0.16);
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
        <a href="{{ route('locale.switch', 'ar') }}" class="{{ $isArabic ? 'active' : '' }}">AR</a>
        <span>/</span>
        <a href="{{ route('locale.switch', 'en') }}" class="{{ ! $isArabic ? 'active' : '' }}">EN</a>
    </div>

    <main class="main">

        {{-- ══════════════ Hero ══════════════ --}}
        <section class="hero" id="hero">

            <div class="hero-grid"></div>

            <a href="{{ route('home') }}" class="hero-logo" aria-label="{{ $t['brand_alt'] }}">
                <img src="{{ asset('images/car-wash/logo.png') }}" alt="{{ $t['brand_alt'] }}">
            </a>

            <div class="hero-badge">
                <div class="hero-badge-dot"></div>
                <span>{{ $t['badge'] }}</span>
            </div>

            <h1>
                {{ $t['title_line_1'] }}
                <br>
                <em>{{ $t['title_line_2'] }}</em>
            </h1>

            <p class="hero-sub">
                {{ $t['hero_sub'] }}
            </p>

            <div class="hero-ctas">
                <a
                    href="https://www.google.com/maps/dir/?api=1&destination=22.4498281,58.8103445"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-primary"
                >
                    {{ $t['get_directions'] }}
                </a>

                <a href="#services" class="btn-secondary">
                    {{ $t['our_services'] }}
                </a>
            </div>

            <div class="hero-metrics">

                <div class="metric">
                    <div class="metric-value">
                        4.3<span>★</span>
                    </div>
                    <div class="metric-label">{{ $t['metric_rating_label'] }}</div>
                </div>

                <div class="metric">
                    <div class="metric-value metric-value-text" dir="ltr">7:00 – 10:00</div>
                    <div class="metric-label">{{ $t['metric_hours_label'] }}</div>
                </div>

                <div class="metric">
                    <div class="metric-value metric-value-text">{{ $t['metric_location_value'] }}</div>
                    <div class="metric-label">{{ $t['metric_location_label'] }}</div>
                </div>

            </div>

        </section>

        {{-- ══════════════ Services ══════════════ --}}
        <section class="section" id="services">

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

            <div class="section-heading section-center">
                <p class="section-tag">{{ $t['services_tag'] }}</p>

                <h2 class="section-title">
                    {{ $t['services_title_1'] }}
                    <strong>{{ $t['services_title_2'] }}</strong>
                </h2>

                <p class="section-body">
                    {{ $t['services_body'] }}
                </p>
            </div>

            <div class="integrations-grid">
                @foreach ($services as $service)
                    <article class="integration-item">
                        <div class="integration-icon">
                            <i class="{{ $service['icon'] }}" aria-hidden="true"></i>
                        </div>

                        <div class="integration-name">
                            {{ $service['name'] }}
                        </div>
                    </article>
                @endforeach
            </div>

        </section>

        {{-- ══════════════ Hours & Map ══════════════ --}}
        <section class="section" id="visit">

            <div class="section-heading section-center">
                <p class="section-tag">{{ $t['visit_tag'] }}</p>

                <h2 class="section-title">
                    {{ $t['visit_title_1'] }} <strong>{{ $t['visit_title_2'] }}</strong>
                </h2>

                <p class="section-body">
                    {{ $t['visit_body'] }}
                </p>
            </div>

            <div class="cta-card" style="padding-top: 0; overflow: hidden;">
                <iframe
                    src="https://www.google.com/maps?q=22.4498281,58.8103445&hl={{ $isArabic ? 'ar' : 'en' }}&z=16&output=embed"
                    width="100%"
                    height="360"
                    style="border:0; border-radius: 24px; display: block;"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="{{ $t['map_title'] }}"
                ></iframe>

                <div class="hero-ctas" style="margin-top: 32px; justify-content: center;">
                    <a
                        href="https://maps.app.goo.gl/tunEtY2GrQZiyzsn6"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-primary"
                    >
                        {{ $t['get_directions'] }}
                    </a>
                </div>
            </div>

        </section>

        {{-- ══════════════ CTA ══════════════ --}}
        <section class="section cta-section" id="contact">

            <div class="cta-card">
                <p class="section-tag">{{ $t['cta_tag'] }}</p>

                <h2 class="section-title">
                    {{ $t['cta_title_1'] }}
                    <strong>{{ $t['cta_title_2'] }}</strong>
                </h2>

                <p class="section-body">
                    {{ $t['cta_body'] }}
                </p>

                <div class="hero-ctas">
                    <a
                        href="https://www.google.com/maps/dir/?api=1&destination=22.4498281,58.8103445"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-primary"
                    >
                        {{ $t['get_directions'] }}
                    </a>

                    <a href="{{ route('home') }}" class="btn-secondary">
                        {{ $t['visit_alyasi'] }}
                    </a>
                </div>
            </div>

        </section>

        <footer class="footer-bar" style="max-width: 1120px; margin: 0 auto; padding: 0 40px 60px;">
            <span class="footer-credit">{{ $t['footer_credit'] }}</span>

            <div class="footer-links">
                <a href="{{ route('home') }}">ALYASI Platform</a>
            </div>
        </footer>

    </main>

</body>

</html>

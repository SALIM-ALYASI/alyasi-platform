@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $arrow = $isEn ? '→' : '←';
@endphp

@section('title', $t('سالم محمد الحجري — مؤسس ALYASI', 'Salem Mohammed Al Hajri — Founder of ALYASI'))
@section('meta_description', str_replace(':count', (string) $appsCount, $t(
    'سالم محمد الحجري، مؤسس منصة ALYASI ومطوّرها: خبرة تقارب 12 عامًا في البرمجة والتصميم، و:count تطبيقًا منشورًا في App Store، ومشاريع مواقع وأنظمة وذكاء اصطناعي.',
    'Salem Mohammed Al Hajri, founder and developer of ALYASI: about 12 years in programming and design, :count apps published on the App Store, and web, systems and AI projects.'
)))
@section('canonical', localized_route('about.founder'))
@section('hreflang_ar', route('about.founder'))
@section('hreflang_en', route('about.founder.en'))
@section('og_image', asset('images/events/cyberx-2026/salem-al-hajri.png'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/about.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/works-index.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/founder.css') }}">
@endpush

@section('content')

    @if ($preview)
        <div class="founder-preview" role="note">{{ $t('مسودة للمراجعة — هذه الصفحة غير منشورة بعد.', 'Draft for review — this page is not published yet.') }}</div>
    @endif

    {{-- المقدمة: الاسم والدور وسطر واحد يلخّص --}}
    <section class="container founder-hero">
        <img class="founder-hero__photo" src="{{ versioned_asset('images/events/cyberx-2026/salem-al-hajri.png') }}" alt="{{ $t('سالم محمد الحجري', 'Salem Mohammed Al Hajri') }}" width="220" height="220">
        <div class="founder-hero__text">
            <span class="founder-hero__badge">{{ $t('عن المؤسس', 'About the founder') }}</span>
            <h1>{{ $t('سالم محمد الحجري', 'Salem Mohammed Al Hajri') }}</h1>
            <p class="founder-hero__role">{{ $t('مؤسس منصة ALYASI ومطوّرها', 'Founder and developer of ALYASI') }}</p>
            <p class="founder-hero__lead">{{ $t(
                'مطوّر ومصمم عُماني، أعمل منذ قرابة 12 عامًا في البرمجة والتصميم والتقنية، تعلّمت أغلبها ذاتيًا. أطوّر المواقع والأنظمة وتطبيقات أجهزة آبل، وأوظّف الذكاء الاصطناعي في بناء أدوات عملية.',
                'An Omani developer and designer with close to 12 years in programming, design and technology, most of it self-taught. I build websites, systems and apps for Apple devices, and use AI to create practical tools.'
            ) }}</p>
        </div>
    </section>

    {{-- أرقام موثّقة: التطبيقات من App Store مباشرة، والمشاريع من صفحة الأعمال --}}
    <section class="container about-stats">
        <div class="grid-3">
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">12</div>
                <div class="about-stats__label">{{ $t('عامًا تقريبًا في البرمجة والتصميم', 'years, roughly, in programming and design') }}</div>
            </div>
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">{{ $appsCount }}</div>
                <div class="about-stats__label">{{ $t('تطبيقًا منشورًا في App Store منذ 2017', 'apps published on the App Store since 2017') }}</div>
            </div>
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">{{ $projects->count() }}</div>
                <div class="about-stats__label">{{ $t('مشاريع معروضة في صفحة الأعمال', 'projects featured in the portfolio') }}</div>
            </div>
        </div>
    </section>

    {{-- مجالات الخبرة --}}
    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ $t('الخبرة', 'Experience') }}</div>
            <h2 class="section-head__title">{{ $t('ما أعمل عليه', 'What I work on') }}</h2>
        </div>
        <div class="founder-skills">
            @foreach ([
                ['fa-solid fa-code', $t('المواقع والأنظمة', 'Websites and systems'), $t('مواقع تعريفية ومنصات محتوى وأنظمة إدارة ولوحات تحكم، مثل نظام «الحرفيون» للمبيعات والمخزون ومنصة ALYASI نفسها.', 'Websites, content platforms, management systems and dashboards, such as the «Harfiyoon» sales and inventory system and the ALYASI platform itself.')],
                ['fa-brands fa-apple', $t('تطبيقات أجهزة آبل', 'Apps for Apple devices'), $t('تطبيقات مخصصة لأجهزة آبل منشورة في App Store منذ 2017، في السياحة والتوصيل والكتب والأدوات والألعاب، ومنها تطبيق «Bidiyah».', 'Apps for Apple devices published on the App Store since 2017 — travel, delivery, books, utilities and games — including «Bidiyah».')],
                ['fa-solid fa-wand-magic-sparkles', $t('الذكاء الاصطناعي', 'Artificial intelligence'), $t('أدوات تجمع الأخبار وتحللها وتتحقق من معلوماتها قبل النشر، وتجارب في توليد الصوت، وتوظيف الذكاء الاصطناعي في تطوير المشاريع.', 'Tools that gather, analyse and fact-check news before publishing, voice generation experiments, and AI applied to building projects.')],
                ['fa-solid fa-pen-ruler', $t('التصميم الرقمي', 'Digital design'), $t('هويات بصرية وتصاميم واجهات ومحتوى مرئي، جزء من أغلب المشاريع من بدايتها.', 'Visual identities, interface design and visual content, part of most projects from the start.')],
            ] as [$icon, $title, $body])
                <div class="founder-skill" data-reveal>
                    <span class="founder-skill__icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- منصة ALYASI: ما بُني من الصفر --}}
    <section class="about-mission">
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">ALYASI</div>
                <h2 class="section-head__title">{{ $t('منصة بنيتها من الصفر', 'A platform built from scratch') }}</h2>
                <p class="section-head__desc">{{ $t(
                    'بدأت ALYASI عام 2019، وطوّرتها بنفسي من الصفر: قسم للأخبار التقنية يعمل بأدوات ذكاء اصطناعي لجمع الأخبار وتحليلها والتحقق منها، ومقالات، و«تاريخ التقنية»، وتغطيات للمؤتمرات التقنية في عُمان، منها تغطية ميدانية لمؤتمر سايبر إكس عُمان 2026 ومقابلات مسجّلة مع متحدثيه، إلى جانب خدمات تطوير المواقع والأنظمة والتصميم.',
                    'ALYASI started in 2019 and I built it myself from scratch: a tech news section powered by AI tools that gather, analyse and fact-check stories, articles, «Tech History», and coverage of technology conferences in Oman — including on-site coverage of CyberX Oman 2026 and recorded interviews with its speakers — alongside web, systems and design services.'
                ) }}</p>
            </div>
        </div>
    </section>

    {{-- أبرز المشاريع (من صفحة الأعمال) --}}
    @if ($projects->isNotEmpty())
        <section class="container about-section">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">{{ $t('المشاريع', 'Projects') }}</div>
                <h2 class="section-head__title">{{ $t('من أعمالي', 'Selected work') }}</h2>
            </div>
            <div class="founder-projects">
                @foreach ($projects as $work)
                    <a class="founder-project" href="{{ localized_route('works.show', ['work' => $work]) }}" data-reveal>
                        <strong>{{ $work->localized('title') }}</strong>
                        <span>{{ $work->type_label }}@if ($work->status_label) · {{ $work->status_label }}@endif</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- تطبيقات App Store (منشورة فعلًا، وتتحدّث تلقائيًا) --}}
    @if ($apps)
        <section class="container about-section">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">App Store</div>
                <h2 class="section-head__title">{{ $t('تطبيقات منشورة', 'Published apps') }}</h2>
                <p class="section-head__desc">{{ str_replace(':count', (string) $appsCount, $t(':count تطبيقًا منشورًا في متجر آبل من حساب المطوّر «Salim Alhajri»، منها:', ':count apps published on the App Store under the developer account «Salim Alhajri», including:')) }}</p>
            </div>
            <div class="works-apps__grid">
                @foreach (array_slice($apps, 0, 4) as $app)
                    <a class="works-app" href="{{ $app['url'] }}" target="_blank" rel="noopener">
                        @if ($app['icon'])
                            <img class="works-app__icon" src="{{ versioned_asset($app['icon']) }}" alt="" width="64" height="64" loading="lazy">
                        @endif
                        <span class="works-app__body">
                            <strong dir="auto">{{ $app['name'] }}</strong>
                            <span class="works-app__meta">{{ $app['genre'] }} · {{ substr((string) $app['released'], 0, 4) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="works-apps__more">
                <a class="btn btn--outline" href="{{ localized_route('works.index') }}#apps">{{ $t('كل التطبيقات في صفحة الأعمال', 'All apps in the portfolio') }} {{ $arrow }}</a>
            </div>
        </section>
    @endif

    {{-- الرؤية --}}
    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ $t('الرؤية', 'Vision') }}</div>
            <h2 class="section-head__title">{{ $t('تقنية عملية بلغة الناس', 'Practical technology, in people’s language') }}</h2>
            <p class="section-head__desc">{{ $t(
                'أؤمن بأن التقنية تكون مفيدة حين تحل مشكلة حقيقية لصاحبها. لذلك أركّز على حلول عملية للأفراد والمشاريع في عُمان والمنطقة، وعلى محتوى تقني عربي واضح ودقيق.',
                'I believe technology is useful when it solves a real problem for the person using it. That is why I focus on practical solutions for people and businesses in Oman and the region, and on clear, accurate Arabic tech content.'
            ) }}</p>
        </div>
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ $t('عندك مشروع؟', 'Have a project in mind?') }}</h2>
            <p class="cta-band__desc">{{ $t('تواصل معي لنناقش فكرتك.', 'Get in touch to talk it through.') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ $t('تواصل معنا', 'Contact us') }}</a>
                <a href="https://wa.me/96898881054" class="btn btn--ghost-light" target="_blank" rel="noopener">{{ $t('واتساب', 'WhatsApp') }}</a>
            </div>
        </div>
    </section>

@endsection

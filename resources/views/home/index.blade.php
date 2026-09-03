@extends('layouts.app')

@section('title', __('home.brand').' — '.__('home.tagline'))
@section('meta_description', __('home.hero_description'))

@section('canonical', localized_route('home'))
@section('og_url', localized_route('home'))
@section('hreflang_ar', localized_route('home', [], 'ar'))
@section('hreflang_en', localized_route('home', [], 'en'))
@section('og_image', asset('images/home/og-image.png'))
@section('og_image_width', '1200')
@section('og_image_height', '630')

{{--
    Organization وWebSite أصبحت بالـ layout (layouts/app.blade.php)
    وتُعرض بكل صفحة — تركت هنا فقط FAQPage الخاص بأسئلة الرئيسية.
--}}
@section('schema')
@if ($faqs->isNotEmpty())
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq->localizedQuestion(),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq->localizedAnswer(),
        ],
    ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/home.css') }}">
@endpush

@section('content')
@php
    $spotlightService = $services->first();
    $gridServices = $services->skip(1)->values();
    $techChunks = $technologies->count() ? $technologies->split(2) : collect();
    $techRow1 = $techChunks->get(0, collect());
    $techRow2 = $techChunks->get(1, collect());
@endphp

    {{-- HERO --}}
    <section class="home-hero">
        <div class="home-hero__bg">
            <img src="{{ asset('images/home/hero.webp') }}" alt="ALYASI" loading="eager" width="1672" height="188">
        </div>
        <div class="home-hero__overlay"></div>

        <div class="container home-hero__content">
            <div class="home-hero__inner">
                <span class="pill home-hero__badge">
                    <span class="pill__dot"></span>
                    {{ __('home.hero_badge') }}
                </span>

                <h1 class="home-hero__title">
                    {{ __('home.hero_title_1') }}
                    <span>{{ __('home.hero_title_2') }}</span>
                </h1>

                <p class="home-hero__desc">{{ __('home.hero_description') }}</p>
                <p class="home-hero__audience">{{ __('home.hero_audience') }}</p>

                <div class="home-hero__ctas">
                    <a href="{{ localized_route('contact') }}" class="btn btn--light">
                        {{ __('home.hero_cta_primary') }}
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                    </a>
                    <a href="{{ localized_route('works.index') }}" class="btn btn--ghost-light">{{ __('home.hero_cta_secondary') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== منطقة الخدمات ===================== --}}

    {{-- SPOTLIGHT --}}
    @if ($spotlightService)
        <section class="container home-spotlight" data-reveal>
            <div class="home-spotlight__media">
                <img src="{{ media_url($spotlightService->image) }}" alt="{{ $spotlightService->localizedTitle() }}" loading="lazy">
                <span class="home-spotlight__number">01</span>
            </div>
            <div class="home-spotlight__body">
                <div class="section-head__eyebrow home-spotlight__eyebrow">
                    <span class="home-spotlight__eyebrow-line"></span>
                    {{ __('home.featured_service') }}
                </div>
                <h2 class="section-head__title">{{ $spotlightService->localizedTitle() }}</h2>
                <p class="home-spotlight__desc">{{ $spotlightService->localizedDescription() }}</p>
                <div class="home-spotlight__actions">
                    <a href="{{ $spotlightService->slug() ? localized_route('services.show', ['slug' => $spotlightService->slug()]) : localized_route('services.index') }}" class="btn btn--primary">{{ __('home.view_service_details') }}</a>
                    <a href="{{ localized_route('contact') }}" class="btn btn--outline">{{ __('home.request_service') }}</a>
                </div>
            </div>
        </section>
    @endif

    {{-- PILLARS --}}
    <section class="home-pillars">
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">{{ __('home.platform_badge') }}</div>
                <h2 class="section-head__title">{{ __('home.platform_section_title') }} <em>{{ __('home.platform_section_title_highlight') }}</em></h2>
                <p class="section-head__desc">{{ __('home.platform_section_description') }}</p>
            </div>

            <div class="grid-3">
                <div class="home-pillar" data-reveal>
                    <div class="home-pillar__icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>
                    </div>
                    <h3>{{ __('home.platform_services_title') }}</h3>
                    <p>{{ __('home.platform_services_description') }}</p>
                </div>
                <div class="home-pillar" data-reveal>
                    <div class="home-pillar__icon home-pillar__icon--alt">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h13a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H4Z"></path><path d="M4 4v16"></path><path d="M9 9h7M9 13h7M9 17h4"></path></svg>
                    </div>
                    <h3>{{ __('home.platform_news_title') }}</h3>
                    <p>{{ __('home.platform_news_description') }}</p>
                </div>
                <div class="home-pillar" data-reveal>
                    <div class="home-pillar__icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"></circle><path d="M2.5 20c0-3.5 2.9-6 6.5-6s6.5 2.5 6.5 6"></path><circle cx="18" cy="9" r="2.4"></circle><path d="M15.8 14.2c2.6.4 4.7 2.3 4.7 5"></path></svg>
                    </div>
                    <h3>{{ __('home.platform_community_title') }}</h3>
                    <p>{{ __('home.platform_community_description') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- SERVICES GRID --}}
    @if ($gridServices->isNotEmpty())
        <section class="container home-section">
            <div class="home-section__head">
                <div>
                    <div class="section-head__eyebrow">{{ __('home.services_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.services_title') }}</h2>
                </div>
                <a href="{{ localized_route('services.index') }}" class="btn btn--outline">{{ __('home.all_services') }}</a>
            </div>

            <div class="grid-3">
                @foreach ($gridServices as $service)
                    <div class="home-service-card" data-reveal>
                        <img src="{{ media_url($service->image) }}" alt="{{ $service->localizedTitle() }}" loading="lazy">
                        <div class="home-service-card__scrim"></div>
                        <div class="home-service-card__text">
                            <span>{{ sprintf('%02d', $loop->iteration) }}</span>
                            <strong>{{ $service->localizedTitle() }}</strong>
                            <div class="home-service-card__actions">
                                <a href="{{ $service->slug() ? localized_route('services.show', ['slug' => $service->slug()]) : localized_route('services.index') }}" class="home-service-card__link">{{ __('home.view_service_details') }} ←</a>
                                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('home.request_service') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- TECH MARQUEE (تُعرض هنا ضمن منطقة الخدمات — ما فيها موقع صريح بترتيب المستخدم) --}}
    @if ($technologies->isNotEmpty())
        <section class="home-tech">
            <div class="container">
                <div class="section-head" data-reveal>
                    <div class="section-head__eyebrow">{{ __('home.technology_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.technology_title') }} <em>{{ __('home.technology_title_highlight') }}</em></h2>
                </div>
            </div>

            @if ($techRow1->isNotEmpty())
                <div class="home-tech__row home-tech__row--left">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($techRow1 as $tech)
                            <span class="home-tech__pill">{{ $tech->name }}</span>
                        @endforeach
                    @endfor
                </div>
            @endif

            @if ($techRow2->isNotEmpty())
                <div class="home-tech__row home-tech__row--right">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach ($techRow2 as $tech)
                            <span class="home-tech__pill home-tech__pill--accent">{{ $tech->name }}</span>
                        @endforeach
                    @endfor
                </div>
            @endif
        </section>
    @endif

    {{-- CTA مصغّر ١ --}}
    <section class="container">
        <div class="cta-band cta-band--mini" data-reveal>
            <p class="cta-band__mini-text">{{ __('home.cta_mini_text') }}</p>
            <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('home.cta_mini_button') }}</a>
        </div>
    </section>

    {{-- WORKS --}}
    @if ($works->isNotEmpty())
        <section class="container home-section">
            <div class="home-section__head">
                <div>
                    <div class="section-head__eyebrow">{{ __('home.portfolio_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.portfolio_title') }} <em>{{ __('home.portfolio_title_highlight') }}</em></h2>
                </div>
                <a href="{{ localized_route('works.index') }}" class="btn btn--outline">{{ __('home.all_projects') }}</a>
            </div>

            <div class="grid-3">
                @foreach ($works as $work)
                    <a href="{{ localized_route('works.show', ['work' => $work]) }}" class="card card--hover home-work-card" data-reveal>
                        <div class="home-work-card__media">
                            <img src="{{ media_url($work->cover_image) }}" alt="{{ $work->title }}" loading="lazy">
                        </div>
                        <div class="home-work-card__body">
                            <div class="home-work-card__tag">{{ $work->service?->localizedTitle() ?? $work->type_label }}</div>
                            <div class="home-work-card__title">{{ $work->title }}</div>
                            @if ($work->outcome)
                                <div class="home-work-card__outcome">✓ {{ $work->outcome }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA مصغّر ٢ --}}
    <section class="container">
        <div class="cta-band cta-band--mini" data-reveal>
            <p class="cta-band__mini-text">{{ __('home.cta_mini_text') }}</p>
            <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('home.cta_mini_button') }}</a>
        </div>
    </section>

    {{-- WHY ALYASI --}}
    <section class="home-why">
        <div class="container">
            <div class="home-why__head" data-reveal>
                <div class="home-why__eyebrow">{{ __('home.about_badge') }}</div>
                <h2 class="home-why__title">{{ __('home.about_title') }} <em>{{ __('home.about_title_highlight') }}</em></h2>
            </div>

            <div class="grid-3">
                <div class="home-why__item" data-reveal>
                    <div class="home-why__num">01</div>
                    <h3>{{ __('home.about_feature_1_title') }}</h3>
                    <p>{{ __('home.about_feature_1_description') }}</p>
                </div>
                <div class="home-why__item" data-reveal>
                    <div class="home-why__num">02</div>
                    <h3>{{ __('home.about_feature_2_title') }}</h3>
                    <p>{{ __('home.about_feature_2_description') }}</p>
                </div>
                <div class="home-why__item" data-reveal>
                    <div class="home-why__num">03</div>
                    <h3>{{ __('home.about_feature_3_title') }}</h3>
                    <p>{{ __('home.about_feature_3_description') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- LATEST UPDATES (أخبار + مقالات + مجتمع مدمجة) --}}
    <section id="updates" class="container home-section">
        <div class="home-section__head">
            <div>
                <div class="section-head__eyebrow">{{ __('home.updates_badge') }}</div>
                <h2 class="section-head__title">{{ __('home.updates_title') }}</h2>
            </div>
        </div>

        <div class="home-updates-grid">

            {{-- News column --}}
            <div class="home-updates-col" id="news">
                <div class="home-updates-col__head">
                    <h3>{{ __('home.news_badge') }}</h3>
                    <a href="{{ localized_route('news.index') }}">{{ __('home.all_news') }} ←</a>
                </div>

                @if ($latestNews->isNotEmpty())
                    @foreach ($latestNews as $article)
                        <a href="{{ $article->slug() ? localized_route('news.show', ['slug' => $article->slug()]) : localized_route('news.index') }}" class="home-updates-card">
                            <div class="home-updates-card__media">
                                <img src="{{ media_url($article->image) }}" alt="{{ $article->title }}" loading="lazy">
                            </div>
                            <div class="home-updates-card__body">
                                <div class="home-updates-card__date">{{ optional($article->published_at)->translatedFormat('d.m.Y') }}</div>
                                <h4>{{ $article->title }}</h4>
                            </div>
                        </a>
                    @endforeach
                @else
                    <p class="home-updates-empty">{{ __('home.news_empty') }}</p>
                @endif
            </div>

            {{-- Articles column --}}
            @if ($showArticles)
                <div class="home-updates-col" id="articles">
                    <div class="home-updates-col__head">
                        <h3>{{ __('home.articles_badge') }}</h3>
                        <a href="{{ article_route('index') }}">{{ __('home.all_articles') }} ←</a>
                    </div>

                    @if ($latestArticles->isNotEmpty())
                        @foreach ($latestArticles as $article)
                            <a href="{{ article_route('show', [$article->slug()]) }}" class="home-updates-card">
                                <div class="home-updates-card__media">
                                    <img src="{{ media_url($article->featured_image_ar) }}" alt="{{ $article->title }}" loading="lazy">
                                </div>
                                <div class="home-updates-card__body">
                                    <div class="home-updates-card__date">{{ optional($article->published_at)->translatedFormat('d.m.Y') }}</div>
                                    <h4>{{ $article->title }}</h4>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <p class="home-updates-empty">{{ __('home.articles_empty') }}</p>
                    @endif
                </div>
            @endif

            {{-- Community column --}}
            @if ($showCommunityEvents)
                <div class="home-updates-col" id="community">
                    <div class="home-updates-col__head">
                        <h3>{{ __('home.community_badge') }}</h3>
                        <a href="{{ localized_route('community.index') }}">{{ __('community.show_all') }} ←</a>
                    </div>

                    @if ($communityHighlights->isNotEmpty())
                        @foreach ($communityHighlights as $post)
                            <a href="{{ route('community.show', $post) }}" class="home-updates-card">
                                <div class="home-updates-card__media">
                                    <img src="{{ media_url($post->image) }}" alt="{{ $post->title }}" loading="lazy">
                                </div>
                                <div class="home-updates-card__body">
                                    <div class="home-updates-card__date">
                                        {{ optional($post->event_start_at ?? $post->published_at)->translatedFormat('d.m.Y') }}
                                    </div>
                                    <h4>{{ $post->title }}</h4>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <p class="home-updates-empty">{{ __('community.empty_title') }}</p>
                    @endif
                </div>
            @endif

        </div>
    </section>

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section id="faq" class="home-faq">
            <div class="container home-faq__container">
                <div class="section-head" data-reveal>
                    <div class="section-head__eyebrow">{{ __('home.faq_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.faq_title') }} <em>{{ __('home.faq_title_highlight') }}</em></h2>
                </div>

                <div class="faq-list">
                    @foreach ($faqs as $faq)
                        <div class="faq-item" data-reveal>
                            <button type="button" class="faq-item__question" aria-expanded="false">
                                {{ $faq->localizedQuestion() }}
                                <svg class="faq-item__chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div class="faq-item__answer-wrap">
                                <div class="faq-item__answer-inner">
                                    <p class="faq-item__answer">{{ $faq->localizedAnswer() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA ختامي --}}
    <section id="contact" class="container">
        <div class="cta-band" data-reveal>
            <div class="cta-band__eyebrow">{{ __('home.cta_badge') }}</div>
            <h2 class="cta-band__title">{{ __('home.cta_title') }} <em>{{ __('home.cta_title_highlight') }}</em></h2>
            <p class="cta-band__desc">{{ __('home.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('services.index') }}" class="btn btn--light">{{ __('home.cta_services') }}</a>
                <a href="{{ localized_route('contact') }}" class="btn btn--ghost-light">{{ __('home.cta_contact') }}</a>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="{{ versioned_asset('js/pages/home.js') }}" defer></script>
    @endpush
@endsection

@extends('layouts.app')

@section('title', __('home.brand').' — '.__('home.tagline'))
@section('meta_description', __('home.meta_description'))

@section('canonical', localized_route('home'))
@section('og_url', localized_route('home'))
@section('hreflang_ar', localized_route('home', [], 'ar'))
@section('hreflang_en', localized_route('home', [], 'en'))
@section('og_image', asset('images/home/og-home-'.app()->getLocale().'.jpg'))
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
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-index.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/events-index.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/certificates.css') }}">
@endpush

@section('content')
@php
    $isEn = app()->getLocale() === 'en';
    $arrow = $isEn ? '→' : '←';
    $phaseClass = ['upcoming' => 'upcoming', 'live' => 'ongoing', 'remote' => 'upcoming', 'concluded' => 'ended'];
    $techChunks = $technologies->count() ? $technologies->split(2) : collect();
    $techRow1 = $techChunks->get(0, collect());
    $techRow2 = $techChunks->get(1, collect());
@endphp

    {{-- HERO: منصة إعلام تقني عُمانية + خدمات رقمية --}}
    <section class="home-hero">
        <div class="home-hero__bg">
            {{-- مسقط ليلًا: المدينة بالجهة المقابلة للنص (يسار بالعربي، يمين بالإنجليزي) --}}
            @php $heroBg = 'images/home/hero-muscat-'.($isEn ? 'en' : 'ar'); @endphp
            <img src="{{ versioned_asset($heroBg.'.webp') }}" srcset="{{ versioned_asset($heroBg.'-960.webp') }} 960w, {{ versioned_asset($heroBg.'.webp') }} 1731w" sizes="100vw" alt="" loading="eager" fetchpriority="high" decoding="async" width="1731" height="909">
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

                <div class="home-hero__ctas">
                    <a href="{{ localized_route('events.index') }}" class="btn btn--light">
                        {{ __('home.hero_cta_coverage') }}
                        <svg class="home-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                    </a>
                    <a href="{{ localized_route('services.index') }}" class="btn btn--ghost-light">{{ __('home.hero_cta_services') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ١) تغطية مميزة: نفس منطق /events (مثبّتة من لوحة التحكم، وإلا أحدث محتوى حصري) --}}
    @include('events._featured', ['featured' => $featuredCoverage, 'sectionClass' => 'home-featured', 'headingId' => 'home-featured-title'])

    {{-- ٢) أحدث الأخبار التقنية + نبض عُمان --}}
    <section id="news" class="container home-section home-news">
        <div class="home-section__head">
            <div>
                <div class="section-head__eyebrow">{{ __('home.news_section_badge') }}</div>
                <h2 class="section-head__title">{{ __('home.news_section_title') }}</h2>
            </div>
        </div>

        <div class="home-news__grid">
            <div class="home-updates-col">
                <div class="home-updates-col__head">
                    <h3>{{ __('home.latest_tech_news') }}</h3>
                    <a href="{{ localized_route('news.index') }}">{{ __('home.all_news') }} {{ $arrow }}</a>
                </div>

                @forelse ($latestNews as $article)
                    <a href="{{ $article->url() ?: localized_route('news.index') }}" class="home-updates-card">
                        <div class="home-updates-card__media">
                            <img src="{{ media_url($article->image) }}" alt="" loading="lazy" decoding="async" width="76" height="58">
                        </div>
                        <div class="home-updates-card__body">
                            <div class="home-updates-card__date">{{ optional($article->published_at)->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}</div>
                            <h4>{{ $article->title }}</h4>
                        </div>
                    </a>
                @empty
                    <p class="home-updates-empty">{{ __('home.news_empty') }}</p>
                @endforelse
            </div>

            @if ($omanPulse->isNotEmpty())
                <div class="home-updates-col home-pulse">
                    <div class="home-updates-col__head">
                        <h3>🇴🇲 {{ __('home.oman_pulse') }}</h3>
                        <a href="{{ localized_route('news.oman') }}">{{ __('home.oman_pulse_all') }} {{ $arrow }}</a>
                    </div>

                    {{-- البيانات الرسمية بنصها العربي حتى بالصفحة الإنجليزية --}}
                    @foreach ($omanPulse as $item)
                        <a href="{{ $item->url() }}" class="home-updates-card">
                            @if ($item->image)
                                <div class="home-updates-card__media">
                                    <img src="{{ media_url($item->image) }}" alt="" loading="lazy" decoding="async" width="76" height="58">
                                </div>
                            @endif
                            <div class="home-updates-card__body">
                                <div class="home-updates-card__date">
                                    {{ __('home.oman_pulse_official') }}@if ($when = $item->source_published_at ?? $item->created_at) · {{ $when->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}@endif
                                </div>
                                <h4 lang="ar" dir="rtl">{{ $item->title }}</h4>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ٣) المؤتمرات والمقابلات: الشراكات الإعلامية أول --}}
    @if ($conferences->isNotEmpty())
        <section class="container home-section home-conferences">
            <div class="home-section__head">
                <div>
                    <div class="section-head__eyebrow">{{ __('home.conferences_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.conferences_title') }}</h2>
                </div>
                <a href="{{ localized_route('events.index') }}" class="btn btn--outline">{{ __('home.conferences_all') }}</a>
            </div>

            <div class="home-scroller">
                @foreach ($conferences as $row)
                    @php
                        $event = $row->event;
                        $edition = $row->edition;
                        $phase = $edition->badge_phase;
                        $url = $edition->publicUrl();
                        $image = $event->image ?: $edition->displayImage();
                    @endphp
                    <article @class(['card card--hover community-card event-card', 'home-conference--partner' => $edition->is_media_partner]) data-reveal>
                        <a href="{{ $url }}" class="community-card__media" tabindex="-1" aria-hidden="true">
                            <img src="{{ $image ? media_url($image) : asset('images/events/og-cover.jpg') }}" @if ($image && ($srcset = media_srcset($image))) srcset="{{ $srcset }}" sizes="(max-width: 767px) 80vw, 400px" @endif alt="" loading="lazy" decoding="async" width="1200" height="630">
                            @if ($edition->is_media_partner)
                                <span class="home-conference__ribbon">{{ __('home.partner_ribbon') }}</span>
                            @elseif ($phase)
                                <span class="badge badge--status-{{ $phaseClass[$phase] }} community-card__badge">{{ __('events.phase.'.$phase) }}</span>
                            @endif
                        </a>

                        <div class="community-card__body">
                            @if ($date = $edition->dateRangeLabel())
                                <div class="community-card__meta event-card__date">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    <time datetime="{{ $edition->event_start_at->copy()->timezone('Asia/Muscat')->toDateString() }}">{{ $date }}</time>
                                </div>
                            @endif

                            <h3 class="community-card__title"><a href="{{ $url }}">{{ $edition->title }}</a></h3>

                            @if ($row->highlights['tags'])
                                <ul class="event-card__tags">
                                    @foreach ($row->highlights['tags'] as $tag)
                                        <li>{{ $tag }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="event-card__links">
                                <a class="community-card__link" href="{{ $url }}">{{ $phase === 'concluded' ? __('events.view_coverage') : __('events.view_details') }} {{ $arrow }}</a>
                                @foreach ($row->highlights['links'] as $link)
                                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ٤) الخدمات الأساسية (أول 4 بترتيب لوحة التحكم)؛ الباقي والأسعار كاملة بصفحة الخدمات --}}
    @if ($services->isNotEmpty())
        <section id="services" class="container home-section home-core-services">
            <div class="home-section__head">
                <div>
                    <div class="section-head__eyebrow">{{ __('home.core_services_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.core_services_title') }}</h2>
                    <p class="home-core-services__desc">{{ __('home.core_services_desc') }}</p>
                </div>
                <a href="{{ localized_route('services.index') }}" class="btn btn--outline">{{ __('home.all_services') }} ({{ $servicesCount }})</a>
            </div>

            <div class="home-core-services__grid">
                @foreach ($services as $service)
                    @php $serviceUrl = $service->slug() ? localized_route('services.show', ['slug' => $service->slug()]) : localized_route('services.index'); @endphp
                    <article class="home-core-service" data-reveal>
                        <a href="{{ $serviceUrl }}" class="home-core-service__media" tabindex="-1" aria-hidden="true">
                            <img src="{{ media_url($service->image) }}" @if ($srcset = media_srcset($service->image)) srcset="{{ $srcset }}" sizes="(max-width: 767px) 46vw, 300px" @endif alt="" loading="lazy" decoding="async" width="800" height="600">
                        </a>
                        <div class="home-core-service__body">
                            <h3><a href="{{ $serviceUrl }}">{{ $service->localizedTitle() }}</a></h3>
                            @if ($price = $service->priceHtml())
                                <div class="home-core-service__price">{{ $price }}</div>
                            @endif
                            <div class="home-core-service__actions">
                                <a href="{{ $serviceUrl }}" class="home-core-service__link">{{ __('home.service_details') }} {{ $arrow }}</a>
                                <a href="{{ $service->requestUrl() }}" class="home-core-service__request">{{ __('home.request_service') }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- التقنيات المستخدمة --}}
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

    {{-- ٥) أحدث المشاريع --}}
    @if ($works->isNotEmpty())
        <section class="container home-section">
            <div class="home-section__head">
                <div>
                    <div class="section-head__eyebrow">{{ __('home.portfolio_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.portfolio_title') }} <em>{{ __('home.portfolio_title_highlight') }}</em></h2>
                </div>
                <a href="{{ localized_route('works.index') }}" class="btn btn--outline">{{ __('home.all_projects') }}</a>
            </div>

            <div class="home-scroller">
                @foreach ($works as $work)
                    <a href="{{ localized_route('works.show', ['work' => $work]) }}" class="card card--hover home-work-card" data-reveal>
                        <div class="home-work-card__media">
                            <img src="{{ media_url($work->cover_image) }}" @if ($srcset = media_srcset($work->cover_image)) srcset="{{ $srcset }}" sizes="(max-width: 767px) 80vw, 400px" @endif alt="{{ $work->localized('title') }}" loading="lazy" decoding="async" width="800" height="640">
                        </div>
                        <div class="home-work-card__body">
                            <div class="home-work-card__tag">{{ $work->service?->localizedTitle() ?? $work->type_label }}</div>
                            <div class="home-work-card__title">{{ $work->localized('title') }}</div>
                            @if ($work->outcome)
                                <div class="home-work-card__outcome">✓ {{ $work->localized('outcome') }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ٦) المقالات، وشهادات المؤسس مع رابط صفحته --}}
    @if ($showArticles && $latestArticles->isNotEmpty())
        <section id="articles" class="container home-section home-articles">
            <div class="home-section__head">
                <div>
                    <div class="section-head__eyebrow">{{ __('home.articles_section_badge') }}</div>
                    <h2 class="section-head__title">{{ __('home.articles_section_title') }}</h2>
                </div>
                <a href="{{ article_route('index') }}" class="btn btn--outline">{{ __('home.all_articles') }}</a>
            </div>

            <div class="home-scroller">
                @foreach ($latestArticles as $article)
                    <a href="{{ article_route('show', [$article->slug()]) }}" class="card card--hover home-work-card" data-reveal>
                        <div class="home-work-card__media home-article-card__media">
                            <img src="{{ media_url($article->displayImage()) }}" @if ($srcset = media_srcset($article->displayImage())) srcset="{{ $srcset }}" sizes="(max-width: 767px) 80vw, 400px" @endif alt="" loading="lazy" decoding="async" width="800" height="450">
                        </div>
                        <div class="home-work-card__body">
                            <div class="home-work-card__tag">{{ optional($article->published_at)->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}</div>
                            <div class="home-work-card__title">{{ $article->title }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.certificates')

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

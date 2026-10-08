@extends('layouts.app')

@php
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $host = $coverage['host'];
    $stops = $coverage['stops'];
    $ordinals = $isEn
        ? ['Stop one', 'Stop two', 'Stop three', 'Stop four', 'Stop five']
        : ['المحطة الأولى', 'المحطة الثانية', 'المحطة الثالثة', 'المحطة الرابعة', 'المحطة الخامسة'];
    $pageTitle = $t('خمس محطات من سايبر إكس عُمان 2026', 'Five Stops from CyberX Oman 2026');
    $pageDescription = $t(
        'تغطية ميدانية من مسقط: الاستعداد للاختراق، والاستمرارية، وأمن القطاع الصحي، والذكاء الاصطناعي وحوكمته — بقلم سالم الحجري.',
        'Field coverage from Muscat: breach readiness, continuity, healthcare security, and AI and its governance — by Salem Al Hajri.'
    );
    $jsData = [
        'stops' => $stops,
        'photos' => array_column($coverage['thanks']['photos'], 'src'),
        'labels' => ['of' => $t('من', 'of'), 'ordinals' => $ordinals],
    ];
@endphp

@section('title', $pageTitle.' — ALYASI')
@section('meta_description', $pageDescription)
@section('canonical', $isEn ? $urlEn : $urlAr)
@section('hreflang_ar', $urlAr)
@section('hreflang_en', $urlEn)
@section('og_type', 'article')
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)
@section('og_url', $isEn ? $urlEn : $urlAr)
@section('og_image', $coverage['hall'])

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/event-coverage.css') }}">
@endpush

@section('content')
    <x-page-hero
        :badge="$t('تغطية ميدانية · CyberX Oman 2026', 'Field coverage · CyberX Oman 2026')"
        :title="$t('خمس', 'Five')"
        :highlight="$t('محطات', 'Stops')"
        :description="$t('ماذا لو وقع الاختراق فعلًا؟ رحلة من مسقط في خمس محطات.', 'What if a breach actually happens? A journey from Muscat in five stops.')"
        :image="$coverage['hall']"
        :image-width="1449"
        :image-height="2576"
    />

    <div class="coverage container">
        {{-- الراوي: سالم -- بطاقة الكاتب وجملة الافتتاح --}}
        <header class="coverage-byline" data-reveal>
            <img class="coverage-byline__avatar" src="{{ $host['avatar'] }}" alt="{{ $host['name'] }}" width="72" height="72">
            <div class="coverage-byline__who">
                <span class="coverage-byline__label">{{ $t('بقلم', 'By') }}</span>
                <strong>{{ $host['name'] }}</strong>
                <span class="coverage-byline__meta">{{ $t('6 أكتوبر 2026 · مسقط · 5 محطات', '6 October 2026 · Muscat · 5 stops') }}</span>
            </div>
            <p class="coverage-byline__intro">{{ $host['intro'] }}</p>
        </header>

        {{-- المحطات الخمس على خط زمني واحد، وكلام سالم بينها --}}
        <ol class="coverage-timeline" aria-label="{{ $t('المحطات الخمس', 'The five stops') }}">
            @foreach ($stops as $i => $stop)
                @if ($stop['lead'])
                    <li class="coverage-bridge" data-reveal>
                        <img src="{{ $host['avatar'] }}" alt="" width="32" height="32">
                        <p>{{ $stop['lead'] }}</p>
                    </li>
                @endif

                <li class="coverage-stop" data-reveal>
                    <span class="coverage-stop__node" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
                    <article class="stop-card">
                        <span class="stop-card__watermark" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
                        <div class="stop-card__top">
                            <div class="stop-card__photo {{ $stop['group'] ? 'stop-card__photo--group' : '' }}">
                                <img src="{{ $stop['image'] }}" alt="{{ $stop['name'] }}" loading="lazy">
                            </div>
                            <div class="stop-card__who">
                                <span class="stop-card__kicker">{{ $ordinals[$i] }}</span>
                                <h2 class="stop-card__name">{{ $stop['name'] }}</h2>
                                <p class="stop-card__role">{{ $stop['role'] }}</p>
                            </div>
                        </div>
                        <div class="stop-card__bottom">
                            <h3 class="stop-card__title">{{ $stop['headline'] }}</h3>
                            <button type="button" class="stop-card__btn" data-stop="{{ $i }}" aria-haspopup="dialog">
                                {{ $t('التفاصيل', 'Details') }}
                                <span aria-hidden="true">{{ $isEn ? '→' : '←' }}</span>
                            </button>
                        </div>
                    </article>
                </li>
            @endforeach
        </ol>

        {{-- الخلاصة --}}
        <section class="coverage-outro" data-reveal>
            <img class="coverage-outro__avatar" src="{{ $host['avatar'] }}" alt="" width="56" height="56">
            <p class="coverage-outro__text">{{ $coverage['outro'] }}</p>
            <div class="coverage-outro__actions">
                <a class="btn btn--light" href="{{ $eventUrl }}">{{ $t('صفحة المؤتمر', 'Conference page') }}</a>
                <button type="button" class="btn btn--ghost-light" id="coverageShare"
                        data-title="{{ $pageTitle }}" data-copied="{{ $t('تم نسخ الرابط', 'Link copied') }}">
                    {{ $t('مشاركة التغطية', 'Share coverage') }}
                </button>
            </div>
        </section>

        {{-- شكر وتقدير لمن خصصوا وقتهم للمقابلات --}}
        <section class="coverage-thanks" data-reveal>
            <p class="coverage-thanks__teaser"><span aria-hidden="true">🎙️</span> {{ $coverage['thanks']['teaser'] }}</p>
            @php($photos = $coverage['thanks']['photos'])
            <div class="coverage-gallery" data-count="{{ count($photos) }}" aria-roledescription="carousel" aria-label="{{ $t('صور من المؤتمر', 'Photos from the conference') }}">
                <div class="coverage-gallery__track" id="coverageGalleryTrack">
                    @foreach ($photos as $i => $photo)
                        <button type="button" class="coverage-gallery__item" data-photo="{{ $i }}"
                                aria-label="{{ $t('عرض الصورة', 'View photo') }} {{ $i + 1 }}">
                            <img src="{{ $photo['src'] }}" alt="{{ $coverage['thanks']['title'] }}"
                                 width="{{ $photo['width'] }}" height="{{ $photo['height'] }}" loading="lazy" draggable="false">
                        </button>
                    @endforeach
                </div>
                @if (count($photos) > 1)
                    <button type="button" class="coverage-gallery__arrow coverage-gallery__arrow--prev" id="coverageGalleryPrev" aria-label="{{ $t('السابقة', 'Previous') }}">{{ $isEn ? '‹' : '›' }}</button>
                    <button type="button" class="coverage-gallery__arrow coverage-gallery__arrow--next" id="coverageGalleryNext" aria-label="{{ $t('التالية', 'Next') }}">{{ $isEn ? '›' : '‹' }}</button>
                    <div class="coverage-gallery__dots" id="coverageGalleryDots" aria-hidden="true"></div>
                @endif
            </div>
            <div class="coverage-thanks__body">
                <h2 class="coverage-thanks__title">{{ $coverage['thanks']['title'] }}</h2>
                <p class="coverage-thanks__text">{{ $coverage['thanks']['text'] }}</p>
                <span class="coverage-thanks__sign">— {{ $host['name'] }}</span>
            </div>
        </section>
    </div>

    {{-- مودال التفاصيل: شاشة كاملة بهوية المنصة، رأس ثابت فيه الإغلاق،
         وتنقّل سابق/تالي بين المحطات بدون الرجوع للصفحة. --}}
    <div class="coverage-modal" id="coverageModal" role="dialog" aria-modal="true" aria-labelledby="coverageModalTitle" hidden>
        <header class="coverage-modal__bar">
            <div class="coverage-modal__progress" id="coverageModalProgress" aria-hidden="true">
                @foreach ($stops as $i => $stop)
                    <span></span>
                @endforeach
            </div>
            <span class="coverage-modal__count" id="coverageModalCount"></span>
            <button type="button" class="coverage-modal__close" id="coverageModalClose" aria-label="{{ $t('إغلاق', 'Close') }}">✕</button>
        </header>

        <div class="coverage-modal__body">
            <div class="coverage-modal__speaker">
                <div class="stop-card__photo" id="coverageModalPhotoBox"><img id="coverageModalPhoto" src="" alt=""></div>
                <div>
                    <span class="stop-card__kicker" id="coverageModalKicker"></span>
                    <h2 id="coverageModalName"></h2>
                    <p id="coverageModalRole"></p>
                </div>
            </div>
            <h1 class="coverage-modal__title" id="coverageModalTitle"></h1>
            <p class="coverage-modal__sub" id="coverageModalSub" hidden></p>
            <div class="coverage-article" id="coverageModalArticle"></div>
        </div>

        <nav class="coverage-modal__nav" aria-label="{{ $t('التنقل بين المحطات', 'Stop navigation') }}">
            <button type="button" class="coverage-nav-btn" id="coverageModalPrev">
                <small>{{ $isEn ? '←' : '→' }} {{ $t('السابق', 'Previous') }}</small><span></span>
            </button>
            <button type="button" class="coverage-nav-btn coverage-nav-btn--next" id="coverageModalNext">
                <small>{{ $t('التالي', 'Next') }} {{ $isEn ? '→' : '←' }}</small><span></span>
            </button>
        </nav>
    </div>

    {{-- عارض صور المعرض بشاشة كاملة --}}
    <div class="coverage-lightbox" id="coverageLightbox" role="dialog" aria-modal="true" aria-label="{{ $t('معرض الصور', 'Photo gallery') }}" hidden>
        <button type="button" class="coverage-lightbox__close" id="coverageLightboxClose" aria-label="{{ $t('إغلاق', 'Close') }}">✕</button>
        <button type="button" class="coverage-lightbox__nav coverage-lightbox__nav--prev" id="coverageLightboxPrev" aria-label="{{ $t('السابقة', 'Previous') }}">{{ $isEn ? '‹' : '›' }}</button>
        <img id="coverageLightboxImg" src="" alt="">
        <button type="button" class="coverage-lightbox__nav coverage-lightbox__nav--next" id="coverageLightboxNext" aria-label="{{ $t('التالية', 'Next') }}">{{ $isEn ? '›' : '‹' }}</button>
        <span class="coverage-lightbox__count" id="coverageLightboxCount"></span>
    </div>

    <script type="application/json" id="coverageData">@json($jsData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
@endsection

@push('scripts')
    <script src="{{ versioned_asset('js/pages/event-coverage.js') }}" defer></script>
@endpush

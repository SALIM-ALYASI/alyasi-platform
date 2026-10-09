@extends('layouts.app')

@php
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $pageTitle = $t('لقاءات صحفية من سايبر إكس عُمان 2026', 'Interviews from CyberX Oman 2026');
    $pageDescription = $t(
        'أسئلة سالم الحجري بصوته، وإجابات ضيوف سايبر إكس عُمان 2026 بأصواتهم ونصّها كاملًا.',
        'Salem Al Hajri’s questions in his own voice, and the answers of CyberX Oman 2026 guests in theirs, with full transcripts.'
    );
    $urlAr = route('event_coverage.cyberx_oman_2026.interviews');
    $urlEn = route('event_coverage.cyberx_oman_2026.interviews.en');
@endphp

@section('title', $pageTitle.' — ALYASI')
@section('meta_description', $pageDescription)
@section('canonical', $isEn ? $urlEn : $urlAr)
@section('hreflang_ar', $urlAr)
@section('hreflang_en', $urlEn)
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)
@section('og_url', $isEn ? $urlEn : $urlAr)
@section('og_image', asset('images/events/cyberx-2026/hall.jpg'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/event-interviews.css') }}">
@endpush

@section('content')
    <x-page-hero
        :badge="$t('لقاءات صحفية · CyberX Oman 2026', 'Interviews · CyberX Oman 2026')"
        :title="$t('لقاءات', 'Conversations')"
        :highlight="$t('مع الضيوف', 'with the guests')"
        :description="$t('أسئلتي بصوتي، وإجاباتهم بأصواتهم. استمع أو اقرأ.', 'My questions in my voice, their answers in theirs. Listen or read.')"
        :image="asset('images/events/cyberx-2026/hall.jpg')"
        :image-width="1449"
        :image-height="2576"
    />

    <section class="container interviews-hub">
        <div class="interviews-grid">
            @foreach ($guests as $guest)
                @if ($guest['published'])
                    @php
                        $inPageLanguage = in_array($isEn ? 'en' : 'ar', $guest['languages'], true);
                        $cardUrl = $inPageLanguage
                            ? localized_route('event_coverage.cyberx_oman_2026.interview', ['guest' => $guest['slug']])
                            : route('event_coverage.cyberx_oman_2026.interview', ['guest' => $guest['slug']]);
                    @endphp
                    <a class="interview-card" data-reveal href="{{ $cardUrl }}" @unless ($inPageLanguage) hreflang="ar" @endunless>
                        <span class="interview-card__top">
                            @include('events.coverage.interviews._avatar', ['guest' => $guest, 'size' => 64])
                            <span class="interview-card__who">
                                <strong>{{ $guest['name'] }}</strong>
                                <small>{{ $guest['role'] }}</small>
                            </span>
                        </span>
                        @if ($guest['quote'])
                            <span class="interview-card__quote">«{{ $guest['quote'] }}»</span>
                        @endif
                        <span class="interview-card__meta">
                            <span>{{ $guest['topic'] }}</span>
                            <span>{{ count($guest['items']) }} {{ $t('أسئلة', 'questions') }} · {{ $guest['minutes'] }} {{ $t('دقائق', 'min') }}</span>
                        </span>
                        <span class="interview-card__cta">
                            <span class="interview-card__play" aria-hidden="true">▶</span>
                            {{ $t('استمع واقرأ', 'Listen & read') }}
                            @unless ($inPageLanguage)
                                <span class="interview-card__lang">In Arabic</span>
                            @endunless
                        </span>
                    </a>
                @else
                    <div class="interview-card interview-card--soon" data-reveal>
                        <span class="interview-card__top">
                            @include('events.coverage.interviews._avatar', ['guest' => $guest, 'size' => 64])
                            <span class="interview-card__who">
                                <strong>{{ $guest['name'] }}</strong>
                                <small>{{ $guest['role'] }}</small>
                            </span>
                        </span>
                        <span class="interview-card__soon">{{ $t('قريبًا', 'Coming soon') }}</span>
                    </div>
                @endif
            @endforeach
        </div>

        <p class="interviews-hub__back">
            <a href="{{ $coverageUrl }}">{{ $isEn ? '←' : '→' }} {{ $t('خمس محطات من سايبر إكس عُمان 2026', 'Five Stops from CyberX Oman 2026') }}</a>
        </p>
    </section>
@endsection

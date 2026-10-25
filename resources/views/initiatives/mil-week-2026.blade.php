@extends('layouts.app')

{{-- مبادرة «قبل أن تشارك الخبر… تحقّق» -- الأسبوع العالمي للدراية الإعلامية والمعلوماتية 2026.
     مبادرة مستقلة من ALYASI: بدون شعارات اليونسكو أو رؤية عُمان 2040 الرسمية. --}}
@php
    $isEn = app()->getLocale() === 'en';
    $other = $isEn ? 'ar' : 'en';
    $pageUrl = localized_route('mil-week');
    $otherUrl = localized_route('mil-week', [], $other);
    $heroBg = 'images/home/hero-muscat-'.($isEn ? 'en' : 'ar');
@endphp

@section('title', __('mil_week.meta_title'))
@section('meta_description', __('mil_week.meta_description'))
@section('canonical', $pageUrl)
@section('og_url', $pageUrl)
@section('hreflang_ar', localized_route('mil-week', [], 'ar'))
@section('hreflang_en', localized_route('mil-week', [], 'en'))
@section('og_image', asset('images/initiatives/mil-week-2026-og-'.($isEn ? 'en' : 'ar').'.jpg'))
@section('og_image_width', '1200')
@section('og_image_height', '630')

@section('schema')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@'.'type' => 'Event',
    'name' => __('mil_week.title'),
    'alternateName' => __('mil_week.title_en'),
    'description' => __('mil_week.meta_description'),
    'startDate' => '2026-10-26',
    'endDate' => '2026-10-31',
    'eventStatus' => 'https://schema.org/EventScheduled',
    'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
    'isAccessibleForFree' => true,
    'inLanguage' => ['ar', 'en'],
    'location' => ['@'.'type' => 'VirtualLocation', 'url' => $pageUrl],
    'organizer' => [
        '@'.'type' => 'Organization',
        'name' => 'ALYASI',
        'url' => url('/'),
        'address' => [
            '@'.'type' => 'PostalAddress',
            'addressLocality' => $isEn ? 'Bidiyah' : 'بدية',
            'addressRegion' => $isEn ? 'North Al Sharqiyah' : 'شمال الشرقية',
            'addressCountry' => 'OM',
        ],
    ],
    'image' => asset('images/initiatives/mil-week-2026-og-'.($isEn ? 'en' : 'ar').'.jpg'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/mil-week.css') }}">
@endpush

@section('content')
<a class="mil-skip" href="#mil-main">{{ __('mil_week.skip') }}</a>

{{-- الواجهة: شعار المبادرة بخط عربي كبير، على ساحل مسقط ليلًا --}}
<section class="mil-hero" aria-labelledby="mil-title">
    <img class="mil-hero__bg" src="{{ versioned_asset($heroBg.'-960.webp') }}" srcset="{{ versioned_asset($heroBg.'-960.webp') }} 960w, {{ versioned_asset($heroBg.'.webp') }} 1731w" sizes="100vw" alt="" width="1731" height="909" fetchpriority="high" decoding="async">
    <div class="mil-hero__shade" aria-hidden="true"></div>

    <div class="container mil-hero__grid">
        <div class="mil-hero__text">
            <p class="mil-hero__badge">
                <img src="{{ asset('images/logo/logo-white-trimmed.png') }}" alt="" width="20" height="24">
                {{ __('mil_week.badge') }}
            </p>

            <h1 id="mil-title" class="mil-hero__title">{{ __('mil_week.title') }}</h1>
            <p class="mil-hero__alt" lang="{{ $other }}" dir="{{ $isEn ? 'rtl' : 'ltr' }}">{{ __('mil_week.title_en') }}</p>
            <p class="mil-hero__tagline">{{ __('mil_week.tagline') }}</p>

            <div class="mil-hero__actions">
                <a class="btn btn--light" href="#mil-steps">{{ __('mil_week.cta_steps') }}</a>
                <a class="btn btn--ghost-light" href="#mil-about">{{ __('mil_week.cta_about') }}</a>
                <a class="mil-lang" href="{{ $otherUrl }}" hreflang="{{ $other }}" lang="{{ $other }}" aria-label="{{ __('mil_week.switch_label') }}">
                    <i class="fa-solid fa-language" aria-hidden="true"></i> {{ __('mil_week.switch') }}
                </a>
            </div>

            <p class="mil-hero__dates">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <time datetime="2026-10-26">{{ __('mil_week.facts.dates.1') }}</time>
            </p>
        </div>

        {{-- رسم توضيحي: عدسة تحقق فوق بطاقة خبر، مع علامة صح ورمز ذكاء اصطناعي --}}
        <figure class="mil-hero__art" role="img" aria-label="{{ __('mil_week.image_alt') }}">
            <svg viewBox="0 0 360 300" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="milCard" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1b3560"/><stop offset="1" stop-color="#0f1f38"/></linearGradient>
                    <linearGradient id="milLens" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#9fc1ff" stop-opacity=".35"/><stop offset="1" stop-color="#5e76a8" stop-opacity=".15"/></linearGradient>
                </defs>
                <rect x="24" y="34" width="250" height="210" rx="18" fill="url(#milCard)" stroke="#5e76a8" stroke-opacity=".6"/>
                <rect x="46" y="58" width="120" height="12" rx="6" fill="#9fb0ce" opacity=".9"/>
                <rect x="46" y="84" width="200" height="8" rx="4" fill="#cbd5e6" opacity=".5"/>
                <rect x="46" y="102" width="180" height="8" rx="4" fill="#cbd5e6" opacity=".5"/>
                <rect x="46" y="120" width="196" height="8" rx="4" fill="#cbd5e6" opacity=".5"/>
                <rect x="46" y="146" width="92" height="66" rx="10" fill="#2c4a7c" opacity=".9"/>
                <path d="M58 200l18-22 14 14 10-10 22 18z" fill="#9fc1ff" opacity=".8"/>
                <rect x="152" y="150" width="90" height="8" rx="4" fill="#cbd5e6" opacity=".45"/>
                <rect x="152" y="168" width="70" height="8" rx="4" fill="#cbd5e6" opacity=".45"/>
                <g transform="translate(262 58)">
                    <rect width="62" height="34" rx="10" fill="#0b1220" stroke="#9fc1ff" stroke-opacity=".7"/>
                    <text x="31" y="23" text-anchor="middle" font-family="Inter, sans-serif" font-size="16" font-weight="800" fill="#9fc1ff">AI ?</text>
                </g>
                <circle cx="230" cy="196" r="58" fill="url(#milLens)" stroke="#e6ecf5" stroke-width="10"/>
                <path d="M272 238l42 42" stroke="#e6ecf5" stroke-width="16" stroke-linecap="round"/>
                <circle cx="230" cy="196" r="30" fill="#3fb37f"/>
                <path d="M216 197l10 10 20-22" fill="none" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </figure>
    </div>
</section>

<div id="mil-main" tabindex="-1">

    {{-- بطاقة المبادرة --}}
    <section class="container mil-section" aria-labelledby="mil-facts-title">
        <h2 id="mil-facts-title" class="mil-section__title mil-section__title--small">{{ __('mil_week.facts_title') }}</h2>
        <dl class="mil-facts">
            @foreach (__('mil_week.facts') as $key => [$label, $value])
                <div class="mil-fact">
                    <dt>{{ $label }}</dt>
                    <dd>@if ($key === 'dates')<time datetime="2026-10-26">{{ $value }}</time>@else{{ $value }}@endif</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- التعريف بالمبادرة --}}
    <section id="mil-about" class="container mil-section mil-about" aria-labelledby="mil-about-title">
        <p class="mil-section__eyebrow">{{ __('mil_week.about_badge') }}</p>
        <h2 id="mil-about-title" class="mil-section__title">{{ __('mil_week.about_title') }}</h2>
        @foreach (__('mil_week.about') as $paragraph)
            <p @class(['mil-about__lead' => $loop->last])>{{ $paragraph }}</p>
        @endforeach
    </section>

    {{-- فيديو المبادرة: يظهر بس لما يكون رابط الفيديو في الإعدادات رابط يوتيوب --}}
    @if ($videoId)
        <section id="mil-video" class="container mil-section mil-video" aria-labelledby="mil-video-title">
            <div class="mil-video__player">
                <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0" title="{{ __('mil_week.video_frame_title') }}" loading="lazy" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <div class="mil-video__text">
                <p class="mil-section__eyebrow">{{ __('mil_week.video_badge') }}</p>
                <h2 id="mil-video-title" class="mil-section__title">{{ __('mil_week.video_title') }}</h2>
                <p class="mil-video__lead">{{ __('mil_week.video_lead') }}</p>
                <p class="mil-video__note"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> {{ __('mil_week.video_ai_note') }}</p>
                <a class="mil-video__yt" href="https://www.youtube.com/shorts/{{ $videoId }}" target="_blank" rel="noopener">
                    <i class="fa-brands fa-youtube" aria-hidden="true"></i> {{ __('mil_week.video_youtube') }}
                </a>
            </div>
        </section>
    @endif

    {{-- خمس خطوات --}}
    <section id="mil-steps" class="mil-steps-band" aria-labelledby="mil-steps-title">
        <div class="container mil-section">
            <p class="mil-section__eyebrow">{{ __('mil_week.steps_badge') }}</p>
            <h2 id="mil-steps-title" class="mil-section__title">{{ __('mil_week.steps_title') }}</h2>
            <ol class="mil-steps">
                @foreach (__('mil_week.steps') as $step)
                    <li class="mil-step">
                        <span class="mil-step__num" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="mil-step__icon" aria-hidden="true"><i class="{{ $step['icon'] }}"></i></span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- المواد: «قريبًا» لين تنشر، وبعدها رابط من الإعدادات --}}
    <section class="container mil-section" aria-labelledby="mil-materials-title">
        <p class="mil-section__eyebrow">{{ __('mil_week.materials_badge') }}</p>
        <h2 id="mil-materials-title" class="mil-section__title">{{ __('mil_week.materials_title') }}</h2>
        <ul class="mil-materials">
            @foreach (__('mil_week.materials') as $key => $item)
                @php $url = $materials[$key] ?? null; @endphp
                <li class="mil-material @unless ($url) is-soon @endunless">
                    <span class="mil-material__icon" aria-hidden="true"><i class="{{ $item['icon'] }}"></i></span>
                    <p class="mil-material__type">{{ $item['type'] }}</p>
                    <h3 class="mil-material__title">
                        @if ($url)
                            <a href="{{ $url }}" @if (! str_starts_with($url, url('/'))) target="_blank" rel="noopener" @endif>{{ $item['title'] }}</a>
                        @else
                            {{ $item['title'] }}
                        @endif
                    </h3>
                    @if ($url)
                        <span class="mil-material__status mil-material__status--live">{{ __('mil_week.open') }} {{ ui_arrow() }}</span>
                    @else
                        <span class="mil-material__status">{{ __('mil_week.soon') }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    {{-- رؤية عُمان 2040: نص فقط، بدون الشعار الرسمي ولا ادعاء شراكة --}}
    <section class="container mil-section" aria-labelledby="mil-vision-title">
        <div class="mil-vision">
            <span class="mil-vision__icon" aria-hidden="true"><i class="fa-solid fa-seedling"></i></span>
            <div>
                <h2 id="mil-vision-title" class="mil-vision__title">{{ __('mil_week.vision_title') }}</h2>
                <p>{{ __('mil_week.vision') }}</p>
            </div>
        </div>
    </section>

    {{-- المشاركة --}}
    <section class="container mil-section mil-share" aria-labelledby="mil-share-title">
        <h2 id="mil-share-title" class="mil-section__title mil-section__title--small">{{ __('mil_week.share_title') }}</h2>
        @php $shareText = __('mil_week.share_text').' '.$pageUrl; @endphp
        <div class="mil-share__buttons">
            <a href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="mil-share__btn" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
            <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="mil-share__btn" aria-label="X"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($pageUrl) }}" target="_blank" rel="noopener" class="mil-share__btn" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($pageUrl) }}" target="_blank" rel="noopener" class="mil-share__btn" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
        </div>
    </section>

    {{-- العلاقة باليونسكو --}}
    <section class="container mil-section mil-disclaimer-wrap">
        <p class="mil-disclaimer" role="note">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            {{ __('mil_week.disclaimer') }}
        </p>
    </section>
</div>
@endsection

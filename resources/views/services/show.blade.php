@extends('layouts.app')

@section('title', $service->localizedTitle().' — ALYASI')

@section(
    'meta_description',
    \Illuminate\Support\Str::limit(
        strip_tags($service->localizedDescription()),
        160
    )
)

@php
    $arServicePermalink = $service->permalinks->firstWhere('locale', 'ar');
    $enServicePermalink = $service->permalinks->firstWhere('locale', 'en');
@endphp

{{-- =========================================================
     SEO / Canonical
========================================================= --}}
@section('canonical', $permalink->url())

@section('hreflang_ar', $arServicePermalink ? route('services.show', $arServicePermalink->slug) : '')
@section('hreflang_en', $enServicePermalink ? localized_route('services.show', ['slug' => $enServicePermalink->slug], 'en') : '')

{{-- =========================================================
     Open Graph
========================================================= --}}
@section('og_type', 'website')

@section('og_title', $service->localizedTitle())

@section(
    'og_description',
    \Illuminate\Support\Str::limit(
        strip_tags($service->localizedDescription()),
        160
    )
)

@section('og_url', url()->current())

@section(
    'og_image',
    media_url($service->image)
)

{{-- =========================================================
     Service Schema
========================================================= --}}
@section('schema')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Service',

    'name' => $service->localizedTitle(),

    'description' => \Illuminate\Support\Str::limit(
        strip_tags($service->localizedDescription()),
        160
    ),

    'url' => url()->current(),

    'image' => media_url($service->image),

    'provider' => [
        '@type' => 'Organization',
        'name' => 'ALYASI',
        'url' => url('/'),
    ],

    'offers' => $service->hasPrice() ? [
        '@type' => 'Offer',
        'priceCurrency' => 'OMR',
        'price' => (float) $service->price_from,
        'priceSpecification' => [
            '@type' => 'PriceSpecification',
            'priceCurrency' => 'OMR',
            ($service->price_is_starting ? 'minPrice' : 'price') => (float) $service->price_from,
        ],
    ] : null,

]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection


{{-- =========================================================
     Styles
========================================================= --}}
@push('styles')

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/pages/services-show.css') }}"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | Service Detail Header Fix
        |--------------------------------------------------------------------------
        | يمنع صورة الخدمة من الدخول خلف Header الموقع
        */

        .service-detail__hero-wrap {
            padding-top: 32px;
        }

        .service-detail__hero-media {
            position: relative;
            overflow: hidden;
        }

        .service-detail__hero-media img {
            display: block;
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 640px) {

            .service-detail__hero-wrap {
                padding-top: 20px;
            }

        }

    </style>

@endpush


@section('content')


{{-- =========================================================
     Service Image
     أول عنصر بعد Header الموقع
========================================================= --}}
<section class="container service-detail__hero-wrap">

    <div class="service-detail__hero-media">

        <img
            src="{{ media_url($service->image) }}"
            @if ($srcset = media_srcset($service->image)) srcset="{{ $srcset }}" sizes="(max-width: 767px) 100vw, 1200px" @endif
            alt="{{ $service->localizedTitle() }}"
            decoding="async"
        >

    </div>

</section>


{{-- =========================================================
     Service Content
========================================================= --}}
<section class="container service-detail__body">

    <span class="badge">
        {{ __('services.digital_service') }}
    </span>


    <h1 class="service-detail__title">
        {{ $service->localizedTitle() }}
    </h1>


    <p class="service-detail__desc">
        {{ $service->localizedDescription() }}
    </p>


    @if ($service->hasPrice())
        <div class="service-detail__price">
            <div class="service-detail__price-main">
                <span class="service-detail__price-label">{{ __('services.pricing.card_label') }}</span>
                <strong>{{ $service->priceHtml() }}</strong>
                @if ($unit = $service->localizedPriceUnit())
                    <span class="service-detail__price-unit">{{ omr_text($unit) }}</span>
                @endif
            </div>
            @if ($note = $service->localizedPriceNote())
                <p class="service-detail__price-note">{{ omr_text($note) }}</p>
            @endif
            <p class="service-detail__price-disclaimer">{{ __('services.pricing.disclaimer') }}</p>
            <a href="{{ localized_route('services.index') }}#pricing" class="service-detail__price-link">{{ __('services.pricing.view_all') }}</a>
        </div>
    @endif


    {{-- =====================================================
         ماذا تستلم؟
    ====================================================== --}}
    @php $deliverables = $service->localizedDeliverables(); @endphp
    @if ($deliverables)
        <div class="service-scope">
            <div class="section-head service-scope__head">
                <div class="section-head__eyebrow">{{ __('services.scope.badge') }}</div>
                <h2 class="section-head__title">{{ __('services.scope.title') }} <em>{{ __('services.scope.title_highlight') }}</em></h2>
            </div>

            <div class="service-scope__grid">
                <ul class="service-scope__list">
                    @foreach ($deliverables as $item)
                        <li><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $item }}</li>
                    @endforeach
                </ul>

                <dl class="service-scope__facts">
                    @if ($revisions = $service->localizedRevisions())
                        <div><dt>{{ __('services.scope.revisions') }}</dt><dd>{{ $revisions }}</dd></div>
                    @endif
                    @if ($support = $service->localizedSupport())
                        <div><dt>{{ __('services.scope.support') }}</dt><dd>{{ $support }}</dd></div>
                    @endif
                    <div><dt>{{ __('services.scope.duration') }}</dt><dd>{{ __('services.scope.duration_text') }}</dd></div>
                </dl>
            </div>

            <details class="service-scope__terms">
                <summary>{{ __('services.scope.terms_title') }}</summary>
                <ul>
                    @foreach (__('services.scope.terms') as $term)
                        <li>{{ omr_text($term) }}</li>
                    @endforeach
                </ul>
            </details>
        </div>
    @endif


    {{-- =====================================================
         Why ALYASI
    ====================================================== --}}
    <div class="section-head service-detail__why-head">

        <div class="section-head__eyebrow">
            {{ __('services.show.hero_badge') }}
        </div>

        <h2 class="section-head__title">

            {{ __('services.show.why_choose') }}

            <em>ALYASI</em>

        </h2>

        <p class="section-head__desc">
            {{ __('services.show.description') }}
        </p>

    </div>


    {{-- =====================================================
         Service Features
    ====================================================== --}}
    <div class="grid-3 service-detail__why-grid">

        {{-- Quality --}}
        <div class="service-detail__why-item">

            <div class="service-detail__why-label">
                {{ __('services.show.quality_label') }}
            </div>

            <h3>
                {{ __('services.show.quality_title') }}
            </h3>

            <p>
                {{ __('services.show.quality_description') }}
            </p>

        </div>


        {{-- Performance --}}
        <div class="service-detail__why-item">

            <div class="service-detail__why-label">
                {{ __('services.show.performance_label') }}
            </div>

            <h3>
                {{ __('services.show.performance_title') }}
            </h3>

            <p>
                {{ __('services.show.performance_description') }}
            </p>

        </div>


        {{-- Support --}}
        <div class="service-detail__why-item">

            <div class="service-detail__why-label">
                {{ __('services.show.support_label') }}
            </div>

            <h3>
                {{ __('services.show.support_title') }}
            </h3>

            <p>
                {{ __('services.show.support_description') }}
            </p>

        </div>

    </div>


    {{-- =====================================================
         CTA
    ====================================================== --}}
    <div class="cta-band service-detail__cta">

        <h3 class="service-detail__cta-title">

            {{ __('services.show.cta_title') }}

            <em>
                {{ __('services.show.cta_title_highlight') }}
            </em>

        </h3>


        <p class="cta-band__desc">
            {{ __('services.show.cta_description') }}
        </p>


        <a
            href="{{ $service->requestUrl() }}"
            class="btn btn--light"
        >
            {{ __('services.show.request_service') }}
        </a>

    </div>

</section>


{{-- =========================================================
     Related Works
========================================================= --}}
@if ($service->works->isNotEmpty())

    <section class="service-detail__related">

        <div class="container">

            <div class="section-head__eyebrow">
                {{ __('services.show.related_works_badge') }}
            </div>


            <h2 class="section-head__title service-detail__related-title">
                {{ __('services.show.related_works_title') }}
            </h2>


            <div class="grid-3 service-detail__related-grid">

                @foreach ($service->works as $work)

                    <a
                        href="{{ localized_route('works.show', ['work' => $work]) }}"
                        class="card card--hover service-detail__work-card"
                    >

                        <div class="service-detail__work-media">

                            <img
                                src="{{ media_url($work->cover_image) }}"
                                alt="{{ $work->localized('title') }}"
                                loading="lazy"
                            >


                            @if ($work->is_featured)

                                <span class="badge service-detail__work-badge">
                                    {{ __('services.show.related_works_featured') }}
                                </span>

                            @endif

                        </div>


                        <div class="service-detail__work-body">

                            <div class="service-detail__work-tag">
                                {{ $work->type_label }}
                            </div>

                            <div class="service-detail__work-title">
                                {{ $work->localized('title') }}
                            </div>

                        </div>

                    </a>

                @endforeach

            </div>

        </div>

    </section>

@endif


{{-- =========================================================
     Reviews
========================================================= --}}
<section
    id="reviews"
    class="container service-detail__reviews"
>

    <x-reviews
        :reviews="$service->approvedReviews"
        action="{{ route('services.reviews.store', $service) }}"
    />

</section>

@endsection
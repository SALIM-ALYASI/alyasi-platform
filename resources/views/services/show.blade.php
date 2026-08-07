@extends('layouts.app')

@section(
    'title',
    $service->localizedTitle() . ' | ' . __('home.brand')
)

@section(
    'description',
    \Illuminate\Support\Str::limit(
        strip_tags($service->localizedDescription()),
        160
    )
)

@push('styles')
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/works/css/works.css') }}"
>
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/reviews/css/reviews.css') }}"
>
@endpush

@section('content')

@php
    $title = $service->localizedTitle();
    $description = $service->localizedDescription();
@endphp

<section
    class="hero hero-small"
    id="service"
>

    <div class="hero-grid"></div>

    <div class="hero-badge">

        <div class="hero-badge-dot"></div>

        <span>
            {{ __('services.show.hero_badge') }}
        </span>

    </div>

    <h1>
        {{ $title }}
    </h1>

    <p class="hero-sub">
        {{ $description }}
    </p>

    <div class="hero-ctas">

        <a
            href="{{ route('social-links.index') }}"
            class="btn-primary"
        >
            {{ __('services.show.request_service') }}
        </a>

        <a
            href="{{ route('services.index') }}"
            class="btn-secondary"
        >
            {{ __('services.show.all_services') }}
        </a>

    </div>

</section>

@if ($service->image)

<section class="section">

    <div class="service-show-image reveal">

        <img
            src="{{ asset($service->image) }}"
            alt="{{ $title }}"
            loading="eager"
        >

    </div>

</section>

@endif

<section
    class="section"
    id="service-details"
>

    <div class="section-heading reveal">

        <p class="section-tag">
            {{ __('services.show.service_badge') }}
        </p>

        <h2 class="section-title">

            {{ __('services.show.why_choose') }}

            <strong>
                {{ $title }}
            </strong>

        </h2>

        <p class="section-body">
            {{ __('services.show.description') }}
        </p>

    </div>

    <div class="pricing-grid">

        <article class="pricing-card reveal">

            <span class="pricing-label">
                {{ __('services.show.quality_label') }}
            </span>

            <h3>
                {{ __('services.show.quality_title') }}
            </h3>

            <p>
                {{ __('services.show.quality_description') }}
            </p>

        </article>

        <article class="pricing-card featured reveal">

            <span class="pricing-label">
                {{ __('services.show.performance_label') }}
            </span>

            <h3>
                {{ __('services.show.performance_title') }}
            </h3>

            <p>
                {{ __('services.show.performance_description') }}
            </p>

        </article>

        <article class="pricing-card reveal">

            <span class="pricing-label">
                {{ __('services.show.support_label') }}
            </span>

            <h3>
                {{ __('services.show.support_title') }}
            </h3>

            <p>
                {{ __('services.show.support_description') }}
            </p>

        </article>

    </div>

</section>

@if($service->works->isNotEmpty())
    <section class="section">

        <div class="container">

            <div class="section-heading section-center reveal">
                <p class="section-tag">{{ __('services.show.related_works_badge') }}</p>
                <h2 class="section-title">{{ __('services.show.related_works_title') }}</h2>
            </div>

            <div class="works-grid">

                @foreach($service->works as $work)

                    <a href="{{ route('works.show', $work) }}" class="public-work-card">

                        <div class="public-work-card__media">

                            @if($work->cover_image)
                                <img
                                    src="{{ asset('storage/'.$work->cover_image) }}"
                                    alt="{{ $work->title }}"
                                    class="public-work-card__image"
                                    loading="lazy"
                                >
                            @else
                                <div class="public-work-card__placeholder">
                                    <img src="{{ asset('images/logo/logo-dark.png') }}" alt="{{ $work->title }}">
                                </div>
                            @endif

                            <span class="public-work-card__type">{{ $work->type_label }}</span>

                            @if($work->is_featured)
                                <span class="public-work-card__featured">
                                    {{ __('services.show.related_works_featured') }}
                                </span>
                            @endif

                            <div class="public-work-card__overlay">
                                <span>{{ __('services.show.related_works_view') }}</span>
                            </div>

                        </div>

                        <div class="public-work-card__body">

                            <h3 class="public-work-card__title">{{ $work->title }}</h3>

                            @if($work->short_description)
                                <p class="public-work-card__description">{{ $work->short_description }}</p>
                            @endif

                        </div>

                        @if($work->client_name)
                            <div class="public-work-card__footer">
                                <div class="public-work-card__info">
                                    <span><i class="fa-regular fa-user"></i> {{ $work->client_name }}</span>
                                </div>
                            </div>
                        @endif

                    </a>

                @endforeach

            </div>

        </div>

    </section>
@endif

@include('partials.reviews', [
    'reviews' => $service->approvedReviews,
    'reviewFormAction' => route('services.reviews.store', $service),
])

<section class="cta-section">

    <div class="cta-card reveal">

        <span class="section-tag">
            {{ __('services.show.cta_badge') }}
        </span>

        <h2>

            {{ __('services.show.cta_title') }}

            <strong>
                {{ __('services.show.cta_title_highlight') }}
            </strong>

        </h2>

        <p>
            {{ __('services.show.cta_description') }}
        </p>

        <div class="hero-ctas">

            <a
                href="{{ route('social-links.index') }}"
                class="btn-primary"
            >
                {{ __('services.contact_us') }}
            </a>

            <a
                href="{{ route('home') }}"
                class="btn-secondary"
            >
                {{ __('services.back_home') }}
            </a>

        </div>

    </div>

</section>

@endsection
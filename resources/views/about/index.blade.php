@extends('layouts.app')

@section('title', __('about.hero_badge') . ' | ' . __('home.brand'))

@section('description', __('about.hero_description'))

@push('styles')
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/pages/css/pages.css') }}"
    >
@endpush

@section('content')

    <section class="page-hero">

        <div class="container">

            <span class="page-hero-badge">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                {{ __('about.hero_badge') }}
            </span>

            <h1 class="page-hero-title">
                {{ __('about.hero_title') }}
                <strong>{{ __('about.hero_title_highlight') }}</strong>
            </h1>

            <p class="page-hero-description">
                {{ __('about.hero_description') }}
            </p>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="page-content-block">

                <p class="section-tag">{{ __('about.story_badge') }}</p>

                <h2>{{ __('about.story_title') }}</h2>

                <p>{{ __('about.story_body') }}</p>

            </div>

            <div class="page-content-block">

                <p class="section-tag">{{ __('about.mission_badge') }}</p>

                <h2>{{ __('about.mission_title') }}</h2>

                <p>{{ __('about.mission_body') }}</p>

            </div>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="hero-metrics" style="justify-content: center;">

                <div class="metric">
                    <div class="metric-value">
                        <span class="counter" data-target="{{ $yearsOfExperience }}" data-decimals="0">
                            {{ $yearsOfExperience }}
                        </span>
                    </div>
                    <div class="metric-label">{{ __('about.stats_years') }}</div>
                </div>

                <div class="metric">
                    <div class="metric-value">
                        <span class="counter" data-target="{{ $projectsCount }}" data-decimals="0">
                            {{ $projectsCount }}
                        </span>
                    </div>
                    <div class="metric-label">{{ __('about.stats_projects') }}</div>
                </div>

                <div class="metric">
                    <div class="metric-value">
                        <span class="counter" data-target="{{ $servicesCount }}" data-decimals="0">
                            {{ $servicesCount }}
                        </span>
                    </div>
                    <div class="metric-label">{{ __('about.stats_services') }}</div>
                </div>

            </div>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="section-heading section-center reveal">

                <p class="section-tag">{{ __('about.values_badge') }}</p>

                <h2 class="section-title">{{ __('about.values_title') }}</h2>

            </div>

            <div class="page-cards-grid">

                <article class="page-card">
                    <span class="page-card-icon">
                        <i class="fa-solid fa-award" aria-hidden="true"></i>
                    </span>
                    <h3>{{ __('about.value_1_title') }}</h3>
                    <p>{{ __('about.value_1_description') }}</p>
                </article>

                <article class="page-card">
                    <span class="page-card-icon">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </span>
                    <h3>{{ __('about.value_2_title') }}</h3>
                    <p>{{ __('about.value_2_description') }}</p>
                </article>

                <article class="page-card">
                    <span class="page-card-icon">
                        <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                    </span>
                    <h3>{{ __('about.value_3_title') }}</h3>
                    <p>{{ __('about.value_3_description') }}</p>
                </article>

                <article class="page-card">
                    <span class="page-card-icon">
                        <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                    </span>
                    <h3>{{ __('about.value_4_title') }}</h3>
                    <p>{{ __('about.value_4_description') }}</p>
                </article>

            </div>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="section-heading section-center reveal">

                <h2 class="section-title">{{ __('about.cta_title') }}</h2>

                <p class="section-body">{{ __('about.cta_description') }}</p>

            </div>

            <div class="page-cta-actions">

                <a href="{{ route('contact') }}" class="nav-cta">
                    {{ __('about.cta_contact') }}
                </a>

                <a href="{{ route('services.index') }}" class="nav-cta page-cta-secondary">
                    {{ __('about.cta_services') }}
                </a>

            </div>

        </div>

    </section>

@endsection

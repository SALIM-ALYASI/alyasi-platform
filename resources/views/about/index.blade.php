@extends('layouts.app')

@section('title', __('about.hero_title').' '.__('about.hero_title_highlight').' — ALYASI')
@section('meta_description', __('about.hero_description'))
@section('canonical', localized_route('about'))
@section('hreflang_ar', localized_route('about', [], 'ar'))
@section('hreflang_en', localized_route('about', [], 'en'))
@section('og_url', localized_route('about'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/about.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('about.hero_badge')"
        :title="__('about.hero_title')"
        :highlight="__('about.hero_title_highlight')"
        :description="__('about.hero_description')"
    />

    <section class="container about-stats">
        <div class="grid-3">
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">{{ $yearsOfExperience }}+</div>
                <div class="about-stats__label">{{ __('about.stats_years') }}</div>
            </div>
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">{{ $projectsCount }}+</div>
                <div class="about-stats__label">{{ __('about.stats_projects') }}</div>
            </div>
            <div class="about-stats__item" data-reveal>
                <div class="about-stats__value">{{ $servicesCount }}+</div>
                <div class="about-stats__label">{{ __('about.stats_services') }}</div>
            </div>
        </div>
    </section>

    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ __('about.story_badge') }}</div>
            <h2 class="section-head__title">{{ __('about.story_title') }}</h2>
            <p class="section-head__desc">{{ __('about.story_body') }}</p>
        </div>
    </section>

    <section class="about-mission">
        <div class="container">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">{{ __('about.mission_badge') }}</div>
                <h2 class="section-head__title">{{ __('about.mission_title') }}</h2>
                <p class="section-head__desc">{{ __('about.mission_body') }}</p>
            </div>
        </div>
    </section>

    <section class="container about-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ __('about.values_badge') }}</div>
            <h2 class="section-head__title">{{ __('about.values_title') }}</h2>
        </div>

        <div class="grid-2">
            <div class="about-value" data-reveal>
                <h3>{{ __('about.value_1_title') }}</h3>
                <p>{{ __('about.value_1_description') }}</p>
            </div>
            <div class="about-value" data-reveal>
                <h3>{{ __('about.value_2_title') }}</h3>
                <p>{{ __('about.value_2_description') }}</p>
            </div>
            <div class="about-value" data-reveal>
                <h3>{{ __('about.value_3_title') }}</h3>
                <p>{{ __('about.value_3_description') }}</p>
            </div>
            <div class="about-value" data-reveal>
                <h3>{{ __('about.value_4_title') }}</h3>
                <p>{{ __('about.value_4_description') }}</p>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ __('about.cta_title') }}</h2>
            <p class="cta-band__desc">{{ __('about.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('about.cta_contact') }}</a>
                <a href="{{ localized_route('services.index') }}" class="btn btn--ghost-light">{{ __('about.cta_services') }}</a>
            </div>
        </div>
    </section>

@endsection

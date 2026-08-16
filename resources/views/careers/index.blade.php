@extends('layouts.app')

@section('title', __('careers.title').' — ALYASI')
@section('meta_description', __('careers.meta_description'))
@section('canonical', localized_route('careers'))
@section('hreflang_ar', localized_route('careers', [], 'ar'))
@section('hreflang_en', localized_route('careers', [], 'en'))
@section('og_url', localized_route('careers'))
@section('og_image', asset('images/home/hero.webp'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/careers.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('careers.hero_badge')"
        :title="__('careers.hero_title')"
        :highlight="__('careers.hero_title_highlight')"
        :description="__('careers.hero_description')"
    />

    <section class="container careers-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ __('careers.values_badge') }}</div>
            <h2 class="section-head__title">{{ __('careers.values_title') }}</h2>
        </div>

        <div class="grid-3">
            <div class="careers-value" data-reveal>
                <h3>{{ __('careers.value_1_title') }}</h3>
                <p>{{ __('careers.value_1_description') }}</p>
            </div>
            <div class="careers-value" data-reveal>
                <h3>{{ __('careers.value_2_title') }}</h3>
                <p>{{ __('careers.value_2_description') }}</p>
            </div>
            <div class="careers-value" data-reveal>
                <h3>{{ __('careers.value_3_title') }}</h3>
                <p>{{ __('careers.value_3_description') }}</p>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ __('careers.cta_title') }}</h2>
            <p class="cta-band__desc">{{ __('careers.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('careers.cta_contact') }}</a>
            </div>
            @if ($contactEmail)
                <p class="cta-band__email">
                    {{ __('careers.cta_email_prefix') }}
                    <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                </p>
            @endif
        </div>
    </section>

@endsection

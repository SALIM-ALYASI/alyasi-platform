@extends('layouts.app')

@section('title', __('social-links.title').' — ALYASI')
@section('meta_description', __('social-links.hero_description'))
@section('canonical', localized_route('social-links.index'))
@section('hreflang_ar', localized_route('social-links.index', [], 'ar'))
@section('hreflang_en', localized_route('social-links.index', [], 'en'))
@section('og_url', localized_route('social-links.index'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/social-links.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('social-links.hero_badge')"
        :title="__('social-links.hero_title')"
        :description="__('social-links.hero_description')"
    />

    <section class="container social-links-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ __('social-links.section_badge') }}</div>
            <h2 class="section-head__title">{{ __('social-links.section_title') }}</h2>
            <p class="section-head__desc">{{ __('social-links.section_description') }}</p>
        </div>

        @if ($socialLinks->isNotEmpty())
            <div class="grid-3">
                @foreach ($socialLinks as $link)
                    <a href="{{ $link->url }}" @if($link->open_new_tab) target="_blank" rel="noopener" @endif class="card card--hover social-link-card" data-reveal>
                        <div class="social-link-card__icon">
                            <i class="{{ $link->icon }}" aria-hidden="true"></i>
                        </div>
                        <div class="social-link-card__body">
                            <span class="badge">{{ __('social-links.official') }}</span>
                            <h3>{{ $link->name }}</h3>
                            @if ($link->username)
                                <p>{{ $link->username }}</p>
                            @endif
                            <span class="social-link-card__cta">{{ str_contains(strtolower($link->platform), 'whatsapp') ? __('social-links.open_whatsapp') : __('social-links.visit') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('social-links.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('social-links.empty_description') }}</p>
            </div>
        @endif
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ __('social-links.verified_title') }}</h2>
            <p class="cta-band__desc">{{ __('social-links.verified_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('social-links.direct_contact') }}</a>
            </div>
        </div>
    </section>

@endsection

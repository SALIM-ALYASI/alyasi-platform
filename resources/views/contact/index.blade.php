@extends('layouts.app')

@section('title', __('contact.title') . ' | ' . __('home.brand'))

@section('description', __('contact.hero_description'))

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/pages/css/pages.css') }}"
    >
@endpush

@section('content')

    <section class="page-hero">

        <div class="container">

            <span class="page-hero-badge">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                {{ __('contact.hero_badge') }}
            </span>

            <h1 class="page-hero-title">
                {{ __('contact.hero_title') }}
            </h1>

            <p class="page-hero-description">
                {{ __('contact.hero_description') }}
            </p>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="section-heading section-center reveal">

                <p class="section-tag">{{ __('contact.channels_badge') }}</p>

                <h2 class="section-title">{{ __('contact.channels_title') }}</h2>

                <p class="section-body">{{ __('contact.channels_description') }}</p>

            </div>

            @if ($socialLinks->isNotEmpty())

                <div class="contact-channels-grid">

                    @foreach ($socialLinks as $link)

                        <a
                            href="{{ $link->url }}"
                            target="{{ $link->open_new_tab ? '_blank' : '_self' }}"
                            rel="noopener noreferrer"
                            class="contact-channel-card"
                            style="--platform-color: {{ $link->color ?: '#D0AA6A' }}"
                        >

                            <span class="contact-channel-icon">
                                <i class="{{ $link->icon }}" aria-hidden="true"></i>
                            </span>

                            <strong>{{ $link->name }}</strong>

                            <span>{{ $link->username ?: $link->platform }}</span>

                        </a>

                    @endforeach

                </div>

                <div style="text-align:center;">
                    <a href="{{ route('social-links.index') }}" class="page-view-all-link">
                        {{ __('contact.view_all') }}
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    </a>
                </div>

            @else

                <div class="page-empty-state">
                    <h3>{{ __('contact.empty_title') }}</h3>
                    <p>{{ __('contact.empty_description') }}</p>
                </div>

            @endif

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div class="page-content-block" style="text-align:center; margin:0 auto;">

                <p class="section-tag">{{ __('contact.response_title') }}</p>

                <p>{{ __('contact.response_description') }}</p>

            </div>

        </div>

    </section>

@endsection

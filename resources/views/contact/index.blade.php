@extends('layouts.app')

@section('title', __('contact.title') . ' | ' . __('home.brand'))

@section('description', __('contact.hero_description'))

@push('styles')
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/pages/css/pages.css') }}"
    >
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/pages/css/contact-form.css') }}"
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

    <section class="section" id="contact-form">

        <div class="container">

            <div class="section-heading section-center reveal">

                <p class="section-tag">{{ __('contact.form_badge') }}</p>

                <h2 class="section-title">{{ __('contact.form_title') }}</h2>

                <p class="section-body">{{ __('contact.form_description') }}</p>

            </div>

            @if($contactEmail || $contactPhone)
                <div class="contact-direct-info">

                    @if($contactEmail)
                        <a href="mailto:{{ $contactEmail }}" class="contact-direct-info__item">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <span dir="ltr">{{ $contactEmail }}</span>
                        </a>
                    @endif

                    @if($contactPhone)
                        <a href="tel:{{ $contactPhone }}" class="contact-direct-info__item">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <span dir="ltr">{{ $contactPhone }}</span>
                        </a>
                    @endif

                </div>
            @endif

            @if(session('success'))
                <div class="contact-form__alert contact-form__alert--success">
                    {{ session('success') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('contact.store') }}"
                class="contact-form"
            >
                @csrf

                <div class="contact-form__row">

                    <div class="contact-form__field">
                        <label for="contact-name">{{ __('contact.form_name') }}</label>
                        <input
                            type="text"
                            id="contact-name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                        >
                        @error('name')
                            <span class="contact-form__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="contact-form__field">
                        <label for="contact-email">{{ __('contact.form_email') }}</label>
                        <input
                            type="email"
                            id="contact-email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            maxlength="255"
                        >
                        @error('email')
                            <span class="contact-form__error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="contact-form__row">

                    <div class="contact-form__field">
                        <label for="contact-phone">{{ __('contact.form_phone') }}</label>
                        <input
                            type="tel"
                            id="contact-phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="30"
                        >
                        @error('phone')
                            <span class="contact-form__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="contact-form__field">
                        <label for="contact-subject">{{ __('contact.form_subject') }}</label>
                        <input
                            type="text"
                            id="contact-subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            maxlength="255"
                        >
                        @error('subject')
                            <span class="contact-form__error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="contact-form__field">
                    <label for="contact-message">{{ __('contact.form_message') }}</label>
                    <textarea
                        id="contact-message"
                        name="message"
                        rows="5"
                        required
                        maxlength="3000"
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <span class="contact-form__error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="contact-form__submit">
                    {{ __('contact.form_submit') }}
                </button>

            </form>

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

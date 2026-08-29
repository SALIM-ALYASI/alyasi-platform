@extends('layouts.app')

@section('title', __('contact.title').' — ALYASI')
@section('meta_description', __('contact.hero_description'))
@section('canonical', localized_route('contact'))
@section('hreflang_ar', localized_route('contact', [], 'ar'))
@section('hreflang_en', localized_route('contact', [], 'en'))
@section('og_url', localized_route('contact'))
@section('og_image', asset('images/home/hero.webp'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/contact.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('contact.hero_badge')"
        :title="__('contact.hero_title')"
        :description="__('contact.hero_description')"
    />

    <section id="contact-form" class="container contact-section">
        <div class="contact-grid">
            <div class="contact-form-card" data-reveal>
                <div class="section-head__eyebrow">{{ __('contact.form_badge') }}</div>
                <h2 class="section-head__title contact-form-card__title">{{ __('contact.form_title') }}</h2>
                <p class="contact-form-card__desc">{{ __('contact.form_description') }}</p>

                @if (session('success'))
                    <div class="form-alert form-alert--success">{{ session('success') }}</div>
                @endif
                @error('message')
                    <div class="form-alert form-alert--error">{{ $message }}</div>
                @enderror

                <form method="POST" action="{{ route('contact.store') }}">
                    @csrf

                    {{-- حقل فخ للبوتات — مخفي عن المستخدم الحقيقي بس البوتات اللي
                    تعبّي كل حقل بالنموذج تلقائياً تعبّيه. لو وصل غير فاضي نرفض
                    الطلب بصمت (نظهر نجاح وهمي بدون حفظ) - نفس المنطق بالكنترولر. --}}
                    <div class="field" style="position: absolute; left: -9999px; top: -9999px;" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input type="text" name="website" id="contact-website" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="hidden" name="form_rendered_at" value="{{ now()->timestamp }}">

                    <div class="field">
                        <label class="field__label" for="contact-name">{{ __('contact.form_name') }}</label>
                        <input type="text" name="name" id="contact-name" class="field__control" value="{{ old('name') }}" required maxlength="255">
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="contact-email">{{ __('contact.form_email') }}</label>
                        <input type="email" name="email" id="contact-email" class="field__control" value="{{ old('email') }}" required maxlength="255">
                        @error('email') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="contact-phone">{{ __('contact.form_phone') }}</label>
                        <input type="tel" name="phone" id="contact-phone" class="field__control" value="{{ old('phone') }}" maxlength="30">
                        @error('phone') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="contact-subject">{{ __('contact.form_subject') }}</label>
                        <input type="text" name="subject" id="contact-subject" class="field__control" value="{{ old('subject', request('subject')) }}" maxlength="255">
                        @error('subject') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="contact-message">{{ __('contact.form_message') }}</label>
                        <textarea name="message" id="contact-message" class="field__control" required maxlength="3000">{{ old('message') }}</textarea>
                        @error('message') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">{{ __('contact.form_submit') }}</button>
                </form>
            </div>

            <div class="contact-side" data-reveal>
                <div class="contact-side__card">
                    <div class="section-head__eyebrow">{{ __('contact.response_title') }}</div>
                    <p>{{ __('contact.response_description') }}</p>

                    @if ($contactEmail)
                        <a href="mailto:{{ $contactEmail }}" class="contact-side__link">{{ $contactEmail }}</a>
                    @endif
                    @if ($contactPhone)
                        <a href="tel:{{ $contactPhone }}" class="contact-side__link">{{ $contactPhone }}</a>
                    @endif
                </div>

                @if ($socialLinks->isNotEmpty())
                    <div class="contact-side__card">
                        <div class="section-head__eyebrow">{{ __('contact.channels_badge') }}</div>
                        <h3 class="contact-side__channels-title">{{ __('contact.channels_title') }}</h3>
                        <div class="contact-side__socials">
                            @foreach ($socialLinks as $link)
                                <a href="{{ $link->url }}" @if($link->open_new_tab) target="_blank" rel="noopener" @endif class="contact-side__social">
                                    <i class="{{ $link->icon }}" aria-hidden="true"></i>
                                    <span>{{ $link->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ localized_route('social-links.index') }}" class="contact-side__link">{{ __('contact.view_all') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

@endsection

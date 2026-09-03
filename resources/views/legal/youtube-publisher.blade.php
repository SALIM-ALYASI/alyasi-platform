@extends('layouts.app')

@section('title', __('youtube_publisher.meta_title').' — ALYASI')
@section('meta_description', __('youtube_publisher.meta_description'))
@section('canonical', localized_route('youtube-publisher'))
@section('hreflang_ar', localized_route('youtube-publisher', [], 'ar'))
@section('hreflang_en', localized_route('youtube-publisher', [], 'en'))
@section('og_url', localized_route('youtube-publisher'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/legal.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('youtube_publisher.tagline')"
        :title="__('youtube_publisher.app_name')"
    />

    <section class="container legal-section">

        <p class="legal-intro">{{ __('youtube_publisher.intro') }}</p>

        <div class="legal-body">

            <div class="legal-item">
                <h2>{{ __('youtube_publisher.what_it_does_title') }}</h2>
                <ul style="margin: 0; padding-inline-start: 20px; display: flex; flex-direction: column; gap: 8px;">
                    @foreach (__('youtube_publisher.what_it_does') as $point)
                        <li style="font-size: 14.5px; line-height: 1.9; color: rgba(20, 32, 46, .72);">{{ $point }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="legal-item">
                <h2>{{ __('youtube_publisher.privacy_title') }}</h2>
                <p>{{ __('youtube_publisher.privacy_body') }}</p>
            </div>

        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-top: 40px;">
            <a href="{{ localized_route('privacy') }}" class="btn btn--primary">{{ __('youtube_publisher.privacy_policy_link') }}</a>
            <a href="{{ localized_route('terms') }}" class="btn btn--outline">{{ __('youtube_publisher.terms_link') }}</a>
        </div>

        <div class="legal-notice" style="margin-top: 48px;">
            <strong>{{ __('youtube_publisher.developed_by') }}</strong><br>
            <a href="{{ url('/') }}">alyasi.dev</a><br>
            {{ __('youtube_publisher.support') }}: alyasiforchargers@gmail.com
        </div>

    </section>

@endsection

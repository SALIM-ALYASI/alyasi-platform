@extends('layouts.app')

@section('title', __('legal.terms_title').' — ALYASI')
@section('meta_description', __('legal.terms_intro'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/legal.css') }}">
@endpush

@section('content')

    <x-page-hero :title="__('legal.terms_title')" />

    <section class="container legal-section">
        <p class="legal-updated">{{ __('legal.terms_updated_at', ['date' => now()->translatedFormat('d.m.Y')]) }}</p>
        <p class="legal-intro">{{ __('legal.terms_intro') }}</p>
        <p class="legal-notice">{{ __('legal.legal_notice') }}</p>

        <div class="legal-body">
            @foreach (__('legal.terms_sections') as $section)
                <div class="legal-item">
                    <h2>{{ $section['title'] }}</h2>
                    <p>{{ $section['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

@endsection

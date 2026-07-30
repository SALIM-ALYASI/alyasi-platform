@extends('layouts.app')

@section('title', __('legal.terms_title') . ' | ' . __('home.brand'))

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
                <i class="fa-solid fa-file-contract" aria-hidden="true"></i>
                {{ __('legal.terms_title') }}
            </span>

            <h1 class="page-hero-title">
                {{ __('legal.terms_title') }}
            </h1>

        </div>

    </section>

    <section class="section">

        <div class="container">

            <div style="text-align:center;">
                <span class="legal-updated">
                    {{ __('legal.terms_updated_at', ['date' => now()->translatedFormat('Y/m/d')]) }}
                </span>
            </div>

            <p class="legal-intro">
                {{ __('legal.terms_intro') }}
            </p>

            <div class="legal-sections">

                @foreach (__('legal.terms_sections') as $section)

                    <div class="legal-section">
                        <h2>{{ $section['title'] }}</h2>
                        <p>{{ $section['body'] }}</p>
                    </div>

                @endforeach

            </div>

        </div>

    </section>

@endsection

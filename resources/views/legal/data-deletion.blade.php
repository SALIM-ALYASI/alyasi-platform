@extends('layouts.app')

@section('title', __('data_deletion.meta_title', [], 'ar').' — ALYASI')
@section('meta_description', __('data_deletion.meta_description', [], 'ar'))
@section('canonical', url('/data-deletion'))
@section('og_url', url('/data-deletion'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/legal.css') }}">
@endpush

@section('content')

    <x-page-hero :title="__('data_deletion.title', [], 'ar')" />

    <section class="container legal-section">

        <p class="legal-intro">{{ __('data_deletion.intro', [], 'ar') }}</p>

        <div class="legal-body">

            <div class="legal-item">
                <h2>{{ __('data_deletion.steps_title', [], 'ar') }}</h2>
                <ul style="margin: 0; padding-inline-start: 20px; display: flex; flex-direction: column; gap: 8px;">
                    @foreach (__('data_deletion.steps', [], 'ar') as $step)
                        <li style="font-size: 14.5px; line-height: 1.9; color: rgba(20, 32, 46, .72);">{{ $step }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="legal-item">
                <h2>{{ __('data_deletion.scope_title', [], 'ar') }}</h2>
                <p>{{ __('data_deletion.scope_body', [], 'ar') }}</p>
            </div>

            <div class="legal-item">
                <h2>{{ __('data_deletion.processing_title', [], 'ar') }}</h2>
                <p>{{ __('data_deletion.processing_body', [], 'ar') }}</p>
            </div>

            <div class="legal-item">
                <h2>{{ __('data_deletion.contact_title', [], 'ar') }}</h2>
                <p><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
            </div>

        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-top: 32px;">
            <a href="{{ url('/privacy') }}" class="btn btn--outline">{{ __('data_deletion.privacy_link_text', [], 'ar') }}</a>
        </div>

        <hr style="margin: 56px 0; border: none; border-top: 1px solid rgba(20, 32, 46, .1);">

        <div dir="ltr" style="text-align: left;">

            <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 16px;">
                {{ __('data_deletion.section_title', [], 'en') }}
            </h1>

            <p class="legal-intro">{{ __('data_deletion.intro', [], 'en') }}</p>

            <div class="legal-body">

                <div class="legal-item">
                    <h2>{{ __('data_deletion.steps_title', [], 'en') }}</h2>
                    <ul style="margin: 0; padding-inline-start: 20px; display: flex; flex-direction: column; gap: 8px;">
                        @foreach (__('data_deletion.steps', [], 'en') as $step)
                            <li style="font-size: 14.5px; line-height: 1.9; color: rgba(20, 32, 46, .72);">{{ $step }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="legal-item">
                    <h2>{{ __('data_deletion.scope_title', [], 'en') }}</h2>
                    <p>{{ __('data_deletion.scope_body', [], 'en') }}</p>
                </div>

                <div class="legal-item">
                    <h2>{{ __('data_deletion.processing_title', [], 'en') }}</h2>
                    <p>{{ __('data_deletion.processing_body', [], 'en') }}</p>
                </div>

                <div class="legal-item">
                    <h2>{{ __('data_deletion.contact_title', [], 'en') }}</h2>
                    <p><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
                </div>

            </div>

            <div style="margin-top: 32px;">
                <a href="{{ url('/privacy') }}" class="btn btn--outline">{{ __('data_deletion.privacy_link_text', [], 'en') }}</a>
            </div>

        </div>

    </section>

@endsection

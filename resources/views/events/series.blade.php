@extends('layouts.app')

@php
    $latestEdition = $editions->first();
    $seriesUrl = route(app()->getLocale() === 'en' ? 'event_editions.show.en' : 'event_editions.show', ['slug' => $event->slug]);
@endphp

@section('title', $event->name.' — ALYASI')
@section('meta_description', __('events.series_meta_description', ['name' => $event->name]))
@section('canonical', $seriesUrl)
@section('og_url', $seriesUrl)
@section('og_image', $latestEdition?->image ? media_url($latestEdition->image) : asset('images/events/og-cover.jpg'))
@section('og_image_width', 1200)
@section('og_image_height', 630)

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('events.series_badge')"
        :title="$event->name"
        :description="__('events.series_intro', ['name' => $event->name])"
        :image="$latestEdition?->image ? media_url($latestEdition->image) : asset('images/events/og-cover.jpg')"
        :image-width="1672"
        :image-height="941"
    />

    <section class="container community-section">
        <div class="section-head">
            <div class="section-head__eyebrow">{{ __('events.series_archive') }}</div>
            <h2 class="section-head__title">{{ __('events.all_editions') }}</h2>
        </div>

        <div class="grid-3">
            @foreach ($editions as $edition)
                @php
                    $phase = $edition->phase;
                    $permalink = $edition->permalink(app()->getLocale()) ?? $edition->permalink();
                @endphp

                <a href="{{ $permalink?->url() }}" class="card card--hover community-card" data-reveal>
                    <div class="community-card__media">
                        <img
                            src="{{ $edition->image ? media_url($edition->image) : asset('images/events/og-cover.jpg') }}"
                            alt="{{ $edition->title }}"
                            loading="lazy"
                        >

                        <span class="badge badge--status-{{ ['upcoming' => 'upcoming', 'live' => 'ongoing', 'concluded' => 'ended'][$phase] }} community-card__badge">
                            {{ $phase === 'upcoming' ? __('events.soon') : __('events.phase.'.$phase) }}
                        </span>
                    </div>

                    <div class="community-card__body">
                        <div class="community-card__meta">
                            {{ $edition->year }}
                            @if ($edition->event_start_at)
                                · {{ $edition->event_start_at->copy()->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}
                            @endif
                        </div>

                        <h3 class="community-card__title">{{ $edition->title }}</h3>

                        <p class="community-card__excerpt">
                            {{ $edition->short_description ?: __('events.edition_default_description') }}
                        </p>

                        <span class="community-card__link">{{ __('events.view_coverage') }} ←</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

@endsection

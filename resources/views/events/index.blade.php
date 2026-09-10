@extends('layouts.app')

@section('title', __('events.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('events.meta_description'))
@section('canonical', paginated_canonical(route('events.index')))
@section('og_url', route('events.index'))
@section('og_image', asset('images/events/og-cover.jpg'))
@section('og_image_width', 1200)
@section('og_image_height', 630)

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('events.hero_badge')"
        :title="__('events.hub_title')"
        :description="__('events.hub_description')"
        :image="asset('images/community/hero.webp')"
        :image-width="1672"
        :image-height="941"
    />

    <section class="container community-section">
        @if ($events->isNotEmpty())
            <div class="grid-3">
                @foreach ($events as $event)
                    @php
                        $latestEdition = $event->editions->first();
                        $phase = $latestEdition?->phase;
                    @endphp

                    <a href="{{ route(app()->getLocale() === 'en' ? 'event_editions.show.en' : 'event_editions.show', ['slug' => $event->slug]) }}"
                       class="card card--hover community-card" data-reveal>
                        <div class="community-card__media">
                            <img
                                src="{{ $latestEdition?->image ? media_url($latestEdition->image) : asset('images/events/og-cover.jpg') }}"
                                alt="{{ $event->name }}"
                                loading="lazy"
                            >

                            @if ($phase)
                                <span class="badge badge--status-{{ ['upcoming' => 'upcoming', 'live' => 'ongoing', 'concluded' => 'ended'][$phase] }} community-card__badge">
                                    {{ __('events.phase.'.$phase) }}
                                </span>
                            @endif
                        </div>

                        <div class="community-card__body">
                            <div class="community-card__meta">
                                {{ trans_choice('events.editions_count', $event->editions->count(), ['count' => $event->editions->count()]) }}
                                @if ($latestEdition)
                                    · {{ __('events.latest_year') }} {{ $latestEdition->year }}
                                @endif
                            </div>

                            <h3 class="community-card__title">{{ $event->name }}</h3>

                            <p class="community-card__excerpt">
                                {{ $latestEdition?->short_description ?: __('events.series_default_description') }}
                            </p>

                            <span class="community-card__link">{{ __('events.view_series') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="community-pagination">
                {{ $events->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('events.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('events.empty_description') }}</p>
            </div>
        @endif
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ __('community.cta_title') }}</h2>
            <p class="cta-band__desc">{{ __('community.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('events.contact_us') }}</a>
            </div>
        </div>
    </section>

@endsection

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
        :title="__('events.hero_title').' '.__('events.hero_title_highlight')"
        :description="__('events.hero_description')"
        :image="asset('images/community/hero.webp')"
        :image-width="1672"
        :image-height="941"
    />

    <section class="container community-section">
        @if ($events->isNotEmpty())
            <div class="grid-3">
                @foreach ($events as $event)
                    <a href="{{ route('community.show', $event) }}" class="card card--hover community-card" data-reveal>
                        <div class="community-card__media">
                            <img src="{{ media_url($event->image) }}" alt="{{ $event->title }}" loading="lazy">
                            @if ($event->event_status)
                                <span class="badge badge--status-{{ $event->event_status }} community-card__badge">{{ __('events.status.'.$event->event_status) }}</span>
                            @endif
                        </div>
                        <div class="community-card__body">
                            <div class="community-card__meta">
                                {{ optional(($event->event_start_at ?? $event->published_at)?->copy()->timezone('Asia/Muscat'))->translatedFormat('d.m.Y — H:i') }}
                                @if ($event->location)
                                    · {{ $event->location }}
                                @endif
                            </div>
                            <h3 class="community-card__title">{{ $event->title }}</h3>
                            <p class="community-card__excerpt">{{ $event->short_description }}</p>
                            <span class="community-card__link">{{ __('events.view_details') }} ←</span>
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

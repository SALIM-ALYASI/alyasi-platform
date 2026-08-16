@extends('layouts.app')

@section('title', __('community.title').' — ALYASI')
@section('meta_description', __('community.description'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('community.badge')"
        :title="__('community.title')"
        :description="__('community.description')"
        :image="asset('images/community/hero.webp')"
        :image-width="1672"
        :image-height="941"
    />

    <section class="container community-section">
        @if ($categories->isNotEmpty())
            <div class="filters">
                <a href="{{ route('community.index') }}" class="filters__btn @if(!request('category')) is-active @endif">
                    {{ __('community.all_categories') }}
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('community.index', ['category' => $category->slug]) }}" class="filters__btn @if(request('category') === $category->slug) is-active @endif">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($posts->isNotEmpty())
            <div class="grid-3">
                @foreach ($posts as $post)
                    <a href="{{ route('community.show', $post) }}" class="card card--hover community-card" data-reveal>
                        <div class="community-card__media">
                            <img src="{{ media_url($post->image) }}" alt="{{ $post->title }}" loading="lazy">
                            @if ($post->event_status)
                                <span class="badge badge--status-{{ $post->event_status }} community-card__badge">{{ __('events.status.'.$post->event_status) }}</span>
                            @elseif ($post->category)
                                <span class="badge community-card__badge">{{ $post->category->name }}</span>
                            @endif
                        </div>
                        <div class="community-card__body">
                            <div class="community-card__meta">
                                {{ optional($post->event_start_at ?? $post->published_at)->translatedFormat('d.m.Y') }}
                                @if ($post->location)
                                    · {{ $post->location }}
                                @endif
                            </div>
                            <h3 class="community-card__title">{{ $post->title }}</h3>
                            <p class="community-card__excerpt">{{ $post->short_description }}</p>
                            <span class="community-card__link">{{ __('community.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="community-pagination">
                {{ $posts->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('community.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('community.empty_description') }}</p>
            </div>
        @endif
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ __('community.cta_title') }}</h2>
            <p class="cta-band__desc">{{ __('community.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('community.contact_us') }}</a>
            </div>
        </div>
    </section>

@endsection

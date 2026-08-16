@extends('layouts.app')

@section('title', __('works.meta_title'))
@section('meta_description', __('works.meta_description'))

@section('canonical', localized_route('works.index'))
@section('og_url', localized_route('works.index'))
@section('hreflang_ar', localized_route('works.index', [], 'ar'))
@section('hreflang_en', localized_route('works.index', [], 'en'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/works-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('works.hero.tag')"
        :title="__('works.hero.title')"
        :highlight="__('works.hero.highlight')"
        :description="__('works.hero.description')"
        :image="asset('images/works/hero.webp')"
        :image-width="1832"
        :image-height="859"
    />

    <section class="container works-section">
        <div class="filters">
            <a href="{{ route('works.index') }}" class="filters__btn @if(!request('type')) is-active @endif">
                {{ __('works.filters.all_types') }}
            </a>
            @foreach ($types as $key => $label)
                <a href="{{ route('works.index', ['type' => $key]) }}" class="filters__btn @if(request('type') === $key) is-active @endif">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($works->isNotEmpty())
            <div class="grid-3">
                @foreach ($works as $work)
                    <a href="{{ route('works.show', $work) }}" class="card card--hover works-card" data-reveal>
                        <div class="works-card__media">
                            <img src="{{ media_url($work->cover_image) }}" alt="{{ $work->title }}" loading="lazy">
                        </div>
                        <div class="works-card__body">
                            <span class="badge">{{ $work->type_label }}</span>
                            <h3 class="works-card__title">{{ $work->title }}</h3>
                            <p class="works-card__excerpt">{{ $work->short_description }}</p>
                            @if ($work->technologies->isNotEmpty())
                                <div class="works-card__tags">
                                    @foreach ($work->technologies->take(3) as $tech)
                                        <span class="works-card__tag">{{ $tech->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="works-pagination">
                {{ $works->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('works.grid.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('works.grid.empty_description') }}</p>
                <a href="{{ route('works.index') }}" class="btn btn--outline">{{ __('works.grid.show_all') }}</a>
            </div>
        @endif
    </section>

    <section class="container">
        <div class="cta-band" data-reveal>
            <div class="cta-band__eyebrow">{{ __('works.cta.tag') }}</div>
            <h2 class="cta-band__title">{{ __('works.cta.title') }} <em>{{ __('works.cta.highlight') }}</em></h2>
            <p class="cta-band__desc">{{ __('works.cta.description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ route('contact') }}" class="btn btn--light">{{ __('works.cta.contact') }}</a>
                <a href="{{ route('services.index') }}" class="btn btn--ghost-light">{{ __('works.cta.services') }}</a>
            </div>
        </div>
    </section>

@endsection

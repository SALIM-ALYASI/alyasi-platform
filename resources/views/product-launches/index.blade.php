@extends('layouts.app')

@section('title', __('product_launches.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('product_launches.meta_description'))

@section('canonical', paginated_canonical(route('product-launches.index')))
@section('og_url', route('product-launches.index'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/news-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('product_launches.hero_badge')"
        :title="__('product_launches.hero_title')"
        :highlight="__('product_launches.hero_title_highlight')"
        :description="__('product_launches.hero_description')"
    />

    <section class="container news-section">
        @if ($companies->isNotEmpty())
            <div class="filters">
                <a href="{{ route('product-launches.index') }}" class="filters__btn @if(!$selectedCompany) is-active @endif">
                    {{ __('product_launches.all_companies') }}
                </a>
                @foreach ($companies as $company)
                    <a href="{{ route('product-launches.index', ['company' => $company]) }}" class="filters__btn @if($selectedCompany === $company) is-active @endif">
                        {{ $company }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($launches->isNotEmpty())
            <div class="grid-3">
                @foreach ($launches as $launch)
                    <a href="{{ $launch->link }}" target="_blank" rel="noopener" class="card card--hover news-card" data-reveal>
                        <div class="news-card__body">
                            <div class="news-card__meta">
                                <span class="badge">{{ $launch->company }}</span>
                                @if ($launch->published_at)
                                    <span>{{ $launch->published_at->translatedFormat('d.m.Y') }}</span>
                                @endif
                            </div>
                            <h3 class="news-card__title">{{ $launch->title }}</h3>
                            <span class="news-card__link">{{ __('product_launches.read_source') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="news-pagination">
                {{ $launches->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('product_launches.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('product_launches.empty_description') }}</p>
            </div>
        @endif
    </section>

@endsection

@extends('layouts.app')

@section('title', __('origin_stories.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('origin_stories.meta_description'))

@section('canonical', paginated_canonical(route('origin-stories.index')))
@section('og_url', route('origin-stories.index'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/news-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('origin_stories.hero_badge')"
        :title="__('origin_stories.hero_title')"
        :highlight="__('origin_stories.hero_title_highlight')"
        :description="__('origin_stories.hero_description')"
    />

    <section class="container news-section">
        @if ($stories->isNotEmpty())
            <div class="grid-3">
                @foreach ($stories as $story)
                    @php $slug = $story->slug('ar'); @endphp
                    <a href="{{ $slug ? route('origin-stories.show', ['slug' => $slug]) : route('origin-stories.index') }}" class="card card--hover news-card" data-reveal>
                        @if ($story->featured_image_ar)
                            <div class="news-card__media">
                                <img src="{{ media_url($story->featured_image_ar) }}" alt="{{ $story->title }}" loading="lazy">
                            </div>
                        @endif
                        <div class="news-card__body">
                            <div class="news-card__meta">
                                <span class="badge">{{ __('origin_stories.hero_badge') }}</span>
                                <span>{{ optional($story->published_at)->translatedFormat('d.m.Y') }}</span>
                            </div>
                            <h3 class="news-card__title">{{ $story->title }}</h3>
                            @if ($story->excerpt)
                                <p class="news-card__excerpt">{{ $story->excerpt }}</p>
                            @endif
                            <span class="news-card__link">{{ __('origin_stories.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="news-pagination">
                {{ $stories->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('origin_stories.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('origin_stories.empty_description') }}</p>
            </div>
        @endif
    </section>

@endsection

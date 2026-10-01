@extends('layouts.app')

@section('title', __('tech_history.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('tech_history.meta_description'))

@section('canonical', paginated_canonical(route('tech-history.index')))
@section('og_url', route('tech-history.index'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/articles-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('tech_history.hero_badge')"
        :title="__('tech_history.hero_title')"
        :highlight="__('tech_history.hero_title_highlight')"
        :description="__('tech_history.hero_description')"
        :image="asset('images/tech-history/hero.webp')"
        :image-width="1672"
        :image-height="941"
    />

    <section class="container articles-section">
        @if ($stories->isNotEmpty())
            <div class="grid-3">
                @foreach ($stories as $story)
                    @php $slug = $story->slug('ar'); @endphp
                    <a href="{{ $slug ? route('tech-history.show', ['slug' => $slug]) : route('tech-history.index') }}" class="card card--hover articles-card" data-reveal>
                        @if ($story->featured_image_ar)
                            <div class="articles-card__media">
                                <img src="{{ media_url($story->featured_image_ar) }}" alt="{{ $story->title }}" loading="lazy">
                            </div>
                        @endif
                        <div class="articles-card__body">
                            <div class="articles-card__meta">
                                <span class="badge">{{ __('tech_history.hero_badge') }}</span>
                                <span>{{ optional($story->published_at)->translatedFormat('d.m.Y') }}</span>
                                @if ($story->reading_time)
                                    <span>· {{ __('tech_history.read_time', ['minutes' => $story->reading_time]) }}</span>
                                @endif
                            </div>
                            <h3 class="articles-card__title">{{ $story->title }}</h3>
                            @if ($story->excerpt)
                                <p class="articles-card__excerpt">{{ $story->excerpt }}</p>
                            @endif
                            <span class="articles-card__link">{{ __('tech_history.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="articles-pagination">
                {{ $stories->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('tech_history.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('tech_history.empty_description') }}</p>
            </div>
        @endif
    </section>

@endsection

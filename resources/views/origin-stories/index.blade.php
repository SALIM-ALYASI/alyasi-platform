@extends('layouts.app')

@section('title', __('origin_stories.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('origin_stories.meta_description'))

@section('canonical', paginated_canonical(route('origin-stories.index')))
@section('og_url', route('origin-stories.index'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/origin-stories.css') }}">
@endpush

@section('content')
    <div class="origin-page">
        <div class="container">
            <span class="origin-eyebrow">{{ __('origin_stories.hero_badge') }}</span>

            <h1 class="origin-title">
                {{ __('origin_stories.hero_title') }}
                <em>{{ __('origin_stories.hero_title_highlight') }}</em>
            </h1>
            <p class="origin-desc">{{ __('origin_stories.hero_description') }}</p>

            @if ($stories->isNotEmpty())
                @foreach ($stories as $story)
                    @php $slug = $story->slug('ar'); @endphp
                    <a href="{{ $slug ? route('origin-stories.show', ['slug' => $slug]) : route('origin-stories.index') }}" class="origin-card" data-reveal>
                        @if ($story->featured_image_ar)
                            <div class="origin-card__media">
                                <img src="{{ media_url($story->featured_image_ar) }}" alt="{{ $story->title }}" loading="lazy">
                            </div>
                        @endif
                        <div class="origin-card__body">
                            <div class="origin-card__cat">{{ optional($story->published_at)->translatedFormat('d.m.Y') }}</div>
                            <h2 class="origin-card__title">{{ $story->title }}</h2>
                            @if ($story->excerpt)
                                <p class="origin-card__excerpt">{{ $story->excerpt }}</p>
                            @endif
                            <span class="origin-card__link">{{ __('origin_stories.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach

                <div class="news-pagination">
                    {{ $stories->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state__title">{{ __('origin_stories.empty_title') }}</div>
                    <p class="empty-state__desc">{{ __('origin_stories.empty_description') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

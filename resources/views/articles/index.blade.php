@extends('layouts.app')

@section('title', __('articles.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('articles.meta_description'))

@section('canonical', article_route('index'))
@section('og_url', article_route('index'))
@section('hreflang_ar', article_route('index', [], 'ar'))
@section('hreflang_en', article_route('index', [], 'en'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/articles-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('articles.hero_badge')"
        :title="__('articles.hero_title')"
        :highlight="__('articles.hero_title_highlight')"
        :description="__('articles.hero_description')"
        :image="asset('images/articles/hero.png')"
    />

    <section class="container articles-section">
        @if ($categories->isNotEmpty())
            <div class="filters">
                <a href="{{ article_route('index') }}" class="filters__btn @if(!request('category')) is-active @endif">
                    {{ __('articles.all_categories') }}
                </a>
                @foreach ($categories as $category)
                    <a href="{{ article_route('index', ['category' => $category->slug]) }}" class="filters__btn @if(request('category') === $category->slug) is-active @endif">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($articles->isNotEmpty())
            <div class="grid-3">
                @foreach ($articles as $article)
                    <a href="{{ article_route('show', [$article->slug()]) }}" class="card card--hover articles-card" data-reveal>
                        <div class="articles-card__media">
                            <img src="{{ media_url($article->featured_image) }}" alt="{{ $article->title }}" loading="lazy">
                            @if ($article->is_featured)
                                <span class="badge articles-card__badge">{{ __('articles.featured_badge') }}</span>
                            @endif
                        </div>
                        <div class="articles-card__body">
                            <div class="articles-card__meta">
                                @if ($article->category)
                                    <span class="badge">{{ $article->category->name }}</span>
                                @endif
                                <span>{{ optional($article->published_at)->translatedFormat('d.m.Y') }}</span>
                                <span>· {{ __('articles.minutes_read', ['minutes' => $article->reading_time ?? 1]) }}</span>
                            </div>
                            <h3 class="articles-card__title">{{ $article->title }}</h3>
                            <p class="articles-card__excerpt">{{ $article->excerpt }}</p>
                            <span class="articles-card__link">{{ __('articles.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="articles-pagination">
                {{ $articles->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('articles.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('articles.empty_description') }}</p>
            </div>
        @endif
    </section>

@endsection

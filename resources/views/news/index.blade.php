@extends('layouts.app')

@section('title', __('news.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('news.meta_description'))

@section('canonical', paginated_canonical(localized_route('news.index')))
@section('og_url', localized_route('news.index'))
@section('hreflang_ar', localized_route('news.index', [], 'ar'))
@section('hreflang_en', localized_route('news.index', [], 'en'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/news-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('news.hero_badge')"
        :title="__('news.hero_title')"
        :highlight="__('news.hero_title_highlight')"
        :description="__('news.hero_description')"
        :image="asset('images/news/hero.webp')"
        :image-width="1832"
        :image-height="859"
    />

    <section class="container news-section">
        @if ($categories->isNotEmpty())
            <div class="filters">
                <a href="{{ localized_route('news.index') }}" class="filters__btn @if(!request('category')) is-active @endif">
                    {{ __('news.all_categories') }}
                </a>
                @foreach ($categories as $category)
                    <a href="{{ localized_route('news.index', ['category' => $category->slug]) }}" class="filters__btn @if(request('category') === $category->slug) is-active @endif">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($articles->isNotEmpty())
            <div class="grid-3">
                @foreach ($articles as $article)
                    @php $slug = $article->slug(); @endphp
                    <a href="{{ $slug ? localized_route('news.show', ['slug' => $slug]) : localized_route('news.index') }}" class="card card--hover news-card" data-reveal>
                        <div class="news-card__media">
                            <img src="{{ media_url($article->image) }}" alt="{{ $article->title }}" loading="lazy">
                        </div>
                        <div class="news-card__body">
                            <div class="news-card__meta">
                                @if ($article->category)
                                    <span class="badge">{{ $article->category->name }}</span>
                                @endif
                                <span>{{ optional($article->published_at)->translatedFormat('d.m.Y') }}</span>
                            </div>
                            <h3 class="news-card__title">{{ $article->title }}</h3>
                            <p class="news-card__excerpt">{{ $article->excerpt }}</p>
                            <span class="news-card__link">{{ __('news.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="news-pagination">
                {{ $articles->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('news.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('news.empty_description') }}</p>
            </div>
        @endif
    </section>

@endsection

@extends('layouts.app')

@section('title', __('analysis.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('analysis.meta_description'))

@section('canonical', paginated_canonical(route('analysis.index')))
@section('og_url', route('analysis.index'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/news-index.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/analysis-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('analysis.hero_badge')"
        :title="__('analysis.hero_title')"
        :highlight="__('analysis.hero_title_highlight')"
        :description="__('analysis.hero_description')"
    />

    <section class="container news-section">
        <div class="filters">
            <a href="{{ route('analysis.index') }}" class="filters__btn @if(!$selectedAngle) is-active @endif">
                {{ __('analysis.all_angles') }}
            </a>
            @foreach ($angles as $value => $label)
                <a href="{{ route('analysis.index', ['angle' => $value]) }}" class="filters__btn @if($selectedAngle === $value) is-active @endif">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($articles->isNotEmpty())
            <div class="grid-3">
                @foreach ($articles as $article)
                    @php $slug = $article->slug('ar'); @endphp
                    <a href="{{ $slug ? route('news.show', ['slug' => $slug]).'#analysis' : route('analysis.index') }}" class="card card--hover news-card" data-reveal>
                        <div class="news-card__media">
                            <img src="{{ media_url($article->image) }}" alt="{{ $article->analysis_title_ar ?: $article->title_ar }}" loading="lazy">
                        </div>
                        <div class="news-card__body">
                            <div class="news-card__meta">
                                @if ($article->angle && isset($angles[$article->angle]))
                                    <span class="badge analysis-card__angle">{{ $angles[$article->angle] }}</span>
                                @endif
                                <span>{{ optional($article->published_at)->translatedFormat('d.m.Y') }}</span>
                            </div>
                            <h3 class="news-card__title">{{ $article->analysis_title_ar ?: $article->title_ar }}</h3>
                            <p class="news-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags((string) $article->analysis_ar), 150) }}</p>
                            <span class="news-card__link">{{ __('analysis.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="news-pagination">
                {{ $articles->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('analysis.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('analysis.empty_description') }}</p>
            </div>
        @endif
    </section>

@endsection

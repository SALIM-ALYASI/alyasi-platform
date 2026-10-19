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
            <a href="{{ localized_route('works.index') }}" class="filters__btn @if(!request('type')) is-active @endif">
                {{ __('works.filters.all_types') }}
            </a>
            @foreach ($types as $key => $label)
                <a href="{{ localized_route('works.index', ['type' => $key]) }}" class="filters__btn @if(request('type') === $key) is-active @endif">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($works->isNotEmpty())
            <div class="grid-3">
                @foreach ($works as $work)
                    <a href="{{ localized_route('works.show', ['work' => $work]) }}" class="card card--hover works-card" data-reveal>
                        <div class="works-card__media">
                            <img src="{{ media_url($work->cover_image) }}" alt="{{ $work->localized('title') }}" loading="lazy">
                        </div>
                        <div class="works-card__body">
                            <span class="badge">{{ $work->type_label }}</span>
                            @if ($work->status_label)
                                <span class="badge badge--status-upcoming">{{ $work->status_label }}</span>
                            @endif
                            <h3 class="works-card__title">{{ $work->localized('title') }}</h3>
                            <p class="works-card__excerpt">{{ $work->localized('short_description') }}</p>
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
                <a href="{{ localized_route('works.index') }}" class="btn btn--outline">{{ __('works.grid.show_all') }}</a>
            </div>
        @endif
    </section>

    {{-- تطبيقات App Store المنشورة فعلًا (من حساب المطوّر، تتحدّث تلقائيًا كل أسبوع) --}}
    @php
        \App\Support\AppStore\DeveloperApps::refreshLaterIfStale();
        $featuredApps = \App\Support\AppStore\DeveloperApps::featured();
        $allAppsCount = count(\App\Support\AppStore\DeveloperApps::all());
        $isEn = app()->getLocale() === 'en';
    @endphp
    @if ($featuredApps)
        <section class="container works-apps" id="apps" aria-labelledby="works-apps-title">
            <div class="section-head">
                <div class="section-head__eyebrow">App Store</div>
                <h2 class="section-head__title" id="works-apps-title">{{ __('works.apps.title') }}</h2>
                <p class="section-head__desc">{{ trans_choice('works.apps.description', $allAppsCount, ['count' => $allAppsCount]) }}</p>
            </div>

            <div class="works-apps__grid">
                @foreach ($featuredApps as $app)
                    <a class="works-app" href="{{ $app['url'] }}" target="_blank" rel="noopener" data-reveal>
                        @if ($app['icon'])
                            <img class="works-app__icon" src="{{ versioned_asset($app['icon']) }}" alt="" width="64" height="64" loading="lazy" decoding="async">
                        @endif
                        <span class="works-app__body">
                            <strong dir="auto">{{ $app['name'] }}</strong>
                            <span class="works-app__meta">
                                {{ \App\Support\AppStore\DeveloperApps::genre($app['genre']) }}
                                @if ($app['released']) · {{ __('works.apps.since', ['year' => substr($app['released'], 0, 4)]) }} @endif
                            </span>
                            @if ($app['rating'] && $app['ratings_count'] >= 3)
                                <span class="works-app__rating">★ {{ number_format($app['rating'], 1) }} <small>({{ $app['ratings_count'] }})</small></span>
                            @endif
                        </span>
                        <span class="works-app__store" aria-hidden="true"><i class="fa-brands fa-app-store-ios"></i></span>
                    </a>
                @endforeach
            </div>

            <div class="works-apps__more">
                <a class="btn btn--outline" href="{{ \App\Support\AppStore\DeveloperApps::DEVELOPER_URL }}" target="_blank" rel="noopener">
                    {{ trans_choice('works.apps.all', $allAppsCount, ['count' => $allAppsCount]) }} {{ $isEn ? '→' : '←' }}
                </a>
            </div>
        </section>
    @endif

    <section class="container">
        <div class="cta-band" data-reveal>
            <div class="cta-band__eyebrow">{{ __('works.cta.tag') }}</div>
            <h2 class="cta-band__title">{{ __('works.cta.title') }} <em>{{ __('works.cta.highlight') }}</em></h2>
            <p class="cta-band__desc">{{ __('works.cta.description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('works.cta.contact') }}</a>
                <a href="{{ localized_route('services.index') }}" class="btn btn--ghost-light">{{ __('works.cta.services') }}</a>
            </div>
        </div>
    </section>

@endsection

@extends('layouts.app')

@section('title', __('services.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('services.meta_description'))

@section('canonical', localized_route('services.index'))
@section('og_url', localized_route('services.index'))
@section('hreflang_ar', localized_route('services.index', [], 'ar'))
@section('hreflang_en', localized_route('services.index', [], 'en'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/services-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('services.hero_badge')"
        :title="__('services.hero_title')"
        :highlight="__('services.hero_title_highlight')"
        :description="__('services.hero_description')"
        :image="asset('images/services/hero.webp')"
        :image-width="1832"
        :image-height="859"
    />

    <section class="container services-section">
        <div class="section-head" data-reveal>
            <div class="section-head__eyebrow">{{ __('services.section_badge') }}</div>
            <h2 class="section-head__title">{{ __('services.section_title') }} <em>{{ __('services.section_title_highlight') }}</em></h2>
            <p class="section-head__desc">{{ __('services.section_description') }}</p>
        </div>

        @if ($services->isNotEmpty())
            <div class="grid-3">
                @foreach ($services as $service)
                    @php $slug = $service->slug(); @endphp
                    <div class="card services-card" data-reveal>
                        <div class="services-card__media">
                            <img src="{{ media_url($service->image) }}" @if ($srcset = media_srcset($service->image)) srcset="{{ $srcset }}" sizes="(max-width: 767px) 92vw, 400px" @endif alt="{{ $service->localizedTitle() }}" loading="lazy" decoding="async">
                            <span class="services-card__number">{{ sprintf('%02d', $loop->iteration) }}</span>
                        </div>
                        <div class="services-card__body">
                            <div class="services-card__eyebrow">{{ __('services.digital_service') }}</div>
                            <h3 class="services-card__title">{{ $service->localizedTitle() }}</h3>
                            <p class="services-card__desc">{{ \Illuminate\Support\Str::limit($service->localizedDescription(), 130) }}</p>
                            @if ($service->hasPrice())
                                <a href="#pricing" class="services-card__price">
                                    <span class="services-card__price-label">{{ __('services.pricing.card_label') }}</span>
                                    <strong>{{ $service->priceHtml() }}</strong>
                                </a>
                            @endif
                            <div class="services-card__actions">
                                <a href="{{ $service->requestUrl() }}" class="services-card__link">{{ __('services.request_service') }}</a>
                                <span class="services-card__sep"></span>
                                <a href="{{ $slug ? localized_route('services.show', ['slug' => $slug]) : localized_route('services.index') }}" class="services-card__link services-card__link--muted">{{ __('services.view_details') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="services-pagination">
                {{ $services->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__title">{{ __('services.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('services.empty_description') }}</p>
            </div>
        @endif
    </section>

    @if ($pricedServices->isNotEmpty())
        <section class="container services-pricing" id="pricing">
            <div class="section-head" data-reveal>
                <div class="section-head__eyebrow">{{ __('services.pricing.badge') }}</div>
                <h2 class="section-head__title">{{ __('services.pricing.title') }} <em>{{ __('services.pricing.title_highlight') }}</em></h2>
                <p class="section-head__desc">{{ __('services.pricing.description') }}</p>
            </div>

            <div class="services-pricing__list">
                @foreach ($pricedServices as $service)
                    @php $slug = $service->slug(); @endphp
                    <div class="services-pricing__row" data-reveal>
                        <div class="services-pricing__info">
                            <h3 class="services-pricing__name">
                                <a href="{{ $slug ? localized_route('services.show', ['slug' => $slug]) : localized_route('services.index') }}">{{ $service->localizedTitle() }}</a>
                            </h3>
                            @if ($note = $service->localizedPriceNote())
                                <p class="services-pricing__note">{{ omr_text($note) }}</p>
                            @endif
                        </div>
                        <div class="services-pricing__price">
                            <strong>{{ $service->priceHtml() }}</strong>
                            @if ($unit = $service->localizedPriceUnit())
                                <span>{{ omr_text($unit) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="services-pricing__disclaimer">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                {{ __('services.pricing.disclaimer') }}
            </p>

            <div class="services-pricing__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--outline">{{ __('services.pricing.request') }}</a>
            </div>
        </section>
    @endif

    <section class="container">
        <div class="cta-band" data-reveal>
            <div class="cta-band__eyebrow">{{ __('services.cta_badge') }}</div>
            <h2 class="cta-band__title">{{ __('services.cta_title') }} <em>{{ __('services.cta_title_highlight') }}</em></h2>
            <p class="cta-band__desc">{{ __('services.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('services.contact_us') }}</a>
                <a href="{{ localized_route('home') }}" class="btn btn--ghost-light">{{ __('services.back_home') }}</a>
            </div>
        </div>
    </section>

@endsection

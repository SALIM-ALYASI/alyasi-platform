@extends('layouts.app')

@section('title', $service->localizedTitle().' — ALYASI')
@section('meta_description', \Illuminate\Support\Str::limit($service->localizedDescription(), 160))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/services-show.css') }}">
@endpush

@section('content')

    <section class="container service-detail__hero-wrap">
        <div class="service-detail__hero-media">
            <img src="{{ media_url($service->image) }}" alt="{{ $service->localizedTitle() }}">
        </div>
    </section>

    <section class="container service-detail__body">
        <span class="badge">{{ __('services.digital_service') }}</span>
        <h1 class="service-detail__title">{{ $service->localizedTitle() }}</h1>
        <p class="service-detail__desc">{{ $service->localizedDescription() }}</p>

        <div class="section-head service-detail__why-head">
            <div class="section-head__eyebrow">{{ __('services.show.hero_badge') }}</div>
            <h2 class="section-head__title">{{ __('services.show.why_choose') }} <em>ALYASI</em></h2>
            <p class="section-head__desc">{{ __('services.show.description') }}</p>
        </div>

        <div class="grid-3 service-detail__why-grid">
            <div class="service-detail__why-item">
                <div class="service-detail__why-label">{{ __('services.show.quality_label') }}</div>
                <h3>{{ __('services.show.quality_title') }}</h3>
                <p>{{ __('services.show.quality_description') }}</p>
            </div>
            <div class="service-detail__why-item">
                <div class="service-detail__why-label">{{ __('services.show.performance_label') }}</div>
                <h3>{{ __('services.show.performance_title') }}</h3>
                <p>{{ __('services.show.performance_description') }}</p>
            </div>
            <div class="service-detail__why-item">
                <div class="service-detail__why-label">{{ __('services.show.support_label') }}</div>
                <h3>{{ __('services.show.support_title') }}</h3>
                <p>{{ __('services.show.support_description') }}</p>
            </div>
        </div>

        <div class="cta-band service-detail__cta">
            <h3 class="service-detail__cta-title">{{ __('services.show.cta_title') }} <em>{{ __('services.show.cta_title_highlight') }}</em></h3>
            <p class="cta-band__desc">{{ __('services.show.cta_description') }}</p>
            <a href="{{ route('contact') }}" class="btn btn--light">{{ __('services.show.request_service') }}</a>
        </div>
    </section>

    @if ($service->works->isNotEmpty())
        <section class="service-detail__related">
            <div class="container">
                <div class="section-head__eyebrow">{{ __('services.show.related_works_badge') }}</div>
                <h2 class="section-head__title service-detail__related-title">{{ __('services.show.related_works_title') }}</h2>

                <div class="grid-3 service-detail__related-grid">
                    @foreach ($service->works as $work)
                        <a href="{{ route('works.show', $work) }}" class="card card--hover service-detail__work-card">
                            <div class="service-detail__work-media">
                                <img src="{{ media_url($work->cover_image) }}" alt="{{ $work->title }}" loading="lazy">
                                @if ($work->is_featured)
                                    <span class="badge service-detail__work-badge">{{ __('services.show.related_works_featured') }}</span>
                                @endif
                            </div>
                            <div class="service-detail__work-body">
                                <div class="service-detail__work-tag">{{ $work->type_label }}</div>
                                <div class="service-detail__work-title">{{ $work->title }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="reviews" class="container service-detail__reviews">
        <x-reviews :reviews="$service->approvedReviews" action="{{ route('services.reviews.store', $service) }}" />
    </section>

@endsection

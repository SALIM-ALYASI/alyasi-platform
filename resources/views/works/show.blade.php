@extends('layouts.app')

@section('title', $work->title.' — ALYASI')
@section('meta_description', \Illuminate\Support\Str::limit($work->short_description, 160))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/works-show.css') }}">
@endpush

@section('content')

    <section class="container work-detail__hero-wrap">
        <div class="work-detail__hero-media">
            <img src="{{ media_url($work->cover_image) }}" alt="{{ $work->title }}">
        </div>
    </section>

    <section class="container work-detail__body">
        <span class="badge">{{ $work->type_label }}</span>
        <h1 class="work-detail__title">{{ $work->title }}</h1>

        <div class="grid-3 work-detail__info-grid">
            <div class="work-detail__info-card">
                <div class="work-detail__info-label">{{ __('works.show.client') }}</div>
                <div class="work-detail__info-value">{{ $work->client_name ?: '—' }}</div>
            </div>
            <div class="work-detail__info-card">
                <div class="work-detail__info-label">{{ __('works.show.completion_date') }}</div>
                <div class="work-detail__info-value">{{ optional($work->completed_at)->translatedFormat('d.m.Y') ?: '—' }}</div>
            </div>
            <div class="work-detail__info-card">
                <div class="work-detail__info-label">{{ __('works.show.work_type') }}</div>
                <div class="work-detail__info-value">{{ $work->type_label }}</div>
            </div>
        </div>

        <p class="work-detail__desc">{{ $work->description ?: __('works.show.no_description') }}</p>

        @if ($work->technologies->isNotEmpty())
            <h3 class="work-detail__section-title">{{ __('works.show.technologies') }}</h3>
            <div class="work-detail__tags">
                @foreach ($work->technologies as $tech)
                    <span class="work-detail__tag">{{ $tech->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($work->images->isNotEmpty())
            <h3 class="work-detail__section-title">{{ __('works.show.gallery') }}</h3>
            <div class="grid-3 work-detail__gallery">
                @foreach ($work->images as $image)
                    <div class="work-detail__gallery-item">
                        <img src="{{ media_url($image->image) }}" alt="{{ $image->alt_text ?: $work->title }}" loading="lazy">
                    </div>
                @endforeach
            </div>
        @endif

        @if ($work->work_url)
            <a href="{{ $work->work_url }}" target="_blank" rel="noopener" class="btn btn--primary work-detail__visit">{{ __('works.show.visit_project') }}</a>
        @endif
    </section>

    @if ($relatedWorks->isNotEmpty())
        <section class="work-detail__related">
            <div class="container">
                <div class="section-head__eyebrow">{{ __('works.show.related_tag') }}</div>
                <h2 class="section-head__title work-detail__related-title">{{ __('works.show.related_title') }}</h2>

                <div class="grid-3">
                    @foreach ($relatedWorks as $related)
                        <a href="{{ route('works.show', $related) }}" class="card card--hover work-detail__related-card">
                            <div class="work-detail__related-media">
                                <img src="{{ media_url($related->cover_image) }}" alt="{{ $related->title }}" loading="lazy">
                            </div>
                            <div class="work-detail__related-body">
                                <div class="work-detail__related-tag">{{ $related->type_label }}</div>
                                <div class="work-detail__related-title-text">{{ $related->title }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="reviews" class="container work-detail__reviews">
        <x-reviews :reviews="$work->approvedReviews" action="{{ route('works.reviews.store', $work) }}" />
    </section>

@endsection

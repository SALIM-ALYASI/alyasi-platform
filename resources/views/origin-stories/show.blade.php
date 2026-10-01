@extends('layouts.app')

@section('title', ($article->meta_title_ar ?: $article->title).' — ALYASI')
@section('meta_description', $article->meta_description_ar ?: \Illuminate\Support\Str::limit(strip_tags($article->content), 160))

@php
    $arPermalink = $article->permalinks->firstWhere('locale', 'ar');
@endphp

@section('canonical', $arPermalink ? $arPermalink->url() : url()->current())
@section('og_type', 'article')
@section('og_title', $article->meta_title_ar ?: $article->title)
@section('og_description', $article->meta_description_ar ?: \Illuminate\Support\Str::limit(strip_tags($article->content), 160))
@section('og_url', url()->current())
@if ($article->featured_image_ar)
    @section('og_image', media_url($article->featured_image_ar))
@endif

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/news-show.css') }}">
@endpush

@section('content')

    @if ($article->featured_image_ar)
        <section class="container news-detail__media-wrap">
            <div class="news-detail__media">
                <img src="{{ media_url($article->featured_image_ar) }}" alt="{{ $article->title }}">
            </div>
        </section>
    @endif

    <section class="container news-detail__header-after-image">
        <div class="news-detail__breadcrumb">
            <a href="{{ route('origin-stories.index') }}">{{ __('origin_stories.hero_badge') }}</a>
        </div>

        <div class="news-detail__meta">
            <span class="badge">{{ __('origin_stories.hero_badge') }}</span>
            <span class="news-detail__date">
                {{ optional($article->published_at)->translatedFormat('d.m.Y') }}
                @if ($article->reading_time)
                    &middot; {{ __('origin_stories.read_time', ['minutes' => $article->reading_time]) }}
                @endif
            </span>
        </div>

        <h1 class="news-detail__title">{{ $article->title }}</h1>
    </section>

    <section class="container news-detail__content-wrap">
        <div class="news-detail__content">
            {!! $article->content !!}
        </div>
    </section>

    @if ($otherStories->isNotEmpty())
        <section class="container news-section">
            <h2 class="section-head__title">{{ __('origin_stories.other_stories') }}</h2>
            <div class="grid-3">
                @foreach ($otherStories as $other)
                    @php $otherSlug = $other->slug('ar'); @endphp
                    <a href="{{ $otherSlug ? route('origin-stories.show', ['slug' => $otherSlug]) : route('origin-stories.index') }}" class="card card--hover news-card" data-reveal>
                        @if ($other->featured_image_ar)
                            <div class="news-card__media">
                                <img src="{{ media_url($other->featured_image_ar) }}" alt="{{ $other->title }}" loading="lazy">
                            </div>
                        @endif
                        <div class="news-card__body">
                            <div class="news-card__meta">
                                <span>{{ optional($other->published_at)->translatedFormat('d.m.Y') }}</span>
                            </div>
                            <h3 class="news-card__title">{{ $other->title }}</h3>
                            <span class="news-card__link">{{ __('origin_stories.read_more') }} ←</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

@endsection

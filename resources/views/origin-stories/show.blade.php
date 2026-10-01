@extends('layouts.app')

@section('title', ($article->meta_title_ar ?: $article->title).' — ALYASI')
@section('meta_description', $article->meta_description_ar ?: $article->excerpt)

@php
    $arPermalink = $article->permalinks->firstWhere('locale', 'ar');
@endphp

@section('canonical', $arPermalink ? $arPermalink->url() : url()->current())
@section('og_type', 'article')
@section('og_title', $article->meta_title_ar ?: $article->title)
@section('og_description', $article->meta_description_ar ?: $article->excerpt)
@section('og_url', url()->current())
@if ($article->featured_image_ar)
    @section('og_image', media_url($article->featured_image_ar))
@endif

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/origin-stories.css') }}">
@endpush

@section('content')
    <div class="origin-page">
        <div class="container">
            <a href="{{ route('origin-stories.index') }}" class="origin-back">→ {{ __('origin_stories.back_to_index') }}</a>

            <span class="origin-eyebrow">{{ __('origin_stories.hero_badge') }}</span>

            <h1 class="origin-show__title">{{ $article->title }}</h1>
            <div class="origin-show__meta">
                {{ optional($article->published_at)->translatedFormat('d F Y') }}
                @if ($article->reading_time)
                    &middot; {{ __('origin_stories.read_time', ['minutes' => $article->reading_time]) }}
                @endif
            </div>

            @if ($article->featured_image_ar)
                <div class="origin-show__media">
                    <img src="{{ media_url($article->featured_image_ar) }}" alt="{{ $article->title }}">
                </div>
            @endif

            <div class="origin-content">
                {!! $article->content !!}
            </div>

            @if ($otherStories->isNotEmpty())
                <div class="origin-others">
                    <h3>{{ __('origin_stories.other_stories') }}</h3>
                    @foreach ($otherStories as $other)
                        @php $otherSlug = $other->slug('ar'); @endphp
                        <a href="{{ $otherSlug ? route('origin-stories.show', ['slug' => $otherSlug]) : route('origin-stories.index') }}" class="origin-card" data-reveal>
                            <div class="origin-card__body">
                                <div class="origin-card__cat">{{ optional($other->published_at)->translatedFormat('d.m.Y') }}</div>
                                <h2 class="origin-card__title">{{ $other->title }}</h2>
                                <span class="origin-card__link">{{ __('origin_stories.read_more') }} ←</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

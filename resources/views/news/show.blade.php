@extends('layouts.app')

@section('title', $article->seo_title.' — ALYASI')
@section('meta_description', $article->seo_description)

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/news-show.css') }}">
@endpush

@section('content')

@php
    $allowedTags = '<p><br><strong><em><b><i><a><ul><ol><li><h2><h3><h4><blockquote>';
    $articleContent = trim((string) $article->content);
    $hasHtmlMarkup = $articleContent !== strip_tags($articleContent);
@endphp

    <section class="container news-detail__top">
        <div class="news-detail__breadcrumb">
            <a href="{{ route('news.index') }}">{{ __('news.breadcrumb_news') }}</a>
            @if ($article->category)
                <span>/</span>
                <span>{{ $article->category->name }}</span>
            @endif
        </div>

        <div class="news-detail__meta">
            @if ($article->category)
                <span class="badge">{{ $article->category->name }}</span>
            @endif
            <span class="news-detail__date">{{ optional($article->published_at)->translatedFormat('d.m.Y') }}</span>
        </div>

        <h1 class="news-detail__title">{{ $article->title }}</h1>
    </section>

    <section class="container news-detail__media-wrap">
        <div class="news-detail__media">
            <img src="{{ media_url($article->image) }}" alt="{{ $article->image_alt }}">
        </div>
    </section>

    <section class="container news-detail__content-wrap">
        @if ($hasHtmlMarkup)
            <div class="news-detail__content">{!! strip_tags($articleContent, $allowedTags) !!}</div>
        @else
            @foreach (collect(preg_split('/\r?\n+/', $articleContent))->filter() as $paragraph)
                <p class="news-detail__paragraph">{{ $paragraph }}</p>
            @endforeach
        @endif

        <div class="news-detail__share">
            <span class="news-detail__share-label">{{ __('news.share') }}:</span>
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}" target="_blank" rel="noopener" class="news-detail__share-btn">X</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="news-detail__share-btn">IN</a>
            <a href="https://wa.me/?text={{ urlencode($article->title.' '.url()->current()) }}" target="_blank" rel="noopener" class="news-detail__share-btn">WA</a>
        </div>
    </section>

    @if ($relatedArticles->isNotEmpty())
        <section class="news-detail__related">
            <div class="container">
                <div class="section-head__eyebrow">{{ __('news.related_badge') }}</div>
                <h2 class="section-head__title news-detail__related-title">{{ __('news.related_title') }}</h2>

                <div class="grid-3">
                    @foreach ($relatedArticles as $related)
                        @php $relatedSlug = $related->slug(); @endphp
                        <a href="{{ $relatedSlug ? route('news.show', $relatedSlug) : route('news.index') }}" class="card card--hover news-detail__related-card">
                            <div class="news-detail__related-media">
                                <img src="{{ media_url($related->image) }}" alt="{{ $related->title }}" loading="lazy">
                            </div>
                            <div class="news-detail__related-body">
                                <div class="news-detail__related-date">{{ optional($related->published_at)->translatedFormat('d.m.Y') }}</div>
                                <div class="news-detail__related-title-text">{{ $related->title }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection

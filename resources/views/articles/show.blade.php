@extends('layouts.app')

@section('title', ($article->meta_title ?: $article->title).' — ALYASI')

@section(
    'meta_description',
    $article->meta_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->excerpt ?: $article->content),
            160
        )
)

{{-- =========================================================
     SEO / Canonical
========================================================= --}}
@section('canonical', route('articles.show', $article))

{{-- =========================================================
     Open Graph
========================================================= --}}
@section('og_type', 'article')

@section(
    'og_title',
    $article->meta_title ?: $article->title
)

@section(
    'og_description',
    $article->meta_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->excerpt ?: $article->content),
            160
        )
)

@section('og_url', route('articles.show', $article))

@section(
    'og_image',
    media_url($article->featured_image)
)

{{-- =========================================================
     Article Schema (JSON-LD)
========================================================= --}}
@section('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',

    'headline' => $article->title,

    'description' => $article->meta_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->excerpt ?: $article->content),
            160
        ),

    'image' => [
        media_url($article->featured_image)
    ],

    'datePublished' => optional(
        $article->published_at
    )->toIso8601String(),

    'dateModified' => optional(
        $article->updated_at
    )->toIso8601String(),

    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('articles.show', $article),
    ],

    'publisher' => [
        '@type' => 'Organization',
        'name' => 'ALYASI',
        'url' => url('/'),
    ],

], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

{{-- =========================================================
     Page Styles
========================================================= --}}
@push('styles')
    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/pages/articles-show.css') }}"
    >
@endpush

@section('content')

@php
    $allowedTags = '<p><br><strong><em><b><i><a><ul><ol><li><h2><h3><h4><blockquote>';

    $articleContent = trim(
        (string) $article->content
    );

    $hasHtmlMarkup =
        $articleContent !== strip_tags($articleContent);
@endphp

{{-- =========================================================
     Article Header
========================================================= --}}
<section class="container articles-detail__top">

    <div class="articles-detail__breadcrumb">

        <a href="{{ route('articles.index') }}">
            {{ __('articles.breadcrumb_articles') }}
        </a>

        @if ($article->category)
            <span>/</span>
            <span>{{ $article->category->name }}</span>
        @endif

    </div>

    <div class="articles-detail__meta">

        @if ($article->category)
            <span class="badge">
                {{ $article->category->name }}
            </span>
        @endif

        <span class="articles-detail__date">

            {{ optional($article->published_at)->translatedFormat('d.m.Y') }}

            ·

            {{ __('articles.minutes_read', [
                'minutes' => $article->reading_time ?? 1
            ]) }}

        </span>

    </div>

    <h1 class="articles-detail__title">
        {{ $article->title }}
    </h1>

</section>

{{-- =========================================================
     Featured Image
========================================================= --}}
<section class="container articles-detail__media-wrap">

    <div class="articles-detail__media">

        <img
            src="{{ media_url($article->featured_image) }}"
            alt="{{ $article->title }}"
        >

    </div>

</section>

{{-- =========================================================
     Article Content
========================================================= --}}
<section class="container articles-detail__content-wrap">

    @if ($hasHtmlMarkup)

        <div class="articles-detail__content">
            {!! strip_tags(
                $articleContent,
                $allowedTags
            ) !!}
        </div>

    @else

        @foreach (
            collect(
                preg_split(
                    '/\r?\n+/',
                    $articleContent
                )
            )->filter()
            as $paragraph
        )

            <p class="articles-detail__paragraph">
                {{ $paragraph }}
            </p>

        @endforeach

    @endif

    {{-- =====================================================
         Share Buttons
    ====================================================== --}}
    <div class="articles-detail__share">

        <span class="articles-detail__share-label">
            {{ __('articles.share') }}:
        </span>

        {{-- X / Twitter --}}
        <a
            href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
        >
            X
        </a>

        {{-- LinkedIn --}}
        <a
            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
        >
            IN
        </a>

        {{-- WhatsApp --}}
        <a
            href="https://wa.me/?text={{ urlencode($article->title.' '.url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
        >
            WA
        </a>

        {{-- Facebook --}}
        <a
            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
        >
            FB
        </a>

    </div>

</section>

{{-- =========================================================
     Related Articles
========================================================= --}}
@if ($relatedArticles->isNotEmpty())

    <section class="articles-detail__related">

        <div class="container">

            <div class="section-head__eyebrow">
                {{ __('articles.related_badge') }}
            </div>

            <h2 class="section-head__title articles-detail__related-title">
                {{ __('articles.related_title') }}
            </h2>

            <div class="grid-3">

                @foreach ($relatedArticles as $related)

                    <a
                        href="{{ route('articles.show', $related) }}"
                        class="card card--hover articles-detail__related-card"
                    >

                        <div class="articles-detail__related-media">

                            <img
                                src="{{ media_url($related->featured_image) }}"
                                alt="{{ $related->title }}"
                                loading="lazy"
                            >

                        </div>

                        <div class="articles-detail__related-body">

                            <div class="articles-detail__related-date">

                                {{ optional(
                                    $related->published_at
                                )->translatedFormat('d.m.Y') }}

                            </div>

                            <div class="articles-detail__related-title-text">
                                {{ $related->title }}
                            </div>

                        </div>

                    </a>

                @endforeach

            </div>

        </div>

    </section>

@endif

@endsection
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
@section('canonical', article_route('show', [$article->slug()]))

@section('hreflang_ar', $article->translatedSlug('ar') ? article_route('show', [$article->translatedSlug('ar')], 'ar') : '')
@section('hreflang_en', $article->translatedSlug('en') ? article_route('show', [$article->translatedSlug('en')], 'en') : '')

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

@section('og_url', article_route('show', [$article->slug()]))

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
    '@'.'context' => 'https://schema.org',
    '@type' => 'Article',

    'inLanguage' => app()->getLocale(),

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
        '@id' => article_route('show', [$article->slug()]),
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

    /*
    |--------------------------------------------------------------------------
    | Allowed HTML inside article content
    |--------------------------------------------------------------------------
    */

    $allowedTags = '
        <p>
        <br>
        <strong>
        <em>
        <b>
        <i>
        <a>
        <ul>
        <ol>
        <li>
        <h2>
        <h3>
        <h4>
        <blockquote>
        <hr>
    ';

    $articleContent = trim(
        (string) $article->content
    );

    $hasHtmlMarkup =
        $articleContent !== strip_tags($articleContent);

@endphp


{{-- =========================================================
     Featured Image
     الصورة تظهر أولاً بعد Header الموقع
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
     Article Header
     العنوان والمعلومات أصبحت تحت الصورة
========================================================= --}}
<section class="container articles-detail__header-after-image">

    <div class="articles-detail__breadcrumb">

        <a href="{{ article_route('index') }}">
            {{ __('articles.breadcrumb_articles') }}
        </a>

        @if ($article->category)

            <span>/</span>

            <span>
                {{ $article->category->name }}
            </span>

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
     Article Content
========================================================= --}}
<section class="container articles-detail__content-wrap">

    @if ($hasHtmlMarkup)

        <div class="articles-detail__content">

            {!! render_rich_content($articleContent, $allowedTags) !!}

        </div>

    @else

        <div class="articles-detail__content">

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

        </div>

    @endif


 {{-- =====================================================
     Share Buttons
====================================================== --}}
<div class="articles-detail__share">

    <span class="articles-detail__share-label">
        {{ __('articles.share') }}:
    </span>

    {{-- X --}}
    <a
        href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
        target="_blank"
        rel="noopener"
        class="articles-detail__share-btn"
        aria-label="مشاركة على X"
        title="X"
    >
        <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
    </a>

    {{-- LinkedIn --}}
    <a
        href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
        target="_blank"
        rel="noopener"
        class="articles-detail__share-btn"
        aria-label="مشاركة على LinkedIn"
        title="LinkedIn"
    >
        <i class="fa-brands fa-linkedin-in" aria-hidden="true"></i>
    </a>

    {{-- WhatsApp --}}
    <a
        href="https://wa.me/?text={{ urlencode($article->title.' '.url()->current()) }}"
        target="_blank"
        rel="noopener"
        class="articles-detail__share-btn"
        aria-label="مشاركة على WhatsApp"
        title="WhatsApp"
    >
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>

    {{-- Facebook --}}
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
        target="_blank"
        rel="noopener"
        class="articles-detail__share-btn"
        aria-label="مشاركة على Facebook"
        title="Facebook"
    >
        <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
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
                    href="{{ article_route('show', [$related->slug()]) }}"
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
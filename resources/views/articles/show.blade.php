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

<style>

    /* ========================================================
       إصلاح بداية صفحة تفاصيل المقال
    ======================================================== */

    .articles-detail__media-wrap {
        padding-top: 32px;
    }

    .articles-detail__header-after-image {
        max-width: 820px;
        margin: 32px auto 0;
    }

    .articles-detail__header-after-image .articles-detail__breadcrumb {
        margin-bottom: 16px;
    }

    .articles-detail__header-after-image .articles-detail__meta {
        margin-bottom: 14px;
    }

    .articles-detail__header-after-image .articles-detail__title {
        margin-top: 0;
        margin-bottom: 0;
    }


    /* ========================================================
       Article Button
    ======================================================== */

    .articles-detail__content a.article-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;

        width: auto;
        min-height: 46px;

        margin: 10px 0;
        padding: 11px 22px;

        background: #0B1F3A;
        color: #ffffff !important;

        border: 1px solid #0B1F3A;
        border-radius: 10px;

        font-family: inherit;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.5;

        text-decoration: none !important;

        cursor: pointer;

        box-shadow:
            0 4px 12px rgba(11, 31, 58, 0.12);

        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            background-color 0.2s ease,
            border-color 0.2s ease;
    }

    .articles-detail__content a.article-btn:hover {
        background: #16375f;
        border-color: #16375f;
        color: #ffffff !important;

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(11, 31, 58, 0.20);
    }

    .articles-detail__content a.article-btn:focus-visible {
        outline: 3px solid rgba(11, 31, 58, 0.25);
        outline-offset: 3px;
    }


    /* ========================================================
       YouTube Button
    ======================================================== */

    .articles-detail__content a.article-btn--youtube {
        background: #ffffff;
        color: #0B1F3A !important;

        border-color: #d9dee7;

        box-shadow:
            0 4px 12px rgba(11, 31, 58, 0.08);
    }

    .articles-detail__content a.article-btn--youtube:hover {
        background: #f5f7fa;
        color: #0B1F3A !important;
        border-color: #c8d0dc;
    }


    /* ========================================================
       Article Separator
    ======================================================== */

    .articles-detail__content hr {
        border: 0;
        border-top: 1px solid #e3e7ed;
        margin: 32px 0;
    }


    /* ========================================================
       Mobile
    ======================================================== */

    @media (max-width: 640px) {

        .articles-detail__media-wrap {
            padding-top: 20px;
        }

        .articles-detail__header-after-image {
            margin-top: 24px;
        }

        .articles-detail__content a.article-btn {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 18px;
        }

    }

</style>

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

        <a href="{{ route('articles.index') }}">
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

            {!! strip_tags(
                $articleContent,
                $allowedTags
            ) !!}

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
            aria-label="X"
        >
            X
        </a>


        {{-- LinkedIn --}}
        <a
            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
            aria-label="LinkedIn"
        >
            IN
        </a>


        {{-- WhatsApp --}}
        <a
            href="https://wa.me/?text={{ urlencode($article->title.' '.url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
            aria-label="WhatsApp"
        >
            WA
        </a>


        {{-- Facebook --}}
        <a
            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="articles-detail__share-btn"
            aria-label="Facebook"
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
@extends('layouts.app')

@section('title', ($article->seo_title ?: $article->title).' — ALYASI')

@section(
    'meta_description',
    $article->seo_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->content),
            160
        )
)

@php
    $arNewsPermalink = $article->permalinks->firstWhere('locale', 'ar');
    $enNewsPermalink = $article->permalinks->firstWhere('locale', 'en');
@endphp

{{-- =========================================================
     SEO / Canonical
========================================================= --}}
@section('canonical', url()->current())

@section('hreflang_ar', $arNewsPermalink ? route('news.show', $arNewsPermalink->slug) : '')
@section('hreflang_en', $enNewsPermalink ? localized_route('news.show', ['slug' => $enNewsPermalink->slug], 'en') : '')

{{-- =========================================================
     Open Graph
========================================================= --}}
@section('og_type', 'article')

@section(
    'og_title',
    $article->seo_title ?: $article->title
)

@section(
    'og_description',
    $article->seo_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->content),
            160
        )
)

@section('og_url', url()->current())

@section(
    'og_image',
    media_url($article->image)
)

{{-- =========================================================
     NewsArticle Schema
========================================================= --}}
@section('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',

    'inLanguage' => app()->getLocale(),

    'headline' => $article->title,

    'description' => $article->seo_description
        ?: \Illuminate\Support\Str::limit(
            strip_tags($article->content),
            160
        ),

    'image' => [
        media_url($article->image)
    ],

    'datePublished' => optional(
        $article->published_at
    )->toIso8601String(),

    'dateModified' => optional(
        $article->updated_at
    )->toIso8601String(),

    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => url()->current(),
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
     Styles
========================================================= --}}
@push('styles')

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/pages/news-show.css') }}"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | News Detail Header Fix
        |--------------------------------------------------------------------------
        | الصورة تصبح أول عنصر بعد Header الموقع.
        */

        .news-detail__media-wrap {
            padding-top: 32px;
        }

        .news-detail__media {
            position: relative;
            overflow: hidden;
        }

        .news-detail__media img {
            display: block;
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | Header after image
        |--------------------------------------------------------------------------
        */

        .news-detail__header-after-image {
            max-width: 820px;
            margin: 32px auto 0;
        }

        .news-detail__header-after-image .news-detail__breadcrumb {
            margin-bottom: 16px;
        }

        .news-detail__header-after-image .news-detail__meta {
            margin-bottom: 14px;
        }

        .news-detail__header-after-image .news-detail__title {
            margin-top: 0;
            margin-bottom: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 640px) {

            .news-detail__media-wrap {
                padding-top: 20px;
            }

            .news-detail__header-after-image {
                margin-top: 24px;
            }

        }

    </style>

@endpush


@section('content')

@php

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
     أول عنصر بعد Header الموقع
========================================================= --}}
<section class="container news-detail__media-wrap">

    <div class="news-detail__media">

        <img
            src="{{ media_url($article->image) }}"
            alt="{{ $article->image_alt ?: $article->title }}"
        >

    </div>

</section>


{{-- =========================================================
     News Header
     العنوان والمعلومات تحت الصورة
========================================================= --}}
<section class="container news-detail__header-after-image">

    {{-- Breadcrumb --}}
    <div class="news-detail__breadcrumb">

        <a href="{{ route('news.index') }}">
            {{ __('news.breadcrumb_news') }}
        </a>

        @if ($article->category)

            <span>/</span>

            <span>
                {{ $article->category->name }}
            </span>

        @endif

    </div>


    {{-- Meta --}}
    <div class="news-detail__meta">

        @if ($article->category)

            <span class="badge">
                {{ $article->category->name }}
            </span>

        @endif

        <span class="news-detail__date">

            {{ optional(
                $article->published_at
            )->translatedFormat('d.m.Y') }}

        </span>

    </div>


    {{-- H1 --}}
    <h1 class="news-detail__title">
        {{ $article->title }}
    </h1>

</section>


{{-- =========================================================
     News Content
========================================================= --}}
<section class="container news-detail__content-wrap">

    @if ($hasHtmlMarkup)

        <div class="news-detail__content">

            {!! strip_tags(
                $articleContent,
                $allowedTags
            ) !!}

        </div>

    @else

        <div class="news-detail__content">

            @foreach (
                collect(
                    preg_split(
                        '/\r?\n+/',
                        $articleContent
                    )
                )->filter()
                as $paragraph
            )

                <p class="news-detail__paragraph">
                    {{ $paragraph }}
                </p>

            @endforeach

        </div>

    @endif


    {{-- =====================================================
         Share Buttons
    ====================================================== --}}
    <div class="news-detail__share">

        <span class="news-detail__share-label">
            {{ __('news.share') }}:
        </span>


        {{-- X --}}
        <a
            href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
            target="_blank"
            rel="noopener"
            class="news-detail__share-btn"
            aria-label="X"
        >
            X
        </a>


        {{-- LinkedIn --}}
        <a
            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="news-detail__share-btn"
            aria-label="LinkedIn"
        >
            IN
        </a>


        {{-- WhatsApp --}}
        <a
            href="https://wa.me/?text={{ urlencode($article->title.' '.url()->current()) }}"
            target="_blank"
            rel="noopener"
            class="news-detail__share-btn"
            aria-label="WhatsApp"
        >
            WA
        </a>

    </div>

</section>


{{-- =========================================================
     Related News
========================================================= --}}
@if ($relatedArticles->isNotEmpty())

    <section class="news-detail__related">

        <div class="container">

            <div class="section-head__eyebrow">
                {{ __('news.related_badge') }}
            </div>

            <h2 class="section-head__title news-detail__related-title">
                {{ __('news.related_title') }}
            </h2>


            <div class="grid-3">

                @foreach ($relatedArticles as $related)

                    @php
                        $relatedSlug = $related->slug();
                    @endphp

                    <a
                        href="{{ $relatedSlug
                            ? route('news.show', $relatedSlug)
                            : route('news.index') }}"
                        class="card card--hover news-detail__related-card"
                    >

                        <div class="news-detail__related-media">

                            <img
                                src="{{ media_url($related->image) }}"
                                alt="{{ $related->title }}"
                                loading="lazy"
                            >

                        </div>


                        <div class="news-detail__related-body">

                            <div class="news-detail__related-date">

                                {{ optional(
                                    $related->published_at
                                )->translatedFormat('d.m.Y') }}

                            </div>

                            <div class="news-detail__related-title-text">
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
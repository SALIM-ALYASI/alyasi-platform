@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $metaTitle = ($isEn ? $article->meta_title_en : $article->meta_title_ar) ?: $article->title;
    $metaDescription = ($isEn ? $article->meta_description_en : $article->meta_description_ar)
        ?: \Illuminate\Support\Str::limit(strip_tags($article->content), 160);
    // روابط الحلقة بقسم تاريخ التقنية نفسه (مو /articles) لكل لغة متوفرة.
    $arSlug = $article->translatedSlug('ar');
    $enSlug = $article->translatedSlug('en');
    $arUrl = $arSlug ? route('tech-history.show', ['slug' => $arSlug]) : null;
    $enUrl = $enSlug ? route('tech-history.show.en', ['slug' => $enSlug]) : null;
@endphp

@section('title', $metaTitle.' — ALYASI')
@section('meta_description', $metaDescription)
@section('canonical', ($isEn ? $enUrl : $arUrl) ?: url()->current())
@if ($arUrl && $enUrl)
    @section('hreflang_ar', $arUrl)
    @section('hreflang_en', $enUrl)
@endif
@section('og_type', 'article')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_url', url()->current())
@section('og_image', media_url($article->displayImage()))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/articles-show.css') }}">
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

    $articleContent = trim((string) $article->content);

    $hasEscapedHtml = (bool) preg_match(
        '/&lt;\/?(?:p|h2|h3|h4|blockquote|strong|b|em|i|a|ul|ol|li|hr)\b/i',
        $articleContent
    );

    if ($hasEscapedHtml) {
        $articleContent = html_entity_decode($articleContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    $hasHtmlMarkup = $articleContent !== strip_tags($articleContent);
@endphp

<section class="container articles-detail__media-wrap">
    <div class="articles-detail__media">
        <img src="{{ media_url($article->displayImage()) }}" alt="{{ $article->title }}">
    </div>
</section>

<section class="container articles-detail__header-after-image">
    <div class="articles-detail__breadcrumb">
        <a href="{{ localized_route('tech-history.index') }}">{{ __('tech_history.hero_badge') }}</a>
    </div>

    <div class="articles-detail__meta">
        <span class="badge">{{ __('tech_history.hero_badge') }}</span>

        <span class="articles-detail__date">
            @if ($article->author)
                {{ __('articles.author_label') }}: {{ $article->author->displayName() }}
                ·
            @endif

            {{ optional($article->published_at)->translatedFormat('d.m.Y') }}

            @if ($article->reading_time)
                · {{ __('tech_history.read_time', ['minutes' => $article->reading_time]) }}
            @endif
        </span>
    </div>

    <h1 class="articles-detail__title">{{ $article->title }}</h1>
</section>

<section class="container articles-detail__content-wrap">
    @if ($hasHtmlMarkup)
        <div class="articles-detail__content articles-detail__content--compact">
            {!! render_rich_content($articleContent, $allowedTags) !!}
        </div>
    @else
        <div class="articles-detail__content">
            @foreach (collect(preg_split('/\r?\n+/', $articleContent))->filter() as $paragraph)
                <p class="articles-detail__paragraph">{{ $paragraph }}</p>
            @endforeach
        </div>
    @endif
</section>

@if ($otherStories->isNotEmpty())
<section class="articles-detail__related">
    <div class="container">
        <div class="section-head__eyebrow">{{ __('tech_history.hero_badge') }}</div>
        <h2 class="section-head__title articles-detail__related-title">{{ __('tech_history.other_stories') }}</h2>

        <div class="grid-3">
            @foreach ($otherStories as $other)
                @php $otherSlug = $other->translatedSlug(app()->getLocale()); @endphp
                <a href="{{ $otherSlug ? localized_route('tech-history.show', ['slug' => $otherSlug]) : localized_route('tech-history.index') }}" class="card card--hover articles-detail__related-card">
                    <div class="articles-detail__related-media">
                        <img src="{{ media_url($other->displayImage()) }}" alt="{{ $other->title }}" loading="lazy">
                    </div>
                    <div class="articles-detail__related-body">
                        <div class="articles-detail__related-date">
                            {{ optional($other->published_at)->translatedFormat('d.m.Y') }}
                        </div>
                        <div class="articles-detail__related-title-text">{{ $other->title }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

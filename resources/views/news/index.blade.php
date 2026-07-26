@extends('layouts.app')

@php
    $isArabic = app()->getLocale() === 'ar';
    $currentLocale = $isArabic ? 'ar' : 'en';
    $fallbackLocale = $isArabic ? 'en' : 'ar';

    /*
    |--------------------------------------------------------------------------
    | استخراج رابط الخبر حسب اللغة
    |--------------------------------------------------------------------------
    */

    $articleUrl = function ($article) use (
        $currentLocale,
        $fallbackLocale
    ) {
        if (! $article) {
            return null;
        }

        $permalink = $article->permalinks
            ->firstWhere('locale', $currentLocale);

        if (! $permalink) {
            $permalink = $article->permalinks
                ->firstWhere('locale', $fallbackLocale);
        }

        if (! $permalink) {
            $permalink = $article->permalinks->first();
        }

        if (! $permalink?->slug) {
            return null;
        }

        return route('news.show', [
            'slug' => $permalink->slug,
        ]);
    };

    $featuredArticle = $articles->firstWhere(
        'is_featured',
        true
    ) ?? $articles->first();

    $remainingArticles = $featuredArticle
        ? $articles->getCollection()->where(
            'id',
            '!=',
            $featuredArticle->id
        )
        : collect();

    $featuredArticleUrl = $articleUrl($featuredArticle);
@endphp

@section(
    'title',
    $isArabic
        ? 'الأخبار التقنية | ALYASI'
        : 'Technology News | ALYASI'
)

@section(
    'description',
    $isArabic
        ? 'تابع أحدث الأخبار التقنية وتطورات البرمجة والذكاء الاصطناعي والأمن السيبراني.'
        : 'Follow the latest technology, programming, artificial intelligence and cybersecurity news.'
)

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/news/css/news.css') }}"
    >
@endpush

@section('content')

<section class="news-hero">

    <div class="news-hero-grid"></div>

    <div class="news-hero-content">

        <img
            class="news-hero-logo"
            src="{{ asset('images/logo/logo-dark.png') }}"
            alt="ALYASI"
        >

        <span class="news-hero-badge">
            {{ $isArabic ? 'مركز الأخبار' : 'News Center' }}
        </span>

        <h1>
            {{ $isArabic ? 'الأخبار التقنية' : 'Technology News' }}
        </h1>

        <p>
            {{ $isArabic
                ? 'آخر المستجدات في التقنية والبرمجة والذكاء الاصطناعي والأمن السيبراني.'
                : 'The latest developments in technology, programming, artificial intelligence and cybersecurity.'
            }}
        </p>

    </div>

</section>

<section class="news-page">

    <div class="news-container">

        {{-- الخبر المميز --}}
        @if ($featuredArticle)

            @if ($featuredArticleUrl)
                <a
                    href="{{ $featuredArticleUrl }}"
                    class="featured-news-link"
                    aria-label="{{ $featuredArticle->title }}"
                >
            @endif

            <article class="featured-news">

                <div class="featured-news-image">

                    <img
                        src="{{ asset(
                            $featuredArticle->image
                                ?: 'images/logo/logo-dark.png'
                        ) }}"
                        alt="{{ $featuredArticle->image_alt }}"
                    >

                    <div class="featured-news-overlay"></div>

                    <div class="featured-news-badges">

                        @if ($featuredArticle->is_breaking)
                            <span class="news-badge news-badge-breaking">
                                {{ $isArabic ? 'عاجل' : 'Breaking' }}
                            </span>
                        @endif

                        <span class="news-badge news-badge-featured">
                            {{ $isArabic ? 'خبر مميز' : 'Featured' }}
                        </span>

                    </div>

                </div>

                <div class="featured-news-content">

                    @if ($featuredArticle->category)
                        <span class="news-category">
                            {{ $featuredArticle->category->name }}
                        </span>
                    @endif

                    <h2>
                        {{ $featuredArticle->title }}
                    </h2>

                    @if ($featuredArticle->excerpt)
                        <p>
                            {{ $featuredArticle->excerpt }}
                        </p>
                    @endif

                    <div class="news-meta">

                        <span>
                            <i class="fa-regular fa-calendar"></i>

                            {{ optional(
                                $featuredArticle->published_at
                            )->format('d.m.Y') }}
                        </span>

                        <span>
                            <i class="fa-regular fa-eye"></i>

                            {{ number_format(
                                $featuredArticle->views_count
                            ) }}

                            {{ $isArabic ? 'مشاهدة' : 'views' }}
                        </span>

                    </div>

                    <span class="news-read-more">
                        {{ $isArabic ? 'اقرأ الخبر' : 'Read article' }}

                        <i class="fa-solid fa-arrow-left"></i>
                    </span>

                </div>

            </article>

            @if ($featuredArticleUrl)
                </a>
            @endif

        @endif

        <div class="news-section-heading">

            <div>
                <span>
                    {{ $isArabic ? 'آخر المستجدات' : 'Latest Updates' }}
                </span>

                <h2>
                    {{ $isArabic ? 'أحدث الأخبار' : 'Latest News' }}
                </h2>
            </div>

            <p>
                {{ $isArabic
                    ? 'ابقَ على اطلاع بكل جديد في عالم التقنية.'
                    : 'Stay informed about the latest technology developments.'
                }}
            </p>

        </div>

        {{-- بقية الأخبار --}}
        @if ($remainingArticles->isNotEmpty())

            <div class="news-grid">

                @foreach ($remainingArticles as $article)

                    @php
                        $url = $articleUrl($article);
                    @endphp

                    @if ($url)
                        <a
                            href="{{ $url }}"
                            class="news-card-link"
                            aria-label="{{ $article->title }}"
                        >
                    @endif

                    <article class="news-card">

                        <div class="news-card-image">

                            <img
                                src="{{ asset(
                                    $article->image
                                        ?: 'images/logo/logo-dark.png'
                                ) }}"
                                alt="{{ $article->image_alt }}"
                                loading="lazy"
                            >

                            @if ($article->is_breaking)
                                <span class="news-badge news-badge-breaking">
                                    {{ $isArabic ? 'عاجل' : 'Breaking' }}
                                </span>
                            @endif

                        </div>

                        <div class="news-card-content">

                            @if ($article->category)
                                <span class="news-category">
                                    {{ $article->category->name }}
                                </span>
                            @endif

                            <h3>
                                {{ $article->title }}
                            </h3>

                            @if ($article->excerpt)
                                <p>
                                    {{ \Illuminate\Support\Str::limit(
                                        $article->excerpt,
                                        135
                                    ) }}
                                </p>
                            @endif

                            <div class="news-card-footer">

                                <div class="news-meta">

                                    <span>
                                        <i class="fa-regular fa-calendar"></i>

                                        {{ optional(
                                            $article->published_at
                                        )->format('d.m.Y') }}
                                    </span>

                                    <span>
                                        <i class="fa-regular fa-eye"></i>

                                        {{ number_format(
                                            $article->views_count
                                        ) }}
                                    </span>

                                </div>

                                <span class="news-card-arrow">
                                    <i class="fa-solid fa-arrow-left"></i>
                                </span>

                            </div>

                        </div>

                    </article>

                    @if ($url)
                        </a>
                    @endif

                @endforeach

            </div>

        @elseif (! $featuredArticle)

            <div class="news-empty">

                <img
                    src="{{ asset('images/logo/logo-dark.png') }}"
                    alt="ALYASI"
                >

                <h2>
                    {{ $isArabic
                        ? 'لا توجد أخبار حاليًا'
                        : 'No news available'
                    }}
                </h2>

                <p>
                    {{ $isArabic
                        ? 'سيتم نشر أحدث الأخبار التقنية قريبًا.'
                        : 'The latest technology news will be published soon.'
                    }}
                </p>

            </div>

        @endif

        @if ($articles->hasPages())
            <div class="news-pagination">
                {{ $articles->links() }}
            </div>
        @endif

    </div>

</section>

@endsection
@extends('layouts.app')

{{-- صفحة قراءة خبر «نبض عُمان»: النص الرسمي كامل كما نُشر، بدون صياغة أو إضافة. --}}

@section('title', $item->title.' | نبض عُمان — ALYASI')
@section('meta_description', $item->lead)
@section('canonical', $item->url())
@section('og_url', $item->url())
@section('og_type', 'article')
@if ($item->image)
    @section('og_image', media_url($item->image))
@else
    @section('og_image', asset('images/news/oman-hero.webp'))
@endif

@section('schema')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => \Illuminate\Support\Str::limit($item->title, 110),
    'description' => \Illuminate\Support\Str::limit($item->lead, 160),
    'image' => $item->image ? [media_url($item->image)] : null,
    'datePublished' => optional($item->source_published_at ?? $item->created_at)->toIso8601String(),
    'dateModified' => optional($item->updated_at)->toIso8601String(),
    'mainEntityOfPage' => $item->url(),
    'isBasedOn' => $item->source_url,
    'sourceOrganization' => ['@type' => 'Organization', 'name' => $item->source_name],
    'publisher' => ['@type' => 'Organization', 'name' => 'ALYASI', 'url' => url('/')],
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/oman-pulse.css') }}">
@endpush

@section('content')

    <article class="container pulse-read">
        <nav class="pulse-read__crumbs" aria-label="مسار الصفحة">
            <a href="{{ route('news.index') }}">الأخبار</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('news.oman') }}">🇴🇲 نبض عُمان</a>
        </nav>

        <div class="pulse-card__meta">
            <a class="badge" href="{{ route('news.oman', ['tag' => $item->tag]) }}">{{ $item->tag_label }}</a>
            <span>{{ $item->source_name }}</span>
            @if ($item->source_published_at)
                <time datetime="{{ $item->source_published_at->toIso8601String() }}">{{ $item->source_published_at->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}</time>
            @endif
        </div>

        <h1 class="pulse-read__title">{{ $item->title }}</h1>

        @if ($item->image)
            <figure class="pulse-read__media">
                <img src="{{ media_url($item->image) }}" alt="{{ $item->title }}">
                <figcaption>الصورة: {{ $item->source_name }}</figcaption>
            </figure>
        @endif

        <div class="pulse-read__body">
            <p class="pulse-read__lead">{{ $item->lead }}</p>
            @foreach ($item->paragraphs ?? [] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>

        <div class="pulse-read__source">
            <p>النص منقول كما نُشر في المصدر الرسمي، دون صياغة أو إضافة من منصة الياسي.</p>
            <a class="btn btn--primary" href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer">
                البيان كاملًا: {{ $item->source_name }} ←
            </a>
        </div>

        @if ($related->isNotEmpty())
            <section class="pulse-read__related">
                <h2>من نبض عُمان</h2>
                <div class="pulse-read__related-list">
                    @foreach ($related as $other)
                        <a href="{{ $other->url() }}" class="pulse-read__related-item">
                            @if ($other->image)
                                <img src="{{ media_url($other->image) }}" alt="" loading="lazy">
                            @endif
                            <span>
                                <small>{{ $other->tag_label }} · {{ optional($other->source_published_at)->timezone('Asia/Muscat')?->translatedFormat('d.m.Y') }}</small>
                                <strong>{{ $other->title }}</strong>
                            </span>
                        </a>
                    @endforeach
                </div>
                <a class="pulse-read__all" href="{{ route('news.oman') }}">كل أخبار نبض عُمان ←</a>
            </section>
        @endif
    </article>

@endsection

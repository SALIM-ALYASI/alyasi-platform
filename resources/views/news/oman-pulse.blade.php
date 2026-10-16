@extends('layouts.app')

@section('title', 'نبض عُمان — أخبار عُمان الرسمية | ALYASI')
@section('meta_description', 'أخبار رسمية من سلطنة عُمان بنصّها الأصلي ومنسوبة لمصادرها: الخدمات، والتقنية، والاقتصاد، والأمن السيبراني.')
@section('canonical', paginated_canonical(route('news.oman')))
@section('og_url', route('news.oman'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/oman-pulse.css') }}">
@endpush

@section('content')

    <x-page-hero
        badge="🇴🇲 نبض عُمان"
        title="أخبار عُمان"
        highlight="من مصادرها الرسمية"
        description="بيانات رسمية من سلطنة عُمان بنصّها الأصلي، منسوبة لمصدرها مع رابط البيان كاملًا."
        :image="asset('images/news/oman-hero.webp')"
        :image-width="1829"
        :image-height="860"
    />

    <section class="container pulse">
        <div class="filters">
            <a href="{{ route('news.oman') }}" class="filters__btn @if (! $activeTag) is-active @endif">الكل</a>
            @foreach ($tags as $key => $label)
                <a href="{{ route('news.oman', ['tag' => $key]) }}" class="filters__btn @if ($activeTag === $key) is-active @endif">{{ $label }}</a>
            @endforeach
        </div>

        @forelse ($items as $item)
            <article class="pulse-card" id="pulse-{{ $item->id }}" data-reveal>
                @if ($item->image)
                    <div class="pulse-card__media">
                        <img src="{{ media_url($item->image) }}" alt="{{ $item->title }}" loading="lazy">
                    </div>
                @endif

                <div class="pulse-card__body">
                    <div class="pulse-card__meta">
                        <span class="badge">{{ $item->tag_label }}</span>
                        <span>{{ $item->source_name }}</span>
                        @if ($item->source_published_at)
                            <time datetime="{{ $item->source_published_at->toIso8601String() }}">{{ $item->source_published_at->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}</time>
                        @endif
                    </div>

                    <h2 class="pulse-card__title">{{ $item->title }}</h2>

                    <p class="pulse-card__lead">{{ $item->lead }}</p>

                    @if (! empty($item->paragraphs))
                        <details class="pulse-card__details">
                            <summary>التفاصيل</summary>
                            @foreach ($item->paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </details>
                    @endif

                    <a class="pulse-card__source" href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer">
                        البيان الأصلي: {{ $item->source_name }} ←
                    </a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <div class="empty-state__title">لا توجد أخبار بعد</div>
                <p class="empty-state__desc">أول الأخبار الرسمية بتظهر هنا قريبًا.</p>
            </div>
        @endforelse

        <div class="pulse__pagination">
            {{ $items->links() }}
        </div>

        <p class="pulse__note">نبض عُمان ينقل البيانات الرسمية بنصّها كما نُشرت، دون صياغة أو إضافة من منصة الياسي، والمرجع هو المصدر الرسمي.</p>
    </section>

@endsection

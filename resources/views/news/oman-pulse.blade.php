@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
@endphp

@section('title', $t('نبض عُمان — أخبار عُمان الرسمية | ALYASI', 'Oman Pulse — News from Oman | ALYASI'))
@section('meta_description', $t(
    'أخبار سلطنة عُمان: بيانات رسمية بنصّها الأصلي منسوبة لمصادرها، وأخبار التقنية والاقتصاد العُمانية من تغطية ALYASI.',
    'News from the Sultanate of Oman: ALYASI coverage of Omani technology and economy, alongside official statements quoted as issued.'
))
@section('canonical', paginated_canonical(localized_route('news.oman')))
@section('og_url', localized_route('news.oman'))
@section('hreflang_ar', route('news.oman'))
@section('hreflang_en', route('news.oman.en'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/oman-pulse.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="$t('🇴🇲 نبض عُمان', '🇴🇲 Oman Pulse')"
        :title="$t('أخبار عُمان', 'News from Oman')"
        :highlight="$t('أولًا بأول', 'as it happens')"
        :description="$t(
            'بيانات رسمية من سلطنة عُمان بنصّها الأصلي، وأخبار التقنية والاقتصاد العُمانية من تغطية ALYASI.',
            'ALYASI coverage of technology and economy in Oman, alongside official statements from the Sultanate quoted in their original Arabic.'
        )"
        :image="asset('images/news/oman-hero.webp')"
        :image-width="1829"
        :image-height="860"
    />

    <section class="container pulse">
        <div class="filters">
            <a href="{{ localized_route('news.oman') }}" class="filters__btn @if (! $activeTag) is-active @endif">{{ $t('الكل', 'All') }}</a>
            @foreach ($tags as $key => $label)
                <a href="{{ localized_route('news.oman', ['tag' => $key]) }}" class="filters__btn @if ($activeTag === $key) is-active @endif">{{ $label }}</a>
            @endforeach
        </div>

        @forelse ($items as $entry)
            @php $item = $entry['item']; @endphp

            @if ($entry['kind'] === 'official')
                {{-- البيان الرسمي بنصه العربي كما نُشر (حتى بالصفحة الإنجليزية: ما نترجم البيانات الرسمية) --}}
                <article class="pulse-card" id="pulse-{{ $item->id }}" data-reveal>
                    @if ($item->image)
                        <a href="{{ $item->url() }}" class="pulse-card__media" tabindex="-1" aria-hidden="true">
                            <img src="{{ media_url($item->image) }}" alt="" loading="lazy">
                        </a>
                    @endif

                    <div class="pulse-card__body">
                        <div class="pulse-card__meta">
                            <span class="badge pulse-kind pulse-kind--official">{{ $t('بيان رسمي', 'Official statement · Arabic') }}</span>
                            <span class="badge">{{ $isEn ? (\App\Models\OmanPulseItem::TAGS_EN[$item->tag] ?? $item->tag_label) : $item->tag_label }}</span>
                            <span lang="ar">{{ $item->source_name }}</span>
                            @if ($item->source_published_at)
                                <time datetime="{{ $item->source_published_at->toIso8601String() }}">{{ $item->source_published_at->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}</time>
                            @endif
                        </div>

                        <h2 class="pulse-card__title" lang="ar" dir="rtl"><a href="{{ $item->url() }}">{{ $item->title }}</a></h2>

                        <p class="pulse-card__lead" lang="ar" dir="rtl">{{ $item->lead }}</p>

                        <div class="pulse-card__actions">
                            <a class="pulse-card__read" href="{{ $item->url() }}">{{ $t('اقرأ الخبر ←', 'Read in Arabic →') }}</a>
                            <a class="pulse-card__source" href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer">
                                {{ $t('البيان الأصلي:', 'Original statement:') }} <span lang="ar">{{ $item->source_name }}</span>
                            </a>
                        </div>
                    </div>
                </article>
            @else
                {{-- خبر من تغطية ALYASI يخص عُمان (يظهر بصفحة الأخبار بعد) --}}
                @php
                    $locale = $isEn ? 'en' : 'ar';
                    $newsUrl = $item->url($locale);
                    $newsTitle = $isEn ? $item->title_en : $item->title_ar;
                    $newsExcerpt = $isEn ? $item->excerpt_en : $item->excerpt_ar;
                @endphp
                <article class="pulse-card" id="news-{{ $item->id }}" data-reveal>
                    @if ($item->image)
                        <a href="{{ $newsUrl }}" class="pulse-card__media" tabindex="-1" aria-hidden="true">
                            <img src="{{ media_url($item->image) }}" alt="" loading="lazy">
                        </a>
                    @endif

                    <div class="pulse-card__body">
                        <div class="pulse-card__meta">
                            <span class="badge pulse-kind pulse-kind--news">{{ $t('خبر ALYASI', 'ALYASI news') }}</span>
                            @if ($item->source_name)
                                <span>{{ $item->source_name }}</span>
                            @endif
                            @if ($item->published_at)
                                <time datetime="{{ $item->published_at->toIso8601String() }}">{{ $item->published_at->timezone('Asia/Muscat')->translatedFormat('d.m.Y') }}</time>
                            @endif
                        </div>

                        <h2 class="pulse-card__title"><a href="{{ $newsUrl }}">{{ $newsTitle }}</a></h2>

                        @if ($newsExcerpt)
                            <p class="pulse-card__lead">{{ \App\Support\Text\Excerpt::make($newsExcerpt, 260) }}</p>
                        @endif

                        <div class="pulse-card__actions">
                            <a class="pulse-card__read" href="{{ $newsUrl }}">{{ $t('اقرأ الخبر ←', 'Read the story →') }}</a>
                        </div>
                    </div>
                </article>
            @endif
        @empty
            <div class="empty-state">
                <div class="empty-state__title">{{ $t('لا توجد أخبار بعد', 'No news yet') }}</div>
                <p class="empty-state__desc">{{ $t('أول الأخبار بتظهر هنا قريبًا.', 'The first stories will appear here soon.') }}</p>
            </div>
        @endforelse

        <div class="pulse__pagination">
            {{ $items->links() }}
        </div>

        <p class="pulse__note">{{ $t(
            'ما يحمل وسم «بيان رسمي» منقول بنصّه كما نُشر، دون صياغة أو إضافة من منصة الياسي، والمرجع هو المصدر الرسمي. وما يحمل وسم «خبر ALYASI» تغطية تحريرية من المنصة منسوبة لمصادرها.',
            'Items marked “Official statement” are quoted in their original Arabic exactly as published, without rewording or translation by ALYASI; the official source is the reference. Items marked “ALYASI news” are ALYASI’s own editorial coverage, attributed to their sources.'
        ) }}</p>
    </section>

@endsection

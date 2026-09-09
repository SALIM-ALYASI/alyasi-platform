@extends('layouts.app')

@php
    $phase = $edition->phase;
    $ogDescription = \Illuminate\Support\Str::limit(strip_tags($edition->short_description ?: ''), 160);

    // يربط كل "منتج/إعلان" بسعره من جدول الأسعار (لو موجود) عبر تطابق الاسم
    // (عربي أولاً، إنجليزي احتياطاً) -- يسمح بعرض السعر على بطاقة المنتج
    // مباشرة بمجرد ما المدير يضيفه، بغض النظر عن مرحلة المؤتمر (قادم/مباشر/انتهى).
    $productKey = fn (array $row) => trim(filled($row['product_ar'] ?? null) ? $row['product_ar'] : ($row['product_en'] ?? ''));
    $announcementKey = fn (array $row) => trim(filled($row['label_ar'] ?? null) ? $row['label_ar'] : ($row['label_en'] ?? ''));

    $pricingGroups = collect($edition->pricing_table ?? [])->groupBy($productKey);

    $priceInfoFor = function (array $item) use ($pricingGroups, $announcementKey) {
        $key = $announcementKey($item);
        $group = $key !== '' ? ($pricingGroups->get($key) ?? collect()) : collect();

        if ($group->isEmpty()) {
            return null;
        }

        $cheapest = $group->sortBy(fn ($row) => (float) preg_replace('/[^0-9.]/', '', $row['official_price'] ?? '0'))->first();

        return [
            'official_price' => $cheapest['official_price'] ?? null,
            'official_currency' => $cheapest['official_currency'] ?? '',
            'omr_price' => $cheapest['omr_price'] ?? null,
            'is_starting' => $group->count() > 1,
        ];
    };
@endphp

@section('title', $edition->title.' — ALYASI')
@section('meta_description', $ogDescription)

@section('canonical', $edition->permalink()?->url())

@if ($edition->permalink('ar') && $edition->permalink('en'))
    @section('hreflang_ar', $edition->permalink('ar')->url())
    @section('hreflang_en', $edition->permalink('en')->url())
@endif

@section('og_type', 'article')
@section('og_title', $edition->title)
@section('og_description', $ogDescription)
@section('og_url', $edition->permalink()?->url())
@section('og_image', $edition->image ? media_url($edition->image) : asset('images/events/og-cover.jpg'))
@section('og_image_width', 1200)
@section('og_image_height', 630)

{{--
    Event JSON-LD — لا تُفعَّل تلقائياً. جاهزة بالكود بس مُعطَّلة (@if(false))
    لحد ما يتأكد المستخدم صراحة إنه يبيها تُنشر، حسب شرط الملف المرجعي.
--}}
@if (false)
    @section('schema')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $edition->title,
        'description' => $ogDescription,
        'startDate' => optional($edition->event_start_at)->toIso8601String(),
        'endDate' => optional($edition->event_end_at)->toIso8601String(),
        'eventAttendanceMode' => $edition->coverage_type === 'global_remote'
            ? 'https://schema.org/OnlineEventAttendanceMode'
            : 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'image' => [$edition->image ? media_url($edition->image) : asset('images/events/og-cover.jpg')],
        'url' => $edition->permalink()?->url(),
        'organizer' => [
            '@type' => 'Organization',
            'name' => $edition->event->organizer ?? $edition->event->name,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'ALYASI',
            'url' => url('/'),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @endsection
@endif

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-show.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/events-show.css') }}">
@endpush

@section('content')

    <section class="container community-detail__hero-wrap">
        <div class="community-detail__hero-media">
            <img
                src="{{ $edition->image ? media_url($edition->image) : asset('images/events/og-cover.jpg') }}"
                alt="{{ $edition->title }}"
            >
            <span class="badge badge--status-{{ ['upcoming' => 'upcoming', 'live' => 'ongoing', 'concluded' => 'ended'][$phase] }} community-detail__hero-badge">
                {{ __('events.phase.'.$phase) }}
            </span>
        </div>
    </section>

    <section class="container community-detail__body">

        <h1 class="community-detail__title">{{ $edition->title }}</h1>

        <div class="event-detail__date-status">
            {{ __('events.date_status.'.$edition->date_status) }}
        </div>

        <div class="grid-2 community-detail__info-grid">
            <div class="community-detail__info-card">
                <div class="community-detail__info-label">{{ __('community.event_information') }}</div>
                <div class="community-detail__info-value">
                    @if ($edition->event_start_at)
                        {{ $edition->event_start_at->copy()->timezone('Asia/Muscat')->translatedFormat('d.m.Y — H:i') }}
                    @else
                        —
                    @endif
                </div>
            </div>

            @if ($edition->livestream_url && in_array($phase, ['upcoming', 'live'], true))
                <div class="community-detail__info-card">
                    <div class="community-detail__info-label">{{ __('events.watch_live') }}</div>
                    <div class="community-detail__info-value">
                        <a href="{{ $edition->livestream_url }}" target="_blank" rel="noopener">{{ __('events.watch_live') }} ←</a>
                    </div>
                </div>
            @endif
        </div>

        @if ($phase === 'live' && $edition->livestream_url)
            <div class="event-detail__live-banner">
                <span>{{ __('events.phase.live') }}</span>
                <a href="{{ $edition->livestream_url }}" target="_blank" rel="noopener" class="btn btn--light">
                    {{ __('events.watch_live') }}
                </a>
            </div>
        @endif

        @if ($edition->short_description)
            <p class="community-detail__paragraph">{{ $edition->short_description }}</p>
        @endif

        {{-- =====================================================
             شبكة المنتجات — موحّدة لكل المراحل، السعر يظهر تلقائيًا
             فوق البطاقة أول ما يتوفّر بجدول الأسعار (بدون انتظار "انتهى").
        ====================================================== --}}
        @if (!empty($edition->announcements))
            <h2 class="event-detail__section-title">
                {{ $phase === 'concluded' ? __('events.what_was_announced') : __('events.expected_announcements') }}
            </h2>
            <div class="product-grid">
                @foreach ($edition->announcements as $item)
                    @include('events._announcement-item', ['item' => $item, 'priceInfo' => $priceInfoFor($item)])
                @endforeach
            </div>
            <p class="event-detail__pricing-disclaimer">{{ __('events.pricing_table_disclaimer') }}</p>
        @endif

        {{-- =====================================================
             معرض الصور وحكم الترقية — لمرحلة "انتهى" فقط
        ====================================================== --}}
        @if ($phase === 'concluded')

            @if (!empty($edition->gallery))
                <h2 class="event-detail__section-title">{{ __('events.gallery_title') }}</h2>
                <div class="event-detail__gallery">
                    @foreach ($edition->gallery as $photo)
                        <img src="{{ media_url($photo) }}" alt="{{ $edition->title }}" loading="lazy">
                    @endforeach
                </div>
            @endif

            @if ($edition->upgrade_verdict)
                <div class="event-detail__verdict">
                    <div class="event-detail__verdict-title">{{ __('events.upgrade_verdict_title') }}</div>
                    <div class="event-detail__verdict-answer">{{ __('events.upgrade_verdict.'.$edition->upgrade_verdict) }}</div>
                    @if ($edition->upgrade_verdict_text)
                        <div class="event-detail__verdict-text">{{ $edition->upgrade_verdict_text }}</div>
                    @endif
                </div>
            @endif

        @endif

    </section>

    {{-- تكبير صورة المنتج — نفس المعرض ونفس بطاقات المنتجات --}}
    <div class="lightbox" id="event-lightbox" hidden>
        <button type="button" class="lightbox__close" id="event-lightbox-close" aria-label="{{ __('events.close_lightbox') }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <img src="" alt="" id="event-lightbox-image">
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lightbox = document.getElementById('event-lightbox');
    var lightboxImage = document.getElementById('event-lightbox-image');
    var closeBtn = document.getElementById('event-lightbox-close');

    function openLightbox(src, alt) {
        lightboxImage.src = src;
        lightboxImage.alt = alt || '';
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.hidden = true;
        lightboxImage.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-lightbox-trigger]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openLightbox(btn.getAttribute('data-lightbox-src'), btn.getAttribute('data-lightbox-alt'));
        });
    });

    closeBtn.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !lightbox.hidden) {
            closeLightbox();
        }
    });
});
</script>
@endpush

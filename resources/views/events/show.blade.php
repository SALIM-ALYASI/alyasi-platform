@extends('layouts.app')

@php
    $phase = $edition->phase;
    $ogDescription = \Illuminate\Support\Str::limit(strip_tags($edition->short_description ?: ''), 160);
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
             upcoming / live: المتوقع طرحه (بدرجة تأكيد كل بند)
        ====================================================== --}}
        @if (in_array($phase, ['upcoming', 'live'], true) && !empty($edition->announcements))
            <h2 class="event-detail__section-title">{{ __('events.expected_announcements') }}</h2>
            <div class="event-detail__announcement-list">
                @foreach ($edition->announcements as $item)
                    <div class="event-detail__announcement">
                        <div>
                            <div class="event-detail__announcement-label">
                                {{ app()->getLocale() === 'en' ? ($item['label_en'] ?? $item['label_ar'] ?? '') : ($item['label_ar'] ?? '') }}
                            </div>
                            @if (!empty($item['note_ar']) || !empty($item['note_en']))
                                <div class="event-detail__announcement-note">
                                    {{ app()->getLocale() === 'en' ? ($item['note_en'] ?? $item['note_ar'] ?? '') : ($item['note_ar'] ?? '') }}
                                </div>
                            @endif
                        </div>
                        @if (!empty($item['confidence']))
                            <span class="badge">{{ __('events.confidence.'.$item['confidence']) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- =====================================================
             concluded: ما أُعلن فعلاً + جدول الأسعار + حكم الترقية
        ====================================================== --}}
        @if ($phase === 'concluded')

            @if (!empty($edition->announcements))
                <h2 class="event-detail__section-title">{{ __('events.what_was_announced') }}</h2>
                <div class="event-detail__announcement-list">
                    @foreach ($edition->announcements as $item)
                        <div class="event-detail__announcement">
                            <div>
                                <div class="event-detail__announcement-label">
                                    {{ app()->getLocale() === 'en' ? ($item['label_en'] ?? $item['label_ar'] ?? '') : ($item['label_ar'] ?? '') }}
                                </div>
                                @if (!empty($item['note_ar']) || !empty($item['note_en']))
                                    <div class="event-detail__announcement-note">
                                        {{ app()->getLocale() === 'en' ? ($item['note_en'] ?? $item['note_ar'] ?? '') : ($item['note_ar'] ?? '') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (!empty($edition->pricing_table))
                <h2 class="event-detail__section-title">{{ __('events.pricing_table_title') }}</h2>
                <table class="event-detail__pricing-table">
                    <thead>
                        <tr>
                            <th>{{ __('events.pricing_table_product') }}</th>
                            <th>{{ __('events.pricing_table_official_price') }}</th>
                            <th>{{ __('events.pricing_table_omr_price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($edition->pricing_table as $row)
                            <tr>
                                <td>{{ app()->getLocale() === 'en' ? ($row['product_en'] ?? $row['product_ar'] ?? '') : ($row['product_ar'] ?? '') }}</td>
                                <td>{{ $row['official_price'] ?? '—' }} {{ $row['official_currency'] ?? '' }}</td>
                                <td>{{ isset($row['omr_price']) ? $row['omr_price'].' OMR' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="event-detail__pricing-disclaimer">{{ __('events.pricing_table_disclaimer') }}</p>
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

@endsection

@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $editionUrl = fn ($edition) => ($edition?->permalink(app()->getLocale()) ?? $edition?->permalink())?->url();
    $seriesUrl = fn ($event) => route($isEn ? 'event_editions.show.en' : 'event_editions.show', ['slug' => $event->slug]);
    $phaseClass = ['upcoming' => 'upcoming', 'live' => 'ongoing', 'remote' => 'upcoming', 'concluded' => 'ended'];
@endphp

@section('title', __('events.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('events.meta_description'))
@section('canonical', localized_route('events.index'))
@section('og_url', localized_route('events.index'))
@section('hreflang_ar', localized_route('events.index', [], 'ar'))
@section('hreflang_en', localized_route('events.index', [], 'en'))
@section('og_image', asset('images/events/og-cover.jpg'))
@section('og_image_width', 1200)
@section('og_image_height', 630)

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/shared/page-hero.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-index.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/events-index.css') }}">
@endpush

@section('content')

    <x-page-hero
        :badge="__('events.hero_badge')"
        :title="__('events.hub_title')"
        :description="__('events.hub_description')"
        :image="asset('images/community/hero.webp')"
        :image-width="1672"
        :image-height="941"
    />

    {{-- تغطيات ALYASI الحصرية: المحتوى اللي ننتجه نحن، قبل قائمة المؤتمرات --}}
    @if (count($interviews) || $partner)
        <section class="container events-exclusive" aria-labelledby="events-exclusive-title">
            <div class="section-head">
                <h2 class="section-head__title" id="events-exclusive-title">{{ __('events.exclusive_title') }}</h2>
                <p class="section-head__desc">{{ __('events.exclusive_intro') }}</p>
            </div>

            <div class="events-exclusive__grid">
                <a class="events-exclusive__card" href="{{ localized_route('event_coverage.cyberx_oman_2026') }}" data-reveal>
                    <span class="events-exclusive__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <strong>{{ __('events.exclusive_cyberx_coverage_title') }}</strong>
                    <span>{{ __('events.exclusive_cyberx_coverage_desc') }}</span>
                    <span class="events-exclusive__more">{{ __('events.coverage_full') }} {{ $isEn ? '→' : '←' }}</span>
                </a>

                @if (count($interviews))
                    <a class="events-exclusive__card" href="{{ localized_route('event_coverage.cyberx_oman_2026.interviews') }}" data-reveal>
                        <span class="events-exclusive__faces" aria-hidden="true">
                            @foreach (array_slice($interviews, 0, 5) as $guest)
                                @include('events.coverage.interviews._avatar', ['guest' => $guest, 'size' => 34])
                            @endforeach
                        </span>
                        <strong>{{ __('events.exclusive_interviews_title') }}</strong>
                        <span>{{ __('events.exclusive_interviews_desc', ['count' => count($interviews)]) }}</span>
                        <span class="events-exclusive__more">{{ __('events.interviews_link') }} {{ $isEn ? '→' : '←' }}</span>
                    </a>
                @endif

                @if ($partner && ($partnerUrl = $editionUrl($partner->edition)))
                    <a class="events-exclusive__card" href="{{ $partnerUrl }}" data-reveal>
                        <span class="events-exclusive__icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
                        <strong>{{ __('events.exclusive_partner_title') }}</strong>
                        <span>{{ __('events.exclusive_partner_desc', ['date' => $partner->edition->dateRangeLabel()]) }}</span>
                        <span class="events-exclusive__more">{{ __('events.view_details') }} {{ $isEn ? '→' : '←' }}</span>
                    </a>
                @endif
            </div>
        </section>
    @endif

    @foreach ([['upcoming', $upcoming], ['past', $past]] as [$group, $rows])
        @continue($rows->isEmpty())

        <section class="container community-section events-group" aria-labelledby="events-{{ $group }}">
            <div class="section-head">
                <h2 class="section-head__title" id="events-{{ $group }}">{{ __('events.section_'.$group) }}</h2>
            </div>

            <div class="grid-3">
                @foreach ($rows as $row)
                    @php
                        $event = $row->event;
                        $edition = $row->edition;
                        $phase = $edition?->badge_phase;
                        $coverageUrl = $editionUrl($edition) ?? $seriesUrl($event);
                        $cardImage = $event->image ?: $edition?->displayImage();
                    @endphp

                    <article class="card card--hover community-card event-card" data-reveal>
                        <a href="{{ $coverageUrl }}" class="community-card__media" tabindex="-1" aria-hidden="true">
                            <img src="{{ $cardImage ? media_url($cardImage) : asset('images/events/og-cover.jpg') }}" alt="" loading="lazy" width="1200" height="630">

                            @if ($phase)
                                <span class="badge badge--status-{{ $phaseClass[$phase] }} community-card__badge">{{ __('events.phase.'.$phase) }}</span>
                            @endif
                        </a>

                        <div class="community-card__body">
                            @if ($date = $edition?->dateRangeLabel())
                                <div class="community-card__meta event-card__date">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    <span class="visually-hidden">{{ __('events.event_date') }}:</span>
                                    <time datetime="{{ $edition->event_start_at->copy()->timezone('Asia/Muscat')->toDateString() }}">{{ $date }}</time>
                                </div>
                            @endif

                            <h3 class="community-card__title"><a href="{{ $coverageUrl }}">{{ $event->localizedName() }}</a></h3>

                            <p class="community-card__excerpt">
                                {{ $event->localizedDescription() ?: ($edition?->short_description ?: __('events.series_default_description')) }}
                            </p>

                            @if ($row->highlights['tags'])
                                <ul class="event-card__tags">
                                    @foreach ($row->highlights['tags'] as $tag)
                                        <li>{{ $tag }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="event-card__links">
                                <a class="community-card__link" href="{{ $coverageUrl }}">
                                    {{ $phase === 'concluded' ? __('events.view_coverage') : __('events.view_details') }} {{ $isEn ? '→' : '←' }}
                                </a>
                                @foreach ($row->highlights['links'] as $link)
                                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                @endforeach
                                <a href="{{ $seriesUrl($event) }}">{{ __('events.archive_link') }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($upcoming->isEmpty() && $past->isEmpty())
        <section class="container community-section">
            <div class="empty-state">
                <div class="empty-state__title">{{ __('events.empty_title') }}</div>
                <p class="empty-state__desc">{{ __('events.empty_description') }}</p>
            </div>
        </section>
    @endif

    <section class="container">
        <div class="cta-band" data-reveal>
            <h2 class="cta-band__title">{{ __('community.cta_title') }}</h2>
            <p class="cta-band__desc">{{ __('community.cta_description') }}</p>
            <div class="cta-band__actions">
                <a href="{{ localized_route('contact') }}" class="btn btn--light">{{ __('events.contact_us') }}</a>
            </div>
        </div>
    </section>

@endsection

@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $phaseClass = ['upcoming' => 'upcoming', 'live' => 'ongoing', 'remote' => 'upcoming', 'concluded' => 'ended'];
@endphp

@section('title', __('events.meta_title', ['brand' => 'ALYASI']))
@section('meta_description', __('events.meta_description'))
{{-- صفحة السنة (?year=) نسخة مفلترة من نفس الصفحة: canonical للصفحة الكاملة --}}
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

    {{-- تغطية مميزة: مثبّتة من لوحة التحكم، وإلا أحدث نسخة نُشر لها محتوى حصري فعلي --}}
    @include('events._featured', ['featured' => $featured])

    @if ($years->count() > 1 || $year)
        <nav class="container events-years" aria-label="{{ __('events.filter_by_year') }}">
            <a href="{{ localized_route('events.index') }}" @class(['is-active' => ! $year]) @unless ($year) aria-current="page" @endunless>{{ __('events.all_events') }}</a>
            @foreach ($years as $y)
                <a href="{{ localized_route('events.index') }}?year={{ $y }}" @class(['is-active' => $year === $y]) @if ($year === $y) aria-current="page" @endif>{{ $y }}</a>
            @endforeach
        </nav>
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
                        $coverageUrl = $edition->publicUrl();
                        $cardImage = $event->image ?: $edition?->displayImage();
                    @endphp

                    <article class="card card--hover community-card event-card" data-reveal>
                        <a href="{{ $coverageUrl }}" class="community-card__media" tabindex="-1" aria-hidden="true">
                            <img src="{{ $cardImage ? media_url($cardImage) : asset('images/events/og-cover.jpg') }}" @if ($cardImage && ($srcset = media_srcset($cardImage))) srcset="{{ $srcset }}" sizes="(max-width: 767px) 92vw, 400px" @endif alt="" loading="lazy" decoding="async" width="1200" height="630">

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
                            </div>

                            {{-- سنوات المؤتمر: كل سنة تفتح نسختها (بدل «الأرشيف») --}}
                            <nav class="event-card__years" aria-label="{{ __('events.editions_label', ['name' => $event->localizedName()]) }}">
                                @foreach ($event->editions->sortByDesc('year') as $yearEdition)
                                    <a href="{{ $yearEdition->publicUrl() }}" @class(['is-current' => $yearEdition->is($edition)]) @if ($yearEdition->is($edition)) aria-current="page" @endif>{{ $yearEdition->year }}</a>
                                @endforeach
                            </nav>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($upcoming->isEmpty() && $past->isEmpty() && ! $featured)
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

@extends('layouts.app')

@section('title', $work->localized('title').' — ALYASI')

@section(
    'meta_description',
    \Illuminate\Support\Str::limit(
        strip_tags($work->localized('short_description') ?: $work->localized('description')),
        160
    )
)

{{-- =========================================================
     SEO / Canonical
========================================================= --}}
@section('canonical', localized_route('works.show', ['work' => $work]))
@section('hreflang_ar', route('works.show', $work))
@section('hreflang_en', localized_route('works.show', ['work' => $work], 'en'))

{{-- =========================================================
     Open Graph
========================================================= --}}
@section('og_type', 'website')

@section('og_title', $work->localized('title'))

@section(
    'og_description',
    \Illuminate\Support\Str::limit(
        strip_tags($work->localized('short_description') ?: $work->localized('description')),
        160
    )
)

@section('og_url', localized_route('works.show', ['work' => $work]))

@section(
    'og_image',
    media_url($work->cover_image)
)

{{-- =========================================================
     CreativeWork Schema
========================================================= --}}
@section('schema')
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'CreativeWork',

    'name' => $work->localized('title'),

    'inLanguage' => app()->getLocale(),

    'description' => \Illuminate\Support\Str::limit(
        strip_tags($work->localized('short_description') ?: $work->localized('description')),
        160
    ),

    'url' => localized_route('works.show', ['work' => $work]),

    'image' => media_url($work->cover_image),

    // «قيد التطوير» لمحركات البحث بدل ما يبان كعمل منجز.
    'creativeWorkStatus' => $work->status === 'in_development' ? 'In development' : null,

    'dateCreated' => optional(
        $work->completed_at
    )->toIso8601String(),

    // أعمال الفيديو: VideoObject عشان تظهر بنتائج الفيديو في البحث.
    'video' => $work->video_path ? [
        '@type' => 'VideoObject',
        'name' => $work->localized('title'),
        'description' => \Illuminate\Support\Str::limit(strip_tags($work->localized('short_description') ?: $work->localized('description')), 160),
        'thumbnailUrl' => media_url($work->cover_image),
        'contentUrl' => media_url($work->video_path),
        'uploadDate' => optional($work->completed_at ?? $work->created_at)->toIso8601String(),
    ] : null,

    'creator' => [
        '@type' => 'Organization',
        'name' => 'ALYASI',
        'url' => url('/'),
    ],

], fn ($value) => $value !== null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection


{{-- =========================================================
     Styles
========================================================= --}}
@push('styles')

    <link
        rel="stylesheet"
        href="{{ versioned_asset('css/pages/works-show.css') }}"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | Work Detail Header Fix
        |--------------------------------------------------------------------------
        | يمنع صورة العمل من الدخول خلف Header الموقع
        */

        .work-detail__hero-wrap {
            padding-top: 32px;
        }

        .work-detail__hero-media {
            position: relative;
            overflow: hidden;
        }

        .work-detail__hero-media img {
            display: block;
            width: 100%;
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 640px) {

            .work-detail__hero-wrap {
                padding-top: 20px;
            }

        }

    </style>

@endpush


@section('content')


{{-- =========================================================
     Work Cover Image
     أول عنصر بعد Header الموقع
========================================================= --}}
<section class="container work-detail__hero-wrap">

    @if ($work->video_path)
        {{-- عمل فيديو (إعلان مثلًا): المشغّل مكان الغلاف، بمقاسه الأصلي (عمودي أو أفقي).
             #t=0.1 يخلي سفاري يعرض أول لقطة بدل مربع أسود. --}}
        <div class="work-detail__video-media">
            <video
                src="{{ media_url($work->video_path) }}#t=0.1"
                controls
                playsinline
                preload="metadata"
                controlslist="nodownload noplaybackrate"
                disablepictureinpicture
                oncontextmenu="return false;"
                aria-label="{{ $work->localized('title') }}"
            ></video>
        </div>
    @else
        <div class="work-detail__hero-media">

            <img
                src="{{ media_url($work->cover_image) }}"
                alt="{{ $work->localized('title') }}"
            >

        </div>
    @endif

    @if ($work->cover_is_illustrative)
        <p class="work-detail__cover-note">
            {{ __('works.show.illustrative_cover') }}
        </p>
    @endif

</section>


{{-- =========================================================
     Work Content
========================================================= --}}
<section class="container work-detail__body">

    {{-- Work Type --}}
    <span class="badge">
        {{ $work->type_label }}
    </span>

    @if ($work->status_label)
        <span class="badge badge--status-upcoming">
            {{ $work->status_label }}
        </span>
    @endif


    {{-- Title --}}
    <h1 class="work-detail__title">
        {{ $work->localized('title') }}
    </h1>


    {{-- =====================================================
         Work Information
    ====================================================== --}}
    <div class="grid-3 work-detail__info-grid">

        {{-- Client --}}
        <div class="work-detail__info-card">

            <div class="work-detail__info-label">
                {{ __('works.show.client') }}
            </div>

            <div class="work-detail__info-value">
                {{ $work->client_name ?: '—' }}
            </div>

        </div>


        {{-- Completion Date / Status (مشروع قيد التطوير ما له تاريخ إنجاز) --}}
        <div class="work-detail__info-card">
            @if ($work->status_label)
                <div class="work-detail__info-label">
                    {{ __('works.show.status') }}
                </div>
                <div class="work-detail__info-value">
                    {{ $work->status_label }}
                </div>
            @else
                <div class="work-detail__info-label">
                    {{ __('works.show.completion_date') }}
                </div>
                <div class="work-detail__info-value">
                    {{ optional(
                        $work->completed_at
                    )->translatedFormat('d.m.Y') ?: '—' }}
                </div>
            @endif
        </div>
        {{-- Work Type --}}
        <div class="work-detail__info-card">

            <div class="work-detail__info-label">
                {{ __('works.show.work_type') }}
            </div>

            <div class="work-detail__info-value">
                {{ $work->type_label }}
            </div>

        </div>

    </div>


    {{-- =====================================================
         Description
    ====================================================== --}}
@if (filled($work->title_en))
        {{-- أعمال ثنائية اللغة مكتوبة بفقرات؛ القديمة تبقى فقرة واحدة مثل ما كانت --}}
        @forelse ($work->localizedParagraphs() as $paragraph)
            <p class="work-detail__desc">{{ $paragraph }}</p>
        @empty
            <p class="work-detail__desc">{{ __('works.show.no_description') }}</p>
        @endforelse
    @else
        <p class="work-detail__desc">
            {{ $work->description
                ?: __('works.show.no_description') }}
        </p>
    @endif


    {{-- =====================================================
         Technologies
    ====================================================== --}}
    @if ($work->technologies->isNotEmpty())

        <h3 class="work-detail__section-title">
            {{ __('works.show.technologies') }}
        </h3>


        <div class="work-detail__tags">

            @foreach ($work->technologies as $tech)

                <span class="work-detail__tag">
                    {{ $tech->name }}
                </span>

            @endforeach

        </div>

    @endif


    {{-- =====================================================
         Gallery
    ====================================================== --}}
    @if ($work->images->isNotEmpty())

        <h3 class="work-detail__section-title">
            {{ __('works.show.gallery') }}
        </h3>


        <div class="grid-3 work-detail__gallery">

            @foreach ($work->images as $image)

                <div class="work-detail__gallery-item">

                    <img
                        src="{{ media_url($image->image) }}"
                        alt="{{ $image->alt_text ?: $work->localized('title') }}"
                        loading="lazy"
                    >

                </div>

            @endforeach

        </div>

    @endif


    {{-- =====================================================
         Visit Project
    ====================================================== --}}
    @if ($work->work_url)

        <a
            href="{{ $work->work_url }}"
            target="_blank"
            rel="noopener"
            class="btn btn--primary work-detail__visit"
        >
            {{ __('works.show.visit_project') }}
        </a>

    @endif

</section>


{{-- =========================================================
     Related Works
========================================================= --}}
@if ($relatedWorks->isNotEmpty())

    <section class="work-detail__related">

        <div class="container">

            <div class="section-head__eyebrow">
                {{ __('works.show.related_tag') }}
            </div>


            <h2 class="section-head__title work-detail__related-title">
                {{ __('works.show.related_title') }}
            </h2>


            <div class="grid-3">

                @foreach ($relatedWorks as $related)

                    <a
                        href="{{ localized_route('works.show', ['work' => $related]) }}"
                        class="card card--hover work-detail__related-card"
                    >

                        <div class="work-detail__related-media">

                            <img
                                src="{{ media_url($related->cover_image) }}"
                                alt="{{ $related->localized('title') }}"
                                loading="lazy"
                            >

                        </div>


                        <div class="work-detail__related-body">

                            <div class="work-detail__related-tag">
                                {{ $related->type_label }}
                            </div>

                            <div class="work-detail__related-title-text">
                                {{ $related->localized('title') }}
                            </div>

                        </div>

                    </a>

                @endforeach

            </div>

        </div>

    </section>

@endif


{{-- =========================================================
     Reviews
========================================================= --}}
<section
    id="reviews"
    class="container work-detail__reviews"
>

    <x-reviews
        :reviews="$work->approvedReviews"
        action="{{ route('works.reviews.store', $work) }}"
    />

</section>

@endsection
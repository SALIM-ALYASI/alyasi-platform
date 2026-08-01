@extends('layouts.app')

@section('title', $work->title . ' | ALYASI')

@push('styles')
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/works/css/work-show.css') }}"
>
@endpush

@section('content')

<main class="work-show-page">

    <section class="work-show-hero">

        <div class="works-container">

            <a
                href="{{ route('works.index') }}"
                class="work-show-back"
            >
                <i
                    class="fa-solid {{ app()->isLocale('ar')
                        ? 'fa-arrow-right'
                        : 'fa-arrow-left' }}"
                    aria-hidden="true"
                ></i>

                {{ __('works.show.back_to_works') }}
            </a>

            <div class="work-show-hero__content">

                <span class="works-section-tag">
                    {{ __('works.types.' . $work->type) }}
                </span>

                <h1>
                    {{ $work->title }}
                </h1>

                @if($work->short_description)

                    <p>
                        {{ $work->short_description }}
                    </p>

                @endif

                <div class="work-show-hero__meta">

                    @if($work->service)

                        <span>

                            <i class="fa-solid fa-briefcase"></i>

                            {{ $work->service->localizedTitle() }}

                        </span>

                    @endif

                    @if($work->client_name)

                        <span>

                            <i class="fa-regular fa-user"></i>

                            {{ $work->client_name }}

                        </span>

                    @endif

                    @if($work->completed_at)

                        <span>

                            <i class="fa-regular fa-calendar"></i>

                            {{ $work->completed_at->format('Y/m/d') }}

                        </span>

                    @endif

                </div>

                <div class="work-show-hero__actions">

                    @if($work->work_url)

                        <a
                            href="{{ $work->work_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="work-show-button work-show-button--primary"
                        >
                            <i class="fa-solid fa-up-right-from-square"></i>

                            {{ __('works.show.visit_project') }}
                        </a>

                    @endif

                    <a
                        href="{{ route('contact') }}"
                        class="work-show-button work-show-button--outline"
                    >
                        <i class="fa-regular fa-paper-plane"></i>

                        {{ __('works.cta.contact') }}
                    </a>

                </div>

            </div>

            @if($work->cover_image)

                <div class="work-show-hero__media">

                    <img
                        src="{{ asset('storage/'.$work->cover_image) }}"
                        alt="{{ $work->title }}"
                    >

                </div>

            @endif

        </div>

    </section>

    <section class="work-show-content">

        <div class="works-container">

            <div class="work-show-content__grid">

                <div class="work-show-panel">

                    <div class="work-show-panel__header">

                        <span>
                            {{ __('works.show.about_work') }}
                        </span>

                        <h2>
                            {{ __('works.show.project_details') }}
                        </h2>

                    </div>

                    <div class="work-show-panel__body">

                        <div class="work-show-description">

                            {!! nl2br(e($work->description ?: __('works.show.no_description'))) !!}

                        </div>

                    </div>

                </div>

                <aside class="work-show-panel">

                    <div class="work-show-panel__header">

                        <span>
                            {{ __('works.show.project_details') }}
                        </span>

                        <h2>
                            {{ __('works.show.project_details') }}
                        </h2>

                    </div>

                    <div class="work-show-panel__body">

                        <div class="work-show-details">

                            @if($work->service)

                                <div class="work-show-detail">

                                    <div class="work-show-detail__icon">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </div>

                                    <div class="work-show-detail__content">

                                        <small>
                                            {{ __('works.show.service') }}
                                        </small>

                                        <strong>
                                            {{ $work->service->localizedTitle() }}
                                        </strong>

                                    </div>

                                </div>

                            @endif

                            @if($work->client_name)

                                <div class="work-show-detail">

                                    <div class="work-show-detail__icon">
                                        <i class="fa-regular fa-user"></i>
                                    </div>

                                    <div class="work-show-detail__content">

                                        <small>
                                            {{ __('works.show.client') }}
                                        </small>

                                        <strong>
                                            {{ $work->client_name }}
                                        </strong>

                                    </div>

                                </div>

                            @endif

                            @if($work->completed_at)

                                <div class="work-show-detail">

                                    <div class="work-show-detail__icon">
                                        <i class="fa-regular fa-calendar"></i>
                                    </div>

                                    <div class="work-show-detail__content">

                                        <small>
                                            {{ __('works.show.completion_date') }}
                                        </small>

                                        <strong>
                                            {{ $work->completed_at->format('Y/m/d') }}
                                        </strong>

                                    </div>

                                </div>

                            @endif

                        </div>

                        @if($work->technologies->isNotEmpty())

                            <div class="work-show-technologies">

                                @foreach($work->technologies as $technology)

                                    <span>

                                        {{ $technology->name }}

                                    </span>

                                @endforeach

                            </div>

                        @endif

                    </div>

                </aside>

            </div>

        </div>

    </section>

</main>

@endsection
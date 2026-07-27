@extends('layouts.app')

@section('title', __('services.meta_title', [
    'brand' => __('home.brand'),
]))

@section('description', __('services.meta_description'))

@section('content')

<section
    class="hero hero-small"
    id="services"
>
    <div class="hero-grid"></div>

    <div class="hero-badge">
        <div class="hero-badge-dot"></div>

        <span>
            {{ __('services.hero_badge') }}
        </span>
    </div>

    <h1>
        {{ __('services.hero_title') }}
        <em>{{ __('services.hero_title_highlight') }}</em>
    </h1>

    <p class="hero-sub">
        {{ __('services.hero_description') }}
    </p>
</section>

<section class="section">

    <div class="section-heading reveal">

        <p class="section-tag">
            {{ __('services.section_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('services.section_title') }}
            <strong>
                {{ __('services.section_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('services.section_description') }}
        </p>

    </div>

    @if ($services->count())

        <div class="services-list">

            @foreach ($services as $service)

                @php
                    $title = $service->localizedTitle();
                    $description = $service->localizedDescription();
                    $slug = $service->slug();
                    $isReverse = $loop->even;
                @endphp

                <article
                    class="feature reveal {{ $isReverse ? 'feature-reverse' : '' }}"
                >
                    <div class="feature-visual">

                        <img
                            src="{{ $service->image
                                ? asset($service->image)
                                : asset('luminary/images/tm-luminary-01.jpg') }}"
                            alt="{{ $title }}"
                            loading="lazy"
                        >

                    </div>

                    <div class="feature-content">

                        <div class="feature-number">
                            {{ str_pad(
                                $loop->iteration,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) }}
                        </div>

                        <p class="section-tag">
                            {{ __('services.digital_service') }}
                        </p>

                        <h2 class="section-title">
                            {{ $title }}
                        </h2>

                        <p class="section-body">
                            {{ \Illuminate\Support\Str::limit(
                                strip_tags($description),
                                260
                            ) }}
                        </p>

                        <div class="hero-ctas">

                            @if ($slug)
                                <a
                                    href="{{ route('services.show', $slug) }}"
                                    class="btn-primary"
                                >
                                    {{ __('services.view_details') }}
                                </a>
                            @endif

                            <a
                                href="{{ route('social-links.index') }}"
                                class="btn-secondary"
                            >
                                {{ __('services.request_service') }}
                            </a>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

        @if ($services->hasPages())
            <div class="services-pagination">
                {{ $services->links() }}
            </div>
        @endif

    @else

        <div class="services-empty reveal">

            <span class="services-empty-icon">
                <i
                    class="fa-solid fa-layer-group"
                    aria-hidden="true"
                ></i>
            </span>

            <h3>
                {{ __('services.empty_title') }}
            </h3>

            <p>
                {{ __('services.empty_description') }}
            </p>

            <a
                href="{{ route('social-links.index') }}"
                class="btn-primary"
            >
                {{ __('services.contact_us') }}
            </a>

        </div>

    @endif

</section>

<section class="cta-section">

    <div class="cta-card reveal">

        <span class="section-tag">
            {{ __('services.cta_badge') }}
        </span>

        <h2>
            {{ __('services.cta_title') }}
            <strong>
                {{ __('services.cta_title_highlight') }}
            </strong>
        </h2>

        <p>
            {{ __('services.cta_description') }}
        </p>

        <div class="hero-ctas">

            <a
                href="{{ route('social-links.index') }}"
                class="btn-primary"
            >
                {{ __('services.contact_us') }}
            </a>

            <a
                href="{{ route('home') }}"
                class="btn-secondary"
            >
                {{ __('services.back_home') }}
            </a>

        </div>

    </div>

</section>

@endsection
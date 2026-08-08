<section class="section" id="services" data-reveal>

    @php
        $featuredService = $services->first();
    @endphp

    @if ($featuredService)

        @php
            $featuredTitle = $featuredService->localizedTitle();
            $featuredDescription = $featuredService->localizedDescription();
            $featuredSlug = $featuredService->slug();
        @endphp

        <div class="section__inner feature">

            <div class="feature__visual">
                <img
                    src="{{ $featuredService->image
                        ? asset($featuredService->image)
                        : asset('images/home/service-featured.webp') }}"
                    alt="{{ $featuredTitle }}"
                    loading="lazy"
                >
                <span class="feature__visual-caption">{{ $featuredTitle }}</span>
            </div>

            <div class="feature__content">

                <span class="eyebrow">{{ __('home.featured_service') }}</span>

                <h2 class="section-title">{{ $featuredTitle }}</h2>

                <p class="section-body">
                    {{ \Illuminate\Support\Str::limit(strip_tags($featuredDescription), 260) }}
                </p>

                @if ($featuredSlug)
                    <div class="hero__ctas">
                        <a href="{{ route('services.show', $featuredSlug) }}" class="btn btn--primary">{{ __('home.view_service_details') }}</a>
                        <a href="{{ route('services.index') }}" class="btn btn--secondary">{{ __('home.all_services') }}</a>
                    </div>
                @endif

            </div>

        </div>

    @else

        <div class="section__inner feature">

            <div class="feature__visual">
                <img src="{{ asset('images/home/service-featured.webp') }}" alt="{{ __('home.brand') }}" loading="lazy">
            </div>

            <div class="feature__content">

                <span class="eyebrow">{{ __('home.platform_name') }}</span>

                <h2 class="section-title">
                    {{ __('home.platform_title') }}
                    <em>{{ __('home.platform_title_highlight') }}</em>
                </h2>

                <p class="section-body">{{ __('home.platform_description') }}</p>

                <div class="hero__ctas">
                    <a href="{{ route('services.index') }}" class="btn btn--primary">{{ __('home.explore_services') }}</a>
                </div>

            </div>

        </div>

    @endif

</section>

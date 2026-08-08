<section
    class="section"
    id="services"
>

    @php
        $featuredService = $services->first();
    @endphp

    @if ($featuredService)

        @php
            $featuredTitle = $featuredService->localizedTitle();
            $featuredDescription = $featuredService->localizedDescription();
            $featuredSlug = $featuredService->slug();
        @endphp

        <div class="feature reveal">

            <div class="feature-visual">

                <img
                    src="{{ $featuredService->image
                        ? asset($featuredService->image)
                        : asset('images/home/service-featured.webp') }}"
                    alt="{{ $featuredTitle }}"
                    loading="lazy"
                >

            </div>

            <div class="feature-content">

                <div class="feature-number">
                    01
                </div>

                <p class="section-tag">
                    {{ __('home.featured_service') }}
                </p>

                <h2 class="section-title">
                    {{ $featuredTitle }}
                </h2>

                <p class="section-body">
                    {{ \Illuminate\Support\Str::limit(
                        strip_tags($featuredDescription),
                        260
                    ) }}
                </p>

                @if ($featuredSlug)

                    <div class="hero-ctas">

                        <a
                            href="{{ route(
                                'services.show',
                                $featuredSlug
                            ) }}"
                            class="btn-primary"
                        >
                            {{ __('home.view_service_details') }}
                        </a>

                        <a
                            href="{{ route('services.index') }}"
                            class="btn-secondary"
                        >
                            {{ __('home.all_services') }}
                        </a>

                    </div>

                @endif

            </div>

        </div>

    @else

        <div class="feature reveal">

            <div class="feature-visual">

                <img
                    src="{{ asset(
                        'images/home/service-featured.webp'
                    ) }}"
                    alt="{{ __('home.brand') }}"
                    loading="lazy"
                >

            </div>

            <div class="feature-content">

                <div class="feature-number">
                    01
                </div>

                <p class="section-tag">
                    {{ __('home.platform_name') }}
                </p>

                <h2 class="section-title">
                    {{ __('home.platform_title') }}
                    <strong>
                        {{ __('home.platform_title_highlight') }}
                    </strong>
                </h2>

                <p class="section-body">
                    {{ __('home.platform_description') }}
                </p>

                <div class="hero-ctas">

                    <a
                        href="{{ route('services.index') }}"
                        class="btn-primary"
                    >
                        {{ __('home.explore_services') }}
                    </a>

                </div>

            </div>

        </div>

    @endif

</section>
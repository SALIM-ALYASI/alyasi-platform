<section class="hero" id="hero">

    <div class="hero-grid"></div>

    <a
        href="{{ route('home') }}"
        class="hero-logo"
        aria-label="{{ __('home.brand') }}"
    >
        <img
            src="{{ asset('images/logo/logo-dark.png') }}"
            alt="{{ __('home.brand') }}"
        >
    </a>

    <div class="hero-badge">
        <div class="hero-badge-dot"></div>

        <span>
            {{ __('home.hero_badge') }}
        </span>
    </div>

    <h1>
        {{ __('home.hero_title_1') }}

        <br>

        <em>
            {{ __('home.hero_title_2') }}
        </em>
    </h1>

    <p class="hero-sub">
        {{ __('home.hero_description') }}
    </p>

    <div class="hero-ctas">

        <a
            href="#services"
            class="btn-primary"
        >
            {{ __('home.explore_services') }}
        </a>

        <a
            href="#news"
            class="btn-secondary"
        >
            {{ __('home.latest_news') }}
        </a>

    </div>

    <div class="hero-metrics">

        <div class="metric">

            <div class="metric-value">
                <span
                    class="counter"
                    data-target="4"
                    data-decimals="0"
                >
                    4
                </span>
            </div>

            <div class="metric-label">
                {{ __('home.metrics.sections') }}
            </div>

        </div>

        <div class="metric">

            <div class="metric-value">
                <span
                    class="counter"
                    data-target="{{ $yearsOfExperience ?? 0 }}"
                    data-decimals="0"
                >
                    {{ $yearsOfExperience ?? 0 }}
                </span>
            </div>

            <div class="metric-label">
                {{ __('home.metrics.experience') }}
            </div>

        </div>

        <div class="metric">

            <div class="metric-value">
                <span
                    class="counter"
                    data-target="{{ $visitorsCount ?? 0 }}"
                    data-decimals="0"
                >
                    {{ number_format($visitorsCount ?? 0) }}
                </span>
            </div>

            <div class="metric-label">
                {{ __('home.metrics.visitors') }}
            </div>

        </div>

    </div>

</section>
 
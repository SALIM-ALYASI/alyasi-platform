<section class="hero" id="hero">

    <div class="hero__bg" style="background-image:url('{{ asset('images/home/hero-bg.webp') }}')" data-parallax data-parallax-speed="0.3"></div>
    <div class="hero__scrim"></div>
    <div class="hero__grid"></div>

    <span class="pill">
        <span class="pill__dot"></span>
        {{ __('home.hero_badge') }}
    </span>

    <h1 class="hero__title">
        {{ __('home.hero_title_1') }}
        <br>
        <em>{{ __('home.hero_title_2') }}</em>
    </h1>

    <p class="hero__desc">
        {{ __('home.hero_description') }}
    </p>

    <div class="hero__ctas">
        <a href="#services" class="btn btn--primary">{{ __('home.explore_services') }}</a>
        <a href="#news" class="btn btn--secondary">{{ __('home.latest_news') }}</a>
    </div>

    <div class="hero__metrics">

        <div class="metric">
            <div class="metric__value">4</div>
            <div class="metric__label">{{ __('home.metrics.sections') }}</div>
        </div>

        <div class="metric">
            <div class="metric__value">{{ $yearsOfExperience ?? 0 }}</div>
            <div class="metric__label">{{ __('home.metrics.experience') }}</div>
        </div>

        <div class="metric">
            <div class="metric__value">{{ number_format($visitorsCount ?? 0) }}</div>
            <div class="metric__label">{{ __('home.metrics.visitors') }}</div>
        </div>

    </div>

    <div class="hero__scroll-hint" aria-hidden="true">↓</div>

</section>

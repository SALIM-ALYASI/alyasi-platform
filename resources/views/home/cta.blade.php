<section class="cta-band" id="contact" data-reveal>
    <span class="eyebrow">{{ __('home.cta_badge') }}</span>

    <h2 class="cta-band__title">
        {{ __('home.cta_title') }}
        <em>{{ __('home.cta_title_highlight') }}</em>
    </h2>

    <p class="cta-band__desc">{{ __('home.cta_description') }}</p>

    <div class="hero__ctas">
        <a href="{{ route('services.index') }}" class="btn btn--light">{{ __('home.cta_services') }}</a>
        <a href="{{ route('social-links.index') }}" class="btn btn--secondary">{{ __('home.cta_contact') }}</a>
    </div>
</section>

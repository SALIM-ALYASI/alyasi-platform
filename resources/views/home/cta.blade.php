<section
    class="section cta-section"
    id="contact"
>

    <div class="cta-card reveal">

        <p class="section-tag">
            {{ __('home.cta_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.cta_title') }}

            <strong>
                {{ __('home.cta_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.cta_description') }}
        </p>

        <div class="hero-ctas">

            <a
                href="{{ route('services.index') }}"
                class="btn-primary"
            >
                {{ __('home.cta_services') }}
            </a>

            <a
                href="{{ route('social-links.index') }}"
                class="btn-secondary"
            >
                {{ __('home.cta_contact') }}
            </a>

        </div>

    </div>

</section>
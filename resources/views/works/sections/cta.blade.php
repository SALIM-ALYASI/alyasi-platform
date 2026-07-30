<section class="works-cta-section">

    <div class="works-container">

        <div class="works-cta-card">

            <div class="works-cta-card__content">

                <span class="works-section-tag">
                    {{ __('works.cta.tag') }}
                </span>

                <h2>

                    {{ __('works.cta.title') }}

                    <strong>
                        {{ __('works.cta.highlight') }}
                    </strong>

                </h2>

                <p>
                    {{ __('works.cta.description') }}
                </p>

            </div>

            <div class="works-cta-card__actions">

                <a
                   href="{{ route('social-links.index') }}"
                    class="works-button works-button--gold"
                >
                    <i
                        class="fa-regular fa-paper-plane"
                        aria-hidden="true"
                    ></i>

                    {{ __('works.cta.contact') }}
                </a>

                <a
                    href="{{ route('services.index') }}"
                    class="works-button works-button--outline"
                >
                    {{ __('works.cta.services') }}
                </a>

            </div>

        </div>

    </div>

</section>
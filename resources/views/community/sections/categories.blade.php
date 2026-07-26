<section class="community-cta">
    <div class="container">

        <div class="community-cta__wrapper">

            <div class="community-cta__content">

                <span class="community-cta__badge">
                    {{ __('community.join_us') }}
                </span>

                <h2 class="community-cta__title">
                    {{ __('community.cta_title') }}
                </h2>

                <p class="community-cta__description">
                    {{ __('community.cta_description') }}
                </p>

            </div>

            <div class="community-cta__actions">

                <a
                    href="{{ route('contact') }}"
                    class="community-action-button community-action-button--primary"
                >
                    <i class="fa-solid fa-paper-plane"></i>

                    {{ __('community.contact_us') }}
                </a>

                <a
                    href="{{ route('services.index') }}"
                    class="community-action-button community-action-button--outline"
                >
                    <i class="fa-solid fa-briefcase"></i>

                    {{ __('home.services') }}
                </a>

            </div>

        </div>

    </div>
</section>
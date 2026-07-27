<section class="community-hero">
    <div class="container">

        <div class="community-hero__content">

            <span class="community-hero__badge">
                {{ __('community.badge') }}
            </span>

            <h1 class="community-hero__title">
                {{ __('community.title') }}
            </h1>

            <p class="community-hero__description">
                {{ __('community.description') }}
            </p>

            <div class="community-hero__actions">

                <a
                    href="#community-posts"
                    class="community-action-button community-action-button--primary"
                >
                    {{ __('community.explore') }}
                </a>

                <a
                    href="{{ route('social-links.index') }}"
                    class="community-action-button community-action-button--outline"
                >
                    {{ __('home.contact') }}
                </a>

            </div>

        </div>

    </div>
</section>
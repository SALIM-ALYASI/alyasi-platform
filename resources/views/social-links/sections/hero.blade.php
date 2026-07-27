<section class="social-hero">

    <div class="container">

        <div class="social-hero-content">

            <span class="social-hero-badge">

                <i class="fa-solid fa-share-nodes"></i>

                {{ __('social-links.hero_badge') }}

            </span>

            <h1 class="social-hero-title">

                {{ __('social-links.hero_title') }}

            </h1>

            <p class="social-hero-description">

                {{ __('social-links.hero_description') }}

            </p>

            @if(isset($socialLinks) && $socialLinks->isNotEmpty())

                <div class="social-hero-stats">

                    <div class="social-stat">

                        <strong>{{ $socialLinks->count() }}</strong>

                        <span>

                            {{ __('social-links.platforms') }}

                        </span>

                    </div>

                    <div class="social-stat">

                        <strong>24/7</strong>

                        <span>

                            {{ __('social-links.available') }}

                        </span>

                    </div>

                </div>

            @endif

        </div>

    </div>

</section>
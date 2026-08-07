<section
    class="section"
    id="community"
>

    <div class="cta-card cta-card--secondary reveal">

        <p class="section-tag">
            {{ __('home.community_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.community_title') }}

            <strong>
                {{ __('home.community_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.community_description') }}
        </p>

        <div class="hero-ctas">

            <a
                href="{{ route('community.index') }}"
                class="btn-primary"
            >
                {{ __('home.join_community') }}
            </a>

        </div>

    </div>

</section>

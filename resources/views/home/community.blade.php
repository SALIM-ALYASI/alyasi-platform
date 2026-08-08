<section class="section section--tight" id="community" data-reveal>
    <div class="section__inner">
        <div class="cta-band cta-band--card">
            <span class="eyebrow">{{ __('home.community_badge') }}</span>

            <h2 class="cta-band__title">
                {{ __('home.community_title') }}
                <em>{{ __('home.community_title_highlight') }}</em>
            </h2>

            <p class="cta-band__desc">{{ __('home.community_description') }}</p>

            <a href="{{ route('community.index') }}" class="btn btn--primary">{{ __('home.join_community') }}</a>
        </div>
    </div>
</section>

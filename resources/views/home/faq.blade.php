<section class="section section--narrow" id="faq" data-reveal>
    <div class="section__inner">

        <div class="section-head">
            <span class="eyebrow">{{ __('home.faq_badge') }}</span>
            <h2 class="section-title">
                {{ __('home.faq_title') }}
                <em>{{ __('home.faq_title_highlight') }}</em>
            </h2>
            <p class="section-body">{{ __('home.faq_description') }}</p>
        </div>

        <div class="faq-list">

            @foreach ($faqs as $index => $faq)

                <div class="faq-item {{ $index === 0 ? 'is-open' : '' }}">
                    <button type="button" class="faq-item__question">
                        <span>{{ $faq->localizedQuestion() }}</span>
                        <span class="faq-item__mark">{{ $index === 0 ? '−' : '+' }}</span>
                    </button>
                    <div class="faq-item__answer">
                        <p>{{ $faq->localizedAnswer() }}</p>
                    </div>
                </div>

            @endforeach

        </div>

    </div>
</section>

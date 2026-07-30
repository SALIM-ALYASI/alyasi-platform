<section
    class="section"
    id="faq"
>

    <div class="section-heading reveal">

        <p class="section-tag">
            {{ __('home.faq_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.faq_title') }}
            <strong>
                {{ __('home.faq_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.faq_description') }}
        </p>

    </div>

    <div class="faq-list">

        @foreach ($faqs as $index => $faq)

            <details
                class="faq-item"
                @if ($index === 0) open @endif
            >

                <summary>
                    {{ $faq->localizedQuestion() }}
                </summary>

                <p>
                    {{ $faq->localizedAnswer() }}
                </p>

            </details>

        @endforeach

    </div>

</section>

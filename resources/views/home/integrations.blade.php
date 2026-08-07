<section
    class="section"
    id="technology"
>

    <div class="section-heading reveal section-center">

        <p class="section-tag">
            {{ __('home.technology_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.technology_title') }}

            <strong>
                {{ __('home.technology_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.technology_description') }}
        </p>

    </div>

    @if ($technologies->isNotEmpty())

        <div class="integrations-grid">

            @foreach ($technologies as $technology)

                <article class="integration-item reveal">

                    <div class="integration-icon">

                        <i
                            class="{{ $technology->icon }}"
                            aria-hidden="true"
                        ></i>

                    </div>

                    <div class="integration-name">
                        {{ $technology->name }}
                    </div>

                </article>

            @endforeach

        </div>

    @else

        <p class="section-empty reveal">
            {{ __('home.technology_empty') }}
        </p>

    @endif

</section>
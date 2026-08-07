<section
    class="section"
    id="portfolio"
>

    <div class="section-heading reveal section-center">

        <p class="section-tag">
            {{ __('home.portfolio_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.portfolio_title') }}

            <strong>
                {{ __('home.portfolio_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.portfolio_description') }}
        </p>

    </div>

    @if ($works->isNotEmpty())

        <div class="portfolio-grid">

            @foreach ($works as $work)

                <a
                    href="{{ route('works.show', $work) }}"
                    class="portfolio-card reveal"
                >

                    <div class="portfolio-card-image">

                        <img
                            src="{{ $work->cover_image
                                ? asset($work->cover_image)
                                : asset('luminary/images/tm-luminary-02.jpg') }}"
                            alt="{{ $work->title }}"
                            loading="lazy"
                        >

                    </div>

                    <div class="portfolio-card-caption">

                        <span class="portfolio-card-type">
                            {{ $work->type_label }}
                        </span>

                        <h3>
                            {{ $work->title }}
                        </h3>

                    </div>

                </a>

            @endforeach

        </div>

        <div class="section-footer-cta reveal">

            <a
                href="{{ route('works.index') }}"
                class="btn-secondary"
            >
                {{ __('home.all_projects') }}
            </a>

        </div>

    @else

        <p class="section-empty reveal">
            {{ __('home.portfolio_empty') }}
        </p>

    @endif

</section>

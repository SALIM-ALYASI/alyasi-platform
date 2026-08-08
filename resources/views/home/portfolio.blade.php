<section class="section section--dark" id="portfolio" data-reveal>
    <div class="section__inner">

        <div class="section-head section-head--split">
            <div>
                <span class="eyebrow">{{ __('home.portfolio_badge') }}</span>
                <h2 class="section-title">
                    {{ __('home.portfolio_title') }}
                    <em>{{ __('home.portfolio_title_highlight') }}</em>
                </h2>
            </div>
            <a href="{{ route('works.index') }}" class="link-arrow">{{ __('home.all_projects') }} ←</a>
        </div>

        @if ($works->isNotEmpty())

            <div class="portfolio-grid">

                @foreach ($works as $work)

                    <a href="{{ route('works.show', $work) }}" class="portfolio-card">

                        @if ($work->cover_image)
                            <img src="{{ asset($work->cover_image) }}" alt="{{ $work->title }}" loading="lazy">
                        @else
                            <div class="portfolio-card__placeholder" aria-hidden="true"></div>
                        @endif

                        <div class="portfolio-card__caption">
                            <span class="portfolio-card__type">{{ $work->type_label }}</span>
                            <h3>{{ $work->title }}</h3>
                        </div>

                    </a>

                @endforeach

            </div>

        @else

            <p class="section-body">{{ __('home.portfolio_empty') }}</p>

        @endif

    </div>
</section>

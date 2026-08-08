<section class="section section--muted" id="technology" data-reveal>
    <div class="section__inner">

        <div class="section-head">
            <span class="eyebrow">{{ __('home.technology_badge') }}</span>
            <h2 class="section-title">
                {{ __('home.technology_title') }}
                <em>{{ __('home.technology_title_highlight') }}</em>
            </h2>
            <p class="section-body">{{ __('home.technology_description') }}</p>
        </div>

        @if ($technologies->isNotEmpty())

            <div class="tech-pills">
                @foreach ($technologies as $technology)
                    <span class="tech-pill">
                        <i class="{{ $technology->icon }}" aria-hidden="true"></i>
                        {{ $technology->name }}
                    </span>
                @endforeach
            </div>

        @else

            <p class="section-body">{{ __('home.technology_empty') }}</p>

        @endif

    </div>
</section>

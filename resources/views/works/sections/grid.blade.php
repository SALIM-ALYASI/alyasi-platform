<section
    class="works-list-section"
    id="works-list"
>

    <div class="works-container">

        <div class="works-section-heading">

            <div>

                <span class="works-section-tag">
                    {{ __('works.grid.tag') }}
                </span>

                <h2 class="works-section-title">
                    {{ __('works.grid.title') }}
                </h2>

                <p class="works-section-description">
                    {{ __('works.grid.description') }}
                </p>

            </div>

            <div class="works-result-count">

                <strong>
                    {{ $works->total() }}
                </strong>

                <span>
                    {{ trans_choice(
                        'works.grid.results_count',
                        $works->total(),
                        ['count' => $works->total()]
                    ) }}
                </span>

            </div>

        </div>

        @if ($works->count())

            <div class="works-grid">

                @foreach ($works as $work)

                    @php
                        $typeLabel = __(
                            'works.types.' . $work->type
                        );

                        if (
                            $typeLabel
                            === 'works.types.' . $work->type
                        ) {
                            $typeLabel = $types[$work->type]
                                ?? $work->type;
                        }

                        $serviceTitle = $work->service
                            ? $work->service->localizedTitle()
                            : null;

                        $detailsLabel = __(
                            'works.grid.view_details_for',
                            ['title' => $work->title]
                        );
                    @endphp

                    <article class="public-work-card">

                        <a
                            href="{{ route('works.show', $work) }}"
                            class="public-work-card__media"
                            aria-label="{{ $detailsLabel }}"
                        >

                            @if ($work->cover_image)

                                <img
                                    src="{{ asset(
                                        'storage/' . $work->cover_image
                                    ) }}"
                                    alt="{{ $work->title }}"
                                    class="public-work-card__image"
                                    loading="lazy"
                                >

                            @else

                                <div
                                    class="public-work-card__placeholder"
                                >

                                    <img
                                        src="{{ asset(
                                            'images/logo/logo-icon.png'
                                        ) }}"
                                        alt="ALYASI"
                                    >

                                </div>

                            @endif

                            <div class="public-work-card__overlay">

                                <span>

                                    <i
                                        class="fa-regular fa-eye"
                                        aria-hidden="true"
                                    ></i>

                                    {{ __('works.grid.view_details') }}

                                </span>

                            </div>

                            <span class="public-work-card__type">
                                {{ $typeLabel }}
                            </span>

                            @if ($work->is_featured)

                                <span
                                    class="public-work-card__featured"
                                >

                                    <i
                                        class="fa-solid fa-star"
                                        aria-hidden="true"
                                    ></i>

                                    {{ __('works.grid.featured') }}

                                </span>

                            @endif

                        </a>

                        <div class="public-work-card__body">

                            @if ($serviceTitle)

                                <span
                                    class="public-work-card__service"
                                >

                                    <i
                                        class="fa-solid fa-briefcase"
                                        aria-hidden="true"
                                    ></i>

                                    {{ $serviceTitle }}

                                </span>

                            @endif

                            <h3 class="public-work-card__title">

                                <a
                                    href="{{ route(
                                        'works.show',
                                        $work
                                    ) }}"
                                >
                                    {{ $work->title }}
                                </a>

                            </h3>

                            @if ($work->short_description)

                                <p
                                    class="public-work-card__description"
                                >
                                    {{ \Illuminate\Support\Str::limit(
                                        $work->short_description,
                                        145
                                    ) }}
                                </p>

                            @endif

                            @if ($work->technologies->isNotEmpty())

                                <div
                                    class="public-work-card__technologies"
                                >

                                    @foreach (
                                        $work->technologies->take(3)
                                        as $technology
                                    )

                                        <span>
                                            {{ $technology->name }}
                                        </span>

                                    @endforeach

                                    @if (
                                        $work->technologies->count() > 3
                                    )

                                        <span>
                                            +{{
                                                $work
                                                    ->technologies
                                                    ->count() - 3
                                            }}
                                        </span>

                                    @endif

                                </div>

                            @endif

                        </div>

                        <div class="public-work-card__footer">

                            <div class="public-work-card__info">

                                @if ($work->completed_at)

                                    <span>

                                        <i
                                            class="fa-regular fa-calendar"
                                            aria-hidden="true"
                                        ></i>

                                        {{
                                            $work
                                                ->completed_at
                                                ->format('Y/m/d')
                                        }}

                                    </span>

                                @endif

                                @if ($work->client_name)

                                    <span>

                                        <i
                                            class="fa-regular fa-user"
                                            aria-hidden="true"
                                        ></i>

                                        {{ $work->client_name }}

                                    </span>

                                @endif

                            </div>

                            <a
                                href="{{ route(
                                    'works.show',
                                    $work
                                ) }}"
                                class="public-work-card__link"
                                aria-label="{{ $detailsLabel }}"
                            >
                                <i
                                    class="fa-solid {{ app()->isLocale('ar')
                                        ? 'fa-arrow-left'
                                        : 'fa-arrow-right' }}"
                                    aria-hidden="true"
                                ></i>
                            </a>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($works->hasPages())

                <div class="works-pagination">
                    {{ $works->links() }}
                </div>

            @endif

        @else

            <div class="works-empty">

                <span class="works-empty__icon">

                    <i
                        class="fa-solid fa-folder-open"
                        aria-hidden="true"
                    ></i>

                </span>

                <h3>
                    {{ __('works.grid.empty_title') }}
                </h3>

                <p>
                    {{ __('works.grid.empty_description') }}
                </p>

                <a
                    href="{{ route('works.index') }}"
                    class="works-button works-button--primary"
                >
                    <i
                        class="fa-solid fa-rotate-left"
                        aria-hidden="true"
                    ></i>

                    {{ __('works.grid.show_all') }}
                </a>

            </div>

        @endif

    </div>

</section>
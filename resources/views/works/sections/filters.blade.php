<section class="works-filter-section">

    <div class="works-container">

        <div class="works-filter-card">

            <div class="works-filter-card__header">

                <div>

                    <span class="works-section-tag">
                        {{ __('works.filters.tag') }}
                    </span>

                    <h2>
                        {{ __('works.filters.title') }}
                    </h2>

                </div>

                @if (request()->hasAny([
                    'search',
                    'type',
                    'service',
                ]))

                    <a
                        href="{{ route('works.index') }}"
                        class="works-filter-reset"
                    >
                        <i
                            class="fa-solid fa-rotate-left"
                            aria-hidden="true"
                        ></i>

                        {{ __('works.filters.reset') }}
                    </a>

                @endif

            </div>

            <form
                method="GET"
                action="{{ route('works.index') }}"
                class="works-filter-form"
            >

                <div class="works-filter-group works-filter-group--search">

                    <label
                        for="works-search"
                        class="works-filter-label"
                    >
                        {{ __('works.filters.search_label') }}
                    </label>

                    <div class="works-filter-control">

                        <i
                            class="fa-solid fa-magnifying-glass"
                            aria-hidden="true"
                        ></i>

                        <input
                            type="search"
                            id="works-search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="{{ __('works.filters.search_placeholder') }}"
                        >

                    </div>

                </div>

                <div class="works-filter-group">

                    <label
                        for="works-type"
                        class="works-filter-label"
                    >
                        {{ __('works.filters.type_label') }}
                    </label>

                    <div class="works-filter-control">

                        <i
                            class="fa-solid fa-layer-group"
                            aria-hidden="true"
                        ></i>

                        <select
                            id="works-type"
                            name="type"
                        >

                            <option value="">
                                {{ __('works.filters.all_types') }}
                            </option>

                            @foreach ($types as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected(request('type') === $value)
                                >
                                    {{ __('works.types.' . $value) }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="works-filter-group">

                    <label
                        for="works-service"
                        class="works-filter-label"
                    >
                        {{ __('works.filters.service_label') }}
                    </label>

                    <div class="works-filter-control">

                        <i
                            class="fa-solid fa-briefcase"
                            aria-hidden="true"
                        ></i>

                        <select
                            id="works-service"
                            name="service"
                        >

                            <option value="">
                                {{ __('works.filters.all_services') }}
                            </option>

                            @foreach ($services as $service)

                                <option
                                    value="{{ $service->id }}"
                                    @selected(
                                        (string) request('service')
                                        === (string) $service->id
                                    )
                                >
                                    {{ $service->localizedTitle() }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="works-filter-actions">

                    <button
                        type="submit"
                        class="works-button works-button--primary"
                    >
                        <i
                            class="fa-solid fa-filter"
                            aria-hidden="true"
                        ></i>

                        {{ __('works.filters.apply') }}
                    </button>

                </div>

            </form>

        </div>

    </div>

</section>
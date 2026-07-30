<section class="works-hero">

    <div class="works-container">

        <div class="works-hero__content">

            <span class="works-section-tag">

                <i
                    class="fa-solid fa-briefcase"
                    aria-hidden="true"
                ></i>

                {{ __('works.hero.tag') }}

            </span>

            <h1 class="works-hero__title">

                {{ __('works.hero.title') }}

                <strong>
                    {{ __('works.hero.highlight') }}
                </strong>

            </h1>

            <p class="works-hero__description">
                {{ __('works.hero.description') }}
            </p>

            <div class="works-hero__actions">

                <a
                    href="#works-list"
                    class="works-button works-button--primary"
                >
                    <i
                        class="fa-solid fa-images"
                        aria-hidden="true"
                    ></i>

                    {{ __('works.hero.view_works') }}
                </a>

                <a
                    href="{{ route('contact') }}"
                    class="works-button works-button--secondary"
                >
                    <i
                        class="fa-regular fa-paper-plane"
                        aria-hidden="true"
                    ></i>

                    {{ __('works.hero.start_project') }}
                </a>

            </div>

        </div>

        <div class="works-hero__visual">

            <div class="works-hero__visual-card">

                <span class="works-hero__visual-icon">

                    <i
                        class="fa-solid fa-layer-group"
                        aria-hidden="true"
                    ></i>

                </span>

                <strong>
                    {{ $works->total() }}
                </strong>

                <span>
                    {{ trans_choice(
                        'works.hero.projects_count',
                        $works->total(),
                        ['count' => $works->total()]
                    ) }}
                </span>

            </div>

            <div
                class="works-hero__shape works-hero__shape--one"
                aria-hidden="true"
            ></div>

            <div
                class="works-hero__shape works-hero__shape--two"
                aria-hidden="true"
            ></div>

        </div>

    </div>

</section>
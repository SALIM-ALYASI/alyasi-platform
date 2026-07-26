<section class="community-posts" id="community-posts">
    <div class="container">

        <div class="section-heading">

            <span class="section-heading__badge">
                {{ __('community.latest') }}
            </span>

            <h2 class="section-heading__title">
                {{ __('community.latest_posts') }}
            </h2>

            <p class="section-heading__description">
                {{ __('community.latest_posts_description') }}
            </p>

        </div>

        @if($posts->isNotEmpty())

            <div class="community-posts__grid">

                @foreach($posts as $post)

                    <article class="community-post-card">

                        <a
                            href="{{ route('community.show', $post) }}"
                            class="community-post-card__image"
                            aria-label="{{ $post->title }}"
                        >
                            <img
                                src="{{ $post->image
                                    ? asset('storage/' . $post->image)
                                    : asset('images/logo/logo-dark.png') }}"
                                class="{{ $post->image ? '' : 'is-placeholder' }}"
                                alt="{{ $post->title }}"
                                loading="lazy"
                            >

                            <span class="community-post-card__type">
                                {{ __('community.types.' . $post->type) }}
                            </span>
                        </a>

                        <div class="community-post-card__content">

                            <div class="community-post-card__meta">

                                @if($post->category)
                                    <a
                                        href="{{ route('community.index', ['category' => $post->category->slug]) }}"
                                        class="community-post-card__category"
                                    >
                                        {{ $post->category->name }}
                                    </a>
                                @endif

                                @if($post->published_at)
                                    <time
                                        datetime="{{ $post->published_at->toIso8601String() }}"
                                        class="community-post-card__date"
                                    >
                                        <i class="fa-regular fa-calendar"></i>

                                        {{ $post->published_at->translatedFormat('d M Y') }}
                                    </time>
                                @endif

                            </div>

                            <h3 class="community-post-card__title">
                                <a href="{{ route('community.show', $post) }}">
                                    {{ $post->title }}
                                </a>
                            </h3>

                            <p class="community-post-card__description">
                                {{ $post->short_description }}
                            </p>

                            @if(
                                $post->type === 'event'
                                && (
                                    $post->event_start_at
                                    || $post->location
                                )
                            )
                                <div class="community-post-card__event">

                                    @if($post->event_start_at)
                                        <span>
                                            <i class="fa-regular fa-clock"></i>

                                            {{ $post->event_start_at->translatedFormat('d M Y - h:i A') }}
                                        </span>
                                    @endif

                                    @if($post->location)
                                        <span>
                                            <i class="fa-solid fa-location-dot"></i>

                                            {{ $post->location }}
                                        </span>
                                    @endif

                                </div>
                            @endif

                            <div class="community-post-card__footer">

                                <a
                                    href="{{ route('community.show', $post) }}"
                                    class="community-post-card__link"
                                >
                                    <span>{{ __('community.read_more') }}</span>

                                    <i class="fa-solid fa-arrow-left-long"></i>
                                </a>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

            @if($posts->hasPages())
                <div class="community-posts__pagination">
                    {{ $posts->links('vendor.pagination.community') }}
                </div>
            @endif

        @else

            <div class="community-posts-empty">

                <span class="community-posts-empty__icon">
                    <i class="fa-regular fa-folder-open"></i>
                </span>

                <h3 class="community-posts-empty__title">
                    {{ __('community.empty_title') }}
                </h3>

                <p class="community-posts-empty__description">
                    {{ __('community.empty_description') }}
                </p>

                @if(request()->hasAny(['category', 'type']))
                    <a
                        href="{{ route('community.index') }}"
                        class="community-posts-empty__link"
                    >
                        {{ __('community.show_all') }}
                    </a>
                @endif

            </div>

        @endif

    </div>
</section>
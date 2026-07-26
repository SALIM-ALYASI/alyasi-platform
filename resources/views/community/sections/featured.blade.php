@if($featuredPosts->isNotEmpty())
<section class="community-featured">

    <div class="container">

        <div class="section-heading">

            <span class="section-heading__badge">
                {{ __('community.featured') }}
            </span>

            <h2 class="section-heading__title">
                {{ __('community.featured_posts') }}
            </h2>

        </div>

        <div class="community-featured__grid">

            @foreach($featuredPosts as $post)

                <article class="community-featured-card">

                    <a
                        href="{{ route('community.show', $post) }}"
                        class="community-featured-card__image"
                    >
                        <img
                          src="{{ $post->image
    ? asset('storage/' . $post->image)
    : asset('images/logo/logo-dark.png') }}"
                            alt="{{ $post->title }}"
                            loading="lazy"
                        >
                    </a>

                    <div class="community-featured-card__content">

                        <div class="community-featured-card__meta">

                            @if($post->category)
                                <span class="community-featured-card__category">
                                    {{ $post->category->name }}
                                </span>
                            @endif

                            <span class="community-featured-card__type">
                                {{ __('community.types.'.$post->type) }}
                            </span>

                        </div>

                        <h3 class="community-featured-card__title">
                            <a href="{{ route('community.show', $post) }}">
                                {{ $post->title }}
                            </a>
                        </h3>

                        <p class="community-featured-card__description">
                            {{ $post->short_description }}
                        </p>

                        <div class="community-featured-card__footer">

                            @if($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}">
                                    {{ $post->published_at->translatedFormat('d M Y') }}
                                </time>
                            @endif

                            <a
                                href="{{ route('community.show', $post) }}"
                                class="community-featured-card__link"
                            >
                                {{ __('community.read_more') }}

                                <i class="fa-solid fa-arrow-left-long"></i>
                            </a>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

    </div>

</section>
@endif
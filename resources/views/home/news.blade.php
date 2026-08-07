<section
    class="section"
    id="news"
>

    <div class="section-heading reveal section-center">

        <p class="section-tag">
            {{ __('home.news_badge') }}
        </p>

        <h2 class="section-title">
            {{ __('home.news_title') }}

            <strong>
                {{ __('home.news_title_highlight') }}
            </strong>
        </h2>

        <p class="section-body">
            {{ __('home.news_description') }}
        </p>

    </div>

    @if ($latestNews->isNotEmpty())

        <div class="news-teaser-list">

            @foreach ($latestNews as $index => $article)

                @php
                    $slug = $article->permalinks
                        ->firstWhere('locale', app()->getLocale())
                        ?->slug
                        ?? $article->permalinks->first()?->slug;
                @endphp

                <a
                    href="{{ $slug ? route('news.show', $slug) : route('news.index') }}"
                    class="news-teaser-item reveal"
                >

                    <span class="news-teaser-index">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <span class="news-teaser-title">
                        {{ $article->title }}
                    </span>

                    @if ($article->category)
                        <span class="news-teaser-tag">
                            {{ $article->category->name }}
                        </span>
                    @endif

                    <span class="news-teaser-arrow" aria-hidden="true">
                        ←
                    </span>

                </a>

            @endforeach

        </div>

        <div class="section-footer-cta reveal">

            <a
                href="{{ route('news.index') }}"
                class="btn-secondary"
            >
                {{ __('home.all_news') }}
            </a>

        </div>

    @else

        <p class="section-empty reveal">
            {{ __('home.news_empty') }}
        </p>

    @endif

</section>

<section class="section" id="news" data-reveal>
    <div class="section__inner">

        <div class="section-head section-head--split">
            <div>
                <span class="eyebrow">{{ __('home.news_badge') }}</span>
                <h2 class="section-title">
                    {{ __('home.news_title') }}
                    <em>{{ __('home.news_title_highlight') }}</em>
                </h2>
            </div>
            <a href="{{ route('news.index') }}" class="link-arrow">{{ __('home.all_news') }} ←</a>
        </div>

        @if ($latestNews->isNotEmpty())

            <div class="news-list">

                @foreach ($latestNews as $index => $article)

                    @php
                        $slug = $article->permalinks
                            ->firstWhere('locale', app()->getLocale())
                            ?->slug
                            ?? $article->permalinks->first()?->slug;
                    @endphp

                    <a href="{{ $slug ? route('news.show', $slug) : route('news.index') }}" class="news-item">
                        <span class="news-item__index">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="news-item__body">
                            <span class="news-item__title">{{ $article->title }}</span>
                            @if ($article->category)
                                <span class="news-item__tag">{{ $article->category->name }}</span>
                            @endif
                        </span>
                        <span class="news-item__arrow" aria-hidden="true">←</span>
                    </a>

                @endforeach

            </div>

        @else

            <p class="section-body">{{ __('home.news_empty') }}</p>

        @endif

    </div>
</section>

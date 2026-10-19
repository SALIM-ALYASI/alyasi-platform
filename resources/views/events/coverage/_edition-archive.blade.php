{{-- صفحة CyberX Oman 2026 بعد انتهاء المؤتمر: أرشيف التغطية بدل الجدول والتفاصيل --
     المحطات الخمس (كل بطاقة تفتح محطتها بصفحة التغطية)، وتحتها اللقاءات المسجّلة المنشورة،
     ثم مقالات سالم عن المؤتمر. --}}
@php
    $isEn = app()->getLocale() === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $archiveStops = \App\Support\Coverage\CyberxOman2026::content($isEn ? 'en' : 'ar')['stops'];
    // ترتيب عشوائي بكل زيارة -- ما فيه ضيف ثابت أول القائمة.
    $archiveGuests = collect(\App\Support\Coverage\CyberxInterviews::guests($isEn ? 'en' : 'ar'))
        ->filter(fn ($guest) => $guest['published'])
        ->shuffle()
        ->values()
        ->all();
    $coverageLink = localized_route('event_coverage.cyberx_oman_2026');

    // مقالات المؤتمر بالترتيب (قبله ثم بعده)، بالرابط العربي الدائم. المنشور بلغة الصفحة بس.
    $archiveArticleSlugs = ['before-the-guardians-gather', 'from-the-corner-cyberx-oman-2026'];
    $archiveArticles = \App\Models\Article::query()
        ->published()
        ->availableIn($isEn ? 'en' : 'ar')
        ->whereHas('permalinks', fn ($q) => $q->where('locale', 'ar')->whereIn('slug', $archiveArticleSlugs))
        ->with('permalinks')
        ->get()
        ->sortBy(fn ($article) => array_search($article->slug('ar'), $archiveArticleSlugs, true))
        ->values();
@endphp

<p class="cyberx-archive__intro">
    {{ $t('انعقد المؤتمر في 6 أكتوبر 2026 بمسقط. هنا تغطية منصة الياسي له: خمس محطات، ولقاءات مسجّلة مع الضيوف.', 'The conference took place on 6 October 2026 in Muscat. Here is ALYASI’s coverage: five stops, and recorded interviews with the guests.') }}
</p>

<section class="cyberx-archive">
    <div class="cyberx-archive__head">
        <h2>{{ $t('خمس محطات', 'Five Stops') }}</h2>
        <a href="{{ $coverageLink }}">{{ $t('التغطية كاملة', 'Full coverage') }} {{ $isEn ? '→' : '←' }}</a>
    </div>

    <ol class="cyberx-stops">
        @foreach ($archiveStops as $i => $stop)
            <li>
                <a class="cyberx-stop" href="{{ $coverageLink }}?stop={{ $i + 1 }}">
                    <span class="cyberx-stop__num">{{ sprintf('%02d', $i + 1) }}</span>
                    <span class="cyberx-stop__photo {{ $stop['group'] ? 'is-group' : '' }}"><img src="{{ $stop['image'] }}" alt="" loading="lazy"></span>
                    <span class="cyberx-stop__body">
                        <span class="cyberx-stop__who">{{ $stop['name'] }}</span>
                        <strong>{{ $stop['headline'] }}</strong>
                    </span>
                </a>
            </li>
        @endforeach
    </ol>
</section>

@if ($archiveGuests)
    <section class="cyberx-archive">
        <div class="cyberx-archive__head">
            <h2>{{ $t('لقاءات مسجّلة', 'Recorded interviews') }}</h2>
        </div>

        <div class="cyberx-interviews">
            @foreach ($archiveGuests as $guest)
                <a class="cyberx-interview"
                   href="{{ route(in_array($isEn ? 'en' : 'ar', $guest['languages'], true) && $isEn ? 'event_coverage.cyberx_oman_2026.interview.en' : 'event_coverage.cyberx_oman_2026.interview', ['guest' => $guest['slug']]) }}">
                    @include('events.coverage.interviews._avatar', ['guest' => $guest, 'size' => 56])
                    <span class="cyberx-interview__body">
                        <strong>{{ $guest['name'] }}</strong>
                        <small>{{ $guest['role'] }}</small>
                        @if ($guest['quote'])
                            <span class="cyberx-interview__quote">«{{ $guest['quote'] }}»</span>
                        @endif
                    </span>
                    <span class="cyberx-interview__play" aria-hidden="true">▶</span>
                </a>
            @endforeach
        </div>
    </section>
@endif

@if ($archiveArticles->isNotEmpty())
    <section class="cyberx-archive">
        <div class="cyberx-archive__head">
            <h2>{{ $t('مقالات', 'Articles') }}</h2>
        </div>

        <div class="cyberx-articles">
            @foreach ($archiveArticles as $article)
                <a class="cyberx-article" href="{{ article_route('show', [$article->slug()]) }}">
                    @if ($article->displayImage())
                        <span class="cyberx-article__photo"><img src="{{ media_url($article->displayImage()) }}" alt="" loading="lazy"></span>
                    @endif
                    <span class="cyberx-article__body">
                        <strong>{{ $article->title }}</strong>
                        @if ($article->excerpt)
                            <span class="cyberx-article__excerpt">{{ $article->excerpt }}</span>
                        @endif
                        <span class="cyberx-article__more">{{ $t('اقرأ المقال', 'Read the article') }} {{ $isEn ? '→' : '←' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@endif

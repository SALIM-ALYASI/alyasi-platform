{{-- «تغطية مميزة»: بطاقة واحدة للرئيسية وصفحة المؤتمرات. المصدر EventsHub::featured()
     (مثبّتة من لوحة التحكم، وإلا أحدث نسخة نُشر لها محتوى حصري). --}}
@if ($featured)
    @php
        $fEdition = $featured->edition;
        $fUrl = $fEdition->publicUrl();
        $fImage = $fEdition->displayImage() ?: $featured->event->image;
        $fInterviews = $featured->highlights['interviews'];
        $headingId = $headingId ?? 'events-featured-title';
    @endphp
    <section class="container events-featured {{ $sectionClass ?? '' }}" aria-labelledby="{{ $headingId }}">
        <h2 class="events-featured__eyebrow" id="{{ $headingId }}">{{ __('events.featured_title') }}</h2>

        <article class="events-featured__card" data-reveal>
            <a class="events-featured__media" href="{{ $fUrl }}" tabindex="-1" aria-hidden="true">
                <img src="{{ $fImage ? media_url($fImage) : asset('images/events/og-cover.jpg') }}" @if ($fImage && ($srcset = media_srcset($fImage))) srcset="{{ $srcset }}" sizes="(max-width: 767px) 92vw, 560px" @endif alt="" loading="lazy" decoding="async" width="1200" height="630">
            </a>

            <div class="events-featured__body">
                @if ($date = $fEdition->dateRangeLabel())
                    <div class="event-card__date">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <span class="visually-hidden">{{ __('events.event_date') }}:</span>
                        <time datetime="{{ $fEdition->event_start_at->copy()->timezone('Asia/Muscat')->toDateString() }}">{{ $date }}</time>
                    </div>
                @endif

                <h3 class="events-featured__title"><a href="{{ $fUrl }}">{{ $fEdition->title }}</a></h3>

                @if ($text = $featured->event->localizedDescription() ?: $fEdition->short_description)
                    <p class="events-featured__text">{{ $text }}</p>
                @endif

                @if ($featured->highlights['tags'])
                    <ul class="event-card__tags">
                        @foreach ($featured->highlights['tags'] as $tag)
                            <li>{{ $tag }}</li>
                        @endforeach
                    </ul>
                @endif

                @if ($fInterviews->isNotEmpty())
                    <span class="events-exclusive__faces" aria-hidden="true">
                        @foreach ($fInterviews->take(6) as $interview)
                            @include('events.coverage.interviews._avatar', ['guest' => $interview->avatar(), 'size' => 34])
                        @endforeach
                    </span>
                @endif

                <div class="events-featured__actions">
                    @foreach ($featured->highlights['links'] as $k => $link)
                        <a class="btn {{ $k === 0 ? 'btn--primary' : 'btn--outline' }}" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                    <a class="btn {{ $featured->highlights['links'] ? 'btn--outline' : 'btn--primary' }}" href="{{ $fUrl }}">{{ __('events.featured_page') }}</a>
                </div>
            </div>
        </article>
    </section>
@endif

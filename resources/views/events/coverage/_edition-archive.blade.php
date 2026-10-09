{{-- صفحة CyberX Oman 2026 بعد انتهاء المؤتمر: أرشيف التغطية بدل الجدول والتفاصيل --
     المحطات الخمس (كل بطاقة تفتح محطتها بصفحة التغطية)، وتحتها اللقاءات المسجّلة المنشورة. --}}
@php
    $isEn = app()->getLocale() === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $archiveStops = \App\Support\Coverage\CyberxOman2026::content($isEn ? 'en' : 'ar')['stops'];
    $archiveGuests = array_values(array_filter(
        \App\Support\Coverage\CyberxInterviews::guests($isEn ? 'en' : 'ar'),
        fn ($guest) => $guest['published']
    ));
    $coverageLink = localized_route('event_coverage.cyberx_oman_2026');
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

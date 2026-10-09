@extends('layouts.app')

@php
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $pageTitle = $guest['name'].' — '.$t('لقاء من سايبر إكس عُمان 2026', 'Interview from CyberX Oman 2026');
    $jsData = [
        'items' => array_map(fn ($item) => [
            'q' => $item['q_audio'],
            'a' => $item['a_audio'],
            'segments' => array_column($item['segments'], 't'),
        ], $guest['items']),
        'labels' => [
            'question' => $t('سؤال', 'Question'),
            'answer' => $guest['name'],
            'host' => $host['name'],
        ],
    ];
@endphp

@section('title', $pageTitle.' — ALYASI')
@section('meta_description', $guest['intro'])
@section('canonical', $isEn ? $urlEn : $urlAr)
@if (in_array('ar', $guest['languages'], true) && in_array('en', $guest['languages'], true))
    @section('hreflang_ar', $urlAr)
    @section('hreflang_en', $urlEn)
@endif
@section('og_type', 'article')
@section('og_title', $pageTitle)
@section('og_description', $guest['intro'])
@section('og_url', $isEn ? $urlEn : $urlAr)
@section('og_image', $guest['og_image'] ?? $guest['photo'] ?? asset('images/events/cyberx-2026/coverage-hero.jpg'))
@if ($guest['og_image'])
    @section('og_image_width', 1200)
    @section('og_image_height', 630)
@endif

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/event-interviews.css') }}">
@endpush

@section('content')
    <article class="interview container">
        <nav class="interview__crumbs" aria-label="{{ $t('مسار التصفح', 'Breadcrumb') }}">
            <a href="{{ $coverageUrl }}">{{ $t('تغطية سايبر إكس عُمان 2026', 'CyberX Oman 2026 coverage') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ $hubUrl }}">{{ $t('اللقاءات', 'Interviews') }}</a>
        </nav>

        {{-- رأس المقابلة: الضيف، والمقدمة بقلم سالم، وتشغيل المقابلة كاملة --}}
        <header class="interview__head" data-reveal>
            @include('events.coverage.interviews._avatar', ['guest' => $guest, 'size' => 96])
            <span class="interview__kicker">{{ $t('لقاء صحفي · CyberX Oman 2026', 'Interview · CyberX Oman 2026') }}</span>
            <h1 class="interview__name">{{ $guest['name'] }}</h1>
            <p class="interview__role">{{ $guest['role'] }}</p>

            <div class="interview__intro">
                <img src="{{ $host['avatar'] }}" alt="" width="40" height="40">
                <p>{{ $guest['intro'] }}</p>
            </div>

            <div class="interview__actions">
                <button type="button" class="btn btn--primary interview__play-all" id="interviewPlayAll">
                    <span class="interview__play-all-icon" aria-hidden="true">▶</span>
                    <span data-label-play>{{ $t('استمع للمقابلة كاملة', 'Play the full interview') }}</span>
                    <span data-label-pause hidden>{{ $t('إيقاف مؤقت', 'Pause') }}</span>
                </button>
                <span class="interview__length">{{ count($guest['items']) }} {{ $t('أسئلة', 'questions') }} · {{ $guest['minutes'] }} {{ $t('دقائق', 'min') }}</span>
            </div>

            @if ($isEn)
                <p class="interview__note">The answers were recorded in Arabic. The English text is a translation that follows the audio.</p>
            @endif
        </header>

        {{-- الأسئلة والإجابات --}}
        <ol class="interview__qa">
            @foreach ($guest['items'] as $i => $item)
                <li class="qa" id="q{{ $i + 1 }}" data-reveal>
                    <div class="qa__question">
                        <img class="qa__host" src="{{ $host['avatar'] }}" alt="" width="36" height="36">
                        <div class="qa__bubble">
                            <span class="qa__label">{{ $t('سؤال', 'Question') }} {{ $i + 1 }} · {{ $host['name'] }}</span>
                            <p>{{ $item['question'] }}</p>
                            <button type="button" class="qa__mini" data-play="q" data-item="{{ $i }}"
                                    aria-label="{{ $t('استمع للسؤال', 'Play the question') }}">
                                <span class="qa__mini-icon" aria-hidden="true">▶</span> {{ $t('بصوتي', 'In my voice') }}
                            </button>
                        </div>
                    </div>

                    {{-- الإجابة مرآة السؤال: فقاعة الضيف وصورته بالجهة المقابلة --}}
                    <div class="qa__answer">
                        <div class="qa__answer-bubble">
                            <span class="qa__label">{{ $t('إجابة', 'Answer') }} {{ $i + 1 }} · {{ $guest['name'] }}</span>

                            <div class="qa__player">
                                <button type="button" class="qa__play" data-play="a" data-item="{{ $i }}"
                                        aria-label="{{ $t('استمع للإجابة', 'Play the answer') }}">
                                    <span class="qa__play-icon" aria-hidden="true">▶</span>
                                </button>
                                <span class="qa__bar"><span class="qa__fill" data-fill="{{ $i }}"></span></span>
                                <span class="qa__time" data-time="{{ $i }}">{{ gmdate('i:s', (int) round($item['duration'])) }}</span>
                            </div>

                            <p class="qa__text">
                                @foreach ($item['segments'] as $s => $segment)
                                    <span class="qa__seg" data-item="{{ $i }}" data-seg="{{ $s }}" data-t="{{ $segment['t'] }}">{{ $segment['text'] }}</span>
                                @endforeach
                            </p>
                        </div>
                        @include('events.coverage.interviews._avatar', ['guest' => $guest, 'size' => 40])
                    </div>

                    @if ($i === 2 && $guest['quote'])
                        <blockquote class="interview__quote">
                            <p>«{{ $guest['quote'] }}»</p>
                            <cite>— {{ $guest['name'] }}</cite>
                        </blockquote>
                    @endif
                </li>
            @endforeach
        </ol>

        {{-- بقية الضيوف --}}
        @if ($others)
            <section class="interview__more">
                <h2>{{ $t('لقاءات أخرى', 'More interviews') }}</h2>
                <div class="interview__more-list">
                    @foreach ($others as $other)
                        @if ($other['published'])
                            <a class="interview__more-item" href="{{ localized_route('event_coverage.cyberx_oman_2026.interview', ['guest' => $other['slug']]) }}">
                                @include('events.coverage.interviews._avatar', ['guest' => $other, 'size' => 44])
                                <span><strong>{{ $other['name'] }}</strong><small>{{ $other['role'] }}</small></span>
                            </a>
                        @else
                            <div class="interview__more-item is-soon">
                                @include('events.coverage.interviews._avatar', ['guest' => $other, 'size' => 44])
                                <span><strong>{{ $other['name'] }}</strong><small>{{ $t('قريبًا', 'Coming soon') }}</small></span>
                            </div>
                        @endif
                    @endforeach
                </div>
                <a class="interview__back" href="{{ $hubUrl }}">{{ $isEn ? '←' : '→' }} {{ $t('كل اللقاءات', 'All interviews') }}</a>
            </section>
        @endif
    </article>

    {{-- شريط التشغيل السفلي: يظهر وقت الاستماع --}}
    <div class="interview-dock" id="interviewDock" hidden>
        <button type="button" class="interview-dock__toggle" id="interviewDockToggle" aria-label="{{ $t('تشغيل / إيقاف', 'Play / pause') }}">❚❚</button>
        <span class="interview-dock__label" id="interviewDockLabel"></span>
        <span class="interview-dock__bar"><span id="interviewDockFill"></span></span>
    </div>

    <script type="application/json" id="interviewData">@json($jsData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)</script>
@endsection

@push('scripts')
    <script src="{{ versioned_asset('js/pages/event-interviews.js') }}" defer></script>
@endpush

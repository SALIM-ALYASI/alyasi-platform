@php
    $showArticlesNav = \App\Models\Setting::get('show_articles', '1') === '1' && \Illuminate\Support\Facades\Route::has('articles.index');
    $showCommunityNav = \App\Models\Setting::get('show_community_events', '1') === '1';

    $navItems = [
        'home' => ['route' => 'home', 'active' => request()->routeIs('home')],
        'services' => ['route' => 'services.index', 'active' => request()->routeIs('services.*')],
        'works' => ['route' => 'works.index', 'active' => request()->routeIs('works.*')],
        'news' => ['route' => 'news.index', 'active' => request()->routeIs('news.*')],
    ];

    if ($showArticlesNav) {
        $navItems['articles'] = ['route' => 'articles.index', 'active' => request()->routeIs('articles.*')];
    }

    if ($showCommunityNav) {
        $navItems['community'] = ['route' => 'community.index', 'active' => request()->routeIs('community.*')];
    }

    $currentLocale = app()->getLocale();
@endphp

<header class="site-header" data-site-header>
    <div class="site-header__bar container">
        <a href="{{ route('home') }}" class="site-header__brand">
            <img src="{{ asset('images/logo/logo-icon-navy.png') }}" alt="ALYASI" class="site-header__logo">
            <span class="site-header__brand-name">ALYASI</span>
        </a>

        <nav class="site-header__nav" aria-label="{{ __('layout.nav.home') }}">
            @foreach ($navItems as $key => $item)
                <a href="{{ route($item['route']) }}" class="site-header__nav-link @if($item['active']) is-active @endif">
                    {{ __('layout.nav.'.$key) }}
                </a>
            @endforeach
        </nav>

        <div class="site-header__actions">
            <div class="site-header__lang" role="group">
                @if (($langLinks['ar'] ?? null))
                    <a href="{{ $langLinks['ar'] }}" class="site-header__lang-option @if($currentLocale === 'ar') is-active @endif">{{ __('layout.lang_switch_ar') }}</a>
                @endif

                @if (($langLinks['en'] ?? null))
                    <a href="{{ $langLinks['en'] }}" class="site-header__lang-option @if($currentLocale === 'en') is-active @endif">{{ __('layout.lang_switch_en') }}</a>
                @endif
            </div>

            <a href="{{ route('contact') }}" class="btn btn--primary site-header__cta">{{ __('layout.start_now') }}</a>

            <button type="button" class="site-header__menu-toggle" data-menu-toggle aria-expanded="false" aria-label="{{ __('layout.menu_open') }}">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <div class="site-header__mobile-menu" data-mobile-menu aria-hidden="true">
        <nav class="site-header__mobile-nav">
            @foreach ($navItems as $key => $item)
                <a href="{{ route($item['route']) }}" class="site-header__mobile-link @if($item['active']) is-active @endif">
                    {{ __('layout.nav.'.$key) }}
                </a>
            @endforeach
        </nav>
        <a href="{{ route('contact') }}" class="btn btn--primary btn--block">{{ __('layout.start_now') }}</a>
    </div>
</header>

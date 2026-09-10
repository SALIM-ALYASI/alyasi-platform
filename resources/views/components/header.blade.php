@php
    $showArticlesNav = \App\Models\Setting::get('show_articles', '1') === '1' && \Illuminate\Support\Facades\Route::has('articles.index');

    // الروابط التي لها فعليًا نسخة /en (غيرها يبقى بدون prefix بغض النظر عن اللغة).
    $localeAwareRoutes = ['home', 'services.index', 'works.index', 'news.index', 'articles.index'];

    $navItems = [
        'home' => ['route' => 'home', 'active' => request()->routeIs('home')],
        'services' => ['route' => 'services.index', 'active' => request()->routeIs('services.*')],
        'works' => ['route' => 'works.index', 'active' => request()->routeIs('works.*')],
        'news' => ['route' => 'news.index', 'active' => request()->routeIs('news.*')],
    ];

    if ($showArticlesNav) {
        $navItems['articles'] = ['route' => 'articles.index', 'active' => request()->routeIs('articles.*')];
    }

    $navItems['events'] = [
        'route' => 'events.index',
        'active' => request()->routeIs('events.*', 'event_editions.*'),
    ];

    $currentLocale = app()->getLocale();

    $navHref = fn (string $routeName) => in_array($routeName, $localeAwareRoutes, true)
        ? localized_route($routeName)
        : route($routeName);
@endphp

<header class="site-header" data-site-header>
    <div class="site-header__bar container">
        <a href="{{ localized_route('home') }}" class="site-header__brand">
            <img src="{{ asset('images/logo/logo-icon-navy.png') }}" alt="ALYASI" class="site-header__logo">
            <span class="site-header__brand-name">ALYASI</span>
        </a>

        <nav class="site-header__nav" aria-label="{{ __('layout.nav.home') }}">
            @foreach ($navItems as $key => $item)
                <a href="{{ $navHref($item['route']) }}" class="site-header__nav-link @if($item['active']) is-active @endif">
                    {{ __('layout.nav.'.$key) }}
                </a>
            @endforeach
        </nav>

        <div class="site-header__actions">
            <div class="site-header__desktop-preferences">
                <div class="site-header__lang" role="group" aria-label="Language">
                    @if (($langLinks['ar'] ?? null))
                        <a href="{{ $langLinks['ar'] }}" class="site-header__lang-option @if($currentLocale === 'ar') is-active @endif">{{ __('layout.lang_switch_ar') }}</a>
                    @endif

                    @if (($langLinks['en'] ?? null))
                        <a href="{{ $langLinks['en'] }}" class="site-header__lang-option @if($currentLocale === 'en') is-active @endif">{{ __('layout.lang_switch_en') }}</a>
                    @endif
                </div>

                <button
                    type="button"
                    class="theme-toggle site-header__theme-toggle"
                    data-theme-toggle
                    data-theme-dark-label="{{ __('layout.theme.dark') }}"
                    data-theme-light-label="{{ __('layout.theme.light') }}"
                    aria-label="{{ __('layout.theme.dark') }}"
                    title="{{ __('layout.theme.dark') }}"
                >
                    <i class="fa-solid fa-moon" data-theme-icon aria-hidden="true"></i>
                </button>
            </div>

            <a href="{{ localized_route('contact') }}" class="btn btn--primary site-header__cta">{{ __('layout.start_now') }}</a>

            <button type="button" class="site-header__menu-toggle" data-menu-toggle aria-expanded="false" aria-label="{{ __('layout.menu_open') }}">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <div class="site-header__mobile-menu" data-mobile-menu aria-hidden="true">
        <div class="site-header__mobile-preferences">
            <div class="site-header__lang site-header__lang--mobile" role="group" aria-label="Language">
                @if (($langLinks['ar'] ?? null))
                    <a href="{{ $langLinks['ar'] }}" class="site-header__lang-option @if($currentLocale === 'ar') is-active @endif">{{ __('layout.lang_switch_ar') }}</a>
                @endif

                @if (($langLinks['en'] ?? null))
                    <a href="{{ $langLinks['en'] }}" class="site-header__lang-option @if($currentLocale === 'en') is-active @endif">{{ __('layout.lang_switch_en') }}</a>
                @endif
            </div>

            <button
                type="button"
                class="theme-toggle site-header__theme-toggle site-header__theme-toggle--mobile"
                data-theme-toggle
                data-theme-dark-label="{{ __('layout.theme.dark') }}"
                data-theme-light-label="{{ __('layout.theme.light') }}"
                aria-label="{{ __('layout.theme.dark') }}"
                title="{{ __('layout.theme.dark') }}"
            >
                <i class="fa-solid fa-moon" data-theme-icon aria-hidden="true"></i>
            </button>
        </div>

        <nav class="site-header__mobile-nav">
            @foreach ($navItems as $key => $item)
                <a href="{{ $navHref($item['route']) }}" class="site-header__mobile-link @if($item['active']) is-active @endif">
                    {{ __('layout.nav.'.$key) }}
                </a>
            @endforeach
        </nav>
        <a href="{{ localized_route('contact') }}" class="btn btn--primary btn--block">{{ __('layout.start_now') }}</a>
    </div>
</header>
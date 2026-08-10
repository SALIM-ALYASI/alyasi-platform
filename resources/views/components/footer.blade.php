@php
    $footerSocialLinks = [
        ['name' => 'واتساب', 'icon' => 'fa-brands fa-whatsapp', 'url' => 'https://wa.me/message/WJELG5R5UVIBO1?src=qr'],
        ['name' => 'X (تويتر)', 'icon' => 'fa-brands fa-x-twitter', 'url' => 'https://x.com/alyasi_mbrmj?s=11'],
        ['name' => 'إنستغرام', 'icon' => 'fa-brands fa-instagram', 'url' => 'https://www.instagram.com/alyasi_mbrmj?igsh=MW4xc2huYWFjbnZmZA=='],
    ];
    $showArticlesFooter = \App\Models\Setting::get('show_articles', '1') === '1' && \Illuminate\Support\Facades\Route::has('articles.index');
    $showCommunityFooter = \App\Models\Setting::get('show_community_events', '1') === '1';
@endphp

<footer class="site-footer">
    <div class="container site-footer__top">
        <div class="site-footer__about">
            <div class="site-footer__brand">
                <img src="{{ asset('images/logo/logo-icon-navy.png') }}" alt="ALYASI" class="site-footer__logo">
                <span class="site-footer__brand-name">ALYASI</span>
            </div>
            <p class="site-footer__desc">{{ __('layout.footer.about') }}</p>
        </div>

        <div class="site-footer__col">
            <h4 class="site-footer__heading">{{ __('layout.footer.quick_links') }}</h4>
            <div class="site-footer__links">
                <a href="{{ route('home') }}">{{ __('layout.nav.home') }}</a>
                <a href="{{ route('services.index') }}">{{ __('layout.nav.services') }}</a>
                <a href="{{ route('works.index') }}">{{ __('layout.nav.works') }}</a>
                <a href="{{ route('news.index') }}">{{ __('layout.nav.news') }}</a>
                @if ($showArticlesFooter)
                    <a href="{{ route('articles.index') }}">{{ __('layout.nav.articles') }}</a>
                @endif
            </div>
        </div>

        <div class="site-footer__col">
            <h4 class="site-footer__heading">{{ __('layout.footer.community_title') }}</h4>
            <div class="site-footer__links">
                @if ($showCommunityFooter)
                    <a href="{{ route('community.index') }}">{{ __('layout.nav.community') }}</a>
                @endif
                <a href="{{ route('home') }}#faq">{{ __('layout.footer.faq') }}</a>
                <a href="{{ route('contact') }}">{{ __('layout.footer.contact_us') }}</a>
            </div>
        </div>

        <div class="site-footer__col">
            <h4 class="site-footer__heading">{{ __('layout.footer.connect_title') }}</h4>
            <div class="site-footer__socials">
                @foreach ($footerSocialLinks as $link)
                    <a href="{{ $link['url'] }}" class="site-footer__social" target="_blank" rel="noopener" aria-label="{{ $link['name'] }}">
                        <i class="{{ $link['icon'] }}" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="container site-footer__bottom">
        <span>{{ __('layout.footer.copyright', ['year' => now()->year]) }}</span>
        <div class="site-footer__legal">
            <a href="{{ route('privacy') }}">{{ __('layout.footer.privacy') }}</a>
            <a href="{{ route('terms') }}">{{ __('layout.footer.terms') }}</a>
        </div>
    </div>
</footer>

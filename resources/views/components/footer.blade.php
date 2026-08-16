@php
    $showArticlesFooter =
        \App\Models\Setting::get('show_articles', '1') === '1'
        && \Illuminate\Support\Facades\Route::has('articles.index');

    $showCommunityFooter =
        \App\Models\Setting::get('show_community_events', '1') === '1';

    $footerSocialLinks = \App\Models\SocialLink::forDisplay();
@endphp

<footer class="site-footer">

    <div class="container site-footer__top">

        {{-- عن ALYASI --}}
        <div class="site-footer__about">

            <div class="site-footer__brand">
                <img
                    src="{{ asset('images/logo/logo-icon-navy.png') }}"
                    alt="ALYASI"
                    class="site-footer__logo"
                >

                <span class="site-footer__brand-name">
                    ALYASI
                </span>
            </div>

            <p class="site-footer__desc">
                {{ __('layout.footer.about') }}
            </p>

        </div>


        {{-- الروابط السريعة --}}
        <div class="site-footer__col">

            <h4 class="site-footer__heading">
                {{ __('layout.footer.quick_links') }}
            </h4>

            <div class="site-footer__links">

                <a href="{{ localized_route('home') }}">
                    {{ __('layout.nav.home') }}
                </a>

                <a href="{{ localized_route('services.index') }}">
                    {{ __('layout.nav.services') }}
                </a>

                <a href="{{ localized_route('works.index') }}">
                    {{ __('layout.nav.works') }}
                </a>

                <a href="{{ localized_route('news.index') }}">
                    {{ __('layout.nav.news') }}
                </a>

                @if ($showArticlesFooter)
                    <a href="{{ article_route('index') }}">
                        {{ __('layout.nav.articles') }}
                    </a>
                @endif

            </div>

        </div>


        {{-- المجتمع --}}
        <div class="site-footer__col">

            <h4 class="site-footer__heading">
                {{ __('layout.footer.community_title') }}
            </h4>

            <div class="site-footer__links">

                @if ($showCommunityFooter)
                    <a href="{{ route('community.index') }}">
                        {{ __('layout.nav.community') }}
                    </a>
                @endif

                <a href="{{ route('home') }}#faq">
                    {{ __('layout.footer.faq') }}
                </a>

                <a href="{{ localized_route('contact') }}">
                    {{ __('layout.footer.contact_us') }}
                </a>

            </div>

        </div>


        {{-- التواصل --}}
        <div class="site-footer__col">

            <h4 class="site-footer__heading">
                {{ __('layout.footer.connect_title') }}
            </h4>

            <div class="site-footer__socials">

                @foreach ($footerSocialLinks as $link)

                    <a
                        href="{{ $link->url }}"
                        class="site-footer__social"
                        @if ($link->open_new_tab) target="_blank" rel="noopener noreferrer" @endif
                        aria-label="{{ $link->name }}"
                        title="{{ $link->name }}"
                    >
                        <i
                            class="{{ $link->icon }}"
                            aria-hidden="true"
                        ></i>
                    </a>

                @endforeach

            </div>

        </div>

    </div>


    {{-- أسفل الفوتر --}}
    <div class="container site-footer__bottom">

        <span>
            {{ __('layout.footer.copyright', ['year' => now()->year]) }}
        </span>

        <div class="site-footer__legal">

            <a href="{{ route('privacy') }}">
                {{ __('layout.footer.privacy') }}
            </a>

            <a href="{{ route('terms') }}">
                {{ __('layout.footer.terms') }}
            </a>

        </div>

    </div>

</footer>
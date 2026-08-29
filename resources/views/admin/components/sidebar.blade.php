<aside
    class="admin-sidebar"
    id="admin-sidebar"
    aria-label="{{ __('admin-sidebar.sidebar_label') }}"
>

    {{-- رأس القائمة --}}
    <div class="sidebar-header">

        <a
            href="{{ route('admin.dashboard') }}"
            class="sidebar-brand"
            aria-label="{{ __('admin-sidebar.go_to_dashboard') }}"
        >
            <img
                src="{{ asset('images/logo/logo-icon.png') }}"
                alt="{{ __('admin-sidebar.logo_alt') }}"
                class="sidebar-brand-logo"
            >

            <div class="sidebar-brand-text">

                <strong>
                    ALYASI
                </strong>

                <span>
                    {{ __('admin-sidebar.admin_panel') }}
                </span>

            </div>
        </a>

        <button
            type="button"
            class="sidebar-close"
            id="sidebar-close"
            aria-label="{{ __('admin-sidebar.close_sidebar') }}"
        >
            <i
                class="fa-solid fa-xmark"
                aria-hidden="true"
            ></i>
        </button>

    </div>

    {{-- محتوى القائمة --}}
    <div class="sidebar-body">

        {{-- القائمة الرئيسية --}}
        <div class="sidebar-section">

            <span class="sidebar-section-title">
                {{ __('admin-sidebar.main_menu') }}
            </span>

            <nav
                class="sidebar-nav"
                aria-label="{{ __('admin-sidebar.main_menu') }}"
            >

                {{-- لوحة التحكم --}}
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-gauge-high"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.dashboard') }}
                    </span>
                </a>

                {{-- الخدمات --}}
                <a
                    href="{{ route('admin.services.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-layer-group"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.services') }}
                    </span>
                </a>

                {{-- تنظيف روابط الخدمات --}}
                <a
                    href="{{ route('admin.service-slug-cleanup.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.service-slug-cleanup.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-link-slash"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.service_slug_cleanup') }}
                    </span>
                </a>

                {{-- الأعمال --}}
                <a
                    href="{{ route('admin.works.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.works.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-briefcase"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.works') }}
                    </span>

                    <span class="sidebar-link-badge">
                        {{ __('admin-sidebar.new') }}
                    </span>
                </a>

                {{-- التقنيات --}}
                <a
                    href="{{ route('admin.technologies.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.technologies.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-microchip"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.technologies') }}
                    </span>
                </a>

                {{-- الأخبار --}}
                <a
                    href="{{ route('admin.news.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.news.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-regular fa-newspaper"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.news') }}
                    </span>
                </a>

                {{-- مقالاتي --}}
                <a
                    href="{{ route('admin.articles.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.articles.*') || request()->routeIs('admin.article-categories.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-file-lines"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.articles') }}
                    </span>
                </a>

                {{-- المجتمع --}}
                <a
                    href="{{ route('admin.community.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.community.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-users"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.community') }}
                    </span>
                </a>

                {{-- تعليقات المجتمع --}}
                <a
                    href="{{ route('admin.community-comments.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.community-comments.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-comments"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.community_comments') }}
                    </span>

                    @if(($pendingCommunityCommentsCount ?? 0) > 0)
                        <span class="sidebar-link-badge">
                            {{ $pendingCommunityCommentsCount }}
                        </span>
                    @endif
                </a>

                {{-- تصحيحات النطق --}}
                <a
                    href="{{ route('admin.pronunciation-queue.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.pronunciation-queue.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-spell-check"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.pronunciation_queue') }}
                    </span>
                </a>

                {{-- التقييمات --}}
                <a
                    href="{{ route('admin.reviews.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-star"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.reviews') }}
                    </span>

                    @if(($pendingReviewsCount ?? 0) > 0)
                        <span class="sidebar-link-badge">
                            {{ $pendingReviewsCount }}
                        </span>
                    @endif
                </a>

                {{-- الزوار المحظورون --}}
                <a
                    href="{{ route('admin.blocked-visitors.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.blocked-visitors.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-user-slash"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.blocked_visitors') }}
                    </span>
                </a>

                {{-- روابط التواصل --}}
                <a
                    href="{{ route('admin.social-links.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.social-links.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-share-nodes"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.social_links') }}
                    </span>
                </a>

                {{-- ربط يوتيوب --}}
                <a
                    href="{{ route('admin.youtube.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.youtube.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-brands fa-youtube"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.youtube') }}
                    </span>
                </a>

                {{-- الأسئلة الشائعة --}}
                <a
                    href="{{ route('admin.faqs.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.faqs.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-circle-question"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.faqs') }}
                    </span>
                </a>

                {{-- المسوّقون --}}
                <a
                    href="{{ route('admin.marketers.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.marketers.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-bullhorn"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.marketers') }}
                    </span>
                </a>

                {{-- استوديو الصوت --}}
                <a
                    href="{{ route('admin.voice-studio.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.voice-studio.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-microphone-lines"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.voice_studio') }}
                    </span>
                </a>

                {{-- النشر الذكي --}}
                <a
                    href="{{ route('admin.publish.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.publish.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-paper-plane"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.publish') }}
                    </span>
                </a>

            </nav>

        </div>

        {{-- إدارة المنصة --}}
        <div class="sidebar-section">

            <span class="sidebar-section-title">
                {{ __('admin-sidebar.platform_management') }}
            </span>

            <nav
                class="sidebar-nav"
                aria-label="{{ __('admin-sidebar.platform_management') }}"
            >

                {{-- الرسائل --}}
                <a
                    href="{{ route('admin.messages.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.messages.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-regular fa-envelope"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.messages') }}
                    </span>

                    @if(($newMessagesCount ?? 0) > 0)
                        <span
                            class="sidebar-link-count"
                            aria-label="{{ __('admin-sidebar.messages_count', ['count' => $newMessagesCount]) }}"
                        >
                            {{ $newMessagesCount }}
                        </span>
                    @endif
                </a>

                {{-- بيانات سيرفر --}}
                <a
                    href="{{ route('admin.server-info.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.server-info.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-server"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.server_info') }}
                    </span>
                </a>

                {{-- الإعدادات --}}
                <a
                    href="{{ route('admin.settings.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i
                            class="fa-solid fa-gear"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="sidebar-link-text">
                        {{ __('admin-sidebar.settings') }}
                    </span>
                </a>

            </nav>

        </div>

    </div>

    {{-- تذييل القائمة --}}
    <div class="sidebar-footer">

        <div class="sidebar-admin">

            <div class="sidebar-admin-avatar">

                @if (auth('admin')->user()?->avatar)

                    <img
                        src="{{ asset(auth('admin')->user()->avatar) }}"
                        alt="{{ auth('admin')->user()->name ?? __('admin-sidebar.platform_admin') }}"
                    >

                @else

                    <span aria-hidden="true">
                        {{ mb_substr(
                            auth('admin')->user()?->name
                                ?? __('admin-sidebar.default_admin_letter'),
                            0,
                            1
                        ) }}
                    </span>

                @endif

            </div>

            <div class="sidebar-admin-info">

                <strong>
                    {{ auth('admin')->user()?->name
                        ?? __('admin-sidebar.platform_admin') }}
                </strong>

                @if (auth('admin')->user()?->email)

                    <span>
                        {{ auth('admin')->user()->email }}
                    </span>

                @endif

            </div>

        </div>

        <form
            method="POST"
            action="{{ route('admin.logout') }}"
            class="sidebar-logout-form"
        >
            @csrf

            <button
                type="submit"
                class="sidebar-logout"
                aria-label="{{ __('admin-sidebar.logout') }}"
            >
                <i
                    class="fa-solid fa-arrow-right-from-bracket"
                    aria-hidden="true"
                ></i>

                <span>
                    {{ __('admin-sidebar.logout') }}
                </span>
            </button>

        </form>

    </div>

</aside>
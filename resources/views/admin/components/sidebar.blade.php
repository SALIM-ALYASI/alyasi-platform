<aside
    class="admin-sidebar"
    id="admin-sidebar"
>

    <div class="sidebar-header">

        <a
            href="{{ route('admin.dashboard') }}"
            class="sidebar-brand"
        >
            <img
                src="{{ asset('images/logo/logo-icon.png') }}"
                alt="شعار ALYASI"
                class="sidebar-brand-logo"
            >

            <div class="sidebar-brand-text">
                <strong>
                    ALYASI
                </strong>

                <span>
                    لوحة الإدارة
                </span>
            </div>
        </a>

        <button
            type="button"
            class="sidebar-close"
            id="sidebar-close"
            aria-label="إغلاق القائمة"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>

    <div class="sidebar-body">

        <div class="sidebar-section">

            <span class="sidebar-section-title">
                القائمة الرئيسية
            </span>

            <nav class="sidebar-nav">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-solid fa-grid-2"></i>
                    </span>

                    <span class="sidebar-link-text">
                        لوحة التحكم
                    </span>
                </a>

                <a
                href="{{ route('admin.services.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>

                    <span class="sidebar-link-text">
                        الخدمات
                    </span>

                    <span class="sidebar-link-badge">
                        جديد
                    </span>
                </a>

                <a
                    href="#"
                    class="sidebar-link {{ request()->routeIs('admin.news.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-regular fa-newspaper"></i>
                    </span>

                    <span class="sidebar-link-text">
                        الأخبار
                    </span>
                </a>

                <a
                    href="#"
                    class="sidebar-link {{ request()->routeIs('admin.community.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <span class="sidebar-link-text">
                        المجتمع
                    </span>
                </a>

            </nav>

        </div>

        <div class="sidebar-section">

            <span class="sidebar-section-title">
                إدارة المنصة
            </span>

            <nav class="sidebar-nav">

                <a
                    href="#"
                    class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-solid fa-user-group"></i>
                    </span>

                    <span class="sidebar-link-text">
                        المستخدمون
                    </span>
                </a>

                <a
                    href="#"
                    class="sidebar-link {{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-regular fa-images"></i>
                    </span>

                    <span class="sidebar-link-text">
                        مكتبة الوسائط
                    </span>
                </a>

                <a
                    href="#"
                    class="sidebar-link {{ request()->routeIs('admin.messages.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-regular fa-envelope"></i>
                    </span>

                    <span class="sidebar-link-text">
                        الرسائل
                    </span>

                    <span class="sidebar-link-count">
                        0
                    </span>
                </a>

                <a
                    href="#"
                    class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="fa-solid fa-gear"></i>
                    </span>

                    <span class="sidebar-link-text">
                        الإعدادات
                    </span>
                </a>

            </nav>

        </div>

    </div>

    <div class="sidebar-footer">

        <div class="sidebar-admin">

            <div class="sidebar-admin-avatar">

                @if (auth('admin')->user()?->avatar)
                    <img
                        src="{{ asset(auth('admin')->user()->avatar) }}"
                        alt="{{ auth('admin')->user()->name }}"
                    >
                @else
                    <span>
                        {{ mb_substr(auth('admin')->user()?->name ?? 'A', 0, 1) }}
                    </span>
                @endif

            </div>

            <div class="sidebar-admin-info">

                <strong>
                    {{ auth('admin')->user()?->name ?? 'مدير المنصة' }}
                </strong>

                <span>
                    {{ auth('admin')->user()?->email }}
                </span>

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
            >
                <i class="fa-solid fa-arrow-right-from-bracket"></i>

                <span>
                    تسجيل الخروج
                </span>
            </button>
        </form>

    </div>

</aside>
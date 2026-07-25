<header class="admin-header">

    <div class="admin-header-start">

        <button
            type="button"
            class="header-icon-button mobile-menu-button"
            id="sidebar-open"
            aria-label="فتح القائمة الجانبية"
            aria-controls="admin-sidebar"
            aria-expanded="false"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="header-page-info">

            <h1>
                @yield('page-title', 'لوحة التحكم')
            </h1>

            <p>
                @yield('page-description', 'مرحبًا بك في لوحة إدارة منصة ALYASI')
            </p>

        </div>

    </div>

    <div class="admin-header-actions">

        <a
            href="{{ route('home') }}"
            target="_blank"
            rel="noopener noreferrer"
            class="header-website-link"
        >
            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            <span>
                عرض الموقع
            </span>
        </a>

        <button
            type="button"
            class="header-icon-button"
            id="fullscreen-toggle"
            aria-label="ملء الشاشة"
            title="ملء الشاشة"
        >
            <i class="fa-solid fa-expand"></i>
        </button>

        <button
            type="button"
            class="header-icon-button notification-button"
            aria-label="الإشعارات"
            title="الإشعارات"
        >
            <i class="fa-regular fa-bell"></i>

            <span class="notification-indicator"></span>
        </button>

        <div class="header-admin-menu">

            <button
                type="button"
                class="header-admin-button"
                id="admin-menu-toggle"
                aria-label="فتح قائمة الحساب"
                aria-controls="admin-account-dropdown"
                aria-expanded="false"
            >
                <div class="header-admin-avatar">

                    @if (auth('admin')->user()?->avatar)
                        <img
                            src="{{ asset(auth('admin')->user()->avatar) }}"
                            alt="{{ auth('admin')->user()->name }}"
                        >
                    @else
                        <span>
                            {{ mb_substr(
                                auth('admin')->user()?->name ?? 'A',
                                0,
                                1
                            ) }}
                        </span>
                    @endif

                </div>

                <div class="header-admin-details">

                    <strong>
                        {{ auth('admin')->user()?->name ?? 'مدير المنصة' }}
                    </strong>

                    <span>
                        مدير النظام
                    </span>

                </div>

                <i class="fa-solid fa-chevron-down header-admin-arrow"></i>
            </button>

            <div
                class="admin-account-dropdown"
                id="admin-account-dropdown"
            >

                <div class="account-dropdown-header">

                    <strong>
                        {{ auth('admin')->user()?->name ?? 'مدير المنصة' }}
                    </strong>

                    <span>
                        {{ auth('admin')->user()?->email }}
                    </span>

                </div>

                <div class="account-dropdown-links">

                    <a href="#">
                        <i class="fa-regular fa-user"></i>

                        <span>
                            الملف الشخصي
                        </span>
                    </a>

                    <a href="#">
                        <i class="fa-solid fa-gear"></i>

                        <span>
                            إعدادات الحساب
                        </span>
                    </a>

                </div>

                <form
                    method="POST"
                    action="{{ route('admin.logout') }}"
                    class="account-dropdown-logout"
                >
                    @csrf

                    <button type="submit">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>

                        <span>
                            تسجيل الخروج
                        </span>
                    </button>
                </form>

            </div>

        </div>

    </div>

</header>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'لوحة التحكم') | ALYASI
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/admin/css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/admin/css/admin-cards.css') }}"
    >

    @stack('styles')
</head>

<body>

    <div class="admin-layout">

        @include('admin.components.sidebar')

        <div class="admin-main">

            @include('admin.components.header')

            <main class="admin-content">

                @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            {{ session('success') }}
                        </span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="admin-alert admin-alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            {{ session('error') }}
                        </span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-alert admin-alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>

                        <div>
                            <strong>يرجى مراجعة البيانات المدخلة.</strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

    <div
        class="sidebar-overlay"
        id="sidebar-overlay"
    ></div>

    <script src="{{ versioned_asset('assets/admin/js/dashboard.js') }}"></script>

    @stack('scripts')

</body>

</html>
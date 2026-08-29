<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="إعادة تعيين كلمة مرور حساب المدير | ALYASI"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>إعادة تعيين كلمة المرور | ALYASI</title>

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
        href="{{ versioned_asset('assets/admin/css/login.css') }}"
    >

    <style>
        .form-footer-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            font-weight: 700;
        }

        .form-footer-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <main class="auth-page">

        <div class="auth-background" aria-hidden="true">
            <span class="background-orb background-orb-one"></span>
            <span class="background-orb background-orb-two"></span>
            <span class="background-grid"></span>
        </div>

        <section class="auth-card">

            <aside class="auth-intro">

                <div class="intro-decoration intro-decoration-top"></div>
                <div class="intro-decoration intro-decoration-bottom"></div>

                <header class="intro-header">

                    <a
                        href="{{ route('home') }}"
                        class="brand-logo"
                        aria-label="العودة إلى منصة ALYASI"
                    >
                        <img
                            src="{{ asset('images/logo/logo-icon.png') }}"
                            alt="شعار منصة ALYASI"
                        >
                    </a>

                    <span class="platform-label">
                        لوحة الإدارة
                    </span>

                </header>

                <div class="intro-content">

                    <h1>
                        كلمة مرور
                        <span>جديدة وآمنة.</span>
                    </h1>

                    <p>
                        اختر كلمة مرور قوية لحماية حساب المدير الخاص بك.
                    </p>

                </div>

                <footer class="intro-footer">
                    <i class="fa-solid fa-lock"></i>

                    <span>
                        الدخول متاح لمديري المنصة المصرح لهم فقط
                    </span>
                </footer>

            </aside>

            <section class="auth-form-section">

                <div class="auth-form-container">

                    <div class="mobile-logo">
                        <img
                            src="{{ asset('images/logo/logo-dark.png') }}"
                            alt="شعار منصة ALYASI"
                        >
                    </div>

                    <header class="form-heading">

                        <span class="form-eyebrow">
                            لوحة تحكم ALYASI
                        </span>

                        <h2>
                            إعادة تعيين كلمة المرور
                        </h2>

                        <p>
                            أدخل كلمة مرور جديدة لحسابك.
                        </p>

                    </header>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i>

                            <span>
                                {{ $errors->first() }}
                            </span>
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('admin.password.update') }}"
                        class="auth-form"
                    >
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">

                            <label for="email" class="form-label">
                                البريد الإلكتروني
                            </label>

                            <div class="input-wrapper">

                                <span class="input-icon">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $email) }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    autocomplete="email"
                                    inputmode="email"
                                    required
                                    autofocus
                                >

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="password" class="form-label">
                                كلمة المرور الجديدة
                            </label>

                            <div class="input-wrapper">

                                <span class="input-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control form-control-password @error('password') is-invalid @enderror"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    id="password-toggle"
                                    aria-label="إظهار كلمة المرور"
                                    aria-controls="password"
                                    aria-pressed="false"
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="password_confirmation" class="form-label">
                                تأكيد كلمة المرور
                            </label>

                            <div class="input-wrapper">

                                <span class="input-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required
                                >

                            </div>

                        </div>

                        <button type="submit" class="submit-button">
                            <span>
                                حفظ كلمة المرور الجديدة
                            </span>

                            <i class="fa-solid fa-arrow-left"></i>
                        </button>

                    </form>

                    <footer class="form-footer">
                        <a href="{{ route('admin.login') }}" class="form-footer-link">
                            <i class="fa-solid fa-arrow-right"></i>
                            العودة لتسجيل الدخول
                        </a>
                    </footer>

                </div>

            </section>

        </section>

    </main>

    <script src="{{ versioned_asset('assets/admin/js/login.js') }}"></script>

</body>

</html>

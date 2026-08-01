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
        content="تسجيل الدخول إلى لوحة تحكم منصة ALYASI"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>تسجيل الدخول | ALYASI</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap"
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
</head>

<body>

    <main class="auth-page">

        <div class="auth-background" aria-hidden="true">
            <span class="background-orb background-orb-one"></span>
            <span class="background-orb background-orb-two"></span>
            <span class="background-grid"></span>
        </div>

        <section class="auth-card">

            {{-- الجانب التعريفي --}}
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

                    <div class="system-status">
                        <span class="status-indicator"></span>

                        <span>
                            نظام الإدارة متصل وآمن
                        </span>
                    </div>

                    <h1>
                        كل ما تحتاجه
                        <span>لإدارة منصتك.</span>
                    </h1>

                    <p>
                        تحكّم في الخدمات والأخبار والمجتمع والمحتوى من
                        لوحة إدارية موحّدة، سريعة وسهلة الاستخدام.
                    </p>

                    <div class="intro-features">

                        <div class="intro-feature">
                            <i class="fa-solid fa-layer-group"></i>

                            <span>
                                إدارة مركزية
                            </span>
                        </div>

                        <div class="intro-feature">
                            <i class="fa-solid fa-shield-halved"></i>

                            <span>
                                حماية متقدمة
                            </span>
                        </div>

                        <div class="intro-feature">
                            <i class="fa-solid fa-bolt"></i>

                            <span>
                                أداء سريع
                            </span>
                        </div>

                    </div>

                </div>

                <footer class="intro-footer">
                    <i class="fa-solid fa-lock"></i>

                    <span>
                        الدخول متاح لمديري المنصة المصرح لهم فقط
                    </span>
                </footer>

            </aside>

            {{-- نموذج تسجيل الدخول --}}
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
                            مرحبًا بعودتك
                        </h2>

                        <p>
                            أدخل بيانات حساب المدير للوصول إلى لوحة التحكم.
                        </p>

                    </header>

                    @if (session('success'))
                        <div
                            class="alert alert-success"
                            role="alert"
                        >
                            <i class="fa-solid fa-circle-check"></i>

                            <span>
                                {{ session('success') }}
                            </span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="alert alert-danger"
                            role="alert"
                        >
                            <i class="fa-solid fa-circle-exclamation"></i>

                            <span>
                                {{ session('error') }}
                            </span>
                        </div>
                    @endif

                    @if ($errors->has('email') || $errors->has('password'))
                        <div
                            class="alert alert-danger"
                            role="alert"
                        >
                            <i class="fa-solid fa-circle-exclamation"></i>

                            <span>
                                يرجى مراجعة بيانات تسجيل الدخول.
                            </span>
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('admin.login.submit') }}"
                        id="admin-login-form"
                        class="auth-form"
                    >
                        @csrf

                        <div class="form-group">

                            <label
                                for="email"
                                class="form-label"
                            >
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
                                    value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="admin@alyasi.local"
                                    autocomplete="email"
                                    inputmode="email"
                                    required
                                    autofocus
                                >

                            </div>

                            @error('email')
                                <span class="field-error">
                                    <i class="fa-solid fa-circle-info"></i>

                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <div class="form-group">

                            <label
                                for="password"
                                class="form-label"
                            >
                                كلمة المرور
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
                                    placeholder="أدخل كلمة المرور"
                                    autocomplete="current-password"
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

                            @error('password')
                                <span class="field-error">
                                    <i class="fa-solid fa-circle-info"></i>

                                    {{ $message }}
                                </span>
                            @enderror

                        </div>

                        <div class="form-options">

                            <label class="remember-option">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                >

                                <span class="custom-checkbox">
                                    <i class="fa-solid fa-check"></i>
                                </span>

                                <span>
                                    تذكرني على هذا الجهاز
                                </span>

                            </label>

                            <span class="secure-connection">
                                <i class="fa-solid fa-shield-halved"></i>

                                اتصال مشفر
                            </span>

                        </div>

                        <div style="text-align: left; margin-bottom: 4px;">
                            <a
                                href="{{ route('admin.password.request') }}"
                                style="color: var(--primary); font-weight: 700; font-size: 13px; text-decoration: none;"
                            >
                                نسيت كلمة المرور؟
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="submit-button"
                            id="login-submit"
                        >
                            <span>
                                تسجيل الدخول
                            </span>

                            <i class="fa-solid fa-arrow-left"></i>
                        </button>

                    </form>

                    <footer class="form-footer">

                        <span>
                            جميع الحقوق محفوظة
                        </span>

                        <strong>
                            © {{ date('Y') }} ALYASI
                        </strong>

                    </footer>

                </div>

            </section>

        </section>

    </main>

    <script src="{{ versioned_asset('assets/admin/js/login.js') }}"></script>

</body>

</html>
@extends('admin.layouts.app')

@section('title', 'ربط يوتيوب')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-brands fa-youtube" aria-hidden="true"></i>
                التكاملات
            </span>

            <h1 class="admin-page-header__title">
                ربط قناة يوتيوب
            </h1>

            <p class="admin-page-header__description">
                سجّل الدخول بحساب Google لتوليد توكن وصول يسمح بنشر فيديوهات
                على يوتيوب، وقراءة التعليقات والرد عليها، لاستخدامه لاحقًا في
                أتمتة n8n.
            </p>

        </div>

    </div>

    @unless ($configured)

        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3>الإعداد مطلوب أولًا</h3>
                    <p>
                        لم يتم ضبط بيانات اعتماد Google OAuth بعد. أضف
                        <code>GOOGLE_CLIENT_ID</code> و
                        <code>GOOGLE_CLIENT_SECRET</code> في ملف
                        <code>.env</code>، ثم أضف رابط الاستدعاء التالي داخل
                        "Authorized redirect URIs" لبيانات الاعتماد من نوع
                        Web application في Google Cloud Console:
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label>Redirect URI</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ route('admin.youtube.callback') }}"
                    readonly
                    onclick="this.select();"
                >
            </div>
        </div>

    @endunless

    <div class="dashboard-panel">

        <div class="dashboard-panel-header">
            <div>
                <h3>حالة الربط</h3>
                <p>
                    الصلاحيات المطلوبة:
                    <code>youtube.force-ssl</code> و
                    <code>youtube.upload</code>
                    (نشر الفيديوهات، وقراءة التعليقات والرد عليها وإدارتها).
                </p>
            </div>

            <span class="admin-status {{ $connected ? 'admin-status--active' : 'admin-status--inactive' }}">
                <i class="{{ $connected ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark' }}" aria-hidden="true"></i>
                {{ $connected ? 'متصل' : 'غير متصل' }}
            </span>
        </div>

        @if ($connected)

            <div class="form-grid">

                <div class="form-group">
                    <label>القناة المرتبطة</label>
                    <input type="text" class="form-control" value="{{ $channelTitle ?: 'غير معروف' }}" readonly>
                </div>

                <div class="form-group">
                    <label>تاريخ آخر ربط</label>
                    <input type="text" class="form-control" value="{{ $connectedAt }}" readonly>
                </div>

            </div>

        @endif

        <div class="admin-data-card__actions" style="justify-content: flex-start; gap: 12px; margin-top: 16px;">

            <a href="{{ route('admin.youtube.connect') }}" class="admin-primary-button">
                <i class="fa-brands fa-google" aria-hidden="true"></i>
                {{ $connected ? 'إعادة الربط وتوليد توكن جديد' : 'تسجيل الدخول عبر Google' }}
            </a>

            @if ($connected)
                <form
                    action="{{ route('admin.youtube.disconnect') }}"
                    method="POST"
                    onsubmit="return confirm('هل أنت متأكد من فصل ربط قناة يوتيوب؟');"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="admin-action-button admin-action-button--delete" title="فصل الربط">
                        <i class="fa-solid fa-link-slash" aria-hidden="true"></i>
                        فصل الربط
                    </button>
                </form>
            @endif

        </div>

    </div>

    @if (session('youtube_new_refresh_token'))

        <div class="dashboard-panel" style="border: 1px solid #E1B93A;">

            <div class="dashboard-panel-header">
                <div>
                    <h3>
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        التوكنات الجديدة (تظهر مرة واحدة فقط)
                    </h3>
                    <p>
                        انسخ <strong>refresh_token</strong> الآن وخزّنه داخل
                        بيانات اعتماد n8n — لن يظهر مرة أخرى بعد مغادرة هذه
                        الصفحة.
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label>refresh_token</label>
                <div style="display: flex; gap: 8px;">
                    <input
                        type="text"
                        id="youtubeRefreshToken"
                        class="form-control"
                        value="{{ session('youtube_new_refresh_token') }}"
                        readonly
                        onclick="this.select();"
                    >
                    <button
                        type="button"
                        class="admin-action-button"
                        title="نسخ"
                        onclick="window.copyYoutubeToken('youtubeRefreshToken')"
                    >
                        <i class="fa-solid fa-copy" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>access_token (صالح لمدة ساعة تقريبًا)</label>
                <div style="display: flex; gap: 8px;">
                    <input
                        type="text"
                        id="youtubeAccessToken"
                        class="form-control"
                        value="{{ session('youtube_new_access_token') }}"
                        readonly
                        onclick="this.select();"
                    >
                    <button
                        type="button"
                        class="admin-action-button"
                        title="نسخ"
                        onclick="window.copyYoutubeToken('youtubeAccessToken')"
                    >
                        <i class="fa-solid fa-copy" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

        </div>

        <script>
            window.copyYoutubeToken = function (inputId) {
                const input = document.getElementById(inputId);

                if (!input) {
                    return;
                }

                input.select();

                navigator.clipboard?.writeText(input.value).catch(() => {
                    document.execCommand('copy');
                });
            };
        </script>

    @endif

</div>

@endsection

@extends('admin.layouts.app')

@section('title', 'الإعدادات')

@push('styles')
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/admin/css/settings.css') }}"
>
@endpush

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-gear" aria-hidden="true"></i>
                إعدادات المنصة
            </span>

            <h1 class="admin-page-header__title">
                الإعدادات
            </h1>

            <p class="admin-page-header__description">
                التحكم في الإعدادات العامة لموقع ALYASI.
            </p>

        </div>

    </div>

    <div class="dashboard-panel" id="profile">

        <div class="dashboard-panel-header">
            <div>
                <h3>الملف الشخصي</h3>
                <p>الاسم والبريد الإلكتروني المستخدَمين لتسجيل الدخول إلى لوحة التحكم.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update-profile') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-grid">

                <div class="form-group">
                    <label for="name">الاسم</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name', auth('admin')->user()->name) }}"
                        required
                    >

                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email', auth('admin')->user()->email) }}"
                        dir="ltr"
                        required
                    >

                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="admin-primary-button">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>

    </div>

    <div class="dashboard-panel" id="password" style="margin-top: 24px;">

        <div class="dashboard-panel-header">
            <div>
                <h3>كلمة المرور</h3>
                <p>غيّر كلمة مرور حسابك بشكل دوري للحفاظ على أمان لوحة التحكم.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update-password') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-grid">

                <div class="form-group full">
                    <label for="current_password">كلمة المرور الحالية</label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="form-control"
                        dir="ltr"
                        required
                    >

                    @error('current_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        dir="ltr"
                        required
                    >

                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">تأكيد كلمة المرور الجديدة</label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        dir="ltr"
                        required
                    >
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="admin-primary-button">
                    <i class="fa-solid fa-key" aria-hidden="true"></i>
                    تغيير كلمة المرور
                </button>
            </div>
        </form>

    </div>

    <div class="dashboard-panel" id="contact-info" style="margin-top: 24px;">

        <div class="dashboard-panel-header">
            <div>
                <h3>بيانات التواصل العامة</h3>
                <p>البريد ورقم الجوال اللي يظهرون للزوار بصفحة "تواصل معنا". اتركهم فاضيين لإخفائهم.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update-contact-info') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-grid">

                <div class="form-group">
                    <label for="contact_email">البريد الإلكتروني</label>

                    <input
                        type="email"
                        id="contact_email"
                        name="contact_email"
                        class="form-control"
                        value="{{ old('contact_email', $contactEmail) }}"
                        dir="ltr"
                    >

                    @error('contact_email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contact_phone">رقم الجوال / واتساب</label>

                    <input
                        type="text"
                        id="contact_phone"
                        name="contact_phone"
                        class="form-control"
                        value="{{ old('contact_phone', $contactPhone) }}"
                        dir="ltr"
                    >

                    @error('contact_phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="admin-primary-button">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>

    </div>

    <div class="dashboard-panel" style="margin-top: 24px;">

        <div class="dashboard-panel-header">
            <div>
                <h3>وضع الصيانة</h3>
                <p>عند التفعيل، تُغلق كل الصفحات العامة للزوار وتظهر لهم صفحة "الموقع تحت الصيانة"، بينما تبقى لوحة التحكم متاحة لك دائمًا.</p>
            </div>

            <span class="admin-status {{ $maintenanceMode ? 'admin-status--inactive' : 'admin-status--active' }}">
                <i class="fa-solid {{ $maintenanceMode ? 'fa-triangle-exclamation' : 'fa-circle-check' }}" aria-hidden="true"></i>
                {{ $maintenanceMode ? 'مفعّل — الموقع مغلق' : 'غير مفعّل — الموقع متاح' }}
            </span>
        </div>

        <form
            action="{{ route('admin.settings.toggle-maintenance') }}"
            method="POST"
            @if (! $maintenanceMode)
                onsubmit="return confirm('سيتم إغلاق جميع الصفحات العامة للزوار فورًا. هل أنت متأكد؟');"
            @endif
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                class="admin-primary-button settings-toggle-button {{ $maintenanceMode ? 'settings-toggle-button--danger' : '' }}"
            >
                <i class="fa-solid {{ $maintenanceMode ? 'fa-play' : 'fa-power-off' }}" aria-hidden="true"></i>
                {{ $maintenanceMode ? 'إيقاف وضع الصيانة' : 'تفعيل وضع الصيانة' }}
            </button>
        </form>

    </div>

</div>

@endsection

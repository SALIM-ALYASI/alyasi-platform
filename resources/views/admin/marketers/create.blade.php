@extends('admin.layouts.app')

@section('title', 'إضافة مسوّق')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                إدارة المسوّقين
            </span>

            <h1 class="admin-page-header__title">
                إضافة مسوّق جديد
            </h1>

            <p class="admin-page-header__description">
                أدخل اسم المسوّق ورقم جواله، وسيُولَّد له كود فريد من 9 خانات تلقائيًا بعد الحفظ.
            </p>

        </div>

        <a href="{{ route('admin.marketers.index') }}" class="admin-primary-button">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            العودة للقائمة
        </a>

    </div>

    <div class="dashboard-panel">

        <form action="{{ route('admin.marketers.store') }}" method="POST">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label for="name">اسم المسوّق</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    >

                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">رقم الجوال</label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="form-control"
                        value="{{ old('phone') }}"
                        dir="ltr"
                        required
                    >

                    @error('phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="admin-primary-button">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    حفظ وتوليد الكود
                </button>
            </div>
        </form>

    </div>

</div>

@endsection

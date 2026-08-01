@extends('admin.layouts.app')

@section('title', 'إضافة عمل جديد')

@push('styles')
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/admin/css/works.css') }}"
>
@endpush

@section('content')

<div class="page-header">

    <div class="page-header__content">

        <h1 class="page-title">
            إضافة عمل جديد
        </h1>

        <p class="page-description">
            أضف مشروعًا أو تصميمًا أو إعلانًا أو أي عمل جديد إلى معرض أعمال ALYASI.
        </p>

    </div>

    <div class="page-header__actions">

        <a
            href="{{ route('admin.works.index') }}"
            class="btn btn-secondary"
        >
            <i class="fa-solid fa-arrow-right"></i>
            العودة إلى الأعمال
        </a>

    </div>

</div>

<div class="card work-form-card">

    <div class="card-header">

        <div>

            <h2 class="card-title">
                بيانات العمل
            </h2>

            <p class="card-description">
                أدخل المعلومات الأساسية والصور والتقنيات المستخدمة في العمل.
            </p>

        </div>

    </div>

    <div class="card-body">

        <form
            action="{{ route('admin.works.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="work-form"
        >

            @csrf

            @include('admin.works._form', [
                'work' => null,
            ])

            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    حفظ العمل
                </button>

                <a
                    href="{{ route('admin.works.index') }}"
                    class="btn btn-secondary"
                >
                    إلغاء
                </a>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ versioned_asset('assets/admin/js/works.js') }}"></script>
@endpush
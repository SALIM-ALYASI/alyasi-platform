@extends('admin.layouts.app')

@section('title', 'تعديل العمل')

@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('assets/admin/css/works.css') }}"
>
@endpush

@section('content')

<div class="page-header">

    <div class="page-header__content">

        <h1 class="page-title">
            تعديل العمل
        </h1>

        <p class="page-description">
            تحديث بيانات العمل والصور والتقنيات المرتبطة به.
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
                تعديل بيانات: {{ $work->title }}
            </p>

        </div>

    </div>

    <div class="card-body">

        <form
            action="{{ route('admin.works.update', $work) }}"
            method="POST"
            enctype="multipart/form-data"
            class="work-form"
        >

            @csrf
            @method('PUT')

            @include('admin.works._form', [
                'work' => $work,
            ])

            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    حفظ التعديلات
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
<script src="{{ asset('assets/admin/js/works.js') }}"></script>
@endpush
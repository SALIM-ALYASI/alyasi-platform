@extends('admin.layouts.app')

@section('title', 'إضافة خدمة')
@section('page-title', 'إضافة خدمة')
@section(
    'page-description',
    'إضافة خدمة جديدة وإنشاء روابطها الدائمة تلقائياً'
)

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>إضافة خدمة</h2>

        <p>
            أدخل المحتوى العربي، ويمكنك إضافة المحتوى
            الإنجليزي الآن أو لاحقاً.
        </p>
    </div>

    <a
        href="{{ route('admin.services.index') }}"
        class="dashboard-button"
    >
        <i class="fa-solid fa-arrow-right"></i>

        <span>رجوع</span>
    </a>
</section>

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="alert-content">
            <i class="fa-solid fa-circle-exclamation"></i>

            <div>
                <strong>تعذر حفظ الخدمة</strong>

                <p>
                    يرجى مراجعة الحقول التالية وتصحيح الأخطاء.
                </p>
            </div>
        </div>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="dashboard-panel">
    <form
        action="{{ route('admin.services.store') }}"
        method="POST"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf

        @include('admin.services._form', [
            'service' => null,
        ])
    </form>
</section>

@endsection

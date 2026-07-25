@extends('admin.layouts.app')

@section('title', 'تعديل الخدمة')
@section('page-title', 'تعديل الخدمة')
@section(
    'page-description',
    'تعديل بيانات الخدمة مع الحفاظ على روابطها الدائمة'
)

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>تعديل الخدمة</h2>

        <p>
            {{ $service->title_ar }}
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
                <strong>تعذر تحديث الخدمة</strong>

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
        action="{{ route('admin.services.update', $service) }}"
        method="POST"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf
        @method('PUT')

        @include('admin.services._form', [
            'service' => $service,
        ])
    </form>
</section>

@endsection
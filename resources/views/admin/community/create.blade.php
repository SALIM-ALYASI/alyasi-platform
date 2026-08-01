@extends('admin.layouts.app')

@section('title', 'إضافة منشور مجتمع')
@section('page-title', 'إضافة منشور مجتمع')
@section('page-description', 'إنشاء منشور أو نقاش أو فعالية جديدة في مجتمع ALYASI')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/admin/css/community.css') }}"
    >
@endpush

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>إضافة منشور جديد</h2>

        <p>
            أضف محتوى جديدًا إلى مجتمع ALYASI، وحدد نوعه
            وتصنيفه وتاريخ نشره.
        </p>
    </div>

    <a
        href="{{ route('admin.community.index') }}"
        class="dashboard-button"
    >
        <i class="fa-solid fa-arrow-right"></i>
        <span>العودة إلى المجتمع</span>
    </a>
</section>

<section class="dashboard-panel community-form-panel">

    <form
        action="{{ route('admin.community.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="community-form"
        id="communityForm"
        novalidate
    >
        @csrf

        @include('admin.community._form', [
            'communityPost' => null,
        ])
    </form>

</section>

@endsection

@push('scripts')
    <script src="{{ versioned_asset('assets/admin/js/community.js') }}"></script>
@endpush
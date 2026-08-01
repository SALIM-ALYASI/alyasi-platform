@extends('admin.layouts.app')

@section('title', 'تعديل منشور المجتمع')
@section('page-title', 'تعديل منشور المجتمع')
@section('page-description', 'تحديث بيانات منشور مجتمع ALYASI')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ versioned_asset('assets/admin/css/community.css') }}"
    >
@endpush

@section('content')

<section class="page-header">

    <div class="page-header-content">

        <h2>
            تعديل منشور المجتمع
        </h2>

        <p>
            قم بتعديل بيانات المنشور ثم احفظ التغييرات.
        </p>

    </div>

    <div class="page-header-actions">

        @if (
            Route::has('community.show')
            && !empty($communityPost->slug)
        )
            <a
                href="{{ route(
                    'community.show',
                    $communityPost->slug
                ) }}"
                target="_blank"
                rel="noopener"
                class="dashboard-button"
            >
                <i class="fa-solid fa-eye"></i>

                <span>
                    عرض المنشور
                </span>
            </a>
        @endif

        <a
            href="{{ route('admin.community.index') }}"
            class="dashboard-button"
        >
            <i class="fa-solid fa-arrow-right"></i>

            <span>
                العودة للقائمة
            </span>
        </a>

    </div>

</section>

<section class="dashboard-panel community-form-panel">

    <form
        action="{{ route(
            'admin.community.update',
            $communityPost
        ) }}"
        method="POST"
        enctype="multipart/form-data"
        class="community-form"
        id="communityForm"
        novalidate
    >
        @csrf
        @method('PUT')

        @include('admin.community._form')

    </form>

</section>

@endsection

@push('scripts')
    <script src="{{ versioned_asset('assets/admin/js/community.js') }}"></script>
@endpush
@extends('admin.layouts.app')

@section('title', 'إضافة سؤال')

@push('styles')
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/admin/css/faqs.css') }}"
>
@endpush

@section('content')

<div class="faqs-admin-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                إدارة الأسئلة الشائعة
            </span>

            <h1 class="admin-page-header__title">
                إضافة سؤال جديد
            </h1>

        </div>

        <a href="{{ route('admin.faqs.index') }}" class="faq-cancel-button">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            العودة للقائمة
        </a>

    </div>

    <form
        action="{{ route('admin.faqs.store') }}"
        method="POST"
    >
        @csrf

        @include('admin.faqs._form')

    </form>

</div>

@endsection

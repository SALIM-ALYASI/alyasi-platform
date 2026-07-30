@extends('admin.layouts.app')

@section('title', 'إضافة تقنية')

@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('assets/admin/css/technology-form.css') }}"
>
@endpush

@section('content')

<div class="technology-form-page">

    <div class="technology-form-header">

        <div class="technology-form-header__content">

            <span class="technology-form-header__badge">

                <i
                    class="fa-solid fa-microchip"
                    aria-hidden="true"
                ></i>

                إدارة التقنيات

            </span>

            <h1>
                إضافة تقنية جديدة
            </h1>

            <p>
                أضف تقنية جديدة ليتم استخدامها داخل الأعمال والصفحة الرئيسية لمنصة ALYASI.
            </p>

        </div>

        <div class="technology-form-header__actions">

            <a
                href="{{ route('admin.technologies.index') }}"
                class="technology-secondary-button"
            >
                <i
                    class="fa-solid fa-arrow-right"
                    aria-hidden="true"
                ></i>

                العودة للقائمة
            </a>

        </div>

    </div>

    <form
        action="{{ route('admin.technologies.store') }}"
        method="POST"
        class="technology-form"
    >

        @csrf

        @include('admin.technologies._form')

    </form>

</div>

@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', () => {

    const name = document.getElementById('name');
    const slug = document.getElementById('slug');

    if (!name || !slug) {
        return;
    }

    let edited = false;

    slug.addEventListener('input', () => {
        edited = slug.value.trim() !== '';
    });

    name.addEventListener('input', () => {

        if (edited) {
            return;
        }

        slug.value = name.value
            .trim()
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-');

    });

});

</script>

@endpush
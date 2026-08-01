@extends('admin.layouts.app')

@section('title', 'تعديل التقنية')

@push('styles')
<link
    rel="stylesheet"
    href="{{ versioned_asset('assets/admin/css/technology-form.css') }}"
>
@endpush

@section('content')

<div class="technology-form-page">

    <div class="technology-form-header">

        <div class="technology-form-header__content">

            <span class="technology-form-header__badge">

                <i
                    class="fa-solid fa-pen-to-square"
                    aria-hidden="true"
                ></i>

                إدارة التقنيات

            </span>

            <h1>
                تعديل التقنية
            </h1>

            <p>
                عدّل بيانات
                <strong>
                    {{ $technology->name }}
                </strong>
                وإعدادات ظهورها داخل منصة ALYASI.
            </p>

        </div>

        <div class="technology-form-header__actions">

            <a
                href="{{ route('admin.technologies.show', $technology) }}"
                class="technology-secondary-button"
            >
                <i
                    class="fa-solid fa-eye"
                    aria-hidden="true"
                ></i>

                عرض التقنية
            </a>

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
        action="{{ route('admin.technologies.update', $technology) }}"
        method="POST"
        class="technology-form"
    >

        @csrf
        @method('PUT')

        @include('admin.technologies._form')

    </form>

</div>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    if (!nameInput || !slugInput) {
        return;
    }

    const originalSlug = slugInput.value.trim();

    let slugEdited = originalSlug !== '';

    slugInput.addEventListener('input', function () {
        slugEdited = slugInput.value.trim() !== originalSlug;
    });

    function makeSlug(value) {
        return value
            .trim()
            .toLowerCase()
            .replace(/[^\p{L}\p{N}\s-]/gu, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    nameInput.addEventListener('input', function (event) {
        if (slugEdited) {
            return;
        }

        slugInput.value = makeSlug(event.target.value);
    });
});
</script>

@endpush
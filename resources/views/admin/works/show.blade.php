@extends('admin.layouts.app')

@section('title', 'عرض العمل')

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
            عرض العمل
        </h1>

        <p class="page-description">
            عرض جميع تفاصيل العمل والصور والتقنيات المرتبطة به.
        </p>

    </div>

    <div class="page-header__actions">

        <a
            href="{{ route('admin.works.index') }}"
            class="btn btn-secondary"
        >
            <i
                class="fa-solid fa-arrow-right"
                aria-hidden="true"
            ></i>

            العودة إلى الأعمال
        </a>

        <a
            href="{{ route('admin.works.edit', $work) }}"
            class="btn btn-primary"
        >
            <i
                class="fa-solid fa-pen"
                aria-hidden="true"
            ></i>

            تعديل العمل
        </a>

    </div>

</div>

@php
    $typeLabel = $types[$work->type] ?? $work->type;

    $serviceTitle = $work->service
        ? $work->service->localizedTitle()
        : null;
@endphp

<div class="work-show-grid">

    {{-- المحتوى الرئيسي --}}
    <div class="work-show-main">

        {{-- صورة الغلاف --}}
        <div class="card work-cover-card">

            <div class="work-cover">

                @if ($work->cover_image)

                    <img
                        src="{{ asset('storage/' . $work->cover_image) }}"
                        alt="{{ $work->title }}"
                        class="work-cover__image"
                    >

                @else

                    <div class="work-cover__placeholder">

                        <img
                            src="{{ asset('images/logo/logo-icon.png') }}"
                            alt="ALYASI"
                        >

                    </div>

                @endif

                <div class="work-cover__badges">

                    <span
                        class="badge {{ $work->is_active ? 'bg-success' : 'bg-danger' }}"
                    >
                        <i
                            class="fa-solid {{ $work->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"
                            aria-hidden="true"
                        ></i>

                        {{ $work->is_active ? 'نشط' : 'غير نشط' }}
                    </span>

                    @if ($work->is_featured)

                        <span class="badge bg-warning">

                            <i
                                class="fa-solid fa-star"
                                aria-hidden="true"
                            ></i>

                            عمل مميز

                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- تفاصيل العمل --}}
        <div class="card">

            <div class="card-header">

                <div>

                    <h2 class="card-title">
                        {{ $work->title }}
                    </h2>

                    <p class="card-description">
                        {{ $typeLabel }}
                    </p>

                </div>

            </div>

            <div class="card-body">

                @if ($work->short_description)

                    <div class="work-content-section">

                        <h3 class="work-content-title">
                            الوصف المختصر
                        </h3>

                        <p class="work-content-text">
                            {{ $work->short_description }}
                        </p>

                    </div>

                @endif

                @if ($work->description)

                    <div class="work-content-section">

                        <h3 class="work-content-title">
                            الوصف الكامل
                        </h3>

                        <div class="work-content-text work-content-text--long">
                            {!! nl2br(e($work->description)) !!}
                        </div>

                    </div>

                @endif

            </div>

        </div>

        {{-- معرض الصور --}}
        @if ($work->images->isNotEmpty())

            <div class="card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            معرض الصور
                        </h2>

                        <p class="card-description">
                            الصور الإضافية الخاصة بهذا العمل.
                        </p>

                    </div>

                    <span class="badge bg-info">
                        {{ $work->images->count() }} صور
                    </span>

                </div>

                <div class="card-body">

                    <div class="work-show-gallery">

                        @foreach ($work->images as $image)

                            <a
                                href="{{ asset('storage/' . $image->image) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="work-show-gallery__item"
                            >

                                <img
                                    src="{{ asset('storage/' . $image->image) }}"
                                    alt="{{ $image->alt_text ?: $work->title }}"
                                >

                                <span class="work-show-gallery__overlay">

                                    <i
                                        class="fa-solid fa-expand"
                                        aria-hidden="true"
                                    ></i>

                                </span>

                            </a>

                        @endforeach

                    </div>

                </div>

            </div>

        @endif

    </div>

    {{-- الشريط الجانبي --}}
    <aside class="work-show-sidebar">

        {{-- معلومات العمل --}}
        <div class="card">

            <div class="card-header">

                <h2 class="card-title">
                    معلومات العمل
                </h2>

            </div>

            <div class="card-body">

                <div class="work-info-list">

                    <div class="work-info-item">

                        <span class="work-info-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>

                        <div>

                            <small>
                                نوع العمل
                            </small>

                            <strong>
                                {{ $typeLabel }}
                            </strong>

                        </div>

                    </div>

                    <div class="work-info-item">

                        <span class="work-info-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>

                        <div>

                            <small>
                                الخدمة المرتبطة
                            </small>

                            <strong>
                                {{ $serviceTitle ?: 'غير مرتبط بخدمة' }}
                            </strong>

                        </div>

                    </div>

                    @if ($work->client_name)

                        <div class="work-info-item">

                            <span class="work-info-icon">
                                <i class="fa-solid fa-user"></i>
                            </span>

                            <div>

                                <small>
                                    اسم العميل
                                </small>

                                <strong>
                                    {{ $work->client_name }}
                                </strong>

                            </div>

                        </div>

                    @endif

                    @if ($work->completed_at)

                        <div class="work-info-item">

                            <span class="work-info-icon">
                                <i class="fa-regular fa-calendar"></i>
                            </span>

                            <div>

                                <small>
                                    تاريخ الإنجاز
                                </small>

                                <strong>
                                    {{ $work->completed_at->format('Y/m/d') }}
                                </strong>

                            </div>

                        </div>

                    @endif

                    <div class="work-info-item">

                        <span class="work-info-icon">
                            <i class="fa-solid fa-arrow-down-1-9"></i>
                        </span>

                        <div>

                            <small>
                                الترتيب
                            </small>

                            <strong>
                                {{ $work->sort_order }}
                            </strong>

                        </div>

                    </div>

                    <div class="work-info-item">

                        <span class="work-info-icon">
                            <i class="fa-solid fa-link"></i>
                        </span>

                        <div>

                            <small>
                                الرابط المختصر
                            </small>

                            <strong dir="ltr">
                                {{ $work->slug }}
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- رابط العمل --}}
        @if ($work->work_url)

            <div class="card">

                <div class="card-header">

                    <h2 class="card-title">
                        رابط العمل
                    </h2>

                </div>

                <div class="card-body">

                    <a
                        href="{{ $work->work_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-primary work-external-link"
                    >
                        <i
                            class="fa-solid fa-arrow-up-right-from-square"
                            aria-hidden="true"
                        ></i>

                        زيارة العمل
                    </a>

                </div>

            </div>

        @endif

        {{-- التقنيات --}}
        <div class="card">

            <div class="card-header">

                <h2 class="card-title">
                    التقنيات المستخدمة
                </h2>

            </div>

            <div class="card-body">

                @if ($work->technologies->isNotEmpty())

                    <div class="work-technologies">

                        @foreach ($work->technologies as $technology)

                            <span class="work-technology">

                                @if ($technology->icon)

                                    <i
                                        class="{{ $technology->icon }}"
                                        aria-hidden="true"
                                    ></i>

                                @else

                                    <i
                                        class="fa-solid fa-code"
                                        aria-hidden="true"
                                    ></i>

                                @endif

                                {{ $technology->name }}

                            </span>

                        @endforeach

                    </div>

                @else

                    <div class="form-empty-message">
                        لا توجد تقنيات مرتبطة بهذا العمل.
                    </div>

                @endif

            </div>

        </div>

        {{-- حذف العمل --}}
        <div class="card work-danger-card">

            <div class="card-header">

                <h2 class="card-title">
                    حذف العمل
                </h2>

            </div>

            <div class="card-body">

                <p class="work-danger-text">
                    سيتم حذف العمل وجميع الصور والبيانات المرتبطة به نهائيًا.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.works.destroy', $work) }}"
                    onsubmit="return confirm('هل أنت متأكد من حذف هذا العمل نهائيًا؟');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-danger work-delete-button"
                    >
                        <i
                            class="fa-regular fa-trash-can"
                            aria-hidden="true"
                        ></i>

                        حذف العمل
                    </button>

                </form>

            </div>

        </div>

    </aside>

</div>

@endsection
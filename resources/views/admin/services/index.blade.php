@extends('admin.layouts.app')

@section('title', 'إدارة الخدمات')
@section('page-title', 'إدارة الخدمات')
@section('page-description', 'إدارة جميع خدمات منصة ALYASI')

 @push('styles')
@push('styles')
<link
    rel="stylesheet"
    href="{{ asset('assets/admin/css/services-index.css') }}"
>
@endpush
@endpush

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>الخدمات</h2>

        <p>
            إدارة محتوى الخدمات وروابطها الدائمة
            وحالة ظهورها في المنصة.
        </p>
    </div>

    <a
        href="{{ route('admin.services.create') }}"
        class="dashboard-button dashboard-button-primary"
    >
        <i class="fa-solid fa-plus"></i>
        <span>إضافة خدمة</span>
    </a>
</section>

@if (session('success'))
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<section class="dashboard-panel">

    <div class="services-toolbar">
        <form
            action="{{ route('admin.services.index') }}"
            method="GET"
            class="services-search"
        >
            <div class="services-search-field">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="ابحث باسم الخدمة..."
                    aria-label="البحث في الخدمات"
                >
            </div>

            <button
                type="submit"
                class="dashboard-button dashboard-button-primary"
            >
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>بحث</span>
            </button>

            @if (request()->filled('search'))
                <a
                    href="{{ route('admin.services.index') }}"
                    class="dashboard-button"
                >
                    <i class="fa-solid fa-xmark"></i>
                    <span>مسح</span>
                </a>
            @endif
        </form>

        <div class="services-summary">
            <div class="services-summary-item">
                <i class="fa-solid fa-layer-group"></i>

                <span>
                    الإجمالي:
                    <strong>{{ $services->total() }}</strong>
                </span>
            </div>

            <div class="services-summary-item">
                <i class="fa-solid fa-list"></i>

                <span>
                    المعروض:
                    <strong>{{ $services->count() }}</strong>
                </span>
            </div>
        </div>
    </div>

    @if ($services->count())
        <div class="services-grid">
            @foreach ($services as $service)
                @php
                    $arabicPermalink = $service
                        ->permalinks
                        ->firstWhere('locale', 'ar');

                    $englishPermalink = $service
                        ->permalinks
                        ->firstWhere('locale', 'en');

                    $publicUrl = $arabicPermalink
                        ? route(
                            'services.show',
                            ['slug' => $arabicPermalink->slug]
                        )
                        : null;
                @endphp

                <article class="service-card">
                    <div class="service-card-image">
                        @if ($service->image)
                            <img
                                src="{{ asset($service->image) }}"
                                alt="{{ $service->title_ar }}"
                                loading="lazy"
                            >
                        @else
                            <div class="service-card-placeholder">
                                <i class="fa-regular fa-image"></i>
                            </div>
                        @endif

                        @if ($service->is_active)
                            <span
                                class="service-status
                                       service-status-active"
                            >
                                مفعلة
                            </span>
                        @else
                            <span
                                class="service-status
                                       service-status-inactive"
                            >
                                معطلة
                            </span>
                        @endif
                    </div>

                    <div class="service-card-body">
                        <div class="service-card-number">
                            الخدمة رقم #{{ $service->id }}
                        </div>

                        <h3 class="service-card-title">
                            {{ $service->title_ar }}
                        </h3>

                        @if ($service->title_en)
                            <div class="service-card-title-en">
                                {{ $service->title_en }}
                            </div>
                        @endif

                        <p class="service-card-description">
                            {{ $service->description_ar }}
                        </p>

                        <div class="service-links">
                            <div class="service-link-row">
                                <span class="service-link-label">
                                    العربي
                                </span>

                                @if ($arabicPermalink)
                                    <code class="service-link-value">
                                        {{ $arabicPermalink->slug }}
                                    </code>
                                @else
                                    <span
                                        class="service-link-missing"
                                    >
                                        الرابط غير موجود
                                    </span>
                                @endif
                            </div>

                            <div class="service-link-row">
                                <span class="service-link-label">
                                    الإنجليزي
                                </span>

                                @if ($englishPermalink)
                                    <code class="service-link-value">
                                        {{ $englishPermalink->slug }}
                                    </code>
                                @else
                                    <span
                                        class="service-link-missing"
                                    >
                                        غير مضاف
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="service-card-actions">
                            @if ($publicUrl)
                                <a
                                    href="{{ $publicUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="service-action
                                           service-action-view"
                                    title="عرض الخدمة"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                    <span>عرض</span>
                                </a>
                            @else
                                <span
                                    class="service-action
                                           service-action-disabled"
                                    title="لا يوجد رابط للخدمة"
                                >
                                    <i class="fa-solid fa-eye-slash"></i>
                                    <span>عرض</span>
                                </span>
                            @endif

                            <a
                                href="{{ route(
                                    'admin.services.edit',
                                    $service
                                ) }}"
                                class="service-action
                                       service-action-edit"
                                title="تعديل الخدمة"
                            >
                                <i class="fa-solid fa-pen"></i>
                                <span>تعديل</span>
                            </a>

                            <button
                                type="button"
                                class="service-action
                                       service-action-copy
                                       js-copy-service-link"
                                data-copy-url="{{ $publicUrl }}"
                                title="نسخ رابط الخدمة"
                                @disabled(!$publicUrl)
                            >
                                <i class="fa-regular fa-copy"></i>
                                <span>نسخ</span>
                            </button>

                            <form
                                action="{{ route(
                                    'admin.services.destroy',
                                    $service
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        'هل تريد حذف هذه الخدمة وروابطها الدائمة؟'
                                    );
                                "
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="service-action
                                           service-action-delete"
                                    title="حذف الخدمة"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    <span>حذف</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="services-empty">
            <div class="services-empty-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>

            <h3>لا توجد خدمات حالياً</h3>

            <p>
                أضف أول خدمة لعرضها وإدارتها من هذه الصفحة.
            </p>

            <a
                href="{{ route('admin.services.create') }}"
                class="dashboard-button dashboard-button-primary"
            >
                <i class="fa-solid fa-plus"></i>
                <span>إضافة أول خدمة</span>
            </a>
        </div>
    @endif

    @if ($services->hasPages())
        <div class="pagination-wrapper">
            {{ $services->links() }}
        </div>
    @endif

</section>

<div
    id="copyMessage"
    class="copy-message"
    role="status"
    aria-live="polite"
>
    <i class="fa-solid fa-circle-check"></i>
    <span>تم نسخ رابط الخدمة</span>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('admin/js/copy-link.js') }}"></script>
@endpush

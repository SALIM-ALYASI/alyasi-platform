@extends('admin.layouts.app')

@section('title', 'إدارة الخدمات')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                إدارة الخدمات
            </h1>

            <p class="admin-page-header__description">
                إدارة محتوى الخدمات وروابطها الدائمة وحالة ظهورها بالمنصة.
            </p>

        </div>

        <a
            href="{{ route('admin.services.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة خدمة
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي الخدمات</span>
                <strong>{{ number_format($services->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>المعروض بالصفحة</span>
                <strong>{{ number_format($services->count()) }}</strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.services.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__search">

                <label for="service-search">البحث</label>

                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>

                    <input
                        type="search"
                        id="service-search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="ابحث باسم الخدمة..."
                    >
                </div>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    بحث
                </button>

                @if (request()->filled('search'))
                    <a
                        href="{{ route('admin.services.index') }}"
                        class="admin-reset-button"
                    >
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        إعادة تعيين
                    </a>
                @endif

            </div>

        </form>

    </section>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة الخدمات</h2>
                <p>عرض وإدارة جميع الخدمات المسجلة بالمنصة.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($services->total()) }}
                خدمة
            </span>

        </div>

        @if ($services->count())

            <div class="admin-card-grid">

                @foreach ($services as $service)

                    @php
                        $arabicPermalink = $service->permalinks->firstWhere('locale', 'ar');
                        $englishPermalink = $service->permalinks->firstWhere('locale', 'en');

                        $publicUrl = $arabicPermalink
                            ? route('services.show', ['slug' => $arabicPermalink->slug])
                            : null;
                    @endphp

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            @if ($service->image)
                                <img
                                    src="{{ asset($service->image) }}"
                                    alt="{{ $service->title_ar }}"
                                    loading="lazy"
                                >
                            @else
                                <i class="fa-regular fa-image" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">
                                <span
                                    class="admin-status {{ $service->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    <i
                                        class="{{ $service->is_active
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-circle-minus' }}"
                                        aria-hidden="true"
                                    ></i>
                                    {{ $service->is_active ? 'مفعلة' : 'معطلة' }}
                                </span>
                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $service->title_ar }}</h3>

                                    @if ($service->title_en)
                                        <span dir="ltr">{{ $service->title_en }}</span>
                                    @endif
                                </div>
                            </div>

                            <p class="admin-data-card__description">
                                {{ $service->description_ar }}
                            </p>

                            <div class="admin-data-card__meta">

                                <div>
                                    <span>الرابط العربي</span>

                                    @if ($arabicPermalink)
                                        <code>{{ $arabicPermalink->slug }}</code>
                                    @else
                                        <strong>غير موجود</strong>
                                    @endif
                                </div>

                                <div>
                                    <span>الرابط الإنجليزي</span>

                                    @if ($englishPermalink)
                                        <code>{{ $englishPermalink->slug }}</code>
                                    @else
                                        <strong>غير مضاف</strong>
                                    @endif
                                </div>

                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            @if ($publicUrl)
                                <a
                                    href="{{ $publicUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="admin-action-button"
                                    title="عرض الخدمة"
                                    aria-label="عرض الخدمة"
                                >
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </a>
                            @endif

                            <a
                                href="{{ route('admin.services.edit', $service) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل الخدمة"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <button
                                type="button"
                                class="admin-action-button js-copy-service-link"
                                data-copy-url="{{ $publicUrl }}"
                                title="نسخ الرابط"
                                aria-label="نسخ رابط الخدمة"
                                @disabled(!$publicUrl)
                            >
                                <i class="fa-regular fa-copy" aria-hidden="true"></i>
                            </button>

                            <form
                                action="{{ route('admin.services.destroy', $service) }}"
                                method="POST"
                                onsubmit="return confirm('هل تريد حذف هذه الخدمة وروابطها الدائمة؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف الخدمة"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($services->hasPages())
                <div class="admin-pagination">
                    {{ $services->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                </div>

                <h3>لا توجد خدمات حاليًا</h3>

                <p>أضف أول خدمة لعرضها وإدارتها من هذه الصفحة.</p>

                <a href="{{ route('admin.services.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول خدمة
                </a>

            </div>

        @endif

    </section>

</div>

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
    <script src="{{ asset('assets/admin/js/copy-link.js') }}"></script>
@endpush

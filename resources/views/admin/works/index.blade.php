@extends('admin.layouts.app')

@section('title', 'إدارة الأعمال')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                إدارة الأعمال
            </h1>

            <p class="admin-page-header__description">
                إدارة الأعمال والمشاريع والتصاميم والمعارض الخاصة بمنصة ALYASI.
            </p>

        </div>

        <a
            href="{{ route('admin.works.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة عمل جديد
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي الأعمال</span>
                <strong>{{ number_format($works->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>الأعمال النشطة</span>
                <strong>{{ number_format($works->getCollection()->where('is_active', true)->count()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>الأعمال المميزة</span>
                <strong>{{ number_format($works->getCollection()->where('is_featured', true)->count()) }}</strong>
            </div>
        </article>

    </section>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة الأعمال</h2>
                <p>استعرض الأعمال وعدّل بياناتها أو احذفها من المنصة.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($works->total()) }}
                عمل
            </span>

        </div>

        @if ($works->count())

            <div class="admin-card-grid">

                @foreach ($works as $work)

                    @php
                        $typeLabel = $types[$work->type] ?? $work->type;
                        $serviceTitle = $work->service ? $work->service->localizedTitle() : null;
                    @endphp

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            @if ($work->cover_image)
                                <img
                                    src="{{ asset('storage/' . $work->cover_image) }}"
                                    alt="{{ $work->title }}"
                                >
                            @else
                                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">

                                <span
                                    class="admin-status {{ $work->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    <i
                                        class="{{ $work->is_active
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-circle-xmark' }}"
                                        aria-hidden="true"
                                    ></i>
                                    {{ $work->is_active ? 'نشط' : 'غير نشط' }}
                                </span>

                                @if ($work->is_featured)
                                    <span class="admin-status admin-status--featured">
                                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                                        مميز
                                    </span>
                                @endif

                            </div>

                            <div class="admin-data-card__badges admin-data-card__badges--end">
                                <span class="admin-status">
                                    #{{ $work->sort_order }}
                                </span>
                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $work->title }}</h3>
                                    <span>
                                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                                        {{ $typeLabel }}
                                    </span>
                                </div>
                            </div>

                            @if ($work->short_description)
                                <p class="admin-data-card__description">
                                    {{ \Illuminate\Support\Str::limit($work->short_description, 130) }}
                                </p>
                            @endif

                            <div class="admin-data-card__meta">

                                <div>
                                    <span>الخدمة</span>
                                    <strong>{{ $serviceTitle ?: 'غير مرتبط' }}</strong>
                                </div>

                                <div>
                                    <span>{{ $work->client_name ? 'العميل' : 'تاريخ الإنجاز' }}</span>
                                    <strong>
                                        {{ $work->client_name
                                            ?: ($work->completed_at?->format('Y/m/d') ?: '—') }}
                                    </strong>
                                </div>

                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ route('admin.works.show', $work) }}"
                                class="admin-action-button"
                                title="عرض"
                                aria-label="عرض العمل"
                            >
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </a>

                            <a
                                href="{{ route('admin.works.edit', $work) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل العمل"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form
                                method="POST"
                                action="{{ route('admin.works.destroy', $work) }}"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا العمل؟ سيتم حذف الصور والبيانات المرتبطة به.');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف العمل"
                                >
                                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($works->hasPages())
                <div class="admin-pagination">
                    {{ $works->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                </div>

                <h3>لا توجد أعمال حتى الآن</h3>

                <p>ابدأ بإضافة أول عمل إلى معرض أعمال ALYASI.</p>

                <a href="{{ route('admin.works.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة عمل جديد
                </a>

            </div>

        @endif

    </section>

</div>

@endsection

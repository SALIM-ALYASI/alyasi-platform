@extends('admin.layouts.app')

@section('title', 'إدارة المجتمع')
@section('page-title', 'إدارة المجتمع')
@section('page-description', 'إدارة منشورات وفعاليات مجتمع منصة ALYASI')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('assets/admin/css/community.css') }}"
    >
@endpush

@section('content')

<section class="page-header">
    <div class="page-header-content">
        <h2>المجتمع</h2>

        <p>
            إدارة منشورات المجتمع والنقاشات والفعاليات
            والإعلانات المنشورة في المنصة.
        </p>
    </div>

    <a
        href="{{ route('admin.community.create') }}"
        class="dashboard-button dashboard-button-primary"
    >
        <i class="fa-solid fa-plus"></i>
        <span>إضافة منشور</span>
    </a>
</section>

@if (session('success'))
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<section class="dashboard-panel">

    <div class="community-toolbar">

        <form
            action="{{ route('admin.community.index') }}"
            method="GET"
            class="community-filters"
        >
            <div class="community-search-field">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="ابحث في منشورات المجتمع..."
                    aria-label="البحث في منشورات المجتمع"
                >
            </div>

            <div class="community-filter-field">
                <select
                    name="category"
                    aria-label="التصنيف"
                >
                    <option value="">
                        جميع التصنيفات
                    </option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(
                                (string) request('category')
                                === (string) $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="community-filter-field">
                <select
                    name="type"
                    aria-label="نوع المنشور"
                >
                    <option value="">
                        جميع الأنواع
                    </option>

                    @foreach ($types as $typeValue => $typeLabel)
                        <option
                            value="{{ $typeValue }}"
                            @selected(
                                request('type') === $typeValue
                            )
                        >
                            {{ $typeLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="community-filter-field">
                <select
                    name="status"
                    aria-label="حالة المنشور"
                >
                    <option value="">
                        جميع الحالات
                    </option>

                    <option
                        value="active"
                        @selected(request('status') === 'active')
                    >
                        مفعلة
                    </option>

                    <option
                        value="inactive"
                        @selected(request('status') === 'inactive')
                    >
                        معطلة
                    </option>

                    <option
                        value="featured"
                        @selected(request('status') === 'featured')
                    >
                        مميزة
                    </option>

                    <option
                        value="scheduled"
                        @selected(request('status') === 'scheduled')
                    >
                        مجدولة
                    </option>
                </select>
            </div>

            <button
                type="submit"
                class="dashboard-button dashboard-button-primary"
            >
                <i class="fa-solid fa-filter"></i>
                <span>تصفية</span>
            </button>

            @if (
                request()->filled('search')
                || request()->filled('category')
                || request()->filled('type')
                || request()->filled('status')
            )
                <a
                    href="{{ route('admin.community.index') }}"
                    class="dashboard-button"
                >
                    <i class="fa-solid fa-xmark"></i>
                    <span>مسح</span>
                </a>
            @endif
        </form>

        <div class="community-summary">
            <div class="community-summary-item">
                <i class="fa-solid fa-comments"></i>

                <span>
                    الإجمالي:
                    <strong>{{ $posts->total() }}</strong>
                </span>
            </div>

            <div class="community-summary-item">
                <i class="fa-solid fa-list"></i>

                <span>
                    المعروض:
                    <strong>{{ $posts->count() }}</strong>
                </span>
            </div>
        </div>
    </div>

    @if ($posts->count())

        <div class="community-grid">

            @foreach ($posts as $post)
                @php
                    $typeLabels = [
                        'post' => 'منشور',
                        'discussion' => 'نقاش',
                        'event' => 'فعالية',
                        'announcement' => 'إعلان',
                    ];

                    $typeIcons = [
                        'post' => 'fa-regular fa-file-lines',
                        'discussion' => 'fa-solid fa-comments',
                        'event' => 'fa-regular fa-calendar-days',
                        'announcement' => 'fa-solid fa-bullhorn',
                    ];

                    $typeLabel = $typeLabels[$post->type]
                        ?? $post->type;

                    $typeIcon = $typeIcons[$post->type]
                        ?? 'fa-regular fa-file-lines';

                    $isScheduled = $post->published_at
                        && $post->published_at->isFuture();
                @endphp

                <article class="community-card">

                    <div class="community-card-image">

                        @if ($post->image)
                            <img
                                src="{{ asset('storage/' . $post->image) }}"
                                alt="{{ $post->title }}"
                                loading="lazy"
                            >
                        @else
                            <div class="community-card-placeholder">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        @endif

                        <div class="community-card-badges">

                            @if ($post->is_active)
                                <span
                                    class="community-status
                                           community-status-active"
                                >
                                    مفعّل
                                </span>
                            @else
                                <span
                                    class="community-status
                                           community-status-inactive"
                                >
                                    معطّل
                                </span>
                            @endif

                            @if ($post->is_featured)
                                <span
                                    class="community-status
                                           community-status-featured"
                                >
                                    <i class="fa-solid fa-star"></i>
                                    مميز
                                </span>
                            @endif

                            @if ($isScheduled)
                                <span
                                    class="community-status
                                           community-status-scheduled"
                                >
                                    <i class="fa-regular fa-clock"></i>
                                    مجدول
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="community-card-body">

                        <div class="community-card-meta">

                            <span class="community-card-number">
                                المنشور رقم #{{ $post->id }}
                            </span>

                            <span
                                class="community-type
                                       community-type-{{ $post->type }}"
                            >
                                <i class="{{ $typeIcon }}"></i>
                                {{ $typeLabel }}
                            </span>
                        </div>

                        <h3 class="community-card-title">
                            {{ $post->title }}
                        </h3>

                        @if ($post->category)
                            <div class="community-category">
                                <i class="fa-solid fa-tag"></i>
                                <span>
                                    {{ $post->category->name }}
                                </span>
                            </div>
                        @endif

                        @if ($post->short_description)
                            <p class="community-card-description">
                                {{ \Illuminate\Support\Str::limit(
                                    $post->short_description,
                                    150
                                ) }}
                            </p>
                        @else
                            <p class="community-card-description">
                                {{ \Illuminate\Support\Str::limit(
                                    strip_tags($post->content),
                                    150
                                ) }}
                            </p>
                        @endif

                        <div class="community-details">

                            <div class="community-detail-row">
                                <i class="fa-regular fa-calendar"></i>

                                <span>
                                    تاريخ النشر:
                                </span>

                                <strong>
                                    {{ $post->published_at
                                        ? $post->published_at->format(
                                            'Y/m/d - h:i A'
                                        )
                                        : 'غير محدد'
                                    }}
                                </strong>
                            </div>

                            @if (
                                $post->type === 'event'
                                && $post->event_start_at
                            )
                                <div class="community-detail-row">
                                    <i class="fa-regular fa-clock"></i>

                                    <span>
                                        بداية الفعالية:
                                    </span>

                                    <strong>
                                        {{ $post->event_start_at->format(
                                            'Y/m/d - h:i A'
                                        ) }}
                                    </strong>
                                </div>
                            @endif

                            @if (
                                $post->type === 'event'
                                && $post->location
                            )
                                <div class="community-detail-row">
                                    <i class="fa-solid fa-location-dot"></i>

                                    <span>
                                        الموقع:
                                    </span>

                                    <strong>
                                        {{ $post->location }}
                                    </strong>
                                </div>
                            @endif
                        </div>

                        <div class="community-card-actions">

                            @if (
                                Route::has('community.show')
                                && $post->slug
                            )
                                <a
                                    href="{{ route(
                                        'community.show',
                                        $post->slug
                                    ) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="community-action
                                           community-action-view"
                                    title="عرض المنشور"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                    <span>عرض</span>
                                </a>
                            @endif

                            <a
                                href="{{ route(
                                    'admin.community.edit',
                                    $post
                                ) }}"
                                class="community-action
                                       community-action-edit"
                                title="تعديل المنشور"
                            >
                                <i class="fa-solid fa-pen"></i>
                                <span>تعديل</span>
                            </a>

                            <form
                                action="{{ route(
                                    'admin.community.destroy',
                                    $post
                                ) }}"
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        'هل أنت متأكد من حذف هذا المنشور؟'
                                    );
                                "
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="community-action
                                           community-action-delete"
                                    title="حذف المنشور"
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

        <div class="community-empty">

            <div class="community-empty-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <h3>لا توجد منشورات حالياً</h3>

            <p>
                لم يتم العثور على منشورات مطابقة،
                أو لم تتم إضافة أي منشور حتى الآن.
            </p>

            @if (
                request()->filled('search')
                || request()->filled('category')
                || request()->filled('type')
                || request()->filled('status')
            )
                <a
                    href="{{ route('admin.community.index') }}"
                    class="dashboard-button"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>عرض جميع المنشورات</span>
                </a>
            @else
                <a
                    href="{{ route('admin.community.create') }}"
                    class="dashboard-button dashboard-button-primary"
                >
                    <i class="fa-solid fa-plus"></i>
                    <span>إضافة أول منشور</span>
                </a>
            @endif
        </div>

    @endif

 @if ($posts->hasPages())
    <nav
        class="community-pagination"
        aria-label="التنقل بين صفحات المجتمع"
    >
        <div class="community-pagination-info">
            عرض
            <strong>{{ $posts->firstItem() }}</strong>
            إلى
            <strong>{{ $posts->lastItem() }}</strong>
            من أصل
            <strong>{{ $posts->total() }}</strong>
            منشور
        </div>

        <div class="community-pagination-links">

            {{-- الصفحة السابقة --}}
            @if ($posts->onFirstPage())
                <span
                    class="community-pagination-button
                           is-disabled"
                    aria-disabled="true"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>السابق</span>
                </span>
            @else
                <a
                    href="{{ $posts->previousPageUrl() }}"
                    class="community-pagination-button"
                    rel="prev"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>السابق</span>
                </a>
            @endif

            {{-- أرقام الصفحات --}}
            @foreach ($posts->getUrlRange(
                max(1, $posts->currentPage() - 2),
                min($posts->lastPage(), $posts->currentPage() + 2)
            ) as $page => $url)
                @if ($page === $posts->currentPage())
                    <span
                        class="community-pagination-number
                               is-active"
                        aria-current="page"
                    >
                        {{ $page }}
                    </span>
                @else
                    <a
                        href="{{ $url }}"
                        class="community-pagination-number"
                    >
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- الصفحة التالية --}}
            @if ($posts->hasMorePages())
                <a
                    href="{{ $posts->nextPageUrl() }}"
                    class="community-pagination-button"
                    rel="next"
                >
                    <span>التالي</span>
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @else
                <span
                    class="community-pagination-button
                           is-disabled"
                    aria-disabled="true"
                >
                    <span>التالي</span>
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @endif

        </div>
    </nav>
@endif

</section>

@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/community.js') }}"></script>
@endpush
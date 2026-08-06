@extends('admin.layouts.app')

@section('title', 'التقييمات')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                التقييمات
            </h1>

            <p class="admin-page-header__description">
                راجع تقييمات الزوار (نجوم + تعليق) على الأعمال والخدمات قبل نشرها للعامة.
            </p>

        </div>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>بانتظار المراجعة</span>
                <strong>{{ number_format($pendingCount) }}</strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.reviews.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__field">

                <label for="review-type">النوع</label>

                <select id="review-type" name="type">
                    <option value="" @selected($type === '')>الكل</option>
                    <option value="service" @selected($type === 'service')>خدمة</option>
                    <option value="work" @selected($type === 'work')>عمل</option>
                </select>

            </div>

            <div class="admin-filter-form__field">

                <label for="review-status">الحالة</label>

                <select id="review-status" name="status">
                    <option value="" @selected($status === '')>كل الحالات</option>
                    <option value="pending" @selected($status === 'pending')>بانتظار المراجعة</option>
                    <option value="approved" @selected($status === 'approved')>معتمد</option>
                    <option value="rejected" @selected($status === 'rejected')>مرفوض</option>
                </select>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if ($status !== '' || $type !== '')
                    <a href="{{ route('admin.reviews.index') }}" class="admin-reset-button">
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
                <h2>قائمة التقييمات</h2>
                <p>اعتماد أو رفض تقييمات الزوار.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($reviews->total()) }}
                تقييم
            </span>

        </div>

        @if ($reviews->isNotEmpty())

            @php
                $statusLabels = [
                    'pending' => 'بانتظار المراجعة',
                    'approved' => 'معتمد',
                    'rejected' => 'مرفوض',
                ];

                $statusClasses = [
                    'pending' => 'admin-status--featured',
                    'approved' => 'admin-status--active',
                    'rejected' => 'admin-status--inactive',
                ];

                $typeLabels = [
                    'service' => 'خدمة',
                    'work' => 'عمل',
                ];
            @endphp

            <div class="admin-card-grid">

                @foreach ($reviews as $review)

                    <article class="admin-data-card">

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $review->name }}</h3>

                                    <p style="margin: 4px 0 0; font-size: 0.8rem; opacity: 0.6;">
                                        {{ $typeLabels[$review->reviewable_type] ?? $review->reviewable_type }}:
                                        {{ $review->reviewable?->title
                                            ?? $review->reviewable?->title_ar
                                            ?? '—' }}
                                    </p>
                                </div>

                                <span class="admin-status {{ $statusClasses[$review->status] ?? 'admin-status--inactive' }}">
                                    {{ $statusLabels[$review->status] ?? $review->status }}
                                </span>
                            </div>

                            <div style="margin: 6px 0; color: #d0aa6a; font-size: 0.85rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star" style="{{ $i > $review->rating ? 'opacity:.25' : '' }}"></i>
                                @endfor
                            </div>

                            <p class="admin-data-card__description">
                                {{ $review->body }}
                            </p>

                            <p style="margin: 8px 0 0; font-size: 0.78rem; opacity: 0.5;">
                                {{ $review->created_at->translatedFormat('d F Y - h:i A') }}
                                @if ($review->email)
                                    · {{ $review->email }}
                                @endif
                            </p>

                        </div>

                        <div class="admin-data-card__actions">

                            @if ($review->status !== 'approved')
                                <form
                                    action="{{ route('admin.reviews.approve', $review) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-action-button"
                                        title="اعتماد التقييم"
                                        aria-label="اعتماد التقييم"
                                    >
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endif

                            @if ($review->status !== 'rejected')
                                <form
                                    action="{{ route('admin.reviews.reject', $review) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-action-button"
                                        title="رفض التقييم"
                                        aria-label="رفض التقييم"
                                    >
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endif

                            <form
                                action="{{ route('admin.reviews.block', $review) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حظر هذا الزائر؟ سيتم رفض جميع تقييماته الحالية.');"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حظر الزائر"
                                    aria-label="حظر الزائر"
                                >
                                    <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.reviews.destroy', $review) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف التقييم"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($reviews->hasPages())
                <div class="admin-pagination">
                    {{ $reviews->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                </div>

                <h3>لا توجد تقييمات</h3>

                <p>لم يُضف أي زائر تقييمًا حتى الآن.</p>

            </div>

        @endif

    </section>

</div>

@endsection

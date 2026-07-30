@extends('admin.layouts.app')

@section('title', 'روابط التواصل الاجتماعي')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                روابط التواصل الاجتماعي
            </h1>

            <p class="admin-page-header__description">
                إدارة روابط وحسابات منصة ALYASI الرسمية بمنصات التواصل.
            </p>

        </div>

        <a
            href="{{ route('admin.social-links.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة منصة
        </a>

    </div>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة المنصات</h2>
                <p>عرض وإدارة جميع حسابات التواصل الاجتماعي.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($socialLinks->total()) }}
                منصة
            </span>

        </div>

        @if ($socialLinks->isNotEmpty())

            <div class="admin-card-grid">

                @foreach ($socialLinks as $socialLink)

                    <article class="admin-data-card">

                        <div
                            class="admin-data-card__media"
                            style="background: linear-gradient(135deg, {{ $socialLink->color ?: '#5E2324' }}, {{ $socialLink->color ?: '#5E2324' }}cc); color: #fff;"
                        >

                            <i class="{{ $socialLink->icon }}" aria-hidden="true"></i>

                            <div class="admin-data-card__badges">
                                <span
                                    class="admin-status {{ $socialLink->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    <i
                                        class="{{ $socialLink->is_active
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-eye-slash' }}"
                                        aria-hidden="true"
                                    ></i>
                                    {{ $socialLink->is_active ? 'نشط' : 'مخفي' }}
                                </span>
                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $socialLink->name }}</h3>
                                    <span>{{ $socialLink->platform }}</span>
                                </div>
                            </div>

                            <p class="admin-data-card__description">
                                {{ $socialLink->url }}
                            </p>

                            <div class="admin-data-card__meta">

                                <div>
                                    <span>اسم المستخدم</span>
                                    <strong>{{ $socialLink->username ?: 'غير محدد' }}</strong>
                                </div>

                                <div>
                                    <span>ترتيب الظهور</span>
                                    <strong>{{ number_format($socialLink->sort_order) }}</strong>
                                </div>

                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ $socialLink->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="admin-action-button"
                                title="زيارة الحساب"
                                aria-label="زيارة الحساب"
                            >
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </a>

                            <a
                                href="{{ route('admin.social-links.edit', $socialLink) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل المنصة"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form
                                action="{{ route('admin.social-links.destroy', $socialLink) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف رابط {{ addslashes($socialLink->name) }}؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف المنصة"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($socialLinks->hasPages())
                <div class="admin-pagination">
                    {{ $socialLinks->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                </div>

                <h3>لا توجد روابط تواصل اجتماعي</h3>

                <p>أضف أول منصة تواصل اجتماعي ليتم عرضها هنا.</p>

                <a href="{{ route('admin.social-links.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة منصة
                </a>

            </div>

        @endif

    </section>

</div>

@endsection

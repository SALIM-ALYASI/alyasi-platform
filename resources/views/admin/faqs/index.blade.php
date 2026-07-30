@extends('admin.layouts.app')

@section('title', 'إدارة الأسئلة الشائعة')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                الأسئلة الشائعة
            </h1>

            <p class="admin-page-header__description">
                أضف الأسئلة المتكررة عن ALYASI وتظهر بترتيب عشوائي بالصفحة الرئيسية.
            </p>

        </div>

        <a
            href="{{ route('admin.faqs.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة سؤال
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي الأسئلة</span>
                <strong>{{ number_format($faqs->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>الظاهرة بالصفحة</span>
                <strong>
                    {{ number_format(
                        \App\Models\Faq::query()->where('is_active', true)->count()
                    ) }}
                </strong>
            </div>
        </article>

    </section>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة الأسئلة</h2>
                <p>عرض وإدارة جميع الأسئلة الشائعة المسجلة بالمنصة.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($faqs->total()) }}
                سؤال
            </span>

        </div>

        @if ($faqs->isNotEmpty())

            <div class="admin-card-grid">

                @foreach ($faqs as $faq)

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            <i class="fa-solid fa-circle-question" aria-hidden="true"></i>

                            <div class="admin-data-card__badges">
                                <span
                                    class="admin-status {{ $faq->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    <i
                                        class="{{ $faq->is_active
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-circle-minus' }}"
                                        aria-hidden="true"
                                    ></i>
                                    {{ $faq->is_active ? 'مفعل' : 'مخفي' }}
                                </span>
                            </div>

                            <div class="admin-data-card__badges admin-data-card__badges--end">
                                <span class="admin-status">
                                    {{ number_format($faq->sort_order) }}
                                </span>
                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $faq->question_ar }}</h3>
                                </div>
                            </div>

                            <p class="admin-data-card__description">
                                {{ $faq->answer_ar }}
                            </p>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ route('admin.faqs.edit', $faq) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل السؤال"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form
                                action="{{ route('admin.faqs.toggle-status', $faq) }}"
                                method="POST"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="{{ $faq->is_active ? 'إخفاء السؤال' : 'تفعيل السؤال' }}"
                                    aria-label="{{ $faq->is_active ? 'إخفاء السؤال' : 'تفعيل السؤال' }}"
                                >
                                    <i
                                        class="{{ $faq->is_active
                                            ? 'fa-solid fa-eye-slash'
                                            : 'fa-solid fa-eye' }}"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.faqs.destroy', $faq) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف السؤال"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($faqs->hasPages())
                <div class="admin-pagination">
                    {{ $faqs->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                </div>

                <h3>لا توجد أسئلة شائعة</h3>

                <p>لم تُضف أي أسئلة حتى الآن.</p>

                <a href="{{ route('admin.faqs.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول سؤال
                </a>

            </div>

        @endif

    </section>

</div>

@endsection

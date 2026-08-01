@extends('admin.layouts.app')

@section('title', 'المسوّقون')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                برنامج التسويق بالعمولة
            </span>

            <h1 class="admin-page-header__title">
                المسوّقون
            </h1>

            <p class="admin-page-header__description">
                سجّل مسوّقًا جديدًا واحصل له على كود من 9 خانات، وسجّل عملاءه لاحتساب عمولته تلقائيًا.
                العمولة تبدأ 5٪ وتزيد 1٪ كل 10 عملاء.
            </p>

        </div>

        <a
            href="{{ route('admin.marketers.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة مسوّق
        </a>

    </div>

    <section class="admin-list-panel">

        <div class="admin-list-panel__header">

            <div>
                <h2>قائمة المسوّقين</h2>
                <p>عرض وإدارة جميع المسوّقين وعمولاتهم.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($marketers->total()) }}
                مسوّق
            </span>

        </div>

        @if ($marketers->isNotEmpty())

            <div class="admin-card-grid">

                @foreach ($marketers as $marketer)

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            <i class="fa-solid fa-user-tie" aria-hidden="true"></i>

                            <div class="admin-data-card__badges">
                                <span
                                    class="admin-status {{ $marketer->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    <i
                                        class="{{ $marketer->is_active
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-circle-minus' }}"
                                        aria-hidden="true"
                                    ></i>
                                    {{ $marketer->is_active ? 'مفعل' : 'موقوف' }}
                                </span>
                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $marketer->name }}</h3>
                                </div>
                            </div>

                            <p class="admin-data-card__description" dir="ltr" style="text-align: right;">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                {{ $marketer->phone }}
                            </p>

                            <p class="admin-data-card__description">
                                <strong style="letter-spacing: 2px;" dir="ltr">{{ $marketer->code }}</strong>
                                — كود المسوّق
                            </p>

                            <p class="admin-data-card__description">
                                {{ number_format($marketer->customers_count) }} عميل
                                — عمولة حالية {{ number_format($marketer->currentCommissionRate(), 0) }}٪
                                — إجمالي العمولات {{ number_format($marketer->totalCommission(), 2) }}
                            </p>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ route('admin.marketers.edit', $marketer) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل / عرض العملاء"
                                aria-label="تعديل المسوّق"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form
                                action="{{ route('admin.marketers.toggle-status', $marketer) }}"
                                method="POST"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="{{ $marketer->is_active ? 'إيقاف المسوّق' : 'تفعيل المسوّق' }}"
                                    aria-label="{{ $marketer->is_active ? 'إيقاف المسوّق' : 'تفعيل المسوّق' }}"
                                >
                                    <i
                                        class="{{ $marketer->is_active
                                            ? 'fa-solid fa-eye-slash'
                                            : 'fa-solid fa-eye' }}"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.marketers.destroy', $marketer) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا المسوّق؟ سيُحذف معه كل سجل عملائه.');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف المسوّق"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($marketers->hasPages())
                <div class="admin-pagination">
                    {{ $marketers->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                </div>

                <h3>لا يوجد مسوّقون</h3>

                <p>لم يتم تسجيل أي مسوّق حتى الآن.</p>

                <a href="{{ route('admin.marketers.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول مسوّق
                </a>

            </div>

        @endif

    </section>

</div>

@endsection

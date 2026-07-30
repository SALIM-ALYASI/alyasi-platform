@extends('admin.layouts.app')

@section('title', 'إدارة التقنيات')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                إدارة التقنيات
            </h1>

            <p class="admin-page-header__description">
                أضف التقنيات المستخدمة في مشاريع ALYASI، وحدد ترتيب ظهورها وحالتها داخل المنصة.
            </p>

        </div>

        <a
            href="{{ route('admin.technologies.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة تقنية
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-microchip" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي التقنيات</span>
                <strong>{{ number_format($technologies->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>الظاهرة في الصفحة</span>
                <strong>
                    {{ number_format(
                        \App\Models\Technology::query()->where('is_active', true)->count()
                    ) }}
                </strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.technologies.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__search">

                <label for="technology-search">البحث</label>

                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>

                    <input
                        type="search"
                        id="technology-search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="ابحث بالاسم أو الرابط المختصر..."
                    >
                </div>

            </div>

            <div class="admin-filter-form__field">

                <label for="technology-status">الحالة</label>

                <select id="technology-status" name="status">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>مفعلة</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>مخفية</option>
                </select>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if (request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.technologies.index') }}" class="admin-reset-button">
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
                <h2>قائمة التقنيات</h2>
                <p>عرض وإدارة جميع التقنيات المسجلة في المنصة.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($technologies->total()) }}
                تقنية
            </span>

        </div>

        @if ($technologies->isNotEmpty())

            <div class="admin-card-grid">

                @foreach ($technologies as $technology)

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            <i
                                class="{{ $technology->icon ?: 'fa-solid fa-code' }}"
                                aria-hidden="true"
                            ></i>

                            <div class="admin-data-card__badges">
                                <span
                                    class="admin-status {{ $technology->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    <i
                                        class="{{ $technology->is_active
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-circle-minus' }}"
                                        aria-hidden="true"
                                    ></i>
                                    {{ $technology->is_active ? 'مفعلة' : 'مخفية' }}
                                </span>
                            </div>

                            <div class="admin-data-card__badges admin-data-card__badges--end">
                                <span class="admin-status">
                                    {{ number_format($technology->sort_order) }}
                                </span>
                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $technology->name }}</h3>
                                </div>
                            </div>

                            <div class="admin-data-card__meta">

                                <div>
                                    <span>الرابط المختصر</span>
                                    <code>{{ $technology->slug }}</code>
                                </div>

                                <div>
                                    <span>الأعمال المرتبطة</span>
                                    <strong>
                                        <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                                        {{ number_format($technology->works_count) }}
                                    </strong>
                                </div>

                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ route('admin.technologies.show', $technology) }}"
                                class="admin-action-button"
                                title="عرض"
                                aria-label="عرض التقنية"
                            >
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </a>

                            <a
                                href="{{ route('admin.technologies.edit', $technology) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل التقنية"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form
                                action="{{ route('admin.technologies.toggle-status', $technology) }}"
                                method="POST"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="{{ $technology->is_active ? 'إخفاء التقنية' : 'تفعيل التقنية' }}"
                                    aria-label="{{ $technology->is_active ? 'إخفاء التقنية' : 'تفعيل التقنية' }}"
                                >
                                    <i
                                        class="{{ $technology->is_active
                                            ? 'fa-solid fa-eye-slash'
                                            : 'fa-solid fa-eye' }}"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.technologies.destroy', $technology) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذه التقنية؟ سيتم فصلها عن الأعمال المرتبطة بها.');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف التقنية"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($technologies->hasPages())
                <div class="admin-pagination">
                    {{ $technologies->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-microchip" aria-hidden="true"></i>
                </div>

                <h3>لا توجد تقنيات</h3>

                <p>لم يتم العثور على تقنيات مطابقة، أو لم تُضف أي تقنية حتى الآن.</p>

                <a href="{{ route('admin.technologies.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول تقنية
                </a>

            </div>

        @endif

    </section>

</div>

@endsection

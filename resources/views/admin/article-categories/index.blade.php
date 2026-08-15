@extends('admin.layouts.app')

@section('title', 'تصنيفات المقالات')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">
            <span class="admin-page-header__badge">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                مقالاتي
            </span>

            <h1 class="admin-page-header__title">تصنيفات المقالات</h1>

            <p class="admin-page-header__description">
                نظّم مقالات قسم "مقالاتي" في تصنيفات، وحدد ترتيب ظهورها وحالتها.
            </p>
        </div>

        <a href="{{ route('admin.article-categories.create') }}" class="admin-primary-button">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة تصنيف
        </a>

    </div>

    <section class="admin-filter-panel">
        <form action="{{ route('admin.article-categories.index') }}" method="GET" class="admin-filter-form">
            <div class="admin-filter-form__search">
                <label for="category-search">البحث</label>

                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="category-search" name="search" value="{{ request('search') }}" placeholder="ابحث بالاسم أو الرابط المختصر...">
                </div>
            </div>

            <div class="admin-filter-form__actions">
                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if (request()->filled('search'))
                    <a href="{{ route('admin.article-categories.index') }}" class="admin-reset-button">
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
                <h2>قائمة التصنيفات</h2>
                <p>عرض وإدارة جميع تصنيفات المقالات.</p>
            </div>

            <span class="admin-results-count">{{ number_format($categories->total()) }} تصنيف</span>
        </div>

        @if ($categories->isNotEmpty())

            <div class="admin-card-grid">
                @foreach ($categories as $category)
                    <article class="admin-data-card">

                        <div class="admin-data-card__media">
                            <i class="fa-solid fa-tag" aria-hidden="true"></i>

                            <div class="admin-data-card__badges">
                                <span class="admin-status {{ $category->is_active ? 'admin-status--active' : 'admin-status--inactive' }}">
                                    <i class="{{ $category->is_active ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-minus' }}" aria-hidden="true"></i>
                                    {{ $category->is_active ? 'مفعّل' : 'مخفي' }}
                                </span>
                            </div>
                        </div>

                        <div class="admin-data-card__body">
                            <div class="admin-data-card__heading">
                                <div><h3>{{ $category->name_ar }}</h3></div>
                            </div>

                            <div class="admin-data-card__meta">
                                <div>
                                    <span>الرابط المختصر</span>
                                    <code>{{ $category->slug }}</code>
                                </div>
                                <div>
                                    <span>عدد المقالات</span>
                                    <strong><i class="fa-solid fa-file-lines" aria-hidden="true"></i> {{ number_format($category->articles_count) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="admin-data-card__actions">
                            <a href="{{ route('admin.article-categories.edit', $category) }}" class="admin-action-button admin-action-button--edit" title="تعديل" aria-label="تعديل التصنيف">
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form action="{{ route('admin.article-categories.toggle-status', $category) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="admin-action-button" title="{{ $category->is_active ? 'إخفاء' : 'تفعيل' }}" aria-label="{{ $category->is_active ? 'إخفاء التصنيف' : 'تفعيل التصنيف' }}">
                                    <i class="{{ $category->is_active ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye' }}" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.article-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا التصنيف؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-action-button admin-action-button--delete" title="حذف" aria-label="حذف التصنيف">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>

                    </article>
                @endforeach
            </div>

            @if ($categories->hasPages())
                <div class="admin-pagination">
                    {{ $categories->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">
                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-tags" aria-hidden="true"></i>
                </div>

                <h3>لا توجد تصنيفات</h3>
                <p>أضف أول تصنيف لتنظيم مقالاتك.</p>

                <a href="{{ route('admin.article-categories.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول تصنيف
                </a>
            </div>

        @endif

    </section>

</div>

@endsection

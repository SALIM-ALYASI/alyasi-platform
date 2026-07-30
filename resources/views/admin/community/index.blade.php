@extends('admin.layouts.app')

@section('title', 'إدارة المجتمع')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                إدارة المجتمع
            </h1>

            <p class="admin-page-header__description">
                إدارة منشورات المجتمع والنقاشات والفعاليات والإعلانات المنشورة بالمنصة.
            </p>

        </div>

        <a
            href="{{ route('admin.community.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة منشور
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي المنشورات</span>
                <strong>{{ number_format($posts->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>المعروض بالصفحة</span>
                <strong>{{ number_format($posts->count()) }}</strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.community.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__search">

                <label for="community-search">البحث</label>

                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>

                    <input
                        type="search"
                        id="community-search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="ابحث في منشورات المجتمع..."
                    >
                </div>

            </div>

            <div class="admin-filter-form__field">

                <label for="community-category">التصنيف</label>

                <select id="community-category" name="category">
                    <option value="">جميع التصنيفات</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) request('category') === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

            </div>

            <div class="admin-filter-form__field">

                <label for="community-type">النوع</label>

                <select id="community-type" name="type">
                    <option value="">جميع الأنواع</option>

                    @foreach ($types as $typeValue => $typeLabel)
                        <option
                            value="{{ $typeValue }}"
                            @selected(request('type') === $typeValue)
                        >
                            {{ $typeLabel }}
                        </option>
                    @endforeach
                </select>

            </div>

            <div class="admin-filter-form__field">

                <label for="community-status">الحالة</label>

                <select id="community-status" name="status">
                    <option value="">جميع الحالات</option>
                    <option value="active" @selected(request('status') === 'active')>مفعلة</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>معطلة</option>
                    <option value="featured" @selected(request('status') === 'featured')>مميزة</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>مجدولة</option>
                </select>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تصفية
                </button>

                @if (
                    request()->filled('search')
                    || request()->filled('category')
                    || request()->filled('type')
                    || request()->filled('status')
                )
                    <a href="{{ route('admin.community.index') }}" class="admin-reset-button">
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
                <h2>قائمة المنشورات</h2>
                <p>عرض وإدارة جميع منشورات المجتمع.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($posts->total()) }}
                منشور
            </span>

        </div>

        @if ($posts->count())

            <div class="admin-card-grid">

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

                        $typeLabel = $typeLabels[$post->type] ?? $post->type;
                        $typeIcon = $typeIcons[$post->type] ?? 'fa-regular fa-file-lines';
                        $isScheduled = $post->published_at && $post->published_at->isFuture();
                    @endphp

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            @if ($post->image)
                                <img
                                    src="{{ asset('storage/' . $post->image) }}"
                                    alt="{{ $post->title }}"
                                    loading="lazy"
                                >
                            @else
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">

                                <span
                                    class="admin-status {{ $post->is_active
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    {{ $post->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>

                                @if ($post->is_featured)
                                    <span class="admin-status admin-status--featured">
                                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                                        مميز
                                    </span>
                                @endif

                            </div>

                            @if ($isScheduled)
                                <div class="admin-data-card__badges admin-data-card__badges--end">
                                    <span class="admin-status admin-status--featured">
                                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                        مجدول
                                    </span>
                                </div>
                            @endif

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $post->title }}</h3>
                                    <span>
                                        <i class="{{ $typeIcon }}" aria-hidden="true"></i>
                                        {{ $typeLabel }}
                                        @if ($post->category)
                                            · {{ $post->category->name }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <p class="admin-data-card__description">
                                {{ \Illuminate\Support\Str::limit(
                                    $post->short_description ?: strip_tags($post->content),
                                    150
                                ) }}
                            </p>

                            <div class="admin-data-card__meta">

                                <div>
                                    <span>تاريخ النشر</span>
                                    <strong>
                                        {{ $post->published_at
                                            ? $post->published_at->format('Y/m/d')
                                            : 'غير محدد' }}
                                    </strong>
                                </div>

                                <div>
                                    <span>الموقع</span>
                                    <strong>
                                        {{ $post->type === 'event' && $post->location
                                            ? $post->location
                                            : '—' }}
                                    </strong>
                                </div>

                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            @if (Route::has('community.show') && $post->slug)
                                <a
                                    href="{{ route('community.show', $post->slug) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="admin-action-button"
                                    title="عرض"
                                    aria-label="عرض المنشور"
                                >
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </a>
                            @endif

                            <a
                                href="{{ route('admin.community.edit', $post) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل المنشور"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form
                                action="{{ route('admin.community.destroy', $post) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا المنشور؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف المنشور"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($posts->hasPages())
                <div class="admin-pagination">
                    {{ $posts->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                </div>

                <h3>لا توجد منشورات حاليًا</h3>

                <p>لم يتم العثور على منشورات مطابقة، أو لم تتم إضافة أي منشور حتى الآن.</p>

                @if (
                    request()->filled('search')
                    || request()->filled('category')
                    || request()->filled('type')
                    || request()->filled('status')
                )
                    <a href="{{ route('admin.community.index') }}" class="admin-primary-button">
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        عرض جميع المنشورات
                    </a>
                @else
                    <a href="{{ route('admin.community.create') }}" class="admin-primary-button">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        إضافة أول منشور
                    </a>
                @endif

            </div>

        @endif

    </section>

</div>

@endsection

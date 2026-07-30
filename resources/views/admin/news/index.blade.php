@extends('admin.layouts.app')

@section('title', 'إدارة الأخبار')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                إدارة المحتوى
            </span>

            <h1 class="admin-page-header__title">
                إدارة الأخبار
            </h1>

            <p class="admin-page-header__description">
                عرض وإدارة الأخبار التقنية المنشورة بالمنصة، سواء المضافة يدويًا أو عبر بوت الأخبار.
            </p>

        </div>

        <a
            href="{{ route('admin.news.create') }}"
            class="admin-primary-button"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            إضافة خبر
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي الأخبار</span>
                <strong>{{ number_format($articles->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>منشورة</span>
                <strong>
                    {{ number_format(
                        \App\Models\NewsArticle::query()
                            ->where('status', \App\Models\NewsArticle::STATUS_PUBLISHED)
                            ->count()
                    ) }}
                </strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>مسودات</span>
                <strong>
                    {{ number_format(
                        \App\Models\NewsArticle::query()
                            ->where('status', \App\Models\NewsArticle::STATUS_DRAFT)
                            ->count()
                    ) }}
                </strong>
            </div>
        </article>

    </section>

    <section class="admin-filter-panel">

        <form
            action="{{ route('admin.news.index') }}"
            method="GET"
            class="admin-filter-form"
        >

            <div class="admin-filter-form__search">

                <label for="news-search">البحث</label>

                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>

                    <input
                        type="search"
                        id="news-search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="ابحث بعنوان الخبر..."
                    >
                </div>

            </div>

            <div class="admin-filter-form__field">

                <label for="news-category">التصنيف</label>

                <select id="news-category" name="category">
                    <option value="">جميع التصنيفات</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) request('category') === (string) $category->id)
                        >
                            {{ $category->name_ar }}
                        </option>
                    @endforeach
                </select>

            </div>

            <div class="admin-filter-form__field">

                <label for="news-status">الحالة</label>

                <select id="news-status" name="status">
                    <option value="">كل الحالات</option>
                    <option value="published" @selected(request('status') === 'published')>منشور</option>
                    <option value="draft" @selected(request('status') === 'draft')>مسودة</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>مجدول</option>
                    <option value="archived" @selected(request('status') === 'archived')>مؤرشف</option>
                </select>

            </div>

            <div class="admin-filter-form__actions">

                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if (request()->filled('search') || request()->filled('category') || request()->filled('status'))
                    <a href="{{ route('admin.news.index') }}" class="admin-reset-button">
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
                <h2>قائمة الأخبار</h2>
                <p>عرض وإدارة جميع الأخبار المسجلة بالمنصة.</p>
            </div>

            <span class="admin-results-count">
                {{ number_format($articles->total()) }}
                خبر
            </span>

        </div>

        @if ($articles->count())

            <div class="admin-card-grid">

                @foreach ($articles as $article)

                    @php
                        $statusLabels = [
                            'draft' => 'مسودة',
                            'scheduled' => 'مجدول',
                            'published' => 'منشور',
                            'archived' => 'مؤرشف',
                        ];

                        $statusLabel = $statusLabels[$article->status] ?? $article->status;
                        $isPublished = $article->status === \App\Models\NewsArticle::STATUS_PUBLISHED;
                    @endphp

                    <article class="admin-data-card">

                        <div class="admin-data-card__media">

                            @if ($article->image)
                                <img
                                    src="{{ \Illuminate\Support\Str::startsWith($article->image, ['http://', 'https://'])
                                        ? $article->image
                                        : asset('storage/' . $article->image) }}"
                                    alt="{{ $article->title_ar }}"
                                    loading="lazy"
                                >
                            @else
                                <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">

                                <span
                                    class="admin-status {{ $isPublished
                                        ? 'admin-status--active'
                                        : 'admin-status--inactive' }}"
                                >
                                    {{ $statusLabel }}
                                </span>

                                @if ($article->is_breaking)
                                    <span class="admin-status admin-status--inactive">
                                        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                                        عاجل
                                    </span>
                                @endif

                                @if ($article->is_featured)
                                    <span class="admin-status admin-status--featured">
                                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                                        مميز
                                    </span>
                                @endif

                            </div>

                        </div>

                        <div class="admin-data-card__body">

                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $article->title_ar }}</h3>
                                    <span>
                                        {{ $article->category?->name_ar ?? 'بدون تصنيف' }}
                                        @if ($article->source_name)
                                            · {{ $article->source_name }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="admin-data-card__meta">

                                <div>
                                    <span>تاريخ النشر</span>
                                    <strong>
                                        {{ $article->published_at
                                            ? $article->published_at->format('Y/m/d')
                                            : 'غير محدد' }}
                                    </strong>
                                </div>

                                <div>
                                    <span>المشاهدات</span>
                                    <strong>{{ number_format($article->views_count) }}</strong>
                                </div>

                            </div>

                        </div>

                        <div class="admin-data-card__actions">

                            <a
                                href="{{ route('admin.news.edit', $article) }}"
                                class="admin-action-button admin-action-button--edit"
                                title="تعديل"
                                aria-label="تعديل الخبر"
                            >
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form action="{{ route('admin.news.toggle-status', $article) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="{{ $isPublished ? 'تحويل إلى مسودة' : 'نشر' }}"
                                    aria-label="{{ $isPublished ? 'تحويل إلى مسودة' : 'نشر الخبر' }}"
                                >
                                    <i
                                        class="{{ $isPublished ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye' }}"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.news.toggle-featured', $article) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="admin-action-button"
                                    title="{{ $article->is_featured ? 'إلغاء التمييز' : 'تمييز' }}"
                                    aria-label="{{ $article->is_featured ? 'إلغاء التمييز' : 'تمييز الخبر' }}"
                                >
                                    <i
                                        class="{{ $article->is_featured
                                            ? 'fa-solid fa-star'
                                            : 'fa-regular fa-star' }}"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </form>

                            <form
                                action="{{ route('admin.news.destroy', $article) }}"
                                method="POST"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الخبر؟');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="admin-action-button admin-action-button--delete"
                                    title="حذف"
                                    aria-label="حذف الخبر"
                                >
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>

                        </div>

                    </article>

                @endforeach

            </div>

            @if ($articles->hasPages())
                <div class="admin-pagination">
                    {{ $articles->links() }}
                </div>
            @endif

        @else

            <div class="admin-empty-state">

                <div class="admin-empty-state__icon">
                    <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                </div>

                <h3>لا توجد أخبار حاليًا</h3>

                <p>لم يتم العثور على أخبار مطابقة، أو لم تتم إضافة أي خبر حتى الآن.</p>

                <a href="{{ route('admin.news.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول خبر
                </a>

            </div>

        @endif

    </section>

</div>

@endsection

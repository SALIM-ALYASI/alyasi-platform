@extends('admin.layouts.app')

@section('title', 'إدارة مقالاتي')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">
            <span class="admin-page-header__badge">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                مقالاتي
            </span>

            <h1 class="admin-page-header__title">إدارة المقالات</h1>

            <p class="admin-page-header__description">
                إدارة مقالات قسم "مقالاتي"، تصنيفاتها، حالة نشرها، وظهورها المميز.
            </p>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="{{ route('admin.article-categories.index') }}" class="dashboard-button">
                <i class="fa-solid fa-tags"></i>
                <span>التصنيفات</span>
            </a>

            <a href="{{ route('admin.articles.create') }}" class="admin-primary-button">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                إضافة مقال
            </a>
        </div>

    </div>

    <section class="admin-stats-row">
        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
            </div>
            <div class="admin-stat-card__content">
                <span>إجمالي المقالات</span>
                <strong>{{ number_format($articles->total()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </div>
            <div class="admin-stat-card__content">
                <span>المنشورة</span>
                <strong>{{ number_format(\App\Models\Article::query()->where('status', 'published')->count()) }}</strong>
            </div>
        </article>
    </section>

    <section class="admin-filter-panel">
        <form action="{{ route('admin.articles.index') }}" method="GET" class="admin-filter-form">
            <div class="admin-filter-form__search">
                <label for="article-search">البحث</label>

                <div class="admin-input-with-icon">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="article-search" name="search" value="{{ request('search') }}" placeholder="ابحث بعنوان المقال...">
                </div>
            </div>

            <div class="admin-filter-form__field">
                <label for="article-status">الحالة</label>
                <select id="article-status" name="status">
                    <option value="">كل الحالات</option>
                    <option value="draft" @selected(request('status') === 'draft')>مسودة</option>
                    <option value="published" @selected(request('status') === 'published')>منشور</option>
                    <option value="archived" @selected(request('status') === 'archived')>مؤرشف</option>
                </select>
            </div>

            <div class="admin-filter-form__field">
                <label for="article-category">التصنيف</label>
                <select id="article-category" name="article_category_id">
                    <option value="">كل التصنيفات</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('article_category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="admin-filter-form__actions">
                <button type="submit" class="admin-filter-button">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    تطبيق
                </button>

                @if (request()->filled('search') || request()->filled('status') || request()->filled('article_category_id'))
                    <a href="{{ route('admin.articles.index') }}" class="admin-reset-button">
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
                <h2>قائمة المقالات</h2>
                <p>عرض وإدارة جميع المقالات المسجلة.</p>
            </div>

            <span class="admin-results-count">{{ number_format($articles->total()) }} مقال</span>
        </div>

        @if ($articles->isNotEmpty())

            <div class="admin-card-grid">
                @foreach ($articles as $article)
                    <article class="admin-data-card">

                        <div class="admin-data-card__media">
                            @if ($article->featured_image)
                                <img src="{{ media_url($article->featured_image) }}" alt="{{ $article->title_ar }}" loading="lazy">
                            @else
                                <i class="fa-regular fa-image" aria-hidden="true"></i>
                            @endif

                            <div class="admin-data-card__badges">
                                <span class="admin-status {{ $article->status === 'published' ? 'admin-status--active' : 'admin-status--inactive' }}">
                                    <i class="{{ $article->status === 'published' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-minus' }}" aria-hidden="true"></i>
                                    {{ ['draft' => 'مسودة', 'published' => 'منشور', 'archived' => 'مؤرشف'][$article->status] ?? $article->status }}
                                </span>
                            </div>

                            @if ($article->is_featured)
                                <div class="admin-data-card__badges admin-data-card__badges--end">
                                    <span class="admin-status"><i class="fa-solid fa-star" aria-hidden="true"></i> مميز</span>
                                </div>
                            @endif
                        </div>

                        <div class="admin-data-card__body">
                            <div class="admin-data-card__heading">
                                <div>
                                    <h3>{{ $article->title_ar }}</h3>

                                    <div style="display:flex;gap:6px;margin-top:4px;">
                                        <span class="admin-status admin-status--active" title="متوفر بالعربية">AR</span>

                                        @if (filled($article->title_en))
                                            <span class="admin-status admin-status--active" title="متوفر بالإنجليزية">EN</span>
                                        @else
                                            <span class="admin-status admin-status--inactive" title="غير متوفر بالإنجليزية">EN</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p class="admin-data-card__description">{{ $article->excerpt_ar }}</p>

                            <div class="admin-data-card__meta">
                                <div>
                                    <span>التصنيف</span>
                                    <strong>{{ $article->category?->name_ar ?? 'بدون تصنيف' }}</strong>
                                </div>
                                <div>
                                    <span>المشاهدات</span>
                                    <strong><i class="fa-solid fa-eye" aria-hidden="true"></i> {{ number_format($article->views) }}</strong>
                                </div>
                                <div>
                                    <span>الكاتب</span>
                                    <strong>{{ $article->author?->name ?? '—' }}</strong>
                                </div>
                                <div>
                                    <span>مدة القراءة</span>
                                    <strong>{{ $article->reading_time ?? 1 }} دقائق</strong>
                                </div>
                            </div>
                        </div>

                        <div class="admin-data-card__actions">
                            @php
                                $previewLocale = $article->translatedSlug('ar') ? 'ar' : 'en';
                                $previewSlug = $article->translatedSlug($previewLocale);
                            @endphp

                            @if ($article->status === 'published' && $previewSlug)
                                <a href="{{ article_route('show', [$previewSlug], $previewLocale) }}" target="_blank" rel="noopener" class="admin-action-button" title="عرض" aria-label="عرض المقال">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </a>
                            @endif

                            <a href="{{ route('admin.articles.edit', $article) }}" class="admin-action-button admin-action-button--edit" title="تعديل" aria-label="تعديل المقال">
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>

                            <form action="{{ route('admin.articles.toggle-featured', $article) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="admin-action-button" title="{{ $article->is_featured ? 'إزالة التمييز' : 'تمييز المقال' }}" aria-label="تبديل تمييز المقال">
                                    <i class="{{ $article->is_featured ? 'fa-solid fa-star' : 'fa-regular fa-star' }}" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.articles.toggle-status', $article) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="admin-action-button" title="{{ $article->status === 'published' ? 'تحويل لمسودة' : 'نشر' }}" aria-label="تبديل حالة النشر">
                                    <i class="{{ $article->status === 'published' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-paper-plane' }}" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا المقال؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-action-button admin-action-button--delete" title="حذف" aria-label="حذف المقال">
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
                    <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                </div>

                <h3>لا توجد مقالات حاليًا</h3>
                <p>أضف أول مقال لعرضه وإدارته من هذه الصفحة.</p>

                <a href="{{ route('admin.articles.create') }}" class="admin-primary-button">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    إضافة أول مقال
                </a>
            </div>

        @endif

    </section>

</div>

@endsection

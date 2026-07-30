@php
    $article ??= null;
    $isEdit = (bool) $article;
@endphp

<div class="form-grid">

    <div class="form-group">
        <label for="title_ar">
            العنوان بالعربية
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="title_ar"
            name="title_ar"
            class="form-control"
            value="{{ old('title_ar', $article->title_ar ?? '') }}"
            maxlength="500"
            required
        >
    </div>

    <div class="form-group">
        <label for="title_en">
            العنوان بالإنجليزية
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="title_en"
            name="title_en"
            class="form-control"
            value="{{ old('title_en', $article->title_en ?? '') }}"
            maxlength="500"
            dir="ltr"
            required
        >
    </div>

    <div class="form-group">
        <label for="news_category_id">التصنيف</label>

        <select id="news_category_id" name="news_category_id" class="form-control">
            <option value="">بدون تصنيف</option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected((string) old('news_category_id', $article->news_category_id ?? '') === (string) $category->id)
                >
                    {{ $category->name_ar }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="published_at">تاريخ النشر</label>

        <input
            type="datetime-local"
            id="published_at"
            name="published_at"
            class="form-control"
            value="{{ old('published_at', $article?->published_at?->format('Y-m-d\TH:i')) }}"
        >
    </div>

    <div class="form-group full">
        <label for="excerpt_ar">مقتطف مختصر (عربي)</label>

        <textarea
            id="excerpt_ar"
            name="excerpt_ar"
            class="form-control"
            rows="3"
            maxlength="2000"
        >{{ old('excerpt_ar', $article->excerpt_ar ?? '') }}</textarea>
    </div>

    <div class="form-group full">
        <label for="excerpt_en">مقتطف مختصر (إنجليزي)</label>

        <textarea
            id="excerpt_en"
            name="excerpt_en"
            class="form-control"
            rows="3"
            maxlength="2000"
            dir="ltr"
        >{{ old('excerpt_en', $article->excerpt_en ?? '') }}</textarea>
    </div>

    <div class="form-group full">
        <label for="content_ar">المحتوى الكامل (عربي)</label>

        <textarea
            id="content_ar"
            name="content_ar"
            class="form-control"
            rows="8"
        >{{ old('content_ar', $article->content_ar ?? '') }}</textarea>
    </div>

    <div class="form-group full">
        <label for="content_en">المحتوى الكامل (إنجليزي)</label>

        <textarea
            id="content_en"
            name="content_en"
            class="form-control"
            rows="8"
            dir="ltr"
        >{{ old('content_en', $article->content_en ?? '') }}</textarea>
    </div>

    <div class="form-group">
        <label for="image">رابط الصورة</label>

        <input
            type="text"
            id="image"
            name="image"
            class="form-control"
            value="{{ old('image', $article->image ?? '') }}"
            dir="ltr"
            placeholder="https://... أو مسار داخلي"
        >
    </div>

    <div class="form-group">
        <label for="source_name">اسم المصدر</label>

        <input
            type="text"
            id="source_name"
            name="source_name"
            class="form-control"
            value="{{ old('source_name', $article->source_name ?? '') }}"
        >
    </div>

    <div class="form-group">
        <label for="source_url">رابط المصدر</label>

        <input
            type="url"
            id="source_url"
            name="source_url"
            class="form-control"
            value="{{ old('source_url', $article->source_url ?? '') }}"
            dir="ltr"
        >
    </div>

    <div class="form-group">
        <label for="author_name">اسم الكاتب</label>

        <input
            type="text"
            id="author_name"
            name="author_name"
            class="form-control"
            value="{{ old('author_name', $article->author_name ?? '') }}"
        >
    </div>

    <div class="form-group full">

        <label>الحالة</label>

        <div style="display:flex; gap:24px; flex-wrap:wrap;">

            <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
                <input
                    type="checkbox"
                    name="is_published"
                    value="1"
                    @checked(old('is_published', $isEdit ? $article->status === \App\Models\NewsArticle::STATUS_PUBLISHED : true))
                >
                منشور
            </label>

            <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    @checked(old('is_featured', $article->is_featured ?? false))
                >
                مميز
            </label>

            <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
                <input
                    type="checkbox"
                    name="is_breaking"
                    value="1"
                    @checked(old('is_breaking', $article->is_breaking ?? false))
                >
                خبر عاجل
            </label>

        </div>

    </div>

</div>

<div class="form-actions">

    <a href="{{ route('admin.news.index') }}" class="dashboard-button">
        إلغاء
    </a>

    <button type="submit" class="dashboard-button dashboard-button-primary">
        <i class="{{ $isEdit ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus' }}"></i>
        <span>{{ $isEdit ? 'حفظ التعديلات' : 'إضافة الخبر' }}</span>
    </button>

</div>

@php
    $article ??= null;
    $isEdit = (bool) $article;
@endphp

@push('styles')
<style>
    .lang-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
        border-bottom: 1px solid #e3e7ed;
    }

    .lang-tabs__btn {
        appearance: none;
        border: none;
        background: transparent;
        padding: 10px 18px;
        font-family: inherit;
        font-size: 15px;
        font-weight: 700;
        color: #6b7280;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        margin-bottom: -1px;
    }

    .lang-tabs__btn.is-active {
        color: #0B1F3A;
        border-bottom-color: #0B1F3A;
    }

    .lang-tabs__panel {
        display: none;
    }

    .lang-tabs__panel.is-active {
        display: block;
    }

    .lang-tabs__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #16a34a;
        margin-inline-start: 6px;
    }
</style>
@endpush

<div class="form-grid">

    <div class="form-group">
        <label for="article_category_id">التصنيف</label>

        <select id="article_category_id" name="article_category_id" class="form-control">
            <option value="">بدون تصنيف</option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected((string) old('article_category_id', $article->article_category_id ?? '') === (string) $category->id)
                >
                    {{ $category->name_ar }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="author_id">الكاتب</label>

        <select id="author_id" name="author_id" class="form-control">
            <option value="">الكاتب الحالي</option>

            @foreach ($authors as $author)
                <option
                    value="{{ $author->id }}"
                    @selected((string) old('author_id', $article->author_id ?? '') === (string) $author->id)
                >
                    {{ $author->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="status">الحالة</label>

        <select id="status" name="status" class="form-control" required>
            <option value="draft" @selected(old('status', $article->status ?? 'draft') === 'draft')>مسودة</option>
            <option value="published" @selected(old('status', $article->status ?? '') === 'published')>منشور</option>
            <option value="archived" @selected(old('status', $article->status ?? '') === 'archived')>مؤرشف</option>
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
        <label for="featured_image">الصورة الرئيسية</label>

        <input
            type="file"
            id="featured_image"
            name="featured_image"
            class="form-control"
            accept="image/png,image/jpeg,image/webp"
        >

        @if ($article?->featured_image)
            <div style="margin-top:10px;">
                <img src="{{ media_url($article->featured_image) }}" alt="" style="max-width:220px;border-radius:8px;">
            </div>
        @endif

        @error('featured_image')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label style="display:inline-flex;align-items:center;gap:10px;">
            <input
                type="checkbox"
                name="is_featured"
                value="1"
                @checked(old('is_featured', $article->is_featured ?? false))
            >
            <span>مقال مميز</span>
        </label>
    </div>

</div>

<hr>

@php
    $hasEn = $isEdit && (filled(old('title_en', $article->title_en)) || filled(old('content_en', $article->content_en)));
@endphp

<div class="lang-tabs" role="tablist">
    <button type="button" class="lang-tabs__btn is-active" data-lang-tab="ar" role="tab">
        العربية
    </button>

    <button type="button" class="lang-tabs__btn" data-lang-tab="en" role="tab">
        English
        @if ($hasEn)
            <span class="lang-tabs__badge" title="تتوفر ترجمة إنجليزية"></span>
        @endif
    </button>
</div>

<div class="lang-tabs__panel is-active" data-lang-panel="ar" dir="rtl">

    <div class="form-grid">

        <div class="form-group full">
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

        <div class="form-group full" data-slug-preview data-slug-base="{{ url('/articles').'/' }}" data-slug-source-id="title_ar">
            <label for="slug_ar">رابط الصفحة (URL)</label>

            <input
                type="text"
                id="slug_ar"
                name="slug_ar"
                class="form-control"
                dir="ltr"
                data-slug-input
                value="{{ old('slug_ar', $article?->translatedSlug('ar') ?? '') }}"
                maxlength="180"
                placeholder="مثال: silent-partner"
            >

            <small style="display:block;margin-top:6px;color:#555;">
                <i class="fa-solid fa-link" aria-hidden="true"></i>
                <span data-slug-preview-text>{{ url('/articles').'/' }}</span>
            </small>

            <small data-slug-warning hidden style="display:block;margin-top:4px;color:#b45309;">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                سيُنشأ رابط تلقائيًا من العنوان (قد يكون بالنقحرة وغير واضح) — يُفضّل تحديد رابط يدوي واضح أعلاه.
            </small>

            @error('slug_ar')
                <small class="text-danger" style="display:block;margin-top:4px;">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group full">
            <label for="excerpt_ar">مقتطف مختصر</label>

            <textarea
                id="excerpt_ar"
                name="excerpt_ar"
                class="form-control"
                rows="3"
                maxlength="500"
            >{{ old('excerpt_ar', $article->excerpt_ar ?? '') }}</textarea>
        </div>

        <div class="form-group full">
            <label for="content_ar">
                المحتوى الكامل
                <span class="text-danger">*</span>
            </label>

            <textarea
                id="content_ar"
                name="content_ar"
                class="form-control lang-editor"
                rows="10"
                required
            >{{ old('content_ar', $article->content_ar ?? '') }}</textarea>
        </div>

        <div class="form-group">
            <label for="meta_title_ar">عنوان SEO</label>

            <input
                type="text"
                id="meta_title_ar"
                name="meta_title_ar"
                class="form-control"
                value="{{ old('meta_title_ar', $article->meta_title_ar ?? '') }}"
                maxlength="255"
            >
        </div>

        <div class="form-group">
            <label for="meta_keywords_ar">كلمات مفتاحية</label>

            <input
                type="text"
                id="meta_keywords_ar"
                name="meta_keywords_ar"
                class="form-control"
                value="{{ old('meta_keywords_ar', $article->meta_keywords_ar ?? '') }}"
                maxlength="255"
            >
        </div>

        <div class="form-group full">
            <label for="meta_description_ar">وصف SEO</label>

            <textarea
                id="meta_description_ar"
                name="meta_description_ar"
                class="form-control"
                rows="2"
                maxlength="500"
            >{{ old('meta_description_ar', $article->meta_description_ar ?? '') }}</textarea>
        </div>

    </div>

</div>

<div class="lang-tabs__panel" data-lang-panel="en" dir="ltr">

    <div class="form-grid">

        <div class="form-group full">
            <label for="title_en">العنوان بالإنجليزية</label>

            <input
                type="text"
                id="title_en"
                name="title_en"
                class="form-control"
                value="{{ old('title_en', $article->title_en ?? '') }}"
                maxlength="500"
                dir="ltr"
            >
        </div>

        <div class="form-group full" data-slug-preview data-slug-base="{{ url('/en/articles').'/' }}" data-slug-source-id="title_en">
            <label for="slug_en">Page URL</label>

            <input
                type="text"
                id="slug_en"
                name="slug_en"
                class="form-control"
                dir="ltr"
                data-slug-input
                value="{{ old('slug_en', $article?->translatedSlug('en') ?? '') }}"
                maxlength="180"
                placeholder="e.g. silent-partner"
            >

            <small style="display:block;margin-top:6px;color:#555;">
                <i class="fa-solid fa-link" aria-hidden="true"></i>
                <span data-slug-preview-text>{{ url('/en/articles').'/' }}</span>
            </small>

            @error('slug_en')
                <small class="text-danger" style="display:block;margin-top:4px;">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group full">
            <label for="excerpt_en">Short excerpt</label>

            <textarea
                id="excerpt_en"
                name="excerpt_en"
                class="form-control"
                rows="3"
                maxlength="500"
                dir="ltr"
            >{{ old('excerpt_en', $article->excerpt_en ?? '') }}</textarea>
        </div>

        <div class="form-group full">
            <label for="content_en">Full content</label>

            <textarea
                id="content_en"
                name="content_en"
                class="form-control lang-editor"
                rows="10"
                dir="ltr"
            >{{ old('content_en', $article->content_en ?? '') }}</textarea>
        </div>

        <div class="form-group">
            <label for="meta_title_en">SEO title</label>

            <input
                type="text"
                id="meta_title_en"
                name="meta_title_en"
                class="form-control"
                value="{{ old('meta_title_en', $article->meta_title_en ?? '') }}"
                maxlength="255"
                dir="ltr"
            >
        </div>

        <div class="form-group">
            <label for="meta_keywords_en">SEO keywords</label>

            <input
                type="text"
                id="meta_keywords_en"
                name="meta_keywords_en"
                class="form-control"
                value="{{ old('meta_keywords_en', $article->meta_keywords_en ?? '') }}"
                maxlength="255"
                dir="ltr"
            >
        </div>

        <div class="form-group full">
            <label for="meta_description_en">SEO description</label>

            <textarea
                id="meta_description_en"
                name="meta_description_en"
                class="form-control"
                rows="2"
                maxlength="500"
                dir="ltr"
            >{{ old('meta_description_en', $article->meta_description_en ?? '') }}</textarea>
        </div>

    </div>

</div>

<div class="form-actions">

    <a href="{{ route('admin.articles.index') }}" class="dashboard-button">
        إلغاء
    </a>

    <button type="submit" class="dashboard-button dashboard-button-primary">
        <i class="{{ $isEdit ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus' }}"></i>
        <span>{{ $isEdit ? 'حفظ التعديلات' : 'إضافة المقال' }}</span>
    </button>

</div>

@push('scripts')
<script src="{{ versioned_asset('js/admin/slug-preview.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    (function () {
        var tabButtons = document.querySelectorAll('[data-lang-tab]');
        var panels = document.querySelectorAll('[data-lang-panel]');

        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var lang = button.getAttribute('data-lang-tab');

                tabButtons.forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === button);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-lang-panel') === lang);
                });

                if (window.tinymce) {
                    tinymce.get('content_' + lang) && tinymce.get('content_' + lang).focus();
                }
            });
        });

        if (window.tinymce) {
            tinymce.init({
                selector: '#content_ar',
                directionality: 'rtl',
                height: 420,
                menubar: false,
                plugins: 'lists link',
                toolbar: 'undo redo | formatselect | bold italic | bullist numlist | blockquote | link | removeformat',
                block_formats: 'الفقرة=p; عنوان 2=h2; عنوان 3=h3; عنوان 4=h4',
                content_style: 'body { direction: rtl; font-family: Tajawal, sans-serif; }',
            });

            tinymce.init({
                selector: '#content_en',
                directionality: 'ltr',
                height: 420,
                menubar: false,
                plugins: 'lists link',
                toolbar: 'undo redo | formatselect | bold italic | bullist numlist | blockquote | link | removeformat',
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                content_style: 'body { direction: ltr; font-family: Arial, sans-serif; }',
            });
        }
    })();
</script>
@endpush

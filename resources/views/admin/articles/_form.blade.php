@php
    $article ??= null;
@endphp

<div class="form-grid">
    <div class="form-group full">
        <label for="title">
            عنوان المقال
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="title"
            name="title"
            class="form-control"
            value="{{ old('title', $article?->title) }}"
            maxlength="255"
            required
            autofocus
        >

        @error('title')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="slug">الرابط المختصر</label>

        <input
            type="text"
            id="slug"
            name="slug"
            class="form-control"
            value="{{ old('slug', $article?->slug) }}"
            maxlength="280"
            dir="ltr"
        >

        <small style="display:block;margin-top:6px;color:#777;">اتركه فارغًا ليتم إنشاؤه تلقائيًا من العنوان.</small>

        @error('slug')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="article_category_id">التصنيف</label>

        <select id="article_category_id" name="article_category_id" class="form-control">
            <option value="">بدون تصنيف</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('article_category_id', $article?->article_category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        @error('article_category_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="author_id">الكاتب</label>

        <select id="author_id" name="author_id" class="form-control">
            @foreach ($authors as $author)
                <option value="{{ $author->id }}" @selected(old('author_id', $article?->author_id ?? auth('admin')->id()) == $author->id)>
                    {{ $author->name }}
                </option>
            @endforeach
        </select>

        @error('author_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="status">حالة المقال</label>

        <select id="status" name="status" class="form-control">
            <option value="draft" @selected(old('status', $article?->status ?? 'draft') === 'draft')>مسودة</option>
            <option value="published" @selected(old('status', $article?->status) === 'published')>منشور</option>
            <option value="archived" @selected(old('status', $article?->status) === 'archived')>مؤرشف</option>
        </select>

        @error('status')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="excerpt">مقتطف مختصر</label>

        <textarea
            id="excerpt"
            name="excerpt"
            rows="3"
            class="form-control"
            maxlength="500"
        >{{ old('excerpt', $article?->excerpt) }}</textarea>

        @error('excerpt')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="content">
            محتوى المقال
            <span class="text-danger">*</span>
        </label>

        <textarea
            id="content"
            name="content"
            rows="14"
            class="form-control"
            required
        >{{ old('content', $article?->content) }}</textarea>

        <small style="display:block;margin-top:6px;color:#777;">
            يمكن استخدام وسوم HTML بسيطة للتنسيق: &lt;p&gt; &lt;strong&gt; &lt;em&gt; &lt;a&gt; &lt;ul&gt;/&lt;li&gt; &lt;h2&gt;-&lt;h4&gt; &lt;blockquote&gt;. مدة القراءة تُحسب تلقائيًا من عدد الكلمات.
        </small>

        @error('content')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="featured_image">الصورة الرئيسية</label>

        <input
            type="file"
            id="featured_image"
            name="featured_image"
            class="form-control"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <small style="display:block;margin-top:8px;color:#777;">
            الصيغ المسموحة: JPG، JPEG، PNG، WEBP. الحد الأقصى: 4 ميجابايت.
        </small>

        @error('featured_image')
            <small class="text-danger">{{ $message }}</small>
        @enderror

        @if (!empty($article?->featured_image))
            <div style="margin-top:15px;">
                <img
                    src="{{ media_url($article->featured_image) }}"
                    alt="{{ $article->title }}"
                    style="max-width:220px;max-height:180px;object-fit:cover;border-radius:12px;"
                >
            </div>
        @endif
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

        <small style="display:block;margin-top:6px;color:#777;">اتركه فارغًا ليُستخدم وقت النشر الحالي عند اختيار "منشور".</small>

        @error('published_at')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label style="display:inline-flex;align-items:center;gap:10px;">
            <input
                type="checkbox"
                name="is_featured"
                value="1"
                @checked(old('is_featured', $article?->is_featured ?? false))
            >
            <span>مقال مميز (يظهر ضمن المقالات المميزة)</span>
        </label>

        @error('is_featured')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <h3 style="margin:10px 0;">تحسين محركات البحث (SEO)</h3>
    </div>

    <div class="form-group">
        <label for="meta_title">عنوان SEO</label>

        <input
            type="text"
            id="meta_title"
            name="meta_title"
            class="form-control"
            value="{{ old('meta_title', $article?->meta_title) }}"
            maxlength="255"
        >

        @error('meta_title')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="meta_keywords">الكلمات المفتاحية</label>

        <input
            type="text"
            id="meta_keywords"
            name="meta_keywords"
            class="form-control"
            value="{{ old('meta_keywords', $article?->meta_keywords) }}"
            maxlength="255"
        >

        @error('meta_keywords')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="meta_description">وصف SEO</label>

        <textarea
            id="meta_description"
            name="meta_description"
            rows="3"
            class="form-control"
            maxlength="500"
        >{{ old('meta_description', $article?->meta_description) }}</textarea>

        @error('meta_description')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<hr>

<div class="form-actions">
    <button type="submit" class="dashboard-button dashboard-button-primary">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>حفظ المقال</span>
    </button>

    <a href="{{ route('admin.articles.index') }}" class="dashboard-button">
        إلغاء
    </a>
</div>

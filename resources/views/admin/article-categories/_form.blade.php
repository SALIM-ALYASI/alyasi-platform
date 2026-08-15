@php
    $category ??= null;
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="name_ar">
            اسم التصنيف (عربي)
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="name_ar"
            name="name_ar"
            class="form-control"
            value="{{ old('name_ar', $category?->name_ar) }}"
            maxlength="150"
            required
            autofocus
        >

        @error('name_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="name_en">اسم التصنيف (إنجليزي)</label>

        <input
            type="text"
            id="name_en"
            name="name_en"
            class="form-control"
            value="{{ old('name_en', $category?->name_en) }}"
            maxlength="150"
            dir="ltr"
        >

        @error('name_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="slug">الرابط المختصر</label>

        <input
            type="text"
            id="slug"
            name="slug"
            class="form-control"
            value="{{ old('slug', $category?->slug) }}"
            maxlength="180"
            dir="ltr"
        >

        <small style="display:block;margin-top:6px;color:#777;">اتركه فارغًا ليتم إنشاؤه تلقائيًا من الاسم العربي.</small>

        @error('slug')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="description_ar">الوصف (عربي)</label>

        <textarea
            id="description_ar"
            name="description_ar"
            rows="4"
            class="form-control"
            maxlength="500"
        >{{ old('description_ar', $category?->description_ar) }}</textarea>

        @error('description_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="description_en">الوصف (إنجليزي)</label>

        <textarea
            id="description_en"
            name="description_en"
            rows="4"
            class="form-control"
            maxlength="500"
            dir="ltr"
        >{{ old('description_en', $category?->description_en) }}</textarea>

        @error('description_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="sort_order">ترتيب الظهور</label>

        <input
            type="number"
            id="sort_order"
            name="sort_order"
            class="form-control"
            value="{{ old('sort_order', $category?->sort_order ?? 0) }}"
            min="0"
        >

        @error('sort_order')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label style="display:inline-flex;align-items:center;gap:10px;">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $category?->is_active ?? true))
            >
            <span>التصنيف مفعّل ويظهر بالموقع</span>
        </label>

        @error('is_active')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<hr>

<div class="form-actions">
    <button type="submit" class="dashboard-button dashboard-button-primary">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>حفظ التصنيف</span>
    </button>

    <a href="{{ route('admin.article-categories.index') }}" class="dashboard-button">
        إلغاء
    </a>
</div>

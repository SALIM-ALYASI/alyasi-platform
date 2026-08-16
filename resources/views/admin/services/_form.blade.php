@php
    $service ??= null;

    $arabicPermalink = $service?->permalinks?->firstWhere('locale', 'ar');
    $englishPermalink = $service?->permalinks?->firstWhere('locale', 'en');
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="title_ar">
            عنوان الخدمة بالعربية
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="title_ar"
            name="title_ar"
            class="form-control"
            value="{{ old('title_ar', $service?->title_ar) }}"
            maxlength="255"
            required
            autofocus
        >

        @error('title_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="title_en">عنوان الخدمة بالإنجليزية</label>

        <input
            type="text"
            id="title_en"
            name="title_en"
            class="form-control"
            value="{{ old('title_en', $service?->title_en) }}"
            maxlength="255"
            dir="ltr"
        >

        @error('title_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="description_ar">
            وصف الخدمة بالعربية
            <span class="text-danger">*</span>
        </label>

        <textarea
            id="description_ar"
            name="description_ar"
            rows="7"
            class="form-control"
            maxlength="5000"
            required
        >{{ old('description_ar', $service?->description_ar) }}</textarea>

        @error('description_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <label for="description_en">وصف الخدمة بالإنجليزية</label>

        <textarea
            id="description_en"
            name="description_en"
            rows="7"
            class="form-control"
            maxlength="5000"
            dir="ltr"
        >{{ old('description_en', $service?->description_en) }}</textarea>

        @error('description_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group" data-slug-preview data-slug-base="{{ url('/services').'/' }}" data-slug-source-id="title_ar">
        <label for="slug_ar">
            الرابط المختصر بالعربية
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="slug_ar"
            name="slug_ar"
            class="form-control"
            dir="ltr"
            data-slug-input
            value="{{ old('slug_ar', $arabicPermalink?->slug) }}"
            maxlength="180"
            placeholder="مثال: social-media-design"
            required
        >

        <small style="display:block;margin-top:6px;color:#555;">
            <i class="fa-solid fa-link" aria-hidden="true"></i>
            <span data-slug-preview-text>{{ url('/services').'/' }}</span>
        </small>

        @error('slug_ar')
            <small class="text-danger" style="display:block;margin-top:4px;">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group" data-slug-preview data-slug-base="{{ url('/services').'/' }}" data-slug-source-id="title_en">
        <label for="slug_en">
            الرابط المختصر بالإنجليزية
            <span class="text-danger">*</span> <small style="font-weight:400;">(عند وجود عنوان إنجليزي)</small>
        </label>

        <input
            type="text"
            id="slug_en"
            name="slug_en"
            class="form-control"
            dir="ltr"
            data-slug-input
            value="{{ old('slug_en', $englishPermalink?->slug) }}"
            maxlength="180"
            placeholder="e.g. social-media-design"
        >

        <small style="display:block;margin-top:6px;color:#555;">
            <i class="fa-solid fa-link" aria-hidden="true"></i>
            <span data-slug-preview-text>{{ url('/services').'/' }}</span>
        </small>

        @error('slug_en')
            <small class="text-danger" style="display:block;margin-top:4px;">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group full">
        <small style="color:#777;">
            لا يوجد توليد تلقائي للرابط — اكتب رابطًا إنجليزيًا واضحًا وقصيرًا. تغيير الرابط يحفظ القديم تلقائيًا كتحويل 301 حتى لا تنكسر روابط سبق مشاركتها.
        </small>
    </div>

    <div class="form-group full">
        <label for="image">صورة الخدمة</label>

        <input
            type="file"
            id="image"
            name="image"
            class="form-control"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <small style="display:block;margin-top:8px;color:#777;">
            الصيغ المسموحة: JPG، JPEG، PNG، WEBP. الحد الأقصى: 4 ميجابايت.
        </small>

        @error('image')
            <small class="text-danger">{{ $message }}</small>
        @enderror

        @if(!empty($service?->image))
            <div style="margin-top:15px;">
                <img
                    src="{{ asset($service->image) }}"
                    alt="{{ $service->title_ar }}"
                    style="max-width:220px;max-height:180px;object-fit:cover;border-radius:12px;"
                >
            </div>
        @endif
    </div>

    <div class="form-group full">
        <label style="display:inline-flex;align-items:center;gap:10px;">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $service?->is_active ?? true))
            >

            <span>الخدمة مفعلة وتظهر في الموقع</span>
        </label>

        @error('is_active')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

<hr>

<div class="form-actions">
    <button type="submit"
            class="dashboard-button dashboard-button-primary">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>حفظ الخدمة</span>
    </button>

    <a href="{{ route('admin.services.index') }}"
       class="dashboard-button">
        إلغاء
    </a>
</div>

@push('scripts')
<script src="{{ versioned_asset('js/admin/slug-preview.js') }}"></script>
@endpush

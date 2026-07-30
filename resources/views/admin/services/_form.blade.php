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

    @if($service)
        <div class="form-group">
            <label>الرابط العربي الدائم</label>

            <input
                type="text"
                class="form-control"
                value="{{ $arabicPermalink?->slug ?? 'سيتم إنشاؤه تلقائياً عند الحفظ' }}"
                readonly
                dir="ltr"
            >
        </div>

        <div class="form-group">
            <label>الرابط الإنجليزي الدائم</label>

            <input
                type="text"
                class="form-control"
                value="{{ $englishPermalink?->slug ?? 'سيتم إنشاؤه تلقائياً عند توفر عنوان إنجليزي' }}"
                readonly
                dir="ltr"
            >
        </div>

        <div class="form-group full">
            <small style="color:#777;">
                الروابط الدائمة لا تتغير تلقائياً عند تعديل عنوان الخدمة.
            </small>
        </div>
    @endif

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
                    src="{{ asset('storage/' . $service->image) }}"
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

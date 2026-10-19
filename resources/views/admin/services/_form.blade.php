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

    <div class="form-group">
        <label for="price_from">السعر بالريال العُماني (اختياري)</label>

        <input
            type="number"
            id="price_from"
            name="price_from"
            class="form-control"
            value="{{ old('price_from', $service?->price_from !== null ? rtrim(rtrim($service->price_from, '0'), '.') : '') }}"
            min="0"
            step="0.001"
            dir="ltr"
            placeholder="مثال: 2.5"
        >

        <small style="display:block;margin-top:6px;color:#555;">اتركه فاضي إذا ما تبي يظهر سعر للخدمة.</small>

        @error('price_from')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label style="display:inline-flex;align-items:center;gap:10px;margin-top:34px;">
            <input
                type="checkbox"
                name="price_is_starting"
                value="1"
                @checked(old('price_is_starting', $service?->price_is_starting ?? true))
            >

            <span>سعر ابتدائي (يظهر «يبدأ من»)</span>
        </label>
    </div>

    <div class="form-group">
        <label for="price_unit_ar">وحدة السعر بالعربية (اختياري)</label>

        <input
            type="text"
            id="price_unit_ar"
            name="price_unit_ar"
            class="form-control"
            value="{{ old('price_unit_ar', $service?->price_unit_ar) }}"
            maxlength="255" placeholder="مثال: للدقيقة، للبوت البسيط"
        >

        @error('price_unit_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="price_unit_en">وحدة السعر بالإنجليزية (اختياري)</label>

        <input
            type="text"
            id="price_unit_en"
            name="price_unit_en"
            class="form-control"
            value="{{ old('price_unit_en', $service?->price_unit_en) }}"
            maxlength="255" dir="ltr" placeholder="e.g. per minute"
        >

        @error('price_unit_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="price_note_ar">ملاحظة السعر بالعربية (اختياري)</label>

        <input
            type="text"
            id="price_note_ar"
            name="price_note_ar"
            class="form-control"
            value="{{ old('price_note_ar', $service?->price_note_ar) }}"
            maxlength="500" placeholder="مثال: تكلفة السيرفر والنطاق منفصلة."
        >

        @error('price_note_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="price_note_en">ملاحظة السعر بالإنجليزية (اختياري)</label>

        <input
            type="text"
            id="price_note_en"
            name="price_note_en"
            class="form-control"
            value="{{ old('price_note_en', $service?->price_note_en) }}"
            maxlength="500" dir="ltr"
        >

        @error('price_note_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="deliverables_ar">ماذا يستلم العميل؟ بالعربية (سطر لكل بند)</label>

        <textarea
            id="deliverables_ar"
            name="deliverables_ar"
            rows="5"
            class="form-control"
            maxlength="2000"
        >{{ old('deliverables_ar', $service?->deliverables_ar) }}</textarea>

        @error('deliverables_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="deliverables_en">ماذا يستلم العميل؟ بالإنجليزية (سطر لكل بند)</label>

        <textarea
            id="deliverables_en"
            name="deliverables_en"
            rows="5"
            class="form-control"
            maxlength="2000" dir="ltr"
        >{{ old('deliverables_en', $service?->deliverables_en) }}</textarea>

        @error('deliverables_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="revisions_ar">جولات التعديل بالعربية (اختياري)</label>

        <input
            type="text"
            id="revisions_ar"
            name="revisions_ar"
            class="form-control"
            value="{{ old('revisions_ar', $service?->revisions_ar) }}"
            maxlength="255"
            placeholder="مثال: جولتا تعديل"
        >

        @error('revisions_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="revisions_en">جولات التعديل بالإنجليزية (اختياري)</label>

        <input
            type="text"
            id="revisions_en"
            name="revisions_en"
            class="form-control"
            value="{{ old('revisions_en', $service?->revisions_en) }}"
            maxlength="255"
            placeholder="e.g. Two revision rounds" dir="ltr"
        >

        @error('revisions_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="support_ar">الدعم بعد التسليم بالعربية (اختياري)</label>

        <input
            type="text"
            id="support_ar"
            name="support_ar"
            class="form-control"
            value="{{ old('support_ar', $service?->support_ar) }}"
            maxlength="255"
            placeholder="مثال: 30 يومًا لإصلاح الأخطاء"
        >

        @error('support_ar')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="support_en">الدعم بعد التسليم بالإنجليزية (اختياري)</label>

        <input
            type="text"
            id="support_en"
            name="support_en"
            class="form-control"
            value="{{ old('support_en', $service?->support_en) }}"
            maxlength="255"
            placeholder="e.g. 30 days of bug fixes" dir="ltr"
        >

        @error('support_en')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-group">
        <label for="sort_order">ترتيب الظهور</label>
        <input type="number" id="sort_order" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $service?->sort_order ?? 100) }}">
        <small style="display:block;margin-top:6px;color:#555;">الأصغر يظهر أول (بالرئيسية وصفحة الخدمات). مثال: 10، 20، 30…</small>
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

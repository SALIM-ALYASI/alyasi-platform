@php
    $isEdit = isset($edition);
    $announcements = old('announcements', $edition->announcements ?? []);
    $pricingTable = old('pricing_table', $edition->pricing_table ?? []);
    $gallery = $edition->gallery ?? [];
@endphp

@if ($isEdit)
    <div class="form-group form-group-full">
        <label>المؤتمر الدائم</label>
        <input type="text" value="{{ $edition->event->name }}" disabled>
        <small class="form-hint">لتغيير المؤتمر الدائم راجع قاعدة البيانات مباشرة.</small>
    </div>
@else
    <div class="form-grid">
        <div class="form-group">
            <label for="event_id">المؤتمر الدائم</label>
            <select id="event_id" name="event_id" required onchange="document.getElementById('new-event-fields').style.display = this.value === 'new' ? 'grid' : 'none'">
                <option value="new" {{ old('event_id') === 'new' || old('event_id') === null ? 'selected' : '' }}>+ مؤتمر جديد</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" {{ (string) old('event_id') === (string) $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                @endforeach
            </select>
            @error('event_id')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="year">سنة النسخة</label>
            <input type="number" id="year" name="year" value="{{ old('year', now()->year) }}" min="2000" max="2100" required>
            @error('year')<span class="form-error">{{ $message }}</span>@enderror
        </div>
    </div>

    <div class="form-grid" id="new-event-fields" style="{{ old('event_id', 'new') === 'new' ? '' : 'display:none' }}">
        <div class="form-group">
            <label for="new_event_name">اسم المؤتمر الدائم (مثال: مؤتمر آبل)</label>
            <input type="text" id="new_event_name" name="new_event_name" value="{{ old('new_event_name') }}">
            @error('new_event_name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="new_event_organizer">الجهة المنظِّمة</label>
            <input type="text" id="new_event_organizer" name="new_event_organizer" value="{{ old('new_event_organizer') }}">
        </div>
    </div>
@endif

<div class="form-grid">

    <div class="form-group form-group-full">
        <label for="title_ar">عنوان النسخة (عربي)</label>
        <input type="text" id="title_ar" name="title_ar" value="{{ old('title_ar', $edition->title_ar ?? '') }}" placeholder="مثال: مؤتمر آبل «Surprise and Shine» — سبتمبر 2026" required>
        @error('title_ar')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group form-group-full">
        <label for="title_en">عنوان النسخة (إنجليزي)</label>
        <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $edition->title_en ?? '') }}">
        @error('title_en')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coverage_type">نوع التغطية</label>
        <select id="coverage_type" name="coverage_type" required>
            @php $ct = old('coverage_type', $edition->coverage_type ?? 'global_remote'); @endphp
            <option value="global_remote" @selected($ct === 'global_remote')>عالمي عن بُعد (آبل، سامسونج)</option>
            <option value="gulf_analysis" @selected($ct === 'gulf_analysis')>تحليل مقارن (GITEX، LEAP)</option>
            <option value="local_field" @selected($ct === 'local_field')>حضور ميداني (مسقط)</option>
        </select>
    </div>

    <div class="form-group">
        <label for="date_status">حالة تأكيد الموعد</label>
        <select id="date_status" name="date_status" required>
            @php $ds = old('date_status', $edition->date_status ?? 'confirmed'); @endphp
            <option value="confirmed" @selected($ds === 'confirmed')>مؤكد رسميًا</option>
            <option value="expected" @selected($ds === 'expected')>متوقع</option>
            <option value="unknown" @selected($ds === 'unknown')>غير معروف بعد</option>
        </select>
    </div>

    <div class="form-group">
        <label for="event_start_at">وقت بداية المؤتمر</label>
        <input type="datetime-local" id="event_start_at" name="event_start_at"
            value="{{ old('event_start_at', optional($edition->event_start_at ?? null)->format('Y-m-d\TH:i')) }}">
        <small class="form-hint">بتوقيت مسقط. الصفحة العامة تتحول تلقائيًا لـ"مباشر" عند هذا الوقت.</small>
    </div>

    <div class="form-group">
        <label for="event_end_at">وقت نهاية المؤتمر</label>
        <input type="datetime-local" id="event_end_at" name="event_end_at"
            value="{{ old('event_end_at', optional($edition->event_end_at ?? null)->format('Y-m-d\TH:i')) }}">
        <small class="form-hint">مهم تعبّيه — بدونه تتحول الصفحة لـ"انتهى" فور بداية الوقت مباشرة.</small>
        @error('event_end_at')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group form-group-full">
        <label for="livestream_url">رابط البث المباشر</label>
        <input type="url" id="livestream_url" name="livestream_url" value="{{ old('livestream_url', $edition->livestream_url ?? '') }}">
        @error('livestream_url')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group form-group-full">
        <label class="check-option">
            <input type="checkbox" name="attended" value="1" @checked(old('attended', $edition->attended ?? false))>
            <span>حضرنا الحدث ميدانيًا (يسمح بصيغة الشاهد بالكتابة)</span>
        </label>
    </div>

</div>

<div class="form-grid">

    <div class="form-group">
        <label for="image">الصورة الرئيسية</label>
        <input type="file" id="image" name="image" accept="image/*">
        @if (($edition->image ?? null))
            <small class="form-hint">الصورة الحالية موجودة — ارفع صورة جديدة لاستبدالها فقط.</small>
        @endif
        @error('image')<span class="form-error">{{ $message }}</span>@enderror
    </div>

</div>

<div class="form-group form-group-full">
    <label>معرض الصور (لمرحلة "انتهى المؤتمر")</label>
    <div id="gallery-list" style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
        @foreach ($gallery as $i => $path)
            <div class="gallery-item" style="position:relative;">
                <img src="{{ asset($path) }}" alt="" style="width:90px;height:90px;object-fit:cover;border-radius:8px;">
                <input type="hidden" name="kept_gallery[]" value="{{ $path }}">
                <button type="button" onclick="this.closest('.gallery-item').remove()" style="position:absolute;top:-6px;left:-6px;background:#c0392b;color:#fff;border:0;border-radius:50%;width:20px;height:20px;cursor:pointer;">×</button>
            </div>
        @endforeach
    </div>
    <input type="file" name="new_gallery_images[]" accept="image/*" multiple>
</div>

<div class="form-grid">
    <div class="form-group form-group-full">
        <label for="short_description_ar">وصف مختصر (عربي)</label>
        <textarea id="short_description_ar" name="short_description_ar" rows="3">{{ old('short_description_ar', $edition->short_description_ar ?? '') }}</textarea>
    </div>

    <div class="form-group form-group-full">
        <label for="short_description_en">وصف مختصر (إنجليزي)</label>
        <textarea id="short_description_en" name="short_description_en" rows="3">{{ old('short_description_en', $edition->short_description_en ?? '') }}</textarea>
    </div>
</div>

{{-- ===================== الإعلانات المتوقعة/الفعلية ===================== --}}

<div class="form-group form-group-full">
    <label>المنتجات / الإعلانات (اسم المنتج بلغتين + ملاحظة + درجة تأكيد)</label>

    <div id="announcements-list"></div>

    <button type="button" id="add-announcement" class="btn btn-secondary" style="margin-top:8px;">+ إضافة منتج/إعلان</button>

    <template id="announcement-row-template">
        <div class="repeater-row" style="border:1px solid var(--panel-border, #ddd); border-radius:10px; padding:14px; margin-top:10px;">
            <div class="form-grid">
                <div class="form-group">
                    <label>اسم المنتج (عربي)</label>
                    <input type="text" name="announcements[__INDEX__][label_ar]" data-field="label_ar">
                </div>
                <div class="form-group">
                    <label>اسم المنتج (إنجليزي)</label>
                    <input type="text" name="announcements[__INDEX__][label_en]" data-field="label_en">
                </div>
                <div class="form-group">
                    <label>ملاحظة (عربي)</label>
                    <input type="text" name="announcements[__INDEX__][note_ar]" data-field="note_ar">
                </div>
                <div class="form-group">
                    <label>ملاحظة (إنجليزي)</label>
                    <input type="text" name="announcements[__INDEX__][note_en]" data-field="note_en">
                </div>
                <div class="form-group">
                    <label>درجة التأكيد</label>
                    <select name="announcements[__INDEX__][confidence]" data-field="confidence">
                        <option value="expected">متوقع</option>
                        <option value="confirmed">مؤكد فعليًا</option>
                        <option value="rumored">إشاعة/تسريب</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>صورة المنتج</label>
                    <img class="announcement-image-preview" style="display:none; width:70px; height:70px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                    <input type="hidden" name="announcements[__INDEX__][image]" data-field="image" class="announcement-image-hidden">
                    <input type="file" name="announcement_images[__INDEX__]" accept="image/*" class="announcement-image-file">
                </div>
            </div>
            <button type="button" class="btn btn-secondary remove-row" style="margin-top:8px;">حذف هذا الصف</button>
        </div>
    </template>
</div>

{{-- ===================== جدول الأسعار ===================== --}}

<div class="form-group form-group-full">
    <label>جدول الأسعار (بعد إعلان الأسعار الرسمية)</label>

    <div id="pricing-list"></div>

    <button type="button" id="add-pricing" class="btn btn-secondary" style="margin-top:8px;">+ إضافة سعر منتج</button>

    <template id="pricing-row-template">
        <div class="repeater-row" style="border:1px solid var(--panel-border, #ddd); border-radius:10px; padding:14px; margin-top:10px;">
            <div class="form-grid">
                <div class="form-group">
                    <label>اسم المنتج (عربي)</label>
                    <input type="text" name="pricing_table[__INDEX__][product_ar]" data-field="product_ar">
                </div>
                <div class="form-group">
                    <label>اسم المنتج (إنجليزي)</label>
                    <input type="text" name="pricing_table[__INDEX__][product_en]" data-field="product_en">
                </div>
                <div class="form-group">
                    <label>السعر الرسمي</label>
                    <input type="text" name="pricing_table[__INDEX__][official_price]" data-field="official_price" placeholder="999">
                </div>
                <div class="form-group">
                    <label>العملة</label>
                    <input type="text" name="pricing_table[__INDEX__][official_currency]" data-field="official_currency" placeholder="USD">
                </div>
                <div class="form-group">
                    <label>السعر بالريال العُماني (تقريبي)</label>
                    <input type="text" name="pricing_table[__INDEX__][omr_price]" data-field="omr_price" placeholder="385">
                </div>
            </div>
            <button type="button" class="btn btn-secondary remove-row" style="margin-top:8px;">حذف هذا الصف</button>
        </div>
    </template>
</div>

<div class="form-grid">
    <div class="form-group">
        <label for="upgrade_verdict">حكم الترقية (بعد انتهاء المؤتمر)</label>
        @php $uv = old('upgrade_verdict', $edition->upgrade_verdict ?? ''); @endphp
        <select id="upgrade_verdict" name="upgrade_verdict">
            <option value="" @selected($uv === '')>— بدون حكم بعد —</option>
            <option value="yes" @selected($uv === 'yes')>يستحق الترقية</option>
            <option value="no" @selected($uv === 'no')>لا يستحق الترقية</option>
            <option value="specific_segment" @selected($uv === 'specific_segment')>يستحق لفئة معينة فقط</option>
        </select>
    </div>

    <div class="form-group form-group-full">
        <label for="upgrade_verdict_text">تفصيل الحكم</label>
        <textarea id="upgrade_verdict_text" name="upgrade_verdict_text" rows="3">{{ old('upgrade_verdict_text', $edition->upgrade_verdict_text ?? '') }}</textarea>
    </div>

    <div class="form-group">
        <label for="status">حالة النشر</label>
        @php $st = old('status', $edition->status ?? 'draft'); @endphp
        <select id="status" name="status" required>
            <option value="draft" @selected($st === 'draft')>مسودة (غير ظاهرة للزوار)</option>
            <option value="published" @selected($st === 'published')>منشور</option>
        </select>
    </div>
</div>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'حفظ التعديلات' : 'إضافة النسخة' }}</button>
    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">إلغاء</a>
</div>

@push('scripts')
<script>
(function () {
    function setupRepeater(listId, addBtnId, templateId, existingRows) {
        var list = document.getElementById(listId);
        var addBtn = document.getElementById(addBtnId);
        var template = document.getElementById(templateId);
        var counter = 0;

        function addRow(values) {
            var html = template.innerHTML.replace(/__INDEX__/g, counter++);
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            var row = wrapper.firstElementChild;

            if (values) {
                row.querySelectorAll('[data-field]').forEach(function (el) {
                    var field = el.getAttribute('data-field');
                    if (values[field] !== undefined && values[field] !== null) {
                        el.value = values[field];
                    }
                });
            }

            var preview = row.querySelector('.announcement-image-preview');
            var fileInput = row.querySelector('.announcement-image-file');
            var hiddenInput = row.querySelector('.announcement-image-hidden');

            if (preview && hiddenInput && hiddenInput.value) {
                preview.src = '/' + hiddenInput.value.replace(/^\/+/, '');
                preview.style.display = 'block';
            }

            if (fileInput && preview) {
                fileInput.addEventListener('change', function () {
                    if (fileInput.files && fileInput.files[0]) {
                        preview.src = URL.createObjectURL(fileInput.files[0]);
                        preview.style.display = 'block';
                    }
                });
            }

            row.querySelector('.remove-row').addEventListener('click', function () {
                row.remove();
            });

            list.appendChild(row);
        }

        (existingRows || []).forEach(addRow);

        addBtn.addEventListener('click', function () {
            addRow(null);
        });
    }

    setupRepeater('announcements-list', 'add-announcement', 'announcement-row-template', @json($announcements));
    setupRepeater('pricing-list', 'add-pricing', 'pricing-row-template', @json($pricingTable));
})();
</script>
@endpush

@php
    $isEdit = isset($technology) && $technology->exists;
@endphp

<div class="technology-form-grid">

    <div class="technology-form-main">

        <section class="technology-form-card">

            <div class="technology-form-card__header">

                <div>

                    <span class="technology-form-card__badge">
                        <i class="fa-solid fa-microchip" aria-hidden="true"></i>
                        البيانات الأساسية
                    </span>

                    <h2>
                        اسم التقنية وأيقونتها
                    </h2>

                    <p>
                        اسم مختصر وأيقونة Font Awesome، مثل ما تظهر بالصفحة الرئيسية.
                    </p>

                </div>

            </div>

            <div class="technology-form-card__body">

                <div class="technology-form-group">

                    <label for="name">
                        الاسم
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $technology->name ?? '') }}"
                        placeholder="مثال: Laravel"
                        maxlength="255"
                        required
                        autofocus
                    >

                    @error('name')
                        <small class="technology-form-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="technology-form-group">

                    <label for="slug">
                        الرابط المختصر
                    </label>

                    <div class="technology-form-input-icon">

                        <i class="fa-solid fa-link" aria-hidden="true"></i>

                        <input
                            type="text"
                            id="slug"
                            name="slug"
                            value="{{ old('slug', $technology->slug ?? '') }}"
                            placeholder="laravel"
                            maxlength="255"
                            dir="ltr"
                        >

                    </div>

                    <small class="technology-form-help">
                        اتركه فارغًا ليتم إنشاؤه تلقائيًا من اسم التقنية.
                    </small>

                    @error('slug')
                        <small class="technology-form-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="technology-form-group">

                    <label for="icon">
                        كلاس الأيقونة (Font Awesome)
                    </label>

                    <div class="technology-form-input-icon">

                        <i class="{{ old('icon', $technology->icon ?? '') ?: 'fa-solid fa-icons' }}" aria-hidden="true"></i>

                        <input
                            type="text"
                            id="icon"
                            name="icon"
                            value="{{ old('icon', $technology->icon ?? '') }}"
                            placeholder="fa-brands fa-laravel"
                            maxlength="255"
                            dir="ltr"
                        >

                    </div>

                    <small class="technology-form-help">
                        مثال: fa-brands fa-laravel
                    </small>

                    @error('icon')
                        <small class="technology-form-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

            </div>

        </section>

    </div>

    <aside class="technology-form-sidebar">

        <section class="technology-form-card technology-form-card--sticky">

            <div class="technology-form-card__header">

                <div>

                    <span class="technology-form-card__badge">
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                        إعدادات النشر
                    </span>

                    <h2>
                        حالة التقنية
                    </h2>

                </div>

            </div>

            <div class="technology-form-card__body">

                <div class="technology-form-group">

                    <label for="sort_order">
                        ترتيب الظهور
                    </label>

                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        value="{{ old('sort_order', $technology->sort_order ?? 0) }}"
                        min="0"
                        step="1"
                    >

                    <small class="technology-form-help">
                        الرقم الأصغر يظهر أولًا.
                    </small>

                    @error('sort_order')
                        <small class="technology-form-error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="technology-switch-list">

                    <label class="technology-switch">

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old(
                                'is_active',
                                $isEdit
                                    ? $technology->is_active
                                    : true
                            ))
                        >

                        <span class="technology-switch__track">
                            <span class="technology-switch__thumb"></span>
                        </span>

                        <span class="technology-switch__content">

                            <strong>
                                مفعلة
                            </strong>

                            <small>
                                السماح بظهور التقنية في المنصة.
                            </small>

                        </span>

                    </label>

                </div>

                <div class="technology-form-actions">

                    <button
                        type="submit"
                        class="technology-submit-button"
                    >
                        <i
                            class="{{ $isEdit
                                ? 'fa-solid fa-floppy-disk'
                                : 'fa-solid fa-plus' }}"
                            aria-hidden="true"
                        ></i>

                        {{ $isEdit
                            ? 'حفظ التعديلات'
                            : 'إضافة التقنية' }}
                    </button>

                    <a
                        href="{{ route('admin.technologies.index') }}"
                        class="technology-cancel-button"
                    >
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        إلغاء والعودة
                    </a>

                </div>

            </div>

        </section>

    </aside>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const iconInput = document.getElementById('icon');
    const iconPreview = iconInput
        ?.closest('.technology-form-input-icon')
        ?.querySelector('i');

    if (!iconInput || !iconPreview) {
        return;
    }

    iconInput.addEventListener('input', function () {
        iconPreview.className = iconInput.value.trim() || 'fa-solid fa-icons';
    });
});
</script>
@endpush

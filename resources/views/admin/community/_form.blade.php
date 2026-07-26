@php
    $communityPost ??= null;

    $selectedType = old(
        'type',
        $communityPost?->type ?? 'post'
    );

    $publishedAt = old(
        'published_at',
        $communityPost?->published_at
            ? $communityPost->published_at->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );

    $eventStartAt = old(
        'event_start_at',
        $communityPost?->event_start_at
            ? $communityPost->event_start_at->format('Y-m-d\TH:i')
            : ''
    );

    $eventEndAt = old(
        'event_end_at',
        $communityPost?->event_end_at
            ? $communityPost->event_end_at->format('Y-m-d\TH:i')
            : ''
    );
@endphp

@if ($errors->any())
    <div class="alert alert-danger community-form-errors">
        <div class="community-form-errors-title">
            <i class="fa-solid fa-circle-exclamation"></i>

            <strong>
                تعذر حفظ منشور المجتمع.
            </strong>
        </div>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-grid">

    {{-- عنوان المنشور --}}
    <div class="form-group full">
        <label for="title">
            عنوان المنشور
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            id="title"
            name="title"
            class="form-control"
            value="{{ old('title', $communityPost?->title) }}"
            maxlength="255"
            placeholder="أدخل عنوان المنشور"
            required
            autofocus
        >

        @error('title')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- الرابط المختصر --}}
    <div class="form-group full">
        <label for="slug">
            الرابط المختصر
        </label>

        <input
            type="text"
            id="slug"
            name="slug"
            class="form-control"
            value="{{ old('slug', $communityPost?->slug) }}"
            maxlength="255"
            placeholder="يُترك فارغًا ليتم إنشاؤه تلقائيًا"
            dir="ltr"
        >

        <small class="form-help">
            يمكنك تركه فارغًا وسيتم إنشاؤه تلقائيًا من عنوان المنشور.
        </small>

        @error('slug')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- التصنيف --}}
    <div class="form-group">
        <label for="community_category_id">
            التصنيف
        </label>

        <select
            id="community_category_id"
            name="community_category_id"
            class="form-control"
        >
            <option value="">
                بدون تصنيف
            </option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected(
                        (string) old(
                            'community_category_id',
                            $communityPost?->community_category_id
                        ) === (string) $category->id
                    )
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        @error('community_category_id')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- نوع المنشور --}}
    <div class="form-group">
        <label for="type">
            نوع المنشور
            <span class="text-danger">*</span>
        </label>

        <select
            id="type"
            name="type"
            class="form-control"
            required
        >
            @foreach ($types as $typeValue => $typeLabel)
                <option
                    value="{{ $typeValue }}"
                    @selected($selectedType === $typeValue)
                >
                    {{ $typeLabel }}
                </option>
            @endforeach
        </select>

        @error('type')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- الوصف المختصر --}}
    <div class="form-group full">
        <label for="short_description">
            الوصف المختصر
        </label>

        <textarea
            id="short_description"
            name="short_description"
            rows="4"
            class="form-control"
            maxlength="500"
            placeholder="وصف مختصر يظهر في بطاقات المجتمع"
        >{{ old(
            'short_description',
            $communityPost?->short_description
        ) }}</textarea>

        <div class="form-field-footer">
            <small class="form-help">
                الحد الأقصى 500 حرف.
            </small>

            <small
                class="community-character-counter"
                data-counter-for="short_description"
            >
                0 / 500
            </small>
        </div>

        @error('short_description')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- المحتوى --}}
    <div class="form-group full">
        <label for="content">
            محتوى المنشور
            <span class="text-danger">*</span>
        </label>

        <textarea
            id="content"
            name="content"
            rows="12"
            class="form-control community-content-editor"
            placeholder="اكتب محتوى المنشور بالتفصيل..."
            required
        >{{ old('content', $communityPost?->content) }}</textarea>

        @error('content')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- تاريخ النشر --}}
    <div class="form-group">
        <label for="published_at">
            تاريخ النشر
        </label>

        <input
            type="datetime-local"
            id="published_at"
            name="published_at"
            class="form-control"
            value="{{ $publishedAt }}"
        >

        <small class="form-help">
            يمكن تحديد تاريخ مستقبلي لجدولة المنشور.
        </small>

        @error('published_at')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- الصورة --}}
    <div class="form-group">
        <label for="image">
            صورة المنشور
        </label>

        <input
            type="file"
            id="image"
            name="image"
            class="form-control"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <small class="form-help">
            الصيغ المسموحة: JPG، JPEG، PNG، WEBP.
            الحد الأقصى 4 ميجابايت.
        </small>

        @error('image')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

    {{-- معاينة الصورة --}}
    <div class="form-group full">
        <div
            class="community-image-preview
                {{ empty($communityPost?->image) ? 'is-empty' : '' }}"
            id="communityImagePreview"
        >
            @if (!empty($communityPost?->image))
                <img
                    src="{{ asset(
                        'storage/' . $communityPost->image
                    ) }}"
                    alt="{{ $communityPost->title }}"
                    id="communityPreviewImage"
                >
            @else
                <div
                    class="community-image-preview-placeholder"
                    id="communityPreviewPlaceholder"
                >
                    <i class="fa-regular fa-image"></i>

                    <span>
                        ستظهر معاينة الصورة هنا
                    </span>
                </div>

                <img
                    src=""
                    alt="معاينة صورة المنشور"
                    id="communityPreviewImage"
                    hidden
                >
            @endif
        </div>

        @if (!empty($communityPost?->image))
            <label class="community-check-option community-remove-image">
                <input
                    type="checkbox"
                    name="remove_image"
                    value="1"
                    @checked(old('remove_image'))
                >

                <span class="community-check-icon">
                    <i class="fa-solid fa-trash"></i>
                </span>

                <span class="community-check-content">
                    <strong>
                        حذف الصورة الحالية
                    </strong>

                    <small>
                        سيتم حذف الصورة عند حفظ التعديلات.
                    </small>
                </span>
            </label>

            @error('remove_image')
                <small class="text-danger">
                    {{ $message }}
                </small>
            @enderror
        @endif
    </div>

    {{-- حقول الفعالية --}}
    <div
        class="form-group full community-event-fields"
        id="communityEventFields"
        @if ($selectedType !== 'event') hidden @endif
    >
        <div class="community-form-section">
            <div class="community-form-section-header">
                <span class="community-form-section-icon">
                    <i class="fa-regular fa-calendar-days"></i>
                </span>

                <div>
                    <h3>
                        بيانات الفعالية
                    </h3>

                    <p>
                        تظهر هذه الحقول عند اختيار نوع المنشور فعالية.
                    </p>
                </div>
            </div>

            <div class="form-grid community-event-grid">

                <div class="form-group">
                    <label for="event_start_at">
                        بداية الفعالية
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="datetime-local"
                        id="event_start_at"
                        name="event_start_at"
                        class="form-control"
                        value="{{ $eventStartAt }}"
                    >

                    @error('event_start_at')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="event_end_at">
                        نهاية الفعالية
                    </label>

                    <input
                        type="datetime-local"
                        id="event_end_at"
                        name="event_end_at"
                        class="form-control"
                        value="{{ $eventEndAt }}"
                    >

                    @error('event_end_at')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="location">
                        موقع الفعالية
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        class="form-control"
                        value="{{ old(
                            'location',
                            $communityPost?->location
                        ) }}"
                        maxlength="255"
                        placeholder="مثال: مسقط أو فعالية عن بُعد"
                    >

                    @error('location')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="registration_url">
                        رابط التسجيل
                    </label>

                    <input
                        type="url"
                        id="registration_url"
                        name="registration_url"
                        class="form-control"
                        value="{{ old(
                            'registration_url',
                            $communityPost?->registration_url
                        ) }}"
                        maxlength="2048"
                        placeholder="https://example.com/register"
                        dir="ltr"
                    >

                    @error('registration_url')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- خيارات المنشور --}}
    <div class="form-group full">
        <div class="community-options-grid">

            <label class="community-check-option">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(
                        old(
                            'is_active',
                            $communityPost?->is_active ?? true
                        )
                    )
                >

                <span class="community-check-icon">
                    <i class="fa-solid fa-eye"></i>
                </span>

                <span class="community-check-content">
                    <strong>
                        المنشور مفعّل
                    </strong>

                    <small>
                        يظهر المنشور في صفحة المجتمع.
                    </small>
                </span>
            </label>

            <label class="community-check-option">
                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    @checked(
                        old(
                            'is_featured',
                            $communityPost?->is_featured ?? false
                        )
                    )
                >

                <span class="community-check-icon">
                    <i class="fa-solid fa-star"></i>
                </span>

                <span class="community-check-content">
                    <strong>
                        منشور مميز
                    </strong>

                    <small>
                        يظهر في مقدمة منشورات المجتمع.
                    </small>
                </span>
            </label>

        </div>

        @error('is_active')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror

        @error('is_featured')
            <small class="text-danger">
                {{ $message }}
            </small>
        @enderror
    </div>

</div>

<hr class="community-form-divider">

<div class="form-actions">
    <button
        type="submit"
        class="dashboard-button dashboard-button-primary"
    >
        <i class="fa-solid fa-floppy-disk"></i>

        <span>
            {{ $communityPost
                ? 'حفظ التعديلات'
                : 'حفظ المنشور'
            }}
        </span>
    </button>

    <a
        href="{{ route('admin.community.index') }}"
        class="dashboard-button"
    >
        <i class="fa-solid fa-xmark"></i>
        <span>إلغاء</span>
    </a>
</div>
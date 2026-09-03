@php
    $selectedTechnologies = old(
        'technology_ids',
        isset($work) && $work
            ? $work->technologies->pluck('id')->toArray()
            : []
    );
@endphp

<div class="form-grid">

    {{-- العنوان --}}
    <div class="form-group form-group--full">

        <label for="title" class="form-label">
            عنوان العمل
            <span class="required">*</span>
        </label>

        <input
            type="text"
            name="title"
            id="title"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $work?->title) }}"
            maxlength="255"
            required
            autofocus
        >

        @error('title')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- الرابط المختصر --}}
    <div class="form-group" data-slug-preview data-slug-base="{{ url('/works').'/' }}" data-slug-source-id="title">

        <label for="slug" class="form-label">
            الرابط المختصر
        </label>

        <input
            type="text"
            name="slug"
            id="slug"
            class="form-control @error('slug') is-invalid @enderror"
            data-slug-input
            value="{{ old('slug', $work?->slug) }}"
            maxlength="180"
            dir="ltr"
            placeholder="مثال: markify (لا تلصق رابطًا كاملًا هنا)"
        >

        <small style="display:block;margin-top:6px;color:#555;">
            <i class="fa-solid fa-link" aria-hidden="true"></i>
            <span data-slug-preview-text>{{ url('/works').'/' }}</span>
        </small>

        @error('slug')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- نوع العمل --}}
    <div class="form-group">

        <label for="type" class="form-label">
            نوع العمل
            <span class="required">*</span>
        </label>

        <select
            name="type"
            id="type"
            class="form-control @error('type') is-invalid @enderror"
            required
        >
            <option value="">اختر نوع العمل</option>

            <option
                value="website"
                @selected(old('type', $work?->type) === 'website')
            >
                موقع إلكتروني
            </option>

            <option
                value="application"
                @selected(old('type', $work?->type) === 'application')
            >
                تطبيق
            </option>

            <option
                value="design"
                @selected(old('type', $work?->type) === 'design')
            >
                تصميم
            </option>

            <option
                value="advertisement"
                @selected(old('type', $work?->type) === 'advertisement')
            >
                إعلان
            </option>

            <option
                value="video"
                @selected(old('type', $work?->type) === 'video')
            >
                فيديو
            </option>

            <option
                value="photography"
                @selected(old('type', $work?->type) === 'photography')
            >
                تصوير
            </option>

            <option
                value="event"
                @selected(old('type', $work?->type) === 'event')
            >
                فعالية
            </option>

            <option
                value="personal"
                @selected(old('type', $work?->type) === 'personal')
            >
                عمل شخصي
            </option>

            <option
                value="other"
                @selected(old('type', $work?->type) === 'other')
            >
                أخرى
            </option>
        </select>

        @error('type')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- الخدمة --}}
    <div class="form-group">

        <label for="service_id" class="form-label">
            الخدمة المرتبطة
        </label>

        <select
            name="service_id"
            id="service_id"
            class="form-control @error('service_id') is-invalid @enderror"
        >
            <option value="">غير مرتبط بخدمة</option>

            @foreach ($services as $service)

                <option
                    value="{{ $service->id }}"
                    @selected((string) old('service_id', $work?->service_id) === (string) $service->id)
                >
                    {{ $service->title_ar }}
                </option>

            @endforeach
        </select>

        @error('service_id')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- اسم العميل --}}
    <div class="form-group">

        <label for="client_name" class="form-label">
            اسم العميل
        </label>

        <input
            type="text"
            name="client_name"
            id="client_name"
            class="form-control @error('client_name') is-invalid @enderror"
            value="{{ old('client_name', $work?->client_name) }}"
            maxlength="255"
        >

        @error('client_name')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- رابط العمل --}}
    <div class="form-group">

        <label for="work_url" class="form-label">
            رابط العمل
        </label>

        <input
            type="url"
            name="work_url"
            id="work_url"
            class="form-control @error('work_url') is-invalid @enderror"
            value="{{ old('work_url', $work?->work_url) }}"
            maxlength="1000"
            dir="ltr"
            placeholder="https://example.com"
        >

        @error('work_url')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- تاريخ الإنجاز --}}
    <div class="form-group">

        <label for="completed_at" class="form-label">
            تاريخ الإنجاز
        </label>

        <input
            type="date"
            name="completed_at"
            id="completed_at"
            class="form-control @error('completed_at') is-invalid @enderror"
            value="{{ old(
                'completed_at',
                $work?->completed_at?->format('Y-m-d')
            ) }}"
        >

        @error('completed_at')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- الترتيب --}}
    <div class="form-group">

        <label for="sort_order" class="form-label">
            الترتيب
        </label>

        <input
            type="number"
            name="sort_order"
            id="sort_order"
            class="form-control @error('sort_order') is-invalid @enderror"
            value="{{ old('sort_order', $work?->sort_order ?? 0) }}"
            min="0"
        >

        @error('sort_order')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- الوصف المختصر --}}
    <div class="form-group form-group--full">

        <label for="short_description" class="form-label">
            الوصف المختصر
            <span class="required">*</span>
        </label>

        <textarea
            name="short_description"
            id="short_description"
            class="form-control @error('short_description') is-invalid @enderror"
            rows="4"
            maxlength="500"
            required
        >{{ old('short_description', $work?->short_description) }}</textarea>

        <small class="form-hint">
            وصف مختصر يظهر في بطاقة العمل، بحد أقصى 500 حرف.
        </small>

        @error('short_description')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- نتيجة ملموسة (تظهر بالصفحة الرئيسية تحت المشروع) --}}
    <div class="form-group form-group--full">

        <label for="outcome" class="form-label">
            نتيجة ملموسة
        </label>

        <input
            type="text"
            name="outcome"
            id="outcome"
            class="form-control @error('outcome') is-invalid @enderror"
            maxlength="255"
            value="{{ old('outcome', $work?->outcome) }}"
        >

        <small class="form-hint">
            سطر نتيجة قصير يظهر تحت المشروع بالصفحة الرئيسية (مثلاً: "تم قبوله بمتجر آبل"). اتركه فاضي لو ما فيه نتيجة موثّقة بعد.
        </small>

        @error('outcome')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- الوصف الكامل --}}
    <div class="form-group form-group--full">

        <label for="description" class="form-label">
            الوصف الكامل
        </label>

        <textarea
            name="description"
            id="description"
            class="form-control @error('description') is-invalid @enderror"
            rows="8"
        >{{ old('description', $work?->description) }}</textarea>

        @error('description')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- صورة الغلاف --}}
    <div class="form-group form-group--full">

        <label for="cover_image" class="form-label">
            صورة الغلاف

            @unless ($work?->cover_image)
                <span class="required">*</span>
            @endunless
        </label>

        @if ($work?->cover_image)

            <div class="current-image">

                <img
                    src="{{ asset('storage/' . $work->cover_image) }}"
                    alt="{{ $work->title }}"
                >

                <div>
                    <strong>الصورة الحالية</strong>
                    <p>اختر صورة جديدة فقط عند الرغبة في استبدالها.</p>
                </div>

            </div>

        @endif

        <input
            type="file"
            name="cover_image"
            id="cover_image"
            class="form-control @error('cover_image') is-invalid @enderror"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            @required(!$work?->cover_image)
        >

        <small class="form-hint">
            الصيغ المسموحة: JPG وPNG وWEBP. الحد الأقصى 5 ميجابايت.
        </small>

        @error('cover_image')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- معرض الصور --}}
    <div class="form-group form-group--full">

        <label for="gallery_images" class="form-label">
            معرض الصور
        </label>

        <input
            type="file"
            name="gallery_images[]"
            id="gallery_images"
            class="form-control @error('gallery_images') is-invalid @enderror @error('gallery_images.*') is-invalid @enderror"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            multiple
        >

        <small class="form-hint">
            يمكنك اختيار عدة صور، وبحد أقصى 15 صورة.
        </small>

        @error('gallery_images')
            <span class="form-error">{{ $message }}</span>
        @enderror

        @error('gallery_images.*')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- الصور الحالية عند التعديل --}}
    @if ($work?->exists && $work->images->isNotEmpty())

        <div class="form-group form-group--full">

            <label class="form-label">
                الصور الحالية
            </label>

            <div class="gallery-grid">

                @foreach ($work->images as $image)

                    <div class="gallery-item">

                        <img
                            src="{{ asset('storage/' . $image->image_path) }}"
                            alt="{{ $work->title }}"
                        >

                        <button
                            type="button"
                            class="gallery-delete-button"
                            data-delete-url="{{ route(
                                'admin.works.images.destroy',
                                [$work, $image]
                            ) }}"
                            data-image-id="{{ $image->id }}"
                        >
                            <i class="fa-solid fa-trash"></i>
                            حذف
                        </button>

                    </div>

                @endforeach

            </div>

        </div>

    @endif

    {{-- التقنيات --}}
    <div class="form-group form-group--full">

        <label class="form-label">
            التقنيات المستخدمة
        </label>

        @if ($technologies->isNotEmpty())

            <div class="technology-options">

                @foreach ($technologies as $technology)

                    <label class="technology-option">

                        <input
                            type="checkbox"
                            name="technology_ids[]"
                            value="{{ $technology->id }}"
                            @checked(in_array(
                                $technology->id,
                                $selectedTechnologies
                            ))
                        >

                        <span class="technology-option__content">

                            @if ($technology->icon)

                                <i class="{{ $technology->icon }}"></i>

                            @endif

                            <span>{{ $technology->name }}</span>

                        </span>

                    </label>

                @endforeach

            </div>

        @else

            <div class="form-empty-message">
                لا توجد تقنيات مضافة بعد.
            </div>

        @endif

        @error('technology_ids')
            <span class="form-error">{{ $message }}</span>
        @enderror

        @error('technology_ids.*')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

    {{-- إعدادات النشر --}}
    <div class="form-group form-group--full">

        <div class="switches-grid">

            <label class="switch-card">

                <input
                    type="hidden"
                    name="is_active"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked((bool) old(
                        'is_active',
                        $work?->is_active ?? true
                    ))
                >

                <span class="switch-card__content">

                    <strong>عمل نشط</strong>

                    <small>
                        يظهر العمل في واجهة المنصة العامة.
                    </small>

                </span>

            </label>

            <label class="switch-card">

                <input
                    type="hidden"
                    name="is_featured"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    @checked((bool) old(
                        'is_featured',
                        $work?->is_featured ?? false
                    ))
                >

                <span class="switch-card__content">

                    <strong>عمل مميز</strong>

                    <small>
                        يعرض في أقسام الأعمال المميزة.
                    </small>

                </span>

            </label>

        </div>

        @error('is_active')
            <span class="form-error">{{ $message }}</span>
        @enderror

        @error('is_featured')
            <span class="form-error">{{ $message }}</span>
        @enderror

    </div>

</div>
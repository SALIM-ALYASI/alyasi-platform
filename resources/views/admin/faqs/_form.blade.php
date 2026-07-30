@php
    $isEdit = isset($faq) && $faq->exists;
@endphp

<div class="faq-form-grid">

    <section class="faq-form-card">

        <div class="faq-form-card__header">
            <h2>السؤال والإجابة</h2>
            <p>أدخل السؤال والإجابة بالعربية، والإنجليزية اختياري.</p>
        </div>

        <div class="faq-form-card__body">

            <div class="faq-form-group">
                <label for="question_ar">
                    السؤال بالعربية
                    <span>*</span>
                </label>

                <input
                    type="text"
                    id="question_ar"
                    name="question_ar"
                    value="{{ old('question_ar', $faq->question_ar ?? '') }}"
                    maxlength="255"
                    required
                    autofocus
                >

                @error('question_ar')
                    <small class="faq-form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="faq-form-group">
                <label for="answer_ar">
                    الإجابة بالعربية
                    <span>*</span>
                </label>

                <textarea
                    id="answer_ar"
                    name="answer_ar"
                    maxlength="2000"
                    required
                >{{ old('answer_ar', $faq->answer_ar ?? '') }}</textarea>

                @error('answer_ar')
                    <small class="faq-form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="faq-form-group">
                <label for="question_en">
                    السؤال بالإنجليزية
                </label>

                <input
                    type="text"
                    id="question_en"
                    name="question_en"
                    value="{{ old('question_en', $faq->question_en ?? '') }}"
                    maxlength="255"
                    dir="ltr"
                >

                @error('question_en')
                    <small class="faq-form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="faq-form-group">
                <label for="answer_en">
                    الإجابة بالإنجليزية
                </label>

                <textarea
                    id="answer_en"
                    name="answer_en"
                    maxlength="2000"
                    dir="ltr"
                >{{ old('answer_en', $faq->answer_en ?? '') }}</textarea>

                @error('answer_en')
                    <small class="faq-form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="faq-form-fields--two">

                <div class="faq-form-group">
                    <label for="sort_order">
                        ترتيب الظهور
                    </label>

                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        value="{{ old('sort_order', $faq->sort_order ?? 0) }}"
                        min="0"
                    >

                    @error('sort_order')
                        <small class="faq-form-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="faq-form-group">
                    <label>
                        الحالة
                    </label>

                    <label class="faq-switch">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $isEdit ? $faq->is_active : true))
                        >
                        مفعّل ويظهر بالصفحة الرئيسية
                    </label>
                </div>

            </div>

            <div class="faq-form-actions">

                <button type="submit" class="faq-submit-button">
                    <i class="{{ $isEdit ? 'fa-solid fa-floppy-disk' : 'fa-solid fa-plus' }}" aria-hidden="true"></i>
                    {{ $isEdit ? 'حفظ التعديلات' : 'إضافة السؤال' }}
                </button>

                <a href="{{ route('admin.faqs.index') }}" class="faq-cancel-button">
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    إلغاء والعودة
                </a>

            </div>

        </div>

    </section>

</div>

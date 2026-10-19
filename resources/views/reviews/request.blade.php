@extends('layouts.app')

{{-- صفحة مؤقتة لطلب تقييم عميل (رابط موقّع ينتهي) -- ما تدخل محركات البحث --}}
@php
    $isEn = app()->getLocale() === 'en';
    $t = fn (string $ar, string $en) => $isEn ? $en : $ar;
    $expiresAt = \Illuminate\Support\Carbon::createFromTimestamp((int) request()->query('expires'))->timezone('Asia/Muscat')->locale(app()->getLocale());
    $projectUrl = localized_route('works.show', ['work' => $work]);
    $client = $work->localized('client_name');
    $starWords = $isEn
        ? [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very good', 5 => 'Excellent']
        : [1 => 'ضعيف', 2 => 'مقبول', 3 => 'جيد', 4 => 'جيد جدًا', 5 => 'ممتاز'];
@endphp

@section('title', $t('قيّم تجربتك مع ALYASI', 'Rate your experience with ALYASI'))
@section('meta_description', $t('طلب تقييم لمشروع ', 'Review request for ').$work->localized('title'))

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/review-request.css') }}">
@endpush

@section('content')
<section class="rr-hero" style="--rr-bg: url('{{ versioned_asset('images/home/hero-muscat-'.($isEn ? 'en' : 'ar').'-960.webp') }}')">
    <div class="container rr-hero__inner">
        <span class="rr-hero__badge"><i class="fa-solid fa-star" aria-hidden="true"></i> {{ $t('رأيك يهمنا', 'Your feedback matters') }}</span>
        <h1>{{ $t('كيف كانت تجربتك مع ALYASI؟', 'How was your experience with ALYASI?') }}</h1>
        <p>{{ $t(
            'شكرًا لثقتكم بنا. دقيقة من وقتك تساعدنا على تطوير خدماتنا، وتمنح من يبحث عن خدمة مماثلة صورة صادقة عن التجربة.',
            'Thank you for trusting us. A minute of your time helps us improve and gives others an honest picture of the experience.'
        ) }}</p>
    </div>
</section>

<section class="container rr-wrap">
    <div class="rr-card">
        <a class="rr-project" href="{{ $projectUrl }}" target="_blank" rel="noopener">
            @if ($work->cover_image)
                <img src="{{ media_url($work->cover_image) }}" alt="" width="132" height="92">
            @endif
            <span class="rr-project__text">
                <span class="rr-project__label">{{ $t('المشروع', 'Project') }}</span>
                <strong>{{ $work->localized('title') }}</strong>
                @if ($client)<span class="rr-project__client">{{ $client }}</span>@endif
            </span>
            <i class="fa-solid fa-arrow-up-right-from-square rr-project__open" aria-hidden="true"></i>
        </a>

        @if (session('review_sent'))
            <div class="rr-done">
                <span class="rr-done__icon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                <h2>{{ $t('شكرًا لك!', 'Thank you!') }}</h2>
                <p>{{ $t('وصل تقييمك، وسيظهر في صفحة المشروع بعد مراجعته. سعدنا بالعمل معكم.', 'Your review has arrived and will appear on the project page once checked. It was a pleasure working with you.') }}</p>
                <a href="{{ $projectUrl }}" class="btn btn--primary">{{ $t('صفحة المشروع', 'Project page') }}</a>
            </div>
        @else
            <form method="POST" action="{{ request()->fullUrl() }}" class="rr-form">
                @csrf

                <fieldset class="rr-stars-field">
                    <legend>{{ $t('تقييمك للخدمة', 'Your rating') }}</legend>
                    <div class="rr-stars" role="radiogroup">
                        @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" name="rating" id="rr-star-{{ $i }}" value="{{ $i }}" @checked(old('rating', 5) == $i)>
                            <label for="rr-star-{{ $i }}" title="{{ $i }}/5"><i class="fa-solid fa-star" aria-hidden="true"></i><span class="visually-hidden">{{ $i }}/5</span></label>
                        @endfor
                    </div>
                    <p class="rr-stars__word" data-words="{{ json_encode($starWords, JSON_UNESCAPED_UNICODE) }}"></p>
                    @error('rating') <span class="field__error">{{ $message }}</span> @enderror
                </fieldset>

                <div class="field">
                    <label class="field__label" for="rr-body">{{ $t('رأيك في الخدمة', 'Your review') }}</label>
                    <textarea name="body" id="rr-body" class="field__control" rows="5" required minlength="10" maxlength="2000" placeholder="{{ $t('كيف كان التواصل، والالتزام بالمواعيد، وجودة العمل النهائي؟', 'How were the communication, timelines and the final result?') }}">{{ old('body') }}</textarea>
                    @error('body') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="rr-grid">
                    <div class="field">
                        <label class="field__label" for="rr-name">{{ $t('الاسم والصفة كما تحب أن يظهرا', 'Name and title, as you would like them shown') }}</label>
                        <input type="text" name="name" id="rr-name" class="field__control" value="{{ old('name') }}" required maxlength="255" placeholder="{{ $t('مثال: الاسم – المسمى، الجهة', 'e.g. Name – Role, Company') }}">
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="rr-email">{{ $t('البريد الإلكتروني (اختياري)', 'Email (optional)') }}</label>
                        <input type="email" name="email" id="rr-email" class="field__control" value="{{ old('email') }}" maxlength="255" dir="ltr">
                        <span class="rr-hint">{{ $t('لا يُنشر', 'Not published') }}</span>
                        @error('email') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <label class="rr-consent">
                    <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
                    <span>{{ $t('أوافق على نشر تقييمي واسمي في صفحة المشروع على موقع ALYASI.', 'I agree to my review and name being published on the project page on the ALYASI website.') }}</span>
                </label>
                @error('consent') <span class="field__error">{{ $t('نحتاج موافقتك قبل النشر.', 'We need your consent before publishing.') }}</span> @enderror

                <button type="submit" class="btn btn--primary rr-submit">
                    {{ $t('إرسال التقييم', 'Send review') }}
                    <i class="fa-solid fa-paper-plane rtl-flip" aria-hidden="true"></i>
                </button>

                <p class="rr-note"><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $t(
                    'يظهر التقييم بعد مراجعته. هذا الرابط خاص بك ويعمل حتى '.$expiresAt->translatedFormat('j F Y').'.',
                    'Reviews appear after they are checked. This link is personal and works until '.$expiresAt->translatedFormat('j F Y').'.'
                ) }}</p>
            </form>
        @endif
    </div>
</section>

@push('scripts')
<script>
    // كلمة تحت النجوم تتغير مع الاختيار (ممتاز، جيد جدًا...).
    (function () {
        var word = document.querySelector('.rr-stars__word');
        if (!word) return;
        var words = JSON.parse(word.dataset.words);
        var show = function (v) { word.textContent = words[v] || ''; };
        document.querySelectorAll('.rr-stars input').forEach(function (input) {
            if (input.checked) show(input.value);
            input.addEventListener('change', function () { show(input.value); });
        });
        document.querySelectorAll('.rr-stars label').forEach(function (label) {
            var v = document.getElementById(label.htmlFor).value;
            label.addEventListener('mouseenter', function () { show(v); });
            label.addEventListener('mouseleave', function () { var c = document.querySelector('.rr-stars input:checked'); show(c ? c.value : ''); });
        });
    })();
</script>
@endpush
@endsection

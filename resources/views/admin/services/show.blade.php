@extends('layouts.app')

@section('title', $service->localizedTitle())

@section(
    'description',
    \Illuminate\Support\Str::limit(
        strip_tags($service->localizedDescription()),
        160
    )
)

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/pages/service-show.css') }}"
    >
@endpush

@section('content')

@php
    $locale = app()->getLocale();

    $title = $service->localizedTitle();
    $description = $service->localizedDescription();

    $currentPermalink = $service->permalinks
        ->firstWhere('locale', $locale);

    $arabicPermalink = $service->permalinks
        ->firstWhere('locale', 'ar');

    $englishPermalink = $service->permalinks
        ->firstWhere('locale', 'en');
@endphp

<section class="service-show-hero">
    <div class="service-show-container">

        <nav
            class="service-breadcrumb"
            aria-label="مسار التنقل"
        >
            <a href="{{ route('home') }}">
                الرئيسية
            </a>

            <i class="fa-solid fa-chevron-left"></i>

            <a href="{{ route('services.index') }}">
                الخدمات
            </a>

            <i class="fa-solid fa-chevron-left"></i>

            <span>
                {{ $title }}
            </span>
        </nav>

        <div class="service-show-grid">

            <div class="service-show-content">
                <span class="service-show-badge">
                    <i class="fa-solid fa-layer-group"></i>

                    خدمات ALYASI
                </span>

                <h1>
                    {{ $title }}
                </h1>

                <div class="service-show-description">
                    {!! nl2br(e($description)) !!}
                </div>

                <div class="service-show-actions">
                    <a
                        href="{{ route('contact') }}"
                        class="service-primary-button"
                    >
                        <i class="fa-solid fa-paper-plane"></i>

                        <span>اطلب الخدمة</span>
                    </a>

                    <a
                        href="{{ route('services.index') }}"
                        class="service-secondary-button"
                    >
                        <i class="fa-solid fa-arrow-right"></i>

                        <span>جميع الخدمات</span>
                    </a>
                </div>
            </div>

            <div class="service-show-media">
                @if ($service->image)
                    <img
                        src="{{ asset($service->image) }}"
                        alt="{{ $title }}"
                    >
                @else
                    <div class="service-show-placeholder">
                        <i class="fa-regular fa-image"></i>

                        <span>{{ $title }}</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>

<section class="service-details-section">
    <div class="service-show-container">

        <div class="service-details-grid">

            <article class="service-details-card">
                <div class="service-details-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <h2>خدمة مصممة باحتراف</h2>

                <p>
                    نقدم الخدمة وفق احتياجات المشروع مع الاهتمام
                    بالتفاصيل والجودة وتجربة المستخدم.
                </p>
            </article>

            <article class="service-details-card">
                <div class="service-details-icon">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>

                <h2>متوافقة مع جميع الأجهزة</h2>

                <p>
                    يتم تنفيذ الخدمة بأسلوب متجاوب يعمل بكفاءة
                    على الهاتف والكمبيوتر والأجهزة اللوحية.
                </p>
            </article>

            <article class="service-details-card">
                <div class="service-details-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>

                <h2>دعم ومتابعة</h2>

                <p>
                    نتابع معك مراحل التنفيذ لضمان الوصول إلى
                    النتيجة المناسبة لهدف مشروعك.
                </p>
            </article>

        </div>

    </div>
</section>

<section class="service-contact-section">
    <div class="service-show-container">

        <div class="service-contact-card">
            <div>
                <span class="service-contact-label">
                    ابدأ مشروعك الآن
                </span>

                <h2>
                    هل تحتاج إلى
                    {{ $title }}؟
                </h2>

                <p>
                    تواصل معنا لمناقشة فكرتك وتحديد الحل الأنسب
                    لمشروعك.
                </p>
            </div>

            <a
                href="{{ route('contact') }}"
                class="service-contact-button"
            >
                <i class="fa-solid fa-comments"></i>

                <span>تواصل معنا</span>
            </a>
        </div>

    </div>
</section>

@endsection
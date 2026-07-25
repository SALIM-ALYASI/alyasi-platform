@extends('layouts.app')

@section(
    'title',
    $service->localizedTitle() . ' | ' . __('home.brand')
)

@section(
    'description',
    \Illuminate\Support\Str::limit(
        strip_tags($service->localizedDescription()),
        160
    )
)

@section('content')

@php
    $title = $service->localizedTitle();
    $description = $service->localizedDescription();
@endphp

<section
    class="hero hero-small"
    id="service"
>
    <div class="hero-grid"></div>

    <div class="hero-badge">
        <div class="hero-badge-dot"></div>

        <span>
            تفاصيل الخدمة
        </span>
    </div>

    <h1>
        {{ $title }}
    </h1>

    <p class="hero-sub">
        {{ $description }}
    </p>

    <div class="hero-ctas">
        <a
            href="{{ route('contact') }}"
            class="btn-primary"
        >
            اطلب الخدمة
        </a>

        <a
            href="{{ route('services.index') }}"
            class="btn-secondary"
        >
            جميع الخدمات
        </a>
    </div>
</section>

@if ($service->image)
    <section class="section">
        <div class="service-show-image reveal">
            <img
                src="{{ asset($service->image) }}"
                alt="{{ $title }}"
                loading="eager"
            >
        </div>
    </section>
@endif

<section
    class="section"
    id="service-details"
>
    <div class="section-heading reveal">
        <p class="section-tag">
            الخدمة
        </p>

        <h2 class="section-title">
            لماذا تختار
            <strong>{{ $title }}</strong>
        </h2>

        <p class="section-body">
            نقدم حلولاً احترافية تعتمد على أحدث التقنيات لضمان
            الجودة والأداء وسهولة التطوير والتوسع مستقبلاً.
        </p>
    </div>

    <div class="pricing-grid">
        <article class="pricing-card reveal">
            <span class="pricing-label">
                الجودة
            </span>

            <h3>
                تنفيذ احترافي
            </h3>

            <p>
                تطوير وفق أفضل الممارسات ومعايير الجودة الحديثة.
            </p>
        </article>

        <article class="pricing-card featured reveal">
            <span class="pricing-label">
                الأداء
            </span>

            <h3>
                سرعة وكفاءة
            </h3>

            <p>
                حلول سريعة وآمنة وقابلة للتوسع مع نمو مشروعك.
            </p>
        </article>

        <article class="pricing-card reveal">
            <span class="pricing-label">
                الدعم
            </span>

            <h3>
                دعم مستمر
            </h3>

            <p>
                متابعة وتطوير وصيانة لضمان استمرار نجاح المشروع.
            </p>
        </article>
    </div>
</section>

<section class="cta-section">
    <div class="cta-card reveal">
        <span class="section-tag">
            جاهز للبدء؟
        </span>

        <h2>
            لنحوّل فكرتك إلى
            <strong>مشروع ناجح</strong>
        </h2>

        <p>
            تواصل معنا الآن للحصول على استشارة والبدء بتنفيذ مشروعك.
        </p>

        <div class="hero-ctas">
            <a
                href="{{ route('contact') }}"
                class="btn-primary"
            >
                تواصل معنا
            </a>

            <a
                href="{{ route('home') }}"
                class="btn-secondary"
            >
                العودة للرئيسية
            </a>
        </div>
    </div>
</section>

@endsection
@extends('layouts.app')

@section('title', 'خدماتنا | ' . __('home.brand'))

@section(
    'description',
    'استكشف خدمات منصة ALYASI الرقمية والتقنية.'
)

@section('content')

<section
    class="hero hero-small"
    id="services"
>
    <div class="hero-grid"></div>

    <div class="hero-badge">
        <div class="hero-badge-dot"></div>

        <span>
            خدمات ALYASI
        </span>
    </div>

    <h1>
        حلول رقمية
        <em>باحترافية</em>
    </h1>

    <p class="hero-sub">
        نقدم مجموعة متكاملة من الخدمات التقنية لمساعدة الأفراد والشركات
        على بناء مشاريع رقمية حديثة وقابلة للنمو.
    </p>
</section>

<section class="section">

    <div class="section-heading reveal">
        <p class="section-tag">
            خدماتنا
        </p>

        <h2 class="section-title">
            ماذا يمكننا أن
            <strong>نقدم لك؟</strong>
        </h2>

        <p class="section-body">
            اختر الخدمة المناسبة واطّلع على تفاصيلها والحلول التي نقدمها.
        </p>
    </div>

    @if ($services->count())

        <div class="services-list">

            @foreach ($services as $service)

                @php
                    $title = $service->localizedTitle();
                    $description = $service->localizedDescription();
                    $slug = $service->slug();
                    $isReverse = $loop->even;
                @endphp

                <article
                    class="feature reveal {{ $isReverse ? 'feature-reverse' : '' }}"
                >
                    <div class="feature-visual">
                        <img
                            src="{{ $service->image
                                ? asset($service->image)
                                : asset('luminary/images/tm-luminary-01.jpg') }}"
                            alt="{{ $title }}"
                            loading="lazy"
                        >
                    </div>

                    <div class="feature-content">
                        <div class="feature-number">
                            {{ str_pad(
                                $loop->iteration,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) }}
                        </div>

                        <p class="section-tag">
                            خدمة رقمية
                        </p>

                        <h2 class="section-title">
                            {{ $title }}
                        </h2>

                        <p class="section-body">
                            {{ \Illuminate\Support\Str::limit(
                                strip_tags($description),
                                260
                            ) }}
                        </p>

                        <div class="hero-ctas">
                            @if ($slug)
                                <a
                                    href="{{ route('services.show', $slug) }}"
                                    class="btn-primary"
                                >
                                    عرض تفاصيل الخدمة
                                </a>
                            @endif

                            <a
                                href="{{ route('contact') }}"
                                class="btn-secondary"
                            >
                                اطلب الخدمة
                            </a>
                        </div>
                    </div>
                </article>

            @endforeach

        </div>

        @if ($services->hasPages())
            <div class="services-pagination">
                {{ $services->links() }}
            </div>
        @endif

    @else

        <div class="services-empty reveal">
            <span class="services-empty-icon">
                <i class="fa-solid fa-layer-group"></i>
            </span>

            <h3>
                لا توجد خدمات متاحة حالياً
            </h3>

            <p>
                سيتم إضافة الخدمات الجديدة قريباً.
            </p>

            <a
                href="{{ route('contact') }}"
                class="btn-primary"
            >
                تواصل معنا
            </a>
        </div>

    @endif

</section>

<section class="cta-section">
    <div class="cta-card reveal">
        <span class="section-tag">
            ابدأ الآن
        </span>

        <h2>
            هل لديك فكرة؟
            <strong>لنحوّلها إلى واقع.</strong>
        </h2>

        <p>
            يسعدنا مناقشة مشروعك واقتراح أفضل الحلول التقنية المناسبة له.
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
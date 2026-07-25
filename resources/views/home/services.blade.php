<section
    class="section"
    id="services"
>
    @php
        $featuredService = $services->first();
    @endphp

    @if ($featuredService)

        @php
            $featuredTitle = $featuredService->localizedTitle();
            $featuredDescription = $featuredService->localizedDescription();
            $featuredSlug = $featuredService->slug();
        @endphp

        <div class="feature reveal">

            <div class="feature-visual">
                <img
                    src="{{ $featuredService->image
                        ? asset($featuredService->image)
                        : asset('luminary/images/tm-luminary-01.jpg') }}"
                    alt="{{ $featuredTitle }}"
                    loading="lazy"
                >
            </div>

            <div class="feature-content">

                <div class="feature-number">
                    01
                </div>

                <p class="section-tag">
                    خدمة مميزة
                </p>

                <h2 class="section-title">
                    {{ $featuredTitle }}
                </h2>

                <p class="section-body">
                    {{ \Illuminate\Support\Str::limit(
                        strip_tags($featuredDescription),
                        260
                    ) }}
                </p>

                @if ($featuredSlug)
                    <div class="hero-ctas">
                        <a
                            href="{{ route(
                                'services.show',
                                $featuredSlug
                            ) }}"
                            class="btn-primary"
                        >
                            عرض تفاصيل الخدمة
                        </a>

                        <a
                            href="{{ route('services.index') }}"
                            class="btn-secondary"
                        >
                            جميع الخدمات
                        </a>
                    </div>
                @endif

            </div>

        </div>

    @else

        <div class="feature reveal">

            <div class="feature-visual">
                <img
                    src="{{ asset(
                        'luminary/images/tm-luminary-01.jpg'
                    ) }}"
                    alt="منصة ALYASI"
                    loading="lazy"
                >
            </div>

            <div class="feature-content">

                <div class="feature-number">
                    01
                </div>

                <p class="section-tag">
                    منصة ALYASI
                </p>

                <h2 class="section-title">
                    كل ما تحتاجه
                    <strong>في منصة تقنية واحدة</strong>
                </h2>

                <p class="section-body">
                    تجمع ALYASI بين تطوير المواقع والتطبيقات،
                    والحلول الذكية، والأخبار التقنية، والمجتمع
                    الرقمي في منصة واحدة.
                </p>

                <div class="hero-ctas">
                    <a
                        href="{{ route('services.index') }}"
                        class="btn-primary"
                    >
                        استكشف خدماتنا
                    </a>
                </div>

            </div>

        </div>

    @endif

</section>
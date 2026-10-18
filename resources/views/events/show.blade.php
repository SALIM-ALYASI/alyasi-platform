@extends('layouts.app')

@php
    $phase = $edition->phase;
    $isAppleEvent2026 = request()->route('slug') === 'apple-event-2026';
    // صفحة تغطية «خمس محطات» الخاصة بـ CyberX Oman 2026.
    $coverageUrl = request()->route('slug') === \App\Support\Coverage\CyberxOman2026::EVENT_SLUG
        ? url((app()->getLocale() === 'en' ? '/en' : '').'/events/'.\App\Support\Coverage\CyberxOman2026::EVENT_SLUG.'/coverage')
        : null;
    $ogDescription = \Illuminate\Support\Str::limit(strip_tags($edition->short_description ?: ''), 160);

    // يربط كل "منتج/إعلان" بسعره من جدول الأسعار (لو موجود) -- مرن بالتطابق
    // (عربي أو إنجليزي، وتطابق جزئي زي "iPhone 18 Pro" داخل "iPhone 18 Pro
    // 256GB") لأن المدير غالبًا يكتب اسم المنتج بصياغة مختلفة شوي بين قسم
    // الإعلانات وقسم الأسعار (ترجمة عربية مقابل اسم إنجليزي، أو أرقام هندية
    // ١٨ مقابل 18، أو سعة تخزين مذكورة بجدول الأسعار بس). يسمح بعرض السعر
    // على بطاقة المنتج بمجرد ما المدير يضيفه، بغض النظر عن مرحلة المؤتمر.
    $normalize = function (?string $text): string {
        $text = trim((string) $text);
        $text = strtr($text, ['١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٠' => '0']);

        return mb_strtolower($text);
    };

    $pricingRows = collect($edition->pricing_table ?? [])
        ->filter(fn ($row) => is_array($row) && (filled($row['product_ar'] ?? null) || filled($row['product_en'] ?? null)))
        ->values();

    $parsePriceAmount = function ($price): float {
        $amount = preg_replace('/[^0-9.]/', '', (string) $price);

        return is_numeric($amount) ? (float) $amount : 0.0;
    };

    // إذا ما كان التحويل إلى OMR محفوظاً في قاعدة البيانات نحسبه وقت العرض.
    // هذا يدعم الأسعار المدخلة بالدولار أو الدرهم الإماراتي بدون الحاجة إلى
    // تعديل يدوي لقيمة OMR لكل سعة. القيم تقريبية للعرض فقط.
    $estimateOmrForRow = function (array $row) use ($parsePriceAmount): ?string {
        if (filled($row['omr_price'] ?? null)) {
            return (string) $row['omr_price'];
        }

        $amount = $parsePriceAmount($row['official_price'] ?? null);
        if ($amount <= 0) {
            return null;
        }

        $estimated = match (strtoupper(trim((string) ($row['official_currency'] ?? '')))) {
            'USD' => ceil($amount * 0.3845),
            'AED' => ceil($amount * 0.1047),
            'OMR' => ceil($amount),
            default => null,
        };

        return $estimated === null ? null : (string) (int) $estimated;
    };

    $priceInfoFor = function (array $item) use ($pricingRows, $normalize, $parsePriceAmount, $estimateOmrForRow) {
        $labelAr = $normalize($item['label_ar'] ?? null);
        $labelEn = $normalize($item['label_en'] ?? null);

        // تطابق حرفي أول (بعد التطبيع) -- يمنع تصادم أسماء متشابهة زي
        // "iPhone 18 Pro" داخل "iPhone 18 Pro Max". لو ما فيه تطابق حرفي،
        // نرجع لتطابق جزئي (احتواء) يغطي حالات زي لاحقة سعة التخزين
        // "iPhone Duo" مقابل "iPhone Duo 256GB".
        $group = $pricingRows->filter(function (array $row) use ($labelAr, $labelEn, $normalize) {
            $productAr = $normalize($row['product_ar'] ?? null);
            $productEn = $normalize($row['product_en'] ?? null);

            return ($labelAr !== '' && $labelAr === $productAr) || ($labelEn !== '' && $labelEn === $productEn);
        });

        if ($group->isEmpty()) {
            $group = $pricingRows->filter(function (array $row) use ($labelAr, $labelEn, $normalize) {
                $productAr = $normalize($row['product_ar'] ?? null);
                $productEn = $normalize($row['product_en'] ?? null);

                $arMatch = $labelAr !== '' && $productAr !== '' && (str_contains($labelAr, $productAr) || str_contains($productAr, $labelAr));
                $enMatch = $labelEn !== '' && $productEn !== '' && (str_contains($labelEn, $productEn) || str_contains($productEn, $labelEn));

                return $arMatch || $enMatch;
            });
        }

        if ($group->isEmpty()) {
            return null;
        }

        $cheapest = $group->sortBy(fn ($row) => $parsePriceAmount($row['official_price'] ?? null))->first();

        return [
            'official_price' => $cheapest['official_price'] ?? null,
            'official_currency' => $cheapest['official_currency'] ?? '',
            'omr_price' => $estimateOmrForRow($cheapest),
            'is_starting' => $group->count() > 1,
        ];
    };

    $productNameForRow = function (array $row, ?string $locale = null): string {
        $locale ??= app()->getLocale();

        if ($locale === 'en') {
            return trim((string) ($row['product_en'] ?? $row['product_ar'] ?? ''));
        }

        return trim((string) ($row['product_ar'] ?? $row['product_en'] ?? ''));
    };

    $variantForRow = function (array $row, ?string $locale = null) use ($productNameForRow): string {
        $locale ??= app()->getLocale();
        $explicit = $locale === 'en'
            ? ($row['variant_en'] ?? $row['variant_ar'] ?? null)
            : ($row['variant_ar'] ?? $row['variant_en'] ?? null);

        if (filled($explicit)) {
            return trim((string) $explicit);
        }

        $name = $productNameForRow($row, $locale);

        if (preg_match('/(?:^|\s)((?:[0-9٠-٩]+(?:\.[0-9٠-٩]+)?)\s*(?:GB|TB))\b/ui', $name, $matches)) {
            return strtoupper(preg_replace('/\s+/u', '', $matches[1]));
        }

        if (preg_match('/([0-9٠-٩]+\s*(?:جيجابايت|تيرابايت))/u', $name, $matches)) {
            return trim($matches[1]);
        }

        return '';
    };

    $baseNameForRow = function (array $row, ?string $locale = null) use ($productNameForRow): string {
        $name = $productNameForRow($row, $locale);
        $name = preg_replace('/\s+(?:[0-9٠-٩]+(?:\.[0-9٠-٩]+)?)\s*(?:GB|TB)\b/ui', '', $name);
        $name = preg_replace('/\s+[0-9٠-٩]+\s*(?:جيجابايت|تيرابايت)$/u', '', $name);

        return trim((string) $name);
    };

    // نجمع كل سعات/إصدارات المنتج في بطاقة أسعار واحدة. البيانات نفسها تبقى
    // في event_editions.pricing_table وتنعكس على الصفحة فور حفظها من لوحة التحكم.
    $pricingGroups = $pricingRows->groupBy(function (array $row) use ($baseNameForRow, $normalize) {
        $groupName = $baseNameForRow($row, 'en');
        if ($groupName === '') {
            $groupName = $baseNameForRow($row, 'ar');
        }

        return $normalize($groupName);
    });
@endphp

@section('title', $edition->title.' — ALYASI')
@section('meta_description', $ogDescription)

@section('canonical', $edition->permalink()?->url())

@if ($edition->permalink('ar') && $edition->permalink('en'))
    @section('hreflang_ar', $edition->permalink('ar')->url())
    @section('hreflang_en', $edition->permalink('en')->url())
@endif

@section('og_type', 'article')
@section('og_title', $edition->title)
@section('og_description', $ogDescription)
@section('og_url', $edition->permalink()?->url())
@section('og_image', $edition->displayImage() ? media_url($edition->displayImage()) : asset('images/events/og-cover.jpg'))
@section('og_image_width', 1200)
@section('og_image_height', 630)

{{-- بيانات Event المنظمة: بس للنسخ اللي مكانها مؤكد (App\Support\Events\EventFacts). --}}
@php
    $eventSchema = \App\Support\Events\EventFacts::schema(
        $edition,
        $ogDescription,
        $edition->displayImage() ? media_url($edition->displayImage()) : asset('images/events/og-cover.jpg')
    );
@endphp
@if ($eventSchema)
    @section('schema')
    <script type="application/ld+json">{!! json_encode($eventSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endsection
@endif

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/community-show.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/events-show.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/pages/events-index.css') }}">
@endpush

@section('content')

    <section class="container community-detail__hero-wrap">
        <div class="community-detail__hero-media">
            <img
                src="{{ $edition->displayImage() ? media_url($edition->displayImage()) : asset('images/events/og-cover.jpg') }}"
                alt="{{ $edition->title }}"
            >
            @if ($phase !== 'concluded')
                <span class="badge badge--status-{{ ['upcoming' => 'upcoming', 'live' => 'ongoing', 'remote' => 'upcoming'][$edition->badge_phase] }} community-detail__hero-badge">
                    {{ __('events.phase.'.$edition->badge_phase) }}
                </span>
            @endif
        </div>
    </section>

    <section class="container community-detail__body">

        <nav class="event-detail__crumbs" aria-label="{{ __('events.back_to_events') }}">
            <a href="{{ localized_route('events.index') }}">{{ __('events.back_to_events') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route(app()->getLocale() === 'en' ? 'event_editions.show.en' : 'event_editions.show', ['slug' => $edition->event->slug]) }}">{{ $edition->event->localizedName() }}</a>
        </nav>

        <h1 class="community-detail__title">{{ $edition->title }}</h1>

        {{-- موعد انعقاد المؤتمر شي، وتاريخ نشر صفحتنا شي ثاني --}}
        <ul class="event-detail__dates">
            @if ($edition->event_start_at)
                <li><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ __('events.event_date') }}: <time datetime="{{ $edition->event_start_at->copy()->timezone('Asia/Muscat')->toDateString() }}">{{ $edition->dateRangeLabel() }}</time></li>
            @endif
            @if ($edition->published_at)
                <li><i class="fa-regular fa-newspaper" aria-hidden="true"></i> {{ __('events.page_published') }}: <time datetime="{{ $edition->published_at->copy()->timezone('Asia/Muscat')->toDateString() }}">{{ $edition->published_at->copy()->timezone('Asia/Muscat')->locale(app()->getLocale())->translatedFormat('j F Y') }}</time></li>
            @endif
            @if ($edition->updated_at && $edition->published_at && $edition->updated_at->copy()->timezone('Asia/Muscat')->toDateString() !== $edition->published_at->copy()->timezone('Asia/Muscat')->toDateString())
                <li>{{ __('events.page_updated') }}: <time datetime="{{ $edition->updated_at->copy()->timezone('Asia/Muscat')->toDateString() }}">{{ $edition->updated_at->copy()->timezone('Asia/Muscat')->locale(app()->getLocale())->translatedFormat('j F Y') }}</time></li>
            @endif
        </ul>

        {{-- تغطية ALYASI الحصرية لهذي النسخة (من لوحة التحكم، لأي مؤتمر) --}}
        @php $highlights = \App\Support\Events\EventHighlights::for($edition); @endphp
        @if ($highlights['tags'] || $highlights['links'])
            <div class="event-detail__exclusive">
                @if ($highlights['tags'])
                    <ul class="event-card__tags">
                        @foreach ($highlights['tags'] as $tag)
                            <li>{{ $tag }}</li>
                        @endforeach
                    </ul>
                @endif
                @foreach ($highlights['links'] as $k => $link)
                    <a class="btn {{ $k === 0 ? 'btn--primary' : 'btn--outline' }}" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </div>
        @endif

        {{-- أهداف ALYASI من تغطية المؤتمر --}}
        @if ($goals = $edition->localizedGoals())
            <section class="event-detail__goals">
                <h2 class="event-detail__section-title">{{ __('events.goals_title') }}</h2>
                <ul>
                    @foreach ($goals as $goal)
                        <li>{{ $goal }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($coverageUrl)
            {{-- المؤتمر انتهى: نعرض أرشيف تغطية الياسي بدل التفاصيل والجدول --}}
            @include('events.coverage._edition-archive')
        @else

        @if ($phase !== 'concluded')
            <div class="event-detail__date-status">
                {{ __('events.date_status.'.$edition->date_status) }}
            </div>
        @endif

        {{-- معلومات المؤتمر (الموعد والحالة) قبل انعقاده بس؛ بعد ما ينتهي تبقى
             تغطيتنا وحدها -- الموعد مذكور في المحتوى نفسه. --}}
        @php $hasEventDate = $isAppleEvent2026 || $edition->event_start_at; @endphp
        @if ($phase !== 'concluded' && ($hasEventDate || $edition->official_url || ($edition->livestream_url && in_array($phase, ['upcoming', 'live'], true))))
        <div class="grid-2 community-detail__info-grid">
            @if ($hasEventDate)
            <div class="event-detail__date-card-row{{ $phase === 'concluded' ? ' event-detail__date-card-row--concluded' : '' }}">
                <div class="community-detail__info-card">
                    <div class="community-detail__info-label">
                        {{ $phase === 'concluded' ? __('events.held_on') : __('community.event_information') }}
                    </div>
                    <div class="community-detail__info-value">
                        @if ($isAppleEvent2026)
                            09.09.2026 — 22:00
                        @else
                            {{ $edition->event_start_at->copy()->timezone('Asia/Muscat')->translatedFormat($phase === 'concluded' ? 'd.m.Y' : 'd.m.Y — H:i') }}
                        @endif
                    </div>
                </div>

                @if ($phase === 'concluded')
                    <span class="badge badge--status-ended event-detail__phase-badge">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        {{ __('events.phase.concluded') }}
                    </span>
                @endif
            </div>
            @endif

            @if ($edition->official_url)
                <div class="community-detail__info-card">
                    <div class="community-detail__info-label">{{ __('events.official_website') }}</div>
                    <div class="community-detail__info-value">
                        <a href="{{ $edition->official_url }}" target="_blank" rel="noopener">{{ parse_url($edition->official_url, PHP_URL_HOST) }} ←</a>
                    </div>
                </div>
            @endif

            @if ($edition->livestream_url && in_array($phase, ['upcoming', 'live'], true))
                <div class="community-detail__info-card">
                    <div class="community-detail__info-label">{{ __('events.watch_live') }}</div>
                    <div class="community-detail__info-value">
                        <a href="{{ $edition->livestream_url }}" target="_blank" rel="noopener">{{ __('events.watch_live') }} ←</a>
                    </div>
                </div>
            @endif
        </div>
        @endif

        @if ($phase === 'live' && $edition->livestream_url)
            <div class="event-detail__live-banner">
                <span>{{ __('events.phase.live') }}</span>
                <a href="{{ $edition->livestream_url }}" target="_blank" rel="noopener" class="btn btn--light">
                    {{ __('events.watch_live') }}
                </a>
            </div>
        @endif

        @if ($edition->short_description)
            <p class="community-detail__paragraph">{{ $edition->short_description }}</p>
        @endif

        @if (filled($edition->content))
            <div class="event-content">{!! $edition->content !!}</div>
        @endif

        {{-- =====================================================
             شبكة المنتجات — موحّدة لكل المراحل، السعر يظهر تلقائيًا
             فوق البطاقة أول ما يتوفّر بجدول الأسعار (بدون انتظار "انتهى").
        ====================================================== --}}
        @if (!empty($edition->announcements))
            <h2 class="event-detail__section-title">
                {{ $phase === 'concluded' ? __('events.what_was_announced') : __('events.expected_announcements') }}
            </h2>
            <div class="product-grid">
                @foreach ($edition->announcements as $item)
                    @include('events._announcement-item', ['item' => $item, 'priceInfo' => $priceInfoFor($item)])
                @endforeach
            </div>
        @endif

        {{-- =====================================================
             جدول الأسعار الكامل — يُقرأ مباشرة من pricing_table
             في قاعدة البيانات ويجمع سعات كل منتج في جدول واحد.
        ====================================================== --}}
        @php
            // الصفوف اللي طلعت أسعارها داخل بطاقات المنتجات فوق ما تتكرر هنا --
            // نفس مطابقة events._announcement-item (الاسم الأساسي، أو «الاسم مع ...»).
            $cardLabels = collect($edition->announcements ?? [])->flatMap(fn ($item) => array_filter([
                $normalize($item['label_ar'] ?? null), $normalize($item['label_en'] ?? null),
            ]))->unique();
            $inCard = function (array $row) use ($cardLabels, $normalize, $baseNameForRow): bool {
                foreach (['ar' => ' مع ', 'en' => ' with '] as $lang => $joiner) {
                    $base = $normalize($baseNameForRow($row, $lang));
                    foreach ($cardLabels as $label) {
                        if ($base === $label || str_starts_with($base, $label.$joiner)) {
                            return true;
                        }
                    }
                }

                return false;
            };
            $tableRows = $pricingRows->reject($inCard)->values();
            $pricingGroups = $pricingGroups
                ->map(fn ($rows) => collect($rows)->reject($inCard)->values())
                ->filter(fn ($rows) => $rows->isNotEmpty());
        @endphp

        @if ($pricingRows->isNotEmpty() && $tableRows->isEmpty())
            <p class="event-detail__pricing-disclaimer">{{ __('events.pricing_table_disclaimer') }}</p>
        @endif

        @if ($tableRows->isNotEmpty())
            <h2 class="event-detail__section-title">{{ __('events.pricing_table_title') }}</h2>

            <div class="event-pricing-grid">
                @foreach ($pricingGroups as $rows)
                    @php
                        $firstRow = $rows->first();
                        $groupTitle = $baseNameForRow($firstRow, app()->getLocale());
                        if ($groupTitle === '') {
                            $groupTitle = $productNameForRow($firstRow, app()->getLocale());
                        }
                    @endphp

                    <section class="event-pricing-card">
                        <div class="event-pricing-card__header">
                            <div>
                                <span class="event-pricing-card__eyebrow">{{ __('events.pricing_table_official_price') }}</span>
                                <h3 class="event-pricing-card__title">{{ $groupTitle }}</h3>
                            </div>
                            <span class="event-pricing-card__count">{{ $rows->count() }}</span>
                        </div>

                        <div class="event-pricing-table-wrap">
                            <table class="event-pricing-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('events.pricing_table_variant') }}</th>
                                        <th>{{ __('events.pricing_table_official_price') }}</th>
                                        <th>{{ __('events.pricing_table_omr_price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        @php
                                            $variant = $variantForRow($row, app()->getLocale());
                                            $omrPrice = $estimateOmrForRow($row);
                                        @endphp
                                        <tr>
                                            <td class="event-pricing-table__variant">
                                                {{ $variant !== '' ? $variant : __('events.pricing_table_base_variant') }}
                                            </td>
                                            <td class="event-pricing-table__official">
                                                <strong>{{ $row['official_price'] ?? '—' }}</strong>
                                                @if (filled($row['official_currency'] ?? null))
                                                    <span>{{ strtoupper($row['official_currency']) }}</span>
                                                @endif
                                            </td>
                                            <td class="event-pricing-table__omr">
                                                @if ($omrPrice)
                                                    <span class="event-pricing-table__approx">≈</span>
                                                    <strong>{{ $omrPrice }}</strong>
                                                    <span>{{ __('events.omr_currency_short') }}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            </div>

            <p class="event-detail__pricing-disclaimer">{{ __('events.pricing_table_disclaimer') }}</p>
        @endif

        {{-- =====================================================
             معرض الصور وحكم الترقية — لمرحلة "انتهى" فقط
        ====================================================== --}}
        @if ($phase === 'concluded')

            @if (!empty($edition->gallery))
                <h2 class="event-detail__section-title">{{ __('events.gallery_title') }}</h2>
                <div class="event-detail__gallery">
                    @foreach ($edition->gallery as $photo)
                        <img src="{{ media_url($photo) }}" alt="{{ $edition->title }}" loading="lazy">
                    @endforeach
                </div>
            @endif

            @if ($edition->upgrade_verdict)
                <div class="event-detail__verdict">
                    <div class="event-detail__verdict-title">{{ __('events.upgrade_verdict_title') }}</div>
                    <div class="event-detail__verdict-answer">{{ __('events.upgrade_verdict.'.$edition->upgrade_verdict) }}</div>
                    @if ($edition->localized_upgrade_verdict_text)
                        @php
                            $verdictParagraphs = preg_split('/\R\s*\R/u', trim($edition->localized_upgrade_verdict_text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        @endphp
                        <div class="event-detail__verdict-text">
                            @foreach ($verdictParagraphs as $paragraph)
                                @php
                                    $paragraph = trim($paragraph);
                                    $isConclusion = \Illuminate\Support\Str::startsWith($paragraph, ['الخلاصة:', 'Bottom line:']);
                                @endphp
                                <p class="event-detail__verdict-paragraph{{ $isConclusion ? ' event-detail__verdict-conclusion' : '' }}">{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        @endif

        @endif {{-- /coverageUrl --}}

    </section>

    {{-- تكبير صورة المنتج — نفس المعرض ونفس بطاقات المنتجات --}}
    <div class="lightbox" id="event-lightbox" hidden>
        <button type="button" class="lightbox__close" id="event-lightbox-close" aria-label="{{ __('events.close_lightbox') }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <img src="" alt="" id="event-lightbox-image">
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lightbox = document.getElementById('event-lightbox');
    var lightboxImage = document.getElementById('event-lightbox-image');
    var closeBtn = document.getElementById('event-lightbox-close');

    function openLightbox(src, alt) {
        lightboxImage.src = src;
        lightboxImage.alt = alt || '';
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.hidden = true;
        lightboxImage.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-lightbox-trigger]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openLightbox(btn.getAttribute('data-lightbox-src'), btn.getAttribute('data-lightbox-alt'));
        });
    });

    closeBtn.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !lightbox.hidden) {
            closeLightbox();
        }
    });
});
</script>
@endpush
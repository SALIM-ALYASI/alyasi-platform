@php
    $locale = app()->getLocale();
    $label = $locale === 'en' ? ($item['label_en'] ?? $item['label_ar'] ?? '') : ($item['label_ar'] ?? '');
    $note = $locale === 'en' ? ($item['note_en'] ?? $item['note_ar'] ?? '') : ($item['note_ar'] ?? '');

    $preorderAt = !empty($item['preorder_at'])
        ? \Carbon\Carbon::parse($item['preorder_at'], 'Asia/Muscat')->startOfDay()
        : null;

    $availableAt = !empty($item['available_at'])
        ? \Carbon\Carbon::parse($item['available_at'], 'Asia/Muscat')->startOfDay()
        : null;

    $today = now('Asia/Muscat')->startOfDay();
    $isAvailable = $availableAt && $today->greaterThanOrEqualTo($availableAt);
    $isPreorderOpen = $preorderAt
        && $today->greaterThanOrEqualTo($preorderAt)
        && (!$availableAt || $today->lessThan($availableAt));

    $formatProductDate = fn ($date) => $date
        ? $date->copy()->locale($locale)->translatedFormat('d F Y')
        : null;

    /*
     * ندمج صفوف السعر داخل بطاقة المنتج نفسها بدل تكرارها في بطاقات بيضاء
     * مستقلة أسفل الصفحة. المطابقة تعتمد على الاسم الأساسي للمنتج، لذلك
     * iPhone 18 Pro لا يختلط مع Pro Max، بينما AirPods 5 with ... يبقى
     * ضمن بطاقة AirPods 5 كإصدار/خيار إضافي.
     */
    $itemLabelAr = isset($normalize) ? $normalize($item['label_ar'] ?? null) : mb_strtolower(trim((string) ($item['label_ar'] ?? '')));
    $itemLabelEn = isset($normalize) ? $normalize($item['label_en'] ?? null) : mb_strtolower(trim((string) ($item['label_en'] ?? '')));

    $cardPricingRows = collect($pricingRows ?? [])
        ->filter(function ($row) use ($itemLabelAr, $itemLabelEn, $normalize, $baseNameForRow) {
            if (!is_array($row)) {
                return false;
            }

            $baseAr = $normalize($baseNameForRow($row, 'ar'));
            $baseEn = $normalize($baseNameForRow($row, 'en'));

            $arMatch = $itemLabelAr !== '' && (
                $baseAr === $itemLabelAr
                || str_starts_with($baseAr, $itemLabelAr.' مع ')
            );

            $enMatch = $itemLabelEn !== '' && (
                $baseEn === $itemLabelEn
                || str_starts_with($baseEn, $itemLabelEn.' with ')
            );

            return $arMatch || $enMatch;
        })
        ->sortBy(function (array $row) use ($variantForRow) {
            $variant = strtoupper(preg_replace('/\s+/u', '', $variantForRow($row, 'en')) ?? '');
            $order = [
                '128GB' => 10,
                '256GB' => 20,
                '512GB' => 30,
                '1TB' => 40,
                '2TB' => 50,
            ];

            return $order[$variant] ?? 100;
        })
        ->values();

    $cardLocalCurrency = $displayCurrency
        ?? ($cardPricingRows->first()['display_currency'] ?? null)
        ?? 'OMR';
@endphp

<div class="product-card">

    @if (!empty($item['image']))
        <button
            type="button"
            class="product-card__image-btn"
            data-lightbox-trigger
            data-lightbox-src="{{ asset($item['image']) }}"
            data-lightbox-alt="{{ $label }}"
        >
            <img src="{{ asset($item['image']) }}" alt="{{ $label }}" class="product-card__image" loading="lazy">
            <span class="product-card__zoom-icon"><i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i></span>
        </button>
    @else
        <div class="product-card__image product-card__image--placeholder">
            <i class="fa-solid fa-image" aria-hidden="true"></i>
        </div>
    @endif

    <div class="product-card__body">

        @if (!empty($item['confidence']))
            <span class="badge product-card__confidence">{{ __('events.confidence.'.$item['confidence']) }}</span>
        @endif

        <h3 class="product-card__name">{{ $label }}</h3>

        @if ($note)
            <p class="product-card__note">{{ $note }}</p>
        @endif

        @if ($preorderAt || $availableAt)
            <div class="product-card__availability">
                @if ($isAvailable)
                    <div class="product-card__availability-row product-card__availability-row--available">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <strong>{{ __('events.available_now') }}</strong>
                    </div>
                @else
                    @if ($preorderAt)
                        <div class="product-card__availability-row">
                            <span class="product-card__availability-label">
                                {{ $isPreorderOpen ? __('events.preorder_open') : __('events.preorder_date') }}
                            </span>
                            @unless ($isPreorderOpen)
                                <span class="product-card__availability-value">{{ $formatProductDate($preorderAt) }}</span>
                            @endunless
                        </div>
                    @endif

                    @if ($availableAt)
                        <div class="product-card__availability-row">
                            <span class="product-card__availability-label">{{ __('events.available_from') }}</span>
                            <span class="product-card__availability-value">{{ $formatProductDate($availableAt) }}</span>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        @if ($cardPricingRows->isNotEmpty())
            <div class="product-card__pricing-panel">
                <div class="product-card__pricing-title">
                    <span>{{ __('events.pricing_table_title') }}</span>
                    <span class="product-card__pricing-currencies">USD · {{ $cardLocalCurrency }}</span>
                </div>

                <div class="product-card__pricing-head" aria-hidden="true">
                    <span>{{ __('events.pricing_table_variant') }}</span>
                    <span>USD</span>
                    <span>{{ $cardLocalCurrency }}</span>
                </div>

                <div class="product-card__pricing-rows">
                    @foreach ($cardPricingRows as $row)
                        @php
                            $variant = $variantForRow($row, $locale);
                            $localPrice = trim((string) ($row['omr_price'] ?? ''));
                            $localPriceNumber = trim(preg_replace('/\s+[A-Z]{3}$/', '', $localPrice) ?? $localPrice);
                        @endphp
                        <div class="product-card__pricing-row">
                            <span class="product-card__pricing-variant">
                                {{ $variant !== '' ? $variant : __('events.pricing_table_base_variant') }}
                            </span>
                            <span class="product-card__pricing-usd">
                                <strong>{{ $row['official_price'] ?? '—' }}</strong>
                            </span>
                            <span class="product-card__pricing-local">
                                @if ($localPriceNumber !== '')
                                    <span class="product-card__pricing-approx">≈</span>
                                    <strong>{{ $localPriceNumber }}</strong>
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif (!empty($priceInfo))
            {{-- Fallback لأي بيانات قديمة لا تحتوي صفوف سعات قابلة للمطابقة. --}}
            <div class="product-card__price">
                <div class="product-card__price-group">
                    <span class="product-card__price-tag">
                        {{ $priceInfo['is_starting'] ? __('events.starting_price') : __('events.official_price_label') }}
                    </span>
                    <span class="product-card__price-value">
                        {{ $priceInfo['official_price'] }} {{ $priceInfo['official_currency'] }}
                    </span>
                </div>
                @if ($priceInfo['omr_price'])
                    <div class="product-card__price-group">
                        <span class="product-card__price-tag">{{ __('events.omr_estimate_label') }}</span>
                        <span class="product-card__price-omr">{{ $priceInfo['omr_price'] }}</span>
                    </div>
                @endif
            </div>
        @endif

    </div>

</div>

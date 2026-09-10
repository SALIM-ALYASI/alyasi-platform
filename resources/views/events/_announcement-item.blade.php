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

        @if (!empty($priceInfo))
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
                        <span class="product-card__price-omr">{{ $priceInfo['omr_price'] }} OMR</span>
                    </div>
                @endif
            </div>
        @endif

    </div>

</div>

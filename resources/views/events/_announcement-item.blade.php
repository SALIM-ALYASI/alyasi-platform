@php
    $label = app()->getLocale() === 'en' ? ($item['label_en'] ?? $item['label_ar'] ?? '') : ($item['label_ar'] ?? '');
    $note = app()->getLocale() === 'en' ? ($item['note_en'] ?? $item['note_ar'] ?? '') : ($item['note_ar'] ?? '');
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

        @if (!empty($priceInfo))
            <div class="product-card__price">
                @if ($priceInfo['is_starting'])
                    <span class="product-card__price-label">{{ __('events.starting_price') }}</span>
                @endif
                <span class="product-card__price-value">
                    {{ $priceInfo['official_price'] }} {{ $priceInfo['official_currency'] }}
                </span>
                @if ($priceInfo['omr_price'])
                    <span class="product-card__price-omr">{{ $priceInfo['omr_price'] }} OMR</span>
                @endif
            </div>
        @endif

    </div>

</div>

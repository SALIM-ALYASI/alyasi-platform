@props([
    'badge' => null,
    'title' => '',
    'highlight' => null,
    'description' => null,
    'image' => null,
    'imageWidth' => null,
    'imageHeight' => null,
])

<section class="page-hero @unless($image) page-hero--solid @endunless">
    @if ($image)
        <div class="page-hero__bg">
            <img
                src="{{ $image }}"
                alt=""
                loading="eager"
                @if ($imageWidth) width="{{ $imageWidth }}" @endif
                @if ($imageHeight) height="{{ $imageHeight }}" @endif
            >
        </div>
    @endif
    <div class="page-hero__overlay"></div>

    <div class="page-hero__content container" data-reveal>
        @if ($badge)
            <span class="pill page-hero__badge">
                <span class="pill__dot"></span>
                {{ $badge }}
            </span>
        @endif

        <h1 class="page-hero__title">
            {{ $title }}
            @if ($highlight)
                <em>{{ $highlight }}</em>
            @endif
        </h1>

        @if ($description)
            <p class="page-hero__desc">{{ $description }}</p>
        @endif

        {{ $slot ?? '' }}
    </div>
</section>

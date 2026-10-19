@if ($paginator->hasPages())
<nav class="community-pagination" role="navigation" aria-label="Pagination Navigation">

    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
        <span class="community-pagination__item disabled">
            <i class="fa-solid fa-chevron-right rtl-flip"></i>
        </span>
    @else
        <a
            href="{{ $paginator->previousPageUrl() }}"
            class="community-pagination__item"
            rel="prev"
            aria-label="Previous"
        >
            <i class="fa-solid fa-chevron-right rtl-flip"></i>
        </a>
    @endif

    {{-- Pagination Elements --}}
    @foreach ($elements as $element)

        {{-- "..." Separator --}}
        @if (is_string($element))
            <span class="community-pagination__item disabled">
                {{ $element }}
            </span>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span
                        class="community-pagination__item active"
                        aria-current="page"
                    >
                        {{ $page }}
                    </span>
                @else
                    <a
                        href="{{ $url }}"
                        class="community-pagination__item"
                    >
                        {{ $page }}
                    </a>
                @endif
            @endforeach
        @endif

    @endforeach

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <a
            href="{{ $paginator->nextPageUrl() }}"
            class="community-pagination__item"
            rel="next"
            aria-label="Next"
        >
            <i class="fa-solid fa-chevron-left rtl-flip"></i>
        </a>
    @else
        <span class="community-pagination__item disabled">
            <i class="fa-solid fa-chevron-left rtl-flip"></i>
        </span>
    @endif

</nav>
@endif
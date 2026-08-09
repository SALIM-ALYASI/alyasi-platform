@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="{{ __('layout.nav.home') }}">
        @if ($paginator->onFirstPage())
            <span class="pagination__link pagination__link--disabled" aria-disabled="true">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination__link" rel="prev">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination__link pagination__link--dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination__link pagination__link--active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination__link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination__link" rel="next">›</a>
        @else
            <span class="pagination__link pagination__link--disabled" aria-disabled="true">›</span>
        @endif
    </nav>
@endif

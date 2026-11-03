{{-- Pagination for the public (X-ray themed) site. Usage: $paginator->links('partials.pagination-dark') --}}
@if ($paginator->hasPages())
    @php
        $base = 'flex h-11 min-w-11 items-center justify-center rounded-full px-3 text-sm transition';
        $link = $base.' bg-film-800 text-ink-2 ring-1 ring-film-600 hover:text-ink hover:ring-glow-400/40';
        $off = $base.' text-ink-3/50';
    @endphp
    <nav class="flex items-center justify-center gap-1.5" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="{{ $off }}" aria-hidden="true"><x-icon name="chevron-left" class="size-4" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $link }}"><x-icon name="chevron-left" class="size-4" /><span class="sr-only">Previous page</span></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="{{ $off }}">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="{{ $base }} bg-glow-400/15 text-glow-100 ring-1 ring-glow-400/50" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="{{ $link }}"><span class="sr-only">Page </span>{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $link }}"><x-icon name="chevron-right" class="size-4" /><span class="sr-only">Next page</span></a>
        @else
            <span class="{{ $off }}" aria-hidden="true"><x-icon name="chevron-right" class="size-4" /></span>
        @endif
    </nav>
@endif

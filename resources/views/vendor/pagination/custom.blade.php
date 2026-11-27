@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination Navigation" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
        {{-- Tombol Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span style="opacity: 0.5; cursor: not-allowed;">&laquo; Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Sebelumnya</a>
        @endif

        {{-- Nomor Halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span>{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" style="font-weight: 600; background-color: #f1f5f9; border-color: #94a3b8;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Tombol Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya &raquo;</a>
        @else
            <span style="opacity: 0.5; cursor: not-allowed;">Berikutnya &raquo;</span>
        @endif
    </nav>
@endif
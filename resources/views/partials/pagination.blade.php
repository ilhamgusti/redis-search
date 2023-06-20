<ul class="pagination-list">
    @isset($paginator)
       <!-- Previous Page -->
    <li class="pagination-item">
        <a href="{{ $prevPageUrl }}" class="pagination-link{{ $prevPageUrl ? '' : ' disabled' }}">
            Previous
        </a>
    </li>

    <!-- Page Numbers -->
    @if ($paginator->lastPage() > 1)
        @php
            $range = 2; // Jumlah halaman yang ingin ditampilkan sebelum dan setelah halaman saat ini
            $start = max(1, $paginator->currentPage() - $range);
            $end = min($paginator->lastPage(), $paginator->currentPage() + $range);
        @endphp

        <!-- First Page -->
        @if ($start > 1)
            <li class="pagination-item">
                <a href="{{ $paginator->url(1) }}" data-page="1" class="pagination-link">1</a>
            </li>
            @if ($start > 2)
                <li class="pagination-item disabled">
                    <span class="pagination-link">...</span>
                </li>
            @endif
        @endif

        <!-- Page Numbers within Range -->
        @for ($i = $start; $i <= $end; $i++)
            <li class="pagination-item{{ $i == $paginator->currentPage() ? ' active' : '' }}">
                <a href="{{ $paginator->url($i) }}" data-page="{{$i}}" class="pagination-link">{{ $i }}</a>
            </li>
        @endfor

        <!-- Last Page -->
        @if ($end < $paginator->lastPage())
            @if ($end < $paginator->lastPage() - 1)
                <li class="pagination-item disabled">
                    <span class="pagination-link">...</span>
                </li>
            @endif
            <li class="pagination-item">
                <a href="{{ $paginator->url($paginator->lastPage()) }}" data-page="{{$paginator->lastPage()}}" class="pagination-link">{{ $paginator->lastPage() }}</a>
            </li>
        @endif
    @endif

    <!-- Next Page -->
    <li class="pagination-item">
        <a href="{{ $nextPageUrl }}" class="pagination-link{{ $nextPageUrl ? '' : ' disabled' }}">
            Next
        </a>
    </li>
    @endisset
   
</ul>

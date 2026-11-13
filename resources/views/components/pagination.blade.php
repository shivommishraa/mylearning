@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())<span aria-disabled="true">←</span>@else<a href="{{ $paginator->previousPageUrl() }}">←</a>@endif
        @foreach ($elements as $element)
            @if (is_string($element))<span>…</span>@endif
            @if (is_array($element))@foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())<span class="active" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach @endif
        @endforeach
        @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">→</a>@else<span aria-disabled="true">→</span>@endif
    </nav>
@endif

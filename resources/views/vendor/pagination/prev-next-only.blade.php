@if ($paginator->hasPages())
    <nav role="navigation" class="flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="text-gray-500">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="text-blue-500 hover:underline">Previous</a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="text-blue-500 hover:underline">Next</a>
        @else
            <span class="text-gray-500">Next</span>
        @endif
    </nav>
@endif

@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1.5">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="w-8 h-8 rounded-xl bg-[#FAF6EE] text-[#D1C4B5] border border-[#EBDDCD] flex items-center justify-center text-xs cursor-not-allowed select-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="w-8 h-8 rounded-xl bg-white hover:bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD] flex items-center justify-center text-xs font-bold transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($paginator->links()->elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="w-8 h-8 flex items-center justify-center text-xs font-bold text-[#9CA3AF] select-none">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                            class="w-8 h-8 rounded-xl bg-[#E08A1E] text-white font-extrabold flex items-center justify-center text-xs shadow-xs select-none">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="w-8 h-8 rounded-xl bg-white hover:bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD] font-bold flex items-center justify-center text-xs transition shadow-xs">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="w-8 h-8 rounded-xl bg-white hover:bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD] flex items-center justify-center text-xs font-bold transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        @else
            <span class="w-8 h-8 rounded-xl bg-[#FAF6EE] text-[#D1C4B5] border border-[#EBDDCD] flex items-center justify-center text-xs cursor-not-allowed select-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        @endif
    </nav>
@else
    {{-- Single page placeholder --}}
    <div class="flex items-center gap-1.5">
        <span class="w-8 h-8 rounded-xl bg-[#E08A1E] text-white font-extrabold flex items-center justify-center text-xs shadow-xs select-none">
            1
        </span>
    </div>
@endif

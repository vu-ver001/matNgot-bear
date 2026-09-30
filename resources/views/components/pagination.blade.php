@props(['paginator'])

@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        // Thuật toán phân trang thông minh với dấu ba chấm
        if ($lastPage <= 7) {
            for ($i = 1; $i <= $lastPage; $i++) {
                $pages[] = $i;
            }
        } else {
            if ($currentPage <= 4) {
                for ($i = 1; $i <= 5; $i++) {
                    $pages[] = $i;
                }
                $pages[] = '...';
                $pages[] = $lastPage;
            } elseif ($currentPage >= $lastPage - 3) {
                $pages[] = 1;
                $pages[] = '...';
                for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                    $pages[] = $i;
                }
            } else {
                $pages[] = 1;
                $pages[] = '...';
                $pages[] = $currentPage - 1;
                $pages[] = $currentPage;
                $pages[] = $currentPage + 1;
                $pages[] = '...';
                $pages[] = $lastPage;
            }
        }
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1 sm:gap-1.5 flex-wrap justify-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-xs cursor-not-allowed select-none opacity-60"
                  style="background-color: #FAF6EE; color: #D1C4B5; border: 1px solid #EBDDCD;">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all duration-150 shadow-xs hover:shadow-sm"
               style="background-color: #FFFFFF; color: #5C3219; border: 1px solid #EBDDCD;"
               onmouseover="this.style.backgroundColor='#FFF5E6'; this.style.borderColor='#5C3219';"
               onmouseout="this.style.backgroundColor='#FFFFFF'; this.style.borderColor='#EBDDCD';">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($pages as $item)
            @if ($item === '...')
                <span class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-xs font-black tracking-widest select-none"
                      style="color: #A8988A;" aria-hidden="true">
                    ···
                </span>
            @elseif ($item == $currentPage)
                <span aria-current="page"
                      class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl font-extrabold flex items-center justify-center text-xs sm:text-sm select-none shadow-sm"
                      style="background-color: #5C3219 !important; color: #FFFFFF !important; border: 1.5px solid #5C3219 !important;">
                    {{ $item }}
                </span>
            @else
                <a href="{{ $paginator->url($item) }}"
                   class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl font-bold flex items-center justify-center text-xs sm:text-sm transition-all duration-150 shadow-xs hover:shadow-sm"
                   style="background-color: #FFFFFF; color: #5C3219; border: 1px solid #EBDDCD;"
                   onmouseover="this.style.backgroundColor='#FFF5E6'; this.style.borderColor='#5C3219';"
                   onmouseout="this.style.backgroundColor='#FFFFFF'; this.style.borderColor='#EBDDCD';">
                    {{ $item }}
                </a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all duration-150 shadow-xs hover:shadow-sm"
               style="background-color: #FFFFFF; color: #5C3219; border: 1px solid #EBDDCD;"
               onmouseover="this.style.backgroundColor='#FFF5E6'; this.style.borderColor='#5C3219';"
               onmouseout="this.style.backgroundColor='#FFFFFF'; this.style.borderColor='#EBDDCD';">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        @else
            <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-xs cursor-not-allowed select-none opacity-60"
                  style="background-color: #FAF6EE; color: #D1C4B5; border: 1px solid #EBDDCD;">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        @endif
    </nav>
@else
    {{-- Single page placeholder --}}
    <div class="flex items-center gap-1.5">
        <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl font-extrabold flex items-center justify-center text-xs sm:text-sm shadow-xs select-none"
              style="background-color: #5C3219 !important; color: #FFFFFF !important; border: 1.5px solid #5C3219 !important;">
            1
        </span>
    </div>
@endif

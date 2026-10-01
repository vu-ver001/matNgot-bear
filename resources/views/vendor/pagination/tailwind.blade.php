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

    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-3 w-full py-1">
        {{-- Thông tin kết quả --}}
        <div class="text-xs sm:text-sm select-none text-center sm:text-left" style="color: #8C7A6B;">
            @if ($paginator->firstItem())
                Hiển thị <span class="font-bold" style="color: #5C3219;">{{ $paginator->firstItem() }}</span>
                - <span class="font-bold" style="color: #5C3219;">{{ $paginator->lastItem() }}</span>
                trong <span class="font-bold" style="color: #5C3219;">{{ $paginator->total() }}</span> kết quả
            @else
                Tổng cộng <span class="font-bold" style="color: #5C3219;">{{ $paginator->total() }}</span> mục
            @endif
        </div>

        {{-- Thanh điều hướng trang --}}
        <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap justify-center">
            {{-- Nút Previous Page (<) --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Trang trước"
                      class="w-9 h-9 rounded-xl flex items-center justify-center text-xs cursor-not-allowed select-none opacity-60"
                      style="background-color: #FAF6EE; color: #D1C4B5; border: 1px solid #EBDDCD;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước"
                   class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all duration-150 shadow-xs hover:shadow-sm"
                   style="background-color: #FFFFFF; color: #5C3219; border: 1px solid #EBDDCD;"
                   onmouseover="this.style.backgroundColor='#FFF5E6'; this.style.borderColor='#5C3219';"
                   onmouseout="this.style.backgroundColor='#FFFFFF'; this.style.borderColor='#EBDDCD';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Các số trang và dấu '...' --}}
            @foreach ($pages as $item)
                @if ($item === '...')
                    <span class="w-8 h-9 sm:w-9 sm:h-9 flex items-center justify-center text-xs font-black tracking-widest select-none"
                          style="color: #A8988A;" aria-hidden="true">
                        ···
                    </span>
                @elseif ($item == $currentPage)
                    <span aria-current="page"
                          class="w-9 h-9 rounded-xl font-extrabold flex items-center justify-center text-xs sm:text-sm select-none shadow-sm"
                          style="background-color: #5C3219 !important; color: #FFFFFF !important; border: 1.5px solid #5C3219 !important;">
                        {{ $item }}
                    </span>
                @else
                    <a href="{{ $paginator->url($item) }}" aria-label="Trang {{ $item }}"
                       class="w-9 h-9 rounded-xl font-bold flex items-center justify-center text-xs sm:text-sm transition-all duration-150 shadow-xs hover:shadow-sm"
                       style="background-color: #FFFFFF; color: #5C3219; border: 1px solid #EBDDCD;"
                       onmouseover="this.style.backgroundColor='#FFF5E6'; this.style.borderColor='#5C3219';"
                       onmouseout="this.style.backgroundColor='#FFFFFF'; this.style.borderColor='#EBDDCD';">
                        {{ $item }}
                    </a>
                @endif
            @endforeach

            {{-- Nút Next Page (>) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau"
                   class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all duration-150 shadow-xs hover:shadow-sm"
                   style="background-color: #FFFFFF; color: #5C3219; border: 1px solid #EBDDCD;"
                   onmouseover="this.style.backgroundColor='#FFF5E6'; this.style.borderColor='#5C3219';"
                   onmouseout="this.style.backgroundColor='#FFFFFF'; this.style.borderColor='#EBDDCD';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="Trang sau"
                      class="w-9 h-9 rounded-xl flex items-center justify-center text-xs cursor-not-allowed select-none opacity-60"
                      style="background-color: #FAF6EE; color: #D1C4B5; border: 1px solid #EBDDCD;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif

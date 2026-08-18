@props(['status'])

@php
    $steps = [
        'PENDING' => 'Chờ xác nhận',
        'CONFIRMED' => 'Đã xác nhận',
        'PREPARING' => 'Đang đóng gói',
        'SHIPPING' => 'Đang giao hàng',
        'COMPLETED' => 'Hoàn thành',
    ];

    $currentIndex = array_search($status, array_keys($steps));
@endphp

@if ($status === 'CANCELLED')
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
        <p class="text-sm font-medium text-red-700">Đơn hàng đã bị hủy</p>
    </div>
@else
    <ol class="flex items-center">
        @foreach ($steps as $key => $label)
            @php
                $stepIndex = array_search($key, array_keys($steps));
                $done = $stepIndex < $currentIndex;
                $current = $stepIndex === $currentIndex;
            @endphp

            @if (!$loop->first)
                <li class="flex-1 h-0.5 mx-2 rounded-full {{ $done || $current ? 'bg-amber-400' : 'bg-gray-200' }}"></li>
            @endif

            <li class="flex flex-col items-center gap-1.5 shrink-0">
                <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold {{ $done ? 'bg-amber-500 text-white' : ($current ? 'bg-amber-100 text-amber-700 ring-2 ring-amber-500' : 'bg-gray-100 text-gray-400') }}">
                    @if ($done)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        {{ $stepIndex + 1 }}
                    @endif
                </span>
                <span class="text-[11px] font-medium {{ $done || $current ? 'text-[#8B5A2B]' : 'text-gray-400' }} whitespace-nowrap">{{ $label }}</span>
            </li>
        @endforeach
    </ol>
@endif
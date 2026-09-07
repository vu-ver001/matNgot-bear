@props(['status', 'cancelRequestStatus' => null, 'forStaff' => null])

@php
    $isStaffView = $forStaff ?? (request()->is('admin*') || request()->is('staff*'));

    $colors = [
        'PENDING' => 'bg-amber-100 text-amber-800',
        'CONFIRMED' => 'bg-blue-100 text-blue-800',
        'PREPARING' => 'bg-indigo-100 text-indigo-800',
        'SHIPPING' => 'bg-cyan-100 text-cyan-800',
        'COMPLETED' => 'bg-green-100 text-green-800',
        'CANCELLED' => 'bg-red-100 text-red-800',
        'RETURNED' => 'bg-orange-100 text-orange-800',
    ];

    $labels = [
        'PENDING' => 'Chờ xác nhận',
        'CONFIRMED' => 'Đã xác nhận',
        'PREPARING' => 'Đang đóng gói',
        'SHIPPING' => 'Đang giao hàng',
        'COMPLETED' => 'Đã giao',
        'CANCELLED' => 'Đã hủy',
        'RETURNED' => 'Trả hàng',
    ];
@endphp

<div class="inline-flex items-center gap-1.5 flex-wrap">
    @if ($cancelRequestStatus === 'PENDING')
        @if ($isStaffView)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 whitespace-nowrap shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                Khách yêu cầu hủy (Chờ duyệt)
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 whitespace-nowrap shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Chờ xác nhận hủy
            </span>
        @endif
    @else
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $colors[$status] ?? 'bg-gray-100 text-gray-800' }}">
            {{ $labels[$status] ?? $status }}
        </span>
    @endif
</div>
@props(['status', 'cancelRequestStatus' => null, 'forStaff' => null, 'paymentStatus' => null])

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

    @if ($status === 'CANCELLED' && $paymentStatus === 'PAID')
        @if ($isStaffView)
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 whitespace-nowrap shadow-2xs">
                <i class="fa-solid fa-hand-holding-dollar text-amber-600 text-[10px]"></i>
                Cần hoàn tiền
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-[10px]"></i>
                Chờ hoàn tiền
            </span>
        @endif
    @elseif ($status === 'CANCELLED' && $paymentStatus === 'REFUNDED')
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-300 whitespace-nowrap">
            <i class="fa-solid fa-check text-emerald-600 text-[10px]"></i>
            Đã hoàn tiền
        </span>
    @endif
</div>
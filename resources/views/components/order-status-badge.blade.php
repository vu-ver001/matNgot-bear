@props(['status', 'cancelRequestStatus' => null])

@php
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
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $colors[$status] ?? 'bg-gray-100 text-gray-800' }}">
        {{ $labels[$status] ?? $status }}
    </span>
    @if ($cancelRequestStatus === 'PENDING')
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 whitespace-nowrap">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
            Chờ duyệt hủy
        </span>
    @endif
</div>
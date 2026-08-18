@props(['status'])

@php
    $colors = [
        'PENDING' => 'bg-amber-100 text-amber-800',
        'CONFIRMED' => 'bg-blue-100 text-blue-800',
        'PREPARING' => 'bg-indigo-100 text-indigo-800',
        'SHIPPING' => 'bg-cyan-100 text-cyan-800',
        'COMPLETED' => 'bg-green-100 text-green-800',
        'CANCELLED' => 'bg-red-100 text-red-800',
    ];

    $labels = [
        'PENDING' => 'Chờ xác nhận',
        'CONFIRMED' => 'Đã xác nhận',
        'PREPARING' => 'Đang đóng gói',
        'SHIPPING' => 'Đang giao hàng',
        'COMPLETED' => 'Hoàn thành',
        'CANCELLED' => 'Đã hủy',
    ];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$status] ?? 'bg-gray-100 text-gray-800' }}">
    {{ $labels[$status] ?? $status }}
</span>
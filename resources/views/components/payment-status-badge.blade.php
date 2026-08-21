@props(['status'])

@php
    $colors = [
        'UNPAID' => 'bg-gray-100 text-gray-700',
        'PENDING' => 'bg-yellow-100 text-yellow-800',
        'PAID' => 'bg-green-100 text-green-800',
        'FAILED' => 'bg-red-100 text-red-800',
        'REFUNDED' => 'bg-purple-100 text-purple-800',
    ];

    $labels = [
        'UNPAID' => 'Chưa thanh toán',
        'PENDING' => 'Chờ xác nhận',
        'PAID' => 'Đã thanh toán',
        'FAILED' => 'Thất bại',
        'REFUNDED' => 'Đã hoàn tiền',
    ];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$status] ?? 'bg-gray-100 text-gray-800' }}">
    {{ $labels[$status] ?? $status }}
</span>
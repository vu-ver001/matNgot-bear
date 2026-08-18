@props(['role'])

@php
    $colors = [
        'ADMIN' => 'bg-purple-100 text-purple-800',
        'STAFF' => 'bg-blue-100 text-blue-800',
        'CUSTOMER' => 'bg-gray-100 text-gray-700',
    ];

    $labels = [
        'ADMIN' => 'Quản trị viên',
        'STAFF' => 'Nhân viên',
        'CUSTOMER' => 'Khách hàng',
    ];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$role] ?? 'bg-gray-100 text-gray-800' }}">
    {{ $labels[$role] ?? $role }}
</span>
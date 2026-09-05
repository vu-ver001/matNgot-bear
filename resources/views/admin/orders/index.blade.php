@extends('layouts.admin-dashboard')
@section('page-title', 'Quản Lý Đơn Hàng')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/order-components.css') }}">
@endsection

@section('content')

@if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- 1. Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-value">{{ $stats['total'] ?? $orders->total() }}</div>
            <div class="stat-subtext text-[#8E8076]">Tất cả thời gian</div>
        </div>
        <div class="stat-icon brown"><i class="fa-solid fa-cart-shopping"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Chờ xác nhận</div>
            <div class="stat-value text-amber-600">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-subtext text-amber-700">Cần duyệt & đóng gói</div>
        </div>
        <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang giao hàng</div>
            <div class="stat-value text-cyan-700">{{ $stats['shipping'] ?? 0 }}</div>
            <div class="stat-subtext text-cyan-600">Shipper đang vận chuyển</div>
        </div>
        <div class="stat-icon cyan"><i class="fa-solid fa-truck-fast"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đã hoàn thành</div>
            <div class="stat-value text-emerald-700">{{ $stats['completed'] ?? 0 }}</div>
            <div class="stat-subtext text-emerald-600">Giao thành công</div>
        </div>
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    </div>
</div>

@php
    $bulkShippingOrderIds = $orders
        ->filter(fn ($order) => $order->canTransitionTo('SHIPPING'))
        ->pluck('id')->values()->all();
@endphp
<div class="orders-ui">
<!-- 2. Orders Panel with Order Cards & Bulk Toolbar -->
<div class="panel-card" x-data="bulkOrderManager({{ json_encode($bulkShippingOrderIds) }})">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-boxes-packing"></i>
                Danh Sách Đơn Hàng
            </div>
            <div class="panel-subtitle">Tra cứu, lọc và cập nhật tiến trình xử lý đơn hàng</div>
        </div>
    </div>

    <!-- Status Tabs (Pills) -->
    @php
        $tabs = [
            '' => ['label' => 'Tất cả', 'count' => $stats['total'] ?? null],
            'PENDING' => ['label' => 'Chờ xác nhận', 'count' => $stats['pending'] ?? null],
            'CONFIRMED' => ['label' => 'Đã xác nhận', 'count' => $stats['confirmed'] ?? null],
            'PREPARING' => ['label' => 'Chờ lấy hàng', 'count' => $stats['preparing'] ?? null],
            'SHIPPING' => ['label' => 'Chờ giao hàng', 'count' => $stats['shipping'] ?? null],
            'COMPLETED' => ['label' => 'Đã giao', 'count' => $stats['completed'] ?? null],
            'RETURNED' => ['label' => 'Trả hàng', 'count' => $stats['returned'] ?? null],
            'CANCELLED' => ['label' => 'Đã hủy', 'count' => $stats['cancelled'] ?? null],
        ];
    @endphp
    <div class="nav-pills">
        @foreach ($tabs as $value => $tab)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('order_status', 'page'), $value ? ['order_status' => $value] : [])) }}"
               class="nav-pill {{ (string) request('order_status') === $value ? 'active' : '' }}">
                <span>{{ $tab['label'] }}</span>
                @if (isset($tab['count']))
                    <span class="nav-pill-count">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Toolbar Filters -->
    @include('orders.partials.filters', ['routePrefix' => 'admin.orders'])

    <!-- Bulk Operations Toolbar -->
    @include('orders.partials.bulk-toolbar', ['routePrefix' => 'admin.orders'])

    <!-- Orders Cards List -->
    <div class="orders-cards-container space-y-4">
        @forelse ($orders as $order)
            @include('orders.partials.staff-order-card', [
                'order' => $order,
                'routePrefix' => 'admin.orders',
                'isStaff' => false
            ])
        @empty
            <div class="p-10 text-center text-[#8E8076] bg-white rounded-2xl border border-amber-200/60">
                <i class="fa-solid fa-box-open text-3xl text-amber-300 mb-2 block"></i>
                Không tìm thấy đơn hàng nào phù hợp với điều kiện lọc.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>
</div>

@endsection

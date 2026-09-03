@extends('layouts.staff-dashboard')

@section('page-title', 'Dashboard Vận Hành')

@section('content')
<!-- 1. Staff Banner -->
<div class="staff-banner">
    <div>
        <div class="staff-banner-title">Xin chào, {{ auth()->user()->full_name ?? 'Nhân viên' }}! 👋</div>
    </div>
    <div class="staff-banner-pill">
        <i class="fa-regular fa-calendar-check"></i>
        <span>Hôm nay: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
    </div>
</div>

<!-- 2. Thống kê vận hành hôm nay -->
<div class="stats-grid">
    <!-- Đơn chờ xác nhận -->
    <a href="{{ route('staff.orders.index', ['status' => 'PENDING']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Chờ xác nhận</div>
            <div class="stat-value text-amber-600">{{ $pendingOrders }}</div>
            <div class="stat-subtext text-amber-700">
                <i class="fa-solid fa-hourglass-half text-[11px]"></i> Cần xử lý sớm
            </div>
        </div>
        <div class="stat-icon amber">
            <i class="fa-solid fa-clock"></i>
        </div>
    </a>

    <!-- Đơn đang xử lý -->
    <a href="{{ route('staff.orders.index', ['status' => 'PROCESSING']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang xử lý</div>
            <div class="stat-value text-blue-600">{{ $processingOrders }}</div>
            <div class="stat-subtext text-blue-700">
                <i class="fa-solid fa-boxes-packing text-[11px]"></i> Đang đóng gói
            </div>
        </div>
        <div class="stat-icon blue">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </a>

    <!-- Đơn đang giao -->
    <a href="{{ route('staff.orders.index', ['status' => 'SHIPPING']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang giao hàng</div>
            <div class="stat-value text-cyan-600">{{ $shippingOrders }}</div>
            <div class="stat-subtext text-cyan-700">
                <i class="fa-solid fa-truck-fast text-[11px]"></i> Đang vận chuyển
            </div>
        </div>
        <div class="stat-icon cyan">
            <i class="fa-solid fa-truck"></i>
        </div>
    </a>

    <!-- Hoàn thành hôm nay -->
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Hoàn thành hôm nay</div>
            <div class="stat-value text-emerald-600">{{ $completedToday }}</div>
            <div class="stat-subtext text-emerald-700">
                <i class="fa-solid fa-circle-check text-[11px]"></i> Giao thành công
            </div>
        </div>
        <div class="stat-icon green">
            <i class="fa-solid fa-check-double"></i>
        </div>
    </div>

    <!-- Doanh thu hôm nay -->
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Doanh thu hôm nay</div>
            <div class="stat-value text-amber-700 text-xl font-extrabold">{{ number_format($revenueToday, 0, ',', '.') }} đ</div>
            <div class="stat-subtext text-amber-800">
                <i class="fa-solid fa-coins text-[11px]"></i> Thu thực tế
            </div>
        </div>
        <div class="stat-icon honey">
            <i class="fa-solid fa-coins"></i>
        </div>
    </div>
</div>

<!-- 3. Đơn Hàng Mới Nhất -->
<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Đơn Hàng Mới Nhất
            </div>
        </div>
        <a href="{{ route('staff.orders.index') }}" class="btn btn-outline btn-sm">
            Xem tất cả đơn hàng <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th class="text-right">Tổng Tiền</th>
                    <th>Trạng Thái Đơn</th>
                    <th>Thanh Toán</th>
                    <th>Ngày Đặt</th>
                    <th class="text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td class="font-bold text-[#4E342E]">{{ $order->order_code }}</td>
                        <td>
                            <div class="font-semibold text-[#4E342E]">{{ $order->customer?->full_name ?? 'Khách vãng lai' }}</div>
                            <div class="text-xs text-[#8E8076]">{{ $order->shipping_phone }}</div>
                        </td>
                        <td class="text-right font-bold text-amber-700">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td><x-order-status-badge :status="$order->order_status" /></td>
                        <td><x-payment-status-badge :status="$order->payment_status" /></td>
                        <td class="text-[#795548] text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right">
                            <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-primary btn-sm">
                                Xử lý
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-sm text-[#8E8076]">Chưa có đơn hàng nào cần xử lý.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
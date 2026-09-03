@extends('layouts.staff-dashboard')
@section('page-title', 'Xử Lý Đơn Hàng')
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

<!-- 2. Orders Panel -->
<div class="panel-card">
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
            'PREPARING' => ['label' => 'Chờ lấy hàng', 'count' => $stats['preparing'] ?? null],
            'SHIPPING' => ['label' => 'Chờ giao hàng', 'count' => $stats['shipping'] ?? null],
            'COMPLETED' => ['label' => 'Đã giao', 'count' => $stats['completed'] ?? null],
            'RETURNED' => ['label' => 'Trả hàng', 'count' => $stats['returned'] ?? null],
            'CANCELLED' => ['label' => 'Đã hủy', 'count' => $stats['cancelled'] ?? null],
        ];
    @endphp
    <div class="nav-pills">
        @foreach ($tabs as $value => $tab)
            <a href="{{ route('staff.orders.index', array_merge(request()->except('order_status', 'page'), $value ? ['order_status' => $value] : [])) }}"
               class="nav-pill {{ request('order_status') === $value ? 'active' : '' }}">
                <span>{{ $tab['label'] }}</span>
                @if (isset($tab['count']))
                    <span class="nav-pill-count">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Toolbar Filters -->
    <form method="GET" action="{{ route('staff.orders.index') }}" class="toolbar-grid">
        @if (request('order_status'))
            <input type="hidden" name="order_status" value="{{ request('order_status') }}">
        @endif

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Tìm kiếm theo mã đơn, tên khách, số điện thoại..."
                   class="input-control">
        </div>

        <div style="min-width: 180px;">
            <select name="payment_status" class="select-control">
                <option value="">-- Thanh toán: Tất cả --</option>
                @foreach (['UNPAID' => 'Chưa thanh toán', 'PENDING' => 'Chờ xác nhận', 'PAID' => 'Đã thanh toán', 'FAILED' => 'Thất bại', 'REFUNDED' => 'Đã hoàn tiền'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter text-xs"></i> Lọc
            </button>
            @if (request()->hasAny(['search', 'payment_status', 'order_status']))
                <a href="{{ route('staff.orders.index') }}" class="btn btn-outline" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Đặt lại
                </a>
            @endif
        </div>
    </form>

    <!-- Orders Data Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Số Điện Thoại</th>
                    <th class="text-right">Tổng Tiền</th>
                    <th>Trạng Thái Đơn</th>
                    <th>Thanh Toán</th>
                    <th>Ngày Đặt</th>
                    <th class="text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('staff.orders.show', $order) }}" class="font-bold text-[#4E342E] hover:text-[#B87309] hover:underline">
                                {{ $order->order_code }}
                            </a>
                        </td>
                        <td>
                            <div class="font-bold text-[#4E342E]">{{ $order->customer?->full_name ?? $order->recipient_name }}</div>
                            @if ($order->customer)
                                <div class="text-[11px] text-[#8E8076]"><i class="fa-regular fa-user text-[10px]"></i> Thành viên</div>
                            @else
                                <div class="text-[11px] text-[#8E8076]">Khách vãng lai</div>
                            @endif
                        </td>
                        <td class="text-[#795548] font-medium">{{ $order->recipient_phone }}</td>
                        <td class="text-right font-extrabold text-amber-700">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td><x-order-status-badge :status="$order->order_status" /></td>
                        <td><x-payment-status-badge :status="$order->payment_status" /></td>
                        <td class="text-xs text-[#795548]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right">
                            <a href="{{ route('staff.orders.show', $order) }}" class="btn btn-outline btn-sm">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-[#8E8076]">
                            <i class="fa-solid fa-box-open text-3xl text-amber-300 mb-2 block"></i>
                            Không tìm thấy đơn hàng nào phù hợp với điều kiện lọc.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-4">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
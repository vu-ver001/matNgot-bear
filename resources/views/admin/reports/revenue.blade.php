@extends('layouts.admin-dashboard')
@section('page-title', 'Báo Cáo Doanh Thu')
@section('content')

@if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- 1. Bộ Lọc Thời Gian & Xuất Báo Cáo -->
<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-chart-line"></i>
                Báo Cáo Doanh Thu Bán Hàng
            </div>
            <div class="panel-subtitle">Thống kê chỉ ghi nhận các đơn hàng giao thành công (COMPLETED & PAID)</div>
        </div>
        <a href="{{ route('admin.reports.revenue.export', request()->only(['from_date', 'to_date'])) }}"
           class="btn btn-success">
            <i class="fa-solid fa-file-csv"></i> Xuất File CSV
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reports.revenue') }}" class="toolbar-grid">
        <div style="min-width: 170px;">
            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Từ ngày</label>
            <input type="date" name="from_date" value="{{ $from->format('Y-m-d') }}" class="input-control">
        </div>

        <div style="min-width: 170px;">
            <label class="block text-xs font-bold text-[#795548] uppercase mb-1">Đến ngày</label>
            <input type="date" name="to_date" value="{{ $to->format('Y-m-d') }}" class="input-control">
        </div>

        <div class="flex items-end gap-2 pt-5">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter text-xs"></i> Xem Báo Cáo
            </button>
            <a href="{{ route('admin.reports.revenue') }}" class="btn btn-outline" title="Về tháng hiện tại">
                <i class="fa-solid fa-rotate-left text-xs"></i> Tháng này
            </a>
        </div>
    </form>
</div>

<!-- 2. KPI Cards Grid (4 Cards) -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng Doanh Thu Thực</div>
            <div class="stat-value text-amber-600">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
            <div class="stat-subtext text-amber-700">Đã trừ giảm giá</div>
        </div>
        <div class="stat-icon honey"><i class="fa-solid fa-coins"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đơn Hoàn Thành</div>
            <div class="stat-value text-emerald-700">{{ $totalOrders }}</div>
            <div class="stat-subtext text-emerald-600">Giao dịch thành công</div>
        </div>
        <div class="stat-icon green"><i class="fa-solid fa-bag-shopping"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Giá Trị TB / Đơn (AOV)</div>
            <div class="stat-value text-blue-700">{{ number_format($avgOrderValue, 0, ',', '.') }} đ</div>
            <div class="stat-subtext text-blue-600">Trung bình mỗi đơn</div>
        </div>
        <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng Giảm Giá Voucher</div>
            <div class="stat-value text-rose-600">-{{ number_format($totalDiscount, 0, ',', '.') }} đ</div>
            <div class="stat-subtext text-rose-500">Khuyến mãi đã áp dụng</div>
        </div>
        <div class="stat-icon red"><i class="fa-solid fa-ticket"></i></div>
    </div>
</div>

<!-- 3. Bảng Doanh Thu Theo Ngày -->
<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-calendar-days"></i>
                Doanh Thu Phân Bổ Theo Ngày
            </div>
            <div class="panel-subtitle">Tổng hợp biến động doanh số và số lượng đơn hàng theo từng ngày trong kỳ</div>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ngày Bán</th>
                    <th class="text-center">Số Lượng Đơn</th>
                    <th class="text-right">Doanh Thu Trong Ngày</th>
                    <th class="text-right">Tỷ Trọng Kỳ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyRevenue as $day)
                    @php
                        $percentage = $totalRevenue > 0 ? round(($day->total / $totalRevenue) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="font-bold text-[#4E342E]">
                            <i class="fa-regular fa-calendar text-xs text-amber-600 mr-1.5"></i>
                            {{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }} (Thứ {{ \Carbon\Carbon::parse($day->date)->dayOfWeekIso + 1 === 8 ? 'CN' : \Carbon\Carbon::parse($day->date)->dayOfWeekIso + 1 }})
                        </td>
                        <td class="text-center">
                            <span class="badge-pastel brown font-bold">
                                {{ $day->order_count }} đơn
                            </span>
                        </td>
                        <td class="text-right font-extrabold text-amber-700 text-sm">
                            {{ number_format($day->total, 0, ',', '.') }} đ
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <div class="w-20 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-[#795548] w-10 text-right">{{ $percentage }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-[#8E8076]">
                            <i class="fa-solid fa-chart-line text-3xl text-amber-300 mb-2 block"></i>
                            Không có phát sinh doanh thu nào trong khoảng thời gian đã chọn.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 4. Danh Sách Đơn Hàng Hoàn Thành Trong Kỳ -->
<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-list-check"></i>
                Danh Sách Đơn Hàng Trong Kỳ
            </div>
            <div class="panel-subtitle">Chi tiết từng đơn hàng hoàn thành phát sinh doanh thu</div>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Phương Thức</th>
                    <th class="text-right">Tổng Tiền</th>
                    <th>Thời Gian Hoàn Thành</th>
                    <th class="text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-[#4E342E] hover:text-amber-700 hover:underline">
                                {{ $order->order_code }}
                            </a>
                        </td>
                        <td>
                            <div class="font-bold text-[#4E342E]">{{ $order->customer?->full_name ?? $order->recipient_name }}</div>
                            <div class="text-xs text-[#8E8076]">{{ $order->recipient_phone }}</div>
                        </td>
                        <td>
                            <span class="badge-pastel blue">{{ $order->payment_method }}</span>
                        </td>
                        <td class="text-right font-extrabold text-amber-700">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td class="text-xs text-[#795548]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-[#8E8076]">Chưa có đơn hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@endsection
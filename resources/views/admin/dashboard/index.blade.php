@extends('layouts.admin-dashboard')
@section('page-title', 'Dashboard Quản Trị')
@section('content')

<!-- 1. Welcome Banner -->
<div class="admin-banner">
    <div>
        <div class="admin-banner-title">Xin chào, {{ auth()->user()->full_name ?? 'Quản trị viên' }}! </div>
    </div>
    <div class="admin-banner-pill">
        <i class="fa-regular fa-calendar-check"></i>
        <span>Hôm nay: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
    </div>
</div>

<!-- 2. Row 1: Quy mô & Doanh thu (4 Cards) -->
<div class="stats-grid">
    <!-- Doanh thu theo tháng -->
    <div class="stat-card" style="flex-direction: column; align-items: stretch; justify-content: space-between;">
        <div class="flex items-center justify-between gap-2 mb-2">
            <span class="stat-label mb-0">
                @if ($isCurrentMonth ?? false)
                    Doanh thu tháng này
                @else
                    Doanh thu T{{ sprintf('%02d', $selectedMonth ?? \Carbon\Carbon::now()->month) }}/{{ $selectedYear ?? \Carbon\Carbon::now()->year }}
                @endif
            </span>
            <select onchange="const [m, y] = this.value.split('-'); window.location.href = '{{ route('admin.dashboard') }}?month=' + m + '&year=' + y;"
                    class="text-xs py-1 px-2 pr-6 bg-amber-50/80 border border-amber-200/80 rounded-lg text-[#8B5A2B] font-bold focus:ring-1 focus:ring-amber-400 focus:border-amber-400 cursor-pointer"
                    aria-label="Chọn tháng thống kê">
                @for ($i = 0; $i < 12; $i++)
                    @php
                        $optDate = \Carbon\Carbon::now()->subMonths($i);
                        $val = $optDate->month . '-' . $optDate->year;
                        $isSelected = ($optDate->month == ($selectedMonth ?? \Carbon\Carbon::now()->month) && $optDate->year == ($selectedYear ?? \Carbon\Carbon::now()->year));
                    @endphp
                    <option value="{{ $val }}" {{ $isSelected ? 'selected' : '' }}>
                        T{{ $optDate->format('m/Y') }}{{ $i === 0 ? ' (Hiện tại)' : '' }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="flex items-center justify-between">
            <div>
                <div class="stat-value text-amber-600">{{ number_format($currentMonthRevenue, 0, ',', '.') }} đ</div>
                @if ($monthChange !== null)
                    <div class="stat-subtext {{ $monthChange > 0 ? 'text-emerald-600' : ($monthChange < 0 ? 'text-rose-600' : 'text-[#64748B]') }}">
                        @if ($monthChange > 0)
                            <i class="fa-solid fa-arrow-trend-up"></i>
                            <span>+{{ number_format($monthChange, 1) }}% so với tháng trước</span>
                        @elseif ($monthChange < 0)
                            <i class="fa-solid fa-arrow-trend-down"></i>
                            <span>{{ number_format($monthChange, 1) }}% so với tháng trước</span>
                        @else
                            <i class="fa-solid fa-minus text-[10px]"></i>
                            <span>0% so với tháng trước</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="stat-icon honey">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="mt-3 pt-2.5 border-t border-amber-100 flex items-center justify-between text-xs text-[#8E8076]">
            <span>Tổng tích lũy:</span>
            <span class="font-bold text-[#1E293B]">{{ number_format($totalRevenue, 0, ',', '.') }} đ</span>
        </div>
    </div>

    <!-- Sản phẩm -->
    <a href="{{ route('admin.products.index') }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Sản phẩm</div>
            <div class="stat-value">{{ $totalProducts }}</div>
            <div class="stat-subtext text-[#8E8076]">
                <i class="fa-solid fa-boxes-stacked text-[11px]"></i> Đang kinh doanh
            </div>
        </div>
        <div class="stat-icon brown">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </a>

    <!-- Khách hàng -->
    <a href="{{ route('admin.users.index', ['role' => 'CUSTOMER']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Khách hàng</div>
            <div class="stat-value text-blue-700">{{ $totalCustomers }}</div>
            <div class="stat-subtext text-blue-600">
                <i class="fa-solid fa-user-check text-[11px]"></i> Tài khoản thành viên
            </div>
        </div>
        <div class="stat-icon blue">
            <i class="fa-solid fa-users"></i>
        </div>
    </a>

    <!-- Nhân viên -->
    <a href="{{ route('admin.users.index', ['role' => 'STAFF']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Nhân viên</div>
            <div class="stat-value text-purple-700">{{ $totalStaff }}</div>
            <div class="stat-subtext text-purple-600">
                <i class="fa-solid fa-shield-halved text-[11px]"></i> Vận hành & CSKH
            </div>
        </div>
        <div class="stat-icon purple">
            <i class="fa-solid fa-user-shield"></i>
        </div>
    </a>
</div>

<!-- 3. Row 2: Tình trạng Đơn hàng (4 Cards) -->
<div class="stats-grid">
    <a href="{{ route('admin.orders.index') }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-value">{{ $totalOrders }}</div>
            <div class="stat-subtext text-[#8E8076]">Toàn bộ thời gian</div>
        </div>
        <div class="stat-icon brown">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
    </a>

    <a href="{{ route('admin.orders.index', ['order_status' => 'COMPLETED']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đã hoàn thành</div>
            <div class="stat-value text-emerald-700">{{ $completedOrders }}</div>
            <div class="stat-subtext text-emerald-600">
                <i class="fa-solid fa-circle-check text-[11px]"></i> Giao dịch thành công
            </div>
        </div>
        <div class="stat-icon green">
            <i class="fa-solid fa-check-double"></i>
        </div>
    </a>

    <a href="{{ route('admin.orders.index', ['order_status' => 'PENDING']) }}" class="stat-card">
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

    <a href="{{ route('admin.orders.index', ['order_status' => 'CANCELLED']) }}" class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đã hủy</div>
            <div class="stat-value text-rose-700">{{ $cancelledOrders }}</div>
            <div class="stat-subtext text-rose-600">
                <i class="fa-solid fa-ban text-[11px]"></i> Đơn bị hủy
            </div>
        </div>
        <div class="stat-icon red">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
    </a>
</div>

<!-- 4. Biểu đồ Doanh thu & Top Sản Phẩm Bán Chạy -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
    <!-- Panel Left: Sơ đồ đường Doanh Thu 12 tháng -->
    <div class="panel-card lg:col-span-3 mb-0 flex flex-col justify-between">
        <div class="panel-header">
            <div>
                <div class="panel-title">
                    <i class="fa-solid fa-chart-line text-amber-600"></i>
                    Doanh Thu 12 Tháng - Năm {{ $selectedYear }}
                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $thisYear = \Carbon\Carbon::now()->year;
                @endphp
                <!-- Chuyển năm: Năm ngoái / Năm nay -->
                <div class="flex items-center p-1 bg-[#FAF6F0] rounded-xl border border-[#EADFCF]">
                    <a href="{{ route('admin.dashboard', ['year' => $thisYear - 1, 'month' => $selectedMonth]) }}"
                       class="px-2.5 py-1 text-xs rounded-lg transition-all {{ $selectedYear == ($thisYear - 1) ? 'bg-white text-[#4E342E] shadow-xs font-bold' : 'text-[#8E8076] hover:text-[#4E342E]' }}">
                        Năm ngoái ({{ $thisYear - 1 }})
                    </a>
                    <a href="{{ route('admin.dashboard', ['year' => $thisYear, 'month' => $selectedMonth]) }}"
                       class="px-2.5 py-1 text-xs rounded-lg transition-all {{ $selectedYear == $thisYear ? 'bg-white text-[#4E342E] shadow-xs font-bold' : 'text-[#8E8076] hover:text-[#4E342E]' }}">
                        Năm nay ({{ $thisYear }})
                    </a>
                </div>

                <a href="{{ route('admin.reports.revenue') }}" class="btn btn-outline btn-sm">
                    Báo cáo <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Sơ đồ đường tràn đầy khung box -->
        <div class="relative w-full flex-1 min-h-[310px]">
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
    </div>

    <!-- Panel Right: Top 10 Sản Phẩm Bán Chạy -->
    <div class="panel-card lg:col-span-2 mb-0" x-data="{ page: 1 }">
        @php $topPages = max($topProducts->chunk(5)->count(), 1); @endphp
        <div class="panel-header">
            <div>
                <div class="panel-title">
                    <i class="fa-solid fa-fire text-amber-500"></i>
                    Top Sản Phẩm Bán Chạy
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                        class="btn-icon btn-sm disabled:opacity-30 disabled:cursor-not-allowed"
                        aria-label="Trang trước">‹</button>
                <span class="text-xs font-bold text-[#795548] w-9 text-center" x-text="`${page}/{{ $topPages }}`"></span>
                <button type="button" @click="page = Math.min({{ $topPages }}, page + 1)" :disabled="page === {{ $topPages }}"
                        class="btn-icon btn-sm disabled:opacity-30 disabled:cursor-not-allowed"
                        aria-label="Trang sau">›</button>
            </div>
        </div>

        @if ($topProducts->isEmpty())
            <div class="p-8 text-center text-sm text-[#8E8076]">
                <i class="fa-solid fa-box-open text-2xl text-amber-300 mb-2 block"></i>
                Chưa có dữ liệu sản phẩm bán ra.
            </div>
        @else
            <div class="table-container">
                @foreach ($topProducts->chunk(5) as $chunkIndex => $chunk)
                    <div x-show="page === {{ $chunkIndex + 1 }}" x-cloak>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 45px;">#</th>
                                    <th>Sản Phẩm</th>
                                    <th class="text-right whitespace-nowrap">Đã Bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chunk as $product)
                                    @php $rank = $chunkIndex * 5 + $loop->iteration; @endphp
                                    <tr>
                                        <td>
                                            @if ($rank === 1)
                                                <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs flex items-center justify-center">🥇</span>
                                            @elseif ($rank === 2)
                                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 font-extrabold text-xs flex items-center justify-center">🥈</span>
                                            @elseif ($rank === 3)
                                                <span class="w-6 h-6 rounded-full bg-amber-50 text-amber-700 font-extrabold text-xs flex items-center justify-center">🥉</span>
                                            @else
                                                <span class="text-xs font-bold text-[#8E8076] pl-1.5">{{ $rank }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-bold text-[#4E342E] line-clamp-1">{{ $product->name }}</div>
                                            <div class="text-xs text-[#8E8076]">{{ number_format($product->price, 0, ',', '.') }} đ</div>
                                        </td>
                                        <td class="text-right font-extrabold text-amber-700">
                                            {{ $product->sold_count }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- 5. Đơn Hàng Gần Đây -->
<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Đơn Hàng Gần Đây
            </div>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
            Xem tất cả <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th class="text-right">Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Đặt</th>
                    <th class="text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td class="font-bold text-[#4E342E]">{{ $order->order_code }}</td>
                        <td>
                            <div class="font-semibold text-[#4E342E]">{{ $order->customer?->full_name ?? $order->recipient_name }}</div>
                            <div class="text-xs text-[#8E8076]">{{ $order->recipient_phone }}</div>
                        </td>
                        <td class="text-right font-bold text-amber-700">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td><x-order-status-badge :status="$order->order_status" /></td>
                        <td class="text-[#795548] text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-sm text-[#8E8076]">Chưa có đơn hàng nào được tạo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('monthlyRevenueChart');
    if (!canvas) return;

    const monthlyData = @json($monthlyRevenue->values());
    const selectedMonth = {{ $selectedMonth ?? \Carbon\Carbon::now()->month }};
    const selectedYear = {{ $selectedYear ?? \Carbon\Carbon::now()->year }};

    const labels = monthlyData.map(d => `Tháng ${d.month}`);
    const values = monthlyData.map(d => Number(d.total));
    const orderCounts = monthlyData.map(d => Number(d.order_count || 0));

    const ctx = canvas.getContext('2d');

    // Gradient màu mật ong nhẹ nhàng
    const gradient = ctx.createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(229, 152, 25, 0.35)');
    gradient.addColorStop(1, 'rgba(229, 152, 25, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Doanh thu',
                    data: values,
                    borderColor: '#B87309',
                    borderWidth: 3.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: monthlyData.map(d => (d.month == selectedMonth && d.year == selectedYear) ? '#B87309' : '#FFFFFF'),
                    pointBorderColor: monthlyData.map(d => (d.month == selectedMonth && d.year == selectedYear) ? '#78350F' : '#E59819'),
                    pointBorderWidth: 2.5,
                    pointRadius: monthlyData.map(d => (d.month == selectedMonth && d.year == selectedYear) ? 8 : 5),
                    pointHoverRadius: 10,
                    pointHoverBackgroundColor: '#B87309',
                    pointHoverBorderColor: '#FFFFFF',
                    pointHoverBorderWidth: 2.5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#FFFBFB',
                    titleColor: '#4E342E',
                    bodyColor: '#795548',
                    borderColor: '#EADFCF',
                    borderWidth: 1.5,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    titleFont: { family: 'Be Vietnam Pro', size: 12, weight: '700' },
                    bodyFont: { family: 'Be Vietnam Pro', size: 12 },
                    callbacks: {
                        label: function(context) {
                            const idx = context.dataIndex;
                            const val = context.parsed.y;
                            const orders = orderCounts[idx];
                            return [
                                ` Doanh thu: ${new Intl.NumberFormat('vi-VN').format(val)} đ`,
                                ` Đơn hoàn thành: ${orders} đơn`
                            ];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#795548',
                        font: { family: 'Be Vietnam Pro', size: 11, weight: '600' }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#F2EAE0' },
                    ticks: {
                        color: '#8E8076',
                        font: { family: 'Be Vietnam Pro', size: 11 },
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000) + 'tr';
                            if (value >= 1000) return (value / 1000) + 'k';
                            return value + ' đ';
                        }
                    }
                }
            },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const idx = elements[0].index;
                    const item = monthlyData[idx];
                    window.location.href = `{{ route('admin.dashboard') }}?month=${item.month}&year=${item.year}`;
                }
            }
        }
    });
});
</script>
@endsection
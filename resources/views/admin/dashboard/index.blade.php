@extends('layouts.admin-dashboard')
@section('page-title', 'Dashboard quản trị')
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <p class="text-sm text-[#64748B]">Tổng doanh thu</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
                @php
                    $monthChange = $previousMonthRevenue > 0
                        ? round(($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue * 100, 1)
                        : ($currentMonthRevenue > 0 ? 100.0 : null);
                @endphp
                @if ($monthChange !== null)
                    <p class="mt-1 text-xs font-medium text-green-600">
                        {{ $monthChange >= 0 ? '+' : '' }}{{ number_format($monthChange, 1) }}% so với tháng trước
                    </p>
                @endif
            </div>
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <p class="text-sm text-[#64748B]">Sản phẩm</p>
                <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalProducts }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <p class="text-sm text-[#64748B]">Khách hàng</p>
                <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalCustomers }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <p class="text-sm text-[#64748B]">Nhân viên</p>
                <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalStaff }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <p class="text-sm text-[#64748B]">Tổng đơn hàng</p>
                <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalOrders }}</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
                <p class="text-sm text-green-700">Hoàn thành</p>
                <p class="mt-1 text-xl font-bold text-green-800">{{ $completedOrders }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <p class="text-sm text-amber-700">Chờ xác nhận</p>
                <p class="mt-1 text-xl font-bold text-amber-800">{{ $pendingOrders }}</p>
            </div>
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                <p class="text-sm text-rose-700">Đã hủy</p>
                <p class="mt-1 text-xl font-bold text-rose-800">{{ $cancelledOrders }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm lg:col-span-3">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Doanh thu 12 tháng</h3>
                    @php
                        $maxRevenue = max($monthlyRevenue->max('total'), 1);
                        $monthLabels = [
                            1 => 'T1', 2 => 'T2', 3 => 'T3', 4 => 'T4', 5 => 'T5', 6 => 'T6',
                            7 => 'T7', 8 => 'T8', 9 => 'T9', 10 => 'T10', 11 => 'T11', 12 => 'T12',
                        ];
                        $hasData = $monthlyRevenue->contains(fn ($item) => $item->total > 0);
                        $points = $monthlyRevenue->values()->map(function ($item, $i) use ($maxRevenue) {
                            return (object) [
                                'x' => round(20 + $i * (560 / 11), 1),
                                'y' => round(200 - ($item->total / $maxRevenue) * 176, 1),
                                'total' => (float) $item->total,
                                'month' => $item->month,
                                'year' => $item->year,
                            ];
                        });
                        $linePoints = $points->map(fn ($p) => "{$p->x},{$p->y}")->implode(' ');
                        $areaPoints = $points->map(fn ($p) => "{$p->x},{$p->y}")->push('580,200')->prepend('20,200')->implode(' ');
                    @endphp
                    @if (! $hasData)
                        <p class="text-sm text-[#64748B]">Chưa có dữ liệu doanh thu.</p>
                    @else
                        <div class="relative" x-data="{ hp: null }">
                            <div class="relative h-56">
                                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 600 220" preserveAspectRatio="none">
                                    @foreach ([200, 156, 112, 68, 24] as $gridY)
                                        <line x1="20" y1="{{ $gridY }}" x2="580" y2="{{ $gridY }}"
                                              stroke-width="1" stroke="{{ $gridY === 200 ? '#FCD34D' : '#FEF3C7' }}" />
                                    @endforeach
                                    <polygon points="{{ $areaPoints }}" fill="rgba(251, 191, 36, 0.15)" />
                                    <polyline points="{{ $linePoints }}" fill="none" stroke="#F59E0B"
                                              stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                    @foreach ($points as $point)
                                        @if ($point->total > 0)
                                            <circle cx="{{ $point->x }}" cy="{{ $point->y }}" r="4.5"
                                                    fill="#ffffff" stroke="#F59E0B" stroke-width="2.5" />
                                            <circle cx="{{ $point->x }}" cy="{{ $point->y }}" r="14"
                                                    fill="transparent" stroke="transparent" class="cursor-pointer"
                                                    @mouseenter="hp = { m: {{ $point->month }}, y: {{ $point->year }}, t: {{ $point->total }}, px: {{ ($point->x / 600) * 100 }}, py: {{ ($point->y / 220) * 100 }} }"
                                                    @mouseleave="hp = null" />
                                        @endif
                                    @endforeach
                                </svg>
                                @foreach ($points as $point)
                                    @if ($point->total > 0)
                                        <span class="absolute -translate-x-1/2 -translate-y-full text-[10px] text-[#64748B] whitespace-nowrap"
                                              style="left: {{ ($point->x / 600) * 100 }}%; top: {{ ($point->y / 220) * 100 }}%;">
                                            {{ number_format($point->total / 1000000, 1) }}tr
                                        </span>
                                    @endif
                                @endforeach
                                <div x-show="hp" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-10 pointer-events-none"
                                     :style="`left:${hp?.px}%;top:${hp?.py}%;transform:translate(-50%,-130%)`">
                                    <div class="bg-[#FFFBFB] border border-[#EBDDCD] rounded-xl shadow-[0_8px_24px_rgba(139,90,43,0.15)] backdrop-blur-sm px-3 py-2 text-center whitespace-nowrap">
                                        <p class="text-[11px] font-medium text-[#8E8076]">Tháng <span x-text="hp?.m"></span>/<span x-text="hp?.y"></span></p>
                                        <p class="text-sm font-extrabold text-[#B45309]"><span x-text="hp?.t ? new Intl.NumberFormat('vi-VN').format(hp.t)+' đ' : '0 đ'"></span></p>
                                    </div>
                                    <svg class="mx-auto -mt-1" width="12" height="6" viewBox="0 0 12 6"><polygon points="0,0 12,0 6,6" fill="#FFFBFB" stroke="#EBDDCD" stroke-width="1" stroke-linejoin="round"/></svg>
                                </div>
                            </div>
                            <div class="relative h-5 mt-1">
                                @foreach ($points as $point)
                                    <span class="absolute -translate-x-1/2 text-xs text-[#64748B]"
                                          style="left: {{ ($point->x / 600) * 100 }}%">
                                        {{ $monthLabels[$point->month] ?? $point->month }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm lg:col-span-2">
                <div class="p-6" x-data="{ page: 1 }">
                    @php $topPages = $topProducts->chunk(5)->count(); @endphp
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#1E293B]">Top 10 sản phẩm bán chạy</h3>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1"
                                    class="w-7 h-7 flex items-center justify-center rounded-full border border-amber-200 text-amber-700 hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed text-sm"
                                    aria-label="Trang trước">‹</button>
                            <span class="text-xs text-[#64748B] w-10 text-center" x-text="`${page} / {{ $topPages }}`"></span>
                            <button type="button" @click="page = Math.min({{ $topPages }}, page + 1)" :disabled="page === {{ $topPages }}"
                                    class="w-7 h-7 flex items-center justify-center rounded-full border border-amber-200 text-amber-700 hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed text-sm"
                                    aria-label="Trang sau">›</button>
                        </div>
                    </div>
                    @if ($topProducts->isEmpty())
                        <p class="text-sm text-[#64748B]">Chưa có dữ liệu.</p>
                    @else
                        @foreach ($topProducts->chunk(5) as $chunkIndex => $chunk)
                            <div x-show="page === {{ $chunkIndex + 1 }}" x-cloak>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-amber-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Sản phẩm</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap">Đã bán</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($chunk as $product)
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-[#64748B]">{{ $chunkIndex * 5 + $loop->iteration }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-[#1E293B]">{{ $product->name }}</td>
                                                <td class="px-4 py-3 text-sm text-[#64748B] text-right">{{ $product->sold_count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-2xl border border-amber-100 shadow-sm">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-[#1E293B]">Đơn hàng gần đây</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm text-amber-700 hover:text-[#8B5A2B]">Xem tất cả →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-amber-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Mã đơn</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Khách hàng</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Tổng tiền</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Trạng thái</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-[#1E293B]">{{ $order->order_code }}</td>
                                    <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->customer?->full_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-[#1E293B] text-right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                    <td class="px-4 py-3"><x-order-status-badge :status="$order->order_status" /></td>
                                    <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-[#64748B]">Chưa có đơn hàng nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
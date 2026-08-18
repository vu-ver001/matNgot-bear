<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1E293B] leading-tight">Dashboard quản trị</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Tổng doanh thu</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Tổng đơn hàng</p>
                    <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalOrders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Khách hàng</p>
                    <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalCustomers }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Sản phẩm</p>
                    <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ $totalProducts }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <p class="text-sm text-amber-700">Chờ xác nhận</p>
                    <p class="mt-1 text-xl font-bold text-amber-800">{{ $pendingOrders }}</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
                    <p class="text-sm text-green-700">Hoàn thành</p>
                    <p class="mt-1 text-xl font-bold text-green-800">{{ $completedOrders }}</p>
                </div>
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                    <p class="text-sm text-rose-700">Đã hủy</p>
                    <p class="mt-1 text-xl font-bold text-rose-800">{{ $cancelledOrders }}</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                    <p class="text-sm text-blue-700">Nhân viên</p>
                    <p class="mt-1 text-xl font-bold text-blue-800">{{ $totalStaff }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Doanh thu 12 tháng</h3>
                        @php
                            $maxRevenue = max($monthlyRevenue->max('total') ?? 0, 1);
                            $monthLabels = [
                                1 => 'T1', 2 => 'T2', 3 => 'T3', 4 => 'T4', 5 => 'T5', 6 => 'T6',
                                7 => 'T7', 8 => 'T8', 9 => 'T9', 10 => 'T10', 11 => 'T11', 12 => 'T12',
                            ];
                        @endphp
                        @if ($monthlyRevenue->isEmpty())
                            <p class="text-sm text-[#64748B]">Chưa có dữ liệu doanh thu.</p>
                        @else
                            <div class="flex items-end gap-2 h-40">
                                @foreach ($monthlyRevenue as $item)
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full bg-amber-400 rounded-t-xl relative" style="height: {{ max(($item->total / $maxRevenue) * 100, 2) }}%">
                                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-[#64748B] whitespace-nowrap">
                                                {{ number_format($item->total / 1000000, 1) }}tr
                                            </div>
                                        </div>
                                        <span class="text-xs text-[#64748B]">{{ $monthLabels[$item->month] ?? $item->month }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Top 10 sản phẩm bán chạy</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-amber-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Sản phẩm</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Đã bán</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($topProducts as $index => $product)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-[#64748B]">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-[#1E293B]">{{ $product->name }}</td>
                                            <td class="px-4 py-3 text-sm text-[#64748B] text-right">{{ $product->sold_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-10 text-center text-sm text-[#64748B]">Chưa có dữ liệu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
</x-app-layout>
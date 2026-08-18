<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard quản trị</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Tổng doanh thu</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Tổng đơn hàng</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Khách hàng</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalCustomers }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Sản phẩm</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-700">Chờ xác nhận</p>
                    <p class="mt-1 text-xl font-bold text-yellow-800">{{ $pendingOrders }}</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-700">Hoàn thành</p>
                    <p class="mt-1 text-xl font-bold text-green-800">{{ $completedOrders }}</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-700">Đã hủy</p>
                    <p class="mt-1 text-xl font-bold text-red-800">{{ $cancelledOrders }}</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700">Nhân viên</p>
                    <p class="mt-1 text-xl font-bold text-blue-800">{{ $totalStaff }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Doanh thu 12 tháng</h3>
                        @php
                            $maxRevenue = max($monthlyRevenue->max('total') ?? 0, 1);
                            $monthLabels = [
                                1 => 'T1', 2 => 'T2', 3 => 'T3', 4 => 'T4', 5 => 'T5', 6 => 'T6',
                                7 => 'T7', 8 => 'T8', 9 => 'T9', 10 => 'T10', 11 => 'T11', 12 => 'T12',
                            ];
                        @endphp
                        @if ($monthlyRevenue->isEmpty())
                            <p class="text-sm text-gray-500">Chưa có dữ liệu doanh thu.</p>
                        @else
                            <div class="flex items-end gap-2 h-40">
                                @foreach ($monthlyRevenue as $item)
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full bg-indigo-100 rounded-t-md relative" style="height: {{ max(($item->total / $maxRevenue) * 100, 2) }}%">
                                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-gray-600 whitespace-nowrap">
                                                {{ number_format($item->total / 1000000, 1) }}tr
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $monthLabels[$item->month] ?? $item->month }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 sản phẩm bán chạy</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đã bán</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($topProducts as $index => $product)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ $product->sold_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-10 text-center text-sm text-gray-500">Chưa có dữ liệu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Đơn hàng gần đây</h3>
                        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Xem tất cả →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $order->order_code }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $order->customer?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                        <td class="px-4 py-3"><x-order-status-badge :status="$order->order_status" /></td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Chưa có đơn hàng nào.</td>
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
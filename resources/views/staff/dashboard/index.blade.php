<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard vận hành</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Đơn chờ xác nhận</p>
                    <p class="mt-2 text-2xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Đơn đang xử lý</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">{{ $processingOrders }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Đơn đang giao</p>
                    <p class="mt-2 text-2xl font-bold text-cyan-600">{{ $shippingOrders }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Hoàn thành hôm nay</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $completedToday }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">Doanh thu hôm nay</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($revenueToday, 0, ',', '.') }} đ</p>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Đơn hàng mới nhất</h3>
                        <a href="{{ route('staff.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Xem tất cả →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thanh toán</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $order->order_code }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $order->customer?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                        <td class="px-4 py-3"><x-order-status-badge :status="$order->order_status" /></td>
                                        <td class="px-4 py-3"><x-payment-status-badge :status="$order->payment_status" /></td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('staff.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Xử lý</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Chưa có đơn hàng nào.</td>
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
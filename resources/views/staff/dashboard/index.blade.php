<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1E293B] leading-tight">Dashboard vận hành</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Đơn chờ xác nhận</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pendingOrders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Đơn đang xử lý</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600">{{ $processingOrders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Đơn đang giao</p>
                    <p class="mt-2 text-2xl font-bold text-cyan-600">{{ $shippingOrders }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Hoàn thành hôm nay</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $completedToday }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                    <p class="text-sm text-[#64748B]">Doanh thu hôm nay</p>
                    <p class="mt-2 text-2xl font-bold text-[#1E293B]">{{ number_format($revenueToday, 0, ',', '.') }} đ</p>
                </div>
            </div>

            <div class="mt-6 bg-white rounded-2xl border border-amber-100 shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#1E293B]">Đơn hàng mới nhất</h3>
                        <a href="{{ route('staff.orders.index') }}" class="text-sm text-amber-700 hover:text-[#8B5A2B]">Xem tất cả →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thanh toán</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Ngày đặt</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-[#1E293B]">{{ $order->order_code }}</td>
                                        <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->customer?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-[#1E293B] text-right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                        <td class="px-4 py-3"><x-order-status-badge :status="$order->order_status" /></td>
                                        <td class="px-4 py-3"><x-payment-status-badge :status="$order->payment_status" /></td>
                                        <td class="px-4 py-3 text-sm text-[#64748B]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('staff.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-[#8B5A2B] bg-amber-100 rounded-full hover:bg-amber-200">Xử lý</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-[#64748B]">Chưa có đơn hàng nào.</td>
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
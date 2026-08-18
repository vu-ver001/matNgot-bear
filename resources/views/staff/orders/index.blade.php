<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quản lý đơn hàng</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Mã đơn / Tên / SĐT"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái đơn</label>
                            <select name="order_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach (['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'PREPARING' => 'Đang đóng gói', 'SHIPPING' => 'Đang giao', 'COMPLETED' => 'Hoàn thành', 'CANCELLED' => 'Đã hủy'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('order_status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái thanh toán</label>
                            <select name="payment_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Tất cả</option>
                                @foreach (['UNPAID' => 'Chưa thanh toán', 'PENDING' => 'Chờ xác nhận', 'PAID' => 'Đã thanh toán', 'FAILED' => 'Thất bại', 'REFUNDED' => 'Đã hoàn tiền'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">Lọc</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SĐT</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thanh toán</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $order->order_code }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ $order->customer?->full_name ?? '—' }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $order->recipient_phone }}</td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                        <td class="px-4 py-4"><x-order-status-badge :status="$order->order_status" /></td>
                                        <td class="px-4 py-4"><x-payment-status-badge :status="$order->payment_status" /></td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 text-right">
                                            <a href="{{ route('staff.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Xử lý</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">Không có đơn hàng nào phù hợp.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
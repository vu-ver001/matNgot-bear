<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Đơn hàng của tôi</h2>
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
                    <div class="mb-6 flex flex-wrap gap-2">
                        @php
                            $tabs = [
                                '' => 'Tất cả',
                                'PENDING' => 'Chờ xác nhận',
                                'CONFIRMED' => 'Đã xác nhận',
                                'PREPARING' => 'Đang đóng gói',
                                'SHIPPING' => 'Đang giao',
                                'COMPLETED' => 'Hoàn thành',
                                'CANCELLED' => 'Đã hủy',
                            ];
                        @endphp
                        @foreach ($tabs as $value => $label)
                            <a href="{{ route('customer.orders.index', $value ? ['order_status' => $value] : []) }}"
                               class="px-3 py-1.5 rounded-md text-sm font-medium {{ request('order_status') === $value ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đặt</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thanh toán</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $order->order_code }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-700">
                                            {{ $order->details->count() }} sản phẩm
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">
                                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                        </td>
                                        <td class="px-4 py-4">
                                            <x-order-status-badge :status="$order->order_status" />
                                        </td>
                                        <td class="px-4 py-4">
                                            <x-payment-status-badge :status="$order->payment_status" />
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <a href="{{ route('customer.orders.show', $order) }}"
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                                Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                            Bạn chưa có đơn hàng nào.
                                        </td>
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
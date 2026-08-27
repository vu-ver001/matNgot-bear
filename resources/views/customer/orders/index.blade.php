<x-customer-account-layout title="Đơn hàng của tôi" :flush="true">
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                <div class="p-6">
                    <div class="mb-6 flex flex-wrap gap-2">
                        @php
                            $tabs = [
                                '' => 'Tất cả',
                                'PENDING' => 'Chờ xác nhận',
                                'PREPARING' => 'Chờ lấy hàng',
                                'SHIPPING' => 'Chờ giao hàng',
                                'COMPLETED' => 'Đã giao',
                                'RETURNED' => 'Trả hàng',
                                'CANCELLED' => 'Đã hủy',
                            ];
                        @endphp
                        @foreach ($tabs as $value => $label)
                            <a href="{{ route('customer.orders.index', $value ? ['order_status' => $value] : []) }}"
                               class="px-3 py-1.5 rounded-full text-sm font-medium {{ request('order_status') === $value ? 'bg-amber-500 text-white' : 'bg-amber-50 text-[#8B5A2B] hover:bg-amber-100' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Ngày đặt</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Sản phẩm</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thanh toán</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-[#1E293B]">{{ $order->order_code }}</td>
                                        <td class="px-4 py-4 text-sm text-[#64748B]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 text-sm text-[#64748B]">
                                            {{ $order->details->count() }} sản phẩm
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-[#1E293B] text-right">
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
                                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-[#8B5A2B] bg-amber-100 rounded-full hover:bg-amber-200">
                                                Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-[#64748B]">
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
</x-customer-account-layout>
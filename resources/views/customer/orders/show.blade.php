<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chi tiết đơn hàng {{ $order->order_code }}</h2>
            <a href="{{ route('customer.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Quay lại danh sách</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thông tin nhận hàng</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-gray-500">Người nhận</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->recipient_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Số điện thoại</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->recipient_phone }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500">Địa chỉ</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->recipient_address }}</dd>
                                </div>
                                @if ($order->note)
                                    <div class="sm:col-span-2">
                                        <dt class="text-gray-500">Ghi chú</dt>
                                        <dd class="font-medium text-gray-900">{{ $order->note }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sản phẩm đã đặt</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($order->details as $detail)
                                            <tr>
                                                <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $detail->product_name }}</td>
                                                <td class="px-4 py-4 text-sm text-gray-700 text-right">{{ number_format($detail->product_price, 0, ',', '.') }} đ</td>
                                                <td class="px-4 py-4 text-sm text-gray-700 text-right">{{ $detail->quantity }}</td>
                                                <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">{{ number_format($detail->line_total, 0, ',', '.') }} đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <dl class="mt-6 space-y-2 text-sm border-t border-gray-200 pt-4">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Tạm tính</dt>
                                    <dd class="font-medium text-gray-900">{{ number_format($order->subtotal, 0, ',', '.') }} đ</dd>
                                </div>
                                @if ($order->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Giảm giá {{ $order->voucher?->code ? "({$order->voucher->code})" : '' }}</dt>
                                        <dd class="font-medium text-red-600">-{{ number_format($order->discount_amount, 0, ',', '.') }} đ</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Phí vận chuyển</dt>
                                    <dd class="font-medium text-gray-900">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</dd>
                                </div>
                                <div class="flex justify-between text-base pt-2 border-t border-gray-200">
                                    <dt class="font-semibold text-gray-900">Tổng cộng</dt>
                                    <dd class="font-bold text-gray-900">{{ number_format($order->total_amount, 0, ',', '.') }} đ</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Thanh toán</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-gray-500">Phương thức</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ match ($order->payment_method) {
                                            'COD' => 'Thanh toán khi nhận hàng (COD)',
                                            'BANK_TRANSFER' => 'Chuyển khoản ngân hàng',
                                            'E_WALLET' => 'Ví điện tử',
                                            'CARD' => 'Thẻ thanh toán',
                                            default => $order->payment_method,
                                        } }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Trạng thái thanh toán</dt>
                                    <dd><x-payment-status-badge :status="$order->payment_status" /></dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Trạng thái đơn hàng</h3>
                                <x-order-status-badge :status="$order->order_status" />
                            </div>
                            <ol class="relative border-l border-gray-200 ml-3 space-y-6">
                                @forelse ($order->statusHistories->sortBy('changed_at') as $history)
                                    <li class="ml-6">
                                        <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white {{ $loop->first ? 'bg-green-500' : 'bg-gray-200' }}"></span>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $history->to_status ? match ($history->to_status) {
                                                'PENDING' => 'Đơn hàng được tạo',
                                                'CONFIRMED' => 'Đã xác nhận đơn hàng',
                                                'PREPARING' => 'Đang đóng gói',
                                                'SHIPPING' => 'Đang giao hàng',
                                                'COMPLETED' => 'Giao hàng thành công',
                                                'CANCELLED' => 'Đơn hàng đã hủy',
                                                default => $history->to_status,
                                            } : '' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $history->changed_at->format('d/m/Y H:i') }}</p>
                                        @if ($history->note)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $history->note }}</p>
                                        @endif
                                    </li>
                                @empty
                                    <li class="ml-6 text-sm text-gray-500">Chưa có cập nhật nào.</li>
                                @endforelse
                            </ol>

                            @if ($order->order_status === 'PENDING')
                                <div class="mt-6" x-data="{ open: false }">
                                    <button @click="open = true"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100">
                                        Hủy đơn hàng
                                    </button>

                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>
                                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="px-6 py-4">
                                                    <h3 class="text-lg font-medium text-gray-900">Xác nhận hủy đơn hàng</h3>
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        Bạn có chắc chắn muốn hủy đơn hàng <span class="font-medium text-gray-900">{{ $order->order_code }}</span>? Hành động này không thể hoàn tác.
                                                    </p>
                                                </div>
                                                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                                                    <button @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Đóng</button>
                                                    <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Xác nhận hủy</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($order->cancel_reason)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-red-800 mb-1">Lý do hủy đơn</h4>
                            <p class="text-sm text-red-700">{{ $order->cancel_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
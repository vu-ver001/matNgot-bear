<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Xử lý đơn hàng {{ $order->order_code }}</h2>
            <a href="{{ route('staff.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Quay lại danh sách</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Thông tin đơn hàng</h3>
                                <div class="flex gap-2">
                                    <x-order-status-badge :status="$order->order_status" />
                                    <x-payment-status-badge :status="$order->payment_status" />
                                </div>
                            </div>
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
                                <div>
                                    <dt class="text-gray-500">Phương thức thanh toán</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->payment_method }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Ngày đặt</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                @if ($order->note)
                                    <div class="sm:col-span-2">
                                        <dt class="text-gray-500">Ghi chú của khách</dt>
                                        <dd class="font-medium text-gray-900">{{ $order->note }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sản phẩm</h3>
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
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Giao dịch thanh toán</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phương thức</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Số tiền</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã GD</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Xác nhận</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($order->payments as $payment)
                                            <tr>
                                                <td class="px-4 py-4 text-sm text-gray-700">{{ $payment->method }}</td>
                                                <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                                                <td class="px-4 py-4"><x-payment-status-badge :status="$payment->status" /></td>
                                                <td class="px-4 py-4 text-sm text-gray-500">{{ $payment->transaction_ref ?? '—' }}</td>
                                                <td class="px-4 py-4 text-sm text-gray-500">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="px-4 py-4 text-right">
                                                    @if ($payment->status === 'PENDING')
                                                        <div class="flex justify-end gap-2">
                                                            <form method="POST" action="{{ route('staff.payments.updateStatus', $payment) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="PAID">
                                                                <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Đã nhận</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('staff.payments.updateStatus', $payment) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="FAILED">
                                                                <button class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Thất bại</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Chưa có giao dịch thanh toán nào.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cập nhật trạng thái</h3>
                            @if (in_array($order->order_status, ['COMPLETED', 'CANCELLED']))
                                <p class="text-sm text-gray-500">Đơn hàng đã ở trạng thái kết thúc, không thể thay đổi.</p>
                            @else
                                <form method="POST" action="{{ route('staff.orders.updateStatus', $order) }}" x-data="{ status: '{{ $order->order_status }}' }">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái mới</label>
                                        <select name="order_status" x-model="status"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            @foreach (['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'PREPARING' => 'Đang đóng gói', 'SHIPPING' => 'Đang giao', 'COMPLETED' => 'Hoàn thành', 'CANCELLED' => 'Hủy đơn'] as $value => $label)
                                                <option value="{{ $value }}" @selected($order->order_status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div x-show="status === 'CANCELLED'" x-cloak class="mt-3" style="display: none;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Lý do hủy <span class="text-red-600">*</span></label>
                                        <textarea name="cancel_reason" rows="3" placeholder="Nhập lý do hủy đơn..."
                                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                                    </div>
                                    <button type="submit" class="mt-4 w-full px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800">
                                        Lưu thay đổi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lịch sử trạng thái</h3>
                            <ol class="relative border-l border-gray-200 ml-3 space-y-6">
                                @forelse ($order->statusHistories->sortBy('changed_at') as $history)
                                    <li class="ml-6">
                                        <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white {{ $loop->first ? 'bg-green-500' : 'bg-gray-200' }}"></span>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $history->from_status ? "{$history->from_status} → " : '' }}{{ $history->to_status }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $history->changed_at->format('d/m/Y H:i') }}
                                            {{ $history->changedByUser ? '• ' . $history->changedByUser->full_name : '' }}
                                        </p>
                                        @if ($history->note)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $history->note }}</p>
                                        @endif
                                    </li>
                                @empty
                                    <li class="ml-6 text-sm text-gray-500">Chưa có cập nhật nào.</li>
                                @endforelse
                            </ol>
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
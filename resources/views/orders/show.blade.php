<div class="orders-ui">
{{-- Shared order detail. $isStaff selects presentation only; controllers enforce access. --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="font-extrabold text-xl text-[#4E342E]">Chi tiết đơn hàng <span class="text-[#E08A1E] font-mono">{{ $order->order_code }}</span></h2>
        <p class="text-sm text-[#795548] mt-1">Đặt lúc: {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-amber-50 text-[#8C4A19] font-bold text-xs sm:text-sm rounded-xl border border-amber-300 shadow-xs transition transform hover:-translate-y-0.5">
            <i class="fa-solid fa-file-invoice text-sm text-[#E08A1E]"></i>
            <span>Xem hóa đơn điện tử</span>
        </a>
        <a href="{{ route($routePrefix.'.index') }}" class="text-sm font-semibold text-[#8C4A19] hover:text-[#5C3219] flex items-center gap-1">
            <span>← Quay lại danh sách</span>
        </a>
    </div>
</div>
@include('orders.partials.alerts')

@unless ($isStaff)
    @include('customer.orders.partials.detail-actions')
@endunless

<div class="panel-card">
    <h3 class="panel-title mb-4">Tiến trình đơn hàng</h3>
    <x-order-timeline :status="$order->order_status" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="min-w-0 lg:col-span-2 space-y-6">
        <div class="panel-card mb-0" x-data="{ showEditAddressModal: false }">
            <div class="order-panel-body">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="panel-title">Thông tin nhận hàng</h3>
                    @if (! $isStaff && $order->order_status === 'PENDING')
                        <button type="button" @click="showEditAddressModal = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-xl transition cursor-pointer">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Đổi địa chỉ</span>
                        </button>
                    @endif
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-[#795548]">Người nhận</dt>
                        <dd class="font-medium text-[#4E342E]">{{ $order->recipient_name }}</dd>
                        @if ($isStaff && $order->customer)
                            <dd class="text-xs text-[#795548] mt-1">Tài khoản: {{ $order->customer->full_name }} ({{ $order->customer->email }})</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-[#795548]">Số điện thoại</dt>
                        <dd class="font-medium text-[#4E342E]">{{ $order->recipient_phone }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-[#795548]">Địa chỉ</dt>
                        <dd class="font-medium text-[#4E342E]">{{ $order->recipient_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-[#795548]">Hình thức giao hàng</dt>
                        <dd class="font-medium text-[#4E342E]">{{ $order->shipping_method_label }}</dd>
                    </div>
                    @if ($order->shipped_at)
                        <div>
                            <dt class="text-[#795548]">Bắt đầu giao</dt>
                            <dd class="font-medium text-[#4E342E]">{{ $order->shipped_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($order->note)
                        <div class="sm:col-span-2">
                            <dt class="text-[#795548]">Ghi chú</dt>
                            <dd class="font-medium text-[#4E342E]">{{ $order->note }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @unless ($isStaff)
                @include('customer.orders.partials.address-modal')
            @endunless
        </div>

        <div class="panel-card mb-0">
            <div class="order-panel-body">
                <h3 class="panel-title mb-4">Sản phẩm đã đặt</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-right">Đơn giá</th>
                                <th class="text-right">Số lượng</th>
                                <th class="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->details as $detail)
                                @php
                                    $rawImg = $detail->product?->images?->where('is_primary', true)->first()?->image_url
                                        ?? $detail->product?->images?->first()?->image_url;
                                    $primaryImg = $rawImg ? (str_starts_with($rawImg, 'http') ? $rawImg : asset($rawImg)) : '';
                                @endphp
                                <tr>
                                    <td class="px-4 py-4 text-sm font-medium text-[#4E342E]">
                                        <div class="flex items-center gap-3">
                                            @if ($primaryImg)
                                                <img src="{{ $primaryImg }}"
                                                     alt="{{ $detail->product_name }}"
                                                     class="w-12 h-12 object-cover rounded-xl border border-amber-200/70 bg-white shrink-0 shadow-2xs"
                                                     onerror="this.src='https://placehold.co/100x100/f5e6ca/7c4a2d?text=Bear'">
                                            @else
                                                <div class="w-12 h-12 rounded-xl border border-amber-200/70 bg-amber-100/70 text-amber-800 font-bold flex items-center justify-center shrink-0 text-xl shadow-2xs">
                                                    🧸
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="font-bold text-[#4E342E] leading-snug">{{ $detail->product_name }}</div>
                                                @if ($isStaff && $detail->product)
                                                    <div class="text-xs text-[#795548]">Mã SP: #{{ $detail->product_id }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-[#795548] text-right">{{ number_format($detail->product_price, 0, ',', '.') }} đ</td>
                                    <td class="px-4 py-4 text-sm text-[#795548] text-right">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-4 text-sm font-medium text-[#4E342E] text-right">{{ number_format($detail->line_total, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <dl class="mt-6 space-y-2 text-sm border-t border-amber-100 pt-4">
                    <div class="flex justify-between">
                        <dt class="text-[#795548]">Tạm tính</dt>
                        <dd class="font-medium text-[#4E342E]">{{ number_format($order->subtotal, 0, ',', '.') }} đ</dd>
                    </div>
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between">
                            <dt class="text-[#795548]">Giảm giá {{ $order->voucher?->code ? "({$order->voucher->code})" : '' }}</dt>
                            <dd class="font-medium text-rose-600">-{{ number_format($order->discount_amount, 0, ',', '.') }} đ</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-[#795548]">Phí vận chuyển</dt>
                        <dd class="font-medium text-[#4E342E]">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</dd>
                    </div>
                    @if ($order->shipping_discount_amount > 0)
                        <div class="flex justify-between">
                            <dt class="text-[#795548]">Giảm phí vận chuyển {{ $order->shippingVoucher?->code ? "({$order->shippingVoucher->code})" : '' }}</dt>
                            <dd class="font-medium text-emerald-600">-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }} đ</dd>
                        </div>
                    @endif
                    <div class="flex justify-between text-base pt-2 border-t border-amber-100">
                        <dt class="font-semibold text-[#4E342E]">Tổng cộng</dt>
                        <dd class="font-bold text-amber-600">{{ number_format($order->total_amount, 0, ',', '.') }} đ</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="panel-card mb-0">
            <div class="order-panel-body">
                <h3 class="panel-title mb-4">Thanh toán</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-[#795548]">Phương thức</dt>
                        <dd class="font-medium text-[#4E342E]">
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
                        <dt class="text-[#795548]">Trạng thái thanh toán</dt>
                        <dd><x-payment-status-badge :status="$order->payment_status" /></dd>
                    </div>
                </div>

                @if(! $isStaff && $order->canPayOnline())
                    <div class="mt-4 pt-4 border-t border-amber-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs text-amber-800 font-semibold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            Đang chờ thanh toán {{ number_format($order->total_amount, 0, ',', '.') }}đ
                        </span>
                        <a href="{{ route('customer.payment.qr', $order->id) }}"
                           class="w-full sm:w-auto px-4 py-2 bg-[#E08A1E] hover:bg-[#D17E17] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Thanh toán ngay (Quét QR / Ví / Thẻ)</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @if ($isStaff)
            @include('staff.orders.partials.payments')
        @endif
    </div>

    <div class="min-w-0 space-y-6">
        @if ($isStaff)
            @include('staff.orders.partials.status-form')
        @endif
        <div class="panel-card mb-0">
            <div class="order-panel-body">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="panel-title">Lịch sử trạng thái</h3>
                    <x-order-status-badge :status="$order->order_status" />
                </div>
                <ol class="relative border-l border-amber-200 ml-3 space-y-6">
                    @forelse ($order->statusHistories->sortBy('changed_at') as $history)
                        <li class="ml-6">
                            <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white {{ $loop->last ? 'bg-amber-500' : 'bg-amber-100' }}"></span>
                            <p class="text-sm {{ $loop->last ? 'font-bold text-[#2C1408]' : 'font-medium text-[#795548]' }}">
                                {{ $history->to_status ? match ($history->to_status) {
                                    'PENDING' => 'Đơn hàng được tạo',
                                    'CONFIRMED' => 'Đã xác nhận đơn hàng',
                                    'PREPARING' => 'Đang đóng gói',
                                    'SHIPPING' => 'Đang giao hàng',
                                    'COMPLETED' => 'Giao hàng thành công',
                                    'CANCELLED' => 'Đơn hàng đã hủy',
                                    'RETURNED' => 'Đã trả hàng',
                                    default => $history->to_status,
                                } : '' }}
                            </p>
                            <p class="text-xs text-[#795548] mt-0.5">{{ $history->changed_at->format('d/m/Y H:i') }}
                                @if ($isStaff && $history->changedByUser)
                                    • {{ $history->changedByUser->full_name }}
                                @endif</p>
                            @if ($history->note)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $history->note }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="ml-6 text-sm text-[#795548]">Chưa có cập nhật nào.</li>
                    @endforelse
                </ol>

                @unless ($isStaff)
                    @include('customer.orders.partials.cancel-action')
                @endunless
            </div>
        </div>

        @if ($order->cancel_reason)
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                <h4 class="text-sm font-semibold text-rose-800 mb-1">Lý do hủy đơn</h4>
                <p class="text-sm text-rose-700">{{ $order->cancel_reason }}</p>
            </div>
        @endif

        @unless ($isStaff)
            @include('customer.orders.partials.quick-navigation')
        @endunless
    </div>
</div>

</div>

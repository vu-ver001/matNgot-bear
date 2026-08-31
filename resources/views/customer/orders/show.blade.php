<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="font-bold text-2xl text-[#2B1810] tracking-tight">Chi tiết đơn hàng <span class="text-[#E08A1E] font-mono">{{ $order->order_code }}</span></h2>
                <a href="{{ route('customer.orders.index') }}" class="text-sm font-semibold text-[#8C4A19] hover:text-[#5C3219] flex items-center gap-1">
                    <span>← Quay lại danh sách</span>
                </a>
            </div>
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

            @php
                $canPayOnline = in_array($order->payment_method, ['BANK_TRANSFER', 'CARD', 'E_WALLET']) 
                    && in_array($order->payment_status, ['UNPAID', 'FAILED']) 
                    && $order->order_status !== 'CANCELLED';
            @endphp

            @if($canPayOnline)
                <div class="mb-6 bg-gradient-to-r from-amber-500/10 via-amber-500/20 to-amber-500/10 border-2 border-amber-400 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm"
                     x-data="{ showChangeModal: false }">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-amber-500/30">
                            💳
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-base font-extrabold text-[#2B1810]">Đơn hàng này chưa hoàn tất thanh toán</h4>
                                <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-bold">
                                    @if($order->payment_method === 'CARD') VNPAY @elseif($order->payment_method === 'E_WALLET') Ví MoMo @else VietQR @endif
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#7D6B5D] mt-0.5">Số tiền cần thanh toán: <strong class="text-amber-700 font-bold text-base">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>. Vui lòng thanh toán hoặc đổi phương thức phù hợp!</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto shrink-0">
                        <a href="{{ $order->payment_method === 'CARD' ? route('customer.payment.vnpay.redirect', $order) : ($order->payment_method === 'E_WALLET' ? route('customer.payment.momo.redirect', $order) : route('customer.payment.qr', $order)) }}" 
                           class="flex-1 sm:flex-initial px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>THANH TOÁN NGAY</span>
                        </a>

                        <button type="button" @click="showChangeModal = true"
                                class="flex-1 sm:flex-initial px-4 py-2.5 bg-white hover:bg-amber-50 text-[#5C3219] font-bold text-xs sm:text-sm rounded-xl border border-amber-300 shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-arrow-right-arrow-left text-xs"></i>
                            <span>Đổi hình thức</span>
                        </button>
                    </div>

                    {{-- Modal đổi phương thức thanh toán --}}
                    <div x-show="showChangeModal" 
                         x-cloak 
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs text-left"
                         @click.self="showChangeModal = false"
                         @keydown.escape.window="showChangeModal = false">
                        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-amber-200 space-y-4"
                             @click.stop>
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div>
                                    <h3 class="font-bold text-base text-[#2C1408]">Đổi phương thức thanh toán</h3>
                                    <p class="text-xs text-[#786B61]">Đơn hàng: <strong class="text-amber-800">#{{ $order->order_code }}</strong> ({{ number_format($order->total_amount, 0, ',', '.') }}đ)</p>
                                </div>
                                <button type="button" @click="showChangeModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>

                            <form action="{{ route('customer.payment.retry', $order->id) }}" method="POST" class="space-y-2.5">
                                @csrf
                                
                                <button type="submit" name="payment_method" value="BANK_TRANSFER"
                                        class="w-full p-3 rounded-xl border-2 {{ $order->payment_method === 'BANK_TRANSFER' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200 hover:border-emerald-300 bg-white' }} flex items-center justify-between text-left transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            🏦
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Chuyển khoản VietQR (MB Bank)</div>
                                            <div class="text-[11px] text-[#786B61]">Quét mã QR qua app ngân hàng 24/7</div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                </button>

                                <button type="submit" name="payment_method" value="VNPAY"
                                        class="w-full p-3 rounded-xl border-2 {{ $order->payment_method === 'CARD' ? 'border-blue-500 bg-blue-50/50' : 'border-gray-200 hover:border-blue-300 bg-white' }} flex items-center justify-between text-left transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            💳
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Cổng thanh toán VNPAY</div>
                                            <div class="text-[11px] text-[#786B61]">Thẻ ATM nội địa, Visa, Mastercard, VNPAY-QR</div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                </button>

                                <button type="submit" name="payment_method" value="MOMO"
                                        class="w-full p-3 rounded-xl border-2 {{ $order->payment_method === 'E_WALLET' ? 'border-pink-500 bg-pink-50/50' : 'border-gray-200 hover:border-pink-300 bg-white' }} flex items-center justify-between text-left transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-pink-100 text-pink-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            👛
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Ví điện tử MoMo</div>
                                            <div class="text-[11px] text-[#786B61]">Thanh toán nhanh qua App MoMo</div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                </button>

                                <button type="submit" name="payment_method" value="COD"
                                        class="w-full p-3 rounded-xl border-2 {{ $order->payment_method === 'COD' ? 'border-amber-500 bg-amber-50/50' : 'border-gray-200 hover:border-amber-300 bg-white' }} flex items-center justify-between text-left transition cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            💵
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Thanh toán khi nhận hàng (COD)</div>
                                            <div class="text-[11px] text-[#786B61]">Nhận hàng và thanh toán tiền mặt cho bưu tá</div>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Delivery Confirmation Banner when order is SHIPPING --}}
            @if($order->order_status === 'SHIPPING')
                <div class="mb-6 bg-gradient-to-r from-emerald-500/15 via-teal-500/20 to-emerald-500/15 border-2 border-emerald-400 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-emerald-600/30">
                            🚚
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-[#1B4332]">Đơn hàng đang trên đường giao đến bạn</h4>
                            <p class="text-xs sm:text-sm text-[#2D6A4F] mt-0.5">Khi đã nhận được kiện hàng gấu bông đầy đủ, quý khách vui lòng bấm nút xác nhận bên dưới để hoàn tất đơn hàng!</p>
                        </div>
                    </div>
                    
                    <form action="{{ route('customer.orders.complete', $order->id) }}" method="POST" class="w-full sm:w-auto shrink-0" onsubmit="return confirm('Bạn đã nhận được sản phẩm đầy đủ và muốn xác nhận hoàn tất đơn hàng #{{ $order->order_code }}?')">
                        @csrf
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>ĐÃ NHẬN ĐƯỢC HÀNG</span>
                        </button>
                    </form>
                </div>
            @endif

            <div class="mb-6 bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Tiến trình đơn hàng</h3>
                <x-order-timeline :status="$order->order_status" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Thông tin nhận hàng</h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-[#64748B]">Người nhận</dt>
                                    <dd class="font-medium text-[#1E293B]">{{ $order->recipient_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[#64748B]">Số điện thoại</dt>
                                    <dd class="font-medium text-[#1E293B]">{{ $order->recipient_phone }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[#64748B]">Địa chỉ</dt>
                                    <dd class="font-medium text-[#1E293B]">{{ $order->recipient_address }}</dd>
                                </div>
                                @if ($order->note)
                                    <div class="sm:col-span-2">
                                        <dt class="text-[#64748B]">Ghi chú</dt>
                                        <dd class="font-medium text-[#1E293B]">{{ $order->note }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Sản phẩm đã đặt</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-amber-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Sản phẩm</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Đơn giá</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Số lượng</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($order->details as $detail)
                                            <tr>
                                                <td class="px-4 py-4 text-sm font-medium text-[#1E293B]">{{ $detail->product_name }}</td>
                                                <td class="px-4 py-4 text-sm text-[#64748B] text-right">{{ number_format($detail->product_price, 0, ',', '.') }} đ</td>
                                                <td class="px-4 py-4 text-sm text-[#64748B] text-right">{{ $detail->quantity }}</td>
                                                <td class="px-4 py-4 text-sm font-medium text-[#1E293B] text-right">{{ number_format($detail->line_total, 0, ',', '.') }} đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <dl class="mt-6 space-y-2 text-sm border-t border-amber-100 pt-4">
                                <div class="flex justify-between">
                                    <dt class="text-[#64748B]">Tạm tính</dt>
                                    <dd class="font-medium text-[#1E293B]">{{ number_format($order->subtotal, 0, ',', '.') }} đ</dd>
                                </div>
                                @if ($order->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <dt class="text-[#64748B]">Giảm giá {{ $order->voucher?->code ? "({$order->voucher->code})" : '' }}</dt>
                                        <dd class="font-medium text-rose-600">-{{ number_format($order->discount_amount, 0, ',', '.') }} đ</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-[#64748B]">Phí vận chuyển</dt>
                                    <dd class="font-medium text-[#1E293B]">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</dd>
                                </div>
                                <div class="flex justify-between text-base pt-2 border-t border-amber-100">
                                    <dt class="font-semibold text-[#1E293B]">Tổng cộng</dt>
                                    <dd class="font-bold text-amber-600">{{ number_format($order->total_amount, 0, ',', '.') }} đ</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Thanh toán</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-[#64748B]">Phương thức</dt>
                                    <dd class="font-medium text-[#1E293B]">
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
                                    <dt class="text-[#64748B]">Trạng thái thanh toán</dt>
                                    <dd><x-payment-status-badge :status="$order->payment_status" /></dd>
                                </div>
                            </div>

                            @if($order->payment_status === 'UNPAID' && $order->order_status !== 'CANCELLED')
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
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-[#1E293B]">Lịch sử trạng thái</h3>
                                <x-order-status-badge :status="$order->order_status" />
                            </div>
                            <ol class="relative border-l border-amber-200 ml-3 space-y-6">
                                @forelse ($order->statusHistories->sortBy('changed_at') as $history)
                                    <li class="ml-6">
                                        <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white {{ $loop->first ? 'bg-amber-500' : 'bg-amber-100' }}"></span>
                                        <p class="text-sm font-medium text-[#1E293B]">
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
                                        <p class="text-xs text-[#64748B] mt-0.5">{{ $history->changed_at->format('d/m/Y H:i') }}</p>
                                        @if ($history->note)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $history->note }}</p>
                                        @endif
                                    </li>
                                @empty
                                    <li class="ml-6 text-sm text-[#64748B]">Chưa có cập nhật nào.</li>
                                @endforelse
                            </ol>

                            @if ($order->order_status === 'PENDING')
                                <div class="mt-6" x-data="{ open: false }">
                                    <button @click="open = true"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100">
                                        Hủy đơn hàng
                                    </button>

                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>
                                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="px-6 py-4">
                                                    <h3 class="text-lg font-medium text-[#1E293B]">Xác nhận hủy đơn hàng</h3>
                                                    <p class="mt-2 text-sm text-[#64748B]">
                                                        Bạn có chắc chắn muốn hủy đơn hàng <span class="font-medium text-[#1E293B]">{{ $order->order_code }}</span>? Hành động này không thể hoàn tác.
                                                    </p>
                                                </div>
                                                <div class="bg-amber-50 px-6 py-3 flex justify-end gap-3">
                                                    <button @click="open = false" class="px-4 py-2 text-sm font-medium text-[#64748B] bg-white border border-amber-200 rounded-full hover:bg-amber-50">Đóng</button>
                                                    <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                                                        @csrf
                                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-full hover:bg-rose-700">Xác nhận hủy</button>
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
                        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                            <h4 class="text-sm font-semibold text-rose-800 mb-1">Lý do hủy đơn</h4>
                            <p class="text-sm text-rose-700">{{ $order->cancel_reason }}</p>
                        </div>
                    @endif

                    {{-- Quick Navigation Card --}}
                    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 space-y-3">
                        <h4 class="text-sm font-bold text-[#1E293B] mb-2 flex items-center gap-2">
                            <span>🧸</span>
                            <span>Mật Ngọt Bear</span>
                        </h4>
                        @if($order->payment_status === 'UNPAID' && $order->order_status !== 'CANCELLED')
                            <a href="{{ route('customer.payment.qr', $order->id) }}" 
                               class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-md shadow-emerald-600/25 uppercase tracking-wide">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>Thanh toán đơn hàng này</span>
                            </a>
                        @endif
                        <a href="{{ route('home') }}" 
                           class="w-full bg-[#E08A1E] hover:bg-[#D17E17] text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Tiếp tục mua sắm</span>
                        </a>
                        <a href="{{ route('customer.cart') }}" 
                           class="w-full bg-[#FAF6EE] hover:bg-[#F2DECA] text-[#8C4A19] font-bold py-2.5 px-4 rounded-xl border border-[#F2DECA] text-xs flex items-center justify-center gap-2 transition">
                            <svg class="w-4 h-4 text-[#E08A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Xem giỏ hàng</span>
                        </a>
                        <a href="{{ route('customer.orders.index') }}" 
                           class="w-full text-center text-xs text-gray-500 hover:text-[#8C4A19] block py-1 font-medium underline">
                            ← Xem tất cả đơn hàng đã mua
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
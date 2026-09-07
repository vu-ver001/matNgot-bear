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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap min-w-[140px]">Trạng thái</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap min-w-[150px]">Thanh toán</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-[#1E293B] whitespace-nowrap">{{ $order->order_code }}</td>
                                        <td class="px-4 py-4 text-sm text-[#64748B] whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 text-sm text-[#64748B] whitespace-nowrap">
                                            {{ $order->details->count() }} sản phẩm
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-[#1E293B] text-right whitespace-nowrap">
                                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <x-order-status-badge :status="$order->order_status" :cancel-request-status="$order->cancel_request_status" />
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <x-payment-status-badge :status="$order->payment_status" />
                                        </td>
                                        <td class="px-4 py-4 text-right space-x-1.5 whitespace-nowrap" x-data="{ openChangePayment: false, showNotReceived: false }">
                                            @php
                                                $canPayOnline = in_array($order->payment_method, ['BANK_TRANSFER', 'CARD', 'E_WALLET']) 
                                                    && in_array($order->payment_status, ['UNPAID', 'FAILED']) 
                                                    && $order->order_status !== 'CANCELLED';
                                            @endphp

                                            @if ($canPayOnline)
                                                {{-- Nút Thanh toán (Mở modal chọn tiếp tục hoặc đổi PTTT) --}}
                                                <button type="button" 
                                                        @click="openChangePayment = true"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 rounded-full hover:from-emerald-700 hover:to-teal-800 shadow-xs transition cursor-pointer">
                                                    <i class="fa-solid fa-credit-card text-[10px]"></i>
                                                    <span>Thanh toán</span>
                                                </button>

                                                {{-- Modal lựa chọn thanh toán --}}
                                                <div x-show="openChangePayment" 
                                                     x-cloak 
                                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs text-left"
                                                     @click.self="openChangePayment = false"
                                                     @keydown.escape.window="openChangePayment = false"
                                                     style="display: none;">
                                                    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-amber-200 space-y-4"
                                                         @click.stop>
                                                        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
                                                            <div>
                                                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#8C7A6B]">Thanh Toán Đơn Hàng</span>
                                                                <h3 class="font-black text-base sm:text-lg text-[#2C1408] mt-0.5">
                                                                    Đơn hàng <span class="text-[#E08A1E] font-mono">#{{ $order->order_code }}</span>
                                                                </h3>
                                                            </div>
                                                            <button type="button" @click="openChangePayment = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition">
                                                                <i class="fa-solid fa-xmark text-sm"></i>
                                                            </button>
                                                        </div>

                                                        {{-- Order Summary Pill --}}
                                                        <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between">
                                                            <span class="text-xs font-semibold text-[#5C3219]">Tổng tiền:</span>
                                                            <span class="text-base sm:text-lg font-black text-amber-700">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                                                        </div>

                                                        @php
                                                            $currentPayUrl = match ($order->payment_method) {
                                                                'CARD' => route('customer.payment.vnpay.redirect', $order),
                                                                'E_WALLET' => route('customer.payment.momo.redirect', $order),
                                                                default => route('customer.payment.qr', $order),
                                                            };
                                                            $currentMethodInfo = match ($order->payment_method) {
                                                                'CARD' => ['name' => 'Cổng thanh toán VNPAY', 'desc' => 'Thẻ ATM nội địa, Visa, Mastercard, VNPAY-QR', 'icon' => '💳'],
                                                                'E_WALLET' => ['name' => 'Ví điện tử MoMo', 'desc' => 'Thanh toán tức thì qua App MoMo', 'icon' => '👛'],
                                                                default => ['name' => 'Chuyển khoản VietQR (MB Bank)', 'desc' => 'Quét mã QR qua mọi app ngân hàng 24/7', 'icon' => '🏦'],
                                                            };
                                                        @endphp

                                                        {{-- Tùy chọn 1: Tiếp tục với phương thức đã chọn --}}
                                                        <div class="space-y-2">
                                                            <div class="flex items-center justify-between text-xs">
                                                                <span class="font-extrabold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                                                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                                    <span>Tiếp tục với phương thức đã chọn:</span>
                                                                </span>
                                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">ĐÃ CHỌN</span>
                                                            </div>
                                                            
                                                            <a href="{{ $currentPayUrl }}"
                                                               class="w-full p-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white shadow-md shadow-emerald-600/25 transition transform hover:-translate-y-0.5 flex items-center justify-between group cursor-pointer">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center text-lg shrink-0">
                                                                        {{ $currentMethodInfo['icon'] }}
                                                                    </div>
                                                                    <div class="text-left">
                                                                        <div class="font-extrabold text-xs sm:text-sm">
                                                                            <span>{{ $currentMethodInfo['name'] }}</span>
                                                                        </div>
                                                                        <div class="text-[11px] text-white/85 mt-0.5">{{ $currentMethodInfo['desc'] }}</div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-1 font-bold text-xs bg-white/20 px-2.5 py-1 rounded-lg group-hover:bg-white/30 transition shrink-0 ml-2">
                                                                    <span>Tiếp tục</span>
                                                                    <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition"></i>
                                                                </div>
                                                            </a>
                                                        </div>

                                                        {{-- Divider --}}
                                                        <div class="relative flex py-1 items-center">
                                                            <div class="flex-grow border-t border-gray-200"></div>
                                                            <span class="flex-shrink mx-3 text-[11px] font-bold text-[#8C7A6B] uppercase tracking-wider bg-white px-2">Hoặc đổi phương thức khác</span>
                                                            <div class="flex-grow border-t border-gray-200"></div>
                                                        </div>

                                                        {{-- Tùy chọn 2: Đổi sang phương thức khác --}}
                                                        <form action="{{ route('customer.payment.retry', $order->id) }}" method="POST" class="space-y-2">
                                                            @csrf
                                                            
                                                            @if ($order->payment_method !== 'BANK_TRANSFER')
                                                                <button type="submit" name="payment_method" value="BANK_TRANSFER"
                                                                        class="w-full p-2.5 rounded-2xl border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition">
                                                                            🏦
                                                                        </div>
                                                                        <div>
                                                                            <div class="font-bold text-xs text-[#2C1408]">Đổi sang Chuyển khoản VietQR</div>
                                                                            <div class="text-[10px] text-[#786B61]">Quét mã QR qua mọi app ngân hàng 24/7</div>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-100/60 px-2 py-0.5 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition">Chọn</span>
                                                                </button>
                                                            @endif

                                                            @if ($order->payment_method !== 'CARD')
                                                                <button type="submit" name="payment_method" value="VNPAY"
                                                                        class="w-full p-2.5 rounded-2xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition">
                                                                            💳
                                                                        </div>
                                                                        <div>
                                                                            <div class="font-bold text-xs text-[#2C1408]">Đổi sang Cổng VNPAY</div>
                                                                            <div class="text-[10px] text-[#786B61]">Thẻ ATM nội địa, Visa, Mastercard</div>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-xs font-bold text-blue-700 bg-blue-100/60 px-2 py-0.5 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition">Chọn</span>
                                                                </button>
                                                            @endif

                                                            @if ($order->payment_method !== 'E_WALLET')
                                                                <button type="submit" name="payment_method" value="MOMO"
                                                                        class="w-full p-2.5 rounded-2xl border border-gray-200 hover:border-pink-500 hover:bg-pink-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-8 h-8 rounded-xl bg-pink-100 text-pink-800 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition">
                                                                            👛
                                                                        </div>
                                                                        <div>
                                                                            <div class="font-bold text-xs text-[#2C1408]">Đổi sang Ví điện tử MoMo</div>
                                                                            <div class="text-[10px] text-[#786B61]">Thanh toán nhanh qua App MoMo</div>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-xs font-bold text-pink-700 bg-pink-100/60 px-2 py-0.5 rounded-lg group-hover:bg-pink-600 group-hover:text-white transition">Chọn</span>
                                                                </button>
                                                            @endif

                                                            @if ($order->payment_method !== 'COD')
                                                                <button type="submit" name="payment_method" value="COD"
                                                                        class="w-full p-2.5 rounded-2xl border border-gray-200 hover:border-amber-500 hover:bg-amber-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                                                    <div class="flex items-center gap-2.5">
                                                                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition">
                                                                            💵
                                                                        </div>
                                                                        <div>
                                                                            <div class="font-bold text-xs text-[#2C1408]">Đổi sang Thanh toán COD</div>
                                                                            <div class="text-[10px] text-[#786B61]">Thanh toán tiền mặt khi nhận hàng</div>
                                                                        </div>
                                                                    </div>
                                                                    <span class="text-xs font-bold text-amber-800 bg-amber-100/60 px-2 py-0.5 rounded-lg group-hover:bg-amber-600 group-hover:text-white transition">Chọn</span>
                                                                </button>
                                                            @endif
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Nút Đã nhận hàng & Chưa nhận hàng (Khi đơn đang giao) --}}
                                            @if ($order->order_status === 'SHIPPING')
                                                <form action="{{ route('customer.orders.complete', $order->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn đã nhận được kiện hàng đầy đủ và muốn xác nhận hoàn tất đơn hàng #{{ $order->order_code }}?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 rounded-full hover:from-emerald-700 hover:to-teal-800 shadow-xs transition cursor-pointer"
                                                            title="Xác nhận đã nhận hàng">
                                                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                                                        <span>Đã nhận hàng</span>
                                                    </button>
                                                </form>

                                                <button type="button" @click="showNotReceived = true"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-full hover:bg-rose-100 transition cursor-pointer"
                                                        title="Chưa nhận được hàng">
                                                    <i class="fa-solid fa-circle-question text-rose-500 text-[10px]"></i>
                                                    <span>Chưa nhận</span>
                                                </button>

                                                {{-- Modal Chưa nhận hàng --}}
                                                <div x-show="showNotReceived" 
                                                     x-cloak 
                                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs text-left"
                                                     @click.self="showNotReceived = false"
                                                     @keydown.escape.window="showNotReceived = false"
                                                     style="display: none;">
                                                    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-rose-200 space-y-4"
                                                         @click.stop>
                                                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                                            <div class="flex items-center gap-2.5 text-rose-700">
                                                                <span class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center text-lg shrink-0">⚠️</span>
                                                                <div>
                                                                    <h3 class="font-black text-base text-[#2C1408]">Bạn chưa nhận được hàng?</h3>
                                                                    <p class="text-xs text-[#786B61]">Đơn hàng: <strong class="text-amber-800">#{{ $order->order_code }}</strong></p>
                                                                </div>
                                                            </div>
                                                            <button type="button" @click="showNotReceived = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition">
                                                                <i class="fa-solid fa-xmark text-sm"></i>
                                                            </button>
                                                        </div>

                                                        <div class="space-y-3 text-xs text-[#5C3219] leading-relaxed">
                                                            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200">
                                                                <div class="font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                                                                    <i class="fa-solid fa-lightbulb text-amber-600"></i>
                                                                    <span>Gợi ý kiểm tra nhanh:</span>
                                                                </div>
                                                                <ul class="list-disc list-inside space-y-1 text-[#786B61] text-[11px]">
                                                                    <li>Kiểm tra xem người thân, bạn cùng phòng, lễ tân hoặc bảo vệ đã nhận hộ chưa.</li>
                                                                    <li>Bưu tá có thể đang trên đường tới hoặc vừa gọi nhỡ cho bạn.</li>
                                                                </ul>
                                                            </div>

                                                            <p class="text-[#786B61]">
                                                                Nếu bạn vẫn chưa nhận được hàng hoặc bưu tá không liên hệ, đừng lo lắng! Đội ngũ <strong>Mật Ngọt Bear</strong> sẽ trực tiếp làm việc với bên vận chuyển để xử lý ngay cho bạn.
                                                            </p>
                                                        </div>

                                                        <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                                                            <a href="tel:0377466205" 
                                                               class="flex-1 py-3 px-4 rounded-xl bg-[#E08A1E] hover:bg-[#C2751D] text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-2">
                                                                <i class="fa-solid fa-phone"></i>
                                                                <span>Hotline: 0377.466.205</span>
                                                            </a>
                                                            <button type="button" @click="showNotReceived = false"
                                                                    class="py-3 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition">
                                                                Đã hiểu
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Nút Đánh giá sản phẩm khi đã hoàn tất --}}
                                            @if ($order->order_status === 'COMPLETED')
                                                <a href="{{ route('customer.orders.review', $order) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-amber-900 bg-amber-200 hover:bg-amber-300 rounded-full shadow-xs transition cursor-pointer"
                                                   title="Đánh giá sản phẩm">
                                                    <i class="fa-solid fa-star text-amber-700 text-[10px]"></i>
                                                    <span>Đánh giá</span>
                                                </a>
                                            @endif

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
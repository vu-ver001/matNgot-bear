<x-app-layout>
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
                                        <td class="px-4 py-4 text-right space-x-1.5 whitespace-nowrap" x-data="{ openChangePayment: false }">
                                            @php
                                                $canPayOnline = in_array($order->payment_method, ['BANK_TRANSFER', 'CARD', 'E_WALLET']) 
                                                    && in_array($order->payment_status, ['UNPAID', 'FAILED']) 
                                                    && $order->order_status !== 'CANCELLED';
                                            @endphp

                                            @if ($canPayOnline)
                                                {{-- Nút Thanh toán nhanh --}}
                                                <a href="{{ $order->payment_method === 'CARD' ? route('customer.payment.vnpay.redirect', $order) : ($order->payment_method === 'E_WALLET' ? route('customer.payment.momo.redirect', $order) : route('customer.payment.qr', $order)) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-700 rounded-full hover:from-emerald-700 hover:to-teal-800 shadow-xs transition">
                                                    <i class="fa-solid fa-credit-card text-[10px]"></i>
                                                    <span>Thanh toán</span>
                                                </a>

                                                {{-- Nút Đổi hình thức thanh toán --}}
                                                <button type="button" 
                                                        @click="openChangePayment = true"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-[#5C3219] bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-full transition cursor-pointer"
                                                        title="Đổi phương thức thanh toán">
                                                    <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>
                                                    <span>Đổi PTTT</span>
                                                </button>

                                                {{-- Modal đổi phương thức thanh toán --}}
                                                <div x-show="openChangePayment" 
                                                     x-cloak 
                                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs text-left"
                                                     @click.self="openChangePayment = false"
                                                     @keydown.escape.window="openChangePayment = false">
                                                    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-amber-200 space-y-4"
                                                         @click.stop>
                                                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                                            <div>
                                                                <h3 class="font-bold text-base text-[#2C1408]">Đổi phương thức thanh toán</h3>
                                                                <p class="text-xs text-[#786B61]">Đơn hàng: <strong class="text-amber-800">#{{ $order->order_code }}</strong> ({{ number_format($order->total_amount, 0, ',', '.') }}đ)</p>
                                                            </div>
                                                            <button type="button" @click="openChangePayment = false" class="text-gray-400 hover:text-gray-600 p-1">
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
                                            @endif

                                            {{-- Nút Đã nhận hàng (Khi đơn đang giao) --}}
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
</x-app-layout>
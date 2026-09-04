{{-- Modal đổi phương thức thanh toán --}}
<div x-show="{{ $modalState }}"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs text-left"
     @click.self="{{ $modalState }} = false"
     @keydown.escape.window="{{ $modalState }} = false">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-amber-200 space-y-4"
         @click.stop>
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="font-bold text-base text-[#2C1408]">Đổi phương thức thanh toán</h3>
                <p class="text-xs text-[#786B61]">Đơn hàng: <strong class="text-amber-800">#{{ $order->order_code }}</strong> ({{ number_format($order->total_amount, 0, ',', '.') }}đ)</p>
            </div>
            <button type="button" @click="{{ $modalState }} = false" class="text-gray-400 hover:text-gray-600 p-1">
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

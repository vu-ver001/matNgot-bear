@php
    $canPayOnline = $order->canPayOnline();
@endphp

@if($canPayOnline)
    <div class="mb-5 bg-gradient-to-r from-amber-500/10 via-amber-500/15 to-amber-500/10 border border-amber-300 rounded-2xl p-3.5 sm:px-5 sm:py-3.5 flex flex-col md:flex-row items-center justify-between gap-3 sm:gap-4 shadow-xs"
         x-data="{ showChangeModal: false }">
        <div class="flex items-center gap-3 sm:gap-4 w-full md:w-auto">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                💳
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h4 class="text-sm sm:text-base font-bold text-[#2B1810]">Đơn hàng này chưa hoàn tất thanh toán</h4>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-bold whitespace-nowrap">
                        @if($order->payment_method === 'CARD') VNPAY @elseif($order->payment_method === 'E_WALLET') Ví MoMo @else VietQR @endif
                    </span>
                </div>
                <p class="text-xs text-[#7D6B5D] mt-0.5">Số tiền cần thanh toán: <strong class="text-amber-700 font-bold text-sm">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>@if($order->paymentExpiresAt()) · Hạn thanh toán: <strong class="text-amber-800">{{ $order->paymentExpiresAt()->format('H:i - d/m/Y') }}</strong>@endif</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto shrink-0 justify-end">
            <a href="{{ $order->payment_method === 'CARD' ? route('customer.payment.vnpay.redirect', $order) : ($order->payment_method === 'E_WALLET' ? route('customer.payment.momo.redirect', $order) : route('customer.payment.qr', $order)) }}"
               class="flex-1 md:flex-initial whitespace-nowrap px-4 py-2 sm:px-5 sm:py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/25 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2">
                <i class="fa-solid fa-credit-card text-xs"></i>
                <span class="whitespace-nowrap">THANH TOÁN NGAY</span>
            </a>

            <button type="button" @click="showChangeModal = true"
                    class="flex-1 md:flex-initial whitespace-nowrap px-3.5 py-2 sm:px-4 sm:py-2.5 bg-white hover:bg-amber-50 text-[#5C3219] font-bold text-xs sm:text-sm rounded-xl border border-amber-300 shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-arrow-right-arrow-left text-xs"></i>
                <span class="whitespace-nowrap">Đổi hình thức</span>
            </button>
        </div>

        @include('customer.orders.partials.payment-method-modal', ['modalState' => 'showChangeModal'])
    </div>
@endif

{{-- Delivery Confirmation Banner when order is SHIPPING or COMPLETED waiting confirmation --}}
@if($order->hasPendingReturnRequest())
    <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-orange-50 to-amber-500/15 border-2 border-amber-300 rounded-2xl p-5 shadow-sm flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
            ⏳
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h4 class="text-sm sm:text-base font-bold text-[#2C1408]">Đang chờ Shop xử lý yêu cầu Trả hàng / Hoàn tiền</h4>
                <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px]">CHỜ DUYỆT TRẢ HÀNG</span>
            </div>
            <p class="text-xs text-[#7D6B5D] mt-1">
                Lý do: <em>"{{ $order->return_request_reason }}"</em>. Shop sẽ liên hệ với bạn trong thời gian sớm nhất.
            </p>
        </div>
    </div>
@elseif($order->isDeliveredWaitingConfirmation() || $order->order_status === 'SHIPPING')
    <div class="mb-6 bg-gradient-to-r from-emerald-500/15 via-teal-500/20 to-emerald-500/15 border-2 border-emerald-400 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm"
         x-data="{ openReturnModal: false }">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-emerald-600/30">
                📦
            </div>
            <div>
                <h4 class="text-base font-extrabold text-[#1B4332]">
                    {{ $order->order_status === 'COMPLETED' ? 'Đơn hàng đã được giao thành công đến bạn' : 'Đơn hàng đang trên đường giao đến bạn' }}
                </h4>
                <p class="text-xs sm:text-sm text-[#2D6A4F] mt-0.5">
                    Quý khách vui lòng kiểm tra kiện hàng và bấm xác nhận khi đã nhận được hàng đầy đủ!
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 justify-end flex-wrap sm:flex-nowrap">
            <form action="{{ route('customer.orders.confirm_received', $order->id) }}" method="POST" class="w-full sm:w-auto shrink-0">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>ĐÃ NHẬN ĐƯỢC HÀNG</span>
                </button>
            </form>

            @if($order->order_status === 'COMPLETED' && ! $order->isCustomerConfirmed())
                <button type="button" @click="openReturnModal = true"
                        class="w-full sm:w-auto px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 font-bold text-xs sm:text-sm rounded-xl border border-rose-200 hover:border-rose-300 shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-arrow-rotate-left text-rose-500"></i>
                    <span>TRẢ HÀNG / HOÀN TIỀN</span>
                </button>
                @include('customer.orders.partials.return-request-modal', ['order' => $order])
            @endif
        </div>
    </div>
@elseif($order->order_status === 'COMPLETED' && $order->isCustomerConfirmed())
    <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-[#FFF4DE] to-amber-500/15 border-2 border-amber-300 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-sm">
                🎉
            </div>
            <div>
                <h4 class="text-sm sm:text-base font-extrabold text-[#2C1408]">Đơn hàng đã hoàn tất thành công!</h4>
                <p class="text-xs text-[#786B61] mt-0.5">Cảm ơn bạn đã tin tưởng Mật Ngọt Bear. Hãy gửi đánh giá về sản phẩm nhé!</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 justify-end flex-wrap sm:flex-nowrap">
            <a href="{{ route('customer.orders.review', $order) }}" 
               data-open-order-review-modal
               data-order-id="{{ $order->id }}"
               class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-[#E08A1E] to-[#C2751D] hover:from-[#C2751D] hover:to-[#A35E14] text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                <i class="fa-solid fa-star text-amber-200"></i>
                <span>ĐÁNH GIÁ SẢN PHẨM</span>
            </a>

            <form action="{{ route('customer.orders.reorder', $order->id) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2.5 bg-white hover:bg-amber-50 text-[#8C4A19] font-bold text-xs sm:text-sm rounded-xl border border-amber-300 shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                    <span>MUA LẠI</span>
                </button>
            </form>
        </div>
    </div>
@endif

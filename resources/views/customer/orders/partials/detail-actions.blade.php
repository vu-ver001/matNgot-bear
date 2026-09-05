@php
    $canPayOnline = in_array($order->payment_method, ['BANK_TRANSFER', 'CARD', 'E_WALLET'])
        && in_array($order->payment_status, ['UNPAID', 'FAILED'])
        && $order->order_status !== 'CANCELLED';
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
                <p class="text-xs text-[#7D6B5D] mt-0.5">Số tiền cần thanh toán: <strong class="text-amber-700 font-bold text-sm">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>. Vui lòng thanh toán hoặc đổi phương thức phù hợp!</p>
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

{{-- Review Prompt Banner when order is COMPLETED --}}
@if($order->order_status === 'COMPLETED' && $order->details->isNotEmpty())
    @php
        $firstDetail = $order->details->first();
        $rawImg = $firstDetail?->product?->images?->where('is_primary', true)->first()?->image_url 
            ?? $firstDetail?->product?->images?->first()?->image_url;
        $firstImg = $rawImg ? (str_starts_with($rawImg, 'http') ? $rawImg : asset($rawImg)) : '';
        $hasAnyUnreviewed = $order->details->contains(function ($detail) use ($order) {
            return !($order->reviews?->where('product_id', $detail->product_id)->isNotEmpty() ?? false);
        });
    @endphp
    <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-500/15 border-2 border-amber-300/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div>
                <h4 class="text-base font-extrabold text-[#2C1408]">Đơn hàng đã hoàn tất thành công!</h4>
                <p class="text-xs sm:text-sm text-[#795548] mt-0.5">Cảm ơn bạn đã tin yêu Mật Ngọt Bear! Hãy để lại đánh giá về bé gấu để chúng mình phục vụ bạn tốt hơn nữa nhé.</p>
            </div>
        </div>

        @if($hasAnyUnreviewed)
            <button type="button"
                    class="btn-review-order w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-[#E08A1E] to-[#8C4A19] hover:from-[#C77815] hover:to-[#733C14] text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-amber-600/25 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer shrink-0"
                    data-order-id="{{ $order->id }}"
                    data-product-id="{{ $firstDetail->product_id }}"
                    data-product-name="{{ $firstDetail->product_name }}">
                <i class="fa-solid fa-star text-amber-200"></i>
                <span>Viết đánh giá</span>
            </button>
        @else
            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-300 shrink-0">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>Bạn đã đánh giá đơn hàng này</span>
            </span>
        @endif
    </div>
@endif

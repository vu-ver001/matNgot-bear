{{-- Quick Navigation Card --}}
<div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 space-y-3">
    <h4 class="text-sm font-bold text-[#1E293B] mb-2 flex items-center gap-2">
        <span>🧸</span>
        <span>Mật Ngọt Bear</span>
    </h4>
    @if(! $isStaff && $order->payment_status === 'UNPAID' && $order->order_status !== 'CANCELLED')
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
    <a href="{{ route($routePrefix.'.index') }}"
       class="w-full text-center text-xs text-gray-500 hover:text-[#8C4A19] block py-1 font-medium underline">
        ← Xem tất cả đơn hàng đã mua
    </a>
</div>

@extends('layouts.customer')

@section('title', 'Đặt hàng & Thanh toán thành công - Mật Ngọt Bear')

@section('content')
<div class="py-8 sm:py-12 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-32 font-sans" x-data="{
    copied: false,
    copyOrderCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        });
    }
}" x-init="
    // Push Notification Pop-up on Payment / Order Success
    @if($order->payment_status === 'PAID')
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '🎉 THANH TOÁN THÀNH CÔNG!',
                html: '<p class=\'text-sm text-gray-600 mt-2\'>Hệ thống đã tự động ghi nhận thanh toán cho đơn hàng <strong>#{{ $order->order_code }}</strong>.</p><p class=\'text-xs text-[#8C4A19] font-bold mt-1\'>Số tiền: {{ number_format($order->total_amount, 0, ',', '.') }}đ</p>',
                confirmButtonColor: '#5C3219',
                confirmButtonText: 'Tuyệt vời, cảm ơn!',
                timer: 5000,
                timerProgressBar: true
            });
        }
    @else
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '📦 ĐẶT HÀNG THÀNH CÔNG!',
                html: '<p class=\'text-sm text-gray-600 mt-2\'>Đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã được ghi nhận và đang chờ xử lý.</p>',
                confirmButtonColor: '#5C3219',
                confirmButtonText: 'Đã hiểu',
                timer: 4500,
                timerProgressBar: true
            });
        }
    @endif
">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="mb-6">
            <x-breadcrumb :items="[
                ['label' => 'Trang Chủ', 'url' => route('home')],
                ['label' => 'Giỏ Hàng', 'url' => route('customer.cart')],
                ['label' => 'Thanh Toán', 'url' => route('customer.checkout')],
                ['label' => 'Đặt Hàng Thành Công']
            ]" />
        </div>

        {{-- Success Celebration Card --}}
        <div class="bg-white rounded-3xl border border-[#F2DECA] shadow-xl overflow-hidden mb-8">
            
            {{-- Top Banner --}}
            <div class="bg-gradient-to-r from-[#2B1810] via-[#4A2818] to-[#2B1810] p-8 sm:p-10 text-center text-white relative overflow-hidden">
                {{-- Decorative Bear Watermark --}}
                <div class="absolute -right-8 -bottom-10 text-white/5 text-9xl font-black pointer-events-none select-none">
                    🧸
                </div>

                {{-- Badge & Confetti Icon --}}
                <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-gradient-to-br from-[#FFF0DC] to-[#FFE3BA] flex items-center justify-center text-4xl sm:text-5xl shadow-xl shadow-black/20 border-2 border-white/40 mb-4 animate-bounce">
                    🎉
                </div>

                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white mb-2">
                    @if($order->payment_status === 'PAID')
                        THANH TOÁN & ĐẶT HÀNG THÀNH CÔNG!
                    @else
                        ĐẶT HÀNG THÀNH CÔNG!
                    @endif
                </h1>
                <p class="text-sm sm:text-base text-[#F6D89B] max-w-xl mx-auto font-medium leading-relaxed">
                    Cảm ơn bạn đã tin tưởng và lựa chọn <strong class="text-white">Mật Ngọt Bear</strong>. Đơn hàng của bạn đang được xưởng chuẩn bị chu đáo và sẽ sớm gửi đến bạn!
                </p>

                {{-- Order Code Highlight Box --}}
                <div class="mt-6 inline-flex flex-wrap items-center justify-center gap-3 bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20">
                    <span class="text-xs uppercase tracking-wider text-white/80 font-semibold">Mã đơn hàng:</span>
                    <span class="font-mono font-black text-base sm:text-lg text-[#F6D89B] tracking-wider">{{ $order->order_code }}</span>
                    <button type="button" @click="copyOrderCode('{{ $order->order_code }}')"
                            class="ml-1 px-3 py-1 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <template x-if="!copied">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </template>
                        <template x-if="copied">
                            <svg class="w-3.5 h-3.5 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </template>
                        <span x-text="copied ? 'Đã chép!' : 'Sao chép'"></span>
                    </button>
                </div>
            </div>

            {{-- Status Quick Summary Bar --}}
            <div class="bg-[#FFFBF5] border-b border-[#F2DECA] px-6 py-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-xs">
                <div>
                    <div class="text-[#7D6B5D] font-medium">Thời gian đặt</div>
                    <div class="font-bold text-[#2B1810] mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-[#7D6B5D] font-medium">Phương thức thanh toán</div>
                    <div class="font-bold text-[#2B1810] mt-0.5">
                        @if($order->payment_method === 'COD')
                            💵 Thanh toán khi nhận (COD)
                        @elseif($order->payment_method === 'CARD')
                            💳 Cổng VNPAY / Thẻ Visa
                        @elseif($order->payment_method === 'E_WALLET')
                            👛 Ví điện tử MoMo
                        @else
                            🏦 Chuyển khoản VietQR
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-[#7D6B5D] font-medium">Trạng thái thanh toán</div>
                    <div class="mt-0.5">
                        @if($order->payment_status === 'PAID')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Đã thanh toán
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Chờ thanh toán
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-[#7D6B5D] font-medium">Trạng thái đơn hàng</div>
                    <div class="mt-0.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-blue-100 text-blue-800 border border-blue-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> {{ $order->order_status === 'PENDING' ? 'Chờ xác nhận' : 'Đã xác nhận' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Content Grid --}}
            <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Left Column: Recipient & Items (7 Cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    
                    {{-- Recipient Information --}}
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#F2DECA] p-5">
                        <h3 class="text-sm font-extrabold text-[#2B1810] uppercase tracking-wider mb-3.5 flex items-center gap-2">
                            <span class="text-base">📍</span>
                            <span>Thông tin người nhận</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                            <div>
                                <span class="text-[#7D6B5D]">Người nhận:</span>
                                <span class="font-bold text-[#2B1810] ml-1">{{ $order->recipient_name }}</span>
                            </div>
                            <div>
                                <span class="text-[#7D6B5D]">Số điện thoại:</span>
                                <span class="font-bold text-[#2B1810] ml-1">{{ $order->recipient_phone }}</span>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="text-[#7D6B5D]">Địa chỉ giao hàng:</span>
                                <span class="font-bold text-[#2B1810] ml-1">{{ $order->recipient_address }}</span>
                            </div>
                            @if($order->note)
                                <div class="sm:col-span-2 text-xs bg-[#FFF5E6] p-2.5 rounded-xl border border-[#F2DECA] text-[#5D4037]">
                                    <span class="font-bold">Ghi chú:</span> {{ $order->note }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Ordered Products List --}}
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#F2DECA] p-5">
                        <h3 class="text-sm font-extrabold text-[#2B1810] uppercase tracking-wider mb-3.5 flex items-center gap-2">
                            <span class="text-base">🧸</span>
                            <span>Sản phẩm đã đặt ({{ $order->details->sum('quantity') }} bé gấu)</span>
                        </h3>

                        <div class="divide-y divide-[#F2DECA]/60">
                            @foreach($order->details as $item)
                                @php
                                    $prodImage = $item->product?->images?->first()?->image_url 
                                        ?? 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80';
                                @endphp
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $prodImage }}" alt="{{ $item->product_name }}" 
                                             class="w-14 h-14 rounded-xl object-cover border border-[#F2DECA] shrink-0 bg-white">
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-xs sm:text-sm text-[#2B1810] truncate">
                                                {{ $item->product_name }}
                                            </h4>
                                            <div class="text-[11px] text-[#7D6B5D] mt-0.5">
                                                Số lượng: <strong class="text-[#E08A1E] font-bold">x{{ $item->quantity }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-xs sm:text-sm text-[#2B1810]">
                                            {{ number_format($item->line_total, 0, ',', '.') }}đ
                                        </div>
                                        <div class="text-[10.5px] text-[#A8988A]">
                                            {{ number_format($item->product_price, 0, ',', '.') }}đ/bé
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Right Column: Summary & Main Navigation Actions (5 Cols) --}}
                <div class="lg:col-span-5 space-y-6">
                    
                    {{-- Price Breakdown --}}
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#F2DECA] p-5 space-y-3">
                        <h3 class="text-sm font-extrabold text-[#2B1810] uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="text-base">🧾</span>
                            <span>Chi tiết thanh toán</span>
                        </h3>

                        <div class="space-y-2 text-xs sm:text-sm border-b border-[#F2DECA] pb-3">
                            <div class="flex justify-between text-[#7D6B5D]">
                                <span>Tạm tính tiền hàng:</span>
                                <span class="font-bold text-[#2B1810]">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                            </div>

                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-rose-600 font-medium">
                                    <span>Giảm giá Voucher:</span>
                                    <span class="font-bold">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif

                            @if($order->shipping_discount_amount > 0)
                                <div class="flex justify-between text-emerald-600 font-medium">
                                    <span>Giảm phí vận chuyển:</span>
                                    <span class="font-bold">-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-[#7D6B5D]">
                                <span>Phí giao hàng:</span>
                                <span class="font-bold text-[#2B1810]">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <span class="font-black text-sm text-[#2B1810]">TỔNG THANH TOÁN:</span>
                            <span class="font-black text-xl sm:text-2xl text-[#E08A1E] tracking-tight">
                                {{ number_format($order->total_amount, 0, ',', '.') }}đ
                            </span>
                        </div>
                    </div>

                    {{-- NAVIGATION ACTION BUTTONS --}}
                    <div class="space-y-3 pt-1">
                        {{-- Button 1: Return to Homepage (Keep shopping) --}}
                        <a href="{{ route('home') }}" 
                           class="w-full bg-gradient-to-r from-[#E08A1E] to-[#D68729] hover:from-[#D17E17] hover:to-[#C2751D] text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-[#E08A1E]/25 flex items-center justify-center gap-2.5 text-sm sm:text-base transition transform hover:-translate-y-0.5 active:translate-y-0 tracking-wide uppercase">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Quay lại trang chủ</span>
                        </a>

                        {{-- Button 2: View Cart --}}
                        <a href="{{ route('customer.cart') }}" 
                           class="w-full bg-white hover:bg-[#FFF9F2] text-[#8C4A19] font-extrabold py-3.5 px-6 rounded-2xl border border-[#F2DECA] flex items-center justify-center gap-2.5 text-sm transition transform hover:-translate-y-0.5 active:translate-y-0 shadow-xs">
                            <svg class="w-5 h-5 shrink-0 text-[#E08A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Xem Giỏ Hàng Của Bạn</span>
                        </a>

                        {{-- Button 3: View Order Details --}}
                        <a href="{{ route('customer.orders.show', $order->id) }}" 
                           class="w-full bg-[#FAF6EE] hover:bg-[#F2DECA]/50 text-[#5D4037] font-bold py-3 px-6 rounded-2xl border border-[#F2DECA]/80 flex items-center justify-center gap-2.5 text-xs sm:text-sm transition">
                            <svg class="w-4 h-4 shrink-0 text-[#8C4A19]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span>Xem Chi Tiết & Theo Dõi Đơn Hàng</span>
                        </a>
                    </div>

                    {{-- Shop Guarantees Badge --}}
                    <div class="bg-[#FFFDF9] border border-[#F2DECA] rounded-2xl p-4 text-xs space-y-2 text-[#5D4037]">
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-base">🚚</span>
                            <span>Giao hàng toàn quốc - Đóng gói cẩn thận 3 lớp</span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-base">🧸</span>
                            <span>100% bông trắng tinh khiết, an toàn tuyệt đối</span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-base">📞</span>
                            <span>Hotline hỗ trợ 24/7: <strong class="text-[#E08A1E]">0377.466.205</strong></span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
@endsection

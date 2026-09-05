@extends('layouts.customer')

@section('title', 'Đặt hàng thành công - Mật Ngọt Bear')

@section('content')
<div class="py-8 sm:py-12 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-32 font-sans" 
     x-data="{
         copied: false,
         copyOrderCode(code) {
             navigator.clipboard.writeText(code).then(() => {
                 this.copied = true;
                 setTimeout(() => this.copied = false, 2500);
             });
         }
     }">
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

        {{-- Main Success Card --}}
        <div class="bg-white rounded-3xl border border-[#F2DECA] shadow-xl overflow-hidden mb-8">
            
            {{-- Top Hero Header --}}
            <div class="bg-gradient-to-br from-[#2B1810] via-[#3A2015] to-[#2B1810] p-8 sm:p-12 text-center text-white relative overflow-hidden">
                {{-- Decorative Background Watermarks --}}
                <div class="absolute -right-6 -bottom-8 text-white/[0.04] text-9xl font-black pointer-events-none select-none">
                    🧸
                </div>
                <div class="absolute -left-6 -top-8 text-white/[0.03] text-9xl font-black pointer-events-none select-none">
                    🍯
                </div>

                {{-- Static, Elegant Success Confirmation Badge (No bouncing poppers) --}}
                <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#10B981]/20 border-2 border-[#10B981]/50 text-emerald-400 mb-4 shadow-lg shadow-emerald-900/30">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white mb-2.5">
                    @if($order->payment_status === 'PAID')
                        Thanh Toán & Đặt Hàng Thành Công!
                    @else
                        Đặt Hàng Thành Công!
                    @endif
                </h1>
                
                <p class="text-xs sm:text-sm lg:text-base text-[#F5DEB3] max-w-xl mx-auto font-normal leading-relaxed opacity-95">
                    Cảm ơn bạn đã tin tưởng và lựa chọn <strong class="text-white font-semibold">Mật Ngọt Bear</strong>. Đơn hàng của bạn đang được xưởng chuẩn bị chu đáo và sẽ sớm gửi đến bạn!
                </p>

                {{-- Order Code Pill with Quick Copy --}}
                <div class="mt-6 inline-flex flex-wrap items-center justify-center gap-3 bg-white/10 backdrop-blur-md px-5 py-2 rounded-xl border border-white/20 shadow-inner">
                    <span class="text-xs uppercase tracking-wide text-white/80 font-medium">Mã đơn hàng:</span>
                    <span class="font-mono font-bold text-base sm:text-lg text-[#F6D89B] tracking-wider">{{ $order->order_code }}</span>
                    <button type="button" @click="copyOrderCode('{{ $order->order_code }}')"
                            class="ml-1 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-medium rounded-lg transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <template x-if="!copied">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span>Sao chép</span>
                            </span>
                        </template>
                        <template x-if="copied">
                            <span class="flex items-center gap-1 text-emerald-300">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Đã chép!</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            {{-- Status Summary Strip --}}
            <div class="bg-[#FFFDF9] border-b border-[#F2DECA] px-5 sm:px-8 py-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-xs">
                <div class="p-2.5 rounded-xl bg-white border border-[#F5E8D8]">
                    <div class="text-[#7D6B5D] font-normal text-[11px]">Thời gian đặt</div>
                    <div class="font-semibold text-[#2B1810] mt-1 text-xs sm:text-[13px]">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div class="p-2.5 rounded-xl bg-white border border-[#F5E8D8]">
                    <div class="text-[#7D6B5D] font-normal text-[11px]">Phương thức thanh toán</div>
                    <div class="font-semibold text-[#2B1810] mt-1 text-xs sm:text-[13px] truncate">
                        @if($order->payment_method === 'COD')
                            💵 Khi nhận hàng (COD)
                        @elseif($order->payment_method === 'CARD')
                            💳 VNPAY / Thẻ
                        @elseif($order->payment_method === 'E_WALLET')
                            👛 Ví MoMo
                        @else
                            🏦 Chuyển khoản VietQR
                        @endif
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-white border border-[#F5E8D8]">
                    <div class="text-[#7D6B5D] font-normal text-[11px]">Trạng thái thanh toán</div>
                    <div class="mt-1">
                        @if($order->payment_status === 'PAID')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đã thanh toán
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Chờ thanh toán
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-2.5 rounded-xl bg-white border border-[#F5E8D8]">
                    <div class="text-[#7D6B5D] font-normal text-[11px]">Trạng thái đơn hàng</div>
                    <div class="mt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> {{ $order->order_status === 'PENDING' ? 'Chờ xác nhận' : 'Đã xác nhận' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Unpaid Online Order Notice (VietQR / VNPay / MoMo) --}}
            @if(in_array($order->payment_method, ['BANK_TRANSFER', 'CARD', 'E_WALLET']) && in_array($order->payment_status, ['UNPAID', 'PENDING']))
                <div class="mx-6 sm:mx-8 mt-6 bg-gradient-to-r from-amber-500/10 via-amber-500/15 to-amber-500/10 border border-amber-300 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#E08A1E] text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                            💳
                        </div>
                        <div>
                            <h4 class="font-bold text-sm sm:text-base text-[#2B1810]">Đơn hàng đang chờ thanh toán</h4>
                            <p class="text-xs text-[#7D6B5D] mt-0.5">Số tiền cần thanh toán: <strong class="text-[#D68729] font-bold">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></p>
                        </div>
                    </div>
                    <div class="shrink-0 w-full sm:w-auto">
                        <a href="{{ $order->payment_method === 'CARD' ? route('customer.payment.vnpay.redirect', $order) : ($order->payment_method === 'E_WALLET' ? route('customer.payment.momo.redirect', $order) : route('customer.payment.qr', $order)) }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-semibold text-xs sm:text-sm rounded-xl shadow-md transition transform hover:-translate-y-0.5 tracking-wide">
                            <span>Thanh toán ngay</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Content Details Grid --}}
            <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Left Column: Delivery Info & Ordered Items (7 Cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    
                    {{-- 1. Recipient Information Card --}}
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#F2DECA] p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center gap-2.5 pb-3.5 mb-3.5 border-b border-[#F2DECA]">
                            <span class="w-7 h-7 rounded-lg bg-[#FFF0DC] text-[#D68729] flex items-center justify-center text-sm font-bold">
                                📍
                            </span>
                            <h3 class="text-xs sm:text-sm font-bold text-[#2B1810] uppercase tracking-wide">
                                Thông tin người nhận
                            </h3>
                        </div>

                        <div class="space-y-2.5 text-xs sm:text-sm">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-[#7D6B5D] shrink-0">Người nhận:</span>
                                <span class="font-medium text-[#2B1810] text-right">{{ $order->recipient_name }}</span>
                            </div>

                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-[#7D6B5D] shrink-0">Số điện thoại:</span>
                                <span class="font-medium text-[#2B1810] text-right">{{ $order->recipient_phone }}</span>
                            </div>

                            <div class="flex items-start justify-between gap-2 pt-1 border-t border-[#F5E8D8]">
                                <span class="text-[#7D6B5D] shrink-0">Địa chỉ giao hàng:</span>
                                <span class="font-medium text-[#2B1810] text-right max-w-sm">{{ $order->recipient_address }}</span>
                            </div>

                            @if($order->note)
                                <div class="mt-3 p-3 rounded-xl bg-[#FFF8EE] border border-[#F2DECA] text-xs text-[#5D4037]">
                                    <strong class="text-[#D68729] font-medium">Ghi chú:</strong> {{ $order->note }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Ordered Products Card --}}
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#F2DECA] p-5 sm:p-6 shadow-xs">
                        <div class="flex items-center justify-between pb-3.5 mb-3.5 border-b border-[#F2DECA]">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-[#FFF0DC] text-[#D68729] flex items-center justify-center text-sm font-bold">
                                    🧸
                                </span>
                                <h3 class="text-xs sm:text-sm font-bold text-[#2B1810] uppercase tracking-wide">
                                    Sản phẩm đã đặt
                                </h3>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#FFF0DC] text-[#D68729] border border-[#FAD9B5]">
                                {{ $order->details->sum('quantity') }} bé gấu
                            </span>
                        </div>

                        <div class="divide-y divide-[#F2DECA]/60">
                            @foreach($order->details as $item)
                                @php
                                    $prod = $item->product;
                                    $primaryImage = $prod?->images?->firstWhere('is_primary', true) ?? $prod?->images?->first();
                                    $imageUrl = $primaryImage ? asset($primaryImage->image_path) : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80';
                                    
                                    $specs = [];
                                    if (!empty($prod?->size)) { $specs[] = $prod->size; }
                                    if (!empty($prod?->color)) { $specs[] = $prod->color; }
                                    $specsText = !empty($specs) ? implode(' · ', $specs) : ($prod?->category?->name ?? 'Gấu bông');
                                @endphp
                                <div class="py-3.5 flex items-center justify-between gap-4 first:pt-1 last:pb-1">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl border border-[#F2DECA] bg-white overflow-hidden shrink-0 p-1">
                                            <img src="{{ $imageUrl }}" 
                                                 alt="{{ $item->product_name }}" 
                                                 class="w-full h-full object-cover rounded-lg"
                                                 onerror="this.src='https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80'">
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="font-medium text-xs sm:text-sm text-[#2B1810] line-clamp-2 leading-snug">
                                                {{ $item->product_name }}
                                            </h4>
                                            <div class="text-[11px] text-[#7D6B5D] mt-0.5">
                                                {{ $specsText }}
                                            </div>
                                            <div class="text-[11px] font-medium text-[#D68729] mt-0.5">
                                                Số lượng: x{{ $item->quantity }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <div class="font-semibold text-sm sm:text-base text-[#2B1810]">
                                            {{ number_format($item->line_total, 0, ',', '.') }}đ
                                        </div>
                                        <div class="text-[10.5px] text-[#7D6B5D] mt-0.5">
                                            {{ number_format($item->product_price, 0, ',', '.') }}đ/bé
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Right Column: Payment Details & Navigation Actions (5 Cols) --}}
                <div class="lg:col-span-5 space-y-6">
                    
                    {{-- 1. Chi Tiết Thanh Toán (Ordered strictly: Tạm tính -> Phí giao hàng -> Giảm ship -> Giảm voucher -> Tổng) --}}
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#F2DECA] p-5 sm:p-6 shadow-xs space-y-3.5">
                        <div class="flex items-center gap-2.5 pb-3 border-b border-[#F2DECA]">
                            <span class="w-7 h-7 rounded-lg bg-[#FFF0DC] text-[#D68729] flex items-center justify-center text-sm font-bold">
                                🧾
                            </span>
                            <h3 class="text-xs sm:text-sm font-bold text-[#2B1810] uppercase tracking-wide">
                                Chi tiết thanh toán
                            </h3>
                        </div>

                        <div class="space-y-2.5 text-xs sm:text-sm">
                            {{-- Line 1: Tạm tính tiền hàng --}}
                            <div class="flex justify-between items-center text-[#7D6B5D]">
                                <span>Tạm tính tiền hàng:</span>
                                <span class="font-medium text-[#2B1810]">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                            </div>

                            {{-- Line 2: Phí giao hàng (Placed ABOVE shipping discount as requested) --}}
                            <div class="flex justify-between items-center text-[#7D6B5D]">
                                <span>Phí giao hàng:</span>
                                <span class="font-medium text-[#2B1810]">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                            </div>

                            {{-- Line 3: Giảm phí vận chuyển (Placed directly UNDER shipping fee) --}}
                            @if($order->shipping_discount_amount > 0)
                                <div class="flex justify-between items-center text-teal-600 font-medium">
                                    <span class="flex items-center gap-1">
                                        <span>🚚 Giảm phí vận chuyển</span>
                                        @if($order->shippingVoucher)
                                            <span class="text-[10.5px] font-normal opacity-85">[{{ $order->shippingVoucher->code }}]</span>
                                        @endif
                                    </span>
                                    <span class="font-semibold">-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif

                            {{-- Line 4: Giảm giá Voucher của Shop --}}
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between items-center text-emerald-600 font-medium">
                                    <span class="flex items-center gap-1">
                                        <span>🛍️ Giảm giá voucher</span>
                                        @if($order->voucher)
                                            <span class="text-[10.5px] font-normal opacity-85">[{{ $order->voucher->code }}]</span>
                                        @endif
                                    </span>
                                    <span class="font-semibold">-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif

                            {{-- Line 5: Total --}}
                            <div class="pt-3 border-t border-[#F2DECA] flex justify-between items-baseline">
                                <span class="font-bold text-xs sm:text-sm text-[#2B1810] uppercase">Tổng thanh toán:</span>
                                <span class="font-bold text-xl sm:text-2xl text-[#D68729] tracking-tight">
                                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. NAVIGATION ACTION BUTTONS --}}
                    <div class="space-y-2.5 pt-1">
                        {{-- Button 1: Return to Homepage --}}
                        <a href="{{ route('home') }}" 
                           class="w-full bg-[#D68729] hover:bg-[#C2751D] text-white font-semibold py-3 px-5 rounded-xl shadow-sm hover:shadow-md transition flex items-center justify-center gap-2 text-sm cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Tiếp tục mua sắm</span>
                        </a>

                        {{-- Button 2: View & Track Order Details --}}
                        <a href="{{ route('customer.orders.show', $order->id) }}" 
                           class="w-full bg-white hover:bg-[#FFFBF5] text-[#5C3219] hover:text-[#D68729] font-medium py-2.5 px-4 rounded-xl border border-[#EADBCC] hover:border-[#D68729]/50 shadow-xs transition flex items-center justify-center gap-2 text-xs sm:text-sm cursor-pointer">
                            <svg class="w-4 h-4 shrink-0 text-[#D68729]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span>Xem chi tiết & theo dõi đơn hàng</span>
                        </a>

                        {{-- Button 3: View Cart --}}
                        <a href="{{ route('customer.cart') }}" 
                           class="w-full bg-[#FAF6EE] hover:bg-[#F3ECE0] text-[#7D6B5D] hover:text-[#3D2517] font-medium py-2 px-4 rounded-xl border border-[#EADBCC]/60 transition flex items-center justify-center gap-2 text-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 shrink-0 text-[#8C7A6B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Xem giỏ hàng của bạn</span>
                        </a>
                    </div>

                    {{-- 3. Shop Trust & Guarantees --}}
                    <div class="bg-[#FFFDF9] border border-[#F2DECA] rounded-2xl p-4 text-xs space-y-2.5 text-[#5D4037] shadow-xs">
                        <div class="flex items-center gap-2.5 font-normal">
                            <span class="text-base shrink-0">🚚</span>
                            <span>Giao hàng toàn quốc · Đóng gói cẩn thận 3 lớp</span>
                        </div>
                        <div class="flex items-center gap-2.5 font-normal">
                            <span class="text-base shrink-0">🧸</span>
                            <span>100% bông gòn tinh khiết, an toàn cho trẻ nhỏ</span>
                        </div>
                        <div class="flex items-center gap-2.5 font-normal">
                            <span class="text-base shrink-0">📞</span>
                            <span>Hotline hỗ trợ 24/7: <strong class="text-[#D68729] font-semibold">0377.466.205</strong></span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
@endsection

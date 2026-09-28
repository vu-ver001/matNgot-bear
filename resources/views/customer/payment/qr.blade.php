@extends('layouts.customer')

@section('title', $order->payment_method === 'CARD' ? 'Thanh Toán Cổng VNPAY - Mật Ngọt Bear' : 'Thanh Toán Chuyển Khoản QR - Mật Ngọt Bear')

@section('content')
    <div class="py-4 sm:py-6 bg-[#FAF6EE] min-h-[calc(100vh-100px)] pb-12 font-sans" x-data="paymentGateway({
        orderCode: '{{ $order->order_code }}',
        amount: {{ $amount }},
        remainingSeconds: {{ $remainingSeconds }},
        expireMinutes: 15,
        qrUrl: '{{ $order->payment_method === 'CARD' ? $vnpayQrUrl : $vietQrUrl }}',
        vietQrUrl: '{{ $vietQrUrl }}',
        vnpayQrUrl: '{{ $vnpayQrUrl }}'
    })">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            {{-- Top Navigation & Back Bar --}}
            <div class="mb-3.5 flex flex-wrap items-center justify-between gap-3">
                <button type="button" 
                        @click="goToOrderDetail()"
                        class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-[#786B61] hover:text-[#5C3219] bg-white hover:bg-amber-50 px-3.5 py-1.5 rounded-xl border border-[#EBDDCD] transition shadow-2xs cursor-pointer group"
                        title="Quay lại chi tiết đơn hàng (đơn hàng được giữ ở trạng thái Chờ thanh toán trong 24h)">
                    <i class="fa-solid fa-arrow-left text-[#B87309] group-hover:-translate-x-0.5 transition-transform"></i>
                    <span>Quay lại chi tiết đơn hàng</span>
                </button>
                <div class="flex items-center gap-2 text-xs text-[#786B61]">
                    <span class="hidden sm:inline">Mã đơn:</span>
                    <span class="font-mono font-bold text-[#5C3219] bg-white border border-[#EBDDCD] px-2.5 py-1 rounded-lg shadow-2xs">#{{ $order->order_code }}</span>
                </div>
            </div>

            {{-- Breadcrumb --}}
            <div class="mb-3">
                <x-breadcrumb :items="[
                    ['label' => 'Trang Chủ', 'url' => route('home')],
                    ['label' => 'Giỏ Hàng', 'url' => route('customer.cart')],
                    ['label' => 'Thanh Toán']
                ]" />
            </div>

            {{-- Main Payment Container --}}
            <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xl overflow-hidden">
                
                {{-- Top Header --}}
                <div class="px-5 py-3.5 sm:px-8 sm:py-4 text-white flex flex-col sm:flex-row items-center justify-between gap-3
                     {{ $order->payment_method === 'CARD' ? 'bg-gradient-to-r from-[#003B73] via-[#005BAA] to-[#003B73]' : 'bg-gradient-to-r from-[#5C3219] via-[#7E4A28] to-[#5C3219]' }}">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl shrink-0 border border-white/20 shadow-inner">
                            @if($order->payment_method === 'CARD')
                                <i class="fa-solid fa-qrcode text-[#64B5F6]"></i>
                            @else
                                <i class="fa-solid fa-building-columns text-[#F6D89B]"></i>
                            @endif
                        </div>
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-widest text-[#FCE4EC] flex items-center gap-2">
                                @if($order->payment_method === 'CARD')
                                    <span>Cổng thanh toán điện tử VNPAY</span>
                                @else
                                    <span>Chuyển khoản trực tuyến 24/7</span>
                                @endif
                            </div>
                            <h1 class="text-lg sm:text-xl font-black mt-0.5 tracking-tight">
                                @if($order->payment_method === 'CARD')
                                    Thanh toán qua Cổng VNPAY-QR
                                @else
                                    Chuyển khoản VietQR Napas 24/7
                                @endif
                            </h1>
                        </div>
                    </div>

                    {{-- Countdown Timer --}}
                    <div class="bg-black/30 backdrop-blur-md rounded-xl px-4 py-2 border border-white/15 text-center shrink-0 shadow-sm transition-colors duration-300"
                         :class="isExpired ? 'border-rose-400/50 bg-rose-950/50' : ''">
                        <div class="text-[10px] font-medium uppercase tracking-wider transition-colors duration-200"
                             :class="isExpired ? 'text-rose-200' : 'text-white/80'"
                             x-text="isExpired ? 'Trạng thái phiên' : 'Thời gian thanh toán còn lại'">Thời gian thanh toán còn lại</div>
                        <div class="text-lg sm:text-xl font-black font-mono tracking-wider transition-colors duration-200"
                             :class="isExpired ? 'text-rose-300' : 'text-[#F6D89B]'"
                                    {{-- Body content --}}
                <div class="p-4 sm:p-6 lg:p-7 grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-7 items-start">
                    
                    {{-- Left Column: QR Code Image & Scan Guide --}}
                    <div class="lg:col-span-5 flex flex-col items-center justify-center p-4 sm:p-5 rounded-3xl border text-center bg-[#FAF6EE] border-[#EBDDCD]">
                        
                        @if($order->payment_method === 'CARD')
                            {{-- VNPAY Card --}}
                            <div class="relative bg-white p-3 rounded-2xl shadow-md border border-[#EBDDCD] overflow-hidden inline-block">
                                <img :src="vnpayQrUrl" alt="VNPAY QR Code" 
                                     :class="isExpired ? 'filter blur-[4px] opacity-20 grayscale select-none' : ''"
                                     class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl transition duration-300">

                                {{-- Expired Overlay --}}
                                <div x-show="isExpired" x-cloak 
                                     class="absolute inset-0 z-20 flex flex-col items-center justify-center p-4 bg-white/92 backdrop-blur-xs text-center">
                                    <div class="w-11 h-11 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg mb-1.5 shadow-xs border border-rose-200 animate-pulse">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <div class="font-extrabold text-sm text-[#2C1408]">Mã QR đã hết hạn</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 max-w-[200px] leading-tight">Thời gian thanh toán 15 phút đã kết thúc. Vui lòng tạo mã mới.</div>
                                    <button type="button" @click="refreshQrSession()" :disabled="isRefreshing"
                                            class="mt-2.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#005BAA] to-[#0070CE] text-white font-bold text-xs shadow-md flex items-center gap-1.5 transition transform hover:scale-105 active:scale-95 cursor-pointer disabled:opacity-50">
                                        <i class="fa-solid fa-arrows-rotate text-xs" :class="isRefreshing ? 'animate-spin' : ''"></i>
                                        <span x-text="isRefreshing ? 'Đang tạo mã...' : 'Lấy mã QR mới'">Lấy mã QR mới</span>
                                    </button>
                                </div>

                                <div x-show="!isExpired" class="mt-2 text-center text-xs font-bold text-[#005BAA] flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#005BAA] animate-ping"></span>
                                    Quét bằng App Ngân hàng hoặc Ví VNPAY
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-[#786B61] leading-relaxed">
                                Mở ứng dụng <strong>Mobile Banking</strong> hoặc <strong>Ví VNPAY</strong> để quét mã.
                            </div>
                        @else
                            {{-- VietQR MB Bank --}}
                            <div class="relative bg-white p-3 rounded-2xl shadow-md border border-[#EBDDCD] overflow-hidden inline-block">
                                <img :src="vietQrUrl" alt="VietQR MB Bank" 
                                     :class="isExpired ? 'filter blur-[4px] opacity-20 grayscale select-none' : ''"
                                     class="w-48 h-48 sm:w-56 sm:h-56 object-contain rounded-xl transition duration-300">

                                {{-- Expired Overlay --}}
                                <div x-show="isExpired" x-cloak 
                                     class="absolute inset-0 z-20 flex flex-col items-center justify-center p-4 bg-white/92 backdrop-blur-xs text-center">
                                    <div class="w-11 h-11 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg mb-1.5 shadow-xs border border-rose-200 animate-pulse">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <div class="font-extrabold text-sm text-[#2C1408]">Mã QR đã hết hạn</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 max-w-[200px] leading-tight">Thời gian thanh toán 15 phút đã kết thúc. Vui lòng tạo mã mới.</div>
                                    <button type="button" @click="refreshQrSession()" :disabled="isRefreshing"
                                            class="mt-2.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#A77B5A] to-[#8C623A] text-white font-bold text-xs shadow-md flex items-center gap-1.5 transition transform hover:scale-105 active:scale-95 cursor-pointer disabled:opacity-50">
                                        <i class="fa-solid fa-arrows-rotate text-xs" :class="isRefreshing ? 'animate-spin' : ''"></i>
                                        <span x-text="isRefreshing ? 'Đang tạo mã...' : 'Lấy mã QR mới'">Lấy mã QR mới</span>
                                    </button>
                                </div>

                                <div x-show="!isExpired" class="mt-2 text-center text-xs font-bold text-[#5C3219] flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    VietQR Napas 24/7 
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-[#786B61] leading-relaxed">
                                Mở ứng dụng ngân hàng bất kỳ để quét mã <strong>VietQR Napas 24/7</strong>.
                            </div>
                        @endif

                    </div>

                    {{-- Right Column: Transfer Info Details & Actions --}}
                    <div class="lg:col-span-7 space-y-3">
                        
                        {{-- Amount Highlight Card --}}
                        <div class="rounded-2xl p-3 sm:p-4 border flex items-center justify-between shadow-2xs bg-[#FFF8E7] border-[#F4B860]/40">
                            <div>
                                <div class="text-xs font-semibold text-[#786B61]">
                                    Số tiền cần thanh toán:
                                </div>
                                <div class="text-2xl sm:text-3xl font-black tracking-tight mt-0.5 text-[#E08A1E]">
                                    {{ number_format($amount, 0, ',', '.') }}đ
                                </div>
                            </div>
                            <button type="button" @click="copyText('{{ $amount }}', 'Số tiền')" 
                                    class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl bg-white border text-xs font-bold transition shadow-2xs flex items-center gap-1.5 cursor-pointer border-[#EBDDCD] text-[#5C3219] hover:bg-[#FAF6EE]">
                                <i class="fa-regular fa-copy text-[11px]"></i>
                                <span>Sao chép số tiền</span>
                            </button>
                        </div>

                        {{-- Payment Info Details --}}
                        <div class="space-y-2 p-3.5 sm:p-4 rounded-2xl border bg-[#FDFBF7] border-[#EBDDCD]">
                            
                            {{-- Order Code / Transfer Content --}}
                            <div class="flex items-center justify-between text-sm py-1.5 border-b border-[#F0E6D8]">
                                <div class="flex flex-col">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Nội dung chuyển khoản / Lời nhắn:</span>
                                    <span class="text-[10px] text-rose-500 font-semibold italic">* Bắt buộc điền đúng mã đơn hàng</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200 text-sm">{{ $transferContent }}</span>
                                    <button type="button" @click="copyText('{{ $transferContent }}', 'Mã đơn hàng')" 
                                            class="px-2.5 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-700 text-xs font-bold transition flex items-center gap-1 cursor-pointer" title="Sao chép nội dung">
                                        <i class="fa-regular fa-copy text-[11px]"></i>
                                        <span>Chép mã</span>
                                    </button>
                                </div>
                            </div>

                            @if($order->payment_method === 'CARD')
                                {{-- VNPAY Specific details --}}
                                <div class="flex items-center justify-between text-sm py-1.5 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Cổng thanh toán:</span>
                                    <span class="font-bold text-[#005BAA] flex items-center gap-1.5 text-xs sm:text-sm">
                                        <span>🛡️</span> Cổng VNPAY Gateway Quốc Gia
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-1.5 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Hình thức hỗ trợ:</span>
                                    <span class="text-xs font-semibold text-[#2C1408]">Thẻ Visa, Mastercard, ATM & VNPAY-QR</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-1.5 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Đơn vị thụ hưởng:</span>
                                    <span class="font-bold text-[#2C1408] text-xs sm:text-sm">{{ $paymentConfig['vnpay_merchant'] ?? 'MẬT NGỌT BEAR' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-1.5">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Mã điểm bán (Terminal ID):</span>
                                    <span class="font-mono font-bold text-[#2C1408] text-xs sm:text-sm">{{ $paymentConfig['vnpay_tmn_code'] ?? 'MNBEAR01' }}</span>
                                </div>
                                <div class="pt-1">
                                    <a href="{{ route('customer.payment.vnpay.redirect', $order->id) }}"
                                       class="w-full bg-gradient-to-r from-[#005BAA] to-[#0088CC] hover:from-[#004B8C] hover:to-[#0077B3] text-white font-extrabold py-2.5 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-md shadow-[#005BAA]/20 tracking-wide">
                                        <span>💳 CHUYỂN ĐẾN CỔNG VNPAY (THẺ VISA / ATM) ➔</span>
                                    </a>
                                </div>
                            @else
                                {{-- Bank Transfer (MB Bank) details --}}
                                <div class="flex items-center justify-between text-sm py-1.5 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Ngân hàng thụ hưởng:</span>
                                    <span class="font-bold text-[#2C1408] text-xs sm:text-sm">{{ $paymentConfig['bank_name'] }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-1.5 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Số tài khoản:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-base text-[#2C1408]">{{ $paymentConfig['account_number'] }}</span>
                                        <button type="button" @click="copyText('{{ $paymentConfig['account_number'] }}', 'Số tài khoản')" class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1 cursor-pointer">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm py-1.5">
                                    <span class="text-[#786B61] font-medium text-xs sm:text-sm">Chủ tài khoản:</span>
                                    <span class="font-bold text-[#2C1408] text-xs sm:text-sm">{{ $paymentConfig['account_name'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Navigation & Secondary Actions --}}
                        <div class="pt-0.5 flex flex-wrap items-center justify-between gap-2 text-xs">
                            @if($order->paymentExpiresAt())
                                <span class="text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-amber-600"></i>
                                    <span>Hạn thanh toán: <strong>{{ $order->paymentExpiresAt()->format('H:i - d/m/Y') }}</strong> (tự hủy sau 24h)</span>
                                </span>
                            @endif
                            <button type="button" 
                               @click="goToOrderDetail()"
                               class="text-[#786B61] hover:text-[#5C3219] font-medium underline inline-flex items-center gap-1 transition cursor-pointer">
                                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                <span>Thanh toán sau & xem đơn hàng #{{ $order->order_code }}</span>
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- QR Code Full Screen Zoom Modal --}}
        <div x-show="isZoomed" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4 transition-all"
             @click="isZoomed = false">
            <div class="bg-white p-6 rounded-3xl max-w-sm w-full shadow-2xl text-center relative" @click.stop>
                <button type="button" @click="isZoomed = false" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="text-xs font-black text-[#8C4A19] uppercase tracking-wider mb-2">Mã QR Chuyển Khoản VietQR</div>
                <img :src="qrUrl" alt="QR Code Large" class="w-64 h-64 mx-auto object-contain rounded-2xl border border-gray-100 shadow-sm">
                <div class="mt-3 text-xs text-gray-600 font-medium">
                    Quét bằng ứng dụng Ngân hàng (MB Bank, Vietcombank, Techcombank...)
                </div>
                <button type="button" @click="downloadQr(qrUrl, 'vietqr_{{ $order->order_code }}.png')"
                        class="mt-3 w-full py-2 px-3 rounded-xl bg-amber-50 text-[#8C4A19] border border-amber-200 font-bold text-xs hover:bg-amber-100 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-download"></i>
                    <span>Tải ảnh này về máy</span>
                </button>
            </div>
        </div>

        {{-- Hidden Form for Cancel and Return Home Fallback --}}
        <form id="cancel-order-home-form" method="POST" action="{{ route('customer.orders.cancel', $order->id) }}" class="hidden">
            @csrf
            <input type="hidden" name="reason" value="Khách hàng hủy đơn khi thoát khỏi trang thanh toán online">
            <input type="hidden" name="redirect_to" value="home">
        </form>

    </div>

    @push('scripts')
    <script>
        function paymentGateway(config) {
            return {
                timeLeft: typeof config.remainingSeconds !== 'undefined' ? config.remainingSeconds : (config.expireMinutes * 60),
                interval: null,
                pollInterval: null,
                isChecking: false,
                isPaid: false,
                isRefreshing: false,
                allowLeave: false,
                isZoomed: false,
                qrUrl: config.qrUrl,
                vietQrUrl: config.vietQrUrl || config.qrUrl,
                vnpayQrUrl: config.vnpayQrUrl || config.qrUrl,

                get isExpired() {
                    return this.timeLeft <= 0;
                },

                get formattedTime() {
                    if (this.timeLeft <= 0) {
                        return '00:00 (Hết hạn)';
                    }
                    const minutes = Math.floor(this.timeLeft / 60);
                    const seconds = this.timeLeft % 60;
                    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                init() {
                    // 1. Countdown timer
                    this.startTimer();

                    // 2. Auto Polling: Tự động kiểm tra biến động số dư / Webhook mỗi 1.8 giây
                    this.pollInterval = setInterval(() => {
                        this.checkAutoPayment();
                    }, 1800);

                    // 3. Nút Back của trình duyệt / Mobile swipe back -> Chuyển về chi tiết đơn hàng vừa tạo
                    try {
                        history.pushState({ page: 'payment_qr' }, '', window.location.href);
                    } catch (e) {}

                    window.addEventListener('popstate', (e) => {
                        if (!this.allowLeave && !this.isPaid) {
                            this.goToOrderDetail();
                        }
                    });
                },

                startTimer() {
                    if (this.interval) clearInterval(this.interval);
                    this.interval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.interval);
                            this.isZoomed = false;
                        }
                    }, 1000);
                },

                refreshQrSession() {
                    if (this.isRefreshing) return;
                    this.isRefreshing = true;

                    fetch('{{ route('customer.payment.refresh-qr', $order->id) }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            this.timeLeft = data.remainingSeconds || 900;
                            if (data.vietQrUrl) {
                                this.vietQrUrl = data.vietQrUrl;
                                this.qrUrl = data.vietQrUrl;
                            }
                            if (data.vnpayQrUrl) {
                                this.vnpayQrUrl = data.vnpayQrUrl;
                                this.qrUrl = data.vnpayQrUrl;
                            }

                            this.startTimer();

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Đã làm mới mã QR thành công!',
                                    text: 'Thời gian thanh toán: 15 phút',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    background: '#FAF6F0',
                                    color: '#2E190E'
                                });
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Thông báo',
                                    text: data.message || 'Không thể làm mới mã QR.',
                                    background: '#FAF6F0',
                                    color: '#2E190E'
                                });
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error refreshing QR:', err);
                    })
                    .finally(() => {
                        this.isRefreshing = false;
                    });
                },

                checkAutoPayment() {
                    if (this.isPaid || this.isChecking) return;
                    this.isChecking = true;

                    fetch('{{ route('customer.payment.status', $order->id) }}', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.paid && data.redirect_url) {
                            this.isPaid = true;
                            this.allowLeave = true;
                            clearInterval(this.pollInterval);
                            clearInterval(this.interval);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '🎉 ĐÃ NHẬN THANH TOÁN THÀNH CÔNG!',
                                    html: 'Hệ thống đã tự động ghi nhận biến động số dư.<br><span class="text-xs text-gray-500">Đang chuyển hướng ngay...</span>',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: '#FAF6F0',
                                    color: '#2E190E'
                                });
                            }

                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1200);
                        }
                    })
                    .catch(() => {})
                    .finally(() => {
                        this.isChecking = false;
                    });
                },

                goToOrderDetail() {
                    this.allowLeave = true;
                    window.location.href = '{{ route('customer.orders.show', $order->id) }}';
                },

                confirmLeave(targetUrl = null) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Rời khỏi trang thanh toán?',
                            html: `
                                <div class="text-left text-xs sm:text-sm space-y-3 text-[#4E342E]">
                                    <div class="p-3.5 bg-gradient-to-r from-rose-50 to-amber-50 border border-rose-200 rounded-2xl shadow-2xs">
                                        <div class="flex items-center gap-2 text-rose-800 font-bold mb-1.5">
                                            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                                            <span>Lưu ý quan trọng:</span>
                                        </div>
                                        <p class="text-xs text-stone-700 leading-relaxed">
                                            Nếu bạn chọn <strong>Quay lại trang chủ</strong>, đơn hàng <strong class="font-mono text-[#5C3219]">#{{ $order->order_code }}</strong> sẽ <strong>bị hủy</strong> ngay lập tức.
                                        </p>
                                    </div>
                                    <p class="text-xs text-stone-600 leading-relaxed">
                                        Bạn có muốn <strong>tiếp tục thanh toán</strong> đơn hàng này không? Hoặc bạn có thể chọn <strong>Thanh toán sau</strong> để hệ thống giữ đơn trong vòng 24 giờ.
                                    </p>
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            showDenyButton: true,
                            cancelButtonText: '<i class="fa-solid fa-qrcode mr-1.5"></i> Thanh toán tiếp',
                            confirmButtonText: '<i class="fa-solid fa-ban mr-1.5"></i> Về trang chủ (Hủy đơn)',
                            denyButtonText: '<i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Thanh toán sau (Giữ 24h)',
                            cancelButtonColor: '#059669',
                            confirmButtonColor: '#DC2626',
                            denyButtonColor: '#B87309',
                            background: '#FAF6F0',
                            color: '#2E190E',
                            customClass: {
                                popup: 'rounded-3xl shadow-2xl border border-amber-200',
                                cancelButton: 'rounded-xl text-xs sm:text-sm font-bold px-3.5 py-2.5 shadow-xs cursor-pointer',
                                confirmButton: 'rounded-xl text-xs sm:text-sm font-bold px-3.5 py-2.5 shadow-xs cursor-pointer',
                                denyButton: 'rounded-xl text-xs sm:text-sm font-bold px-3.5 py-2.5 shadow-xs cursor-pointer'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Người dùng chọn: Về trang chủ (Hủy đơn)
                                this.cancelOrderAndGoHome();
                            } else if (result.isDenied) {
                                // Người dùng chọn: Thanh toán sau (Giữ đơn 24h)
                                this.allowLeave = true;
                                window.location.href = '{{ route('customer.checkout.success', $order->id) }}';
                            } else {
                                // Người dùng chọn: Thanh toán tiếp (ở lại trang)
                                try {
                                    history.pushState({ page: 'payment_qr' }, '', window.location.href);
                                } catch (e) {}
                            }
                        });
                    } else {
                        if (confirm('Nếu bạn rời đi và quay lại trang chủ, đơn hàng #{{ $order->order_code }} sẽ bị hủy.\\n\\nBạn có muốn hủy đơn và quay về trang chủ không?')) {
                            this.cancelOrderAndGoHome();
                        } else {
                            try {
                                history.pushState({ page: 'payment_qr' }, '', window.location.href);
                            } catch (e) {}
                        }
                    }
                },

                confirmPayLater() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Thanh toán sau & Giữ đơn hàng?',
                            html: `
                                <div class="text-left text-xs sm:text-sm space-y-2.5 text-[#4E342E]">
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 leading-relaxed">
                                        Đơn hàng <strong class="font-mono text-[#5C3219]">#{{ $order->order_code }}</strong> sẽ được giữ với trạng thái <strong>Chờ thanh toán</strong>.
                                    </div>
                                    <p class="text-xs text-stone-600">
                                        ⏳ Hệ thống sẽ giữ đơn hàng của bạn trong <strong>24 giờ</strong>. Bạn có thể thanh toán sau tại mục <em>Đơn hàng của tôi</em>. Sau 24h nếu chưa thanh toán, đơn hàng sẽ tự động hủy.
                                    </p>
                                </div>
                            `,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Đồng ý thanh toán sau',
                            cancelButtonText: 'Ở lại thanh toán ngay',
                            confirmButtonColor: '#B87309',
                            cancelButtonColor: '#059669',
                            background: '#FAF6F0',
                            color: '#2E190E',
                            customClass: {
                                popup: 'rounded-3xl shadow-2xl border border-amber-200',
                                confirmButton: 'rounded-xl text-xs sm:text-sm font-bold px-4 py-2.5 shadow-xs cursor-pointer',
                                cancelButton: 'rounded-xl text-xs sm:text-sm font-bold px-4 py-2.5 shadow-xs cursor-pointer'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.allowLeave = true;
                                window.location.href = '{{ route('customer.checkout.success', $order->id) }}';
                            }
                        });
                    } else {
                        this.allowLeave = true;
                        window.location.href = '{{ route('customer.checkout.success', $order->id) }}';
                    }
                },

                cancelOrderAndGoHome() {
                    this.allowLeave = true;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Đang hủy đơn hàng...',
                            text: 'Vui lòng chờ trong giây lát...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    }

                    fetch('{{ route('customer.orders.cancel', $order->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            reason: 'Khách hàng hủy đơn khi thoát khỏi trang thanh toán online'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        window.location.href = (data && data.redirect_url) ? data.redirect_url : '{{ route('home') }}';
                    })
                    .catch(() => {
                        const fallbackForm = document.getElementById('cancel-order-home-form');
                        if (fallbackForm) {
                            fallbackForm.submit();
                        } else {
                            window.location.href = '{{ route('home') }}';
                        }
                    });
                },

                copyText(text, label) {
                    const notify = () => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: `Đã sao chép ${label}!`,
                                showConfirmButton: false,
                                timer: 1800
                            });
                        }
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(notify).catch(() => {
                            this.fallbackCopy(text, notify);
                        });
                    } else {
                        this.fallbackCopy(text, notify);
                    }
                },

                fallbackCopy(text, callback) {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        if (callback) callback();
                    } catch (err) {
                        console.error('Fallback copy failed', err);
                    }
                    document.body.removeChild(textArea);
                },

                downloadQr(url, filename) {
                    fetch(url)
                        .then(resp => resp.blob())
                        .then(blob => {
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = blobUrl;
                            a.download = filename || 'payment_qr.png';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(blobUrl);
                            document.body.removeChild(a);
                        })
                        .catch(() => {
                            window.open(url, '_blank');
                        });
                }
            };
        }
    </script>
    @endpush
@endsection

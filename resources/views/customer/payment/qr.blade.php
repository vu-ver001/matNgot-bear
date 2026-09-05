@extends('layouts.customer')

@section('title', 'Thanh Toán QR VietQR - Mật Ngọt Bear')

@section('content')
    <div class="py-10 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-24 font-sans" x-data="paymentGateway({
        orderCode: '{{ $order->order_code }}',
        amount: {{ $amount }},
        expireMinutes: 15
    })">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <div class="mb-6">
                <x-breadcrumb :items="[
                    ['label' => 'Trang Chủ', 'url' => route('home')],
                    ['label' => 'Giỏ Hàng', 'url' => route('customer.cart')],
                    ['label' => 'Thanh Toán']
                ]" />
            </div>

            {{-- Main Payment Container --}}
            <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xl overflow-hidden">
                
                {{-- Top Header --}}
                <div class="p-6 sm:p-8 text-white flex flex-col sm:flex-row items-center justify-between gap-4
                     {{ $order->payment_method === 'CARD' ? 'bg-gradient-to-r from-[#003B73] via-[#005BAA] to-[#003B73]' : ($order->payment_method === 'E_WALLET' ? 'bg-gradient-to-r from-[#800040] via-[#A50064] to-[#800040]' : 'bg-gradient-to-r from-[#5C3219] via-[#7E4A28] to-[#5C3219]') }}">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-[#F6D89B] text-2xl shrink-0 border border-white/20">
                            @if($order->payment_method === 'E_WALLET')
                                <i class="fa-solid fa-wallet text-[#FF80AB]"></i>
                            @elseif($order->payment_method === 'CARD')
                                <i class="fa-solid fa-qrcode text-[#64B5F6]"></i>
                            @else
                                <i class="fa-solid fa-building-columns text-[#F6D89B]"></i>
                            @endif
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-widest text-[#F6D89B]">
                                @if($order->payment_method === 'CARD')
                                    Cổng thanh toán điện tử VNPAY
                                @elseif($order->payment_method === 'E_WALLET')
                                    Cổng thanh toán Ví điện tử MoMo
                                @else
                                    Chuyển khoản trực tuyến 24/7
                                @endif
                            </div>
                            <h1 class="text-xl sm:text-2xl font-black mt-0.5">
                                @if($order->payment_method === 'E_WALLET')
                                    Thanh toán qua Ví MoMo
                                @elseif($order->payment_method === 'CARD')
                                    Thanh toán qua Cổng VNPAY-QR
                                @else
                                    Chuyển khoản VietQR (MB Bank)
                                @endif
                            </h1>
                        </div>
                    </div>

                    {{-- Countdown Timer --}}
                    <div class="bg-black/25 backdrop-blur-md rounded-2xl px-5 py-2.5 border border-white/10 text-center shrink-0">
                        <div class="text-[11px] text-white/80 font-medium uppercase tracking-wider">Thời gian thanh toán còn lại</div>
                        <div class="text-xl sm:text-2xl font-black text-[#F6D89B] font-mono tracking-wider" x-text="formattedTime">15:00</div>
                    </div>
                </div>

                {{-- Body content --}}
                <div class="p-6 sm:p-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                    
                    {{-- Left Column: QR Code Image & Scan Guide --}}
                    <div class="md:col-span-5 flex flex-col items-center justify-center p-6 bg-[#FAF6EE] rounded-3xl border border-[#EBDDCD] text-center">
                        <div class="relative group">
                            <div class="bg-white p-3.5 rounded-2xl shadow-md border border-[#EBDDCD]">
                                @if($order->payment_method === 'E_WALLET')
                                    {{-- Authentic MoMo QR Card Header --}}
                                    <div class="bg-gradient-to-r from-[#A50064] to-[#C2185B] text-white py-2 px-3 rounded-xl mb-2 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5h-2v-5h2v5zm0-6.5h-2V8h2v2zm4 6.5h-2v-5h2v5zm0-6.5h-2V8h2v2z"/>
                                        </svg>
                                        <span class="text-xs font-black tracking-wider uppercase">VÍ ĐIỆN TỬ MOMO</span>
                                    </div>
                                    <img src="{{ $momoQrUrl }}" alt="MoMo QR Code" class="w-56 h-56 object-contain rounded-xl mx-auto border border-[#F5E6EC]">
                                    <div class="mt-2 text-center text-xs font-bold text-[#A50064] flex items-center justify-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#A50064] animate-ping"></span>
                                        Quét bằng ứng dụng MoMo
                                    </div>
                                @elseif($order->payment_method === 'CARD')
                                    <img src="{{ $vnpayQrUrl }}" alt="VNPAY QR Code" class="w-56 h-56 object-contain rounded-xl">
                                    <div class="mt-2 text-center text-xs font-bold text-[#005BAA] flex items-center justify-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#005BAA] animate-ping"></span>
                                        Quét bằng App Ngân hàng hoặc Ví VNPAY
                                    </div>
                                @else
                                    <img src="{{ $vietQrUrl }}" alt="VietQR MB Bank" class="w-56 h-56 object-contain rounded-xl">
                                    <div class="mt-2 text-center text-xs font-bold text-[#5C3219] flex items-center justify-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                        VietQR Napas 24/7 (MB Bank)
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 text-xs text-[#786B61] leading-relaxed">
                            @if($order->payment_method === 'CARD')
                                Mở ứng dụng <strong>Mobile Banking</strong> (VCB, BIDV, VietinBank, MB, Agribank, Techcombank...) hoặc <strong>Ví VNPAY</strong> để quét mã.
                            @elseif($order->payment_method === 'E_WALLET')
                                Mở ứng dụng <strong>Ví MoMo</strong> trên điện thoại để quét mã thanh toán tự động.
                            @else
                                Mở ứng dụng ngân hàng bất kỳ để quét mã <strong>VietQR Napas 24/7</strong> nhanh chóng.
                            @endif
                        </div>
                    </div>

                    {{-- Right Column: Transfer Info Details & Copy Actions --}}
                    <div class="md:col-span-7 space-y-4">
                        
                        {{-- Amount Highlight --}}
                        <div class="bg-[#FFF8E7] rounded-2xl p-4 border border-[#F4B860]/40 flex items-center justify-between">
                            <div>
                                <div class="text-xs text-[#786B61] font-semibold">Số tiền cần thanh toán:</div>
                                <div class="text-2xl sm:text-3xl font-black text-[#E08A1E] tracking-tight mt-0.5">
                                    {{ number_format($amount, 0, ',', '.') }}đ
                                </div>
                            </div>
                            <button type="button" @click="copyText('{{ $amount }}', 'Số tiền')" 
                                    class="px-3 py-1.5 rounded-xl bg-white border border-[#EBDDCD] text-xs font-bold text-[#5C3219] hover:bg-[#FAF6EE] transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-[#5C3219]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span>Sao chép</span>
                            </button>
                        </div>

                        {{-- Payment Info Details --}}
                        <div class="space-y-3 bg-[#FDFBF7] p-5 rounded-2xl border border-[#EBDDCD]">
                            
                            {{-- Order Code / Transfer Content --}}
                            <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                <span class="text-[#786B61] font-medium">Mã đơn hàng:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">{{ $transferContent }}</span>
                                    <button type="button" @click="copyText('{{ $transferContent }}', 'Mã đơn hàng')" 
                                            class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1 cursor-pointer" title="Sao chép mã">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if($order->payment_method === 'E_WALLET')
                                {{-- MoMo Specific details --}}
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Hình thức:</span>
                                    <span class="font-bold text-[#A50064] flex items-center gap-1.5">
                                        <span>👛</span> Ví điện tử MoMo
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Số điện thoại MoMo:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-lg text-[#2C1408]">{{ $paymentConfig['momo_phone'] }}</span>
                                        <button type="button" @click="copyText('{{ $paymentConfig['momo_phone'] }}', 'Số điện thoại')" class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Chủ tài khoản ví:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['momo_name'] }}</span>
                                </div>
                                <div class="pt-1">
                                    <a href="momo://" 
                                       class="w-full bg-gradient-to-r from-[#A50064] to-[#C2185B] hover:from-[#880052] hover:to-[#AD1457] text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                                        <span>📱 Bấm để mở App MoMo trên điện thoại</span>
                                    </a>
                                </div>
                            @elseif($order->payment_method === 'CARD')
                                {{-- VNPAY Specific details --}}
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Cổng thanh toán:</span>
                                    <span class="font-bold text-[#005BAA] flex items-center gap-1.5">
                                        <span>🛡️</span> Cổng VNPAY Gateway Quốc Gia
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Hình thức hỗ trợ:</span>
                                    <span class="text-xs font-semibold text-[#2C1408]">Thẻ Visa, Mastercard, ATM & VNPAY-QR</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Đơn vị thụ hưởng:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['vnpay_merchant'] ?? 'MẬT NGỌT BEAR' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2">
                                    <span class="text-[#786B61] font-medium">Mã điểm bán (Terminal ID):</span>
                                    <span class="font-mono font-bold text-[#2C1408]">{{ $paymentConfig['vnpay_tmn_code'] ?? 'MNBEAR01' }}</span>
                                </div>
                                <div class="pt-2">
                                    <a href="{{ route('customer.payment.vnpay.redirect', $order->id) }}"
                                       class="w-full bg-gradient-to-r from-[#005BAA] to-[#0088CC] hover:from-[#004B8C] hover:to-[#0077B3] text-white font-extrabold py-3 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition shadow-md shadow-[#005BAA]/25 tracking-wide">
                                        <span>💳 CHUYỂN ĐẾN CỔNG VNPAY (THẺ VISA / ATM) ➔</span>
                                    </a>
                                </div>
                            @else
                                {{-- Bank Transfer (MB Bank) details --}}
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Ngân hàng thụ hưởng:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['bank_name'] }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Số tài khoản:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-lg text-[#2C1408]">{{ $paymentConfig['account_number'] }}</span>
                                        <button type="button" @click="copyText('{{ $paymentConfig['account_number'] }}', 'Số tài khoản')" class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2">
                                    <span class="text-[#786B61] font-medium">Chủ tài khoản:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['account_name'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Live Automatic Payment Detection Card --}}
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-300/80 rounded-2xl p-5 shadow-sm space-y-3">
                            <div class="flex items-center gap-3">
                                <span class="relative flex h-4 w-4 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-600 shadow-md"></span>
                                </span>
                                <div class="font-extrabold text-xs sm:text-sm text-emerald-900 uppercase tracking-wide flex items-center gap-1.5">
                                    <span>Tự động nhận diện thanh toán 24/7</span>
                                </div>
                            </div>
                            <p class="text-xs text-emerald-800 leading-relaxed">
                                Hệ thống đang tự động lắng nghe biến động số dư từ Ngân hàng / MoMo. Bạn chỉ cần <strong>chuyển khoản đúng số tiền và nội dung</strong>, trang sẽ <strong>tự động chuyển sang Thanh toán thành công ngay lập tức</strong> mà không cần bấm nút!
                            </p>
                        </div>

                        {{-- Navigation & Secondary Actions --}}
                        <div class="space-y-2 pt-1 text-center">
                            <a href="{{ route('customer.orders.show', $order->id) }}" 
                               class="text-xs text-[#786B61] hover:text-[#5C3219] font-medium underline inline-flex items-center gap-1.5 transition">
                                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                <span>Thanh toán sau & xem chi tiết đơn hàng #{{ $order->order_code }}</span>
                            </a>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function paymentGateway(config) {
            return {
                timeLeft: config.expireMinutes * 60,
                interval: null,
                pollInterval: null,
                isChecking: false,
                isPaid: false,

                init() {
                    // 1. Countdown timer
                    this.interval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.interval);
                        }
                    }, 1000);

                    // 2. Auto Polling: Tự động kiểm tra biến động số dư / Webhook mỗi 1.8 giây
                    this.pollInterval = setInterval(() => {
                        this.checkAutoPayment();
                    }, 1800);
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
                            clearInterval(this.pollInterval);
                            clearInterval(this.interval);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '🎉 ĐÃ NHẬN THANH TOÁN THÀNH CÔNG!',
                                    html: 'Hệ thống đã tự động ghi nhận biến động số dư từ Ngân hàng.<br><span class=\"text-xs text-gray-500\">Đang chuyển hướng ngay...</span>',
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

                get formattedTime() {
                    const minutes = Math.floor(this.timeLeft / 60);
                    const seconds = this.timeLeft % 60;
                    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                },

                copyText(text, label) {
                    navigator.clipboard.writeText(text).then(() => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: `Đã sao chép ${label}!`,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    });
                }
            };
        }
    </script>
    @endpush
@endsection

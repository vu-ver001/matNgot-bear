<x-app-layout>
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
                                    <img src="{{ $momoQrUrl }}" alt="MoMo QR Code" class="w-56 h-56 object-contain rounded-xl">
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
                                    class="px-3 py-1.5 rounded-xl bg-white border border-[#EBDDCD] text-xs font-bold text-[#5C3219] hover:bg-[#FAF6EE] transition shadow-xs flex items-center gap-1.5">
                                <i class="fa-regular fa-copy"></i>
                                <span>Sao chép</span>
                            </button>
                        </div>

                        {{-- Payment Info Details --}}
                        <div class="space-y-3 bg-[#FDFBF7] p-5 rounded-2xl border border-[#EBDDCD]">
                            
                            {{-- Order Code / Transfer Content --}}
                            <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                <span class="text-[#786B61] font-medium">Mã đơn hàng / Cú pháp:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">{{ $transferContent }}</span>
                                    <button type="button" @click="copyText('{{ $transferContent }}', 'Mã đơn hàng')" 
                                            class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1" title="Sao chép mã">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            @if($order->payment_method === 'E_WALLET')
                                {{-- MoMo Specific details --}}
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Hình thức:</span>
                                    <span class="font-bold text-[#A50064] flex items-center gap-1">
                                        <i class="fa-solid fa-wallet"></i> Ví điện tử MoMo
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Số điện thoại MoMo:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[#2C1408]">{{ $paymentConfig['momo_phone'] }}</span>
                                        <button type="button" @click="copyText('{{ $paymentConfig['momo_phone'] }}', 'SĐT MoMo')" class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Chủ tài khoản ví:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['momo_name'] }}</span>
                                </div>
                                <div class="pt-1">
                                    <a href="https://nhantien.momo.vn/{{ $paymentConfig['momo_phone'] }}" target="_blank"
                                       class="w-full bg-gradient-to-r from-[#A50064] to-[#C2185B] hover:from-[#880052] hover:to-[#AD1457] text-white font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        <span>Bấm để mở App MoMo chuyển tiền</span>
                                    </a>
                                </div>
                            @elseif($order->payment_method === 'CARD')
                                {{-- VNPAY Specific details --}}
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Cổng thanh toán:</span>
                                    <span class="font-bold text-[#005BAA] flex items-center gap-1">
                                        <i class="fa-solid fa-shield-halved"></i> Cổng VNPAY-QR Quốc Gia
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2 border-b border-[#F0E6D8]">
                                    <span class="text-[#786B61] font-medium">Đơn vị thụ hưởng:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['vnpay_merchant'] ?? 'MẬT NGỌT BEAR' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2">
                                    <span class="text-[#786B61] font-medium">Mã điểm bán (Terminal ID):</span>
                                    <span class="font-mono font-bold text-[#2C1408]">{{ $paymentConfig['vnpay_tmn_code'] ?? 'MNBEAR01' }}</span>
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
                                        <button type="button" @click="copyText('{{ $paymentConfig['account_number'] }}', 'Số tài khoản')" class="text-[#E08A1E] hover:text-[#5C3219] text-xs font-bold transition p-1">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm py-2">
                                    <span class="text-[#786B61] font-medium">Chủ tài khoản:</span>
                                    <span class="font-bold text-[#2C1408]">{{ $paymentConfig['account_name'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Confirm Actions --}}
                        <form action="{{ route('customer.payment.confirm', $order->id) }}" method="POST" class="pt-2">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold py-4 px-6 rounded-2xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center text-base tracking-wide uppercase flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check-circle text-lg"></i>
                                <span>TÔI ĐÃ HOÀN TẤT CHUYỂN TIỀN</span>
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="text-xs text-[#786B61] hover:text-[#5C3219] font-medium underline">
                                Hoàn tất thanh toán sau & xem đơn hàng
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

                init() {
                    this.interval = setInterval(() => {
                        if (this.timeLeft > 0) {
                            this.timeLeft--;
                        } else {
                            clearInterval(this.interval);
                        }
                    }, 1000);
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
</x-app-layout>

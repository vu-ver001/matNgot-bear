<x-app-layout>
    <div class="py-10 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-36 font-sans" 
         x-data="checkoutComponent({
            subtotal: {{ (float) $subtotal }},
            shippingFee: {{ (float) $shippingFee }},
            orderVouchers: {{ json_encode($orderVouchers) }},
            shippingVouchers: {{ json_encode($shippingVouchers) }}
         })">
        
        {{-- Breadcrumb --}}
        <div class="hidden md:block max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <x-breadcrumb :items="[
                ['label' => 'Trang Chủ', 'url' => route('home')],
                ['label' => 'Giỏ Hàng', 'url' => route('customer.cart')],
                ['label' => 'Thanh Toán']
            ]" />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header Title --}}
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-[#5C3219] text-[#F6D89B] flex items-center justify-center shadow-md shadow-[#5C3219]/20 shrink-0">
                    <i class="fa-solid fa-credit-card text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] tracking-tight">Thanh toán đơn hàng</h1>
                    <p class="text-xs sm:text-sm font-medium text-[#786B61] mt-0.5">Vui lòng kiểm tra thông tin giao hàng và chọn phương thức thanh toán</p>
                </div>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-lg shrink-0"></i>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('customer.checkout.process') }}" method="POST" id="checkout-form">
                @csrf

                {{-- Hidden fields for selected items --}}
                @foreach($selectedItemIds as $id)
                    <input type="hidden" name="selected_items[]" value="{{ $id }}">
                @endforeach

                <input type="hidden" name="voucher_id" :value="selectedOrderVoucher ? selectedOrderVoucher.id : ''">
                <input type="hidden" name="shipping_voucher_id" :value="selectedShippingVoucher ? selectedShippingVoucher.id : ''">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    {{-- Left Column: Customer info, Products, Payment methods --}}
                    <div class="lg:col-span-7 space-y-6">

                        {{-- 1. Delivery Information --}}
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EBDDCD] shadow-sm">
                            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-[#F0E6D8]">
                                <div class="w-8 h-8 rounded-xl bg-[#FFF5E6] text-[#E08A1E] flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <h2 class="text-lg font-bold text-[#2C1408]">1. Thông tin người nhận</h2>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#5C3219] uppercase tracking-wider mb-2">Họ và tên <span class="text-rose-500">*</span></label>
                                    <input type="text" name="recipient_name" required
                                           value="{{ old('recipient_name', $user->full_name ?? '') }}"
                                           placeholder="Ví dụ: Nguyễn Văn A"
                                           class="w-full px-4 py-3 rounded-2xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20 text-sm font-medium text-[#2C1408] bg-[#FDFBF7] transition">
                                    @error('recipient_name')
                                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#5C3219] uppercase tracking-wider mb-2">Số điện thoại <span class="text-rose-500">*</span></label>
                                    <input type="tel" name="recipient_phone" required
                                           value="{{ old('recipient_phone', $user->phone ?? '') }}"
                                           placeholder="Ví dụ: 0987654321"
                                           class="w-full px-4 py-3 rounded-2xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20 text-sm font-medium text-[#2C1408] bg-[#FDFBF7] transition">
                                    @error('recipient_phone')
                                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-[#5C3219] uppercase tracking-wider mb-2">Địa chỉ giao hàng <span class="text-rose-500">*</span></label>
                                    <input type="text" name="recipient_address" required
                                           value="{{ old('recipient_address', $user->address ?? '') }}"
                                           placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"
                                           class="w-full px-4 py-3 rounded-2xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20 text-sm font-medium text-[#2C1408] bg-[#FDFBF7] transition">
                                    @error('recipient_address')
                                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-[#5C3219] uppercase tracking-wider mb-2">Ghi chú cho shipper (Không bắt buộc)</label>
                                    <textarea name="note" rows="2"
                                              placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao, viết kèm thiệp..."
                                              class="w-full px-4 py-3 rounded-2xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20 text-sm font-medium text-[#2C1408] bg-[#FDFBF7] transition resize-none">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Selected Products --}}
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EBDDCD] shadow-sm">
                            <div class="flex items-center justify-between pb-4 mb-5 border-b border-[#F0E6D8]">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-[#FFF5E6] text-[#E08A1E] flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <h2 class="text-lg font-bold text-[#2C1408]">2. Sản phẩm đặt mua ({{ $cartItems->count() }})</h2>
                                </div>
                                <a href="{{ route('customer.cart') }}" class="text-xs font-bold text-[#E08A1E] hover:underline">Thay đổi</a>
                            </div>

                            <div class="space-y-4 divide-y divide-[#F0E6D8]">
                                @foreach($cartItems as $item)
                                    @php
                                        $price = $item->product->sale_price ?? $item->product->price;
                                        $primaryImage = $item->product->images->firstWhere('is_primary', true) ?? $item->product->images->first();
                                        $imageUrl = $primaryImage ? asset($primaryImage->image_path) : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80';
                                    @endphp
                                    <div class="flex items-center gap-4 pt-4 first:pt-0">
                                        <img src="{{ $imageUrl }}" alt="{{ $item->product->name }}" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-2xl border border-[#EBDDCD] bg-[#FAF6EE] shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-sm text-[#2C1408] truncate">{{ $item->product->name }}</h4>
                                            <div class="text-xs text-[#786B61] mt-1">Số lượng: <strong class="text-[#2C1408]">x{{ $item->quantity }}</strong></div>
                                            <div class="text-xs text-[#786B61] mt-0.5">Đơn giá: {{ number_format($price, 0, ',', '.') }}đ</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-sm sm:text-base font-bold text-[#E08A1E]">{{ number_format($price * $item->quantity, 0, ',', '.') }}đ</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 3. Payment Method --}}
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EBDDCD] shadow-sm">
                            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-[#F0E6D8]">
                                <div class="w-8 h-8 rounded-xl bg-[#FFF5E6] text-[#E08A1E] flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <h2 class="text-lg font-bold text-[#2C1408]">3. Phương thức thanh toán</h2>
                            </div>

                            <div class="space-y-3" x-data="{ paymentMethod: 'COD' }">
                                {{-- COD --}}
                                <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition"
                                       :class="paymentMethod === 'COD' ? 'border-[#E08A1E] bg-[#FFF8E7]' : 'border-[#EBDDCD] bg-white hover:border-[#E08A1E]/50'">
                                    <input type="radio" name="payment_method" value="COD" x-model="paymentMethod" class="text-[#E08A1E] focus:ring-[#E08A1E]">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-bold text-[#2C1408]">Thanh toán khi nhận hàng (COD)</div>
                                        <div class="text-xs text-[#786B61] mt-0.5">Nhận gấu bông, kiểm tra hàng rồi thanh toán tiền mặt</div>
                                    </div>
                                </label>

                                {{-- Banking / QR --}}
                                <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition"
                                       :class="paymentMethod === 'BANKING' ? 'border-[#E08A1E] bg-[#FFF8E7]' : 'border-[#EBDDCD] bg-white hover:border-[#E08A1E]/50'">
                                    <input type="radio" name="payment_method" value="BANKING" x-model="paymentMethod" class="text-[#E08A1E] focus:ring-[#E08A1E]">
                                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-qrcode text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-bold text-[#2C1408]">Chuyển khoản Ngân hàng / QR Pay</div>
                                        <div class="text-xs text-[#786B61] mt-0.5">Quét mã QR chuyển khoản nhanh 24/7 sau khi đặt hàng</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>

                    {{-- Right Column: Voucher & Order Summary --}}
                    <div class="lg:col-span-5 space-y-6 sticky top-6">

                        {{-- Voucher Selection --}}
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EBDDCD] shadow-sm space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-ticket text-[#E08A1E]"></i>
                                    <h3 class="text-base font-bold text-[#2C1408]">Ưu đãi & Voucher</h3>
                                </div>
                            </div>

                            {{-- Order Voucher Dropdown --}}
                            <div>
                                <label class="block text-xs font-bold text-[#786B61] mb-1.5">Mã giảm giá đơn hàng:</label>
                                <select class="w-full px-4 py-2.5 rounded-2xl border border-[#EBDDCD] text-sm text-[#2C1408] bg-[#FDFBF7] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20"
                                        @change="selectOrderVoucher($event.target.value)">
                                    <option value="">-- Chọn mã giảm giá --</option>
                                    <template x-for="v in orderVouchers" :key="v.id">
                                        <option :value="v.id" x-text="v.code + ' - Giảm ' + (v.discount_type === 'PERCENTAGE' ? v.discount_value + '%' : formatVND(v.discount_value))"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Shipping Voucher Dropdown --}}
                            <div>
                                <label class="block text-xs font-bold text-[#786B61] mb-1.5">Mã giảm phí vận chuyển:</label>
                                <select class="w-full px-4 py-2.5 rounded-2xl border border-[#EBDDCD] text-sm text-[#2C1408] bg-[#FDFBF7] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20"
                                        @change="selectShippingVoucher($event.target.value)">
                                    <option value="">-- Chọn mã freeship --</option>
                                    <template x-for="v in shippingVouchers" :key="v.id">
                                        <option :value="v.id" x-text="v.code + ' - ' + (v.discount_type === 'PERCENTAGE' ? 'Giảm ' + v.discount_value + '% ship' : 'Giảm ' + formatVND(v.discount_value) + ' ship')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Order Summary Card --}}
                        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EBDDCD] shadow-sm space-y-4">
                            <h3 class="text-base font-bold text-[#2C1408] pb-3 border-b border-[#F0E6D8]">Tóm tắt đơn hàng</h3>

                            <div class="space-y-2.5 text-sm">
                                <div class="flex justify-between text-[#786B61]">
                                    <span>Tạm tính hàng:</span>
                                    <span class="font-bold text-[#2C1408]" x-text="formatVND(subtotal)"></span>
                                </div>

                                <div class="flex justify-between text-[#786B61]">
                                    <span>Phí giao hàng:</span>
                                    <span class="font-bold text-[#2C1408]" x-text="formatVND(shippingFee)"></span>
                                </div>

                                <template x-if="orderDiscount > 0">
                                    <div class="flex justify-between text-emerald-600 font-medium">
                                        <span>Giảm giá đơn hàng:</span>
                                        <span class="font-bold" x-text="'-' + formatVND(orderDiscount)"></span>
                                    </div>
                                </template>

                                <template x-if="shippingDiscount > 0">
                                    <div class="flex justify-between text-teal-600 font-medium">
                                        <span>Giảm phí vận chuyển:</span>
                                        <span class="font-bold" x-text="'-' + formatVND(shippingDiscount)"></span>
                                    </div>
                                </template>

                                <div class="pt-4 border-t border-[#F0E6D8] flex justify-between items-baseline">
                                    <span class="text-base font-extrabold text-[#2C1408]">Tổng thanh toán:</span>
                                    <span class="text-2xl sm:text-3xl font-extrabold text-[#E08A1E] tracking-tight" x-text="formatVND(finalTotal)"></span>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full mt-4 bg-gradient-to-r from-[#E08A1E] to-[#5C3219] hover:from-[#5C3219] hover:to-[#2C160B] text-white font-extrabold py-4 px-6 rounded-2xl shadow-xl shadow-[#E08A1E]/25 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center text-base tracking-wide uppercase flex items-center justify-center gap-2">
                                <i class="fa-solid fa-lock text-sm"></i>
                                <span>ĐẶT HÀNG NGAY</span>
                            </button>

                            <div class="text-center text-xs text-[#786B61] pt-2 flex items-center justify-center gap-4">
                                <span><i class="fa-solid fa-shield-halved text-emerald-600"></i> Bảo mật 100%</span>
                                <span><i class="fa-solid fa-box text-[#E08A1E]"></i> Đóng gói hút chân không</span>
                            </div>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function checkoutComponent(config) {
            return {
                subtotal: config.subtotal,
                shippingFee: config.shippingFee,
                orderVouchers: config.orderVouchers,
                shippingVouchers: config.shippingVouchers,
                selectedOrderVoucher: null,
                selectedShippingVoucher: null,

                selectOrderVoucher(id) {
                    this.selectedOrderVoucher = this.orderVouchers.find(v => v.id == id) || null;
                },

                selectShippingVoucher(id) {
                    this.selectedShippingVoucher = this.shippingVouchers.find(v => v.id == id) || null;
                },

                get orderDiscount() {
                    if (!this.selectedOrderVoucher) return 0;
                    const v = this.selectedOrderVoucher;
                    if (this.subtotal < parseFloat(v.min_order_value || 0)) return 0;

                    let discount = 0;
                    if (v.discount_type === 'PERCENTAGE') {
                        discount = (this.subtotal * parseFloat(v.discount_value)) / 100;
                        if (v.max_discount_value) {
                            discount = Math.min(discount, parseFloat(v.max_discount_value));
                        }
                    } else {
                        discount = Math.min(this.subtotal, parseFloat(v.discount_value));
                    }
                    return discount;
                },

                get shippingDiscount() {
                    if (!this.selectedShippingVoucher) return 0;
                    const v = this.selectedShippingVoucher;
                    if (this.subtotal < parseFloat(v.min_order_value || 0)) return 0;

                    let discount = 0;
                    if (v.discount_type === 'PERCENTAGE') {
                        discount = (this.shippingFee * parseFloat(v.discount_value)) / 100;
                    } else {
                        discount = Math.min(this.shippingFee, parseFloat(v.discount_value));
                    }
                    return discount;
                },

                get finalTotal() {
                    return Math.max(0, this.subtotal - this.orderDiscount) + Math.max(0, this.shippingFee - this.shippingDiscount);
                },

                formatVND(value) {
                    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value).replace('₫', 'đ');
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

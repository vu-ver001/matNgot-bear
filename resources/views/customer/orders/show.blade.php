<x-customer-account-layout title="Chi tiết đơn hàng" :flush="true">
    <div class="orders-ui">
        {{-- Header Breadcrumb & Actions --}}
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders.index') }}" 
                   class="w-9 h-9 inline-flex items-center justify-center text-sm font-bold text-[#795548] bg-white hover:bg-amber-50 rounded-xl border border-amber-200 transition shadow-2xs hover:scale-105"
                   title="Quay lại danh sách đơn hàng">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-extrabold text-xl sm:text-2xl text-[#4E342E] flex items-center gap-2.5 flex-wrap">
                        <span>Đơn Hàng <span class="text-[#E08A1E] font-mono">#{{ $order->order_code }}</span></span>
                        <x-order-status-badge :order="$order" />
                        <x-payment-status-badge :status="$order->payment_status" />
                    </h2>
                    <p class="text-xs text-[#8E8076] mt-0.5">
                        Đặt lúc: {{ $order->created_at->format('d/m/Y H:i:s') }} ({{ $order->created_at->diffForHumans() }})
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('customer.messages.index', ['order_id' => $order->id]) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-xl border border-amber-300 transition shadow-2xs hover:scale-102"
                    title="Chat với Shop">
                    <i class="fa-regular fa-comment-dots text-sm"></i>
                    <span>Chat với Shop</span>
                </a>
                <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-[#795548] bg-white hover:bg-amber-50 rounded-xl border border-amber-200 transition shadow-2xs hover:scale-102">
                    <i class="fa-solid fa-file-invoice text-amber-600"></i>
                    <span>Xem hóa đơn điện tử</span>
                </a>
            </div>
        </div>

        @php
            $canPayOnline = $order->canPayOnline();
        @endphp

        @php
            $isShopRejected = $order->order_status === 'CANCELLED' && !str_contains($order->cancel_reason ?? '', '24') && (
                str_contains($order->cancel_reason ?? '', 'từ chối') || 
                str_contains($order->cancel_reason ?? '', 'Cửa hàng') || 
                str_contains($order->cancel_reason ?? '', 'Shop') || 
                ($order->cancelled_by && $order->cancelledByUser?->role !== 'CUSTOMER')
            );
        @endphp

        {{-- Banner thông báo Đơn đã hủy nhưng đã thanh toán online (Khách tự hủy hoặc hệ thống hủy) --}}
        @if($order->order_status === 'CANCELLED' && $order->payment_status === 'PAID' && !$isShopRejected)
            <div class="mb-6 bg-gradient-to-r from-amber-500/10 via-amber-100/80 to-orange-500/10 border-2 border-amber-400 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-amber-500/30">
                        💸
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base font-black text-[#2C1408]">Đơn hàng đã hủy - Đang đợi shop đối soát hoàn tiền</h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px]">CHỜ HOÀN TIỀN</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1 leading-relaxed">
                            Đơn hàng của bạn đã hủy thành công. Số tiền đã thanh toán <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> sẽ được Mật Ngọt Bear chuyển khoản hoàn lại 100% cho bạn.
                        </p>

                        @if($order->refund_bank_account)
                            <div class="mt-3 p-3.5 bg-white rounded-xl border border-amber-300 text-xs text-amber-950" x-data="{ showEditBank: false }">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                                        <span>STK nhận hoàn tiền: <strong>{{ $order->refund_bank_name }}</strong> - <strong class="font-mono text-sm text-amber-900">{{ $order->refund_bank_account }}</strong> ({{ $order->refund_account_holder }})</span>
                                    </div>
                                    <button type="button" @click="showEditBank = !showEditBank" class="text-amber-800 font-bold hover:underline cursor-pointer text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Đổi STK khác
                                    </button>
                                </div>

                                <form x-show="showEditBank" x-cloak action="{{ route('customer.orders.update_refund_account', $order) }}" method="POST" class="mt-3 pt-3 border-t border-amber-200 space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Ngân hàng</label>
                                            <select name="refund_bank_name" class="w-full text-xs p-2 rounded-xl border border-amber-300 bg-white" required>
                                                @foreach(['MB Bank', 'Vietcombank', 'Techcombank', 'BIDV', 'VietinBank', 'ACB', 'TPBank', 'VPBank', 'Sacombank', 'HDBank', 'MSB', 'OCB', 'VIB', 'Agribank', 'Khác'] as $bank)
                                                    <option value="{{ $bank }}" {{ ($order->refund_bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Số tài khoản</label>
                                            <input type="text" name="refund_bank_account" value="{{ $order->refund_bank_account }}" required class="w-full text-xs p-2 rounded-xl border border-amber-300 bg-white font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Chủ tài khoản</label>
                                            <input type="text" name="refund_account_holder" value="{{ $order->refund_account_holder }}" required class="w-full text-xs p-2 rounded-xl border border-amber-300 bg-white uppercase">
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="showEditBank = false" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg cursor-pointer">Hủy</button>
                                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg cursor-pointer">Cập nhật</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="mt-3 p-4 bg-white rounded-2xl border-2 border-amber-300 shadow-xs">
                                <div class="flex items-center gap-2 text-amber-900 font-bold text-xs sm:text-sm mb-1">
                                    <i class="fa-solid fa-building-columns text-amber-700"></i>
                                    <span>Vui lòng cung cấp số tài khoản để Shop chuyển khoản hoàn tiền:</span>
                                </div>
                                <p class="text-xs text-[#7D6B5D] mb-3">Shop cần thông tin STK của bạn để chuyển khoản lại <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>.</p>

                                <form action="{{ route('customer.orders.update_refund_account', $order) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Ngân hàng <span class="text-rose-600">*</span></label>
                                            <select name="refund_bank_name" class="w-full text-xs p-2.5 rounded-xl border border-amber-300 bg-white" required>
                                                <option value="">-- Chọn ngân hàng --</option>
                                                @foreach(['MB Bank', 'Vietcombank', 'Techcombank', 'BIDV', 'VietinBank', 'ACB', 'TPBank', 'VPBank', 'Sacombank', 'HDBank', 'MSB', 'OCB', 'VIB', 'Agribank', 'Khác'] as $bank)
                                                    <option value="{{ $bank }}" {{ ($order->refund_bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Số tài khoản <span class="text-rose-600">*</span></label>
                                            <input type="text" name="refund_bank_account" value="{{ $order->refund_bank_account }}" required placeholder="Nhập STK nhận tiền..." class="w-full text-xs p-2.5 rounded-xl border border-amber-300 bg-white font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Chủ tài khoản <span class="text-rose-600">*</span></label>
                                            <input type="text" name="refund_account_holder" value="{{ $order->refund_account_holder ?: $order->recipient_name }}" required placeholder="TÊN CHỦ TÀI KHOẢN" class="w-full text-xs p-2.5 rounded-xl border border-amber-300 bg-white uppercase">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-paper-plane"></i> Lưu thông tin nhận tiền
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Banner thông báo Đơn đã hủy và ĐÃ HOÀN TIỀN XONG --}}
        @if($order->order_status === 'CANCELLED' && $order->payment_status === 'REFUNDED')
            <div class="mb-6 bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-5 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                    <i class="fa-solid fa-check text-white"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-900">Đơn hàng đã hủy & Shop đã hoàn tiền thành công</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">
                        Shop đã xử lý hoàn lại số tiền <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> vào tài khoản của bạn.
                        @if($order->refund_note)
                            <br><span class="italic text-emerald-800">Ghi chú đối soát: {{ $order->refund_note }}</span>
                        @endif
                    </p>
                </div>
            </div>
        @endif

        {{-- Banner thông báo Đơn đã tự động hủy do quá hạn thanh toán 24h --}}
        @if($order->order_status === 'CANCELLED' && $order->payment_status !== 'PAID' && str_contains($order->cancel_reason ?? '', '24'))
            <div class="mb-6 bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                    <i class="fa-solid fa-clock-rotate-left text-white"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-rose-900">Đơn hàng đã tự động hủy do quá thời hạn thanh toán 24 giờ</h4>
                    <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                        Do đơn hàng chưa được thanh toán thành công trong vòng 24 giờ kể từ thời điểm đặt hàng, hệ thống đã tự động hủy đơn và hoàn lại số lượng tồn kho sản phẩm. Bạn có thể bấm nút <strong>"Mua lại đơn hàng"</strong> bên dưới để tạo đơn mới.
                    </p>
                </div>
            </div>
        @endif

        {{-- Banner thông báo Đơn hàng bị Cửa hàng từ chối tiếp nhận --}}
        @if($isShopRejected)
            <div class="mb-6 bg-gradient-to-r from-rose-500/10 via-rose-50 to-red-500/10 border-2 border-rose-300 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-rose-600/30">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base font-black text-[#2C1408]">Đơn hàng đã bị Cửa hàng từ chối tiếp nhận</h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-900 font-extrabold text-[11px]">CỬA HÀNG TỪ CHỐI</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1 leading-relaxed">
                            Rất tiếc, cửa hàng không thể tiếp nhận và chuẩn bị đơn hàng này cho bạn.
                        </p>

                        <!-- Khối lý do từ chối -->
                        <div class="mt-3 p-3.5 bg-white/95 rounded-xl border border-rose-200 shadow-2xs">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-rose-800 uppercase">
                                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                                <span>Lý do từ chối từ cửa hàng:</span>
                            </div>
                            <p class="text-sm text-rose-700 font-semibold mt-1 italic leading-relaxed">
                                "{{ str_replace(['Cửa hàng từ chối: ', 'Shop từ chối tiếp nhận đơn: ', 'Shop từ chối: '], '', $order->cancel_reason ?? 'Cửa hàng không thể đáp ứng đơn hàng vào thời điểm này') }}"
                            </p>
                        </div>

                        @if($order->payment_status === 'PAID')
                            @if($order->refund_bank_account)
                                <div class="mt-3 p-3.5 bg-white rounded-xl border border-amber-300 text-xs text-amber-950" x-data="{ showEditBank: false }">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                                            <span>STK nhận hoàn tiền ({{ number_format($order->total_amount, 0, ',', '.') }}đ): <strong>{{ $order->refund_bank_name }}</strong> - <strong class="font-mono text-sm text-amber-900">{{ $order->refund_bank_account }}</strong> ({{ $order->refund_account_holder }})</span>
                                        </div>
                                        <button type="button" @click="showEditBank = !showEditBank" class="text-amber-800 font-bold hover:underline cursor-pointer text-xs flex items-center gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Đổi STK khác
                                        </button>
                                    </div>

                                    <form x-show="showEditBank" x-cloak action="{{ route('customer.orders.update_refund_account', $order) }}" method="POST" class="mt-3 pt-3 border-t border-amber-200 space-y-3">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Ngân hàng</label>
                                                <select name="refund_bank_name" class="w-full text-xs p-2 rounded-xl border border-amber-300 bg-white" required>
                                                    @foreach(['MB Bank', 'Vietcombank', 'Techcombank', 'BIDV', 'VietinBank', 'ACB', 'TPBank', 'VPBank', 'Sacombank', 'HDBank', 'MSB', 'OCB', 'VIB', 'Agribank', 'Khác'] as $bank)
                                                        <option value="{{ $bank }}" {{ ($order->refund_bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Số tài khoản</label>
                                                <input type="text" name="refund_bank_account" value="{{ $order->refund_bank_account }}" required class="w-full text-xs p-2 rounded-xl border border-amber-300 bg-white font-mono">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Chủ tài khoản</label>
                                                <input type="text" name="refund_account_holder" value="{{ $order->refund_account_holder }}" required class="w-full text-xs p-2 rounded-xl border border-amber-300 bg-white uppercase">
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="showEditBank = false" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg cursor-pointer">Hủy</button>
                                            <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg cursor-pointer">Cập nhật</button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="mt-3 p-4 bg-white rounded-2xl border-2 border-amber-300 shadow-xs">
                                    <div class="flex items-center gap-2 text-amber-900 font-bold text-xs sm:text-sm mb-1">
                                        <i class="fa-solid fa-building-columns text-amber-700"></i>
                                        <span>Cung cấp số tài khoản nhận tiền hoàn ({{ number_format($order->total_amount, 0, ',', '.') }}đ):</span>
                                    </div>
                                    <p class="text-xs text-[#7D6B5D] mb-3">Đơn hàng này bạn đã thanh toán trực tuyến. Vui lòng cung cấp số tài khoản để Shop chuyển khoản hoàn lại 100% tiền cho bạn.</p>

                                    <form action="{{ route('customer.orders.update_refund_account', $order) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Ngân hàng <span class="text-rose-600">*</span></label>
                                                <select name="refund_bank_name" class="w-full text-xs p-2.5 rounded-xl border border-amber-300 bg-white" required>
                                                    <option value="">-- Chọn ngân hàng --</option>
                                                    @foreach(['MB Bank', 'Vietcombank', 'Techcombank', 'BIDV', 'VietinBank', 'ACB', 'TPBank', 'VPBank', 'Sacombank', 'HDBank', 'MSB', 'OCB', 'VIB', 'Agribank', 'Khác'] as $bank)
                                                        <option value="{{ $bank }}" {{ ($order->refund_bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Số tài khoản <span class="text-rose-600">*</span></label>
                                                <input type="text" name="refund_bank_account" value="{{ $order->refund_bank_account }}" required placeholder="Nhập STK nhận tiền..." class="w-full text-xs p-2.5 rounded-xl border border-amber-300 bg-white font-mono">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-[#4E342E] uppercase mb-1">Chủ tài khoản <span class="text-rose-600">*</span></label>
                                                <input type="text" name="refund_account_holder" value="{{ $order->refund_account_holder ?: $order->recipient_name }}" required placeholder="TÊN CHỦ TÀI KHOẢN" class="w-full text-xs p-2.5 rounded-xl border border-amber-300 bg-white uppercase">
                                            </div>
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                                <i class="fa-solid fa-paper-plane"></i> Lưu thông tin nhận tiền
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @else
                            <p class="text-xs text-[#8E8076] mt-2.5">
                                Quý khách có thể xem và chọn các sản phẩm tương tự khác trên website hoặc liên hệ bộ phận hỗ trợ khách hàng của Mật Ngọt Bear để được tư vấn thêm.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Banner thông báo Đang chờ duyệt hủy đơn hàng --}}
        @if($order->hasPendingCancelRequest())
            <div class="mb-6 bg-gradient-to-r from-amber-500/10 via-amber-100/70 to-amber-500/10 border-2 border-amber-400 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-amber-500/30">
                            ⏳
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-base font-black text-[#2C1408]">Đang chờ nhân viên xác nhận hủy đơn</h4>
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px]">CHỜ DUYỆT HỦY</span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1">
                                Yêu cầu gửi lúc: <strong>{{ $order->cancel_requested_at?->format('d/m/Y H:i') }}</strong>
                                · Lý do: <em class="text-[#2C1408] font-medium">"{{ $order->cancel_request_reason }}"</em>
                            </p>
                            <div class="mt-2 text-xs font-semibold text-amber-900 bg-amber-200/60 rounded-xl px-3 py-1.5 flex items-center gap-2 border border-amber-300">
                                <i class="fa-regular fa-clock text-amber-700"></i>
                                <span>Thời hạn nhân viên xử lý: <strong>còn {{ $order->cancelRequestTimeRemainingText() }}</strong> (tối đa 24h kể từ khi gửi yêu cầu). Nếu quá 24h chưa được duyệt, yêu cầu sẽ tự động hết hạn và đơn hàng tiếp tục được chuẩn bị để giao cho bạn.</span>
                            </div>
                            @if($order->payment_status === 'PAID')
                                <div class="mt-2.5 inline-flex items-center gap-2 p-2.5 bg-white/80 rounded-xl border border-amber-300 text-xs text-amber-900">
                                    <i class="fa-solid fa-circle-info text-amber-600"></i>
                                    <span>Đơn hàng đã thanh toán. Nhân viên sẽ liên hệ qua SĐT <strong>{{ $order->recipient_phone }}</strong> để lấy STK và hoàn lại <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> cho bạn.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('customer.orders.withdraw_cancel', $order) }}" method="POST" 
                          onsubmit="return confirm('Bạn có chắc chắn muốn rút lại yêu cầu hủy và tiếp tục nhận đơn hàng này?')" 
                          class="shrink-0 w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-white hover:bg-amber-50 text-[#8C4A19] font-bold text-xs rounded-xl border border-amber-300 shadow-2xs transition cursor-pointer">
                            Rút lại yêu cầu hủy
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Banner thông báo nếu yêu cầu hủy bị từ chối --}}
        @if($order->isCancelRejected())
            <div class="mb-6 bg-rose-50 border-2 border-rose-200 rounded-2xl p-5 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                    ⚠️
                </div>
                <div>
                    <h4 class="text-sm font-bold text-rose-900">Yêu cầu hủy đơn hàng không được chấp thuận</h4>
                    <p class="text-xs text-rose-700 mt-0.5">
                        Lý do từ chối: <strong class="font-medium">{{ $order->cancel_rejection_reason }}</strong>
                    </p>
                    <p class="text-[11px] text-[#786B61] mt-1">Đơn hàng của bạn đang tiếp tục được xử lý và giao theo đúng tiến trình.</p>
                </div>
            </div>
        @endif

        @if($canPayOnline)
            <div class="mb-6 bg-gradient-to-r from-amber-500/10 via-amber-500/15 to-amber-500/10 border-2 border-amber-400 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm"
                 x-data="{ showPaymentModal: false }">
                <div class="flex items-center gap-3.5 w-full md:w-auto">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-amber-500/30">
                        💳
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-sm sm:text-base font-extrabold text-[#2B1810]">Đơn hàng này chưa hoàn tất thanh toán</h4>
                            <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-bold whitespace-nowrap">
                                @if($order->payment_method === 'CARD') Cổng VNPAY @elseif($order->payment_method === 'BANK_TRANSFER') Chuyển khoản VietQR @else Trực tuyến @endif
                            </span>
                        </div>
                        <p class="text-xs text-[#7D6B5D] mt-0.5">Số tiền cần thanh toán: <strong class="text-amber-800 font-bold text-sm">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></p>
                        @if($order->paymentExpiresAt())
                            <p class="text-[11px] text-amber-800 font-medium mt-1 flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-amber-600"></i>
                                <span>Hạn thanh toán: <strong>{{ $order->paymentExpiresAt()->format('H:i - d/m/Y') }}</strong> (tự động hủy sau 24h đặt hàng)</span>
                            </p>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center gap-2.5 w-full md:w-auto shrink-0 justify-end">
                    <button type="button" id="btn-top-pay" @click="showPaymentModal = true"
                            class="w-full md:w-auto whitespace-nowrap px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/25 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-credit-card text-xs"></i>
                        <span class="whitespace-nowrap">THANH TOÁN NGAY</span>
                    </button>
                </div>

                {{-- Modal lựa chọn thanh toán --}}
                <div x-show="showPaymentModal" 
                     x-cloak 
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs text-left"
                     @click.self="showPaymentModal = false"
                     @keydown.escape.window="showPaymentModal = false"
                     style="display: none;">
                    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-amber-200 space-y-5"
                         @click.stop>
                        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#8C7A6B]">Thanh Toán Đơn Hàng</span>
                                <h3 class="font-black text-lg text-[#2C1408] mt-0.5">
                                    Đơn hàng <span class="text-[#E08A1E] font-mono">#{{ $order->order_code }}</span>
                                </h3>
                            </div>
                            <button type="button" @click="showPaymentModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        {{-- Order Summary Pill --}}
                        <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between">
                            <span class="text-xs font-semibold text-[#5C3219]">Tổng tiền cần thanh toán:</span>
                            <span class="text-lg font-black text-amber-700">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                        </div>

                        @php
                            $currentPayUrl = match ($order->payment_method) {
                                'CARD' => route('customer.payment.vnpay.redirect', $order),
                                default => route('customer.payment.qr', $order),
                            };
                            $currentMethodInfo = match ($order->payment_method) {
                                'CARD' => ['name' => 'Cổng thanh toán VNPAY', 'desc' => 'Thẻ ATM nội địa, Visa, Mastercard, VNPAY-QR', 'icon' => '💳'],
                                default => ['name' => 'Chuyển khoản VietQR', 'desc' => 'Quét mã QR qua mọi app ngân hàng 24/7', 'icon' => '🏦'],
                            };
                        @endphp

                        {{-- Tùy chọn 1: Tiếp tục với phương thức đã chọn --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-extrabold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                    <span>Tiếp tục với phương thức đã chọn:</span>
                                </span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">ĐÃ CHỌN</span>
                            </div>
                            
                            <a href="{{ $currentPayUrl }}"
                               class="w-full p-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white shadow-md shadow-emerald-600/25 transition transform hover:-translate-y-0.5 flex items-center justify-between group cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center text-xl shrink-0">
                                        {{ $currentMethodInfo['icon'] }}
                                    </div>
                                    <div class="text-left">
                                        <div class="font-extrabold text-sm sm:text-base flex items-center gap-2">
                                            <span>{{ $currentMethodInfo['name'] }}</span>
                                        </div>
                                        <div class="text-xs text-white/85 mt-0.5">{{ $currentMethodInfo['desc'] }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 font-bold text-xs bg-white/20 px-3 py-1.5 rounded-xl group-hover:bg-white/30 transition shrink-0 ml-2">
                                    <span>Thanh toán</span>
                                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                </div>
                            </a>
                        </div>

                        {{-- Divider --}}
                        <div class="relative flex py-1 items-center">
                            <div class="flex-grow border-t border-gray-200"></div>
                            <span class="flex-shrink mx-3 text-[11px] font-bold text-[#8C7A6B] uppercase tracking-wider bg-white px-2">Hoặc đổi sang phương thức khác</span>
                            <div class="flex-grow border-t border-gray-200"></div>
                        </div>

                        {{-- Tùy chọn 2: Đổi sang phương thức khác --}}
                        <form action="{{ route('customer.payment.retry', $order->id) }}" method="POST" class="space-y-2">
                            @csrf
                            
                            @if ($order->payment_method !== 'BANK_TRANSFER')
                                <button type="submit" name="payment_method" value="BANK_TRANSFER"
                                        class="w-full p-3 rounded-2xl border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-105 transition">
                                            🏦
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Đổi sang Chuyển khoản VietQR (MB Bank)</div>
                                            <div class="text-[11px] text-[#786B61]">Quét mã QR tự động qua mọi app ngân hàng</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-100/60 px-2.5 py-1 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition">Chọn</span>
                                </button>
                            @endif

                            @if ($order->payment_method !== 'CARD')
                                <button type="submit" name="payment_method" value="VNPAY"
                                        class="w-full p-3 rounded-2xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-105 transition">
                                            💳
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Đổi sang Cổng VNPAY</div>
                                            <div class="text-[11px] text-[#786B61]">Thẻ ATM nội địa, Visa, Mastercard, VNPAY-QR</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-blue-700 bg-blue-100/60 px-2.5 py-1 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition">Chọn</span>
                                </button>
                            @endif

                            @if ($order->payment_method !== 'COD')
                                <button type="submit" name="payment_method" value="COD"
                                        class="w-full p-3 rounded-2xl border border-gray-200 hover:border-amber-500 hover:bg-amber-50/40 bg-white flex items-center justify-between text-left transition group cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-105 transition">
                                            💵
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-[#2C1408]">Đổi sang Thanh toán khi nhận hàng (COD)</div>
                                            <div class="text-[11px] text-[#786B61]">Nhận hàng và thanh toán tiền mặt cho bưu tá</div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-amber-700 bg-amber-100/60 px-2.5 py-1 rounded-lg group-hover:bg-amber-600 group-hover:text-white transition">Chọn</span>
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delivery Confirmation Banner when order is SHIPPING (Compact version) --}}
        @if($order->order_status === 'SHIPPING')
            <div class="mb-4 bg-gradient-to-r from-emerald-500/10 via-teal-500/15 to-emerald-500/10 border border-emerald-400/80 rounded-2xl py-3 px-4 sm:px-5 flex flex-col md:flex-row items-center justify-between gap-3 shadow-2xs"
                 x-data="{ showNotReceivedModal: false }">
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                        🚚
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-xs sm:text-sm font-extrabold text-[#1B4332]">Đơn hàng đang được giao đến bạn</h4>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[10px]">ĐANG GIAO HÀNG</span>
                        </div>
                        <p class="text-[11px] text-[#2D6A4F] mt-0.5">Kiện hàng đang trên đường giao đến bạn</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 justify-end flex-wrap sm:flex-nowrap">
                    {{-- Nút 1: Đã nhận được hàng (Chưa bật sáng - chỉ bật sáng khi nhân viên xác nhận đã giao) --}}
                    <form action="{{ route('customer.orders.complete', $order->id) }}" method="POST" class="w-full sm:w-auto shrink-0">
                        @csrf
                        <button type="button" disabled 
                                title="Nút sẽ được bật sáng khi nhân viên xác nhận đã giao hàng thành công"
                                class="w-full sm:w-auto px-3.5 py-2 bg-gray-100 text-gray-400 font-bold text-xs rounded-xl border border-gray-200 cursor-not-allowed opacity-75 shadow-none flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-xs text-gray-300"></i>
                            <span>ĐÃ NHẬN ĐƯỢC HÀNG</span>
                        </button>
                    </form>

                    {{-- Nút 2: Chưa nhận được hàng --}}
                    <button type="button" @click="showNotReceivedModal = true"
                            class="w-full sm:w-auto px-3 py-2 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 font-bold text-xs rounded-xl border border-rose-200 hover:border-rose-300 shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-circle-question text-rose-500 text-xs"></i>
                        <span>CHƯA NHẬN ĐƯỢC HÀNG</span>
                    </button>
                </div>

                {{-- Modal Khiếu Nại / Hướng Dẫn Chưa Nhận Được Hàng --}}
                <div x-show="showNotReceivedModal" 
                     x-cloak 
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs text-left"
                     @click.self="showNotReceivedModal = false"
                     @keydown.escape.window="showNotReceivedModal = false"
                     style="display: none;">
                    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-rose-200 space-y-4"
                         @click.stop>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2.5 text-rose-700">
                                <span class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center text-lg shrink-0">
                                    ⚠️
                                </span>
                                <div>
                                    <h3 class="font-black text-base text-[#2C1408]">Bạn chưa nhận được hàng?</h3>
                                    <p class="text-xs text-[#786B61]">Đơn hàng: <strong class="text-amber-800 font-mono">#{{ $order->order_code }}</strong></p>
                                </div>
                            </div>
                            <button type="button" @click="showNotReceivedModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-gray-800 flex items-center justify-center transition">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        <div class="space-y-3 text-xs text-[#5C3219] leading-relaxed">
                            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200">
                                <div class="font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-lightbulb text-amber-600"></i>
                                    <span>Gợi ý kiểm tra nhanh:</span>
                                </div>
                                <ul class="list-disc list-inside space-y-1 text-[#786B61] text-[11px]">
                                    <li>Kiểm tra xem người thân, bạn cùng phòng, lễ tân hoặc bảo vệ đã nhận hộ chưa.</li>
                                    <li>Bưu tá có thể đang trên đường tới hoặc vừa gọi nhỡ cho bạn.</li>
                                </ul>
                            </div>

                            <p class="text-[#786B61]">
                                Nếu bạn vẫn chưa nhận được hàng hoặc bưu tá không liên hệ, đừng lo lắng! Đội ngũ <strong>Mật Ngọt Bear</strong> sẽ trực tiếp làm việc với đơn vị vận chuyển để kiểm tra định vị và hỗ trợ giao lại ngay.
                            </p>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                            <a href="tel:0377466205" 
                               class="flex-1 py-3 px-4 rounded-xl bg-[#E08A1E] hover:bg-[#C2751D] text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-phone"></i>
                                <span>Gọi Hotline: 0377.466.205</span>
                            </a>
                            <button type="button" @click="showNotReceivedModal = false"
                                    class="py-3 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer">
                                Đã hiểu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Banner Đang chờ xử lý yêu cầu Trả hàng / Hoàn tiền --}}
        @if($order->hasPendingReturnRequest())
            <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-orange-50 to-amber-500/15 border-2 border-amber-300 rounded-2xl p-5 sm:p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-amber-500/30">
                        ⏳
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base font-black text-[#2C1408]">Đang chờ Shop xử lý yêu cầu Trả hàng / Hoàn tiền</h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px]">CHỜ DUYỆT TRẢ HÀNG</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#7D6B5D]">
                            Yêu cầu gửi lúc: <strong>{{ $order->return_requested_at?->format('d/m/Y H:i') }}</strong>
                            <br>Lý do: <em>"{{ $order->return_request_reason }}"</em>
                        </p>
                        <p class="text-xs text-amber-800 font-medium">Nhân viên CSKH của Mật Ngọt Bear sẽ liên hệ qua SĐT <strong>{{ $order->recipient_phone }}</strong> để hỗ trợ bạn sớm nhất.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Banner Đã giao hàng - Chờ khách xác nhận hoặc yêu cầu trả hàng --}}
        @if($order->isDeliveredWaitingConfirmation() && ! $order->hasPendingReturnRequest())
            <div class="mb-6 bg-gradient-to-r from-emerald-500/15 via-teal-50 to-emerald-500/15 border-2 border-emerald-400 rounded-2xl p-5 sm:p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4"
                 x-data="{ openReturnModal: false }">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-emerald-600/30">
                        📦
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base font-black text-[#1B4332]">Đơn hàng đã được giao thành công đến bạn!</h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px]">CHỜ BẠN XÁC NHẬN</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#2D6A4F] mt-0.5">
                            Vui lòng kiểm tra sản phẩm. Bấm <strong>"Đã nhận được hàng"</strong> để hoàn tất và mở khóa Đánh giá, hoặc bấm <strong>"Yêu cầu Trả hàng / Hoàn tiền"</strong> nếu có sự cố.
                        </p>
                        @if($order->autoConfirmDeadline())
                            <p class="text-[11px] text-amber-900 font-medium mt-1 flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-amber-700"></i>
                                <span>Hệ thống sẽ tự động xác nhận <strong>"Đã nhận hàng"</strong> vào <strong>{{ $order->autoConfirmDeadline()->format('H:i - d/m/Y') }}</strong> (sau 7 ngày kể từ khi giao hàng).</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2.5 w-full md:w-auto shrink-0 justify-end flex-wrap sm:flex-nowrap">
                    {{-- Nút 1: Đã nhận được hàng --}}
                    <form action="{{ route('customer.orders.confirm_received', $order->id) }}" method="POST" class="w-full sm:w-auto shrink-0">
                        @csrf
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-circle-check text-base"></i>
                            <span>ĐÃ NHẬN ĐƯỢC HÀNG</span>
                        </button>
                    </form>

                    {{-- Nút 2: Yêu cầu Trả hàng / Hoàn tiền --}}
                    <button type="button" @click="openReturnModal = true"
                            class="w-full sm:w-auto px-4 py-3 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 font-bold text-xs sm:text-sm rounded-xl border border-rose-200 hover:border-rose-300 shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrow-rotate-left text-rose-500"></i>
                        <span>HOÀN HÀNG / ĐỔI TRẢ</span>
                    </button>
                </div>

                @include('customer.orders.partials.return-request-modal', ['order' => $order])
            </div>
        @endif

        {{-- Completed Banner with Direct Review Button --}}
        @if($order->order_status === 'COMPLETED' && $order->isCustomerConfirmed())
            <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-[#FFF4DE] to-amber-500/15 border-2 border-amber-300 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-amber-500/30">
                        🎉
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-base font-black text-[#2C1408]">Đơn hàng đã được giao hoàn tất thành công!</h4>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[11px]">ĐÃ HOÀN TẤT</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#786B61] mt-0.5">Bạn cảm nhận thế nào về bé gấu bông? Hãy để lại vài lời đánh giá cho shop nhé!</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0 justify-end flex-wrap sm:flex-nowrap">
                    <a href="{{ route('customer.orders.review', $order) }}" 
                       data-open-order-review-modal
                       data-order-id="{{ $order->id }}"
                       class="w-full sm:w-auto px-6 py-3.5 bg-gradient-to-r from-[#E08A1E] to-[#C2751D] hover:from-[#C2751D] hover:to-[#A35E14] text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md shadow-amber-600/25 transition transform hover:-translate-y-0.5 active:translate-y-0 text-center uppercase tracking-wide flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-star text-amber-200"></i>
                        <span>ĐÁNH GIÁ SẢN PHẨM</span>
                    </a>

                    <form action="{{ route('customer.orders.reorder', $order->id) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-3.5 bg-white hover:bg-amber-50 text-[#8C4A19] font-bold text-xs sm:text-sm rounded-xl border border-amber-300 shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-cart-arrow-down"></i>
                            <span>MUA LẠI</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- 1. Tiến Trình Đơn Hàng --}}
        <div class="panel-card mb-6">
            <div class="panel-header flex items-center justify-between flex-wrap gap-2">
                <div class="panel-title">
                    <i class="fa-solid fa-timeline"></i>
                    <span>Tiến trình đơn hàng</span>
                </div>
            </div>
            <div class="py-2">
                <x-order-timeline :status="$order->order_status" />
            </div>
        </div>

        {{-- 2. Grid Nội Dung Chính --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Cột Trái (2 Cols) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Thông tin người nhận & vận chuyển --}}
                <div class="panel-card mb-0" x-data="{ showEditAddressModal: false }">
                    <div class="panel-header flex items-center justify-between">
                        <div class="panel-title">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Thông tin nhận hàng</span>
                        </div>
                        @if($order->order_status === 'PENDING')
                            <button type="button" @click="showEditAddressModal = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-xl transition cursor-pointer shadow-2xs hover:scale-102">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Đổi địa chỉ</span>
                            </button>
                        @endif
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                            <dt class="text-xs font-bold text-[#8E8076] uppercase">Người nhận</dt>
                            <dd class="font-extrabold text-[#4E342E] text-base mt-0.5">{{ $order->recipient_name }}</dd>
                        </div>
                        <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                            <dt class="text-xs font-bold text-[#8E8076] uppercase">Số điện thoại</dt>
                            <dd class="font-extrabold text-[#4E342E] text-base mt-0.5">{{ $order->recipient_phone }}</dd>
                            <span class="text-[11px] text-[#8E8076]">Liên hệ khi giao hàng</span>
                        </div>
                        <div class="sm:col-span-2 p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                            <dt class="text-xs font-bold text-[#8E8076] uppercase">Địa chỉ</dt>
                            <dd class="font-bold text-[#4E342E] mt-0.5 leading-relaxed">{{ $order->recipient_address }}</dd>
                        </div>
                        <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                            <dt class="text-xs font-bold text-[#8E8076] uppercase">Hình thức giao hàng</dt>
                            <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->shipping_method_label }}</dd>
                            @if ($order->shipped_at)
                                <span class="text-[11px] text-[#8E8076]">Bắt đầu giao: {{ $order->shipped_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                            <dt class="text-xs font-bold text-[#8E8076] uppercase">Thời gian đặt</dt>
                            <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        @if ($order->note)
                            <div class="sm:col-span-2 p-3 bg-amber-100/40 rounded-xl border border-amber-200/80">
                                <dt class="text-xs font-bold text-[#8B5A2B] uppercase">Ghi chú cho shipper</dt>
                                <dd class="text-[#4E342E] font-medium mt-0.5 italic">"{{ $order->note }}"</dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Modal đổi địa chỉ nhận hàng --}}
                    @if($order->order_status === 'PENDING')
                    <div x-show="showEditAddressModal" 
                         x-cloak 
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs text-left"
                         @click.self="showEditAddressModal = false"
                         @keydown.escape.window="showEditAddressModal = false"
                         style="display: none;">
                        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-amber-200 space-y-4"
                             @click.stop>
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div>
                                    <h3 class="font-black text-lg text-[#2C1408] flex items-center gap-2">
                                        <span class="text-xl">📍</span> Thay đổi địa chỉ nhận hàng
                                    </h3>
                                    <p class="text-xs text-[#786B61] mt-0.5">Đơn hàng #{{ $order->order_code }} (Đang chờ nhân viên xác nhận)</p>
                                </div>
                                <button type="button" @click="showEditAddressModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition">
                                    <i class="fa-solid fa-xmark text-base"></i>
                                </button>
                            </div>

                            <form action="{{ route('customer.orders.update_shipping_address', $order->id) }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-xs font-bold text-[#4A3B32] mb-1">Tên người nhận <span class="text-rose-500">*</span></label>
                                    <input type="text" name="recipient_name" value="{{ old('recipient_name', $order->recipient_name) }}" required
                                           class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#4A3B32] mb-1">Số điện thoại liên hệ <span class="text-rose-500">*</span></label>
                                    <input type="tel" name="recipient_phone" value="{{ old('recipient_phone', $order->recipient_phone) }}" required
                                           class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#4A3B32] mb-1">Địa chỉ giao hàng chi tiết <span class="text-rose-500">*</span></label>
                                    <textarea name="recipient_address" rows="3" required
                                              placeholder="Số nhà, tên đường, thôn xóm, phường/xã, quận/huyện, tỉnh/thành..."
                                              class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">{{ old('recipient_address', $order->recipient_address) }}</textarea>
                                    <p class="text-[11px] text-[#8C4A19] mt-1">💡 Hệ thống sẽ tự động làm sạch và định dạng địa chỉ chuẩn đẹp.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#4A3B32] mb-1">Ghi chú cho shipper (Tùy chọn)</label>
                                    <input type="text" name="note" value="{{ old('note', $order->note) }}"
                                           placeholder="VD: Gọi trước khi giao, giao giờ hành chính..."
                                           class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                                    <button type="button" @click="showEditAddressModal = false"
                                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-[#64748B] hover:bg-gray-100 transition cursor-pointer">
                                        Hủy bỏ
                                    </button>
                                    <button type="submit"
                                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#E08A1E] to-[#8C4A19] hover:from-[#C77815] hover:to-[#733C14] shadow-md shadow-[#E08A1E]/30 transition cursor-pointer">
                                        Lưu thay đổi địa chỉ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Danh sách sản phẩm đặt mua --}}
                <div class="panel-card mb-0">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span>Sản phẩm đã đặt</span>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>SẢN PHẨM</th>
                                    <th class="text-right">ĐƠN GIÁ</th>
                                    <th class="text-center">SỐ LƯỢNG</th>
                                    <th class="text-right">THÀNH TIỀN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->details as $detail)
                                    @php
                                        $rawImg = $detail->variant_image_url
                                            ?: ($detail->variant?->image_url
                                                ?? $detail->product?->images?->where('is_primary', true)->first()?->image_url
                                                ?? $detail->product?->images?->first()?->image_url);
                                        $primaryImg = $rawImg ? ((str_starts_with($rawImg, 'http') || str_starts_with($rawImg, 'data:')) ? $rawImg : asset($rawImg)) : '';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @if ($primaryImg)
                                                    <img src="{{ $primaryImg }}"
                                                         alt="{{ $detail->product_name }}"
                                                         class="w-12 h-12 object-cover rounded-xl border border-amber-200/70 bg-white shrink-0 shadow-2xs"
                                                         onerror="this.src='https://placehold.co/100x100/f5e6ca/7c4a2d?text=Bear'">
                                                @else
                                                    <div class="w-12 h-12 rounded-xl border border-amber-200/70 bg-amber-100/70 text-amber-800 font-bold flex items-center justify-center shrink-0 text-xl shadow-2xs">
                                                        🧸
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="font-bold text-[#4E342E]">{{ $detail->product_name }}</div>
                                                    @php
                                                        $variantLabel = $detail->variant_display;
                                                        if (empty($variantLabel) || $variantLabel === 'Phân loại tiêu chuẩn') {
                                                            $vParts = array_filter([
                                                                $detail->variant_size ? 'Size: '.$detail->variant_size : null,
                                                                $detail->variant_color ? 'Màu: '.$detail->variant_color : null,
                                                            ]);
                                                            $variantLabel = !empty($vParts) ? implode(' · ', $vParts) : null;
                                                        }
                                                        $sku = $detail->variant_sku ?: $detail->variant?->sku;
                                                    @endphp
                                                    @if ($variantLabel)
                                                        <div class="mt-0.5 flex items-center flex-wrap gap-1">
                                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#9A4A0A] bg-[#FFF3DD] border border-[#FDE68A] px-2 py-0.5 rounded shadow-2xs">
                                                                <span>✨</span>
                                                                <span>{{ $variantLabel }}</span>
                                                            </span>
                                                            @if ($sku)
                                                                <span class="text-[10px] text-gray-400 font-mono">({{ $sku }})</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right text-[#795548] font-medium">{{ number_format($detail->product_price, 0, ',', '.') }} đ</td>
                                        <td class="text-center font-bold text-[#4E342E]">{{ $detail->quantity }}</td>
                                        <td class="text-right font-extrabold text-[#4E342E]">{{ number_format($detail->line_total, 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tóm tắt chi phí --}}
                    <div class="mt-4 p-4 bg-amber-50/60 rounded-xl border border-amber-100">
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between text-[#795548]">
                                <dt>Tiền hàng (Tạm tính)</dt>
                                <dd class="font-bold text-[#4E342E]">{{ number_format($order->subtotal, 0, ',', '.') }} đ</dd>
                            </div>
                            <div class="flex justify-between text-[#795548]">
                                <dt>Phí vận chuyển</dt>
                                <dd class="font-bold text-[#4E342E]">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</dd>
                            </div>
                            @if (($order->shipping_discount_amount ?? 0) > 0)
                                <div class="flex justify-between text-teal-700">
                                    <dt>
                                        Giảm phí vận chuyển
                                        @if ($order->shippingVoucher)
                                            <span class="px-1.5 py-0.5 bg-teal-100 text-teal-800 rounded font-bold text-xs">{{ $order->shippingVoucher->code }}</span>
                                        @endif
                                    </dt>
                                    <dd class="font-bold">-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }} đ</dd>
                                </div>
                            @endif
                            @if ($order->discount_amount > 0)
                                <div class="flex justify-between text-rose-700">
                                    <dt>
                                        Giảm giá voucher
                                        @if ($order->voucher)
                                            <span class="px-1.5 py-0.5 bg-rose-100 text-rose-800 rounded font-bold text-xs">{{ $order->voucher->code }}</span>
                                        @endif
                                    </dt>
                                    <dd class="font-bold">-{{ number_format($order->discount_amount, 0, ',', '.') }} đ</dd>
                                </div>
                            @endif
                            <div class="flex justify-between items-center text-base pt-3 border-t border-amber-200">
                                <dt class="font-black text-[#4E342E]">Tổng Thanh Toán</dt>
                                <dd class="font-black text-xl text-amber-700">{{ number_format($order->total_amount, 0, ',', '.') }} đ</dd>
                            </div>
                            <div class="pt-3 mt-3 border-t border-dashed border-amber-200/80 flex items-center justify-between">
                                <span class="text-xs text-[#786B61] flex items-center gap-1.5">
                                    <i class="fa-solid fa-receipt text-amber-500"></i>
                                    <span>Chứng từ đơn hàng:</span>
                                </span>
                                <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-[#8C4A19] font-bold text-xs border border-amber-200 shadow-2xs transition">
                                    <i class="fa-solid fa-file-invoice text-amber-600"></i>
                                    <span>Xem hóa đơn điện tử</span>
                                </a>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Thông tin thanh toán & Lịch sử giao dịch --}}
                <div class="panel-card mb-0">
                    <div class="panel-header">
                        <div class="panel-title">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Thông tin thanh toán</span>
                        </div>
                    </div>

                    @if ($order->payments && $order->payments->isNotEmpty())
                        <div class="table-container mb-2">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Phương Thức</th>
                                        <th class="text-right">Số Tiền</th>
                                        <th>Trạng Thái</th>
                                        <th>Mã Giao Dịch</th>
                                        <th>Thời Gian Thanh Toán</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->payments as $payment)
                                        <tr>
                                            <td class="font-bold text-[#4E342E]">
                                                <span class="inline-flex items-center gap-1.5">
                                                    @if($payment->method === 'CARD')
                                                        <i class="fa-solid fa-credit-card text-blue-600"></i>
                                                    @elseif($payment->method === 'BANK_TRANSFER')
                                                        <i class="fa-solid fa-building-columns text-emerald-600"></i>
                                                    @elseif($payment->method === 'COD')
                                                        <i class="fa-solid fa-money-bill-wave text-amber-600"></i>
                                                    @else
                                                        <i class="fa-solid fa-wallet text-purple-600"></i>
                                                    @endif
                                                    <span>{{ $payment->method_label }}</span>
                                                </span>
                                            </td>
                                            <td class="text-right font-extrabold text-amber-700">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                                            <td><x-payment-status-badge :status="$payment->status" :method="$payment->method" /></td>
                                            <td class="text-xs text-[#795548] font-mono">
                                                @if ($payment->status === 'PAID' && $payment->transaction_ref)
                                                    {{ $payment->transaction_ref }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="text-xs">
                                                @if ($payment->paid_at)
                                                    <span class="font-medium text-[#4E342E]">{{ $payment->paid_at->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-gray-400 italic">Chưa thanh toán</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
                            <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                                <dt class="text-xs font-bold text-[#8E8076] uppercase">Phương thức thanh toán</dt>
                                <dd class="font-bold text-[#4E342E] mt-0.5">
                                    {{ match ($order->payment_method) {
                                        'COD' => 'Thanh toán khi nhận hàng (COD)',
                                        'BANK_TRANSFER' => 'Chuyển khoản VietQR (MB Bank)',
                                        'CARD' => 'Cổng thanh toán VNPAY',
                                        'E_WALLET' => 'Ví điện tử',
                                        default => $order->payment_method,
                                    } }}
                                </dd>
                            </div>
                            <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                                <dt class="text-xs font-bold text-[#8E8076] uppercase">Trạng thái thanh toán</dt>
                                <dd class="mt-0.5"><x-payment-status-badge :status="$order->payment_status" /></dd>
                            </div>
                        </div>
                    @endif

                    @if ($order->payment_method === 'COD')
                        <div class="mt-3 p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 flex items-center gap-3 text-xs text-[#786B61]">
                            <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-base shrink-0 font-bold">💵</span>
                            <div>
                                <div class="font-bold text-[#2C1408] text-xs sm:text-sm">Thanh toán khi nhận hàng (COD)</div>
                                <div class="mt-0.5">Đơn hàng thu COD cố định. Quý khách vui lòng chuẩn bị <strong class="text-amber-800 font-bold">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> tiền mặt để gửi cho bưu tá khi nhận hàng.</div>
                            </div>
                        </div>
                    @elseif($canPayOnline)
                        <div class="mt-3 p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <span class="text-xs text-amber-800 font-semibold flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                Đang chờ thanh toán {{ number_format($order->total_amount, 0, ',', '.') }}đ
                            </span>
                            <button type="button" onclick="const btn = document.querySelector('#btn-top-pay'); if(btn) { btn.scrollIntoView({behavior: 'smooth', block: 'center'}); btn.click(); }"
                                    class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Thanh toán ngay</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cột Phải (1 Col) --}}
            <div class="space-y-6">
                {{-- Lịch sử trạng thái / Nhật ký đơn hàng --}}
                <div class="panel-card mb-0">
                    <div class="panel-header flex items-center justify-between">
                        <div class="panel-title">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>Lịch sử trạng thái</span>
                        </div>
                        <x-order-status-badge :order="$order" />
                    </div>

                    <ol class="relative border-l-2 border-amber-200 ml-3 space-y-5 my-2">
                        @forelse ($order->statusHistories->sortBy('changed_at') as $history)
                            @php
                                $displayTitle = $history->display_title;
                                $isCancelEvent = str_contains($displayTitle, 'hủy') || str_contains($displayTitle, 'huỷ');
                                $isRejectEvent = str_contains($displayTitle, 'Từ chối');
                            @endphp
                            <li class="ml-5">
                                <span class="absolute flex items-center justify-center w-5 h-5 rounded-full -left-2.5 ring-4 ring-white 
                                    @if($isRejectEvent) bg-rose-100 text-rose-700
                                    @elseif($isCancelEvent) bg-amber-100 text-amber-800
                                    @elseif($loop->last) bg-amber-500 text-white
                                    @else bg-amber-200 text-amber-800 @endif">
                                    <i class="fa-solid {{ $history->display_icon }}"></i>
                                </span>
                                <div class="text-xs font-extrabold text-[#4E342E]">
                                    {{ $displayTitle }}
                                </div>
                                <div class="text-[11px] text-[#8E8076] mt-0.5">
                                    {{ $history->changed_at->format('d/m/Y H:i') }}
                                </div>
                                @if ($history->note)
                                    <div class="text-xs text-[#795548] mt-1 p-2 bg-amber-50/50 border border-amber-100 rounded-lg leading-relaxed">
                                        {{ $history->note }}
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="ml-5 text-xs text-[#8E8076]">Chưa có cập nhật nào.</li>
                        @endforelse
                    </ol>

                    {{-- Customer Cancel Options --}}
                    @if ($order->canBeCancelledByCustomer())
                        @php
                            $isDirectCancel = $order->canCancelDirectly();
                        @endphp
                        <div class="mt-6 pt-4 border-t border-amber-100" x-data="{ 
                            open: false, 
                            selectedReason: '', 
                            customReason: '',
                            setReason(val) {
                                this.selectedReason = val;
                                if (val !== 'Lý do khác') {
                                    this.customReason = val;
                                } else {
                                    this.customReason = '';
                                }
                            }
                        }">
                            <button @click="open = true"
                                    class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-ban"></i>
                                <span>{{ $isDirectCancel ? 'Hủy đơn hàng' : 'Yêu cầu hủy đơn hàng' }}</span>
                            </button>

                            {{-- Modal Hủy / Yêu Cầu Hủy Đơn Hàng --}}
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="open = false"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    
                                    <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-amber-200"
                                         @click.stop>
                                        <form method="POST" action="{{ route($isDirectCancel ? 'customer.orders.cancel' : 'customer.orders.request_cancel', $order) }}">
                                            @csrf
                                            
                                            {{-- Modal Header --}}
                                            <div class="p-6 bg-gradient-to-b from-[#FAF6EE] to-white border-b border-amber-100 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg shrink-0">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-black text-[#2B1810]">{{ $isDirectCancel ? 'Xác nhận hủy đơn hàng' : 'Yêu cầu hủy đơn hàng' }}</h3>
                                                        <p class="text-xs text-[#7D6B5D]">Mã đơn: <strong class="text-amber-800 font-mono">#{{ $order->order_code }}</strong></p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="open = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-700 flex items-center justify-center transition">
                                                    <i class="fa-solid fa-xmark text-sm"></i>
                                                </button>
                                            </div>

                                            {{-- Modal Body --}}
                                            <div class="p-6 space-y-4 text-xs">
                                                {{-- Cảnh báo thanh toán & hoàn tiền --}}
                                                @php
                                                    $isPendingPaidDirectToAdmin = ($order->order_status === 'PENDING' && $order->payment_status === 'PAID');
                                                @endphp

                                                @if($isPendingPaidDirectToAdmin)
                                                    <div class="p-3.5 bg-sky-50 rounded-2xl border border-sky-200 space-y-2">
                                                        <div class="flex items-center gap-2 text-sky-900 font-bold text-xs">
                                                            <i class="fa-solid fa-shield-halved text-sky-600"></i>
                                                            <span>Đơn trực tuyến đã thanh toán ({{ number_format($order->total_amount, 0, ',', '.') }}đ)</span>
                                                        </div>
                                                        <p class="text-[11.5px] text-[#5C3219] leading-relaxed">
                                                            Đơn hàng chưa được xác nhận. Yêu cầu hủy và hoàn tiền của bạn sẽ được <strong>gửi thẳng lên Quản trị viên (Admin)</strong> để xử lý chuyển khoản hoàn lại 100% số tiền theo thông tin tài khoản bạn cung cấp bên dưới.
                                                        </p>
                                                        <div class="pt-1 text-[11px] text-sky-800 font-medium">
                                                            💡 Vui lòng nhập số tài khoản ngân hàng để Admin hoàn tiền cho bạn:
                                                        </div>
                                                    </div>

                                                    {{-- Form STK hoàn tiền --}}
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 rounded-2xl border border-gray-200">
                                                        <div class="sm:col-span-2">
                                                            <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Tên ngân hàng <span class="text-rose-500">*</span></label>
                                                            <input type="text" name="refund_bank_name" required placeholder="Ví dụ: Vietcombank, MB Bank, Techcombank..."
                                                                   class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Số tài khoản <span class="text-rose-500">*</span></label>
                                                            <input type="text" name="refund_bank_account" required placeholder="Nhập số tài khoản..."
                                                                   class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Tên chủ tài khoản <span class="text-rose-500">*</span></label>
                                                            <input type="text" name="refund_account_holder" required placeholder="NGUYEN VAN A"
                                                                   class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500 uppercase">
                                                        </div>
                                                    </div>
                                                @elseif($order->payment_status === 'PAID')
                                                    <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 space-y-2">
                                                        <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                                                            <i class="fa-solid fa-wallet text-amber-600"></i>
                                                            <span>Đơn hàng đã thanh toán ({{ number_format($order->total_amount, 0, ',', '.') }}đ) - Đang chuẩn bị hàng</span>
                                                        </div>
                                                        <p class="text-[11.5px] text-[#7D6B5D] leading-relaxed">
                                                            Đơn hàng đang ở trạng thái <strong>Đang chuẩn bị</strong>. Cửa hàng sẽ kiểm tra: nếu đơn <strong>chưa giao cho bên vận chuyển</strong>, Shop sẽ chấp nhận hủy đơn và hoàn lại 100% tiền vào STK bạn cung cấp. Trường hợp hàng <strong>đã giao cho bên vận chuyển</strong>, Shop xin phép từ chối hủy.
                                                        </p>
                                                        <div class="pt-1 text-[11px] text-amber-800 font-medium">
                                                            💡 Vui lòng nhập STK ngân hàng nhận tiền hoàn nếu đơn được hủy thành công:
                                                        </div>
                                                    </div>

                                                    {{-- Form STK hoàn tiền --}}
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 rounded-2xl border border-gray-200">
                                                        <div class="sm:col-span-2">
                                                            <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Tên ngân hàng</label>
                                                            <input type="text" name="refund_bank_name" list="vietnam_banks_list" placeholder="Chọn hoặc nhập: Vietcombank, MB Bank, Techcombank..."
                                                                   class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Số tài khoản</label>
                                                            <input type="text" name="refund_bank_account" placeholder="Nhập số tài khoản..."
                                                                   class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Tên chủ tài khoản</label>
                                                            <input type="text" name="refund_account_holder" placeholder="NGUYEN VAN A"
                                                                   class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500 uppercase">
                                                        </div>
                                                    </div>

                                                    <datalist id="vietnam_banks_list">
                                                        <option value="MB Bank (Ngân hàng Quân Đội)">
                                                        <option value="Vietcombank (Ngoại thương)">
                                                        <option value="Techcombank">
                                                        <option value="VietinBank">
                                                        <option value="BIDV">
                                                        <option value="ACB">
                                                        <option value="VPBank">
                                                        <option value="TPBank">
                                                        <option value="Sacombank">
                                                        <option value="VIB">
                                                        <option value="HDBank">
                                                        <option value="MSB">
                                                        <option value="Agribank">
                                                    </datalist>
                                                @else
                                                    <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200 text-[#7D6B5D] text-[11.5px] leading-relaxed">
                                                        <i class="fa-solid fa-circle-info text-amber-600 mr-1"></i>
                                                        @if($isDirectCancel)
                                                            Đơn hàng đang chờ nhân viên xác nhận và chưa thanh toán. Đơn sẽ được <strong>hủy ngay lập tức</strong> sau khi bạn xác nhận.
                                                        @else
                                                            Đơn hàng đang ở trạng thái <strong>Đang chuẩn bị hàng</strong>. Cửa hàng sẽ kiểm tra: nếu đơn <strong>chưa giao cho bên vận chuyển</strong> thì Shop sẽ chấp nhận hủy đơn cho bạn. Nếu hàng <strong>đã giao cho bên vận chuyển</strong>, Shop xin phép từ chối hủy.
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- Lý do hủy đơn (Bắt buộc) --}}
                                                <div class="space-y-2">
                                                    <label class="block font-bold text-[#2B1810] text-xs">
                                                        Lý do hủy đơn <span class="text-rose-500">*</span>
                                                    </label>
                                                    
                                                    {{-- Quick choices --}}
                                                    <div class="grid grid-cols-1 gap-1.5">
                                                        @foreach([
                                                            'Muốn thay đổi địa chỉ nhận hàng',
                                                            'Muốn đổi sản phẩm khác / đổi mẫu',
                                                            'Thời gian giao hàng không phù hợp',
                                                            'Đổi ý, không còn nhu cầu mua nữa',
                                                            'Lý do khác'
                                                        ] as $preset)
                                                            <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-amber-50/70 border border-gray-200 cursor-pointer transition text-[11.5px]">
                                                                <input type="radio" name="preset_reason" value="{{ $preset }}" 
                                                                       @change="setReason('{{ $preset }}')"
                                                                       class="text-[#E08A1E] focus:ring-[#E08A1E]">
                                                                <span class="text-[#2B1810]">{{ $preset }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>

                                                    <div class="pt-1">
                                                        <textarea name="reason" rows="3" required x-model="customReason"
                                                                  placeholder="Vui lòng nhập chi tiết lý do bạn muốn hủy đơn hàng (bắt buộc)..."
                                                                  class="w-full rounded-xl border-gray-300 text-xs p-3 focus:border-amber-500 focus:ring-amber-500"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Modal Footer --}}
                                            <div class="bg-[#FAF6EE] px-6 py-4 flex items-center justify-end gap-2.5 border-t border-amber-100">
                                                <button type="button" @click="open = false" 
                                                        class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition cursor-pointer">
                                                    Đóng
                                                </button>
                                                <button type="submit" 
                                                        class="px-5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                                                    @if($isDirectCancel)
                                                        <i class="fa-solid fa-ban text-[10px]"></i>
                                                        <span>Xác nhận hủy đơn ngay</span>
                                                    @elseif($isPendingPaidDirectToAdmin)
                                                        <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                                        <span>Gửi yêu cầu hủy & hoàn tiền lên Admin</span>
                                                    @else
                                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                                        <span>Gửi yêu cầu hủy cho Shop</span>
                                                    @endif
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($order->hasPendingCancelRequest())
                        <div class="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-3">
                            <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
                                <i class="fa-solid fa-hourglass-half text-amber-600"></i>
                                <span>Đang chờ nhân viên xác nhận hủy</span>
                            </div>
                            <p class="text-[11.5px] text-[#7D6B5D] leading-relaxed">
                                Yêu cầu gửi lúc: {{ $order->cancel_requested_at?->format('d/m/Y H:i') }}
                                <br>Lý do: <em>"{{ $order->cancel_request_reason }}"</em>
                            </p>
                            @if($order->payment_status === 'PAID')
                                <div class="text-[11px] text-amber-800 bg-white/80 p-2 rounded-xl border border-amber-200">
                                    📞 Nhân viên sẽ sớm liên hệ SĐT <strong>{{ $order->recipient_phone }}</strong> để hoàn tiền.
                                </div>
                            @endif
                            <form method="POST" action="{{ route('customer.orders.withdraw_cancel', $order) }}"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn rút lại yêu cầu hủy?')">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-white hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl border border-amber-300 transition cursor-pointer">
                                    Rút lại yêu cầu hủy
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Lý do hủy đơn nếu có --}}
                @if ($order->cancel_reason)
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                        <h4 class="text-xs font-bold text-rose-800 uppercase mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Lý do hủy đơn hàng
                        </h4>
                        <p class="text-sm text-rose-700 font-medium">{{ $order->cancel_reason }}</p>
                    </div>
                @endif

                {{-- Lý do từ chối hủy nếu có --}}
                @if ($order->cancel_rejection_reason)
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl">
                        <h4 class="text-xs font-bold text-gray-800 uppercase mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-ban text-gray-600"></i>
                            Lý do từ chối yêu cầu hủy
                        </h4>
                        <p class="text-sm text-gray-700 font-medium">{{ $order->cancel_rejection_reason }}</p>
                    </div>
                @endif

                {{-- Quick Navigation Card --}}
                <div class="panel-card mb-0 space-y-3">
                    <h4 class="text-sm font-bold text-[#4E342E] mb-2 flex items-center gap-2">
                        <span>🧸</span>
                        <span>Mật Ngọt Bear Shop</span>
                    </h4>
                    <a href="{{ route('customer.messages.index', ['order_id' => $order->id]) }}"
                       class="w-full bg-amber-50 hover:bg-amber-100 text-[#8C4A19] font-bold py-2.5 px-4 rounded-xl border border-amber-300 text-xs flex items-center justify-center gap-2 transition shadow-2xs">
                        <i class="fa-regular fa-comment-dots text-sm text-[#E08A1E]"></i>
                        <span>Chat với Shop</span>
                    </a>
                    <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank"
                       class="w-full bg-white hover:bg-amber-50 text-[#8C4A19] font-bold py-2.5 px-4 rounded-xl border border-amber-300 text-xs flex items-center justify-center gap-2 transition shadow-xs">
                        <i class="fa-solid fa-file-invoice text-sm text-[#E08A1E]"></i>
                        <span>Xem hóa đơn điện tử</span>
                    </a>
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
                    <a href="{{ route('customer.orders.index') }}" 
                       class="w-full text-center text-xs text-gray-500 hover:text-[#8C4A19] block py-1 font-medium underline">
                        ← Xem tất cả đơn hàng đã mua
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-customer-account-layout>


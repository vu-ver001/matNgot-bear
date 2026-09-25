@extends('layouts.staff-dashboard')
@section('page-title', "Chi Tiết Đơn Hàng #{$order->order_code}")

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/order-components.css') }}">
@endsection

@section('content')
<div x-data="{ openApproveModal: false, openRejectModal: false, openRefundRequestModal: false }">
    <!-- Header Breadcrumb & Actions -->
    <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.orders.index') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-left text-xs"></i> Quay lại
            </a>
            <div>
                <h2 class="font-extrabold text-xl text-[#4E342E] flex items-center gap-2 flex-wrap">
                    <span>Đơn Hàng #{{ $order->order_code }}</span>
                    <x-order-status-badge :status="$order->order_status" :cancel-request-status="$order->cancel_request_status" :payment-status="$order->payment_status" />
                    <x-payment-status-badge :status="$order->payment_status" />
                </h2>
                <p class="text-xs text-[#8E8076] mt-0.5">
                    Đặt lúc: {{ $order->created_at->format('d/m/Y H:i:s') }} ({{ $order->created_at->diffForHumans() }})
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            @if ($order->customer_id)
                <a href="{{ route('staff.support.index', ['customer_id' => $order->customer_id, 'order_id' => $order->id]) }}"
                   class="btn btn-primary btn-sm flex items-center gap-1.5"
                   title="Nhắn tin hỗ trợ khách hàng về đơn này">
                    <i class="fa-solid fa-comments"></i>
                    <span>Nhắn tin cho khách</span>
                </a>
            @endif
            <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank"
               class="btn btn-outline btn-sm">
                <i class="fa-solid fa-file-invoice text-amber-600"></i>
                <span>Xem hóa đơn điện tử</span>
            </a>
        </div>
    </div>

    {{-- Flash success/error đã chuyển sang toast góc phải, chỉ giữ $errors validation. --}}

    @if ($errors->any())
        <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- KHỐI CẢNH BÁO 1: Khách hàng yêu cầu hủy đơn hàng --}}
    @if($order->hasPendingCancelRequest())
        <div class="mb-6 bg-gradient-to-r from-rose-500/10 via-rose-50 to-amber-500/10 border-2 border-rose-300 rounded-2xl p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-rose-600/30">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base sm:text-lg font-bold text-rose-900">Khách hàng yêu cầu hủy đơn hàng này</h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-900 font-extrabold text-xs">CẦN XỬ LÝ</span>
                            @if($order->cancelRequestHoursRemaining() <= 2)
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-extrabold text-xs animate-pulse">
                                    <i class="fa-solid fa-hourglass-end mr-1"></i>SẮP HẾT HẠN ({{ $order->cancelRequestTimeRemainingText() }})
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-xs">
                                    <i class="fa-regular fa-clock mr-1"></i>Hạn xử lý: {{ $order->cancelRequestTimeRemainingText() }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1">
                            Thời gian gửi: <strong>{{ $order->cancel_requested_at?->format('d/m/Y H:i:s') }}</strong>
                            · Hạn chót xử lý (24h): <strong class="text-rose-700">{{ $order->cancelRequestExpiresAt()?->format('H:i - d/m/Y') }}</strong>
                        </p>
                        <div class="mt-2 p-2.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 leading-relaxed flex items-start gap-2">
                            <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5 shrink-0"></i>
                            <span><strong>Quy định xử lý:</strong> Nhân viên bắt buộc xử lý yêu cầu này trong vòng <strong>24 giờ</strong>. Nếu quá 24 giờ không duyệt, hệ thống sẽ <strong>tự động từ chối hủy</strong> và đơn hàng tiếp tục được giao cho khách.</span>
                        </div>
                        <div class="mt-2 p-3 bg-white rounded-xl border border-rose-200 text-xs sm:text-sm">
                            <span class="font-bold text-[#2B1810]">Lý do khách hàng muốn hủy:</span>
                            <p class="text-rose-800 mt-1 font-medium italic">"{{ $order->cancel_request_reason }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $isPendingOnlineDirectToAdmin = ($order->order_status === 'PENDING' && $order->payment_status === 'PAID');
            @endphp

            @if($isPendingOnlineDirectToAdmin)
                {{-- Trường hợp đơn PENDING trực tuyến: Chuyển thẳng Admin xử lý hoàn tiền, Nhân viên không thao tác --}}
                <div class="mt-4 p-4 rounded-xl bg-sky-50 border border-sky-300 text-xs sm:text-sm space-y-2">
                    <div class="flex items-center gap-2 text-sky-900 font-bold">
                        <i class="fa-solid fa-shield-halved text-sky-600 text-base"></i>
                        <span>YÊU CẦU HỦY & HOÀN TIỀN ĐÃ ĐƯỢC CHUYỂN THẲNG LÊN ADMIN</span>
                    </div>
                    <p class="text-sky-950 leading-relaxed">
                        Đơn hàng này được thanh toán trực tuyến ({{ $order->payment_method }}) và khách hàng gửi yêu cầu hủy khi đơn <strong>chưa được xác nhận</strong>. Yêu cầu hủy & lệnh hoàn tiền (<strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>) đã được gửi trực tiếp lên <strong>Quản trị viên (Admin)</strong> để duyệt và chuyển khoản hoàn tiền.
                    </p>
                    <p class="text-[#7D6B5D] text-xs italic">
                        * Nhân viên không cần thao tác duyệt hủy hoặc gửi yêu cầu hoàn tiền cho đơn hàng này.
                    </p>

                    @if($order->refund_bank_account || $order->refund_bank_name)
                        <div class="mt-2 p-3 bg-white rounded-lg border border-sky-200 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-[#7D6B5D] block">Ngân hàng nhận hoàn:</span>
                                <strong class="text-[#2B1810]">{{ $order->refund_bank_name ?: '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[#7D6B5D] block">Số tài khoản:</span>
                                <strong class="text-sky-800 font-mono text-sm">{{ $order->refund_bank_account ?: '—' }}</strong>
                            </div>
                            <div>
                                <span class="text-[#7D6B5D] block">Chủ tài khoản:</span>
                                <strong class="text-[#2B1810] uppercase">{{ $order->refund_account_holder ?: '—' }}</strong>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Trường hợp đơn PREPARING (Đang chuẩn bị hàng): Nhân viên kiểm tra và quyết định cho hủy hay không --}}
                <div class="mt-4 p-3.5 bg-amber-50 rounded-xl border border-amber-300 text-xs text-amber-950 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-amber-900">
                        <i class="fa-solid fa-truck-ramp-box text-amber-700"></i>
                        <span>LƯU Ý QUYẾT ĐỊNH CHO NHÂN VIÊN:</span>
                    </div>
                    <p class="leading-relaxed">
                        • Nếu đơn hàng <strong>đã bàn giao cho đơn vị vận chuyển</strong> (nhưng chưa kịp cập nhật trạng thái): Nhân viên vui lòng bấm <strong>"Từ chối hủy"</strong> và nêu rõ lý do đã bàn giao vận chuyển.<br>
                        • Nếu đơn hàng <strong>vẫn còn ở cửa hàng / chưa bàn giao</strong>: Nhân viên bấm <strong>"Chấp nhận hủy đơn"</strong> để hủy và hoàn lại tồn kho.
                    </p>
                </div>

                @if($order->payment_status === 'PAID')
                    <div class="mt-3 p-4 rounded-xl bg-rose-50 border border-rose-300 text-xs sm:text-sm space-y-2">
                        <div class="flex items-center gap-2 text-rose-900 font-bold">
                            <i class="fa-solid fa-hand-holding-dollar text-rose-600 text-base"></i>
                            <span>CẢNH BÁO HOÀN TIỀN: Đơn hàng này ĐÃ THANH TOÁN ({{ number_format($order->total_amount, 0, ',', '.') }}đ)</span>
                        </div>
                        @if($order->refund_bank_account || $order->refund_bank_name)
                            <div class="mt-2 p-3 bg-white rounded-lg border border-rose-200 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div>
                                    <span class="text-[#7D6B5D] block">Ngân hàng:</span>
                                    <strong class="text-[#2B1810]">{{ $order->refund_bank_name ?: '—' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[#7D6B5D] block">Số tài khoản:</span>
                                    <strong class="text-rose-800 font-mono text-sm">{{ $order->refund_bank_account ?: '—' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[#7D6B5D] block">Chủ tài khoản:</span>
                                    <strong class="text-[#2B1810] uppercase">{{ $order->refund_account_holder ?: '—' }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-3 p-2.5 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-700 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>Đơn hàng thanh toán khi nhận hàng (COD), khi chấp nhận hủy không cần hoàn tiền.</span>
                    </div>
                @endif

                <div class="mt-5 pt-4 border-t border-rose-200 flex flex-wrap items-center justify-end gap-3">
                    <button type="button" @click="openRejectModal = true"
                            class="px-4 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-bold text-xs sm:text-sm rounded-xl border border-gray-300 shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-xmark text-rose-500"></i>
                        <span>Từ chối hủy</span>
                    </button>

                    <button type="button" @click="openApproveModal = true"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Chấp nhận hủy đơn</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- POPUP MODAL 1: Chấp nhận hủy đơn --}}
        <div x-show="openApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="openApproveModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-rose-200" @click.stop>
                    <form method="POST" action="{{ route('staff.orders.approve_cancel', $order) }}">
                        @csrf
                        <div class="p-6 sm:p-7">
                            <div class="flex items-center gap-3.5 mb-4 pb-4 border-b border-gray-100">
                                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0 shadow-xs">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-black text-[#2B1810]">Chấp nhận hủy đơn hàng</h3>
                                    <p class="text-xs text-[#7D6B5D] mt-0.5">Mã đơn: <strong class="text-rose-700 font-mono">#{{ $order->order_code }}</strong></p>
                                </div>
                            </div>

                            <div class="space-y-3.5 text-xs text-[#5C3219]">
                                <div class="p-3.5 bg-rose-50/80 rounded-2xl border border-rose-200">
                                    <span class="font-bold text-rose-900 block mb-1">Lý do khách hàng đưa ra:</span>
                                    <p class="italic text-rose-800 font-medium">"{{ $order->cancel_request_reason }}"</p>
                                </div>

                                @if($order->payment_status === 'PAID')
                                    @if($order->refund_bank_account && $order->refund_bank_name)
                                        {{-- Khối quét mã QR hoàn tiền chuyển khoản --}}
                                        <div class="p-4 bg-gradient-to-br from-amber-50/90 to-orange-50/50 rounded-2xl border-2 border-amber-300 shadow-sm space-y-3">
                                            <div class="flex items-center justify-between gap-2 pb-2 border-b border-amber-200">
                                                <div class="font-extrabold text-amber-950 flex items-center gap-2 text-xs sm:text-sm">
                                                    <i class="fa-solid fa-qrcode text-amber-600 text-base"></i>
                                                    <span>QUÉT MÃ VIETQR HOÀN TIỀN CHO KHÁCH</span>
                                                </div>
                                                <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px]">Napas 24/7</span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-center">
                                                {{-- Cột QR Code --}}
                                                <div class="sm:col-span-5 flex flex-col items-center justify-center p-2.5 bg-white rounded-xl border border-amber-200 shadow-xs">
                                                    <img src="{{ $order->refund_viet_qr_url }}" 
                                                         alt="VietQR Hoàn tiền" 
                                                         class="w-36 h-36 object-contain rounded-lg shadow-2xs"
                                                         onerror="this.src='https://placehold.co/150x150/fff7ed/ea580c?text=QR+Loi'">
                                                    <div class="text-[10.5px] font-bold text-[#8C4A19] mt-1.5 flex items-center gap-1 text-center">
                                                        <i class="fa-solid fa-camera text-xs"></i>
                                                        <span>Quét bằng App Ngân hàng</span>
                                                    </div>
                                                </div>

                                                {{-- Cột Thông tin chuyển khoản --}}
                                                <div class="sm:col-span-7 space-y-2 text-xs">
                                                    <div class="flex justify-between items-center py-1 border-b border-amber-200/60">
                                                        <span class="text-[#7D6B5D]">Số tiền hoàn:</span>
                                                        <span class="font-black text-rose-600 text-base">{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                                                    </div>
                                                    <div class="flex justify-between items-center py-1 border-b border-amber-200/60">
                                                        <span class="text-[#7D6B5D]">Ngân hàng:</span>
                                                        <strong class="text-[#2B1810] font-bold">{{ $order->refund_bank_name }}</strong>
                                                    </div>
                                                    <div class="flex justify-between items-center py-1 border-b border-amber-200/60">
                                                        <span class="text-[#7D6B5D]">Số tài khoản:</span>
                                                        <div class="flex items-center gap-1.5">
                                                            <strong class="font-mono text-amber-900 font-bold text-sm">{{ $order->refund_bank_account }}</strong>
                                                            <button type="button" 
                                                                    onclick="navigator.clipboard.writeText('{{ $order->refund_bank_account }}'); alert('Đã sao chép số tài khoản: {{ $order->refund_bank_account }}');"
                                                                    class="text-[#E08A1E] hover:text-[#B87309] text-xs cursor-pointer p-0.5" title="Sao chép STK">
                                                                <i class="fa-regular fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-between items-center py-1 border-b border-amber-200/60">
                                                        <span class="text-[#7D6B5D]">Chủ tài khoản:</span>
                                                        <strong class="text-[#2B1810] uppercase font-bold">{{ $order->refund_account_holder ?: '—' }}</strong>
                                                    </div>
                                                    <div class="flex justify-between items-center py-1">
                                                        <span class="text-[#7D6B5D]">Nội dung CK:</span>
                                                        <strong class="text-[#2B1810] font-mono text-[11px]">Hoan tien don {{ $order->order_code }}</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-2 text-[11px] text-amber-900 leading-relaxed bg-white/70 p-2.5 rounded-lg border border-amber-200/60">
                                                👉 <strong>Hướng dẫn:</strong> Quét mã QR trên app ngân hàng để chuyển trả lại <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong> cho khách, sau đó bấm <strong>"Xác nhận duyệt hủy"</strong> bên dưới.
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Ghi chú hoàn tiền (Mã GD / Tham chiếu)</label>
                                                <input type="text" name="refund_note" placeholder="Ví dụ: Đã chuyển khoản qua App MB lúc {{ now()->format('H:i') }}..."
                                                       class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500 bg-white">
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-300 space-y-1.5">
                                            <div class="font-bold text-amber-900 flex items-center gap-1.5 text-xs">
                                                <i class="fa-solid fa-hand-holding-dollar text-amber-600"></i>
                                                <span>LƯU Ý ĐƠN HÀNG ĐÃ THANH TOÁN</span>
                                            </div>
                                            <p class="text-[#786B61] leading-relaxed">
                                                Số tiền cần hoàn: <strong class="text-amber-800 text-sm font-extrabold">{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>.
                                                Khách hàng chưa điền sẵn STK trong yêu cầu, vui lòng liên hệ SĐT <strong>{{ $order->recipient_phone }}</strong> để lấy STK chuyển trả tiền cho khách.
                                            </p>
                                            <div class="mt-2">
                                                <label class="block text-[11px] font-bold text-[#2B1810] mb-1">Ghi chú hoàn tiền</label>
                                                <input type="text" name="refund_note" placeholder="Nhập ghi chú chuyển tiền hoàn..."
                                                       class="w-full rounded-xl border-gray-300 text-xs px-3 py-2 focus:border-amber-500 focus:ring-amber-500 bg-white">
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-[#786B61]">
                                        Sau khi chấp nhận, đơn hàng sẽ chuyển sang trạng thái <strong>Đã hủy</strong> và toàn bộ sản phẩm sẽ được tự động hoàn lại vào kho.
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2.5 border-t border-gray-100">
                            <button type="button" @click="openApproveModal = false" class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                                Quay lại
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-check"></i>
                                <span>Xác nhận duyệt hủy</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- POPUP MODAL 2: Từ chối hủy đơn --}}
        <div x-show="openRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="openRejectModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-200" @click.stop>
                    <form method="POST" action="{{ route('staff.orders.reject_cancel', $order) }}">
                        @csrf
                        <div class="p-6 sm:p-7">
                            <div class="flex items-center gap-3.5 mb-4 pb-4 border-b border-gray-100">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-600 flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid fa-shield-xmark"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-black text-[#2B1810]">Từ chối yêu cầu hủy đơn</h3>
                                    <p class="text-xs text-[#7D6B5D] mt-0.5">Mã đơn: <strong class="text-amber-800 font-mono">#{{ $order->order_code }}</strong></p>
                                </div>
                            </div>

                            <div class="space-y-3.5 text-xs text-[#5C3219]">
                                <div>
                                    <label class="block font-bold text-[#2B1810] mb-1">
                                        Lý do từ chối yêu cầu hủy <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="rejection_reason" rows="3" required
                                              placeholder="Nhập lý do cửa hàng không đồng ý cho hủy đơn (VD: Đơn hàng đã giao cho bên vận chuyển lấy đi...)"
                                              class="w-full rounded-xl border-gray-300 text-xs p-3 focus:border-amber-500 focus:ring-amber-500"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2.5 border-t border-gray-100">
                            <button type="button" @click="openRejectModal = false" class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                                Đóng
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-gray-900 hover:bg-black rounded-xl shadow-xs transition cursor-pointer">
                                Xác nhận từ chối
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- KHỐI CẢNH BÁO 2: Cần hoàn tiền cho khách (Đơn đã hủy & đã thanh toán) --}}
    @if($order->needsRefund())
        @php
            $latestRefund = $order->latestRefundRequest;
        @endphp

        @if($latestRefund && $latestRefund->status === 'PENDING')
            {{-- Trạng thái: Đã gửi yêu cầu, đang chờ Admin duyệt --}}
            <div class="mb-6 bg-gradient-to-r from-purple-500/10 via-purple-50 to-indigo-500/10 border-2 border-purple-300 rounded-2xl p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-purple-600/30">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base sm:text-lg font-bold text-purple-950">Đã gửi yêu cầu hoàn tiền - ĐANG CHỜ ADMIN PHÊ DUYỆT</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-purple-200 text-purple-900 font-extrabold text-xs animate-pulse">CHỜ ADMIN DUYỆT</span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1">
                                Mã yêu cầu: <strong class="text-purple-900 font-mono">#RF-{{ str_pad($latestRefund->id, 5, '0', STR_PAD_LEFT) }}</strong>
                                &bull; Số tiền: <strong class="text-purple-900 font-black">{{ number_format($latestRefund->amount, 0, ',', '.') }} đ</strong>
                            </p>
                            <p class="text-xs text-[#7D6B5D] mt-0.5">
                                Gửi bởi: <strong>{{ $latestRefund->requestedByUser?->full_name ?? $latestRefund->requestedByUser?->name ?? 'Nhân viên' }}</strong> lúc {{ $latestRefund->created_at->format('H:i - d/m/Y') }}
                            </p>
                            <div class="mt-2 text-xs bg-white/80 p-2.5 rounded-xl border border-purple-200 text-purple-950">
                                <span class="font-bold">Lý do đề xuất:</span> {{ $latestRefund->reason }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-800 text-xs font-bold rounded-xl border border-purple-200">
                            <i class="fa-solid fa-user-shield"></i>
                            <span>Chờ Admin chuyển khoản &amp; duyệt</span>
                        </div>
                        <span class="text-[11px] text-[#7D6B5D]">Nhân viên chỉ gửi yêu cầu, Admin mới duyệt lệnh chuyển tiền</span>
                    </div>
                </div>

                @if($order->refund_bank_account || $order->refund_bank_name)
                    <div class="mt-4 p-3.5 bg-white rounded-xl border border-purple-200 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[#7D6B5D] block font-medium">Ngân hàng:</span>
                            <strong class="text-[#2B1810] text-sm">{{ $order->refund_bank_name ?: '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-[#7D6B5D] block font-medium">Số tài khoản:</span>
                            <strong class="text-purple-900 font-mono text-sm tracking-wide">{{ $order->refund_bank_account ?: '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-[#7D6B5D] block font-medium">Chủ tài khoản:</span>
                            <strong class="text-[#2B1810] uppercase text-sm">{{ $order->refund_account_holder ?: '—' }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        @else
            {{-- Chưa gửi yêu cầu hoặc yêu cầu trước bị từ chối --}}
            <div class="mb-6 bg-gradient-to-r from-amber-500/15 via-amber-50 to-orange-500/10 border-2 border-amber-400 rounded-2xl p-6 shadow-sm">
                @if($latestRefund && $latestRefund->status === 'REJECTED')
                    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5"></i>
                        <div>
                            <strong>Yêu cầu hoàn tiền trước bị Admin từ chối:</strong>
                            {{ $latestRefund->admin_note ?? 'Không có ghi chú' }} (Vui lòng kiểm tra lại thông tin và gửi lại yêu cầu).
                        </div>
                    </div>
                @endif

                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-amber-600/30">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base sm:text-lg font-bold text-amber-950">Đơn hàng đã hủy - CẦN HOÀN TIỀN CHO KHÁCH</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-xs animate-pulse">CHỜ GỬI YÊU CẦU</span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1">
                                Số tiền cần hoàn trả: <strong class="text-amber-900 text-base font-black">{{ number_format($order->total_amount, 0, ',', '.') }} đ</strong>
                            </p>
                            <p class="text-xs text-[#7D6B5D] mt-0.5">
                                Liên hệ khách: <strong class="text-[#2B1810] text-sm">{{ $order->recipient_phone }}</strong> (Khách nhận: <strong>{{ $order->recipient_name }}</strong>)
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="openRefundRequestModal = true"
                            class="px-5 py-2.5 bg-[#5C3219] hover:bg-[#4E2B15] text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>{{ $latestRefund && $latestRefund->status === 'REJECTED' ? 'Gửi lại yêu cầu lên Admin' : 'Gửi yêu cầu hoàn tiền lên Admin' }}</span>
                    </button>
                </div>

                @if($order->refund_bank_account || $order->refund_bank_name)
                    <div class="mt-4 p-3.5 bg-white rounded-xl border border-amber-200 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <span class="text-[#7D6B5D] block font-medium">Ngân hàng:</span>
                            <strong class="text-[#2B1810] text-sm">{{ $order->refund_bank_name ?: '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-[#7D6B5D] block font-medium">Số tài khoản:</span>
                            <strong class="text-amber-800 font-mono text-sm tracking-wide">{{ $order->refund_bank_account ?: '—' }}</strong>
                        </div>
                        <div>
                            <span class="text-[#7D6B5D] block font-medium">Chủ tài khoản:</span>
                            <strong class="text-[#2B1810] uppercase text-sm">{{ $order->refund_account_holder ?: '—' }}</strong>
                        </div>
                    </div>
                @else
                    <div class="mt-3 p-3 bg-white/80 rounded-xl border border-amber-200 text-xs text-amber-900">
                        💡 Khách hàng chưa điền sẵn STK khi hủy. Vui lòng gọi điện tới <strong>{{ $order->recipient_phone }}</strong> để xin STK ngân hàng trước khi gửi yêu cầu lên Admin.
                    </div>
                @endif
            </div>

            {{-- POPUP Gửi yêu cầu hoàn tiền lên Admin --}}
            <div x-show="openRefundRequestModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="openRefundRequestModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-amber-300" @click.stop>
                        <form method="POST" action="{{ route('staff.orders.request_refund', $order) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="p-6 sm:p-7">
                                <div class="flex items-center gap-3.5 mb-4 pb-4 border-b border-gray-100">
                                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0 shadow-xs">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-lg font-black text-[#2B1810]">Gửi yêu cầu hoàn tiền lên Admin</h3>
                                        <p class="text-xs text-[#7D6B5D] mt-0.5">Mã đơn: <strong class="text-amber-800 font-mono">#{{ $order->order_code }}</strong></p>
                                    </div>
                                </div>

                                <div class="space-y-4 text-xs text-[#5C3219]">
                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-[#7D6B5D]">Số tiền cần hoàn trả:</span>
                                            <span class="text-amber-900 text-base font-black">{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                                        </div>
                                        <input type="hidden" name="amount" value="{{ (int)$order->total_amount }}">
                                        <p class="text-[11px] text-[#7D6B5D] mt-1">Yêu cầu sau khi gửi sẽ chuyển đến trang phê duyệt của Admin (Chủ shop) để thực hiện chuyển khoản.</p>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-[#2B1810] mb-1">Ngân hàng nhận:</label>
                                            <input type="text" name="bank_name" value="{{ old('bank_name', $order->refund_bank_name ?: 'MB') }}" placeholder="MB, VCB, Techcombank..."
                                                   class="w-full rounded-xl border-gray-300 text-xs p-2.5 focus:border-amber-500 focus:ring-amber-500">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-[#2B1810] mb-1">Số tài khoản nhận: <span class="text-red-500">*</span></label>
                                            <input type="text" name="bank_account" value="{{ old('bank_account', $order->refund_bank_account) }}" required placeholder="Nhập số tài khoản"
                                                   class="w-full rounded-xl border-gray-300 text-xs p-2.5 font-mono focus:border-amber-500 focus:ring-amber-500">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[#2B1810] mb-1">Chủ tài khoản: <span class="text-red-500">*</span></label>
                                        <input type="text" name="account_holder" value="{{ old('account_holder', $order->refund_account_holder ?: $order->recipient_name) }}" required placeholder="NGUYEN VAN A"
                                               class="w-full rounded-xl border-gray-300 text-xs p-2.5 uppercase focus:border-amber-500 focus:ring-amber-500">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[#2B1810] mb-1">Lý do đề xuất hoàn tiền: <span class="text-red-500">*</span></label>
                                        <textarea name="reason" rows="3" required placeholder="Nhập lý do gửi Admin duyệt hoàn tiền..."
                                                  class="w-full rounded-xl border-gray-300 text-xs p-2.5 focus:border-amber-500 focus:ring-amber-500">{{ old('reason', $order->cancel_reason ? 'Đơn hàng online đã hủy (Lý do: ' . $order->cancel_reason . '). Cần hoàn tiền trả khách.' : 'Đơn hàng đã thanh toán online bị hủy, gửi Admin duyệt chuyển khoản hoàn tiền.') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[#2B1810] mb-1">Ảnh chứng từ / Bill chuyển khoản (nếu có):</label>
                                        <input type="file" name="proof_image" accept="image/*"
                                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2.5 border-t border-gray-100">
                                <button type="button" @click="openRefundRequestModal = false" class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                                    Hủy
                                </button>
                                <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Gửi yêu cầu lên Admin</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- KHỐI CẢNH BÁO 3: Đã hoàn tiền thành công --}}
    @if($order->order_status === 'CANCELLED' && $order->payment_status === 'REFUNDED')
        <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-2xl p-4 flex items-center gap-3 text-emerald-900 text-xs sm:text-sm">
            <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
            <div>
                <strong>Đơn hàng đã được hoàn tiền thành công.</strong>
                @if($order->refund_note)
                    <p class="text-xs text-emerald-800 mt-0.5">Ghi chú: {{ $order->refund_note }}</p>
                @endif
            </div>
        </div>
    @endif

    <!-- 1. Timeline Đơn Hàng -->
    <div class="panel-card mb-6">
        <div class="panel-header">
            <div class="panel-title">
                <i class="fa-solid fa-timeline"></i>
                Tiến Trình Đơn Hàng
            </div>
        </div>
        <div class="py-2">
            <x-order-timeline :status="$order->order_status" />
        </div>
    </div>

    <!-- 2. Grid Nội Dung Chính -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cột Trái (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Thông tin người nhận & vận chuyển -->
            <div class="panel-card mb-0">
                <div class="panel-header flex items-center justify-between flex-wrap gap-2">
                    <div class="panel-title">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        Thông tin nhận hàng & Vận chuyển
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($order->customer_id)
                            <a href="{{ route('staff.support.index', ['customer_id' => $order->customer_id, 'order_id' => $order->id]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-xl transition cursor-pointer"
                               title="Mở cuộc trò chuyện hỗ trợ khách hàng cho đơn này">
                                <i class="fa-solid fa-comments"></i>
                                <span>Nhắn tin cho khách</span>
                            </a>
                        @endif
                    </div>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                        <dt class="text-xs font-bold text-[#8E8076] uppercase">Người nhận hàng</dt>
                        <dd class="font-extrabold text-[#4E342E] text-base mt-0.5">{{ $order->recipient_name }}</dd>
                        @if ($order->customer)
                            <span class="text-[11px] text-[#8D6E63] font-medium"><i class="fa-solid fa-user-tag text-[10px]"></i> Tài khoản: {{ $order->customer->full_name }}</span>
                        @endif
                    </div>
                    <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                        <dt class="text-xs font-bold text-[#8E8076] uppercase">Số điện thoại liên hệ</dt>
                        <dd class="font-extrabold text-[#4E342E] text-base mt-0.5">{{ $order->recipient_phone }}</dd>
                        <span class="text-[11px] text-[#8E8076]">Liên hệ khi giao hàng</span>
                    </div>
                    <div class="sm:col-span-2 p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                        <dt class="text-xs font-bold text-[#8E8076] uppercase">Địa chỉ nhận hàng</dt>
                        <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->recipient_address }}</dd>
                    </div>
                    <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                        <dt class="text-xs font-bold text-[#8E8076] uppercase">Phương thức thanh toán</dt>
                        <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->payment_method_label }}</dd>
                    </div>
                    <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                        <dt class="text-xs font-bold text-[#8E8076] uppercase">Hình thức giao hàng</dt>
                        <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->shipping_method_label }}</dd>
                        @if ($order->shipped_at)
                            <span class="text-[11px] text-[#8E8076]">Bắt đầu giao: {{ $order->shipped_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                    <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                        <dt class="text-xs font-bold text-[#8E8076] uppercase">Ngày tạo đơn</dt>
                        <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    @if ($order->note)
                        <div class="sm:col-span-2 p-3 bg-amber-100/40 rounded-xl border border-amber-200/80">
                            <dt class="text-xs font-bold text-[#8B5A2B] uppercase">Ghi chú từ khách hàng</dt>
                            <dd class="text-[#4E342E] font-medium mt-0.5 italic">"{{ $order->note }}"</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Danh sách sản phẩm đặt mua -->
            <div class="panel-card mb-0">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-basket-shopping"></i>
                        Sản phẩm đã đặt
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sản Phẩm</th>
                                <th class="text-right">Đơn Giá</th>
                                <th class="text-center">Số Lượng</th>
                                <th class="text-right">Thành Tiền</th>
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
                                                @if ($detail->product)
                                                    <div class="text-[11px] text-[#8E8076] mt-0.5">Mã SP: #{{ $detail->product_id }}</div>
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

                <!-- Tóm tắt chi phí -->
                <div class="mt-4 p-4 bg-amber-50/60 rounded-xl border border-amber-100">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between text-[#795548]">
                            <dt>Tiền hàng (Tạm tính)</dt>
                            <dd class="font-bold text-[#4E342E]">{{ number_format($order->subtotal, 0, ',', '.') }} đ</dd>
                        </div>
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
                        <div class="flex justify-between text-[#795548]">
                            <dt>Phí giao hàng</dt>
                            <dd class="font-bold text-[#4E342E]">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</dd>
                        </div>
                        @if ($order->shipping_discount_amount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <dt>
                                    Giảm phí vận chuyển
                                    @if ($order->shippingVoucher)
                                        <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 rounded font-bold text-xs">{{ $order->shippingVoucher->code }}</span>
                                    @endif
                                </dt>
                                <dd class="font-bold">-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }} đ</dd>
                            </div>
                        @endif
                        <div class="flex justify-between items-center text-base pt-3 border-t border-amber-200">
                            <dt class="font-black text-[#4E342E]">Tổng Thanh Toán</dt>
                            <dd class="font-black text-xl text-amber-700">{{ number_format($order->total_amount, 0, ',', '.') }} đ</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Lịch sử giao dịch thanh toán -->
            <div class="panel-card mb-0">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-credit-card"></i>
                        Lịch Sử Giao Dịch Thanh Toán
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Phương Thức</th>
                                <th class="text-right">Số Tiền</th>
                                <th>Trạng Thái</th>
                                <th>Mã GD</th>
                                <th>Thời Gian</th>
                                <th class="text-right">Xử Lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->payments as $payment)
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
                                    <td>
                                        <x-payment-status-badge :status="$payment->status" :method="$payment->method" />
                                    </td>
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
                                    <td class="text-right">
                                        @if ($payment->method === 'COD')
                                            <span class="text-xs text-[#8E8076] italic bg-amber-50/80 px-0.5 py-1 rounded-lg border border-amber-200/60 inline-block">
                                                Thu khi giao hàng
                                            </span>
                                        @elseif ($payment->status === 'PENDING')
                                            <div class="flex justify-end gap-1.5">
                                                <form method="POST" action="{{ route('staff.payments.updateStatus', $payment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="PAID">
                                                    <button class="btn btn-success btn-sm">Xác nhận</button>
                                                </form>
                                                <form method="POST" action="{{ route('staff.payments.updateStatus', $payment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="FAILED">
                                                    <button class="btn btn-danger btn-sm">Hủy</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-sm text-[#8E8076]">Chưa có bản ghi giao dịch nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cột Phải (1 Col) -->
        <div class="space-y-6">
            <!-- Cập nhật trạng thái -->
            <div class="panel-card mb-0">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        Cập Nhật Trạng Thái
                    </div>
                </div>

                @if (in_array($order->order_status, ['CANCELLED', 'RETURNED']))
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#795548]">
                        <i class="fa-solid fa-lock text-gray-500 mr-1"></i>
                        Đơn hàng đã kết thúc ở trạng thái <strong>{{ \App\Models\OrderStatusHistory::statusLabel($order->order_status) }}</strong>, không thể cập nhật thêm.
                    </div>
                @else
                    @if ($order->payment_status === 'FAILED')
                        <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-xmark text-rose-600 text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="font-bold">Đơn hàng thanh toán thất bại:</strong>
                                <p class="text-stone-600 mt-0.5">Giao dịch thanh toán trực tuyến của đơn hàng này không thành công. Nhân viên/admin không được phép xác nhận đơn hàng này.</p>
                            </div>
                        </div>
                    @elseif ($order->payment_method !== 'COD' && $order->payment_status !== 'PAID')
                        <div class="mb-4 p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2.5">
                            <i class="fa-solid fa-clock text-amber-600 text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="font-bold">Đơn hàng chưa thanh toán:</strong>
                                <p class="text-stone-600 mt-0.5">Khách hàng chọn thanh toán trực tuyến nhưng chưa hoàn tất thanh toán. Chỉ có thể xác nhận đơn sau khi thanh toán thành công.</p>
                            </div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('staff.orders.updateStatus', $order) }}" x-data="{ status: '' }">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-xs font-bold text-[#795548] uppercase mb-1.5">Chuyển sang trạng thái:</label>
                            <select name="order_status" x-model="status" class="select-control" required>
                                <option value="" disabled>Chọn trạng thái mới</option>
                                @foreach (['PENDING' => 'Chờ xác nhận', 'PREPARING' => 'Đang chuẩn bị', 'SHIPPING' => 'Đang giao hàng', 'COMPLETED' => 'Đã giao', 'RETURNED' => 'Trả hàng / Hoàn tiền', 'CANCELLED' => 'Hủy đơn hàng'] as $value => $label)
                                    @if (in_array($value, $order->allowedNextStatuses(), true))
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div x-show="status === 'CANCELLED'" x-cloak class="mt-3">
                            <label class="block text-xs font-bold text-rose-700 uppercase mb-1.5">Lý do hủy đơn <span class="text-rose-600">*</span></label>
                            <textarea name="cancel_reason" rows="3" maxlength="255" :required="status === 'CANCELLED'" :disabled="status !== 'CANCELLED'" placeholder="Nhập lý do hủy đơn chi tiết..."
                                      class="input-control"></textarea>

                            @if ($order->order_status === 'SHIPPING')
                                <label class="mt-3 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-semibold text-rose-800">
                                    <input type="checkbox" name="stock_returned" value="1"
                                           :required="status === 'CANCELLED'" :disabled="status !== 'CANCELLED'"
                                           class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                    <span>Tôi xác nhận kiện hàng đã quay lại kho và có thể hoàn lại tồn kho.</span>
                                </label>
                            @endif
                        </div>

                        <button type="submit" :disabled="!status" class="mt-4 w-full btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
                        </button>
                    </form>
                @endif
            </div>

            <!-- Lịch sử thay đổi trạng thái -->
            <div class="panel-card mb-0">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Nhật Ký Đơn Hàng
                    </div>
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
                                <span>{{ $displayTitle }}</span>
                            </div>
                            <div class="text-[11px] text-[#8E8076] mt-0.5">
                                {{ $history->changed_at->format('d/m/Y H:i') }}
                                {{ $history->changedByUser ? '• ' . $history->changedByUser->full_name : '' }}
                            </div>
                            @if ($history->note)
                                <div class="text-xs text-[#795548] mt-1 p-2 bg-amber-50/50 border border-amber-100 rounded-lg">
                                    {{ $history->note }}
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="ml-5 text-xs text-[#8E8076]">Chưa có nhật ký trạng thái.</li>
                    @endforelse
                </ol>
            </div>

            {{-- Hiển thị lý do hủy đơn nếu có --}}
            @if ($order->cancel_reason)
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                    <h4 class="text-xs font-bold text-rose-800 uppercase mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Lý do hủy đơn hàng
                    </h4>
                    <p class="text-sm text-rose-700 font-medium">{{ $order->cancel_reason }}</p>
                </div>
            @endif

            {{-- Hiển thị lý do từ chối hủy nếu có --}}
            @if ($order->cancel_rejection_reason)
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl">
                    <h4 class="text-xs font-bold text-gray-800 uppercase mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-ban text-gray-600"></i>
                        Lý do từ chối yêu cầu hủy
                    </h4>
                    <p class="text-sm text-gray-700 font-medium">{{ $order->cancel_rejection_reason }}</p>
                </div>
            @endif
        </div>
</div>
@endsection


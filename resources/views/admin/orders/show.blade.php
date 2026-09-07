@extends('layouts.admin-dashboard')
@section('page-title', "Chi tiết đơn hàng {$order->order_code}")
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-[#1E293B] leading-tight">Chi tiết đơn hàng <span class="font-mono text-amber-700">{{ $order->order_code }}</span></h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-amber-50 text-[#8B5A2B] font-bold text-xs sm:text-sm rounded-xl border border-amber-200 shadow-2xs transition">
                    <i class="fa-solid fa-file-invoice text-amber-600"></i>
                    <span>Xem hóa đơn điện tử</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-amber-700 hover:text-[#8B5A2B]">← Quay lại danh sách</a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Tiến trình đơn hàng</h3>
            <x-order-timeline :status="$order->order_status" />
        </div>

        {{-- Khung cảnh báo & xử lý yêu cầu hủy đơn hàng từ khách hàng --}}
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
                            </div>
                            <p class="text-xs sm:text-sm text-[#7D6B5D] mt-1">
                                Thời gian gửi yêu cầu: <strong>{{ $order->cancel_requested_at?->format('d/m/Y H:i:s') }}</strong>
                                ({{ $order->cancel_requested_at?->diffForHumans() }})
                            </p>
                            <div class="mt-2 p-3 bg-white rounded-xl border border-rose-200 text-xs sm:text-sm">
                                <span class="font-bold text-[#2B1810]">Lý do khách hàng muốn hủy:</span>
                                <p class="text-rose-800 mt-1 font-medium italic">"{{ $order->cancel_request_reason }}"</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Khối thông tin nghiệp vụ hoàn tiền nếu đơn ĐÃ THANH TOÁN --}}
                @if($order->payment_status === 'PAID')
                    <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-300 text-xs sm:text-sm space-y-2">
                        <div class="flex items-center gap-2 text-amber-900 font-bold">
                            <i class="fa-solid fa-hand-holding-dollar text-amber-600 text-base"></i>
                            <span>CẢNH BÁO HOÀN TIỀN: Đơn hàng này ĐÃ THANH TOÁN ({{ number_format($order->total_amount, 0, ',', '.') }}đ)</span>
                        </div>
                        <p class="text-[#5C3219] leading-relaxed">
                            👉 Nhân viên cần liên hệ khách hàng qua SĐT <strong>{{ $order->recipient_phone }}</strong> để lấy thông tin STK và chuyển lại 100% tiền.
                        </p>

                        @if($order->refund_bank_account || $order->refund_bank_name)
                            <div class="mt-2 p-3 bg-white rounded-lg border border-amber-200 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div>
                                    <span class="text-[#7D6B5D] block">Ngân hàng:</span>
                                    <strong class="text-[#2B1810]">{{ $order->refund_bank_name ?: '—' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[#7D6B5D] block">Số tài khoản:</span>
                                    <strong class="text-amber-800 font-mono text-sm">{{ $order->refund_bank_account ?: '—' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[#7D6B5D] block">Chủ tài khoản:</span>
                                    <strong class="text-[#2B1810] uppercase">{{ $order->refund_account_holder ?: '—' }}</strong>
                                </div>
                            </div>
                        @else
                            <div class="text-[11.5px] text-amber-800 italic">
                                (Khách hàng chưa nhập sẵn STK trong form, vui lòng gọi điện thoại qua SĐT của khách để xin STK hoàn tiền)
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-3 p-2.5 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-700 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>Đơn hàng chưa thanh toán (COD / Chờ thanh toán), không cần thực hiện chuyển khoản hoàn tiền.</span>
                    </div>
                @endif

                {{-- 2 Nút thao tác dành cho nhân viên --}}
                <div class="mt-5 pt-4 border-t border-rose-200 flex flex-wrap items-center justify-end gap-3" x-data="{ openReject: false, openApprove: false }">
                    <button type="button" @click="openReject = true"
                            class="px-4 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-bold text-xs sm:text-sm rounded-xl border border-gray-300 shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-xmark text-rose-500"></i>
                        <span>Từ chối hủy đơn</span>
                    </button>

                    <button type="button" @click="openApprove = true"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Xác nhận duyệt hủy đơn</span>
                    </button>

                    {{-- Modal Duyệt hủy --}}
                    <div x-show="openApprove" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="openApprove = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200" @click.stop>
                                <form method="POST" action="{{ route('admin.orders.approve_cancel', $order) }}">
                                    @csrf
                                    <div class="p-6">
                                        <h3 class="text-lg font-bold text-[#1E293B]">Xác nhận duyệt hủy đơn hàng {{ $order->order_code }}</h3>
                                        <p class="text-xs text-[#64748B] mt-1 leading-relaxed">
                                            Hành động này sẽ hủy đơn hàng, khôi phục tồn kho sản phẩm, hoàn lại lượt dùng voucher (nếu có), và cập nhật thanh toán sang ĐÃ HOÀN TIỀN (nếu đơn đã trả tiền).
                                        </p>
                                        @if($order->payment_status === 'PAID')
                                            <div class="mt-3 p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                                                ⚠️ Vui lòng chắc chắn bạn đã liên hệ khách và chuyển khoản hoàn lại số tiền <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>.
                                            </div>
                                            <div class="mt-3">
                                                <label class="block text-xs font-bold text-[#1E293B] mb-1">Ghi chú đối soát hoàn tiền (tùy chọn)</label>
                                                <input type="text" name="refund_note" placeholder="Ví dụ: Đã chuyển tiền hoàn qua MB Bank lúc 10h"
                                                       class="w-full rounded-xl border-gray-300 text-xs p-2.5 focus:border-amber-500 focus:ring-amber-500">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="bg-gray-50 px-6 py-3.5 flex justify-end gap-2.5 border-t border-gray-100">
                                        <button type="button" @click="openApprove = false" class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Đóng</button>
                                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 shadow-xs">Đồng ý duyệt hủy</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Từ chối hủy --}}
                    <div x-show="openReject" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="openReject = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200" @click.stop>
                                <form method="POST" action="{{ route('admin.orders.reject_cancel', $order) }}">
                                    @csrf
                                    <div class="p-6">
                                        <h3 class="text-lg font-bold text-[#1E293B]">Từ chối yêu cầu hủy đơn hàng</h3>
                                        <p class="text-xs text-[#64748B] mt-1">
                                            Đơn hàng sẽ tiếp tục quy trình xử lý và giao hàng. Vui lòng nhập lý do từ chối để thông báo cho khách hàng:
                                        </p>
                                        <div class="mt-3">
                                            <label class="block text-xs font-bold text-[#1E293B] mb-1">Lý do từ chối <span class="text-rose-500">*</span></label>
                                            <textarea name="rejection_reason" rows="3" required placeholder="Ví dụ: Đơn hàng đã đóng gói và giao cho bưu tá..."
                                                      class="w-full rounded-xl border-gray-300 text-xs p-2.5 focus:border-amber-500 focus:ring-amber-500"></textarea>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-6 py-3.5 flex justify-end gap-2.5 border-t border-gray-100">
                                        <button type="button" @click="openReject = false" class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50">Đóng</button>
                                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-gray-800 rounded-xl hover:bg-black shadow-xs">Xác nhận từ chối</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-[#1E293B]">Thông tin đơn hàng</h3>
                            <div class="flex gap-2">
                                <x-order-status-badge :status="$order->order_status" :cancel-request-status="$order->cancel_request_status" />
                                <x-payment-status-badge :status="$order->payment_status" />
                            </div>
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-[#64748B]">Người nhận</dt>
                                <dd class="font-medium text-[#1E293B]">{{ $order->recipient_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-[#64748B]">Số điện thoại</dt>
                                <dd class="font-medium text-[#1E293B]">{{ $order->recipient_phone }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-[#64748B]">Địa chỉ</dt>
                                <dd class="font-medium text-[#1E293B]">{{ $order->recipient_address }}</dd>
                            </div>
                            <div>
                                <dt class="text-[#64748B]">Phương thức thanh toán</dt>
                                <dd class="font-medium text-[#1E293B]">{{ $order->payment_method }}</dd>
                            </div>
                            <div>
                                <dt class="text-[#64748B]">Ngày đặt</dt>
                                <dd class="font-medium text-[#1E293B]">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            @if ($order->note)
                                <div class="sm:col-span-2">
                                    <dt class="text-[#64748B]">Ghi chú của khách</dt>
                                    <dd class="font-medium text-[#1E293B]">{{ $order->note }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Sản phẩm</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-amber-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Sản phẩm</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Đơn giá</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($order->details as $detail)
                                        <tr>
                                            <td class="px-4 py-4 text-sm font-medium text-[#1E293B]">{{ $detail->product_name }}</td>
                                            <td class="px-4 py-4 text-sm text-[#64748B] text-right">{{ number_format($detail->product_price, 0, ',', '.') }} đ</td>
                                            <td class="px-4 py-4 text-sm text-[#64748B] text-right">{{ $detail->quantity }}</td>
                                            <td class="px-4 py-4 text-sm font-medium text-[#1E293B] text-right">{{ number_format($detail->line_total, 0, ',', '.') }} đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <dl class="mt-6 space-y-2 text-sm border-t border-amber-100 pt-4">
                            <div class="flex justify-between">
                                <dt class="text-[#64748B]">Tạm tính</dt>
                                <dd class="font-medium text-[#1E293B]">{{ number_format($order->subtotal, 0, ',', '.') }} đ</dd>
                            </div>
                            @if ($order->discount_amount > 0)
                                <div class="flex justify-between">
                                    <dt class="text-[#64748B]">Giảm giá {{ $order->voucher?->code ? "({$order->voucher->code})" : '' }}</dt>
                                    <dd class="font-medium text-rose-600">-{{ number_format($order->discount_amount, 0, ',', '.') }} đ</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-[#64748B]">Phí vận chuyển</dt>
                                <dd class="font-medium text-[#1E293B]">{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</dd>
                            </div>
                            <div class="flex justify-between text-base pt-2 border-t border-amber-100">
                                <dt class="font-semibold text-[#1E293B]">Tổng cộng</dt>
                                <dd class="font-bold text-amber-600">{{ number_format($order->total_amount, 0, ',', '.') }} đ</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Giao dịch thanh toán</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-amber-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Phương thức</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Số tiền</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Trạng thái</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Mã GD</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Thời gian</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Xác nhận</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($order->payments as $payment)
                                        <tr>
                                            <td class="px-4 py-4 text-sm text-[#64748B]">{{ $payment->method }}</td>
                                            <td class="px-4 py-4 text-sm font-medium text-[#1E293B] text-right">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                                            <td class="px-4 py-4"><x-payment-status-badge :status="$payment->status" /></td>
                                            <td class="px-4 py-4 text-sm text-[#64748B]">{{ $payment->transaction_ref ?? '—' }}</td>
                                            <td class="px-4 py-4 text-sm text-[#64748B]">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-4 text-right">
                                                @if ($payment->status === 'PENDING')
                                                    <div class="flex justify-end gap-2">
                                                        <form method="POST" action="{{ route('admin.payments.updateStatus', $payment) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="PAID">
                                                            <button class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-full hover:bg-green-700">Đã nhận</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.payments.updateStatus', $payment) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="FAILED">
                                                            <button class="px-3 py-1.5 text-xs font-medium text-white bg-rose-600 rounded-full hover:bg-rose-700">Thất bại</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-10 text-center text-sm text-[#64748B]">Chưa có giao dịch thanh toán nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Cập nhật trạng thái</h3>
                        @if (in_array($order->order_status, ['CANCELLED', 'RETURNED']))
                            <p class="text-sm text-[#64748B]">Đơn hàng đã ở trạng thái kết thúc, không thể thay đổi.</p>
                        @else
                            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" x-data="{ status: '{{ $order->order_status }}' }">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Trạng thái mới</label>
                                    <select name="order_status" x-model="status"
                                            class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                                        @foreach (['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'PREPARING' => 'Đang đóng gói', 'SHIPPING' => 'Chờ giao hàng', 'COMPLETED' => 'Đã giao', 'RETURNED' => 'Trả hàng', 'CANCELLED' => 'Hủy đơn'] as $value => $label)
                                            <option value="{{ $value }}" @selected($order->order_status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="status === 'CANCELLED'" x-cloak class="mt-3" style="display: none;">
                                    <label class="block text-sm font-medium text-[#64748B] mb-1">Lý do hủy <span class="text-rose-600">*</span></label>
                                    <textarea name="cancel_reason" rows="3" placeholder="Nhập lý do hủy đơn..."
                                              class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"></textarea>
                                </div>
                                <button type="submit" class="mt-4 w-full px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B]">
                                    Lưu thay đổi
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-[#1E293B] mb-4">Lịch sử trạng thái</h3>
                        <ol class="relative border-l border-amber-200 ml-3 space-y-6">
                            @forelse ($order->statusHistories->sortBy('changed_at') as $history)
                                <li class="ml-6">
                                    <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-8 ring-white {{ $loop->first ? 'bg-amber-500' : 'bg-amber-100' }}"></span>
                                    <p class="text-sm font-medium text-[#1E293B]">
                                        {{ $history->from_status ? "{$history->from_status} → " : '' }}{{ $history->to_status }}
                                    </p>
                                    <p class="text-xs text-[#64748B] mt-0.5">
                                        {{ $history->changed_at->format('d/m/Y H:i') }}
                                        {{ $history->changedByUser ? '• ' . $history->changedByUser->full_name : '' }}
                                    </p>
                                    @if ($history->note)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $history->note }}</p>
                                    @endif
                                </li>
                            @empty
                                <li class="ml-6 text-sm text-[#64748B]">Chưa có cập nhật nào.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>

                @if ($order->cancel_reason)
                    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                        <h4 class="text-sm font-semibold text-rose-800 mb-1">Lý do hủy đơn</h4>
                        <p class="text-sm text-rose-700">{{ $order->cancel_reason }}</p>
                    </div>
                @endif

                @if ($order->refund_note)
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                        <h4 class="text-sm font-semibold text-amber-800 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-receipt text-amber-600"></i>
                            <span>Ghi chú đối soát & hoàn tiền</span>
                        </h4>
                        <p class="text-sm text-amber-900">{{ $order->refund_note }}</p>
                    </div>
                @endif

                @if ($order->cancel_rejection_reason)
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-ban text-gray-600"></i>
                            <span>Lý do từ chối hủy đơn</span>
                        </h4>
                        <p class="text-sm text-gray-700">{{ $order->cancel_rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
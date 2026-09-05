@extends('layouts.admin-dashboard')
@section('page-title', "Chi Tiết Đơn Hàng #{$order->order_code}")
@section('content')

<!-- Header Breadcrumb & Actions -->
<div class="flex items-center justify-between flex-wrap gap-3 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left text-xs"></i> Quay lại
        </a>
        <div>
            <h2 class="font-extrabold text-xl text-[#4E342E] flex items-center gap-2">
                Đơn Hàng #{{ $order->order_code }}
                <x-order-status-badge :status="$order->order_status" />
                <x-payment-status-badge :status="$order->payment_status" />
            </h2>
            <p class="text-xs text-[#8E8076] mt-0.5">
                Đặt lúc: {{ $order->created_at->format('d/m/Y H:i:s') }} ({{ $order->created_at->diffForHumans() }})
            </p>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- 1. Timeline Đơn Hàng -->
<div class="panel-card">
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Cột Trái (2 Cols) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Thông tin người nhận & vận chuyển -->
        <div class="panel-card mb-0">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    Thông Tin Người Nhận & Vận Chuyển
                </div>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-100/60">
                    <dt class="text-xs font-bold text-[#8E8076] uppercase">Người nhận hàng</dt>
                    <dd class="font-extrabold text-[#4E342E] text-base mt-0.5">{{ $order->recipient_name }}</dd>
                    @if ($order->customer)
                        <span class="text-[11px] text-[#8D6E63] font-medium"><i class="fa-solid fa-user-tag text-[10px]"></i> Tài khoản: {{ $order->customer->full_name }} ({{ $order->customer->email }})</span>
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
                    <dd class="font-bold text-[#4E342E] mt-0.5">{{ $order->payment_method }}</dd>
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
                    Sản Phẩm Đặt Mua ({{ $order->details->sum('quantity') }} món)
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
                            <tr>
                                <td>
                                    <div class="font-bold text-[#4E342E]">{{ $detail->product_name }}</div>
                                    @if ($detail->product)
                                        <div class="text-[11px] text-[#8E8076]">Mã SP: #{{ $detail->product_id }}</div>
                                    @endif
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
                                <td class="font-bold text-[#4E342E]">{{ $payment->method }}</td>
                                <td class="text-right font-extrabold text-amber-700">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                                <td><x-payment-status-badge :status="$payment->status" /></td>
                                <td class="text-xs text-[#795548] font-mono">{{ $payment->transaction_ref ?? '—' }}</td>
                                <td class="text-xs text-[#8E8076]">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right">
                                    @if ($payment->status === 'PENDING')
                                        <div class="flex justify-end gap-1.5">
                                            <form method="POST" action="{{ route('admin.payments.updateStatus', $payment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="PAID">
                                                <button class="btn btn-success btn-sm">Xác nhận</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.payments.updateStatus', $payment) }}">
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
                    Đơn hàng đã kết thúc ở trạng thái <strong>{{ $order->order_status }}</strong>, không thể cập nhật thêm.
                </div>
            @else
                <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" x-data="{ status: '' }">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold text-[#795548] uppercase mb-1.5">Chuyển sang trạng thái:</label>
                        <select name="order_status" x-model="status" class="select-control" required>
                            <option value="" disabled>Chọn trạng thái mới</option>
                            @foreach (['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'PREPARING' => 'Đang đóng gói', 'SHIPPING' => 'Chờ giao hàng', 'COMPLETED' => 'Đã giao thành công', 'RETURNED' => 'Trả hàng / Hoàn tiền', 'CANCELLED' => 'Hủy đơn hàng'] as $value => $label)
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
                    <li class="ml-5">
                        <span class="absolute flex items-center justify-center w-5 h-5 rounded-full -left-2.5 ring-4 ring-white {{ $loop->last ? 'bg-amber-500 text-white' : 'bg-amber-200 text-amber-800' }}">
                            <i class="fa-solid fa-check text-[9px]"></i>
                        </span>
                        <div class="text-xs font-extrabold text-[#4E342E]">
                            {{ $history->from_status ? "{$history->from_status} → " : '' }}{{ $history->to_status }}
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

        @if ($order->cancel_reason)
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                <h4 class="text-xs font-bold text-rose-800 uppercase mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Lý do hủy đơn hàng
                </h4>
                <p class="text-sm text-rose-700 font-medium">{{ $order->cancel_reason }}</p>
            </div>
        @endif
    </div>
</div>

@endsection
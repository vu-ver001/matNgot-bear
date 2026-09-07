@extends('layouts.staff-dashboard')

@section('page-title', 'Quản Lý Đơn Hàng')

@section('content')
<div>
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

    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm">
        <div class="p-6">
            <div class="mb-6 flex flex-wrap gap-2 items-center">
                @php
                    $tabs = [
                        '' => 'Tất cả',
                        'PENDING' => 'Chờ xác nhận',
                        'PREPARING' => 'Chờ lấy hàng',
                        'SHIPPING' => 'Chờ giao hàng',
                        'COMPLETED' => 'Đã giao',
                        'RETURNED' => 'Trả hàng',
                        'CANCELLED' => 'Đã hủy',
                    ];
                @endphp
                @foreach ($tabs as $value => $label)
                    <a href="{{ route('staff.orders.index', array_merge(request()->except('order_status', 'tab', 'page'), $value ? ['order_status' => $value] : [])) }}"
                       class="px-3 py-1.5 rounded-full text-sm font-medium {{ request('order_status') === $value && request('tab') !== 'cancel_requests' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-[#8B5A2B] hover:bg-amber-100' }}">
                        {{ $label }}
                    </a>
                @endforeach

                {{-- Tab riêng cho Đơn có yêu cầu hủy --}}
                <a href="{{ route('staff.orders.index', array_merge(request()->except('order_status', 'tab', 'page'), ['tab' => 'cancel_requests'])) }}"
                   class="px-3.5 py-1.5 rounded-full text-sm font-bold inline-flex items-center gap-1.5 transition {{ request('tab') === 'cancel_requests' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200' }}">
                    <span>⚠️ Yêu cầu hủy</span>
                    @if(($pendingCancelRequestsCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-black {{ request('tab') === 'cancel_requests' ? 'bg-white text-rose-700' : 'bg-rose-600 text-white animate-pulse' }}">
                            {{ $pendingCancelRequestsCount }}
                        </span>
                    @endif
                </a>

                {{-- Tab riêng cho Đơn cần hoàn tiền --}}
                <a href="{{ route('staff.orders.index', array_merge(request()->except('order_status', 'tab', 'page'), ['tab' => 'need_refund'])) }}"
                   class="px-3.5 py-1.5 rounded-full text-sm font-bold inline-flex items-center gap-1.5 transition {{ request('tab') === 'need_refund' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-300' }}">
                    <span>💰 Cần hoàn tiền</span>
                    @if(($needRefundCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-xs font-black {{ request('tab') === 'need_refund' ? 'bg-white text-amber-800' : 'bg-amber-600 text-white animate-pulse' }}">
                            {{ $needRefundCount }}
                        </span>
                    @endif
                </a>
            </div>

            <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#64748B] mb-1">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Mã đơn / Tên / SĐT"
                           class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#64748B] mb-1">Trạng thái thanh toán</label>
                    <select name="payment_status" class="w-full rounded-xl border-amber-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                        <option value="">Tất cả</option>
                        @foreach (['UNPAID' => 'Chưa thanh toán', 'PENDING' => 'Chờ xác nhận', 'PAID' => 'Đã thanh toán', 'FAILED' => 'Thất bại', 'REFUNDED' => 'Đã hoàn tiền'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-full hover:bg-[#8B5A2B]">Lọc</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-amber-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Mã đơn</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Khách hàng</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">SĐT</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider">Tổng tiền</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap min-w-[140px]">Trạng thái</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap min-w-[150px]">Thanh toán</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap">Ngày đặt</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-[#8B5A2B] uppercase tracking-wider whitespace-nowrap">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-4 text-sm font-medium text-[#1E293B] whitespace-nowrap">{{ $order->order_code }}</td>
                                <td class="px-4 py-4 text-sm text-[#64748B] whitespace-nowrap">{{ $order->customer?->full_name ?? '—' }}</td>
                                <td class="px-4 py-4 text-sm text-[#64748B] whitespace-nowrap">{{ $order->recipient_phone }}</td>
                                <td class="px-4 py-4 text-sm font-medium text-[#1E293B] text-right whitespace-nowrap">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                <td class="px-4 py-4 whitespace-nowrap"><x-order-status-badge :status="$order->order_status" :cancel-request-status="$order->cancel_request_status" :payment-status="$order->payment_status" /></td>
                                <td class="px-4 py-4 whitespace-nowrap"><x-payment-status-badge :status="$order->payment_status" /></td>
                                <td class="px-4 py-4 text-sm text-[#64748B] whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-4 text-right">
                                    @php
                                        $btnClass = 'text-[#8B5A2B] bg-amber-100 hover:bg-amber-200';
                                        $btnText = 'Xử lý';
                                        if ($order->hasPendingCancelRequest()) {
                                            $btnClass = 'text-white bg-rose-600 hover:bg-rose-700 animate-pulse shadow-xs';
                                            $btnText = 'Duyệt hủy';
                                        } elseif ($order->needsRefund()) {
                                            $btnClass = 'text-amber-900 bg-amber-200 hover:bg-amber-300 font-extrabold shadow-xs';
                                            $btnText = 'Hoàn tiền';
                                        }
                                    @endphp
                                    <a href="{{ route('staff.orders.show', $order) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold {{ $btnClass }} rounded-full transition">
                                        <span>{{ $btnText }}</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-[#64748B]">Không có đơn hàng nào phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
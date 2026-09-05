{{-- Shared staff-style order list. Both roles provide scoped $orders and $stats. --}}
<div class="orders-ui">
@include('orders.partials.alerts')
@include('orders.partials.stats')

<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-boxes-packing"></i>
                {{ $isStaff ? 'Danh sách đơn hàng' : 'Đơn hàng của tôi' }}
            </div>
            <div class="panel-subtitle">{{ $isStaff ? 'Tra cứu, lọc và cập nhật tiến trình xử lý đơn hàng' : 'Tra cứu, lọc và theo dõi tiến trình đơn hàng của bạn' }}</div>
        </div>
    </div>

    <!-- Status Tabs (Pills) -->
    @php
        $tabs = [
            '' => ['label' => 'Tất cả', 'count' => $stats['total'] ?? null],
            'PENDING' => ['label' => 'Chờ xác nhận', 'count' => $stats['pending'] ?? 0],
            'CONFIRMED' => ['label' => 'Đã xác nhận', 'count' => $stats['confirmed'] ?? 0],
            'PREPARING' => ['label' => 'Chờ lấy hàng', 'count' => $stats['preparing'] ?? 0],
            'SHIPPING' => ['label' => 'Chờ giao hàng', 'count' => $stats['shipping'] ?? 0],
            'COMPLETED' => ['label' => 'Đã giao', 'count' => $stats['completed'] ?? 0],
            'RETURNED' => ['label' => 'Trả hàng', 'count' => $stats['returned'] ?? 0],
            'CANCELLED' => ['label' => 'Đã hủy', 'count' => $stats['cancelled'] ?? 0],
        ];
    @endphp
    <div class="nav-pills">
        @foreach ($tabs as $value => $tab)
            <a href="{{ route($routePrefix.'.index', array_merge(request()->except('order_status', 'page'), $value ? ['order_status' => $value] : [])) }}"
               class="nav-pill {{ (string) request('order_status') === $value ? 'active' : '' }}">
                <span>{{ $tab['label'] }}</span>
                @if (isset($tab['count']))
                    <span class="nav-pill-count">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @include('orders.partials.filters')

    <!-- Orders Data Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>{{ $isStaff ? 'Khách hàng' : 'Người nhận' }}</th>
                    <th>Số Điện Thoại</th>
                    <th class="text-right">Tổng Tiền</th>
                    <th>Trạng Thái Đơn</th>
                    <th>Thanh Toán</th>
                    <th>Ngày Đặt</th>
                    <th class="text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route($routePrefix.'.show', $order) }}" class="font-bold text-[#4E342E] hover:text-[#B87309] hover:underline">
                                {{ $order->order_code }}
                            </a>
                        </td>
                        <td>
                            <div class="font-bold text-[#4E342E]">{{ $isStaff ? ($order->customer?->full_name ?? $order->recipient_name) : $order->recipient_name }}</div>
                            @if ($isStaff && $order->customer)
                                <div class="text-[11px] text-[#8E8076]"><i class="fa-regular fa-user text-[10px]"></i> Thành viên</div>
                            @elseif ($isStaff)
                                <div class="text-[11px] text-[#8E8076]">Khách vãng lai</div>
                            @endif
                        </td>
                        <td class="text-[#795548] font-medium">{{ $order->recipient_phone }}</td>
                        <td class="text-right font-extrabold text-amber-700">
                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                        </td>
                        <td><x-order-status-badge :status="$order->order_status" /></td>
                        <td><x-payment-status-badge :status="$order->payment_status" /></td>
                        <td class="text-xs text-[#795548]">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right whitespace-nowrap">
                            @include('orders.partials.row-actions')
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-[#8E8076]">
                            <i class="fa-solid fa-box-open text-3xl text-amber-300 mb-2 block"></i>
                            Không tìm thấy đơn hàng nào phù hợp với điều kiện lọc.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-4">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>


</div>

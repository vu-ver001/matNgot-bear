{{-- Shared staff-style order list. Both roles provide scoped $orders and $stats. --}}
<link rel="stylesheet" href="{{ asset('css/order-components.css') }}">

@php
    $bulkShippingOrderIds = $orders
        ->filter(fn ($order) => $order->canTransitionTo('SHIPPING'))
        ->pluck('id')->values()->all();
@endphp
<div class="orders-ui" x-data="bulkOrderManager({{ json_encode($bulkShippingOrderIds) }})">
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
            'SHIPPING' => ['label' => 'Đang giao hàng', 'count' => $stats['shipping'] ?? 0],
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

    <!-- Bulk Operations Toolbar -->
    @include('orders.partials.bulk-toolbar', ['routePrefix' => $routePrefix])

    <!-- Orders Cards List -->
    <div class="orders-cards-container space-y-4">
        @forelse ($orders as $order)
            @include('orders.partials.staff-order-card', [
                'order' => $order,
                'routePrefix' => $routePrefix,
                'isStaff' => $isStaff
            ])
        @empty
            <div class="p-10 text-center text-[#8E8076] bg-white rounded-2xl border border-amber-200/60">
                <i class="fa-solid fa-box-open text-3xl text-amber-300 mb-2 block"></i>
                Không tìm thấy đơn hàng nào phù hợp với điều kiện lọc.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($orders->hasPages())
        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>

</div>

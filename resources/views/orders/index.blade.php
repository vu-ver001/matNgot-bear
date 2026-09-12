{{-- Shared staff-style order list. Both roles provide scoped $orders and $stats. --}}
<link rel="stylesheet" href="{{ asset('css/order-components.css') }}">

@php
    $currentStatus = (string) request('order_status', '');
    $currentTab = (string) request('tab', '');

    if ($currentStatus === 'PENDING') {
        $bulkTargetStatus = 'CONFIRMED';
        $bulkActionLabel = 'Chấp nhận đơn hàng loạt';
        $bulkActionIcon = 'fa-solid fa-circle-check';
        $bulkConfirmTitle = 'Xác nhận chấp nhận đơn hàng loạt?';
        $bulkConfirmText = 'Bạn có chắc chắn muốn chấp nhận :count đơn hàng đã chọn sang trạng thái "Đã xác nhận"?';
        $bulkConfirmButtonText = '<i class="fa-solid fa-circle-check mr-1"></i> Đồng ý chấp nhận';
        $bulkConfirmColor = '#B87309';
        $bulkCountLabel = 'chờ xác nhận';
        $bulkActionableOrderIds = $orders->filter(fn ($o) => $o->canTransitionTo('CONFIRMED'))->pluck('id')->values()->all();
    } elseif ($currentStatus === 'CONFIRMED') {
        $bulkTargetStatus = 'PREPARING';
        $bulkActionLabel = 'Chuẩn bị hàng loạt';
        $bulkActionIcon = 'fa-solid fa-box-open';
        $bulkConfirmTitle = 'Xác nhận chuẩn bị hàng loạt?';
        $bulkConfirmText = 'Bạn có chắc chắn muốn chuyển :count đơn hàng đã chọn sang trạng thái "Chờ lấy hàng"?';
        $bulkConfirmButtonText = '<i class="fa-solid fa-box-open mr-1"></i> Bắt đầu chuẩn bị';
        $bulkConfirmColor = '#2563EB';
        $bulkCountLabel = 'chờ chuẩn bị';
        $bulkActionableOrderIds = $orders->filter(fn ($o) => $o->canTransitionTo('PREPARING'))->pluck('id')->values()->all();
    } elseif ($currentStatus === 'PREPARING') {
        $bulkTargetStatus = 'SHIPPING';
        $bulkActionLabel = 'Giao hàng loạt';
        $bulkActionIcon = 'fa-solid fa-truck-fast';
        $bulkConfirmTitle = 'Xác nhận giao hàng loạt?';
        $bulkConfirmText = 'Bạn có chắc chắn muốn chuyển :count đơn hàng đã chọn sang trạng thái "Đang giao hàng"?';
        $bulkConfirmButtonText = '<i class="fa-solid fa-truck-fast mr-1"></i> Đồng ý giao hàng';
        $bulkConfirmColor = '#E08A1E';
        $bulkCountLabel = 'có thể giao';
        $bulkActionableOrderIds = $orders->filter(fn ($o) => $o->canTransitionTo('SHIPPING'))->pluck('id')->values()->all();
    } else {
        $pendingIds = $orders->filter(fn ($o) => $o->canTransitionTo('CONFIRMED'))->pluck('id')->values()->all();
        $preparingIds = $orders->filter(fn ($o) => $o->canTransitionTo('SHIPPING'))->pluck('id')->values()->all();

        if (count($pendingIds) > 0) {
            $bulkTargetStatus = 'CONFIRMED';
            $bulkActionLabel = 'Chấp nhận đơn hàng loạt';
            $bulkActionIcon = 'fa-solid fa-circle-check';
            $bulkConfirmTitle = 'Xác nhận chấp nhận đơn hàng loạt?';
            $bulkConfirmText = 'Bạn có chắc chắn muốn chấp nhận :count đơn hàng chờ xác nhận đã chọn?';
            $bulkConfirmButtonText = '<i class="fa-solid fa-circle-check mr-1"></i> Đồng ý chấp nhận';
            $bulkConfirmColor = '#B87309';
            $bulkCountLabel = 'chờ xác nhận';
            $bulkActionableOrderIds = $pendingIds;
        } elseif (count($preparingIds) > 0) {
            $bulkTargetStatus = 'SHIPPING';
            $bulkActionLabel = 'Giao hàng loạt';
            $bulkActionIcon = 'fa-solid fa-truck-fast';
            $bulkConfirmTitle = 'Xác nhận giao hàng loạt?';
            $bulkConfirmText = 'Bạn có chắc chắn muốn chuyển :count đơn hàng đã chọn sang trạng thái "Đang giao hàng"?';
            $bulkConfirmButtonText = '<i class="fa-solid fa-truck-fast mr-1"></i> Đồng ý giao hàng';
            $bulkConfirmColor = '#E08A1E';
            $bulkCountLabel = 'có thể giao';
            $bulkActionableOrderIds = $preparingIds;
        } else {
            $bulkTargetStatus = 'CONFIRMED';
            $bulkActionLabel = 'Chấp nhận đơn hàng loạt';
            $bulkActionIcon = 'fa-solid fa-circle-check';
            $bulkConfirmTitle = 'Xác nhận chấp nhận đơn hàng loạt?';
            $bulkConfirmText = 'Bạn có chắc chắn muốn chấp nhận :count đơn hàng đã chọn?';
            $bulkConfirmButtonText = '<i class="fa-solid fa-circle-check mr-1"></i> Đồng ý chấp nhận';
            $bulkConfirmColor = '#B87309';
            $bulkCountLabel = 'hợp lệ';
            $bulkActionableOrderIds = [];
        }
    }

    $bulkConfig = [
        'targetStatus' => $bulkTargetStatus,
        'actionLabel' => $bulkActionLabel,
        'actionIcon' => $bulkActionIcon,
        'confirmTitle' => $bulkConfirmTitle,
        'confirmText' => $bulkConfirmText,
        'confirmButtonText' => $bulkConfirmButtonText,
        'confirmButtonColor' => $bulkConfirmColor,
        'countLabel' => $bulkCountLabel,
    ];

    $showBulkToolbar = !in_array($currentStatus, ['SHIPPING', 'COMPLETED', 'RETURNED', 'CANCELLED'], true) 
        && !in_array($currentTab, ['cancel_requests', 'need_refund'], true);
@endphp
<div class="orders-ui" x-data="bulkOrderManager({{ json_encode($bulkActionableOrderIds) }}, {{ json_encode($bulkConfig) }})">
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
            <a href="{{ route($routePrefix.'.index', array_merge(request()->except('order_status', 'tab', 'page'), $value ? ['order_status' => $value] : [])) }}"
               class="nav-pill {{ (string) request('order_status') === $value && request('tab') !== 'cancel_requests' && request('tab') !== 'need_refund' ? 'active' : '' }}">
                <span>{{ $tab['label'] }}</span>
                @if (isset($tab['count']))
                    <span class="nav-pill-count">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach

        @if($isStaff)
            <a href="{{ route($routePrefix.'.index', array_merge(request()->except('order_status', 'tab', 'page'), ['tab' => 'cancel_requests'])) }}"
               class="nav-pill {{ request('tab') === 'cancel_requests' ? 'active bg-rose-600! text-white!' : 'text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200' }}">
                <span>⚠️ Yêu cầu hủy</span>
                @if(($pendingCancelRequestsCount ?? 0) > 0)
                    <span class="nav-pill-count bg-rose-600 text-white animate-pulse">{{ $pendingCancelRequestsCount }}</span>
                @endif
            </a>

            <a href="{{ route($routePrefix.'.index', array_merge(request()->except('order_status', 'tab', 'page'), ['tab' => 'need_refund'])) }}"
               class="nav-pill {{ request('tab') === 'need_refund' ? 'active bg-amber-600! text-white!' : 'text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-300' }}">
                <span>💰 Cần hoàn tiền</span>
                @if(($needRefundCount ?? 0) > 0)
                    <span class="nav-pill-count bg-amber-600 text-white animate-pulse">{{ $needRefundCount }}</span>
                @endif
            </a>
        @endif
    </div>

    @include('orders.partials.filters')

    <!-- Bulk Operations Toolbar -->
    @include('orders.partials.bulk-toolbar', ['routePrefix' => $routePrefix, 'showBulkToolbar' => $showBulkToolbar])

    <!-- Orders Cards List -->
    <div class="orders-cards-container space-y-4">
        @forelse ($orders as $order)
            @include('orders.partials.staff-order-card', [
                'order' => $order,
                'routePrefix' => $routePrefix,
                'isStaff' => $isStaff,
                'bulkActionableOrderIds' => $bulkActionableOrderIds
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

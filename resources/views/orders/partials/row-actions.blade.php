@php
    $canPayOnline = ! $isStaff
        && in_array($order->payment_method, ['BANK_TRANSFER', 'CARD', 'E_WALLET'])
        && in_array($order->payment_status, ['UNPAID', 'FAILED'])
        && $order->order_status !== 'CANCELLED';
@endphp
<div class="inline-flex items-center gap-1.5" x-data="{
    openActions: false,
    openChangePayment: false,
    menuTop: 0,
    menuLeft: 0,
    toggleActions() {
        if (this.openActions) { this.openActions = false; return; }
        const rect = this.$refs.trigger.getBoundingClientRect();
        this.menuTop = Math.max(8, Math.min(rect.bottom + 8, window.innerHeight - 240));
        this.menuLeft = Math.max(8, Math.min(rect.right - 224, window.innerWidth - 232));
        this.openActions = true;
        this.$nextTick(() => this.$refs.actions.querySelector('a, button').focus());
    }
}" @keydown.escape.window="if (openActions) { openActions = false; $refs.trigger.focus(); }"
   @resize.window="openActions = false" @scroll.window="openActions = false">
    <a href="{{ route($routePrefix.'.show', $order) }}" class="btn btn-outline btn-sm">Chi tiết</a>
    @if (! $isStaff && $order->order_status === 'COMPLETED')
        @php
            $hasReviewed = $order->reviews?->isNotEmpty();
        @endphp
        <button type="button"
                class="btn btn-sm {{ $hasReviewed ? 'btn-outline text-emerald-700 border-emerald-300 hover:bg-emerald-50' : 'bg-amber-600 text-white hover:bg-amber-700' }}"
                data-open-order-review-modal
                data-order-id="{{ $order->id }}"
                title="{{ $hasReviewed ? 'Xem lại đánh giá' : 'Viết đánh giá sản phẩm' }}">
            <i class="fa-solid fa-star text-xs {{ $hasReviewed ? 'text-amber-500' : 'text-white' }}" aria-hidden="true"></i>
            <span>{{ $hasReviewed ? 'Xem đánh giá' : 'Đánh giá' }}</span>
        </button>
    @endif
    <button type="button" class="btn btn-outline btn-sm" x-ref="trigger" @click="toggleActions()"
            :aria-expanded="openActions" aria-label="Thao tác khác cho đơn {{ $order->order_code }}" title="Thao tác khác">
        <i class="fa-solid fa-ellipsis" aria-hidden="true"></i>
    </button>
    <template x-teleport="body">
        <div class="orders-ui order-actions-menu" x-ref="actions" x-show="openActions" x-cloak
             :style="{ top: menuTop + 'px', left: menuLeft + 'px' }"
             @click.outside="openActions = false" @focusout="if (!$el.contains($event.relatedTarget)) openActions = false">
            @unless ($isStaff)
                @include('customer.orders.partials.list-actions')
            @endunless
            <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank" @click="openActions = false">
                <i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Xem hóa đơn
            </a>
        </div>
    </template>
    @if ($canPayOnline)
        <template x-teleport="body">
            @include('customer.orders.partials.payment-method-modal', ['modalState' => 'openChangePayment'])
        </template>
    @endif
</div>

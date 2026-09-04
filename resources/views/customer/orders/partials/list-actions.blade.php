@if ($canPayOnline)
    <a href="{{ $order->payment_method === 'CARD' ? route('customer.payment.vnpay.redirect', $order) : ($order->payment_method === 'E_WALLET' ? route('customer.payment.momo.redirect', $order) : route('customer.payment.qr', $order)) }}">
        <i class="fa-solid fa-credit-card" aria-hidden="true"></i> Thanh toán
    </a>
    <button type="button" @click="openActions = false; openChangePayment = true">
        <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i> Đổi phương thức thanh toán
    </button>
@endif
@if ($order->order_status === 'SHIPPING')
    <form action="{{ route('customer.orders.complete', $order->id) }}" method="POST"
          onsubmit="return confirm('Bạn đã nhận đủ hàng và muốn xác nhận hoàn tất đơn hàng #{{ $order->order_code }}?')">
        @csrf
        <button type="submit"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Đã nhận hàng</button>
    </form>
@endif

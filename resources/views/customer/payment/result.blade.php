@extends('layouts.customer')

@section('title', 'Kết quả thanh toán - Mật Ngọt Bear')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/payment-result.css') }}">
@endsection

@section('content')
<div class="payment-result-container"
     x-data="{
        copied: false,
        copyOrderCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                this.copied = true;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Đã sao chép mã đơn hàng!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
                setTimeout(() => this.copied = false, 2500);
            });
        }
     }">

    <div style="max-width: 960px; width: 100%; margin: 0 auto;">

        {{-- Breadcrumb Navigation --}}
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #7D6B5D; margin-bottom: 16px; padding: 0 4px;">
            <a href="{{ route('home') }}" style="color: #7D6B5D; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                <i class="fa-solid fa-house"></i> Trang chủ
            </a>
            <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #A8988A;"></i>
            <a href="{{ route('customer.cart') }}" style="color: #7D6B5D; text-decoration: none;">Giỏ hàng</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #A8988A;"></i>
            <span style="font-weight: 700; color: #5C3219;">Kết quả thanh toán</span>
        </div>

        {{-- Main Card --}}
        <div class="payment-result-card">
            
            {{-- Top Header Status Banner --}}
            @if($order->payment_status === 'PAID')
                <div class="status-banner-paid">
                    <div class="status-icon-circle">
                        <svg style="width: 38px; height: 38px; color: #ffffff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <h1 class="status-title">
                        THANH TOÁN THÀNH CÔNG!
                    </h1>
                    <p class="status-subtitle">
                        Đơn hàng của bạn đã được thanh toán thành công và đang chờ nhân viên cửa hàng <strong style="color: #ffffff;">Mật Ngọt Bear</strong> xác nhận & đóng gói!
                    </p>

                    {{-- Order Code Badge --}}
                    <div class="order-code-badge">
                        <span>Mã đơn hàng:</span>
                        <span class="order-code-text">#{{ $order->order_code }}</span>
                        <button type="button" @click="copyOrderCode('{{ $order->order_code }}')" class="btn-copy-code" title="Sao chép">
                            <i class="fa-regular fa-copy"></i>
                            <span x-text="copied ? 'Đã chép!' : 'Chép'"></span>
                        </button>
                    </div>
                </div>
            @elseif($order->payment_status === 'FAILED')
                <div class="status-banner-failed">
                    <div class="status-icon-circle">
                        <svg style="width: 38px; height: 38px; color: #ffffff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="status-title">
                        THANH TOÁN CHƯA THÀNH CÔNG
                    </h1>
                    <p class="status-subtitle">
                        Giao dịch chưa được hoàn tất hoặc xảy ra lỗi từ cổng thanh toán. Quý khách có thể quét lại mã QR bên dưới.
                    </p>
                    <div class="order-code-badge">
                        <span>Mã đơn hàng:</span>
                        <span class="order-code-text">#{{ $order->order_code }}</span>
                    </div>
                </div>
            @else
                <div class="status-banner-pending">
                    <div class="status-icon-circle">
                        <svg style="width: 38px; height: 38px; color: #ffffff;" class="fa-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h1 class="status-title">
                        ĐANG CHỜ XÁC NHẬN THANH TOÁN
                    </h1>
                    <p class="status-subtitle">
                        Hệ thống đang tự động kiểm tra biến động số dư từ Ngân hàng / MoMo / VNPay...
                    </p>
                    <div class="order-code-badge">
                        <span>Mã đơn hàng:</span>
                        <span class="order-code-text">#{{ $order->order_code }}</span>
                    </div>
                </div>
            @endif

            {{-- 2-Column Content Layout --}}
            <div style="padding: 28px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">

                {{-- Left Column: Recipient Info & Ordered Items --}}
                <div>

                    {{-- Recipient Info Card --}}
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-card-title">
                                <i class="fa-solid fa-location-dot" style="color: #E08A1E;"></i> Thông tin người nhận
                            </span>
                            <span style="font-size: 11px; font-weight: 700; background: #EBDDCD; color: #5C3219; padding: 2px 8px; border-radius: 9999px;">Giao tận nơi</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                            <div>
                                <div style="color: #786B61; font-size: 12px;">Người nhận:</div>
                                <div style="font-weight: 700; color: #2C1408; margin-top: 2px;">{{ $order->recipient_name }}</div>
                            </div>
                            <div>
                                <div style="color: #786B61; font-size: 12px;">Số điện thoại:</div>
                                <div style="font-weight: 700; color: #2C1408; margin-top: 2px;">{{ $order->recipient_phone }}</div>
                            </div>
                            <div style="grid-column: span 2;">
                                <div style="color: #786B61; font-size: 12px;">Địa chỉ nhận hàng:</div>
                                <div style="font-weight: 600; color: #2C1408; margin-top: 2px; line-height: 1.5;">{{ $order->recipient_address }}</div>
                            </div>
                            @if($order->note)
                                <div style="grid-column: span 2; padding: 10px; background: #ffffff; border-radius: 10px; border: 1px solid #EBDDCD; font-size: 12px; color: #5C3219;">
                                    <strong>Ghi chú:</strong> {{ $order->note }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Ordered Products Card --}}
                    <div class="info-card" style="margin-bottom: 0;">
                        <div class="info-card-header">
                            <span class="info-card-title">
                                <i class="fa-solid fa-box-open" style="color: #E08A1E;"></i> Danh sách sản phẩm ({{ $order->details->sum('quantity') }} bé gấu)
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($order->details as $detail)
                                @php
                                    $itemImage = $detail->product?->images?->first()?->image_url 
                                        ?? 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80';
                                @endphp
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; padding-bottom: 12px; border-bottom: 1px solid rgba(235, 221, 205, 0.6);">
                                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                        <img src="{{ $itemImage }}" alt="{{ $detail->product_name }}" class="product-thumb-img">
                                        <div style="min-width: 0;">
                                            <h4 style="font-weight: 700; font-size: 13px; color: #2C1408; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;">
                                                {{ $detail->product_name }}
                                            </h4>
                                            <div style="font-size: 11px; color: #786B61; margin-top: 4px;">
                                                Số lượng: <strong style="color: #E08A1E; font-weight: 700;">x{{ $detail->quantity }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right; flex-shrink: 0;">
                                        <div style="font-weight: 800; font-size: 14px; color: #5C3219;">
                                            {{ number_format($detail->line_total, 0, ',', '.') }}đ
                                        </div>
                                        <div style="font-size: 10.5px; color: #A8988A;">
                                            {{ number_format($detail->product_price, 0, ',', '.') }}đ/bé
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Right Column: Payment Receipt & Navigation Actions --}}
                <div>

                    {{-- Transaction Details Card --}}
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-card-title">
                                <i class="fa-solid fa-receipt" style="color: #E08A1E;"></i> Chi tiết giao dịch
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                            {{-- Method --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(235, 221, 205, 0.7);">
                                <span style="color: #786B61;">Phương thức:</span>
                                <span style="font-weight: 700; color: #2C1408;">
                                    @if($order->payment_method === 'CARD')
                                        💳 Cổng VNPAY (ATM/Visa/QR)
                                    @elseif($order->payment_method === 'E_WALLET')
                                        Ví MoMo
                                    @elseif($order->payment_method === 'BANK_TRANSFER')
                                        🏦 Chuyển khoản VietQR
                                    @else
                                        💵 Thu hộ COD
                                    @endif
                                </span>
                            </div>

                            {{-- Transaction Ref --}}
                            @if(!empty($latestPayment?->transaction_ref))
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(235, 221, 205, 0.7);">
                                <span style="color: #786B61;">Mã giao dịch cổng:</span>
                                <span style="font-family: monospace; font-weight: 700; color: #2C1408;">{{ $latestPayment->transaction_ref }}</span>
                            </div>
                            @endif

                            {{-- Payment Status --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(235, 221, 205, 0.7);">
                                <span style="color: #786B61;">Trạng thái thanh toán:</span>
                                @if($order->payment_status === 'PAID')
                                    <span class="status-pill-paid">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span> Đã thanh toán
                                    </span>
                                @elseif($order->payment_status === 'FAILED')
                                    <span style="background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                                        Thất bại
                                    </span>
                                @else
                                    <span class="status-pill-pending">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #D97706;"></span> Chờ xác nhận
                                    </span>
                                @endif
                            </div>

                            {{-- Order Status --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(235, 221, 205, 0.7);">
                                <span style="color: #786B61;">Trạng thái đơn hàng:</span>
                                @if($order->order_status === 'PENDING')
                                    <span style="background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                                        Chờ nhân viên xác nhận
                                    </span>
                                @elseif($order->order_status === 'CONFIRMED')
                                    <span style="background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                                        Đã xác nhận
                                    </span>
                                @elseif($order->order_status === 'SHIPPING')
                                    <span style="background: #E0E7FF; color: #3730A3; border: 1px solid #C7D2FE; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                                        Đang giao hàng
                                    </span>
                                @elseif($order->order_status === 'COMPLETED')
                                    <span style="background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                                        Hoàn thành
                                    </span>
                                @else
                                    <span style="background: #F3F4F6; color: #4B5563; border: 1px solid #D1D5DB; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                                        {{ $order->order_status }}
                                    </span>
                                @endif
                            </div>

                            {{-- Subtotal & Fees --}}
                            <div style="display: flex; justify-content: space-between; color: #786B61; font-size: 12px; padding-top: 4px;">
                                <span>Tiền hàng:</span>
                                <span style="font-weight: 700; color: #2C1408;">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                            </div>
                            @if($order->discount_amount > 0)
                            <div style="display: flex; justify-content: space-between; color: #DC2626; font-size: 12px; font-weight: 600;">
                                <span>Giảm giá voucher:</span>
                                <span>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                            </div>
                            @endif
                            @if($order->shipping_discount_amount > 0)
                            <div style="display: flex; justify-content: space-between; color: #059669; font-size: 12px; font-weight: 600;">
                                <span>Giảm ship voucher:</span>
                                <span>-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }}đ</span>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; color: #786B61; font-size: 12px;">
                                <span>Phí vận chuyển:</span>
                                <span style="font-weight: 700; color: #2C1408;">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                            </div>

                            {{-- Total Amount --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; margin-top: 4px; border-top: 1px solid #EBDDCD;">
                                <span style="font-size: 13px; font-weight: 900; color: #5C3219;">TỔNG THANH TOÁN:</span>
                                <span style="font-size: 22px; font-weight: 900; color: #C45E1B; letter-spacing: -0.5px;">
                                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Navigation Buttons --}}
                    <div style="margin-top: 16px;">
                        @if($order->payment_status === 'PAID')
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn-primary-action">
                                <i class="fa-solid fa-box-archive"></i>
                                <span>Xem chi tiết & Theo dõi đơn hàng</span>
                            </a>

                            <a href="{{ route('home') }}" class="btn-secondary-action">
                                <i class="fa-solid fa-bag-shopping" style="color: #E08A1E;"></i>
                                <span>Tiếp tục mua sắm gấu bông</span>
                            </a>

                            <div style="text-align: center; margin-top: 12px;">
                                <a href="{{ route('customer.cart') }}" style="font-size: 12px; color: #786B61; text-decoration: underline;">
                                    <i class="fa-solid fa-cart-shopping"></i> Xem giỏ hàng của bạn
                                </a>
                            </div>
                        @else
                            <a href="{{ route('customer.payment.qr', $order->id) }}" class="btn-primary-action">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>Mở lại mã QR thanh toán</span>
                            </a>

                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn-secondary-action">
                                <i class="fa-solid fa-clock-rotate-left" style="color: #E08A1E;"></i>
                                <span>Xem chi tiết đơn hàng</span>
                            </a>
                        @endif
                    </div>

                    {{-- Security Guarantee Badge --}}
                    <div style="background: #ffffff; border: 1px solid #EBDDCD; border-radius: 14px; padding: 14px; margin-top: 18px; font-size: 11.5px; color: #786B61; display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-shield-halved" style="color: #059669;"></i>
                            <span style="font-weight: 600; color: #2C1408;">Bảo mật thanh toán chuẩn SSL 256-bit</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-headset" style="color: #E08A1E;"></i>
                            <span>Hotline hỗ trợ 24/7: <strong style="color: #5C3219;">0377.466.205</strong></span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
@endsection

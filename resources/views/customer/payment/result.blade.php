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
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #795548; margin-bottom: 16px; padding: 0 4px;">
            <a href="{{ route('home') }}" style="color: #795548; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                <i class="fa-solid fa-house"></i> Trang chủ
            </a>
            <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #BCAAA4;"></i>
            <a href="{{ route('customer.cart') }}" style="color: #795548; text-decoration: none;">Giỏ hàng</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 9px; color: #BCAAA4;"></i>
            <span style="font-weight: 700; color: #4E342E;">Kết quả thanh toán</span>
        </div>

        {{-- Main Card --}}
        <div class="payment-result-card">
            
            {{-- Top Header Status Banner --}}
            @if($order->payment_status === 'PAID')
                <div class="status-banner-paid">
                    {{-- Decorative Watermarks --}}
                    <div style="position: absolute; right: -15px; bottom: -25px; color: rgba(255,255,255,0.04); font-size: 130px; font-weight: 900; pointer-events: none; user-select: none;">
                        🧸
                    </div>
                    <div style="position: absolute; left: -15px; top: -25px; color: rgba(255,255,255,0.03); font-size: 130px; font-weight: 900; pointer-events: none; user-select: none;">
                        🍯
                    </div>

                    <div class="status-icon-circle">
                        <svg style="width: 38px; height: 38px; color: #81C784;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
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
            @elseif($order->payment_status === 'FAILED' || $order->payment_status === 'UNPAID')
                <div class="status-banner-failed">
                    <div class="status-icon-circle icon-failed">
                        <svg style="width: 38px; height: 38px; color: #EF9A9A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h1 class="status-title">
                        ĐƠN HÀNG CHỜ THANH TOÁN
                    </h1>
                    <p class="status-subtitle">
                        Thanh toán trực tuyến chưa hoàn tất. Đơn hàng của bạn đã được ghi nhận trong hệ thống và được giữ trong <strong>24 giờ</strong>. Vui lòng thanh toán lại trước khi đơn tự động hủy.
                    </p>
                    <div class="order-code-badge">
                        <span>Mã đơn hàng:</span>
                        <span class="order-code-text">#{{ $order->order_code }}</span>
                        <button type="button" @click="copyOrderCode('{{ $order->order_code }}')" class="btn-copy-code" title="Sao chép">
                            <i class="fa-regular fa-copy"></i>
                            <span x-text="copied ? 'Đã chép!' : 'Chép'"></span>
                        </button>
                    </div>
                </div>
            @else
                <div class="status-banner-pending">
                    <div class="status-icon-circle icon-pending">
                        <svg style="width: 38px; height: 38px; color: #FFE082;" class="fa-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h1 class="status-title">
                        ĐANG CHỜ XÁC NHẬN THANH TOÁN
                    </h1>
                    <p class="status-subtitle">
                        Hệ thống đang tự động kiểm tra biến động số dư từ Cổng thanh toán / Ngân hàng...
                    </p>
                    <div class="order-code-badge">
                        <span>Mã đơn hàng:</span>
                        <span class="order-code-text">#{{ $order->order_code }}</span>
                        <button type="button" @click="copyOrderCode('{{ $order->order_code }}')" class="btn-copy-code" title="Sao chép">
                            <i class="fa-regular fa-copy"></i>
                            <span x-text="copied ? 'Đã chép!' : 'Chép'"></span>
                        </button>
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
                            <span style="font-size: 11px; font-weight: 700; background: #FFF3E0; color: #C45E1B; padding: 2px 8px; border-radius: 9999px; border: 1px solid #FFE0B2;">Giao tận nơi</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                            <div>
                                <div style="color: #795548; font-size: 12px;">Người nhận:</div>
                                <div style="font-weight: 700; color: #4E342E; margin-top: 2px;">{{ $order->recipient_name }}</div>
                            </div>
                            <div>
                                <div style="color: #795548; font-size: 12px;">Số điện thoại:</div>
                                <div style="font-weight: 700; color: #4E342E; margin-top: 2px;">{{ $order->recipient_phone }}</div>
                            </div>
                            <div style="grid-column: span 2;">
                                <div style="color: #795548; font-size: 12px;">Địa chỉ nhận hàng:</div>
                                <div style="font-weight: 600; color: #4E342E; margin-top: 2px; line-height: 1.5;">{{ $order->recipient_address }}</div>
                            </div>
                            @if($order->note)
                                <div style="grid-column: span 2; padding: 10px; background: #FFF8E7; border-radius: 10px; border: 1px solid #F6D89B; font-size: 12px; color: #5D4037;">
                                    <strong style="color: #C45E1B;">Ghi chú:</strong> {{ $order->note }}
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
                                    $prod = $detail->product;
                                    $rawImage = $detail->effective_image ?? ($prod?->images?->firstWhere('is_primary', true)?->image_url ?? $prod?->images?->first()?->image_url);
                                    $imageUrl = !empty($rawImage) 
                                        ? ((str_starts_with($rawImage, 'http') || str_starts_with($rawImage, 'data:')) ? $rawImage : asset($rawImage)) 
                                        : null;
                                @endphp
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; padding-bottom: 12px; border-bottom: 1px solid rgba(234, 223, 207, 0.7);">
                                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                        <div class="product-thumb-container">
                                            @if(!empty($imageUrl))
                                                <img src="{{ $imageUrl }}" alt="{{ $detail->product_name }}" class="product-thumb-img"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="product-thumb-fallback" style="display: none;">
                                                    <i class="fa-regular fa-image" style="font-size: 13px;"></i>
                                                    <span style="margin-top: 2px;">Không ảnh</span>
                                                </div>
                                            @else
                                                <div class="product-thumb-fallback">
                                                    <i class="fa-regular fa-image" style="font-size: 13px;"></i>
                                                    <span style="margin-top: 2px;">Không ảnh</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div style="min-width: 0;">
                                            <h4 style="font-weight: 700; font-size: 13px; color: #4E342E; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;">
                                                {{ $detail->product_name }}
                                            </h4>
                                            <div style="font-size: 11px; color: #795548; margin-top: 4px;">
                                                Số lượng: <strong style="color: #E08A1E; font-weight: 700;">x{{ $detail->quantity }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right; flex-shrink: 0;">
                                        <div style="font-weight: 800; font-size: 14px; color: #4E342E;">
                                            {{ number_format($detail->line_total, 0, ',', '.') }}đ
                                        </div>
                                        <div style="font-size: 10.5px; color: #9E8076;">
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
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(234, 223, 207, 0.7);">
                                <span style="color: #795548;">Phương thức:</span>
                                <span style="font-weight: 700; color: #4E342E;">
                                    @if($order->payment_method === 'CARD')
                                        Cổng VNPAY (ATM/Visa/QR)
                                    @elseif($order->payment_method === 'E_WALLET')
                                        Ví MoMo
                                    @elseif($order->payment_method === 'BANK_TRANSFER')
                                        Chuyển khoản VietQR
                                    @else
                                        Thanh toán khi nhận hàng (COD)
                                    @endif
                                </span>
                            </div>

                            {{-- Transaction Ref --}}
                            @if(!empty($latestPayment?->transaction_ref))
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(234, 223, 207, 0.7);">
                                <span style="color: #795548;">Mã giao dịch cổng:</span>
                                <span style="font-family: monospace; font-weight: 700; color: #4E342E;">{{ $latestPayment->transaction_ref }}</span>
                            </div>
                            @endif

                            {{-- Payment Status --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(234, 223, 207, 0.7);">
                                <span style="color: #795548;">Trạng thái thanh toán:</span>
                                @if($order->payment_status === 'PAID')
                                    <span class="status-pill-paid">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #2E7D32;"></span> Đã thanh toán
                                    </span>
                                @elseif($order->payment_status === 'FAILED' || $order->payment_status === 'UNPAID')
                                    <span class="status-pill-pending">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #D97706;"></span> Chờ thanh toán
                                    </span>
                                @else
                                    <span class="status-pill-pending">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #B87309;"></span> Chờ xác nhận
                                    </span>
                                @endif
                            </div>

                            {{-- Order Status --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px solid rgba(234, 223, 207, 0.7);">
                                <span style="color: #795548;">Trạng thái đơn hàng:</span>
                                @if($order->order_status === 'PENDING')
                                    <span class="status-pill-pending">
                                        Chờ xác nhận
                                    </span>
                                @elseif(in_array($order->order_status, ['CONFIRMED', 'PREPARING']))
                                    <span class="status-pill-confirmed">
                                        Đang chuẩn bị
                                    </span>
                                @elseif($order->order_status === 'SHIPPING')
                                    <span class="status-pill-shipping">
                                        Đang giao hàng
                                    </span>
                                @elseif($order->order_status === 'COMPLETED')
                                    <span class="status-pill-completed">
                                        Đã giao
                                    </span>
                                @else
                                    <span class="status-pill-shipping">
                                        {{ $order->order_status }}
                                    </span>
                                @endif
                            </div>

                            {{-- Subtotal & Fees --}}
                            <div style="display: flex; justify-content: space-between; color: #795548; font-size: 12px; padding-top: 4px;">
                                <span>Tiền hàng:</span>
                                <span style="font-weight: 700; color: #4E342E;">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                            </div>
                            @if($order->discount_amount > 0)
                            <div style="display: flex; justify-content: space-between; color: #C62828; font-size: 12px; font-weight: 600;">
                                <span>Giảm giá voucher:</span>
                                <span>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                            </div>
                            @endif
                            @if($order->shipping_discount_amount > 0)
                            <div style="display: flex; justify-content: space-between; color: #2E7D32; font-size: 12px; font-weight: 600;">
                                <span>Giảm ship voucher:</span>
                                <span>-{{ number_format($order->shipping_discount_amount, 0, ',', '.') }}đ</span>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; color: #795548; font-size: 12px;">
                                <span>Phí vận chuyển:</span>
                                <span style="font-weight: 700; color: #4E342E;">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                            </div>

                            {{-- Total Amount --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; margin-top: 4px; border-top: 1px solid #EADFCF;">
                                <span style="font-size: 13px; font-weight: 900; color: #4E342E;">TỔNG THANH TOÁN:</span>
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
                                <a href="{{ route('customer.cart') }}" style="font-size: 12px; color: #795548; text-decoration: underline;">
                                    <i class="fa-solid fa-cart-shopping"></i> Xem giỏ hàng của bạn
                                </a>
                            </div>
                        @else
                            <a href="{{ route('customer.orders.show', $order->id) }}" class="btn-primary-action">
                                <i class="fa-solid fa-credit-card"></i>
                                <span>Thanh toán lại đơn hàng này</span>
                            </a>

                            <a href="{{ route('customer.payment.qr', $order->id) }}" class="btn-secondary-action">
                                <i class="fa-solid fa-qrcode" style="color: #E08A1E;"></i>
                                <span>Quét mã QR thanh toán</span>
                            </a>

                            @if($order->paymentExpiresAt())
                                <div style="margin-top: 12px; font-size: 11.5px; color: #B87309; background: #FFF8E7; border: 1px solid #F6D89B; border-radius: 10px; padding: 8px 12px; text-align: center;">
                                    <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                                    <span>Hạn thanh toán: <strong>{{ $order->paymentExpiresAt()->format('H:i - d/m/Y') }}</strong> (Tự động hủy sau 24h đặt hàng)</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Security Guarantee Badge --}}
                    <div style="background: #ffffff; border: 1px solid #EADFCF; border-radius: 14px; padding: 14px; margin-top: 18px; font-size: 11.5px; color: #795548; display: flex; flex-direction: column; gap: 6px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-shield-halved" style="color: #2E7D32;"></i>
                            <span style="font-weight: 600; color: #4E342E;">Cam kết bảo mật thanh toán</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-headset" style="color: #E08A1E;"></i>
                            <span>Hotline hỗ trợ 24/7: <strong style="color: #C45E1B;">0377.466.205</strong></span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
@endsection

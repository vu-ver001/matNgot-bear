@extends('layouts.customer-account', ['title' => 'Tin nhắn / Hỗ trợ'])

@section('content')
<div
    class="customer-chat-container"
    data-customer-chat
    data-poll-url="{{ route('customer.messages.poll') }}"
>
    {{-- CỘT TRÁI: KHUNG TRÒ CHUYỆN CHÍNH --}}
    <div class="customer-chat-main">
        {{-- HEADER --}}
        <div class="customer-chat-header">
            <div class="customer-chat-header__avatar-wrap">
                <img
                    src="{{ asset('images/auth/bear-hero.png') }}"
                    alt="Mật Ngọt Bear Support"
                    class="customer-chat-header__avatar"
                    onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                >
            </div>
            <div class="customer-chat-header__info">
                <div class="customer-chat-header__title-row">
                    <h2 class="customer-chat-header__name">Mật Ngọt Bear Support</h2>
                    <span class="customer-chat-header__badge-online">Đang hỗ trợ</span>
                </div>
                <p class="customer-chat-header__subtext">Thường phản hồi trong vài phút</p>
            </div>
        </div>

        {{-- DANH SÁCH TIN NHẮN --}}
        <div class="customer-chat-body" data-chat-body>
            {{-- Tin nhắn mở đầu mặc định nếu chưa từng chat --}}
            @if ($messages->isEmpty())
                <div class="customer-chat-date-divider">
                    <span>Hôm nay, {{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="customer-chat-msg-row is-shop" data-msg-id="0">
                    <img
                        src="{{ asset('images/auth/bear-hero.png') }}"
                        alt="Mật Ngọt Bear"
                        class="customer-chat-msg-row__avatar"
                        onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                    >
                    <div class="customer-chat-msg-wrap">
                        <div class="customer-chat-bubble">
                            Xin chào bạn! 👋<br>
                            Mật Ngọt Bear rất vui được hỗ trợ bạn. Bạn cần tư vấn về sản phẩm, đơn hàng hay vấn đề nào khác ạ?
                        </div>
                        <div class="customer-chat-meta">
                            <span>{{ now()->format('H:i') }}</span>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $groupedMessages = $messages->groupBy(function ($m) {
                        return $m->sent_at ? $m->sent_at->format('d/m/Y') : now()->format('d/m/Y');
                    });
                @endphp

                @foreach ($groupedMessages as $dateStr => $msgsInDate)
                    <div class="customer-chat-date-divider">
                        <span>
                            @if ($dateStr === now()->format('d/m/Y'))
                                Hôm nay, {{ $dateStr }}
                            @elseif ($dateStr === now()->subDay()->format('d/m/Y'))
                                Hôm qua, {{ $dateStr }}
                            @else
                                {{ $dateStr }}
                            @endif
                        </span>
                    </div>

                    @foreach ($msgsInDate as $msg)
                        @php
                            $isSelf = (int) $msg->sender_id === (int) $customer->id;
                        @endphp
                        <div
                            class="customer-chat-msg-row {{ $isSelf ? 'is-customer' : 'is-shop' }}"
                            data-msg-id="{{ $msg->id }}"
                        >
                            @if (! $isSelf)
                                <img
                                    src="{{ asset('images/auth/bear-hero.png') }}"
                                    alt="Mật Ngọt Bear"
                                    class="customer-chat-msg-row__avatar"
                                    onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                                >
                            @endif
                            <div class="customer-chat-msg-wrap">
                                <div class="customer-chat-bubble">
                                    @if (str_contains($msg->content, '📦 [ĐƠN HÀNG #'))
                                        @php
                                            preg_match('/#([A-Z0-9\-]+)/', $msg->content, $custCodeMatches);
                                            $custParsedCode = $custCodeMatches[1] ?? null;
                                            $custOrder = $custParsedCode ? \App\Models\Order::with('details.product')->where('order_code', $custParsedCode)->first() : null;
                                        @endphp
                                        @if ($custOrder)
                                            @php
                                                $custDetail = $custOrder->details->first();
                                                $custProd = $custDetail?->product;
                                                $custImg = $custProd?->primary_image_url ?? $custProd?->image_url ?? asset('images/auth/bear-hero.png');
                                                $custOthers = $custOrder->details->count() - 1;
                                            @endphp
                                            <div class="chat-order-card">
                                                <div class="chat-order-card__header">
                                                    <span class="chat-order-card__tag">
                                                        <i class="fa-solid fa-box"></i> Đơn hàng #{{ $custOrder->order_code }}
                                                    </span>
                                                    <span class="chat-order-card__status">{{ $custOrder->order_status }}</span>
                                                </div>
                                                <div class="chat-order-card__body">
                                                    <img src="{{ $custImg }}" alt="{{ $custOrder->order_code }}" class="chat-order-card__img" onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'">
                                                    <div class="chat-order-card__info">
                                                        <div class="chat-order-card__pname">{{ $custDetail?->product_name ?? 'Đơn hàng' }}</div>
                                                        @if ($custOthers > 0)
                                                            <div class="chat-order-card__other">+{{ $custOthers }} sản phẩm khác</div>
                                                        @endif
                                                        <div class="chat-order-card__total">Tổng tiền: <strong>{{ number_format($custOrder->total_amount, 0, ',', '.') }} đ</strong></div>
                                                    </div>
                                                </div>
                                                <div class="chat-order-card__footer">
                                                    <a href="{{ route('customer.orders.show', $custOrder) }}" class="chat-order-card__link">
                                                        <span>Xem chi tiết đơn hàng</span>
                                                        <i class="fa-solid fa-chevron-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            {!! nl2br(e($msg->content)) !!}
                                        @endif
                                    @else
                                        {!! nl2br(e($msg->content)) !!}
                                    @endif
                                </div>
                                <div class="customer-chat-meta">
                                    <span>{{ $msg->sent_at ? $msg->sent_at->format('H:i') : '' }}</span>
                                    @if ($isSelf)
                                        <span class="customer-chat-check-icon" title="{{ $msg->is_read ? 'Đã xem' : 'Đã gửi' }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                                <path d="M18 6 7 17l-5-5"/>
                                                <path d="m22 10-7.5 7.5L13 16"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @endif
        </div>

        {{-- Ô NHẬP TIN NHẮN (FOOTER) --}}
        <div class="customer-chat-footer">
            <form
                action="{{ route('customer.messages.send') }}"
                method="POST"
                class="customer-chat-form"
                data-chat-form
            >
                @csrf
                <div class="customer-chat-input-wrap">
                    <button type="button" class="customer-chat-btn-icon" title="Đính kèm tệp / ảnh" aria-label="Đính kèm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                        </svg>
                    </button>

                    <input
                        type="text"
                        name="content"
                        class="customer-chat-input"
                        placeholder="Nhập tin nhắn..."
                        required
                        maxlength="2000"
                        autocomplete="off"
                        data-chat-input
                    >

                    <button type="button" class="customer-chat-btn-icon" title="Biểu tượng cảm xúc" aria-label="Emoji">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" x2="9.01" y1="9" y2="9"/>
                            <line x1="15" x2="15.01" y1="9" y2="9"/>
                        </svg>
                    </button>
                </div>

                <button
                    type="submit"
                    class="customer-chat-btn-send"
                    data-chat-submit
                >
                    <span>Gửi</span>
                </button>
            </form>
            <p class="customer-chat-footer__subtext">Bạn có thể gửi hình ảnh khi cần thiết. Dung lượng tối đa 5MB.</p>
        </div>
    </div>

    {{-- CỘT PHẢI: CÁC WIDGET THÔNG TIN HỖ TRỢ (MOCKUP ẢNH 2) --}}
    <aside class="customer-chat-sidebar">
        {{-- CARD 1: LỜI NHẮN ẤM ÁP --}}
        <div class="customer-chat-card-welcome">
            <p>Mọi thắc mắc của bạn đều rất quan trọng. Hãy nhắn tin cho <strong>Mật Ngọt Bear</strong> nhé! ♡</p>
        </div>

        {{-- CARD 2: THỜI GIAN HỖ TRỢ --}}
        <div class="customer-chat-card-hours">
            <h3 class="customer-chat-card-hours__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color: #a05a2c;">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Thời gian hỗ trợ</span>
            </h3>
            <div class="customer-chat-card-hours__content">
                <div class="customer-chat-card-hours__icon-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                        <line x1="16" x2="16" y1="2" y2="6"/>
                        <line x1="8" x2="8" y1="2" y2="6"/>
                        <line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                </div>
                <div>
                    <div class="customer-chat-card-hours__days">Thứ 2 - Chủ nhật</div>
                    <div class="customer-chat-card-hours__time">8:00 - 22:00</div>
                </div>
            </div>
        </div>

        {{-- CARD 3: CÂU HỎI THƯỜNG GẶP (FAQ) --}}
        <div class="customer-chat-card-faq">
            <h3 class="customer-chat-card-faq__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color: #a05a2c;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
                <span>Câu hỏi thường gặp</span>
            </h3>

            <ul class="customer-chat-faq-list">
                <li class="customer-chat-faq-item" data-faq-question="Làm sao để kiểm tra đơn hàng?">
                    <span>Làm sao để kiểm tra đơn hàng?</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </li>
                <li class="customer-chat-faq-item" data-faq-question="Chính sách đổi trả như thế nào?">
                    <span>Chính sách đổi trả như thế nào?</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </li>
                <li class="customer-chat-faq-item" data-faq-question="Thời gian giao hàng bao lâu?">
                    <span>Thời gian giao hàng bao lâu?</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </li>
                <li class="customer-chat-faq-item" data-faq-question="Tôi muốn hủy đơn hàng thì làm thế nào?">
                    <span>Tôi muốn hủy đơn hàng</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </li>
                <li class="customer-chat-faq-item" data-faq-question="Sản phẩm có bảo hành không?">
                    <span>Sản phẩm có bảo hành không?</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </li>
            </ul>

            <a href="javascript:void(0)" class="customer-chat-faq-btn-all" onclick="document.querySelector('[data-faq-question]')?.click()">
                <span>Xem tất cả câu hỏi</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </aside>
</div>
@endsection

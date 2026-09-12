@extends('layouts.customer-account', ['title' => 'Tin nhắn / Hỗ trợ', 'flush' => true])

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
                    <span
                        class="customer-chat-header__badge-online {{ $headerStatus['class'] ?? 'is-online' }}"
                        data-support-status-badge
                    >{{ $headerStatus['label'] ?? 'Đang trực tuyến' }}</span>
                </div>
                <p class="customer-chat-header__subtext" data-support-status-subtext>
                    {{ $headerStatus['subtext'] ?? 'Thường phản hồi trong vài phút' }}
                </p>
            </div>
        </div>

        {{-- DANH SÁCH TIN NHẮN --}}
        <div class="customer-chat-body" data-chat-body>
            {{-- Tin nhắn mở đầu mặc định nếu chưa từng chat --}}
            @if ($messages->isEmpty())
                <div class="customer-chat-date-divider">
                    <span>Hôm nay, {{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="customer-chat-msg-row is-shop is-greeting-prompt" data-greeting-prompt="1">
                    <img
                        src="{{ asset('images/auth/bear-hero.png') }}"
                        alt="Mật Ngọt Bear"
                        class="customer-chat-msg-row__avatar"
                        onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                    >
                    <div class="customer-chat-msg-wrap">
                        <div class="customer-chat-bubble">
                            <div class="chat-msg-text">
                                Xin chào bạn! 👋<br>
                                Mật Ngọt Bear rất vui được hỗ trợ bạn. Bạn cần tư vấn về sản phẩm, đơn hàng hay vấn đề nào khác ạ?
                            </div>
                            <div class="chat-msg-time">
                                <span>{{ now()->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $groupedMessages = $messages->groupBy(function ($m) {
                        return $m->sent_at ? $m->sent_at->format('d/m/Y') : now()->format('d/m/Y');
                    });
                    $lastMsg = $messages->last();
                    $lastMsgId = $lastMsg?->id;
                    $isLastMsgSelf = $lastMsg && ((int) $lastMsg->sender_id === (int) $customer->id);
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

                    @php
                        $msgsInDate = $msgsInDate->values();
                        $totalInDate = $msgsInDate->count();
                    @endphp

                    @foreach ($msgsInDate as $idx => $msg)
                        @php
                            $isSelf = (int) $msg->sender_id === (int) $customer->id;
                            $prevMsg = $idx > 0 ? $msgsInDate[$idx - 1] : null;
                            $nextMsg = $idx < $totalInDate - 1 ? $msgsInDate[$idx + 1] : null;

                            $isPrevSame = $prevMsg
                                && (int) $prevMsg->sender_id === (int) $msg->sender_id
                                && $msg->sent_at && $prevMsg->sent_at
                                && abs($msg->sent_at->diffInMinutes($prevMsg->sent_at)) <= 10;

                            $isNextSame = $nextMsg
                                && (int) $nextMsg->sender_id === (int) $msg->sender_id
                                && $msg->sent_at && $nextMsg->sent_at
                                && abs($nextMsg->sent_at->diffInMinutes($msg->sent_at)) <= 10;

                            if (! $isPrevSame && ! $isNextSame) {
                                $groupPos = 'pos-single';
                            } elseif (! $isPrevSame && $isNextSame) {
                                $groupPos = 'pos-first';
                            } elseif ($isPrevSame && $isNextSame) {
                                $groupPos = 'pos-middle';
                            } else {
                                $groupPos = 'pos-last';
                            }

                            $showAvatar = ($groupPos === 'pos-last' || $groupPos === 'pos-single');
                            $showTime = ($groupPos === 'pos-last' || $groupPos === 'pos-single');
                        @endphp
                        <div
                            class="customer-chat-msg-row {{ $isSelf ? 'is-customer' : 'is-shop' }} {{ $groupPos }} {{ ! empty($msg->image_urls) ? 'has-gallery' : '' }}"
                            data-msg-id="{{ $msg->id }}"
                            data-sender-id="{{ $msg->sender_id }}"
                            data-is-self="{{ $isSelf ? '1' : '0' }}"
                            data-timestamp="{{ $msg->sent_at ? $msg->sent_at->timestamp : '' }}"
                        >
                            @if (! $isSelf)
                                @if ($showAvatar)
                                    <img
                                        src="{{ asset('images/auth/bear-hero.png') }}"
                                        alt="Mật Ngọt Bear"
                                        class="customer-chat-msg-row__avatar"
                                        onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                                    >
                                @else
                                    <div class="customer-chat-msg-row__avatar is-spacer" aria-hidden="true"></div>
                                @endif
                            @endif
                            <div class="customer-chat-msg-wrap">
                                @php
                                    $custMsgImages = $msg->image_urls;
                                @endphp
                                @if (! empty($custMsgImages))
                                    <div class="chat-msg-gallery {{ count($custMsgImages) === 1 ? 'is-single' : (count($custMsgImages) === 2 ? 'is-double' : 'is-grid') }}">
                                        @foreach ($custMsgImages as $imgUrl)
                                            <div class="chat-msg-image-wrap">
                                                <img src="{{ $imgUrl }}" alt="Hình ảnh đính kèm" class="chat-msg-image" loading="lazy" onclick="window.open(this.src, '_blank')">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($msg->content)
                                    <div class="customer-chat-bubble" title="{{ $msg->sent_at ? $msg->sent_at->format('H:i, d/m/Y') : '' }}">
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
                                                            <i class="fa-solid fa-box"></i> #{{ $custOrder->order_code }}
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
                                                <div class="chat-msg-text">{!! nl2br(e($msg->content)) !!}</div>
                                            @endif
                                        @else
                                            <div class="chat-msg-text">{!! nl2br(e($msg->content)) !!}</div>
                                        @endif
                                        @if ($showTime && $msg->sent_at)
                                            <div class="chat-msg-time customer-chat-time">
                                                <span>{{ $msg->sent_at->format('H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @elseif (empty($custMsgImages))
                                    <div class="customer-chat-bubble" title="{{ $msg->sent_at ? $msg->sent_at->format('H:i, d/m/Y') : '' }}">
                                        @if ($showTime && $msg->sent_at)
                                            <div class="chat-msg-time customer-chat-time">
                                                <span>{{ $msg->sent_at->format('H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($showTime && $msg->sent_at)
                                    <div class="chat-msg-time customer-chat-time is-outside">
                                        <span>{{ $msg->sent_at->format('H:i') }}</span>
                                    </div>
                                @endif
                                @if ($isLastMsgSelf && $msg->id === $lastMsgId)
                                    <div class="chat-msg-status" data-status="{{ $msg->is_read ? 'seen' : 'sent' }}">
                                        {{ $msg->is_read ? 'Đã xem' : 'Đã gửi' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach

                {{-- Hiển thị lời chào từ Mật Ngọt Bear khi cuộc trò chuyện trước đó đã kết thúc --}}
                @if ($shouldShowGreeting ?? ($activeCase === null || $activeCase->isClosed()))
                    @if ($lastMsg && $lastMsg->sent_at && $lastMsg->sent_at->format('d/m/Y') !== now()->format('d/m/Y'))
                        <div class="customer-chat-date-divider">
                            <span>Hôm nay, {{ now()->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    <div class="customer-chat-msg-row is-shop is-greeting-prompt" data-greeting-prompt="1">
                        <img
                            src="{{ asset('images/auth/bear-hero.png') }}"
                            alt="Mật Ngọt Bear"
                            class="customer-chat-msg-row__avatar"
                            onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                        >
                        <div class="customer-chat-msg-wrap">
                            <div class="customer-chat-bubble">
                                <div class="chat-msg-text">
                                    Xin chào bạn! 👋<br>
                                    Mật Ngọt Bear rất vui được hỗ trợ bạn. Bạn cần tư vấn về sản phẩm, đơn hàng hay vấn đề nào khác ạ?
                                </div>
                                <div class="chat-msg-time">
                                    <span>{{ now()->format('H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Ô NHẬP TIN NHẮN (FOOTER) --}}
        <div class="customer-chat-footer">
            {{-- GỢI Ý GỬI ĐƠN HÀNG KIỂU SHOPEE CHO KHÁCH HÀNG --}}
            @if (isset($suggestedOrder) && $suggestedOrder)
                @php
                    $suggDetail = $suggestedOrder->details->first();
                    $suggProduct = $suggDetail?->product;
                    $suggImg = $suggProduct?->primary_image_url ?? $suggProduct?->image_url ?? asset('images/auth/bear-hero.png');
                    $suggOtherCount = $suggestedOrder->details->count() - 1;
                @endphp
                <div class="staff-support-order-suggestion" data-order-suggestion>
                    <div class="staff-support-order-suggestion__header">
                        <div class="staff-support-order-suggestion__title">
                            <i class="fa-solid fa-box-open"></i>
                            <span>Bạn đang cần hỗ trợ về đơn hàng này?</span>
                        </div>
                        <button
                            type="button"
                            class="staff-support-order-suggestion__close"
                            data-dismiss-suggested-order
                            title="Bỏ qua gợi ý"
                        >
                            &times;
                        </button>
                    </div>
                    <div class="staff-support-order-suggestion__body">
                        <img
                            src="{{ $suggImg }}"
                            alt="{{ $suggestedOrder->order_code }}"
                            class="staff-support-order-suggestion__thumb"
                            onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                        >
                        <div class="staff-support-order-suggestion__info">
                            <div class="staff-support-order-suggestion__code-row">
                                <span class="staff-support-order-suggestion__code">#{{ $suggestedOrder->order_code }}</span>
                                <span class="staff-support-order-suggestion__status">{{ $suggestedOrder->order_status }}</span>
                            </div>
                            <div class="staff-support-order-suggestion__product">
                                {{ $suggDetail?->product_name ?? 'Đơn hàng Mật Ngọt Bear' }}
                                @if ($suggOtherCount > 0)
                                    <span style="color: #8c7667; font-weight: normal;">(+{{ $suggOtherCount }} sản phẩm khác)</span>
                                @endif
                            </div>
                            <div class="staff-support-order-suggestion__total">
                                Tổng tiền: <strong>{{ number_format($suggestedOrder->total_amount, 0, ',', '.') }} đ</strong>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="staff-support-order-suggestion__btn-send"
                            data-send-suggested-order
                            data-order-id="{{ $suggestedOrder->id }}"
                            data-order-code="{{ $suggestedOrder->order_code }}"
                            data-order-total="{{ number_format($suggestedOrder->total_amount, 0, ',', '.') }} đ"
                            data-order-status="{{ $suggestedOrder->order_status }}"
                            data-product-name="{{ $suggDetail?->product_name ?? 'Sản phẩm' }}{{ $suggOtherCount > 0 ? ' (+' . $suggOtherCount . ' sản phẩm khác)' : '' }}"
                            data-order-url="{{ route('customer.orders.show', $suggestedOrder) }}"
                        >
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Gửi đơn này</span>
                        </button>
                    </div>
                </div>
            @endif

            <form
                action="{{ route('customer.messages.send') }}"
                method="POST"
                class="customer-chat-form"
                data-chat-form
                enctype="multipart/form-data"
            >
                @csrf
                <div class="chat-image-preview-container" data-chat-image-preview style="display: none;"></div>

                <div class="customer-chat-form-row">
                    <button type="button" class="chat-btn-image customer-chat-btn-icon" data-chat-btn-image title="Đính kèm hình ảnh" aria-label="Đính kèm hình ảnh">
                        <i class="fa-regular fa-image"></i>
                    </button>
                    <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif" style="display: none;" data-chat-file-input>

                    <input
                        type="text"
                        name="content"
                        class="customer-chat-input"
                        placeholder="Nhập tin nhắn..."
                        maxlength="2000"
                        autocomplete="off"
                        data-chat-input
                    >

                    <button
                        type="submit"
                        class="customer-chat-btn-send"
                        data-chat-submit
                        title="Gửi tin nhắn"
                        aria-label="Gửi tin nhắn"
                    >
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Gửi</span>
                    </button>
                </div>
            </form>
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
        <div class="customer-chat-card-faq" data-faq-card>
            <h3 class="customer-chat-card-faq__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="color: #a05a2c;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
                <span>Câu hỏi thường gặp</span>
            </h3>

            @php
                $allFaqs = $faqList ?? app(\App\Services\ChatKT\ChatService::class)->getFaqData();
                $initialFaqs = array_slice($allFaqs, 0, 5);
                $extraFaqs = array_slice($allFaqs, 5);
            @endphp

            <div class="customer-chat-faq-list-wrap" data-faq-list-wrap>
                <ul class="customer-chat-faq-list">
                    @foreach ($initialFaqs as $faq)
                        <li class="customer-chat-faq-item" data-faq-question="{{ $faq['question'] }}" role="button" tabindex="0">
                            <span>{{ $faq['question'] }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </li>
                    @endforeach
                </ul>

                @if (count($extraFaqs) > 0)
                    <div class="customer-chat-faq-extra" data-faq-extra style="display: none;">
                        <ul class="customer-chat-faq-list is-extra-list">
                            @foreach ($extraFaqs as $faq)
                                <li class="customer-chat-faq-item" data-faq-question="{{ $faq['question'] }}" role="button" tabindex="0">
                                    <span>{{ $faq['question'] }}</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                        <path d="m9 18 6-6-6-6"/>
                                    </svg>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            @if (count($extraFaqs) > 0)
                <button type="button" class="customer-chat-faq-btn-all" data-faq-toggle-btn aria-expanded="false">
                    <span data-faq-toggle-text>Xem tất cả câu hỏi</span>
                    <svg data-faq-toggle-icon viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
            @endif
        </div>
    </aside>
</div>
@endsection

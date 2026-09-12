@extends($layout, ['currentPage' => 'support', 'contentClass' => 'staff-support-page-content'])

@section('page-title', 'Hỗ Trợ Khách Hàng')

@section('content')
<div
    class="staff-support-wrapper"
    data-staff-support
    data-current-case-id="{{ $selectedCase?->id ?? 0 }}"
    @if ($selectedCase)
        data-poll-url="{{ route($routePrefix . '.poll', $selectedCase) }}"
    @endif
>
    {{-- BỐ CỤC 3 CỘT THEO MOCKUP (2 CỘT KHI CHƯA CHỌN CASE ĐỂ TRẢI NGHIỆM THOÁNG VÀ ĐẸP MẮT) --}}
    <div class="staff-support-grid {{ $selectedCase ? 'has-selected-case' : 'is-empty-selection' }}">
        {{-- ==========================================
             CỘT 1: DANH SÁCH CASE (BÊN TRÁI)
             ========================================== --}}
        <div class="staff-support-col-cases">
            {{-- 3 TABS TRẠNG THÁI: TẤT CẢ - CHƯA XỬ LÝ - ĐANG XỬ LÝ --}}
            <div class="staff-support-tabs">
                <a
                    href="{{ route($routePrefix . '.index', ['tab' => 'all', 'q' => $search]) }}"
                    class="staff-support-tab-btn {{ $statusTab === 'all' ? 'is-active' : '' }}"
                >
                    <span>Tất cả</span>
                    <span class="staff-support-tab-count">{{ $counts['all'] ?? 0 }}</span>
                </a>
                <a
                    href="{{ route($routePrefix . '.index', ['tab' => 'waiting', 'q' => $search]) }}"
                    class="staff-support-tab-btn {{ $statusTab === 'waiting' ? 'is-active' : '' }}"
                >
                    <span>Chưa xử lý</span>
                    <span class="staff-support-tab-count">{{ $counts['waiting'] ?? 0 }}</span>
                </a>
                <a
                    href="{{ route($routePrefix . '.index', ['tab' => 'in_progress', 'q' => $search]) }}"
                    class="staff-support-tab-btn {{ $statusTab === 'in_progress' ? 'is-active' : '' }}"
                >
                    <span>Đang xử lý</span>
                    <span class="staff-support-tab-count">{{ $counts['in_progress'] ?? 0 }}</span>
                </a>
            </div>

            {{-- Ô TÌM KIẾM --}}
            {{-- Ô TÌM KIẾM --}}
            <form action="{{ route($routePrefix . '.index') }}" method="GET" class="staff-support-search-wrap" data-staff-search-form>
                <input type="hidden" name="tab" value="{{ $statusTab }}">
                <div class="staff-support-search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="Tìm kiếm khách hàng, nội dung..."
                        autocomplete="off"
                        data-staff-search-input
                    >
                    <button type="button" class="staff-search-clear-btn" data-search-clear style="{{ empty($search) ? 'display: none;' : '' }}" title="Xóa tìm kiếm">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <button type="submit" class="staff-support-filter-btn" title="Tìm kiếm / Lọc">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                </button>
            </form>

            {{-- DANH SÁCH CASE --}}
            <div class="staff-support-case-list">
                @forelse ($cases as $c)
                    @php
                        $isLockedOther = $c->isInProgress() && $c->assignedStaff && (int) $c->assigned_staff_id !== (int) $user->id && $user->role !== \App\Models\User::ROLE_ADMIN;
                        $isActive = ! $isLockedOther && $selectedCase && (int) $selectedCase->id === (int) $c->id;
                        $unreadCount = app(\App\Services\ChatKT\ChatService::class)->countUnreadMessagesForStaff($c);
                        $lastMsg = $c->latestMessage ?? $c->messages?->first() ?? $c->conversation?->lastMessage;
                        $displayMsg = app(\App\Services\ChatKT\ChatService::class)->getCasePreviewMessage($c, $lastMsg);
                        $lastMsgTime = $lastMsg?->sent_at ?? $lastMsg?->created_at ?? $c->created_at;
                        $timeAgo = $lastMsgTime ? $lastMsgTime->diffForHumans(null, true) : '';
                        $isUnread = $unreadCount > 0;
                        $otherStaffName = $c->assignedStaff?->full_name ?? 'nhân viên khác';

                        $caseTooltip = '';
                        if ($isLockedOther) {
                            $caseTooltip = '🔒 Đang do ' . $otherStaffName . ' xử lý. Bạn không thể truy cập.';
                        } elseif ($c->isInProgress() && $c->assignedStaff) {
                            if ((int) $c->assigned_staff_id === (int) $user->id) {
                                $caseTooltip = 'Bạn đang xử lý';
                            } else {
                                $caseTooltip = '🔒 Đang do ' . $otherStaffName . ' xử lý';
                            }
                        }
                    @endphp
                    @if ($isLockedOther)
                        <div
                            class="staff-support-case-item is-locked-other {{ $isUnread ? 'is-unread' : '' }}"
                            data-locked="true"
                            data-locked-by="{{ $otherStaffName }}"
                            @if ($caseTooltip)
                                data-tooltip="{{ $caseTooltip }}"
                                title="{{ $caseTooltip }}"
                            @endif
                        >
                    @else
                        <a
                            href="{{ route($routePrefix . '.index', ['tab' => $statusTab, 'case_id' => $c->id, 'q' => $search]) }}"
                            class="staff-support-case-item {{ $isActive ? 'is-active' : '' }} {{ $isUnread ? 'is-unread' : '' }}"
                            @if ($caseTooltip)
                                data-tooltip="{{ $caseTooltip }}"
                                title="{{ $caseTooltip }}"
                            @endif
                        >
                    @endif
                        <div class="staff-support-case-avatar">
                            @if ($c->customer?->avatar_url)
                                <img
                                    src="{{ $c->customer->avatar_url }}"
                                    alt="{{ $c->customer?->full_name ?? 'Khách hàng' }}"
                                    onerror="this.remove()"
                                >
                            @endif
                            <span>{{ mb_strtoupper(mb_substr(trim($c->customer?->full_name ?? $c->customer?->name ?? 'K'), 0, 1, 'UTF-8')) }}</span>
                        </div>
                        <div class="staff-support-case-info">
                            <div class="staff-support-case-top">
                                <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                                    <span class="staff-support-case-name">{{ $c->customer?->full_name ?? 'Khách hàng' }}</span>
                                    @if ($isUnread)
                                        <span class="staff-support-unread-dot" title="Tin nhắn mới chưa đọc"></span>
                                    @endif
                                </div>
                                <span class="staff-support-case-time" title="{{ $lastMsgTime ? $lastMsgTime->format('H:i d/m/Y') : '' }}">{{ $timeAgo }}</span>
                            </div>

                            <div class="staff-support-case-middle">
                                <p class="staff-support-case-preview">
                                    {{ $displayMsg?->content ?: ($displayMsg?->image_url ? '📷 [Hình ảnh]' : 'Chưa có tin nhắn') }}
                                </p>
                                @if ($unreadCount > 0)
                                    <span class="staff-support-case-unread">{{ $unreadCount }}</span>
                                @endif
                            </div>

                            @if ($c->order)
                                <div class="staff-support-case-bottom">
                                    <span class="staff-support-case-order" data-tooltip="Đơn hàng #{{ $c->order->order_code }}">#{{ $c->order->order_code }}</span>
                                </div>
                            @endif
                        </div>
                    @if ($isLockedOther)
                        </div>
                    @else
                        </a>
                    @endif
                @empty
                    <div style="padding: 40px 20px; text-align: center; color: #a89488; font-size: 13px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="margin: 0 auto 10px; color: #d5c4b8;">
                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                        Không có cuộc hỗ trợ nào trong mục này.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ==========================================
             CỘT 2: KHUNG TRÒ CHUYỆN CHÍNH (Ở GIỮA)
             ========================================== --}}
        <div class="staff-support-col-chat">
            @if ($selectedCase)
                @php
                    $isAdmin = $user->role === \App\Models\User::ROLE_ADMIN;
                    $isWaiting = $selectedCase->isWaiting();
                    $isInProgress = $selectedCase->isInProgress();
                    $isClosed = $selectedCase->isClosed();
                    $isHandler = $isInProgress && (int) $selectedCase->assigned_staff_id === (int) $user->id;
                    $handlerStaff = $selectedCase->assignedStaff;
                    $handlerName = $handlerStaff?->full_name ?? $handlerStaff?->name ?? ($isClosed ? 'Hệ thống tự động' : 'Chưa có');
                @endphp

                {{-- TOP BAR KHÁCH HÀNG & NÚT THAO TÁC (KIỂU MESSENGER FB) --}}
                <div class="staff-support-chat-topbar">
                    <div class="staff-support-chat-customer-meta">
                        <div class="staff-support-chat-customer-avatar">
                            @if ($selectedCase->customer?->avatar_url)
                                <img
                                    src="{{ $selectedCase->customer->avatar_url }}"
                                    alt="{{ $selectedCase->customer?->full_name }}"
                                    onerror="this.remove()"
                                >
                            @endif
                            <span>{{ mb_strtoupper(mb_substr(trim($selectedCase->customer?->full_name ?? $selectedCase->customer?->name ?? 'K'), 0, 1, 'UTF-8')) }}</span>
                        </div>
                        <div class="staff-support-chat-customer-info">
                            <div class="staff-support-chat-customer-name-row">
                                <h2 class="staff-support-chat-customer-name" data-tooltip="{{ $selectedCase->customer?->full_name ?? 'Khách hàng' }}">{{ $selectedCase->customer?->full_name ?? 'Khách hàng' }}</h2>
                            </div>

                            {{-- DÒNG TRẠNG THÁI CHỮ NHỎ DƯỚI TÊN KIỂU MESSENGER FB --}}
                            <div class="staff-support-chat-status-meta staff-support-chat-messenger-subtext" data-tooltip="Trạng thái: {{ $selectedCase->status_label }} • Người xử lý: {{ $handlerName }}">
                                <span class="staff-messenger-dot is-{{ str_replace('_', '-', strtolower($selectedCase->status)) }}"></span>
                                <span class="staff-messenger-status is-{{ str_replace('_', '-', strtolower($selectedCase->status)) }}">
                                    <span class="sr-only">Trạng thái: </span>{{ $selectedCase->status_label }}
                                </span>
                                <span class="staff-messenger-divider">•</span>
                                <span class="staff-messenger-handler" data-tooltip="Người xử lý: {{ $handlerName }}">
                                    Người xử lý: <strong>{{ $handlerName }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- NÚT HÀNH ĐỘNG (LÊN TRÊN BÊN PHẢI) --}}
                    <div class="staff-support-chat-actions">
                        @if ($isClosed)
                            {{-- Ca đã kết thúc: không cần nút trên topbar, chỉ cần gõ gửi tin nhắn bên dưới là tự động mở lại về Đang xử lý --}}
                        @elseif ($isAdmin)
                            {{-- THAO TÁC CỦA ADMIN --}}
                            @if ($isWaiting)
                                {{-- Admin chỉ cần nút Chuyển cho nhân viên (nếu Admin muốn xử lý thì gửi tin nhắn trực tiếp hệ thống sẽ tự động nhận) --}}
                                <button
                                    type="button"
                                    class="staff-btn-assign"
                                    data-btn-open-assign
                                    title="Chuyển cuộc hỗ trợ cho một nhân viên"
                                >
                                    <i class="fa-solid fa-user-gear"></i>
                                    <span>Chuyển cho nhân viên</span>
                                </button>
                            @elseif ($isInProgress)
                                @if (! $isHandler)
                                    {{-- Staff đang xử lý: Nút Tiếp quản chính + Menu 3 chấm thao tác quản trị (đã bỏ Kết thúc hỗ trợ) --}}
                                    <button
                                        type="button"
                                        class="staff-btn-takeover"
                                        data-btn-takeover
                                        data-takeover-url="{{ route('admin.support.takeover', $selectedCase) }}"
                                        title="Tiếp quản cuộc hỗ trợ để trực tiếp chat với khách hàng"
                                    >
                                        <i class="fa-solid fa-user-shield"></i>
                                        <span>Tiếp quản</span>
                                    </button>

                                    <div class="staff-dropdown-wrapper" data-staff-dropdown>
                                        <button
                                            type="button"
                                            class="staff-btn-more"
                                            data-staff-dropdown-trigger
                                            title="Thao tác quản trị khác"
                                            aria-label="Thao tác khác"
                                        >
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <div class="staff-dropdown-menu" data-staff-dropdown-menu>
                                            <button
                                                type="button"
                                                class="staff-dropdown-item"
                                                data-btn-open-assign
                                                title="Chuyển cuộc hỗ trợ sang nhân viên khác"
                                            >
                                                <i class="fa-solid fa-user-gear"></i>
                                                <span>Chuyển nhân viên</span>
                                            </button>
                                            <button
                                                type="button"
                                                class="staff-dropdown-item"
                                                data-btn-revoke
                                                data-revoke-url="{{ route('admin.support.revoke', $selectedCase) }}"
                                                title="Thu hồi cuộc hỗ trợ về hàng chờ Chưa xử lý"
                                            >
                                                <i class="fa-solid fa-rotate-left"></i>
                                                <span>Thu hồi xử lý</span>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- Admin đang xử lý: Nút Chuyển cho nhân viên + Menu 3 chấm (Bàn giao, Kết thúc hỗ trợ) --}}
                                    <button
                                        type="button"
                                        class="staff-btn-assign"
                                        data-btn-open-assign
                                        title="Chuyển cuộc hỗ trợ cho nhân viên khác"
                                    >
                                        <i class="fa-solid fa-user-gear"></i>
                                        <span>Chuyển cho nhân viên</span>
                                    </button>

                                    <div class="staff-dropdown-wrapper" data-staff-dropdown>
                                        <button
                                            type="button"
                                            class="staff-btn-more"
                                            data-staff-dropdown-trigger
                                            title="Thao tác khác"
                                            aria-label="Thao tác khác"
                                        >
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <div class="staff-dropdown-menu" data-staff-dropdown-menu>
                                            <button
                                                type="button"
                                                class="staff-dropdown-item"
                                                data-btn-handover
                                                data-handover-url="{{ route('admin.support.handover', $selectedCase) }}"
                                                title="Bàn giao ca về hàng chờ Chưa xử lý"
                                            >
                                                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                                <span>Bàn giao</span>
                                            </button>
                                            <div class="staff-dropdown-divider"></div>
                                            <button
                                                type="button"
                                                class="staff-dropdown-item is-danger"
                                                data-btn-close
                                                data-close-url="{{ route('admin.support.close', $selectedCase) }}"
                                                title="Kết thúc phiên hỗ trợ khách hàng"
                                            >
                                                <i class="fa-regular fa-circle-check"></i>
                                                <span>Kết thúc hỗ trợ</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @else
                            {{-- THAO TÁC CỦA STAFF --}}
                            @if ($isWaiting)
                                <button
                                    type="button"
                                    class="staff-btn-accept"
                                    data-btn-accept
                                    data-accept-url="{{ route('staff.support.accept', $selectedCase) }}"
                                >
                                    <i class="fa-solid fa-user-check"></i>
                                    <span>Nhận xử lý</span>
                                </button>
                            @elseif ($isHandler)
                                {{-- Nhân viên đang xử lý: Menu 3 chấm thu gọn cho Bàn giao & Kết thúc hỗ trợ --}}
                                <div class="staff-dropdown-wrapper" data-staff-dropdown>
                                    <button
                                        type="button"
                                        class="staff-btn-more"
                                        data-staff-dropdown-trigger
                                        title="Thao tác phiên hỗ trợ"
                                        aria-label="Thao tác khác"
                                    >
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div class="staff-dropdown-menu" data-staff-dropdown-menu>
                                        <button
                                            type="button"
                                            class="staff-dropdown-item"
                                            data-btn-handover
                                            data-handover-url="{{ route('staff.support.handover', $selectedCase) }}"
                                            title="Bàn giao ca về hàng chờ cho nhân viên khác tiếp nhận"
                                        >
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                            <span>Bàn giao</span>
                                        </button>
                                        <div class="staff-dropdown-divider"></div>
                                        <button
                                            type="button"
                                            class="staff-dropdown-item is-danger"
                                            data-btn-close
                                            data-close-url="{{ route('staff.support.close', $selectedCase) }}"
                                            title="Kết thúc phiên hỗ trợ khách hàng"
                                        >
                                            <i class="fa-regular fa-circle-check"></i>
                                            <span>Kết thúc hỗ trợ</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- NỘI DUNG CUỘC TRÒ CHUYỆN --}}
                <div class="staff-support-chat-stream" data-staff-chat-stream>
                    @php
                        $allMsgs = $selectedCase->conversation->messages()
                            ->with('sender')
                            ->orderBy('id', 'asc')
                            ->get();
                        $lastMsg = $allMsgs->last();
                        $lastMsgId = $lastMsg?->id;
                        $isLastMsgSelf = $lastMsg && ((int) $lastMsg->sender_id !== (int) $selectedCase->customer_id);
                        $groupedMsgs = $allMsgs->groupBy(fn ($m) => $m->sent_at ? $m->sent_at->format('d/m/Y') : now()->format('d/m/Y'));
                    @endphp

                    @foreach ($groupedMsgs as $dateStr => $msgs)
                        <div class="customer-chat-date-divider">
                            <span>
                                @if ($dateStr === now()->format('d/m/Y'))
                                    Hôm nay, {{ $dateStr }}
                                @else
                                    {{ $dateStr }}
                                @endif
                            </span>
                        </div>

                        @php
                            $msgs = $msgs->values();
                            $totalInDate = $msgs->count();
                        @endphp

                        @foreach ($msgs as $idx => $msg)
                            @php
                                $isCustomer = (int) $msg->sender_id === (int) $selectedCase->customer_id;
                                $prevMsg = $idx > 0 ? $msgs[$idx - 1] : null;
                                $nextMsg = $idx < $totalInDate - 1 ? $msgs[$idx + 1] : null;

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
                                class="staff-chat-msg-row {{ $isCustomer ? 'is-customer' : 'is-staff' }} {{ $groupPos }} {{ ! empty($msg->image_urls) ? 'has-gallery' : '' }}"
                                data-msg-id="{{ $msg->id }}"
                                data-sender-id="{{ $msg->sender_id }}"
                                data-is-customer="{{ $isCustomer ? '1' : '0' }}"
                                data-timestamp="{{ $msg->sent_at ? $msg->sent_at->timestamp : '' }}"
                            >
                                @if ($isCustomer)
                                    @if ($showAvatar)
                                        <div class="staff-chat-msg-avatar">
                                            @if ($selectedCase->customer?->avatar_url)
                                                <img
                                                    src="{{ $selectedCase->customer->avatar_url }}"
                                                    alt="{{ $selectedCase->customer?->full_name ?? 'Khách hàng' }}"
                                                    onerror="this.remove()"
                                                >
                                            @endif
                                            <span>{{ mb_strtoupper(mb_substr(trim($selectedCase->customer?->full_name ?? $selectedCase->customer?->name ?? 'K'), 0, 1, 'UTF-8')) }}</span>
                                        </div>
                                    @else
                                        <div class="staff-chat-msg-avatar is-spacer" aria-hidden="true"></div>
                                    @endif
                                @endif
                                <div class="staff-chat-msg-wrap">
                                    @php
                                        $msgImages = $msg->image_urls;
                                    @endphp
                                    @if (! empty($msgImages))
                                        <div class="chat-msg-gallery {{ count($msgImages) === 1 ? 'is-single' : (count($msgImages) === 2 ? 'is-double' : 'is-grid') }}">
                                            @foreach ($msgImages as $imgUrl)
                                                <div class="chat-msg-image-wrap">
                                                    <img src="{{ $imgUrl }}" alt="Hình ảnh đính kèm" class="chat-msg-image" loading="lazy" onclick="window.open(this.src, '_blank')">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($msg->content)
                                        <div class="staff-chat-bubble" title="{{ $msg->sent_at ? $msg->sent_at->format('H:i, d/m/Y') : '' }}">
                                            @if (str_contains($msg->content, '📦 [ĐƠN HÀNG #'))
                                                @php
                                                    preg_match('/#([A-Z0-9\-]+)/', $msg->content, $orderCodeMatches);
                                                    $parsedOrderCode = $orderCodeMatches[1] ?? null;
                                                    $bubbleOrder = $parsedOrderCode ? \App\Models\Order::with('details.product')->where('order_code', $parsedOrderCode)->first() : null;
                                                @endphp
                                                @if ($bubbleOrder)
                                                    @php
                                                        $bubbleDetail = $bubbleOrder->details->first();
                                                        $bubbleProduct = $bubbleDetail?->product;
                                                        $bubbleImg = $bubbleProduct?->primary_image_url ?? $bubbleProduct?->image_url ?? asset('images/auth/bear-hero.png');
                                                        $bubbleOthers = $bubbleOrder->details->count() - 1;
                                                        $bubbleUrl = str_starts_with($routePrefix ?? '', 'admin.') ? route('admin.orders.show', $bubbleOrder) : route('staff.orders.show', $bubbleOrder);
                                                    @endphp
                                                    <div class="chat-order-card">
                                                        <div class="chat-order-card__header">
                                                            <span class="chat-order-card__tag">
                                                                <i class="fa-solid fa-box"></i> #{{ $bubbleOrder->order_code }}
                                                            </span>
                                                            <span class="chat-order-card__status">{{ $bubbleOrder->order_status }}</span>
                                                        </div>
                                                        <div class="chat-order-card__body">
                                                            <img src="{{ $bubbleImg }}" alt="{{ $bubbleOrder->order_code }}" class="chat-order-card__img" onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'">
                                                            <div class="chat-order-card__info">
                                                                <div class="chat-order-card__pname">{{ $bubbleDetail?->product_name ?? 'Đơn hàng' }}</div>
                                                                @if ($bubbleOthers > 0)
                                                                    <div class="chat-order-card__other">+{{ $bubbleOthers }} sản phẩm khác</div>
                                                                @endif
                                                                <div class="chat-order-card__total">Tổng tiền: <strong>{{ number_format($bubbleOrder->total_amount, 0, ',', '.') }} đ</strong></div>
                                                            </div>
                                                        </div>
                                                        <div class="chat-order-card__footer">
                                                            <a href="{{ $bubbleUrl }}" class="chat-order-card__link">
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
                                            @if ($showTime && $msg->sent_at)
                                                <div class="staff-chat-time">
                                                    <span>{{ $msg->sent_at->format('H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif (empty($msgImages))
                                        <div class="staff-chat-bubble" title="{{ $msg->sent_at ? $msg->sent_at->format('H:i, d/m/Y') : '' }}">
                                            @if ($showTime && $msg->sent_at)
                                                <div class="staff-chat-time">
                                                    <span>{{ $msg->sent_at->format('H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif ($showTime && $msg->sent_at)
                                        <div class="staff-chat-time is-outside">
                                            <span>{{ $msg->sent_at->format('H:i') }}</span>
                                        </div>
                                    @endif
                                    @if ($isLastMsgSelf && $msg->id === $lastMsgId)
                                        <div class="chat-msg-status staff-chat-status" data-status="{{ $msg->is_read ? 'seen' : 'sent' }}">
                                            {{ $msg->is_read ? 'Đã xem' : 'Đã gửi' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                {{-- Ô NHẬP PHẢN HỒI (FOOTER) --}}
                <div class="staff-support-chat-footer">
                    {{-- GỢI Ý GỬI ĐƠN HÀNG KIỂU SHOPEE --}}
                    @if (isset($suggestedOrder) && $suggestedOrder)
                        @php
                            $suggDetail = $suggestedOrder->details->first();
                            $suggProduct = $suggDetail?->product;
                            $suggImg = $suggProduct?->primary_image_url ?? $suggProduct?->image_url ?? asset('images/auth/bear-hero.png');
                            $suggOtherCount = $suggestedOrder->details->count() - 1;
                            $suggOrderRoute = str_starts_with($routePrefix ?? '', 'admin.') ? 'admin.orders.show' : 'staff.orders.show';
                        @endphp
                        <div class="staff-support-order-suggestion" data-order-suggestion>
                            <div class="staff-support-order-suggestion__header">
                                <div class="staff-support-order-suggestion__title">
                                    <i class="fa-solid fa-box-open"></i>
                                    <span>Gợi ý: Gửi thông tin đơn hàng này cho khách</span>
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
                                    data-order-url="{{ route($suggOrderRoute, $suggestedOrder) }}"
                                >
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Gửi đơn này</span>
                                </button>
                            </div>
                        </div>
                    @endif
                    @if ($isClosed)
                        <div class="staff-support-chat-notice is-closed-notice">
                            <i class="fa-solid fa-clock-rotate-left" style="color: #64748b; font-size: 14px; flex-shrink: 0;"></i>
                            <span>Cuộc hỗ trợ trước đã kết thúc. Bạn có thể gửi tin nhắn bên dưới để mở lại và nhắn trước cho khách hàng.</span>
                        </div>

                        <form
                            action="{{ route($routePrefix . '.messages.send', $selectedCase) }}"
                            method="POST"
                            class="staff-support-chat-form"
                            data-staff-chat-form
                            enctype="multipart/form-data"
                        >
                            @csrf
                            <div class="chat-image-preview-container" data-chat-image-preview style="display: none;"></div>

                            <div class="staff-support-chat-form-row">
                                <button type="button" class="chat-btn-image customer-chat-btn-icon" data-chat-btn-image title="Đính kèm hình ảnh" aria-label="Đính kèm hình ảnh">
                                    <i class="fa-regular fa-image"></i>
                                </button>
                                <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif" style="display: none;" data-chat-file-input>

                                <input
                                    type="text"
                                    name="content"
                                    class="staff-support-chat-input"
                                    placeholder="Nhập tin nhắn gửi cho khách hàng..."
                                    maxlength="2000"
                                    autocomplete="off"
                                    data-staff-chat-input
                                >

                                <button
                                    type="submit"
                                    class="staff-support-btn-send"
                                    data-staff-chat-submit
                                >
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Gửi</span>
                                </button>
                            </div>
                        </form>
                    @elseif ($isWaiting)
                        @if ($isAdmin)
                            <div class="staff-support-chat-notice is-waiting-notice">
                                <i class="fa-solid fa-circle-info" style="color: #b45309; font-size: 14px; flex-shrink: 0;"></i>
                                <span>Chưa có người phụ trách. Bạn có thể gửi tin nhắn trực tiếp để tự động tiếp nhận cuộc hỗ trợ.</span>
                            </div>

                            <form
                                action="{{ route($routePrefix . '.messages.send', $selectedCase) }}"
                                method="POST"
                                class="staff-support-chat-form"
                                data-staff-chat-form
                                enctype="multipart/form-data"
                            >
                                @csrf
                                <div class="chat-image-preview-container" data-chat-image-preview style="display: none;"></div>

                                <div class="staff-support-chat-form-row">
                                    <button type="button" class="chat-btn-image customer-chat-btn-icon" data-chat-btn-image title="Đính kèm hình ảnh" aria-label="Đính kèm hình ảnh">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                    <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif" style="display: none;" data-chat-file-input>

                                    <input
                                        type="text"
                                        name="content"
                                        class="staff-support-chat-input"
                                        placeholder="Nhập phản hồi cho khách hàng..."
                                        maxlength="2000"
                                        autocomplete="off"
                                        data-staff-chat-input
                                    >

                                    <button
                                        type="submit"
                                        class="staff-support-btn-send"
                                        data-staff-chat-submit
                                    >
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <span>Gửi</span>
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="staff-support-chat-notice is-waiting-notice">
                                <i class="fa-solid fa-hand-point-up" style="color: #b45309; font-size: 14px; flex-shrink: 0;"></i>
                                <span>Vui lòng nhấn nút <strong>"Nhận xử lý"</strong> ở trên để bắt đầu hỗ trợ khách hàng.</span>
                            </div>
                        @endif
                    @elseif ($isInProgress)
                        @if ($isHandler || $isAdmin)
                            @if ($isAdmin && ! $isHandler)
                                <div class="staff-support-chat-notice is-assigned-other">
                                    <span>🔒 {{ $handlerName }} đang xử lý. Bạn có thể gửi tin nhắn để tiếp quản và trực tiếp hỗ trợ.</span>
                                </div>
                            @endif

                            <form
                                action="{{ route($routePrefix . '.messages.send', $selectedCase) }}"
                                method="POST"
                                class="staff-support-chat-form"
                                data-staff-chat-form
                                enctype="multipart/form-data"
                            >
                                @csrf
                                <div class="chat-image-preview-container" data-chat-image-preview style="display: none;"></div>

                                <div class="staff-support-chat-form-row">
                                    <button type="button" class="chat-btn-image customer-chat-btn-icon" data-chat-btn-image title="Đính kèm hình ảnh" aria-label="Đính kèm hình ảnh">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                    <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif" style="display: none;" data-chat-file-input>

                                    <input
                                        type="text"
                                        name="content"
                                        class="staff-support-chat-input"
                                        placeholder="Nhập phản hồi cho khách hàng..."
                                        maxlength="2000"
                                        autocomplete="off"
                                        data-staff-chat-input
                                    >

                                    <button
                                        type="submit"
                                        class="staff-support-btn-send"
                                        data-staff-chat-submit
                                    >
                                        <i class="fa-solid fa-paper-plane"></i>
                                        <span>Gửi</span>
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="staff-support-chat-notice is-assigned-other">
                                <i class="fa-solid fa-lock" style="font-size: 14px; color: #2563eb;"></i>
                                <span>🔒 {{ $handlerName }} đang xử lý</span>
                            </div>
                        @endif
                    @endif
                </div>
            @else
                <div class="staff-support-empty-state">
                    <div class="staff-support-empty-card">
                        {{-- MASCOT VISUAL WITH HALO & FLOATING BADGES --}}
                        <div class="staff-empty-mascot-wrapper">
                            <div class="staff-empty-mascot-halo"></div>
                            <div class="staff-empty-mascot-circle">
                                <img
                                    src="{{ asset('images/auth/bear-hero.png') }}"
                                    alt="Mật Ngọt Bear Support"
                                    class="staff-empty-mascot-img"
                                    onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'"
                                >
                            </div>
                            <div class="staff-empty-floating-badge is-top" title="Trung tâm hỗ trợ Mật Ngọt Bear">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <div class="staff-empty-floating-badge is-bottom" title="Sẵn sàng phản hồi">
                                <i class="fa-solid fa-sparkles"></i>
                            </div>
                        </div>

                        {{-- GREETING & HEADLINE --}}
                        <div class="staff-empty-header-block">
                            <span class="staff-empty-badge">
                                <i class="fa-solid fa-shield-heart"></i>
                                <span>Cổng Hỗ Trợ Khách Hàng</span>
                            </span>
                            <h2 class="staff-empty-title">Xin chào, {{ $user->full_name ?? $user->name ?? 'bạn' }}! 👋</h2>
                            <p class="staff-empty-desc">
                                Vui lòng chọn một cuộc hỗ trợ từ danh sách bên trái để xem nội dung.
                            </p>
                        </div>

                        {{-- QUICK METRIC PILLS / STAT CARDS --}}
                        <div class="staff-empty-stats-row">
                            <a href="{{ route($routePrefix . '.index', ['tab' => 'waiting']) }}" class="staff-empty-stat-item is-waiting" title="Xem danh sách ca chưa xử lý">
                                <div class="staff-empty-stat-icon-wrap">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div class="staff-empty-stat-content">
                                    <span class="staff-empty-stat-value">{{ $counts['waiting'] ?? 0 }}</span>
                                    <span class="staff-empty-stat-name">Chưa xử lý</span>
                                </div>
                                <i class="fa-solid fa-chevron-right staff-empty-stat-arrow"></i>
                            </a>

                            <a href="{{ route($routePrefix . '.index', ['tab' => 'in_progress']) }}" class="staff-empty-stat-item is-in-progress" title="Xem danh sách ca đang xử lý">
                                <div class="staff-empty-stat-icon-wrap">
                                    <i class="fa-solid fa-comments"></i>
                                </div>
                                <div class="staff-empty-stat-content">
                                    <span class="staff-empty-stat-value">{{ $counts['in_progress'] ?? 0 }}</span>
                                    <span class="staff-empty-stat-name">Đang xử lý</span>
                                </div>
                                <i class="fa-solid fa-chevron-right staff-empty-stat-arrow"></i>
                            </a>

                            <a href="{{ route($routePrefix . '.index', ['tab' => 'closed']) }}" class="staff-empty-stat-item is-closed" title="Xem danh sách ca đã kết thúc">
                                <div class="staff-empty-stat-icon-wrap">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div class="staff-empty-stat-content">
                                    <span class="staff-empty-stat-value">{{ $counts['closed'] ?? 0 }}</span>
                                    <span class="staff-empty-stat-name">Đã hoàn thành</span>
                                </div>
                                <i class="fa-solid fa-chevron-right staff-empty-stat-arrow"></i>
                            </a>
                        </div>

                        {{-- CTA ACTION BUTTON NẾU CÓ CA TRONG DANH SÁCH --}}
                        @php
                            $firstWaitingCase = $cases->first(fn ($c) => $c->isWaiting());
                            $firstAvailableCase = $firstWaitingCase ?? $cases->first();
                        @endphp
                        @if ($firstAvailableCase)
                            <div class="staff-empty-action-wrap">
                                <a
                                    href="{{ route($routePrefix . '.index', ['case_id' => $firstAvailableCase->id, 'tab' => $statusTab]) }}"
                                    class="staff-empty-btn-cta"
                                >
                                    <i class="fa-solid fa-bolt-lightning"></i>
                                    <span>{{ $firstWaitingCase ? 'Tiếp nhận ca chờ gần nhất (#' . $firstWaitingCase->case_code . ')' : 'Mở cuộc trò chuyện gần nhất' }}</span>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                        @endif

                        {{-- WORKFLOW GUIDE / TIPS SECTION --}}
                        <div class="staff-empty-guide-card">
                            <div class="staff-empty-guide-header">
                                <i class="fa-solid fa-lightbulb"></i>
                                <span>Hướng dẫn thao tác nhanh cho nhân viên</span>
                            </div>
                            <div class="staff-empty-guide-grid">
                                <div class="staff-empty-guide-item">
                                    <span class="staff-empty-guide-bullet">1</span>
                                    <div class="staff-empty-guide-text">
                                        <strong>Tiếp nhận ca:</strong> Chọn ca ở tab <em>Chưa xử lý</em> rồi bấm <em>Nhận xử lý</em> để mở ô chat.
                                    </div>
                                </div>
                                <div class="staff-empty-guide-item">
                                    <span class="staff-empty-guide-bullet">2</span>
                                    <div class="staff-empty-guide-text">
                                        <strong>Hồ sơ & Đơn hàng:</strong> Bảng bên phải hiển thị toàn bộ lịch sử đơn hàng và thông tin khách liên quan.
                                    </div>
                                </div>
                                <div class="staff-empty-guide-item">
                                    <span class="staff-empty-guide-bullet">3</span>
                                    <div class="staff-empty-guide-text">
                                        <strong>Gửi kèm hình ảnh:</strong> Hỗ trợ tải lên cùng lúc tối đa 10 hình ảnh để tư vấn trực quan cho khách.
                                    </div>
                                </div>
                                <div class="staff-empty-guide-item">
                                    <span class="staff-empty-guide-bullet">4</span>
                                    <div class="staff-empty-guide-text">
                                        <strong>Phím tắt nhanh:</strong> Nhấn <kbd>Enter</kbd> để gửi tin nhắn, <kbd>Shift + Enter</kbd> để xuống dòng.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ==========================================
             CỘT 3: PANEL THÔNG TIN KHÁCH HÀNG & ĐƠN HÀNG (BÊN PHẢI)
             ========================================== --}}
        <div class="staff-support-col-info">
            @if ($selectedCase)
                @php
                    $cust = $selectedCase->customer;
                    $customerOrders = $customerOrders ?? ($cust ? \App\Models\Order::where('customer_id', $cust->id)->latest()->get() : collect());
                    $ordersCount = $customerOrders->count();
                    $isLoyal = $ordersCount >= 3;
                @endphp

                {{-- CARD 1: THÔNG TIN KHÁCH HÀNG --}}
                <div class="staff-support-info-card">
                    <h3 class="staff-support-info-card__title">
                        <i class="fa-regular fa-circle-user"></i>
                        <span>Thông tin khách hàng</span>
                    </h3>

                    <div class="staff-customer-profile-block">
                        <div class="staff-customer-profile-avatar">
                            @if ($cust?->avatar_url)
                                <img
                                    src="{{ $cust->avatar_url }}"
                                    alt="{{ $cust?->full_name }}"
                                    onerror="this.remove()"
                                >
                            @endif
                            <span>{{ mb_strtoupper(mb_substr(trim($cust?->full_name ?? $cust?->name ?? 'K'), 0, 1, 'UTF-8')) }}</span>
                        </div>
                        <div>
                            <div class="staff-customer-profile-name" data-tooltip="{{ $cust?->full_name ?? 'Khách hàng' }}">{{ $cust?->full_name ?? 'Khách hàng' }}</div>
                        </div>
                    </div>

                    <div class="staff-customer-detail-rows">
                        <div class="staff-customer-detail-row">
                            <i class="fa-solid fa-phone"></i>
                            <span data-tooltip="{{ $cust?->phone ?? 'Chưa cập nhật SĐT' }}">{{ $cust?->phone ?? 'Chưa cập nhật SĐT' }}</span>
                        </div>
                        <div class="staff-customer-detail-row">
                            <i class="fa-regular fa-envelope"></i>
                            <span style="word-break: break-all;" data-tooltip="{{ $cust?->email ?? 'Chưa cập nhật email' }}">{{ $cust?->email ?? 'Chưa cập nhật email' }}</span>
                        </div>
                        <div class="staff-customer-detail-row">
                            <i class="fa-solid fa-location-dot"></i>
                            <span data-tooltip="{{ $cust?->address ?? 'Hà Nội' }}">{{ $cust?->address ?? 'Hà Nội' }}</span>
                        </div>
                        <div class="staff-customer-detail-row">
                            <i class="fa-regular fa-calendar"></i>
                            <span>Tham gia: {{ $cust?->created_at ? $cust->created_at->format('d/m/Y') : '12/04/2024' }}</span>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: THÔNG TIN HỖ TRỢ --}}
                <div class="staff-support-info-card">
                    <h3 class="staff-support-info-card__title">
                        <i class="fa-solid fa-headset"></i>
                        <span>Thông tin hỗ trợ</span>
                    </h3>

                    <div class="staff-support-case-meta-list">
                        <div class="staff-support-case-meta-row">
                            <span class="staff-support-case-meta-label">Mã case:</span>
                            <span class="staff-support-case-meta-val" data-tooltip="{{ $selectedCase->case_code }}">{{ $selectedCase->case_code }}</span>
                        </div>
                        <div class="staff-support-case-meta-row">
                            <span class="staff-support-case-meta-label">Mã đơn:</span>
                            <span class="staff-support-case-meta-val" data-related-order-code data-tooltip="{{ $selectedCase->order ? '#' . $selectedCase->order->order_code : 'Không có' }}">
                                {{ $selectedCase->order ? '#' . $selectedCase->order->order_code : 'Không có' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: THÔNG TIN ĐƠN HÀNG --}}
                <div class="staff-support-info-card">
                    <h3 class="staff-support-info-card__title">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Thông tin đơn hàng</span>
                    </h3>

                    <div class="staff-support-case-meta-list">
                        <div class="staff-support-case-meta-row">
                            <span class="staff-support-case-meta-label">Tổng đơn hàng:</span>
                            <span class="staff-support-case-meta-val">{{ $ordersCount }} đơn</span>
                        </div>
                    </div>

                    @if ($customerOrders->isNotEmpty())
                        <div class="staff-support-order-list {{ $ordersCount >= 5 ? 'has-scroll' : '' }}">
                            @foreach ($customerOrders as $cOrder)
                                @php
                                    $orderRoute = str_starts_with($routePrefix, 'admin.')
                                        ? route('admin.orders.show', $cOrder)
                                        : route('staff.orders.show', $cOrder);
                                    $isCurrentRelated = $selectedCase->order_id && (int) $selectedCase->order_id === (int) $cOrder->id;

                                    $statusLabels = [
                                        'PENDING' => 'Chờ xác nhận',
                                        'CONFIRMED' => 'Đã xác nhận',
                                        'PROCESSING' => 'Đang xử lý',
                                        'SHIPPING' => 'Đang giao',
                                        'COMPLETED' => 'Hoàn thành',
                                        'CANCELLED' => 'Đã hủy',
                                        'REFUNDED' => 'Hoàn tiền',
                                    ];
                                    $statusLabel = $statusLabels[$cOrder->order_status] ?? $cOrder->order_status;
                                @endphp
                                <a
                                    href="{{ $orderRoute }}"
                                    class="staff-support-order-item-btn {{ $isCurrentRelated ? 'is-current' : '' }}"
                                    data-order-card-code="{{ $cOrder->order_code }}"
                                    data-order-card-id="{{ $cOrder->id }}"
                                    data-tooltip="Đơn hàng #{{ $cOrder->order_code }} • {{ $statusLabel }} • {{ number_format($cOrder->total_amount, 0, ',', '.') }} đ"
                                    target="_blank"
                                    title="Xem chi tiết đơn #{{ $cOrder->order_code }}"
                                >
                                    <div class="staff-support-order-item-header">
                                        <span class="staff-support-order-item-code" data-tooltip="Mã đơn hàng: #{{ $cOrder->order_code }}">#{{ $cOrder->order_code }}</span>
                                        @if ($isCurrentRelated)
                                            <span class="staff-support-order-item-tag" data-tooltip="Đơn hàng liên quan đến cuộc hỗ trợ này">Đơn liên quan</span>
                                        @endif
                                    </div>
                                    <div class="staff-support-order-item-footer">
                                        <span class="staff-support-order-item-status status-{{ strtolower($cOrder->order_status) }}">{{ $statusLabel }}</span>
                                        <div class="staff-support-order-item-price-wrap">
                                            <span class="staff-support-order-item-price">{{ number_format($cOrder->total_amount, 0, ',', '.') }} đ</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square staff-support-order-item-icon"></i>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="staff-support-order-empty">
                            <span>Khách hàng chưa có đơn hàng nào</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL PHÂN CÔNG / CHUYỂN NHÂN VIÊN DÀNH CHO ADMIN --}}
    @if ($user->role === \App\Models\User::ROLE_ADMIN && $selectedCase)
        <div id="assignStaffModal" class="staff-modal-backdrop" style="display: none;">
            <div class="staff-modal-content">
                <div class="staff-modal-header">
                    <h3 class="staff-modal-title">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Chuyển người phụ trách cuộc hỗ trợ</span>
                    </h3>
                    <button type="button" class="staff-modal-close" data-modal-close>&times;</button>
                </div>
                <form action="{{ route('admin.support.assign', $selectedCase) }}" method="POST" id="assignStaffForm">
                    @csrf
                    <div class="staff-modal-body">
                        <p style="margin-bottom: 14px; font-size: 13px; color: #6b7280; line-height: 1.5;">
                            Chuyển cuộc hỗ trợ <strong>#{{ $selectedCase->case_code }}</strong> (Khách hàng: <strong>{{ $selectedCase->customer?->full_name }}</strong>) cho nhân viên tiếp nhận:
                        </p>
                        <div class="staff-modal-form-group">
                            <label for="assignStaffSelect" style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; display: block;">
                                Chọn nhân viên hỗ trợ:
                            </label>
                            <select name="staff_id" id="assignStaffSelect" class="staff-modal-select" required>
                                <option value="">-- Chọn nhân viên tiếp nhận --</option>
                                @foreach ($staffList as $st)
                                    <option value="{{ $st->id }}" {{ (int) $selectedCase->assigned_staff_id === (int) $st->id ? 'disabled' : '' }}>
                                        {{ $st->full_name }} ({{ $st->email }}) {{ (int) $selectedCase->assigned_staff_id === (int) $st->id ? ' [Đang xử lý]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="staff-modal-footer">
                        <button type="button" class="staff-modal-btn-cancel" data-modal-close>Hủy</button>
                        <button type="submit" class="staff-modal-btn-submit">
                            <i class="fa-solid fa-check"></i>
                            <span>Xác nhận chuyển</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

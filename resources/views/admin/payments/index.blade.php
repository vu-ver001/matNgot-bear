@extends('layouts.admin-dashboard')

@section('page-title', 'Quản lý thanh toán & Đối soát dòng tiền')

@section('content')
<div class="px-2 sm:px-4 lg:px-6 max-w-7xl mx-auto font-sans" x-data="{
    activeTab: '{{ $activeTab ?? 'transactions' }}',
    drawerOpen: false,
    selectedPayment: null,
    refundModalOpen: false,
    confirmModalOpen: false,
    qrRefundModalOpen: false,
    selectedRefundRequest: null,
    rejectModalOpen: false,
    paymentToAct: null,

    openDrawer(payment) {
        this.selectedPayment = payment;
        this.drawerOpen = true;
    },
    closeDrawer() {
        this.drawerOpen = false;
    },
    openConfirm(payment) {
        this.paymentToAct = payment;
        this.confirmModalOpen = true;
    },
    openRefund(payment) {
        this.paymentToAct = payment;
        this.refundModalOpen = true;
    },
    openQrRefund(req) {
        this.selectedRefundRequest = req;
        this.qrRefundModalOpen = true;
    },
    openRejectRefund(req) {
        this.selectedRefundRequest = req;
        this.rejectModalOpen = true;
    }
}">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            {{-- <x-breadcrumb :items="[
                ['label' => 'Trang chủ', 'url' => route('admin.dashboard')],
                ['label' => 'Quản lý thanh toán & Đối soát']
            ]" class="mb-2 text-xs" /> --}}
            <h1 class="text-2xl sm:text-3xl font-black text-[#2C1408] tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#5C3219] to-[#8C5835] text-white flex items-center justify-center text-lg shadow-sm">
                    💳
                </span>
                <span style="font-weight: 800;">Quản lý Thanh toán &amp; Đối soát</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#786B61] mt-1">
                Xem toàn bộ dòng tiền, duyệt hoàn tiền kèm mã QR, đối soát công nợ COD &amp; cấu hình cổng thanh toán.
            </p>
        </div>

        {{-- Top Actions: Settings, Export CSV & Quick Links --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.payments.settings') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] font-bold text-xs shadow-xs hover:shadow-sm transition">
                <i class="fa-solid fa-sliders text-[#E08A1E]"></i>
                <span>Cấu Hình Cổng &amp; API</span>
            </a>
            <a href="{{ route('admin.payments.export', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EBDDCD] hover:border-emerald-600 text-emerald-800 font-bold text-xs shadow-xs hover:shadow-sm transition">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Xuất Báo Cáo</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#5C3219] hover:bg-[#432310] text-white font-bold text-xs shadow-sm hover:shadow transition">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Xem Đơn Hàng</span>
            </a>
        </div>
    </div>


    {{-- Navigation Tabs (Shopify Style) --}}
    <div class="flex items-center gap-2 mb-6 border-b border-[#EBDDCD] pb-2 overflow-x-auto">
        <a href="{{ route('admin.payments.index', ['tab' => 'transactions']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition {{ ($activeTab ?? 'transactions') === 'transactions' ? 'bg-[#5C3219] text-white shadow-sm' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:bg-[#FFF9EE]' }}">
            <i class="fa-solid fa-list-check"></i>
            <span>Sổ Giao Dịch</span>
            <span class="px-2 py-0.5 rounded-md text-[10px] {{ ($activeTab ?? 'transactions') === 'transactions' ? 'bg-white/20 text-white' : 'bg-[#FAF6EE] text-[#786B61]' }}">
                {{ $counts['ALL'] }}
            </span>
        </a>

        <a href="{{ route('admin.payments.index', ['tab' => 'refund_requests']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition {{ ($activeTab ?? 'transactions') === 'refund_requests' ? 'bg-[#5C3219] text-white shadow-sm' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:bg-[#FFF9EE]' }}">
            <i class="fa-solid fa-arrow-rotate-left text-purple-500"></i>
            <span>Duyệt Hoàn Tiền &amp; Quét Mã QR</span>
            @if ($counts['REFUND_REQUESTS'] > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white animate-pulse">
                    {{ $counts['REFUND_REQUESTS'] }} chờ duyệt
                </span>
            @endif
        </a>

        <a href="{{ route('admin.payments.index', ['tab' => 'cod']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition {{ ($activeTab ?? 'transactions') === 'cod' ? 'bg-[#5C3219] text-white shadow-sm' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:bg-[#FFF9EE]' }}">
            <i class="fa-solid fa-truck-fast text-blue-500"></i>
            <span>Đối Soát COD &amp; Công Nợ ĐVVC</span>
        </a>

        <a href="{{ route('admin.payments.settings') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition bg-white border border-[#EBDDCD] text-[#5C3219] hover:border-[#E08A1E] hover:bg-[#FFF9EE] ml-auto">
            <i class="fa-solid fa-gear text-[#E08A1E]"></i>
            <span>Cấu Hình Ngân Hàng &amp; SePAY</span>
        </a>
    </div>

    {{-- TAB 1: SỔ CÁI GIAO DỊCH --}}
    @if (($activeTab ?? 'transactions') === 'transactions')
        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Paid (Thực thu) --}}
            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs relative overflow-hidden group hover:border-emerald-400 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">Thực Thu Thành Công</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 tracking-tight">
                    {{ number_format($kpi['total_paid'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Tiền đã về tài khoản / Đã thu xong
                </p>
            </div>

            {{-- Pending Payments --}}
            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs relative overflow-hidden group hover:border-amber-400 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">Chờ Thanh Toán</span>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-[#E08A1E] tracking-tight">
                    {{ number_format($kpi['total_pending'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Chờ quét VietQR hoặc chờ giao COD
                </p>
            </div>

            {{-- Pending COD --}}
            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs relative overflow-hidden group hover:border-blue-400 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">COD Đang Luân Chuyển</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-blue-600 tracking-tight">
                    {{ number_format($kpi['total_cod_pending'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Tiền bưu cục / Shipper đang giữ
                </p>
            </div>

            {{-- Total Refunded --}}
            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs relative overflow-hidden group hover:border-purple-400 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">Tiền Đã Hoàn Trả</span>
                    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </span>
                </div>
                <div class="text-2xl font-black text-purple-600 tracking-tight">
                    {{ number_format($kpi['total_refunded'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    Đã hoàn tiền do đổi trả / hủy đơn
                </p>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            {{-- Status Tabs --}}
            <div class="border-b border-[#F0E6D8] bg-[#FAF8F5] px-5 pt-3 overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max pb-3">
                    @php
                        $statusTabs = [
                            '' => ['label' => 'Tất cả giao dịch', 'count' => $counts['ALL']],
                            'PENDING' => ['label' => 'Chờ thanh toán', 'count' => $counts['PENDING']],
                            'PAID' => ['label' => 'Đã thanh toán', 'count' => $counts['PAID']],
                            'FAILED' => ['label' => 'Thất bại', 'count' => $counts['FAILED']],
                            'REFUNDED' => ['label' => 'Đã hoàn tiền', 'count' => $counts['REFUNDED']],
                        ];
                        $currentStatus = request('status', '');
                    @endphp

                    @foreach ($statusTabs as $stKey => $stData)
                        <a href="{{ route('admin.payments.index', array_merge(request()->except('status', 'p_page'), $stKey ? ['status' => $stKey] : [])) }}"
                           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ $currentStatus === $stKey ? 'bg-[#5C3219] text-white shadow-xs' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:border-[#E08A1E] hover:bg-[#FFF9EE]' }}">
                            <span>{{ $stData['label'] }}</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === $stKey ? 'bg-white/20 text-white' : 'bg-[#FAF6EE] text-[#786B61]' }}">
                                {{ $stData['count'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Smart Search & Filters --}}
            <div class="p-5 border-b border-[#F0E6D8] bg-white">
                <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                    <input type="hidden" name="tab" value="transactions">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    {{-- Keyword Search --}}
                    <div class="lg:col-span-4">
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Tìm kiếm giao dịch</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Mã đơn / Mã GD / Tên / SĐT..."
                                   class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E] placeholder-gray-400">
                        </div>
                    </div>

                    {{-- Payment Method Filter --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Phương thức</label>
                        <select name="method" class="w-full py-2 px-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                            <option value="">-- Tất cả phương thức --</option>
                            <option value="BANK_TRANSFER" @selected(request('method') === 'BANK_TRANSFER')>Chuyển khoản (VietQR / SePAY)</option>
                            <option value="COD" @selected(request('method') === 'COD')>Tiền mặt (COD)</option>
                            <option value="E_WALLET" @selected(request('method') === 'E_WALLET')>Ví điện tử</option>
                            <option value="CARD" @selected(request('method') === 'CARD')>Thẻ ATM / Quốc tế</option>
                        </select>
                    </div>

                    {{-- Date Preset Filter --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Thời gian</label>
                        <select name="date_preset" class="w-full py-2 px-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                            <option value="">-- Tất cả thời gian --</option>
                            <option value="today" @selected(request('date_preset') === 'today')>Hôm nay</option>
                            <option value="yesterday" @selected(request('date_preset') === 'yesterday')>Hôm qua</option>
                            <option value="7days" @selected(request('date_preset') === '7days')>7 ngày gần nhất</option>
                            <option value="this_month" @selected(request('date_preset') === 'this_month')>Tháng này</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="lg:col-span-2 flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 py-2 px-3 bg-[#E08A1E] hover:bg-[#C2751D] text-white text-xs font-extrabold rounded-xl shadow-xs hover:shadow transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            <span>Lọc Dữ Liệu</span>
                        </button>
                        <a href="{{ route('admin.payments.index', ['tab' => 'transactions']) }}"
                           class="py-2 px-3 bg-[#FAF8F5] border border-[#EBDDCD] hover:border-gray-400 text-[#786B61] text-xs font-semibold rounded-xl transition"
                           title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-arrow-rotate-right"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- Transactions Table --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-xs text-[#2C1408]">
                    <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã GD / Đơn Hàng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Phương Thức</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Số Tiền</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã Tham Chiếu / Chứng Từ</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Trạng Thái</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Thời Gian</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0E6D8]">
                        @forelse ($payments as $item)
                            @php
                                $order = $item->order;
                                $methodBadge = match ($item->method) {
                                    'BANK_TRANSFER' => ['label' => 'VietQR / SePAY', 'class' => 'bg-amber-50 text-[#C2751D] border-amber-200', 'icon' => 'fa-qrcode'],
                                    'COD' => ['label' => 'Tiền mặt (COD)', 'class' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'fa-truck-fast'],
                                    'E_WALLET' => ['label' => 'Ví điện tử', 'class' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'fa-wallet'],
                                    'CARD' => ['label' => 'Thẻ ATM / Visa', 'class' => 'bg-slate-50 text-slate-700 border-slate-200', 'icon' => 'fa-credit-card'],
                                    default => ['label' => $item->method, 'class' => 'bg-gray-50 text-gray-700 border-gray-200', 'icon' => 'fa-circle-dot'],
                                };

                                $statusBadge = match ($item->status) {
                                    'PAID' => ['label' => 'Đã thanh toán', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dot-emerald-500'],
                                    'PENDING' => ['label' => 'Chờ thanh toán', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 dot-amber-500'],
                                    'FAILED' => ['label' => 'Thất bại', 'class' => 'bg-rose-50 text-rose-700 border-rose-200 dot-rose-500'],
                                    'REFUNDED' => ['label' => 'Đã hoàn tiền', 'class' => 'bg-purple-50 text-purple-700 border-purple-200 dot-purple-500'],
                                    default => ['label' => $item->status, 'class' => 'bg-gray-50 text-gray-700 border-gray-200 dot-gray-500'],
                                };
                            @endphp

                            <tr class="hover:bg-[#FFFDF9] transition group">
                                {{-- Mã GD & Đơn hàng --}}
                                <td class="py-3.5 px-4 font-semibold whitespace-nowrap">
                                    <div class="font-bold text-[#5C3219]">#PAY-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @if ($order)
                                        <a href="{{ route('admin.orders.show', $order->id) }}" 
                                           class="inline-flex items-center gap-1 text-[11px] font-bold text-[#E08A1E] hover:underline mt-0.5">
                                            <span>Đơn: {{ $order->order_code }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-gray-400">Không có đơn</span>
                                    @endif
                                </td>

                                {{-- Khách hàng --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#2C1408] max-w-[170px] truncate" title="{{ $order?->recipient_name ?? $order?->customer?->full_name }}">
                                        {{ $order?->recipient_name ?? $order?->customer?->full_name ?? 'Khách vãng lai' }}
                                    </div>
                                    <div class="text-[11px] text-[#786B61] mt-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-phone text-[10px] text-gray-400"></i>
                                        <span>{{ $order?->recipient_phone ?? $order?->customer?->phone ?? '—' }}</span>
                                    </div>
                                </td>

                                {{-- Phương thức --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold whitespace-nowrap {{ $methodBadge['class'] }}">
                                        <i class="fa-solid {{ $methodBadge['icon'] }}"></i>
                                        <span>{{ $methodBadge['label'] }}</span>
                                    </span>
                                </td>

                                {{-- Số tiền --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-black text-sm text-[#2C1408]">
                                        {{ number_format($item->amount, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                {{-- Mã tham chiếu NH / Ảnh bill --}}
                                <td class="py-3.5 px-4 text-[11px] whitespace-nowrap">
                                    @if ($item->transaction_ref)
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#FAF6EE] border border-[#EBDDCD] text-[#5C3219] font-mono font-bold">
                                            <span>{{ $item->transaction_ref }}</span>
                                        </div>
                                    @elseif ($item->proof_image)
                                        <a href="{{ $item->proof_image_url }}" target="_blank" 
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold hover:bg-emerald-100 transition">
                                            <i class="fa-solid fa-image"></i>
                                            <span>Xem ảnh bill</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif

                                    @if ($item->note)
                                        <div class="text-[10px] text-gray-500 italic max-w-[150px] truncate mt-0.5" title="{{ $item->note }}">
                                            Ghi chú: {{ $item->note }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Trạng thái --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[11px] font-bold whitespace-nowrap shrink-0 {{ $statusBadge['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ str_replace('dot-', 'bg-', $statusBadge['class']) }}"></span>
                                        <span class="whitespace-nowrap">{{ $statusBadge['label'] }}</span>
                                    </span>
                                </td>

                                {{-- Thời gian --}}
                                <td class="py-3.5 px-4 text-[11px] text-[#786B61] whitespace-nowrap">
                                    <div>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '—' }}</div>
                                    @if ($item->paid_at)
                                        <div class="text-[10px] text-emerald-600 font-medium whitespace-nowrap">Trả lúc: {{ $item->paid_at->format('H:i d/m') }}</div>
                                    @endif
                                </td>

                                {{-- Thao tác --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Xem chi tiết drawer --}}
                                        <button type="button" 
                                                @click="openDrawer({{ json_encode([
                                                    'id' => $item->id,
                                                    'order_code' => $order?->order_code,
                                                    'order_url' => $order ? route('admin.orders.show', $order->id) : null,
                                                    'recipient_name' => $order?->recipient_name ?? $order?->customer?->full_name,
                                                    'recipient_phone' => $order?->recipient_phone ?? $order?->customer?->phone,
                                                    'recipient_address' => $order?->recipient_address,
                                                    'method' => $item->method,
                                                    'method_label' => $methodBadge['label'],
                                                    'amount' => number_format($item->amount, 0, ',', '.') . 'đ',
                                                    'transaction_ref' => $item->transaction_ref,
                                                    'proof_image_url' => $item->proof_image_url,
                                                    'note' => $item->note,
                                                    'status' => $item->status,
                                                    'status_label' => $statusBadge['label'],
                                                    'gateway_response' => $item->gateway_response,
                                                    'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '—',
                                                    'paid_at' => $item->paid_at ? $item->paid_at->format('d/m/Y H:i:s') : '—',
                                                    'confirmed_by' => $item->confirmedByUser?->full_name ?? ($item->status === 'PAID' ? 'Hệ thống tự động' : '—'),
                                                ]) }})"
                                                class="w-8 h-8 rounded-lg bg-[#FAF8F5] border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] flex items-center justify-center text-xs transition"
                                                title="Xem chi tiết giao dịch">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        {{-- Realtime SePAY Check for Bank Transfer --}}
                                        @if ($item->status === 'PENDING' && in_array($item->method, ['BANK_TRANSFER', 'E_WALLET']))
                                            <form method="POST" action="{{ route('admin.payments.verifySepay', $item->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-8 h-8 rounded-lg bg-amber-500 hover:bg-[#C2751D] text-white flex items-center justify-center text-xs shadow-xs transition"
                                                        title="Đối soát SePAY ngay">
                                                    <i class="fa-solid fa-bolt"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Manual Confirm (Admin Toàn quyền) --}}
                                        @if ($item->status === 'PENDING')
                                            <button type="button" 
                                                    @click="openConfirm({{ json_encode(['id' => $item->id, 'amount' => number_format($item->amount, 0, ',', '.') . 'đ', 'order_code' => $order?->order_code]) }})"
                                                    class="w-8 h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center text-xs shadow-xs transition"
                                                    title="Xác nhận đã thu tiền">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        {{-- Refund Action for Paid --}}
                                        @if ($item->status === 'PAID')
                                            <button type="button" 
                                                    @click="openRefund({{ json_encode(['id' => $item->id, 'amount' => number_format($item->amount, 0, ',', '.') . 'đ', 'order_code' => $order?->order_code]) }})"
                                                    class="w-8 h-8 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-700 flex items-center justify-center text-xs transition"
                                                    title="Hoàn tiền trực tiếp">
                                                <i class="fa-solid fa-arrow-rotate-left"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-[#786B61]">
                                    <div class="w-16 h-16 rounded-full bg-[#FAF6EE] border border-[#EBDDCD] flex items-center justify-center text-3xl mx-auto mb-3">
                                        🧸
                                    </div>
                                    <div class="font-bold text-sm text-[#2C1408]">Không tìm thấy bản ghi giao dịch nào</div>
                                    <p class="text-xs text-[#786B61] mt-1">Hãy thử thay đổi điều kiện lọc hoặc từ khóa tìm kiếm.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($payments->hasPages())
                <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5]">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 2: DUYỆT HOÀN TIỀN & QUÉT MÃ QR --}}
    @if (($activeTab ?? 'transactions') === 'refund_requests')
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2C1408]">Danh Sách Yêu Cầu Hoàn Tiền</h3>
                        <p class="text-xs text-[#786B61]">Phê duyệt lệnh và quét mã VietQR Napas247 để chuyển tiền hoàn tức thì cho khách hàng.</p>
                    </div>
                </div>

                {{-- Status Filter for Refund Requests --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.payments.index', ['tab' => 'refund_requests']) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ !request('rf_status') ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('admin.payments.index', ['tab' => 'refund_requests', 'rf_status' => 'PENDING']) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('rf_status') === 'PENDING' ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                        Chờ duyệt
                    </a>
                    <a href="{{ route('admin.payments.index', ['tab' => 'refund_requests', 'rf_status' => 'APPROVED']) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('rf_status') === 'APPROVED' ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                        Đã duyệt
                    </a>
                    <a href="{{ route('admin.payments.index', ['tab' => 'refund_requests', 'rf_status' => 'REJECTED']) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ request('rf_status') === 'REJECTED' ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                        Từ chối
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-xs text-[#2C1408]">
                    <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã Yêu Cầu / Đơn</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Số Tiền Hoàn</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Tài Khoản Khách Nhận</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Lý Do Hoàn</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Nhân Viên Yêu Cầu</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Trạng Thái</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0E6D8]">
                        @forelse ($refundRequests as $rf)
                            @php
                                $rfOrder = $rf->order;
                                $rfStatusBadge = match ($rf->status) {
                                    'APPROVED' => ['label' => 'Đã duyệt & chuyển tiền', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                    'PENDING' => ['label' => 'Chờ Admin duyệt', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse'],
                                    'REJECTED' => ['label' => 'Đã từ chối', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                    default => ['label' => $rf->status, 'class' => 'bg-gray-50 text-gray-700 border-gray-200'],
                                };
                            @endphp
                            <tr class="hover:bg-[#FFFDF9] transition">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-[#5C3219]">#RF-{{ str_pad($rf->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @if ($rfOrder)
                                        <a href="{{ route('admin.orders.show', $rfOrder->id) }}" class="text-[11px] font-bold text-[#E08A1E] hover:underline">
                                            Đơn: {{ $rfOrder->order_code }}
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#2C1408]">{{ $rfOrder?->recipient_name ?? $rfOrder?->customer?->full_name ?? '—' }}</div>
                                    <div class="text-[11px] text-[#786B61]">{{ $rfOrder?->recipient_phone ?? $rfOrder?->customer?->phone ?? '—' }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-black text-sm text-purple-700">
                                        {{ number_format($rf->amount, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-xs">
                                    @if ($rf->bank_account)
                                        <div class="font-bold text-[#5C3219]">{{ $rf->bank_name }} - {{ $rf->bank_account }}</div>
                                        <div class="text-[11px] text-[#786B61] uppercase">{{ $rf->account_holder }}</div>
                                    @else
                                        <span class="text-gray-400">Chưa cung cấp STK</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 max-w-[200px]">
                                    <div class="text-xs text-[#2C1408] truncate" title="{{ $rf->reason }}">{{ $rf->reason }}</div>
                                    @if ($rf->proof_image)
                                        <a href="{{ Storage::disk('public')->url($rf->proof_image) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-blue-600 hover:underline mt-0.5">
                                            <i class="fa-solid fa-image"></i>
                                            <span>Xem ảnh chứng từ</span>
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-[#2C1408]">{{ $rf->requestedByUser?->full_name ?? 'Nhân viên' }}</div>
                                    <div class="text-[10px] text-[#786B61]">{{ $rf->created_at->format('d/m/Y H:i') }}</div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-bold {{ $rfStatusBadge['class'] }}">
                                        {{ $rfStatusBadge['label'] }}
                                    </span>
                                    @if ($rf->admin_note)
                                        <div class="text-[10px] text-gray-500 mt-1 italic max-w-[140px] truncate" title="{{ $rf->admin_note }}">
                                            Admin: {{ $rf->admin_note }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if ($rf->status === 'PENDING')
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Quét mã VietQR & Phê duyệt --}}
                                            <button type="button" 
                                                    @click="openQrRefund({{ json_encode([
                                                        'id' => $rf->id,
                                                        'amount' => number_format($rf->amount, 0, ',', '.') . 'đ',
                                                        'raw_amount' => (int) $rf->amount,
                                                        'order_code' => $rfOrder?->order_code,
                                                        'bank_name' => $rf->bank_name,
                                                        'bank_account' => $rf->bank_account,
                                                        'account_holder' => $rf->account_holder,
                                                        'reason' => $rf->reason,
                                                        'viet_qr_url' => $rf->viet_qr_url,
                                                        'requested_by' => $rf->requestedByUser?->full_name,
                                                    ]) }})"
                                                    class="px-3 py-1.5 rounded-xl bg-[#5C3219] hover:bg-[#432310] text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <span>Quét QR &amp; Duyệt</span>
                                            </button>

                                            {{-- Từ chối --}}
                                            <button type="button" 
                                                    @click="openRejectRefund({{ json_encode([
                                                        'id' => $rf->id,
                                                        'order_code' => $rfOrder?->order_code,
                                                    ]) }})"
                                                    class="p-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition"
                                                    title="Từ chối yêu cầu">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Đã giải quyết</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-[#786B61]">
                                    <div class="w-14 h-14 rounded-full bg-[#FAF6EE] flex items-center justify-center text-2xl mx-auto mb-2">
                                        ✨
                                    </div>
                                    <div class="font-bold text-sm text-[#2C1408]">Không có yêu cầu hoàn tiền nào</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($refundRequests->hasPages())
                <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5]">
                    {{ $refundRequests->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 3: ĐỐI SOÁT COD & CÔNG NỢ ĐVVC --}}
    @if (($activeTab ?? 'transactions') === 'cod')
        {{-- COD Debt KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">COD Đang Luân Chuyển</span>
                <div class="text-2xl font-black text-blue-600 mt-1">
                    {{ number_format($codDebtSummary['cod_in_transit'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1">Đơn hàng đang giao, Shipper giữ tiền</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">COD Đã Đối Soát (Chờ Tiền Về)</span>
                <div class="text-2xl font-black text-[#E08A1E] mt-1">
                    {{ number_format($codDebtSummary['cod_reconciled_pending_settle'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1">Nhân viên đã đối soát, bưu cục chuẩn bị nộp</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">COD Đã Nhận Về Tài Khoản</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">
                    {{ number_format($codDebtSummary['cod_settled'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1">Tiền ĐVVC đã chuyển thành công về tài khoản shop</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-[#786B61]">Tổng Tiền COD Thực Thu</span>
                <div class="text-2xl font-black text-[#5C3219] mt-1">
                    {{ number_format($codDebtSummary['cod_total_delivered'], 0, ',', '.') }}đ
                </div>
                <p class="text-[11px] text-[#786B61] mt-1">Toàn bộ doanh thu thu hộ COD thành công</p>
            </div>
        </div>

        {{-- COD Table --}}
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden" x-data="{ selectedCod: [] }">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2C1408]">Quản Lý Đối Soát Đơn COD &amp; Nhận Tiền Về Tài Khoản</h3>
                        <p class="text-xs text-[#786B61]">Admin theo dõi công nợ các đơn vị vận chuyển và chốt nhận tiền về tài khoản ngân hàng shop.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.payments.bulkMarkCodSettled') }}" class="inline" x-show="selectedCod.length > 0">
                    @csrf
                    <template x-for="id in selectedCod" :key="id">
                        <input type="hidden" name="payment_ids[]" :value="id">
                    </template>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-sm transition flex items-center gap-1.5">
                        <i class="fa-solid fa-building-columns"></i>
                        <span>Chốt Nhận Tiền Về TK (<span x-text="selectedCod.length"></span> đơn)</span>
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-xs text-[#2C1408]">
                    <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 w-10 text-center">
                                <input type="checkbox" @click="selectedCod = selectedCod.length ? [] : {{ json_encode($codPayments->whereNotNull('cod_reconciled_at')->whereNull('cod_settled_at')->pluck('id')) }}" 
                                       class="rounded text-[#E08A1E] focus:ring-[#E08A1E]">
                            </th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã GD / Đơn Hàng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Tiền Thu Hộ COD</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Đối Soát (Nhân Viên)</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Nhận Tiền Về TK (Admin)</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0E6D8]">
                        @forelse ($codPayments as $cp)
                            @php $cpOrder = $cp->order; @endphp
                            <tr class="hover:bg-[#FFFDF9] transition">
                                <td class="py-3.5 px-4 text-center">
                                    @if (!$cp->cod_settled_at)
                                        @if ($cp->cod_reconciled_at)
                                            <input type="checkbox" value="{{ $cp->id }}" x-model="selectedCod" class="rounded text-[#E08A1E] focus:ring-[#E08A1E]">
                                        @else
                                            <span class="text-gray-300" title="Chờ nhân viên đối soát với bưu tá"><i class="fa-solid fa-lock text-[11px]"></i></span>
                                        @endif
                                    @else
                                        <i class="fa-solid fa-check-double text-emerald-500"></i>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap font-semibold">
                                    <div class="font-bold text-[#5C3219]">#PAY-{{ str_pad($cp->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @if ($cpOrder)
                                        <a href="{{ route('admin.orders.show', $cpOrder->id) }}" class="text-[11px] font-bold text-[#E08A1E] hover:underline">
                                            Đơn: {{ $cpOrder->order_code }}
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#2C1408]">{{ $cpOrder?->recipient_name ?? $cpOrder?->customer?->full_name ?? '—' }}</div>
                                    <div class="text-[11px] text-[#786B61]">{{ $cpOrder?->recipient_phone ?? $cpOrder?->customer?->phone ?? '—' }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-black text-sm text-[#2C1408]">
                                        {{ number_format($cp->amount, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($cp->cod_reconciled_at)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Đã đối soát ({{ $cp->cod_reconciled_at->format('d/m H:i') }})</span>
                                        </span>
                                        <div class="text-[10px] text-gray-500 mt-0.5">NV: {{ $cp->reconciledByUser?->full_name ?? 'Nhân viên' }}</div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-clock"></i>
                                            <span>Chưa đối soát với bưu tá</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($cp->cod_settled_at)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i class="fa-solid fa-building-columns"></i>
                                            <span>Đã nhận về TK ({{ $cp->cod_settled_at->format('d/m H:i') }})</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600">
                                            Chưa nhận tiền
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if (!$cp->cod_settled_at)
                                        @if ($cp->cod_reconciled_at)
                                            <form method="POST" action="{{ route('admin.payments.markCodSettled', $cp->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span>Nhận Tiền Về TK</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-gray-100 text-gray-400 border border-dashed border-gray-300 cursor-not-allowed" title="Cần nhân viên đối soát với bưu tá trước">
                                                <i class="fa-solid fa-lock text-[10px]"></i>
                                                <span>Chờ đối soát</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[11px] font-bold text-emerald-600">✓ Hoàn tất</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#786B61]">
                                    <div class="font-bold text-sm text-[#2C1408]">Không có đơn COD nào</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($codPayments->hasPages())
                <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5]">
                    {{ $codPayments->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- SLIDE-OVER DETAIL DRAWER --}}
    <div x-show="drawerOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 backdrop-blur-xs flex justify-end"
         style="display: none;"
         @keydown.escape.window="closeDrawer()">

        <div x-show="drawerOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             @click.outside="closeDrawer()"
             class="w-full max-w-md sm:max-w-lg bg-white h-full shadow-2xl flex flex-col justify-between">
            
            {{-- Drawer Header --}}
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-[#8B7A6B] uppercase tracking-wider">Chi Tiết Thanh Toán</span>
                    <h3 class="text-lg font-black text-[#2C1408]" x-text="'#PAY-' + (selectedPayment ? selectedPayment.id.toString().padStart(5, '0') : '')"></h3>
                </div>
                <button type="button" @click="closeDrawer()" 
                        class="w-8 h-8 rounded-full bg-white border border-[#EBDDCD] hover:bg-gray-100 flex items-center justify-center text-[#786B61] transition">
                    &times;
                </button>
            </div>

            {{-- Drawer Body --}}
            <div class="p-5 overflow-y-auto space-y-5 text-xs text-[#2C1408] flex-1">
                {{-- Status & Amount Callout --}}
                <div class="p-4 rounded-2xl bg-gradient-to-br from-[#FFF9EE] to-[#FFF4DE] border border-[#FDE68A] flex items-center justify-between">
                    <div>
                        <div class="text-[11px] text-[#786B61] font-semibold">Tổng số tiền giao dịch</div>
                        <div class="text-2xl font-black text-[#E08A1E] mt-0.5" x-text="selectedPayment?.amount"></div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white border border-[#E08A1E] text-[#C2751D] shadow-2xs whitespace-nowrap"
                          x-text="selectedPayment?.status_label">
                    </span>
                </div>

                {{-- Order Meta --}}
                <div class="space-y-2.5">
                    <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Thông Tin Đơn Hàng Gắn Liền</h4>
                    <div class="p-3.5 rounded-xl border border-[#F0E6D8] bg-[#FAF8F5] space-y-2">
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Mã đơn:</span>
                            <a :href="selectedPayment?.order_url" target="_blank" class="font-bold text-[#E08A1E] hover:underline flex items-center gap-1">
                                <span x-text="selectedPayment?.order_code"></span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                            </a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Người nhận:</span>
                            <span class="font-bold text-right" x-text="selectedPayment?.recipient_name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Số điện thoại:</span>
                            <span class="font-mono" x-text="selectedPayment?.recipient_phone"></span>
                        </div>
                        <div class="flex justify-between items-start">
                            <span class="text-[#786B61] shrink-0">Địa chỉ giao:</span>
                            <span class="text-right text-[11px] max-w-[220px]" x-text="selectedPayment?.recipient_address"></span>
                        </div>
                    </div>
                </div>

                {{-- Payment Details --}}
                <div class="space-y-2.5">
                    <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Dữ Liệu Đối Soát Ngân Hàng &amp; Xác Nhận</h4>
                    <div class="p-3.5 rounded-xl border border-[#F0E6D8] space-y-2">
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Phương thức:</span>
                            <span class="font-bold" x-text="selectedPayment?.method_label"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Mã tham chiếu NH / SePAY:</span>
                            <span class="font-mono font-bold text-[#5C3219]" x-text="selectedPayment?.transaction_ref || 'Chưa ghi nhận'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Thời gian tạo GD:</span>
                            <span x-text="selectedPayment?.created_at"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Thời gian xác nhận tiền:</span>
                            <span class="font-semibold text-emerald-700" x-text="selectedPayment?.paid_at || 'Chưa hoàn tất'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Người duyệt:</span>
                            <span class="font-semibold text-[#5C3219]" x-text="selectedPayment?.confirmed_by"></span>
                        </div>
                        <template x-if="selectedPayment?.note">
                            <div class="flex justify-between items-start pt-1 border-t border-dashed border-gray-200">
                                <span class="text-[#786B61]">Ghi chú / Lý do:</span>
                                <span class="font-semibold text-right max-w-[220px]" x-text="selectedPayment?.note"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Ảnh bill / chứng từ nếu có --}}
                <template x-if="selectedPayment?.proof_image_url">
                    <div class="space-y-2">
                        <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Ảnh Chụp Bill / Chứng Từ</h4>
                        <div class="p-2 bg-[#FAF8F5] border border-[#EBDDCD] rounded-2xl text-center">
                            <a :href="selectedPayment?.proof_image_url" target="_blank">
                                <img :src="selectedPayment?.proof_image_url" class="max-h-60 mx-auto rounded-xl object-contain shadow-xs hover:opacity-90 transition">
                            </a>
                            <p class="text-[10px] text-gray-500 mt-1">Nhấp để mở ảnh kích thước đầy đủ</p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-end gap-2">
                <button type="button" @click="closeDrawer()" 
                        class="px-4 py-2 rounded-xl border border-[#EBDDCD] bg-white text-[#5C3219] font-bold text-xs hover:bg-gray-50 transition">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DUYỆT HOÀN TIỀN & QUÉT MÃ VIETQR (QUY ĐỊNH 3) --}}
    <div x-show="qrRefundModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         @keydown.escape.window="qrRefundModalOpen = false">
        
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-[#F0E6D8]"
             @click.outside="qrRefundModalOpen = false">
            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h3 class="text-lg font-black text-[#2C1408]">Phê Duyệt &amp; Quét Mã Chuyển Tiền Hoàn</h3>
                <p class="text-xs text-[#786B61]">Quét mã VietQR trên App ngân hàng để chuyển tiền cho khách hàng, sau đó bấm Xác Nhận Đã Chuyển Tiền.</p>
            </div>

            {{-- QR Code Image Auto Generated --}}
            <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#EBDDCD] text-center mb-4">
                <template x-if="selectedRefundRequest?.viet_qr_url">
                    <div>
                        <img :src="selectedRefundRequest?.viet_qr_url" alt="VietQR Hoàn tiền" 
                             class="w-56 h-56 mx-auto rounded-xl border border-white shadow-md mb-2">
                        <div class="text-xs font-bold text-purple-700 font-mono" x-text="selectedRefundRequest?.amount"></div>
                    </div>
                </template>
                <div class="text-left text-xs space-y-1 mt-3 pt-3 border-t border-[#EBDDCD]">
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Ngân hàng:</span>
                        <span class="font-bold" x-text="selectedRefundRequest?.bank_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Số tài khoản:</span>
                        <span class="font-mono font-bold text-[#5C3219]" x-text="selectedRefundRequest?.bank_account"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Chủ tài khoản:</span>
                        <span class="font-bold uppercase" x-text="selectedRefundRequest?.account_holder"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Lý do hoàn:</span>
                        <span class="italic max-w-[200px] text-right" x-text="selectedRefundRequest?.reason"></span>
                    </div>
                </div>
            </div>

            <form method="POST" :action="'/admin/payments/refund-requests/' + (selectedRefundRequest?.id || '') + '/approve'">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">Ghi chú phê duyệt của Admin (Tùy chọn)</label>
                    <input type="text" name="admin_note" placeholder="Đã quét mã chuyển khoản hoàn tất..."
                           class="w-full py-2 px-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="qrRefundModalOpen = false" 
                            class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                        Đóng
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Đã Chuyển Tiền &amp; Duyệt</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TỪ CHỐI HOÀN TIỀN --}}
    <div x-show="rejectModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         @keydown.escape.window="rejectModalOpen = false">
        
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-[#F0E6D8]"
             @click.outside="rejectModalOpen = false">
            <div class="text-center mb-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <h3 class="text-base font-black text-[#2C1408]">Từ Chối Yêu Cầu Hoàn Tiền</h3>
                <p class="text-xs text-[#786B61]">Vui lòng nhập lý do từ chối để thông báo cho nhân viên vận hành.</p>
            </div>

            <form method="POST" :action="'/admin/payments/refund-requests/' + (selectedRefundRequest?.id || '') + '/reject'">
                @csrf
                <div class="mb-4">
                    <textarea name="admin_note" rows="3" required placeholder="Lý do từ chối: Hàng chưa về kho / Không đủ điều kiện hoàn trả..."
                              class="w-full p-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-rose-500 focus:ring-1 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="rejectModalOpen = false" 
                            class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-xs transition">
                        Từ Chối
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL XÁC NHẬN THU TIỀN THỦ CÔNG (ADMIN TOÀN QUYỀN - QUY ĐỊNH 2) --}}
    <div x-show="confirmModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         @keydown.escape.window="confirmModalOpen = false">
        
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-[#F0E6D8] text-center"
             @click.outside="confirmModalOpen = false">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 class="text-lg font-black text-[#2C1408] mb-1">Xác Nhận Đã Thu Tiền</h3>
            <p class="text-xs text-[#786B61] mb-4 leading-relaxed">
                Xác nhận đã nhận số tiền <strong class="text-[#2C1408]" x-text="paymentToAct?.amount"></strong> cho đơn hàng <strong class="text-[#E08A1E]" x-text="paymentToAct?.order_code"></strong>.
            </p>

            <form method="POST" :action="'/admin/payments/' + (paymentToAct?.id || '') + '/status'" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="PAID">

                <div class="text-left mb-3">
                    <label class="block text-[11px] font-bold text-[#5C3219] mb-1">Ghi chú xác nhận (Tùy chọn)</label>
                    <input type="text" name="note" placeholder="Admin duyệt trực tiếp..."
                           class="w-full py-2 px-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E]">
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="confirmModalOpen = false" 
                            class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                        Hủy Bỏ
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition">
                        Xác Nhận Thu
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL HOÀN TIỀN TRỰC TIẾP --}}
    <div x-show="refundModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         @keydown.escape.window="refundModalOpen = false">
        
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-[#F0E6D8] text-center"
             @click.outside="refundModalOpen = false">
            <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </div>
            <h3 class="text-lg font-black text-[#2C1408] mb-1">Xác Nhận Hoàn Tiền Trực Tiếp</h3>
            <p class="text-xs text-[#786B61] mb-4 leading-relaxed">
                Đánh dấu hoàn trả số tiền <strong class="text-[#2C1408]" x-text="paymentToAct?.amount"></strong> cho đơn <strong class="text-[#E08A1E]" x-text="paymentToAct?.order_code"></strong>.
            </p>

            <form method="POST" :action="'/admin/payments/' + (paymentToAct?.id || '') + '/status'">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="REFUNDED">
                <div class="flex gap-2">
                    <button type="button" @click="refundModalOpen = false" 
                            class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                        Hủy Bỏ
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-extrabold text-xs shadow-xs transition">
                        Xác Nhận Hoàn
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

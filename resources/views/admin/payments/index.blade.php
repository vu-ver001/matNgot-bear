@extends('layouts.admin-dashboard')

@section('page-title', 'Quản lý thanh toán & Đối soát dòng tiền')

@section('content')
<div class=" px-2 sm:px-4 lg:px-6 max-w-7xl mx-auto font-sans" x-data="{
    drawerOpen: false,
    selectedPayment: null,
    refundModalOpen: false,
    confirmModalOpen: false,
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
    }
}">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-[#8B7A6B] mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#5C3219] transition">Trang chủ</a>
                <span>/</span>
                <span class="text-[#2C1408]">Tài chính & Thanh toán</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#2C1408] tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#5C3219] to-[#8C5835] text-white flex items-center justify-center text-lg shadow-sm">
                    💳
                </span>
                <span>Quản Lý Thanh Toán &amp; Đối Soát</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#786B61] mt-1">
                Theo dõi sổ cái giao dịch, kiểm soát dòng tiền SePAY &amp; đối soát tiền COD theo thời gian thực.
            </p>
        </div>

        {{-- Top Actions: Export CSV & Quick Links --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.payments.export', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] font-bold text-xs shadow-xs hover:shadow-sm transition">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Xuất Báo Cáo CSV</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#5C3219] hover:bg-[#432310] text-white font-bold text-xs shadow-sm hover:shadow transition">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Xem Đơn Hàng</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-center justify-between text-xs sm:text-sm shadow-xs"
             x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">✓</span>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 text-lg leading-none p-1">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl flex items-center justify-between text-xs sm:text-sm shadow-xs"
             x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold">✕</span>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-rose-700 hover:text-rose-900 text-lg leading-none p-1">&times;</button>
        </div>
    @endif

    @if (session('info'))
        <div class="mb-5 p-3.5 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl flex items-center justify-between text-xs sm:text-sm shadow-xs"
             x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold">ℹ</span>
                <span class="font-semibold">{{ session('info') }}</span>
            </div>
            <button @click="show = false" class="text-amber-700 hover:text-amber-900 text-lg leading-none p-1">&times;</button>
        </div>
    @endif

    {{-- 1. KPI Financial Metric Cards (Chuẩn Shopee / Shopify Finances) --}}
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
                Chờ quét mã VietQR hoặc chờ giao COD
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
        <div class="bg-white rounded-2xl p-5 border border-[#EBDDCD] shadow-xs relative overflow-hidden group hover:border-rose-400 transition">
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

    {{-- Main Content Card --}}
    <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">

        {{-- 2. Quick Status Tabs (Shopee Style) --}}
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
                    <a href="{{ route('admin.payments.index', array_merge(request()->except('status', 'page'), $stKey ? ['status' => $stKey] : [])) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ $currentStatus === $stKey ? 'bg-[#5C3219] text-white shadow-xs' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:border-[#E08A1E] hover:bg-[#FFF9EE]' }}">
                        <span>{{ $stData['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === $stKey ? 'bg-white/20 text-white' : 'bg-[#FAF6EE] text-[#786B61]' }}">
                            {{ $stData['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- 3. Smart Search & Filters --}}
        <div class="p-5 border-b border-[#F0E6D8] bg-white">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
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
                    <a href="{{ route('admin.payments.index') }}"
                       class="py-2 px-3 bg-[#FAF8F5] border border-[#EBDDCD] hover:border-gray-400 text-[#786B61] text-xs font-semibold rounded-xl transition"
                       title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-arrow-rotate-right"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- 4. Transaction Ledger Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-xs text-[#2C1408]">
                <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 whitespace-nowrap">Mã GD / Đơn Hàng</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Phương Thức</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">Số Tiền</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Mã Tham Chiếu (NH / SePAY)</th>
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
                            {{-- Payment ID & Order Code --}}
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

                            {{-- Customer Info --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-[#2C1408] max-w-[170px] truncate" title="{{ $order?->recipient_name ?? $order?->customer?->full_name }}">
                                    {{ $order?->recipient_name ?? $order?->customer?->full_name ?? 'Khách vãng lai' }}
                                </div>
                                <div class="text-[11px] text-[#786B61] mt-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-phone text-[10px] text-gray-400"></i>
                                    <span>{{ $order?->recipient_phone ?? $order?->customer?->phone ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Method Badge --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold whitespace-nowrap {{ $methodBadge['class'] }}">
                                    <i class="fa-solid {{ $methodBadge['icon'] }}"></i>
                                    <span>{{ $methodBadge['label'] }}</span>
                                </span>
                            </td>

                            {{-- Amount --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="font-black text-sm text-[#2C1408]">
                                    {{ number_format($item->amount, 0, ',', '.') }}đ
                                </div>
                            </td>

                            {{-- Reference Code (SePAY / Bank) --}}
                            <td class="py-3.5 px-4 font-mono text-[11px] whitespace-nowrap">
                                @if ($item->transaction_ref)
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-[#FAF6EE] border border-[#EBDDCD] text-[#5C3219] font-bold whitespace-nowrap">
                                        <span>{{ $item->transaction_ref }}</span>
                                        <button type="button" @click="navigator.clipboard.writeText('{{ $item->transaction_ref }}'); alert('Đã sao chép mã giao dịch!')" 
                                                class="text-gray-400 hover:text-[#E08A1E] transition" title="Sao chép">
                                            <i class="fa-regular fa-copy text-[10px]"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[11px] font-bold whitespace-nowrap shrink-0 {{ $statusBadge['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ str_replace('dot-', 'bg-', $statusBadge['class']) }}"></span>
                                    <span class="whitespace-nowrap">{{ $statusBadge['label'] }}</span>
                                </span>
                            </td>

                            {{-- Datetime --}}
                            <td class="py-3.5 px-4 text-[11px] text-[#786B61] whitespace-nowrap">
                                <div>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '—' }}</div>
                                @if ($item->paid_at)
                                    <div class="text-[10px] text-emerald-600 font-medium whitespace-nowrap">Trả lúc: {{ $item->paid_at->format('H:i d/m') }}</div>
                                @endif
                            </td>

                            {{-- Quick Actions --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Open Detail Drawer --}}
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

                                    {{-- Manual Confirm for Pending COD --}}
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
                                                title="Hoàn tiền giao dịch">
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

    {{-- 5. Slide-Over Detail Drawer (Shopify Style) --}}
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
                    <span class="text-[11px] font-bold text-[#8B7A6B] uppercase tracking-wider">Chi Tiết Sổ Cái Thanh Toán</span>
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
                    <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Dữ Liệu Đối Soát Ngân Hàng</h4>
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
                    </div>
                </div>

                {{-- Raw Gateway Payload if available --}}
                <template x-if="selectedPayment?.gateway_response">
                    <div class="space-y-1.5">
                        <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Dữ Liệu Raw Cổng Thanh Toán</h4>
                        <pre class="p-3 rounded-xl bg-slate-900 text-emerald-400 font-mono text-[10px] overflow-x-auto max-h-48 leading-relaxed" 
                             x-text="typeof selectedPayment?.gateway_response === 'object' ? JSON.stringify(selectedPayment.gateway_response, null, 2) : selectedPayment.gateway_response"></pre>
                    </div>
                </template>
            </div>

            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-end gap-2">
                <button type="button" @click="closeDrawer()" 
                        class="px-4 py-2 rounded-xl border border-[#EBDDCD] bg-white text-[#5C3219] font-bold text-xs hover:bg-gray-50 transition">
                    Đóng Drawer
                </button>
                <template x-if="selectedPayment?.order_url">
                    <a :href="selectedPayment?.order_url" 
                       class="px-4 py-2 rounded-xl bg-[#5C3219] text-white font-bold text-xs hover:bg-[#432310] transition flex items-center gap-1.5">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Chi Tiết Đơn Hàng</span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    {{-- 6. Confirm Payment Modal --}}
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
                Bạn có chắc chắn muốn xác nhận đã nhận số tiền <strong class="text-[#2C1408]" x-text="paymentToAct?.amount"></strong> cho đơn hàng <strong class="text-[#E08A1E]" x-text="paymentToAct?.order_code"></strong>?
            </p>

            <form method="POST" :action="'/admin/payments/' + (paymentToAct?.id || '') + '/status'" class="flex gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="PAID">
                <button type="button" @click="confirmModalOpen = false" 
                        class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                    Hủy Bỏ
                </button>
                <button type="submit" 
                        class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition">
                    Xác Nhận Thu
                </button>
            </form>
        </div>
    </div>

    {{-- 7. Refund Modal --}}
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
            <h3 class="text-lg font-black text-[#2C1408] mb-1">Xác Nhận Hoàn Tiền</h3>
            <p class="text-xs text-[#786B61] mb-4 leading-relaxed">
                Đánh dấu hoàn trả số tiền <strong class="text-[#2C1408]" x-text="paymentToAct?.amount"></strong> cho đơn <strong class="text-[#E08A1E]" x-text="paymentToAct?.order_code"></strong>. Trạng thái đơn sẽ được cập nhật sang ĐÃ HOÀN TIỀN.
            </p>

            <form method="POST" :action="'/admin/payments/' + (paymentToAct?.id || '') + '/status'" class="flex gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="REFUNDED">
                <button type="button" @click="refundModalOpen = false" 
                        class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                    Hủy Bỏ
                </button>
                <button type="submit" 
                        class="flex-1 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-extrabold text-xs shadow-xs transition">
                    Xác Nhận Hoàn
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

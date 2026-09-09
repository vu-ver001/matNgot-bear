@extends('layouts.staff-dashboard')

@section('page-title', 'Xử lý thanh toán & Đối soát COD')

@section('content')
<div class="px-2 sm:px-4 lg:px-6 max-w-7xl mx-auto font-sans" x-data="{
    activeTab: '{{ $activeTab ?? 'transactions' }}',
    drawerOpen: false,
    selectedPayment: null,
    manualModalOpen: false,
    refundRequestModalOpen: false,
    paymentToAct: null,
    previewImage: null,

    openDrawer(payment) {
        this.selectedPayment = payment;
        this.drawerOpen = true;
    },
    closeDrawer() {
        this.drawerOpen = false;
    },
    openManualConfirm(payment) {
        this.paymentToAct = payment;
        this.previewImage = null;
        this.manualModalOpen = true;
    },
    openRefundRequest(payment) {
        this.paymentToAct = payment;
        this.refundRequestModalOpen = true;
    },
    handleImagePreview(e) {
        const file = e.target.files[0];
        if (file) {
            this.previewImage = URL.createObjectURL(file);
        }
    }
}">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[#2C1408] tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#5C3219] to-[#8C5835] text-white flex items-center justify-center text-lg shadow-sm">
                    🧾
                </span>
                <span>Xử Lý Thanh Toán &amp; Đối Soát Đơn</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#786B61] mt-1">
                Xử lý giao dịch hôm nay, xác nhận thủ công (ảnh bill bắt buộc), tạo yêu cầu hoàn tiền &amp; tải bảng kê đối soát COD.
            </p>
        </div>

        {{-- Top Actions --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('staff.payments.codExport', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] font-bold text-xs shadow-xs hover:shadow-sm transition">
                <i class="fa-solid fa-file-csv text-blue-600"></i>
                <span>Tải Bảng Kê COD</span>
            </a>
            <a href="{{ route('staff.orders.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#5C3219] hover:bg-[#432310] text-white font-bold text-xs shadow-sm hover:shadow transition">
                <i class="fa-solid fa-boxes-packing"></i>
                <span>Xử Lý Đơn Hàng</span>
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

    @if ($errors->any())
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs sm:text-sm shadow-xs">
            <div class="font-bold mb-1 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Yêu cầu chưa hợp lệ:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-rose-700">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. Daily Operational Counters (Quy định 6: Chỉ xem chỉ số vận hành hôm nay, KHÔNG thấy lợi nhuận) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-[#EBDDCD] shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#786B61]">Đơn Trong Ngày</span>
            <div class="text-xl sm:text-2xl font-black text-[#5C3219] mt-1">
                {{ $operationalStats['today_total'] }}
            </div>
            <p class="text-[10px] text-[#786B61] mt-1">Hôm nay ({{ date('d/m/Y') }})</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-[#EBDDCD] shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#786B61]">Chờ Thanh Toán</span>
            <div class="text-xl sm:text-2xl font-black text-[#E08A1E] mt-1">
                {{ $operationalStats['today_pending'] }}
            </div>
            <p class="text-[10px] text-[#786B61] mt-1">Cần đối soát / xác nhận</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-[#EBDDCD] shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#786B61]">Đã Thanh Toán</span>
            <div class="text-xl sm:text-2xl font-black text-emerald-600 mt-1">
                {{ $operationalStats['today_paid'] }}
            </div>
            <p class="text-[10px] text-[#786B61] mt-1">Đã khớp tiền hôm nay</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-[#EBDDCD] shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#786B61]">COD Chưa Đối Soát</span>
            <div class="text-xl sm:text-2xl font-black text-blue-600 mt-1">
                {{ $operationalStats['cod_unreconciled'] }}
            </div>
            <p class="text-[10px] text-[#786B61] mt-1">Cần đối chiếu với shipper</p>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-[#EBDDCD] shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#786B61]">Yêu Cầu Hoàn Tiền</span>
            <div class="text-xl sm:text-2xl font-black text-purple-600 mt-1">
                {{ $operationalStats['my_pending_refunds'] }}
            </div>
            <p class="text-[10px] text-[#786B61] mt-1">Đang chờ Admin phê duyệt</p>
        </div>
    </div>

    {{-- Main Navigation Tabs --}}
    <div class="flex items-center gap-2 mb-6 border-b border-[#EBDDCD] pb-2 overflow-x-auto">
        <a href="{{ route('staff.payments.index', ['tab' => 'transactions']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition {{ ($activeTab ?? 'transactions') === 'transactions' ? 'bg-[#5C3219] text-white shadow-sm' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:bg-[#FFF9EE]' }}">
            <i class="fa-solid fa-calendar-day"></i>
            <span>Giao Dịch Hôm Nay</span>
            <span class="px-2 py-0.5 rounded-md text-[10px] {{ ($activeTab ?? 'transactions') === 'transactions' ? 'bg-white/20 text-white' : 'bg-[#FAF6EE] text-[#786B61]' }}">
                {{ $counts['ALL'] }}
            </span>
        </a>

        <a href="{{ route('staff.payments.index', ['tab' => 'cod']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition {{ ($activeTab ?? 'transactions') === 'cod' ? 'bg-[#5C3219] text-white shadow-sm' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:bg-[#FFF9EE]' }}">
            <i class="fa-solid fa-truck-fast text-blue-500"></i>
            <span>Đối Soát Đơn COD &amp; Bưu Cục</span>
        </a>

        <a href="{{ route('staff.payments.index', ['tab' => 'refund_requests']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-black text-xs transition {{ ($activeTab ?? 'transactions') === 'refund_requests' ? 'bg-[#5C3219] text-white shadow-sm' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:bg-[#FFF9EE]' }}">
            <i class="fa-solid fa-arrow-rotate-left text-purple-500"></i>
            <span>Yêu cầu hoàn tiền</span>
            @if ($operationalStats['my_pending_refunds'] > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                    {{ $operationalStats['my_pending_refunds'] }} chờ duyệt
                </span>
            @endif
        </a>
    </div>

    {{-- TAB 1: GIAO DỊCH TRONG NGÀY --}}
    @if (($activeTab ?? 'transactions') === 'transactions')
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            {{-- Status Tabs --}}
            <div class="border-b border-[#F0E6D8] bg-[#FAF8F5] px-5 pt-3 overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max pb-3">
                    @php
                        $statusTabs = [
                            '' => ['label' => 'Tất cả đơn hôm nay', 'count' => $counts['ALL']],
                            'PENDING' => ['label' => 'Chờ thanh toán', 'count' => $counts['PENDING']],
                            'PAID' => ['label' => 'Đã thanh toán', 'count' => $counts['PAID']],
                            'FAILED' => ['label' => 'Thất bại', 'count' => $counts['FAILED']],
                            'REFUNDED' => ['label' => 'Đã hoàn tiền', 'count' => $counts['REFUNDED']],
                        ];
                        $currentStatus = request('status', '');
                    @endphp

                    @foreach ($statusTabs as $stKey => $stData)
                        <a href="{{ route('staff.payments.index', array_merge(request()->except('status', 'p_page'), $stKey ? ['status' => $stKey] : [])) }}"
                           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ $currentStatus === $stKey ? 'bg-[#5C3219] text-white shadow-xs' : 'bg-white border border-[#EBDDCD] text-[#5C3219] hover:border-[#E08A1E] hover:bg-[#FFF9EE]' }}">
                            <span>{{ $stData['label'] }}</span>
                            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === $stKey ? 'bg-white/20 text-white' : 'bg-[#FAF6EE] text-[#786B61]' }}">
                                {{ $stData['count'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Search & Method Filter (Quy định 1: Toàn quyền tra cứu & lọc) --}}
            <div class="p-4 sm:p-5 border-b border-[#F0E6D8] bg-white">
                <form method="GET" action="{{ route('staff.payments.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="transactions">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    {{-- 1. Ô tra cứu --}}
                    <div class="flex items-center gap-2 flex-1 min-w-[260px]">
                        <label class="text-xs font-bold text-[#5C3219] whitespace-nowrap shrink-0">Tra cứu:</label>
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Mã đơn / Mã GD / Tên khách / SĐT..."
                                   class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-[#EBDDCD] bg-[#FAF8F5] focus:bg-white focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E] text-[#2C1408] transition">
                        </div>
                    </div>

                    {{-- 2. Bộ lọc phương thức --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                        <label class="text-xs font-bold text-[#5C3219] whitespace-nowrap shrink-0">Phương thức:</label>
                        <select name="method" class="py-2 px-3 text-xs rounded-xl border border-[#EBDDCD] bg-[#FAF8F5] focus:bg-white focus:border-[#E08A1E] text-[#2C1408] transition cursor-pointer">
                            <option value="">-- Tất cả phương thức --</option>
                            <option value="BANK_TRANSFER" @selected(request('method') === 'BANK_TRANSFER')>Chuyển khoản (VietQR / SePAY)</option>
                            <option value="COD" @selected(request('method') === 'COD')>Tiền mặt khi nhận hàng (COD)</option>
                            <option value="E_WALLET" @selected(request('method') === 'E_WALLET')>Ví điện tử</option>
                            <option value="CARD" @selected(request('method') === 'CARD')>Thẻ ATM / Quốc tế</option>
                        </select>
                    </div>

                    {{-- 3. Nút hành động --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit"
                                class="py-2 px-4 bg-[#E08A1E] hover:bg-[#C2751D] text-white text-xs font-extrabold rounded-xl shadow-xs hover:shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            <span>Tra Cứu</span>
                        </button>
                        <a href="{{ route('staff.payments.index', ['tab' => 'transactions']) }}"
                           class="py-2 px-3 bg-[#FAF8F5] hover:bg-[#F2EAE0] border border-[#EBDDCD] text-[#786B61] hover:text-[#5C3219] text-xs font-semibold rounded-xl transition cursor-pointer"
                           title="Đặt lại">
                            <i class="fa-solid fa-arrow-rotate-right"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-xs text-[#2C1408]">
                    <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã GD / Đơn Hàng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Phương Thức</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Số Tiền Đơn</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã Tham Chiếu / Chứng Từ</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Trạng Thái</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Thời Gian</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Nghiệp Vụ Nhân Viên</th>
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

                            <tr class="hover:bg-[#FFFDF9] transition">
                                <td class="py-3.5 px-4 font-semibold whitespace-nowrap">
                                    <div class="font-bold text-[#5C3219]">#PAY-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @if ($order)
                                        <a href="{{ route('staff.orders.show', $order->id) }}" 
                                           class="inline-flex items-center gap-1 text-[11px] font-bold text-[#E08A1E] hover:underline mt-0.5">
                                            <span>Đơn: {{ $order->order_code }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#2C1408]">{{ $order?->recipient_name ?? $order?->customer?->full_name ?? 'Khách vãng lai' }}</div>
                                    <div class="text-[11px] text-[#786B61]">{{ $order?->recipient_phone ?? $order?->customer?->phone ?? '—' }}</div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold {{ $methodBadge['class'] }}">
                                        <i class="fa-solid {{ $methodBadge['icon'] }}"></i>
                                        <span>{{ $methodBadge['label'] }}</span>
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-black text-sm text-[#2C1408]">
                                        {{ number_format($item->amount, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-[11px] whitespace-nowrap">
                                    @if ($item->transaction_ref)
                                        <span class="px-2 py-0.5 rounded bg-[#FAF6EE] font-mono font-bold text-[#5C3219] border border-[#EBDDCD]">
                                            {{ $item->transaction_ref }}
                                        </span>
                                    @elseif ($item->proof_image)
                                        <a href="{{ $item->proof_image_url }}" target="_blank" 
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            <i class="fa-solid fa-image"></i>
                                            <span>Xem ảnh bill</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif

                                    @if ($item->note)
                                        <div class="text-[10px] text-gray-500 italic max-w-[140px] truncate mt-0.5" title="{{ $item->note }}">
                                            Ghi chú: {{ $item->note }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[11px] font-bold {{ $statusBadge['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ str_replace('dot-', 'bg-', $statusBadge['class']) }}"></span>
                                        <span>{{ $statusBadge['label'] }}</span>
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-[11px] text-[#786B61] whitespace-nowrap">
                                    <div>{{ $item->created_at ? $item->created_at->format('H:i d/m') : '—' }}</div>
                                    @if ($item->paid_at)
                                        <div class="text-[10px] text-emerald-600 font-medium">Trả lúc: {{ $item->paid_at->format('H:i') }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Xem chi tiết drawer --}}
                                        <button type="button" 
                                                @click="openDrawer({{ json_encode([
                                                    'id' => $item->id,
                                                    'order_code' => $order?->order_code,
                                                    'order_url' => $order ? route('staff.orders.show', $order->id) : null,
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
                                                    'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '—',
                                                    'paid_at' => $item->paid_at ? $item->paid_at->format('d/m/Y H:i:s') : '—',
                                                    'confirmed_by' => $item->confirmedByUser?->full_name ?? ($item->status === 'PAID' ? 'Hệ thống tự động' : '—'),
                                                ]) }})"
                                                class="w-8 h-8 rounded-lg bg-[#FAF8F5] border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] flex items-center justify-center text-xs transition"
                                                title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        {{-- Xác nhận thanh toán thủ công (Quy định 2: Bắt buộc nhập lý do/ảnh bill) --}}
                                        @if ($item->status === 'PENDING')
                                            <button type="button" 
                                                    @click="openManualConfirm({{ json_encode([
                                                        'id' => $item->id,
                                                        'amount' => number_format($item->amount, 0, ',', '.') . 'đ',
                                                        'order_code' => $order?->order_code,
                                                        'recipient_name' => $order?->recipient_name ?? $order?->customer?->full_name,
                                                    ]) }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition flex items-center gap-1"
                                                    title="Xác nhận thanh toán kèm ảnh bill">
                                                <i class="fa-solid fa-receipt"></i>
                                                <span>Xác nhận (+Bill)</span>
                                            </button>
                                        @endif

                                        {{-- Yêu cầu hoàn tiền (Quy định 3: Nhân viên tạo yêu cầu hoàn tiền) --}}
                                        @if ($item->status === 'PAID')
                                            <button type="button" 
                                                    @click="openRefundRequest({{ json_encode([
                                                        'id' => $item->id,
                                                        'amount' => (int) $item->amount,
                                                        'formatted_amount' => number_format($item->amount, 0, ',', '.') . 'đ',
                                                        'order_code' => $order?->order_code,
                                                        'customer_name' => $order?->recipient_name ?? $order?->customer?->full_name,
                                                        'bank_name' => $order?->refund_bank_name,
                                                        'bank_account' => $order?->refund_bank_account,
                                                        'account_holder' => $order?->refund_account_holder,
                                                    ]) }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-700 font-bold text-xs transition flex items-center gap-1"
                                                    title="Gửi yêu cầu hoàn tiền lên Admin">
                                                <i class="fa-solid fa-arrow-rotate-left"></i>
                                                <span>Yêu cầu hoàn tiền</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-[#786B61]">
                                    <div class="w-14 h-14 rounded-full bg-[#FAF6EE] flex items-center justify-center text-2xl mx-auto mb-2">
                                        ✨
                                    </div>
                                    <div class="font-bold text-sm text-[#2C1408]">Không có giao dịch nào hôm nay</div>
                                    <p class="text-xs text-[#786B61] mt-1">Đơn hàng mới tạo trong ngày sẽ tự động xuất hiện tại đây.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5]">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 2: ĐỐI SOÁT ĐƠN COD (QUY ĐỊNH 4: TẢI BẢNG KÊ & ĐÁNH DẤU ĐƠN) --}}
    @if (($activeTab ?? 'transactions') === 'cod')
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden" x-data="{ selectedCod: [] }">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2C1408]">Đối Soát Đơn COD Với Đơn Vị Vận Chuyển</h3>
                        <p class="text-xs text-[#786B61]">Nhân viên tải bảng kê đối chiếu với bưu tá và đánh dấu các đơn COD đã thu tiền.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('staff.payments.codExport', request()->query()) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white border border-[#EBDDCD] hover:border-blue-600 text-blue-700 font-bold text-xs shadow-2xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-download"></i>
                        <span>Tải Bảng Kê Đối Soát (CSV)</span>
                    </a>

                    <form method="POST" action="{{ route('staff.payments.bulkReconcileCod') }}" class="inline" x-show="selectedCod.length > 0">
                        @csrf
                        <template x-for="id in selectedCod" :key="id">
                            <input type="hidden" name="payment_ids[]" :value="id">
                        </template>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-2xs transition flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i>
                            <span>Đánh Dấu Đã Đối Soát (<span x-text="selectedCod.length"></span>)</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Filter COD --}}
            <div class="p-4 border-b border-[#F0E6D8] bg-white flex items-center gap-2 overflow-x-auto text-xs">
                <a href="{{ route('staff.payments.index', ['tab' => 'cod']) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition {{ !request('cod_status') ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                    Tất cả đơn COD
                </a>
                <a href="{{ route('staff.payments.index', ['tab' => 'cod', 'cod_status' => 'UNRECONCILED']) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition {{ request('cod_status') === 'UNRECONCILED' ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                    Chưa đối soát
                </a>
                <a href="{{ route('staff.payments.index', ['tab' => 'cod', 'cod_status' => 'RECONCILED']) }}"
                   class="px-3 py-1.5 rounded-xl font-bold transition {{ request('cod_status') === 'RECONCILED' ? 'bg-[#5C3219] text-white' : 'bg-white border border-[#EBDDCD] text-[#5C3219]' }}">
                    Đã đối soát
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-xs text-[#2C1408]">
                    <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 w-10 text-center">
                                <input type="checkbox" @click="selectedCod = selectedCod.length ? [] : {{ json_encode($codPayments->whereNull('cod_reconciled_at')->pluck('id')) }}" 
                                       class="rounded text-[#E08A1E] focus:ring-[#E08A1E]">
                            </th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã Đơn / Mã GD</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng &amp; Địa Chỉ</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Tiền Thu Hộ COD</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Trạng Thái Giao Hàng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Trạng Thái Đối Soát</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0E6D8]">
                        @forelse ($codPayments as $cp)
                            @php $cpOrder = $cp->order; @endphp
                            <tr class="hover:bg-[#FFFDF9] transition">
                                <td class="py-3.5 px-4 text-center">
                                    @if (!$cp->cod_reconciled_at)
                                        <input type="checkbox" value="{{ $cp->id }}" x-model="selectedCod" class="rounded text-[#E08A1E] focus:ring-[#E08A1E]">
                                    @else
                                        <i class="fa-solid fa-check text-emerald-600"></i>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-[#5C3219]">#PAY-{{ str_pad($cp->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @if ($cpOrder)
                                        <a href="{{ route('staff.orders.show', $cpOrder->id) }}" class="text-[11px] font-bold text-[#E08A1E] hover:underline">
                                            Đơn: {{ $cpOrder->order_code }}
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#2C1408]">{{ $cpOrder?->recipient_name ?? '—' }} ({{ $cpOrder?->recipient_phone ?? '—' }})</div>
                                    <div class="text-[11px] text-[#786B61] max-w-[240px] truncate" title="{{ $cpOrder?->recipient_address }}">{{ $cpOrder?->recipient_address ?? '—' }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-black text-sm text-[#2C1408]">
                                        {{ number_format($cp->amount, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#FAF6EE] text-[#5C3219] border border-[#EBDDCD]">
                                        {{ $cpOrder?->order_status ?? '—' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($cp->cod_reconciled_at)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Đã đối soát ({{ $cp->cod_reconciled_at->format('d/m H:i') }})</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-clock"></i>
                                            <span>Chờ đối soát bưu tá</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if (!$cp->cod_reconciled_at)
                                        <form method="POST" action="{{ route('staff.payments.reconcileCod', $cp->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Đánh Dấu Đã Đối Soát</span>
                                            </button>
                                        </form>
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

    {{-- TAB 3: YÊU CẦU HOÀN TIỀN (QUY ĐỊNH 3) --}}
    @if (($activeTab ?? 'transactions') === 'refund_requests')
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2C1408]">Theo Dõi Yêu Cầu Hoàn Tiền</h3>
                        <p class="text-xs text-[#786B61]">Các yêu cầu hoàn tiền do bạn gửi lên để Admin phê duyệt &amp; quét mã chuyển tiền hoàn cho khách.</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-xs text-[#2C1408]">
                    <thead class="bg-[#FAF8F5] border-b border-[#F0E6D8] text-[11px] font-bold text-[#786B61] uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 whitespace-nowrap">Mã Yêu Cầu / Đơn</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Khách Hàng Nhận Hoàn</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap">Số Tiền Hoàn</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Lý Do Đề Xuất</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Thời Gian Gửi</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Trạng Thái Duyệt</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Phản Hồi Từ Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0E6D8]">
                        @forelse ($refundRequests as $rf)
                            @php
                                $rfStatusBadge = match ($rf->status) {
                                    'APPROVED' => ['label' => 'Admin đã duyệt & hoàn tiền', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                    'PENDING' => ['label' => 'Đang chờ Admin duyệt', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse'],
                                    'REJECTED' => ['label' => 'Admin từ chối', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                    default => ['label' => $rf->status, 'class' => 'bg-gray-50 text-gray-700 border-gray-200'],
                                };
                            @endphp
                            <tr class="hover:bg-[#FFFDF9] transition">
                                <td class="py-3.5 px-4 whitespace-nowrap font-semibold">
                                    <div class="font-bold text-[#5C3219]">#RF-{{ str_pad($rf->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    @if ($rf->order)
                                        <a href="{{ route('staff.orders.show', $rf->order->id) }}" class="text-[11px] font-bold text-[#E08A1E] hover:underline">
                                            Đơn: {{ $rf->order->order_code }}
                                        </a>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-[#2C1408]">{{ $rf->order?->recipient_name ?? $rf->order?->customer?->full_name ?? '—' }}</div>
                                    <div class="text-[11px] text-[#786B61]">{{ $rf->bank_name }} - {{ $rf->bank_account }} ({{ $rf->account_holder }})</div>
                                </td>

                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-black text-sm text-purple-700">
                                        {{ number_format($rf->amount, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 max-w-[200px]">
                                    <div class="text-xs text-[#2C1408] truncate" title="{{ $rf->reason }}">{{ $rf->reason }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-[11px] text-[#786B61] whitespace-nowrap">
                                    {{ $rf->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-bold {{ $rfStatusBadge['class'] }}">
                                        {{ $rfStatusBadge['label'] }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-xs">
                                    @if ($rf->admin_note)
                                        <span class="font-semibold text-[#5C3219]">{{ $rf->admin_note }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-[#786B61]">
                                    <div class="font-bold text-sm text-[#2C1408]">Bạn chưa gửi yêu cầu hoàn tiền nào</div>
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

    {{-- MODAL XÁC NHẬN THANH TOÁN THỦ CÔNG (QUY ĐỊNH 2: BẮT BUỘC NHẬP LÝ DO & ẢNH BILL) --}}
    <div x-show="manualModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         @keydown.escape.window="manualModalOpen = false">
        
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-[#F0E6D8]"
             @click.outside="manualModalOpen = false">
            
            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <h3 class="text-lg font-black text-[#2C1408]">Xác Nhận Thanh Toán Thủ Công</h3>
                <p class="text-xs text-[#786B61]">
                    Quy định: Nhân viên <strong class="text-rose-600">bắt buộc nhập lý do</strong> và <strong class="text-rose-600">tải ảnh chụp bill chuyển khoản / chứng từ</strong>.
                </p>
            </div>

            <form method="POST" :action="'/staff/payments/' + (paymentToAct?.id || '') + '/manual-confirm'" enctype="multipart/form-data">
                @csrf
                
                {{-- Order Summary --}}
                <div class="p-3 bg-[#FAF8F5] rounded-xl border border-[#EBDDCD] mb-4 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Đơn hàng:</span>
                        <span class="font-bold text-[#E08A1E]" x-text="paymentToAct?.order_code"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Khách hàng:</span>
                        <span class="font-bold text-[#2C1408]" x-text="paymentToAct?.recipient_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#786B61]">Số tiền:</span>
                        <span class="font-black text-emerald-600 text-sm" x-text="paymentToAct?.amount"></span>
                    </div>
                </div>

                {{-- Reason (Bắt buộc) --}}
                <div class="mb-3.5">
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">
                        Lý do xác nhận thanh toán <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" rows="2.5" required
                              placeholder="Ví dụ: Khách chuyển khoản đúng cú pháp qua MB Bank, đối soát qua ảnh bill..."
                              class="w-full p-2.5 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]"></textarea>
                </div>

                {{-- Proof Image (Bắt buộc) --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">
                        Ảnh chụp bill chuyển khoản / Chứng từ <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="proof_image" accept="image/*" required @change="handleImagePreview($event)"
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FAF6EE] file:text-[#5C3219] hover:file:bg-[#FFF4DE] cursor-pointer">
                    
                    {{-- Preview image --}}
                    <template x-if="previewImage">
                        <div class="mt-2 text-center p-2 bg-[#FAF8F5] border border-[#EBDDCD] rounded-xl">
                            <img :src="previewImage" class="max-h-36 mx-auto rounded-lg object-contain">
                            <span class="text-[10px] text-emerald-600 font-bold block mt-1">✓ Đã chọn ảnh chứng từ</span>
                        </div>
                    </template>
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="manualModalOpen = false" 
                            class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                        Hủy Bỏ
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Xác Nhận Thanh Toán</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TẠO YÊU CẦU HOÀN TIỀN (QUY ĐỊNH 3) --}}
    <div x-show="refundRequestModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         style="display: none;"
         @keydown.escape.window="refundRequestModalOpen = false">
        
        <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-[#F0E6D8]"
             @click.outside="refundRequestModalOpen = false">
            
            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </div>
                <h3 class="text-lg font-black text-[#2C1408]">Tạo Yêu Cầu Hoàn Tiền</h3>
                <p class="text-xs text-[#786B61]">
                    Gửi yêu cầu hoàn tiền cho đơn <strong class="text-[#E08A1E]" x-text="paymentToAct?.order_code"></strong> tới Admin phê duyệt &amp; chuyển khoản.
                </p>
            </div>

            <form method="POST" :action="'/staff/payments/' + (paymentToAct?.id || '') + '/refund-request'" enctype="multipart/form-data">
                @csrf
                
                {{-- Amount to refund --}}
                <div class="mb-3">
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">
                        Số tiền đề xuất hoàn (VND) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="amount" :value="paymentToAct?.amount" required min="1000"
                           class="w-full py-2 px-3 text-xs font-bold text-purple-700 rounded-xl border border-[#EBDDCD] focus:border-purple-600">
                </div>

                {{-- Bank details --}}
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div>
                        <label class="block text-[11px] font-bold text-[#5C3219] mb-1">Ngân hàng nhận</label>
                        <input type="text" name="bank_name" :value="paymentToAct?.bank_name || 'MB'" placeholder="MB, VCB, TCB..."
                               class="w-full py-1.5 px-2.5 text-xs rounded-xl border border-[#EBDDCD]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#5C3219] mb-1">Số tài khoản (STK)</label>
                        <input type="text" name="bank_account" :value="paymentToAct?.bank_account" placeholder="0123456789..."
                               class="w-full py-1.5 px-2.5 text-xs font-mono font-bold rounded-xl border border-[#EBDDCD]">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-[11px] font-bold text-[#5C3219] mb-1">Tên chủ tài khoản nhận</label>
                    <input type="text" name="account_holder" :value="paymentToAct?.account_holder || paymentToAct?.customer_name" placeholder="NGUYEN VAN A"
                           class="w-full py-1.5 px-2.5 text-xs uppercase font-bold rounded-xl border border-[#EBDDCD]">
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">
                        Lý do hoàn tiền <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" rows="2" required placeholder="Lý do khách trả hàng, gửi thiếu hàng, lỗi sản phẩm..."
                              class="w-full p-2.5 text-xs rounded-xl border border-[#EBDDCD] focus:border-purple-600"></textarea>
                </div>

                {{-- Proof image (tùy chọn) --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">Ảnh bằng chứng hàng lỗi / đối chiếu (Tùy chọn)</label>
                    <input type="file" name="proof_image" accept="image/*"
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FAF6EE] file:text-[#5C3219]">
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="refundRequestModalOpen = false" 
                            class="flex-1 py-2.5 rounded-xl border border-[#EBDDCD] font-bold text-xs text-[#5C3219] hover:bg-gray-50 transition">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Gửi Lên Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
            
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-[#8B7A6B] uppercase tracking-wider">Chi Tiết Giao Dịch</span>
                    <h3 class="text-lg font-black text-[#2C1408]" x-text="'#PAY-' + (selectedPayment ? selectedPayment.id.toString().padStart(5, '0') : '')"></h3>
                </div>
                <button type="button" @click="closeDrawer()" 
                        class="w-8 h-8 rounded-full bg-white border border-[#EBDDCD] hover:bg-gray-100 flex items-center justify-center text-[#786B61] transition">
                    &times;
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-5 text-xs text-[#2C1408] flex-1">
                <div class="p-4 rounded-2xl bg-gradient-to-br from-[#FFF9EE] to-[#FFF4DE] border border-[#FDE68A] flex items-center justify-between">
                    <div>
                        <div class="text-[11px] text-[#786B61] font-semibold">Số tiền đơn hàng</div>
                        <div class="text-2xl font-black text-[#E08A1E] mt-0.5" x-text="selectedPayment?.amount"></div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white border border-[#E08A1E] text-[#C2751D]"
                          x-text="selectedPayment?.status_label">
                    </span>
                </div>

                {{-- Order Meta --}}
                <div class="space-y-2.5">
                    <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Thông Tin Đơn Hàng</h4>
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
                    <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Chi Tiết Thanh Toán</h4>
                    <div class="p-3.5 rounded-xl border border-[#F0E6D8] space-y-2">
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Phương thức:</span>
                            <span class="font-bold" x-text="selectedPayment?.method_label"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Mã tham chiếu:</span>
                            <span class="font-mono font-bold text-[#5C3219]" x-text="selectedPayment?.transaction_ref || 'Chưa có'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Thời gian tạo:</span>
                            <span x-text="selectedPayment?.created_at"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#786B61]">Người xác nhận:</span>
                            <span class="font-semibold text-[#5C3219]" x-text="selectedPayment?.confirmed_by"></span>
                        </div>
                        <template x-if="selectedPayment?.note">
                            <div class="flex justify-between items-start pt-1 border-t border-dashed border-gray-200">
                                <span class="text-[#786B61]">Lý do / Ghi chú:</span>
                                <span class="font-semibold text-right max-w-[220px]" x-text="selectedPayment?.note"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Bill Image --}}
                <template x-if="selectedPayment?.proof_image_url">
                    <div class="space-y-2">
                        <h4 class="font-black text-xs uppercase tracking-wider text-[#786B61]">Ảnh Chứng Từ / Bill Đã Xác Nhận</h4>
                        <div class="p-2 bg-[#FAF8F5] border border-[#EBDDCD] rounded-2xl text-center">
                            <a :href="selectedPayment?.proof_image_url" target="_blank">
                                <img :src="selectedPayment?.proof_image_url" class="max-h-56 mx-auto rounded-xl object-contain shadow-xs">
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-end">
                <button type="button" @click="closeDrawer()" 
                        class="px-4 py-2 rounded-xl border border-[#EBDDCD] bg-white text-[#5C3219] font-bold text-xs hover:bg-gray-50 transition">
                    Đóng
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

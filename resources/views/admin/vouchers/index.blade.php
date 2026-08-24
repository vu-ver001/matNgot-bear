<x-app-layout>
    <div class="py-8 bg-[#FAF6EE] min-h-screen font-sans" x-data="vouchersList()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Header Title Section --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#5C3219] flex items-center justify-center text-[#F6D89B] shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] tracking-tight uppercase">
                            QUẢN LÝ VOUCHER
                        </h1>
                        <p class="text-xs sm:text-sm font-medium text-[#786B61] mt-0.5">
                            Quản lý, tạo mới và cấu hình các chương trình ưu đãi, khuyến mãi cho khách hàng
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    @if($isTrashed)
                        <a href="{{ route('admin.vouchers.index') }}"
                            class="inline-flex items-center gap-2 bg-white hover:bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD] font-bold px-4 py-2.5 rounded-2xl shadow-xs text-xs sm:text-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span>Danh sách chính</span>
                        </a>
                    @else
                        <a href="{{ route('admin.vouchers.index', ['status' => 'TRASHED']) }}"
                            class="inline-flex items-center gap-2 bg-white hover:bg-rose-50 text-[#786B61] hover:text-rose-600 border border-[#EBDDCD] font-bold px-4 py-2.5 rounded-2xl shadow-xs text-xs sm:text-sm transition">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            <span>Thùng rác ({{ $stats['trashed'] }})</span>
                        </a>
                    @endif

                    <a href="{{ route('admin.vouchers.create') }}"
                        class="inline-flex items-center justify-center gap-2 bg-[#E08A1E] hover:bg-[#C97810] text-white font-bold px-5 py-2.5 rounded-2xl shadow-md shadow-[#E08A1E]/20 text-xs sm:text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tạo Voucher Mới</span>
                    </a>
                </div>
            </div>

            {{-- 2. 4 Quick Statistics Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card 1: Tổng Voucher --}}
                <div class="voucher-stat-card">
                    <div class="voucher-stat-icon bg-[#E08A1E] shadow-md shadow-[#E08A1E]/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-[#786B61] uppercase tracking-wider">TỔNG VOUCHER</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5">{{ $stats['total'] }}</div>
                    </div>
                </div>

                {{-- Card 2: Đang Diễn Ra --}}
                <div class="voucher-stat-card">
                    <div class="voucher-stat-icon bg-[#10B981] shadow-md shadow-[#10B981]/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-[#786B61] uppercase tracking-wider">ĐANG DIỄN RA</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5">{{ $stats['running'] }}</div>
                    </div>
                </div>

                {{-- Card 3: Hết Hạn / Lượt --}}
                <div class="voucher-stat-card">
                    <div class="voucher-stat-icon bg-[#EF4444] shadow-md shadow-[#EF4444]/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-[#786B61] uppercase tracking-wider">HẾT HẠN / LƯỢT</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5">{{ $stats['expired'] }}</div>
                    </div>
                </div>

                {{-- Card 4: Vô Hiệu Hóa --}}
                <div class="voucher-stat-card">
                    <div class="voucher-stat-icon bg-[#64748B] shadow-md shadow-[#64748B]/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-[#786B61] uppercase tracking-wider">VÔ HIỆU HÓA</div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5">{{ $stats['inactive'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Trashed Banner Alert (If viewing trash) --}}
            @if($isTrashed)
                <div class="bg-[#FFFBEB] border border-[#FDE68A] rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#92400E] font-medium shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🗑️</span>
                        <span>Bạn đang xem <strong>{{ $vouchers->total() }}</strong> voucher trong <strong>Thùng rác</strong>. Bạn có thể khôi phục lại bất kỳ lúc nào.</span>
                    </div>
                    <a href="{{ route('admin.vouchers.index') }}" class="font-bold text-[#E08A1E] underline hover:text-[#B45309]">
                        ← Quay lại danh sách chính
                    </a>
                </div>
            @endif

            {{-- 3. Search & Filter Bar --}}
            <form method="GET" action="{{ route('admin.vouchers.index') }}" class="voucher-filter-card">
                {{-- Search by code --}}
                <div class="relative flex-1 min-w-[240px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Tìm theo mã voucher (VD: BEAR-10K)..."
                        class="voucher-filter-input w-full pl-9 pr-4 py-2.5">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#8E8076]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Filter by Voucher Type --}}
                <select name="voucher_type" class="voucher-filter-select">
                    <option value="">-- Phân loại --</option>
                    <option value="ORDER" {{ request('voucher_type') === 'ORDER' ? 'selected' : '' }}>Giảm giá đơn hàng</option>
                    <option value="SHIPPING" {{ request('voucher_type') === 'SHIPPING' ? 'selected' : '' }}>Giảm phí vận chuyển</option>
                </select>

                {{-- Filter by Status --}}
                <select name="status" class="voucher-filter-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="RUNNING" {{ request('status') === 'RUNNING' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="UPCOMING" {{ request('status') === 'UPCOMING' ? 'selected' : '' }}>Sắp diễn ra</option>
                    <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Đã hết hạn</option>
                    <option value="OUT_OF_STOCK" {{ request('status') === 'OUT_OF_STOCK' ? 'selected' : '' }}>Hết lượt dùng</option>
                    <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    <option value="TRASHED" {{ request('status') === 'TRASHED' ? 'selected' : '' }}>Đã xóa ({{ $stats['trashed'] }})</option>
                </select>

                {{-- Filter by Discount Type --}}
                <select name="discount_type" class="voucher-filter-select">
                    <option value="">-- Mức giảm --</option>
                    <option value="PERCENTAGE" {{ request('discount_type') === 'PERCENTAGE' ? 'selected' : '' }}>Giảm theo %</option>
                    <option value="FIXED" {{ request('discount_type') === 'FIXED' ? 'selected' : '' }}>Giảm VNĐ cố định</option>
                </select>

                {{-- Filter Action Button --}}
                <button type="submit" class="voucher-filter-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    <span>Lọc</span>
                </button>
            </form>

            {{-- 4. Vouchers List Table --}}
            <div class="voucher-table-wrapper">
                <div class="overflow-x-auto">
                    <table class="voucher-table">
                        <thead>
                            <tr>
                                <th class="w-[24%]">MÃ VOUCHER</th>
                                <th class="w-[20%]">MỨC GIẢM & ĐIỀU KIỆN</th>
                                <th class="w-[18%]">THỜI GIAN ÁP DỤNG</th>
                                <th class="w-[12%]">LƯỢT DÙNG</th>
                                <th class="w-[16%]">TRẠNG THÁI</th>
                                <th class="w-[10%] text-center">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $voucher)
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $isExpired = $voucher->end_date && $voucher->end_date->isPast();
                                    $isOutOfStock = $voucher->used_count >= $voucher->usage_limit;
                                    $isUpcoming = $voucher->start_date && $voucher->start_date->isFuture();
                                    $isRunning = $voucher->status === 'ACTIVE' && !$isExpired && !$isOutOfStock && !$isUpcoming;
                                    $usagePercent = min(100, round(($voucher->used_count / max(1, $voucher->usage_limit)) * 100));
                                @endphp
                                <tr>
                                    {{-- 1. Mã Voucher & Badges --}}
                                    <td>
                                        <div class="space-y-2">
                                            <div class="inline-flex items-center gap-2">
                                                <span class="voucher-code-badge">
                                                    {{ $voucher->code }}
                                                </span>
                                                <button type="button" @click="copyCode('{{ $voucher->code }}')"
                                                    class="text-gray-400 hover:text-[#E08A1E] transition p-1 cursor-pointer"
                                                    title="Sao chép mã">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Badges Row --}}
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                {{-- Type Badge --}}
                                                @if($voucher->voucher_type === 'ORDER')
                                                    <span class="bg-[#FFF9EE] text-[#5C3219] border border-[#EBDDCD] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Đơn Hàng
                                                    </span>
                                                @else
                                                    <span class="bg-[#E6F4EA] text-[#0D652D] border border-[#CEEAD6] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Vận Chuyển
                                                    </span>
                                                @endif

                                                {{-- Scope Badge --}}
                                                @if($voucher->voucher_type === 'SHIPPING')
                                                    <span class="bg-[#F0F7FF] text-[#1E40AF] border border-[#DBEAFE] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Phí vận chuyển
                                                    </span>
                                                @elseif($voucher->apply_scope === 'CATEGORY')
                                                    <span class="bg-[#FDF4FF] text-[#86198F] border border-[#F5D0FE] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Theo danh mục
                                                    </span>
                                                @elseif($voucher->apply_scope === 'PRODUCT')
                                                    <span class="bg-[#FEFCE8] text-[#854D0E] border border-[#FEF08A] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Sản phẩm cụ thể
                                                    </span>
                                                @else
                                                    <span class="bg-[#F0F7FF] text-[#1E40AF] border border-[#DBEAFE] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Toàn bộ shop
                                                    </span>
                                                @endif

                                                {{-- Discount Type Badge --}}
                                                @if($voucher->discount_type === 'PERCENTAGE')
                                                    <span class="bg-[#F3E8FF] text-[#6B21A8] border border-[#E9D5FF] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Giảm %
                                                    </span>
                                                @else
                                                    <span class="bg-[#FFF5E6] text-[#B45309] border border-[#FED7AA] text-[11px] font-bold px-2 py-0.5 rounded-md">
                                                        Giảm VNĐ
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 2. Mức Giảm & Điều Kiện --}}
                                    <td>
                                        <div>
                                            <div class="font-extrabold text-sm sm:text-base text-[#2C1408]">
                                                @if($voucher->discount_type === 'PERCENTAGE')
                                                    Giảm {{ (float)$voucher->discount_value }}%
                                                @else
                                                    Giảm {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                                @endif
                                            </div>

                                            @if($voucher->discount_type === 'PERCENTAGE' && $voucher->max_discount_value)
                                                <div class="text-xs text-[#786B61] mt-0.5 font-medium">
                                                    Tối đa {{ number_format($voucher->max_discount_value, 0, ',', '.') }}đ
                                                </div>
                                            @endif

                                            <div class="text-xs text-[#786B61] font-medium mt-0.5">
                                                @if($voucher->min_order_value > 0)
                                                    Đơn tối thiểu: {{ number_format($voucher->min_order_value, 0, ',', '.') }}đ
                                                @else
                                                    Đơn tối thiểu: 0đ
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. Thời Gian Áp Dụng --}}
                                    <td>
                                        <div class="space-y-0.5 text-xs text-[#786B61] font-medium">
                                            <div>Từ: {{ $voucher->start_date ? $voucher->start_date->format('d/m/Y H:i') : '---' }}</div>
                                            <div>Đến: {{ $voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : 'Vô thời hạn' }}</div>
                                            @if($isExpired)
                                                <span class="bg-[#FFEBEE] text-[#C62828] text-[10px] font-bold px-2 py-0.5 rounded-md mt-1 inline-block">
                                                    Đã hết hạn
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 4. Lượt Dùng --}}
                                    <td>
                                        <div>
                                            <div class="text-xs text-[#786B61]">
                                                <span class="font-extrabold text-sm text-[#2C1408]">{{ $voucher->used_count }}</span> / {{ $voucher->usage_limit }}
                                            </div>
                                            <div class="w-24 h-1.5 bg-[#EBDDCD] rounded-full overflow-hidden mt-1.5">
                                                <div class="h-full rounded-full transition-all duration-300 {{ $isOutOfStock ? 'bg-[#EF4444]' : ($isRunning ? 'bg-[#10B981]' : 'bg-[#94A3B8]') }}"
                                                    style="width: {{ $usagePercent }}%;"></div>
                                            </div>
                                            @if($isOutOfStock)
                                                <span class="text-[10px] text-rose-500 font-bold mt-1 block">
                                                    Hết lượt dùng
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 5. Trạng Thái --}}
                                    <td>
                                        <div class="space-y-2 min-w-[130px]">
                                            @if($voucher->trashed())
                                                <span class="bg-rose-50 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 whitespace-nowrap border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span> Đã trong thùng rác
                                                </span>
                                                <div class="text-[10px] text-[#A8988B] font-medium">
                                                    Xóa: {{ $voucher->deleted_at?->format('d/m/Y H:i') }}
                                                </div>
                                            @elseif($voucher->status === 'INACTIVE')
                                                <span class="bg-[#F1F5F9] text-[#64748B] text-xs font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#64748B] shrink-0"></span> Vô hiệu hóa
                                                </span>
                                            @elseif($isExpired || $isOutOfStock)
                                                <span class="bg-[#FFEBEE] text-[#C62828] text-xs font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#C62828] shrink-0"></span> Đã hết hạn
                                                </span>
                                            @elseif($isUpcoming)
                                                <span class="bg-[#FFF3E0] text-[#EF6C00] text-xs font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EF6C00] shrink-0"></span> Sắp diễn ra
                                                </span>
                                            @else
                                                <span class="bg-[#E8F5E9] text-[#2E7D32] text-xs font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32] shrink-0"></span> Đang diễn ra
                                                </span>
                                            @endif

                                            {{-- Toggle Switch Form (Only if not trashed) --}}
                                            @if(!$voucher->trashed())
                                                <form id="toggle-form-{{ $voucher->id }}" method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="voucher-toggle-track {{ $voucher->status === 'ACTIVE' ? 'is-active' : 'is-inactive' }}"
                                                        title="{{ $voucher->status === 'ACTIVE' ? 'Bấm để vô hiệu hóa' : 'Bấm để kích hoạt' }}">
                                                        <div class="voucher-toggle-thumb"></div>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 6. Thao Tác --}}
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($voucher->trashed())
                                                {{-- Restore Button --}}
                                                <button type="button"
                                                    @click="confirmRestore('{{ $voucher->code }}', 'restore-form-{{ $voucher->id }}')"
                                                    class="text-emerald-600 hover:text-emerald-800 p-1.5 rounded-lg hover:bg-emerald-50 transition cursor-pointer"
                                                    title="Khôi phục voucher">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                        </path>
                                                    </svg>
                                                </button>
                                                <form id="restore-form-{{ $voucher->id }}" method="POST"
                                                    action="{{ route('admin.vouchers.restore', $voucher->id) }}" class="hidden">
                                                    @csrf
                                                </form>

                                                {{-- Force Delete Button --}}
                                                <button type="button"
                                                    @click="confirmForceDelete('{{ $voucher->code }}', 'force-delete-form-{{ $voucher->id }}')"
                                                    class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                                    title="Xóa vĩnh viễn">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                                <form id="force-delete-form-{{ $voucher->id }}" method="POST"
                                                    action="{{ route('admin.vouchers.force-delete', $voucher->id) }}" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @else
                                                {{-- Edit --}}
                                                <a href="{{ route('admin.vouchers.edit', $voucher) }}"
                                                    class="text-gray-400 hover:text-[#2C1408] p-1.5 rounded-lg hover:bg-white transition"
                                                    title="Chỉnh sửa voucher">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                </a>

                                                {{-- Soft Delete --}}
                                                <button type="button"
                                                    @click="confirmDelete('{{ $voucher->code }}', 'delete-form-{{ $voucher->id }}')"
                                                    class="text-gray-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                                    title="Chuyển vào thùng rác">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>

                                                <form id="delete-form-{{ $voucher->id }}" method="POST"
                                                    action="{{ route('admin.vouchers.destroy', $voucher) }}" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-[#786B61]">
                                        <div class="text-4xl mb-3">{{ $isTrashed ? '🗑️' : '🎫' }}</div>
                                        <div class="font-bold text-base text-[#2C1408]">
                                            {{ $isTrashed ? 'Thùng rác đang trống' : 'Không tìm thấy mã voucher nào' }}
                                        </div>
                                        <p class="text-xs text-[#9CA3AF] mt-1">
                                            {{ $isTrashed ? 'Không có voucher nào trong thùng rác.' : 'Thử thay đổi bộ lọc hoặc tạo mã voucher mới.' }}
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 5. Footer Pagination --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 text-xs text-[#786B61] font-medium">
                <div>
                    Hiển thị <strong>{{ $vouchers->firstItem() ?? 0 }} – {{ $vouchers->lastItem() ?? 0 }}</strong> trong tổng số <strong>{{ $vouchers->total() }}</strong> mã voucher (10 mã / trang)
                </div>

                <div>
                    <x-pagination :paginator="$vouchers" />
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

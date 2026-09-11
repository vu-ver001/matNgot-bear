@extends('layouts.admin-dashboard')

@section('page-title', 'Quản Lý Voucher')

@section('content')
<div x-data="vouchersList()">
    <div class="space-y-6">

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
                    @if(request('status') === 'TRASHED')
                        <a href="{{ route('admin.vouchers.index') }}"
                            class="inline-flex items-center justify-center gap-2 bg-white hover:bg-[#FAF6F0] text-[#5C3219] border border-[#EBDDCD] font-bold px-4 py-2.5 rounded-xl shadow-xs text-xs sm:text-sm transition">
                            <svg class="w-4 h-4 text-[#5C3219]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span>Danh Sách Chính</span>
                        </a>
                    @else
                        <a href="{{ route('admin.vouchers.index', ['status' => 'TRASHED']) }}"
                            class="inline-flex items-center justify-center gap-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 font-bold px-4 py-2.5 rounded-xl shadow-xs text-xs sm:text-sm transition"
                            title="Xem danh sách voucher đã xóa mềm">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span>Thùng Rác ({{ $stats['trashed'] }})</span>
                        </a>
                    @endif

                    <a href="{{ route('admin.vouchers.create') }}"
                        class="inline-flex items-center justify-center gap-2 bg-[#E08A1E] hover:bg-[#C97810] text-white font-bold px-5 py-2.5 rounded-xl shadow-md shadow-[#E08A1E]/20 text-xs sm:text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tạo Voucher Mới</span>
                    </a>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-2xl flex items-center gap-2.5 shadow-xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-2xl flex items-center gap-2.5 shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base shrink-0"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

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

            @if(request('status') === 'TRASHED')
                <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-rose-900">Thùng rác voucher (Đã xóa mềm)</div>
                            <div class="text-xs text-rose-700 mt-0.5">Các voucher dưới đây đã bị xóa mềm và không còn hiển thị trên trang danh sách chính. Bạn có thể bấm <strong>"Khôi phục"</strong> để đưa voucher trở lại hoạt động.</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.vouchers.index') }}" class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-rose-800 hover:text-rose-950 bg-white border border-rose-300 hover:bg-rose-100/50 px-3.5 py-2 rounded-xl transition">
                        <span>← Quay lại danh sách chính</span>
                    </a>
                </div>
            @endif

            {{-- 4. Vouchers List Table --}}
            <div class="voucher-table-wrapper">
                <div class="overflow-x-auto">
                    <table class="voucher-table">
                        <thead>
                            <tr>
                                <th class="w-[28%]">MÃ VOUCHER</th>
                                <th class="w-[20%]">MỨC GIẢM & ĐIỀU KIỆN</th>
                                <th class="w-[18%]">THỜI GIAN ÁP DỤNG</th>
                                <th class="w-[11%]">LƯỢT DÙNG</th>
                                <th class="w-[13%]">TRẠNG THÁI</th>
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
                                <tr class="transition-colors {{ $voucher->trashed() ? 'bg-rose-50/25' : '' }}">
                                    {{-- 1. Mã Voucher & Badges --}}
                                    <td>
                                        <div class="space-y-2">
                                            <div class="inline-flex items-center gap-2">
                                                <span class="voucher-code-badge">
                                                    {{ $voucher->code }}
                                                </span>
                                                @if($voucher->trashed())
                                                    <span class="bg-rose-100 text-rose-700 text-[10px] font-extrabold px-1.5 py-0.5 rounded border border-rose-200 shrink-0">
                                                        Đã xóa mềm
                                                    </span>
                                                @endif
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
                                            <span class="text-[11px] text-[#A8988B] font-medium block mt-1 whitespace-nowrap">
                                                Tối đa {{ $voucher->usage_limit_per_user ?? 1 }} lượt/khách
                                            </span>
                                            @if($isOutOfStock)
                                                <span class="text-[10px] text-rose-500 font-bold mt-0.5 block">
                                                    Hết lượt dùng
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- 5. Trạng Thái --}}
                                    <td>
                                        <div class="space-y-1.5">
                                            @if($voucher->trashed())
                                                <span class="bg-rose-50 text-rose-700 text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span> Đã trong thùng rác
                                                </span>
                                                <div class="text-[10px] text-[#A8988B] font-medium">
                                                    Xóa: {{ $voucher->deleted_at?->format('d/m/Y H:i') }}
                                                </div>
                                            @elseif($voucher->status === 'INACTIVE')
                                                <span class="bg-[#F1F5F9] text-[#64748B] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#64748B] shrink-0"></span> Vô hiệu hóa
                                                </span>
                                            @elseif($isExpired || $isOutOfStock)
                                                <span class="bg-[#FFEBEE] text-[#C62828] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#C62828] shrink-0"></span> Đã hết hạn
                                                </span>
                                            @elseif($isUpcoming)
                                                <span class="bg-[#FFF3E0] text-[#EF6C00] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EF6C00] shrink-0"></span> Sắp diễn ra
                                                </span>
                                            @else
                                                <span class="bg-[#E8F5E9] text-[#2E7D32] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap">
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
                                        @php
                                            $totalOrders = $voucher->getTotalOrdersCount();
                                            $activeOrders = $voucher->getActiveOrdersCount();
                                        @endphp
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($voucher->trashed())
                                                {{-- Voucher đã trong thùng rác: Hiển thị nút Khôi Phục (Icon) --}}
                                                <button type="button"
                                                    @click="openRestoreModal({
                                                        id: {{ (int) $voucher->id }},
                                                        code: '{{ addslashes($voucher->code) }}',
                                                        used_count: {{ (int) $voucher->used_count }},
                                                        usage_limit: {{ $voucher->usage_limit !== null ? (int) $voucher->usage_limit : 'null' }},
                                                        end_date_formatted: '{{ $voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : 'Vô thời hạn' }}',
                                                        end_date_raw: '{{ $voucher->end_date ? $voucher->end_date->format('Y-m-d\TH:i') : '' }}',
                                                        is_expired: {{ $isExpired ? 'true' : 'false' }},
                                                        is_out_of_stock: {{ $isOutOfStock ? 'true' : 'false' }},
                                                        action: '{{ route('admin.vouchers.restore', $voucher->id) }}'
                                                    })"
                                                    class="text-emerald-600 hover:text-emerald-800 p-1.5 rounded-lg hover:bg-emerald-50 transition cursor-pointer"
                                                    title="Khôi phục voucher này">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                        </path>
                                                    </svg>
                                                </button>
                                            @else
                                                {{-- Voucher chưa xóa: Nếu đã hết hạn (ngày hoặc lượt) thì hiển thị THÊM nút Khôi phục để gia hạn (Icon) --}}
                                                @if($isExpired || $isOutOfStock)
                                                    <button type="button"
                                                        @click="openRestoreModal({
                                                            id: {{ (int) $voucher->id }},
                                                            code: '{{ addslashes($voucher->code) }}',
                                                            used_count: {{ (int) $voucher->used_count }},
                                                            usage_limit: {{ $voucher->usage_limit !== null ? (int) $voucher->usage_limit : 'null' }},
                                                            end_date_formatted: '{{ $voucher->end_date ? $voucher->end_date->format('d/m/Y H:i') : 'Vô thời hạn' }}',
                                                            end_date_raw: '{{ $voucher->end_date ? $voucher->end_date->format('Y-m-d\TH:i') : '' }}',
                                                            is_expired: {{ $isExpired ? 'true' : 'false' }},
                                                            is_out_of_stock: {{ $isOutOfStock ? 'true' : 'false' }},
                                                            action: '{{ route('admin.vouchers.restore', $voucher->id) }}'
                                                        })"
                                                        class="text-emerald-600 hover:text-emerald-800 p-1.5 rounded-lg hover:bg-emerald-50 transition cursor-pointer"
                                                        title="Khôi phục & gia hạn voucher">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Nút Chỉnh sửa (Edit) --}}
                                                <a href="{{ route('admin.vouchers.edit', $voucher) }}"
                                                    class="text-gray-400 hover:text-[#2C1408] p-1.5 rounded-lg hover:bg-white transition"
                                                    title="Chỉnh sửa voucher">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                </a>

                                                {{-- Nút Xóa: TẤT CẢ ĐỀU LÀ XÓA MỀM (không xóa vĩnh viễn) --}}
                                                @if($isRunning)
                                                    {{-- Đang diễn ra: Không cho xóa, hướng dẫn vô hiệu hóa trước --}}
                                                    <button type="button"
                                                        @click="alertCannotDeleteRunning('{{ $voucher->code }}')"
                                                        class="text-gray-300 hover:text-gray-400 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-not-allowed"
                                                        title="Không thể xóa: Voucher đang diễn ra. Vui lòng chuyển công tắc sang 'Vô hiệu hóa' trước khi xóa">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                @elseif($activeOrders > 0)
                                                    {{-- Đang có đơn hàng chưa hoàn tất: Chặn không cho xóa --}}
                                                    <button type="button"
                                                        @click="alertCannotDeleteActive('{{ $voucher->code }}', {{ $activeOrders }})"
                                                        class="text-gray-300 hover:text-gray-400 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-not-allowed"
                                                        title="Không thể xóa: Đang có {{ $activeOrders }} đơn hàng chưa hoàn tất đang áp dụng mã này">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                @else
                                                    {{-- Xóa mềm voucher --}}
                                                    <button type="button"
                                                        @click="confirmSoftDelete('{{ $voucher->code }}', 'delete-form-{{ $voucher->id }}')"
                                                        class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                                        title="Xóa voucher (chuyển vào thùng rác)">
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
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-[#786B61]">
                                        <div class="text-4xl mb-3">🎫</div>
                                        <div class="font-bold text-base text-[#2C1408]">
                                            Không tìm thấy mã voucher nào
                                        </div>
                                        <p class="text-xs text-[#9CA3AF] mt-1">
                                            Thử thay đổi bộ lọc hoặc tạo mã voucher mới.
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

        {{-- 6. Modal Khôi Phục & Gia Hạn Voucher --}}
        <div x-show="restoreModalOpen" x-cloak x-transition.opacity 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs" 
            @keydown.escape.window="closeRestoreModal()">
            <div class="fixed inset-0" @click="closeRestoreModal()"></div>
        
        <div class="relative bg-[#FAF6F0] rounded-[28px] border-2 border-[#EBDDCD] shadow-[0_25px_60px_rgba(44,20,8,0.25)] w-full max-w-xl my-auto z-10 overflow-visible"
            @click.stop>
            {{-- Modal Header --}}
            <div class="px-6 py-4.5 border-b border-[#EBDDCD] bg-white flex items-center justify-between rounded-t-[26px]">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center shadow-md shadow-emerald-600/25 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base sm:text-lg text-[#2C1408] tracking-tight">
                            Khôi Phục & Gia Hạn Voucher
                        </h3>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-xs text-[#786B61] font-medium">Mã voucher:</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#FAF4ED] border border-[#E8DACB] font-mono text-xs font-black text-[#5C3219] shadow-2xs">
                                🎟️ <span x-text="restoreData.code"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="closeRestoreModal()" 
                    class="w-8 h-8 rounded-full bg-[#FAF6F0] hover:bg-[#F0E6DA] text-[#786B61] hover:text-[#2C1408] flex items-center justify-center transition cursor-pointer"
                    aria-label="Đóng">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form method="POST" :action="restoreData.action" @submit="submitRestoreForm($event)" class="p-6 space-y-5">
                @csrf

                {{-- Status Overview Bar --}}
                <div class="p-4 rounded-2xl bg-white border border-[#EBDDCD] shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs font-extrabold text-[#5C3219] uppercase tracking-wide">
                            <svg class="w-4 h-4 text-[#E08A1E] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Tình trạng hiện tại của voucher</span>
                        </div>
                        <span class="text-[11px] font-semibold text-[#8E8076]">Dữ liệu gốc</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        {{-- Hạn kết thúc cũ --}}
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-[#FAF6F0] border border-[#F0E6DA]">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-base shadow-2xs shrink-0">
                                📅
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[10px] uppercase font-bold text-[#8E8076] tracking-wider">Hạn kết thúc cũ</div>
                                <div class="font-extrabold text-xs sm:text-sm text-[#2C1408] truncate mt-0.5" x-text="restoreData.end_date_formatted"></div>
                                <template x-if="restoreData.is_expired">
                                    <span class="inline-block mt-1 text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">Đã hết hạn</span>
                                </template>
                            </div>
                        </div>

                        {{-- Lượt dùng --}}
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-[#FAF6F0] border border-[#F0E6DA]">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-base shadow-2xs shrink-0">
                                📊
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[10px] uppercase font-bold text-[#8E8076] tracking-wider">Lượt dùng hiện tại</div>
                                <div class="font-extrabold text-xs sm:text-sm text-[#2C1408] mt-0.5">
                                    <span class="text-emerald-700" x-text="restoreData.used_count"></span> / <span x-text="restoreData.usage_limit"></span> lượt
                                </div>
                                <template x-if="restoreData.is_out_of_stock">
                                    <span class="inline-block mt-1 text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">Hết lượt dùng</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 1. Gia hạn thời gian kết thúc --}}
                <div class="bg-white p-4.5 rounded-2xl border border-[#EBDDCD] shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#5C3219]">
                            <span class="w-5 h-5 rounded-md bg-[#5C3219] text-[#F6D89B] flex items-center justify-center text-[11px] font-bold">1</span>
                            <span>Thời gian kết thúc mới (Gia hạn ngày)</span>
                        </label>
                        <span class="text-[10px] font-bold text-[#8E8076] bg-[#FAF6F0] border border-[#EBDDCD] px-2 py-0.5 rounded-full">Tùy chọn</span>
                    </div>

                    {{-- Custom Cute DateTime Picker --}}
                    <x-datetime-picker name="end_date" :disablePast="true" :minDate="now()->format('Y-m-d H:i')" placeholder="Chọn ngày giờ kết thúc mới..." />
                    
                    {{-- Quick date buttons --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="text-[11px] text-[#786B61] font-semibold shrink-0">Gia hạn nhanh:</span>
                        <button type="button" @click="addDaysToEndDate(7)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-[#FAF6F0] hover:bg-[#5C3219] hover:text-white border border-[#EBDDCD] shadow-2xs transition-all cursor-pointer flex items-center gap-1">
                            <span>⚡</span> +7 ngày
                        </button>
                        <button type="button" @click="addDaysToEndDate(14)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-[#FAF6F0] hover:bg-[#5C3219] hover:text-white border border-[#EBDDCD] shadow-2xs transition-all cursor-pointer flex items-center gap-1">
                            <span>⚡</span> +14 ngày
                        </button>
                        <button type="button" @click="addDaysToEndDate(30)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-[#FAF6F0] hover:bg-[#5C3219] hover:text-white border border-[#EBDDCD] shadow-2xs transition-all cursor-pointer flex items-center gap-1">
                            <span>⚡</span> +30 ngày
                        </button>
                    </div>

                    <p class="text-[11px] text-[#8E8076] font-medium flex items-center gap-1.5 pt-0.5">
                        <span class="text-amber-600 font-bold">*</span> Thời gian kết thúc gia hạn phải ở <strong>tương lai</strong> (sau thời điểm hiện tại).
                    </p>
                </div>

                {{-- 2. Gia hạn số lượt dùng --}}
                <div class="bg-white p-4.5 rounded-2xl border border-[#EBDDCD] shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-[#5C3219]">
                            <span class="w-5 h-5 rounded-md bg-[#5C3219] text-[#F6D89B] flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>Tổng lượt dùng mới (Gia hạn số lượng)</span>
                        </label>
                        <span class="text-[10px] font-bold text-[#8E8076] bg-[#FAF6F0] border border-[#EBDDCD] px-2 py-0.5 rounded-full">Tùy chọn</span>
                    </div>

                    <div class="relative">
                        <input type="number" name="usage_limit" x-model="restoreData.new_usage_limit"
                            :min="restoreData.used_count"
                            placeholder="Nhập tổng số lượt dùng mới..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#EBDDCD] rounded-xl text-xs sm:text-sm font-bold text-[#2C1408] focus:border-[#E08A1E] focus:ring-2 focus:ring-[#E08A1E]/20 transition shadow-2xs">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-base pointer-events-none">🎟️</span>
                    </div>
                    
                    {{-- Quick limit buttons --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="text-[11px] text-[#786B61] font-semibold shrink-0">Thêm nhanh:</span>
                        <button type="button" @click="addLimit(10)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-[#FAF6F0] hover:bg-[#5C3219] hover:text-white border border-[#EBDDCD] shadow-2xs transition-all cursor-pointer flex items-center gap-1">
                            <span>➕</span> +10 lượt
                        </button>
                        <button type="button" @click="addLimit(50)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-[#FAF6F0] hover:bg-[#5C3219] hover:text-white border border-[#EBDDCD] shadow-2xs transition-all cursor-pointer flex items-center gap-1">
                            <span>➕</span> +50 lượt
                        </button>
                        <button type="button" @click="addLimit(100)"
                            class="px-3 py-1.5 text-xs font-bold rounded-xl bg-[#FAF6F0] hover:bg-[#5C3219] hover:text-white border border-[#EBDDCD] shadow-2xs transition-all cursor-pointer flex items-center gap-1">
                            <span>➕</span> +100 lượt
                        </button>
                    </div>

                    <p class="text-[11px] text-[#8E8076] font-medium flex items-center gap-1.5 pt-0.5">
                        <span class="text-amber-600 font-bold">*</span> Tối thiểu bằng số lượt đã dùng (<span class="font-bold text-[#5C3219]" x-text="restoreData.used_count"></span> lượt). Nếu không thay đổi, hệ thống giữ nguyên cấu hình cũ.
                    </p>
                </div>

                {{-- Modal Actions --}}
                <div class="pt-2 flex items-center justify-end gap-3 border-t border-[#EBDDCD]/80">
                    <button type="button" @click="closeRestoreModal()"
                        class="px-5 py-2.5 rounded-xl border border-[#EBDDCD] bg-white hover:bg-[#FAF6F0] text-[#786B61] hover:text-[#2C1408] font-bold text-xs sm:text-sm transition cursor-pointer shadow-2xs">
                        Hủy bỏ
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-700/25 hover:shadow-lg transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Khôi phục & Lưu cấu hình</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
                    <a href="{{ route('admin.vouchers.create') }}"
                        class="inline-flex items-center justify-center gap-2 bg-[#E08A1E] hover:bg-[#C97810] text-white font-bold px-5 py-2.5 rounded-xl shadow-md shadow-[#E08A1E]/20 text-xs sm:text-sm transition">
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
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5" id="stat-total">{{ $stats['total'] }}</div>
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
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5" id="stat-running">{{ $stats['running'] }}</div>
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
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5" id="stat-expired">{{ $stats['expired'] }}</div>
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
                        <div class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] mt-0.5" id="stat-inactive">{{ $stats['inactive'] }}</div>
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
                                                @if($voucher->usage_limit_per_user)
                                                    Tối đa {{ $voucher->usage_limit_per_user }} lượt/khách
                                                @else
                                                    Không giới hạn lượt/khách
                                                @endif
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
                                        <div class="space-y-1.5" id="voucher-status-container-{{ $voucher->id }}">
                                            <div id="voucher-badge-{{ $voucher->id }}">
                                                @if($voucher->status === 'INACTIVE')
                                                    <span class="bg-[#F1F5F9] text-[#64748B] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#64748B] shrink-0"></span> Vô hiệu hóa
                                                    </span>
                                                @elseif($isExpired || $isOutOfStock)
                                                    <span class="bg-[#FFEBEE] text-[#C62828] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#C62828] shrink-0"></span> Đã hết hạn
                                                    </span>
                                                @elseif($isUpcoming)
                                                    <span class="bg-[#FFF3E0] text-[#EF6C00] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#EF6C00] shrink-0"></span> Sắp diễn ra
                                                    </span>
                                                @else
                                                    <span class="bg-[#E8F5E9] text-[#2E7D32] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32] shrink-0"></span> Đang diễn ra
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Toggle Switch Form (AJAX không giật trang) --}}
                                            <form id="toggle-form-{{ $voucher->id }}" method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}" class="block"
                                                @submit.prevent="toggleVoucherStatus({{ $voucher->id }}, '{{ route('admin.vouchers.toggle', $voucher) }}', '{{ addslashes($voucher->code) }}', {{ (int) $voucher->used_count }})">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    id="toggle-btn-{{ $voucher->id }}"
                                                    class="voucher-toggle-track transition-all duration-200 active:scale-95 cursor-pointer {{ $voucher->status === 'ACTIVE' ? 'is-active' : 'is-inactive' }}"
                                                    title="{{ $voucher->status === 'ACTIVE' ? 'Bấm để vô hiệu hóa' : 'Bấm để kích hoạt' }}">
                                                    <div class="voucher-toggle-thumb"></div>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    {{-- 6. Thao Tác --}}
                                    <td class="text-center">
                                        @php
                                            $totalOrders = $voucher->getTotalOrdersCount();
                                            $activeOrders = $voucher->getActiveOrdersCount();
                                        @endphp
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Nếu đã hết hạn (ngày hoặc lượt) hoặc đã xóa mềm thì hiển thị nút Gia hạn / Khôi phục thời gian sử dụng --}}
                                            @if($isExpired || $isOutOfStock || $voucher->trashed())
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
                                                    title="Gia hạn / Khôi phục thời gian sử dụng voucher">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
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

                                            {{-- Nút Xóa: XÓA MỀM ĐỂ BẢO TOÀN DỮ LIỆU KHÁCH HÀNG --}}
                                            <div id="voucher-actions-delete-{{ $voucher->id }}" data-active-orders="{{ (int) $activeOrders }}" class="inline-flex items-center">
                                                @if($activeOrders > 0)
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
                                                @elseif($isRunning && $voucher->used_count > 0)
                                                    {{-- Đang diễn ra VÀ ĐÃ CÓ lượt dùng: Yêu cầu vô hiệu hóa trước --}}
                                                    <button type="button"
                                                        @click="alertCannotDeleteRunning('{{ $voucher->code }}', {{ (int) $voucher->used_count }})"
                                                        class="text-gray-300 hover:text-gray-400 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-not-allowed"
                                                        title="Không thể xóa: Voucher đang diễn ra và đã có {{ $voucher->used_count }} lượt dùng. Vui lòng chuyển công tắc sang 'Vô hiệu hóa' trước khi xóa">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                @else
                                                    {{-- Cho phép xóa mềm voucher: Đang diễn ra nhưng 0 lượt dùng, hoặc đã vô hiệu hóa, hoặc đã hết hạn/hết lượt --}}
                                                    <button type="button"
                                                        @click="confirmSoftDelete('{{ $voucher->code }}', 'delete-form-{{ $voucher->id }}')"
                                                        class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                                        title="Xóa voucher (xóa mềm bảo toàn dữ liệu)">
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

        {{-- 6. Modal Gia Hạn Voucher --}}
        <div x-show="restoreModalOpen" x-cloak x-transition.opacity 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-xs" 
            @keydown.escape.window="closeRestoreModal()">
            <div class="fixed inset-0" @click="closeRestoreModal()"></div>
        
            <div class="relative bg-white rounded-3xl border border-[#EBDDCD] shadow-[0_25px_60px_rgba(44,20,8,0.22)] w-full max-w-[580px] my-auto z-10"
                @click.stop>
                
                {{-- 1. Modal Header --}}
                <div class="px-7 py-5 bg-[#FAF6EE] border-b border-[#F0E6D8] flex items-center justify-between rounded-t-3xl">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#E08A1E] to-[#E67E17] text-white flex items-center justify-center shadow-md shadow-[#E08A1E]/25 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h3 class="font-black text-lg text-[#2C1408] tracking-tight">
                                    Gia Hạn Voucher
                                </h3>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#FFF9EE] border border-[#FDE68A] font-mono text-xs font-black text-[#B45309] shadow-2xs">
                                    🎟️ <span x-text="restoreData.code"></span>
                                </span>
                            </div>
                            <p class="text-xs text-[#8E8076] mt-0.5 font-medium">Cập nhật thêm thời gian sử dụng hoặc lượt dùng mới</p>
                        </div>
                    </div>

                    <button type="button" @click="closeRestoreModal()" 
                        class="w-8 h-8 rounded-full bg-white hover:bg-gray-100 text-gray-400 hover:text-gray-700 flex items-center justify-center transition border border-gray-200 shadow-2xs cursor-pointer"
                        aria-label="Đóng">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- 2. Modal Body Form --}}
                <form method="POST" :action="restoreData.action" @submit="submitRestoreForm($event)" class="p-7 space-y-6">
                    @csrf

                    {{-- Status Overview Strip --}}
                    <div class="bg-[#FBF8F3] border border-[#EBDDCD] rounded-2xl p-4 sm:p-5 grid grid-cols-2 divide-x divide-[#EBDDCD]">
                        {{-- Hạn kết thúc cũ --}}
                        <div class="pr-4 sm:pr-6">
                            <div class="text-[11px] font-bold text-[#8E8076] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar text-[#E08A1E] text-xs"></i>
                                <span>Hạn kết thúc cũ</span>
                            </div>
                            <div class="font-black text-sm sm:text-base text-[#2C1408] mt-1.5 truncate" x-text="restoreData.end_date_formatted"></div>
                            <div class="mt-2">
                                <span x-show="restoreData.is_expired" class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200/80 px-2 py-0.5 rounded-md shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Đã hết hạn
                                </span>
                                <span x-show="!restoreData.is_expired" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-md shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Còn thời gian
                                </span>
                            </div>
                        </div>

                        {{-- Lượt dùng hiện tại --}}
                        <div class="pl-4 sm:pl-6">
                            <div class="text-[11px] font-bold text-[#8E8076] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-chart-pie text-[#E08A1E] text-xs"></i>
                                <span>Lượt dùng</span>
                            </div>
                            <div class="font-black text-sm sm:text-base text-[#2C1408] mt-1.5">
                                <span class="text-emerald-700" x-text="restoreData.used_count"></span> / <span x-text="restoreData.usage_limit || '∞'"></span> lượt
                            </div>
                            <div class="mt-2">
                                <span x-show="restoreData.is_out_of_stock" class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200/80 px-2 py-0.5 rounded-md shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Đã hết lượt
                                </span>
                                <span x-show="!restoreData.is_out_of_stock" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-md shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Còn lượt dùng
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Section 1: Gia hạn thời gian kết thúc --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-xs sm:text-sm font-bold text-[#2C1408] flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#FAF0E6] text-[#E08A1E] flex items-center justify-center text-[10.5px] font-black shrink-0">1</span>
                                <span>Thời gian kết thúc mới</span>
                            </label>
                            {{-- Quick date pills --}}
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-[11px] font-semibold text-[#8E8076] hidden sm:inline">Cộng nhanh:</span>
                                <button type="button" @click="addDaysToEndDate(7)"
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#FAF5ED] hover:bg-[#E08A1E] hover:text-white text-[#7A4B2A] border border-[#EADBCE] transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                    +7 ngày
                                </button>
                                <button type="button" @click="addDaysToEndDate(14)"
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#FAF5ED] hover:bg-[#E08A1E] hover:text-white text-[#7A4B2A] border border-[#EADBCE] transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                    +14 ngày
                                </button>
                                <button type="button" @click="addDaysToEndDate(30)"
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#FAF5ED] hover:bg-[#E08A1E] hover:text-white text-[#7A4B2A] border border-[#EADBCE] transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                    +30 ngày
                                </button>
                            </div>
                        </div>

                        {{-- Custom Cute DateTime Picker --}}
                        <x-datetime-picker name="end_date" :disablePast="true" :minDate="now()->format('Y-m-d H:i')" placeholder="Chọn ngày giờ kết thúc mới..." />

                        <p class="text-xs text-[#8E8076] font-medium flex items-center gap-1.5 pt-0.5">
                            <i class="fa-solid fa-circle-info text-[#E08A1E] text-[11px]"></i>
                            <span>Thời gian kết thúc gia hạn phải ở tương lai.</span>
                        </p>
                    </div>

                    {{-- Section 2: Gia hạn số lượt dùng --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <label class="text-xs sm:text-sm font-bold text-[#2C1408] flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#FAF0E6] text-[#E08A1E] flex items-center justify-center text-[10.5px] font-black shrink-0">2</span>
                                <span>Tổng số lượt dùng mới</span>
                            </label>
                            {{-- Quick limit pills --}}
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="text-[11px] font-semibold text-[#8E8076] hidden sm:inline">Thêm nhanh:</span>
                                <button type="button" @click="addLimit(10)"
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#FAF5ED] hover:bg-[#E08A1E] hover:text-white text-[#7A4B2A] border border-[#EADBCE] transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                    +10
                                </button>
                                <button type="button" @click="addLimit(50)"
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#FAF5ED] hover:bg-[#E08A1E] hover:text-white text-[#7A4B2A] border border-[#EADBCE] transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                    +50
                                </button>
                                <button type="button" @click="addLimit(100)"
                                    class="px-2.5 py-1 text-xs font-bold rounded-lg bg-[#FAF5ED] hover:bg-[#E08A1E] hover:text-white text-[#7A4B2A] border border-[#EADBCE] transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                    +100
                                </button>
                            </div>
                        </div>

                        <div class="relative">
                            <input type="number" name="usage_limit" x-model="restoreData.new_usage_limit"
                                :min="restoreData.used_count"
                                placeholder="Nhập tổng số lượt dùng mới..."
                                class="w-full pl-11 pr-4 py-3 bg-[#FAF8F5] hover:bg-white focus:bg-white border border-[#E4D5C5] focus:border-[#E08A1E] focus:ring-3 focus:ring-[#E08A1E]/15 rounded-xl text-sm font-bold text-[#2C1408] transition shadow-2xs">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-base pointer-events-none">🎟️</span>
                        </div>

                        <p class="text-xs text-[#8E8076] font-medium flex items-center gap-1.5 pt-0.5">
                            <i class="fa-solid fa-circle-info text-[#E08A1E] text-[11px]"></i>
                            <span>Tối thiểu bằng số lượt đã dùng (<strong class="text-[#2C1408]" x-text="restoreData.used_count"></strong> lượt). Giữ nguyên nếu không thay đổi.</span>
                        </p>
                    </div>

                    {{-- 3. Modal Actions Footer --}}
                    <div class="-mx-7 -mb-7 mt-6 px-7 py-4.5 bg-[#FAF6EE] border-t border-[#F0E6D8] flex items-center justify-end gap-3 rounded-b-3xl">
                        <button type="button" @click="closeRestoreModal()"
                            class="px-5 py-2.5 rounded-xl border border-[#D9C8B5] bg-white hover:bg-[#F5EFE6] text-[#6B5E55] hover:text-[#2C1408] font-bold text-xs sm:text-sm transition cursor-pointer shadow-2xs">
                            Hủy bỏ
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#E08A1E] to-[#CF730D] hover:from-[#CF730D] hover:to-[#B66006] text-white font-black text-xs sm:text-sm shadow-md shadow-[#E08A1E]/25 hover:shadow-lg transition-all cursor-pointer hover:scale-[1.01] active:scale-[0.99]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Xác nhận gia hạn</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

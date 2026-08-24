<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#E09028] to-[#5C3219] text-white flex items-center justify-center shadow-md shadow-[#E09028]/25 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-[#2E190E] tracking-tight">QUẢN LÝ VOUCHER</h2>
                    <p class="text-xs text-[#7E4A28]/90 mt-0.5 font-normal">Quản lý, tạo mới và cấu hình các chương trình ưu đãi, khuyến mãi cho khách hàng</p>
                </div>
            </div>
            <a href="{{ route('admin.vouchers.create') }}" 
               class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#E09028] to-[#5C3219] hover:from-[#5C3219] hover:to-[#2C160B] text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-[#5C3219]/20 transition transform hover:-translate-y-0.5 active:translate-y-0 text-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tạo Voucher Mới</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F9F5EE] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Statistics Cards (Sử dụng CSS Class riêng) --}}
            <div class="voucher-stat-grid">
                {{-- Card 1: Tổng Voucher --}}
                <div class="voucher-stat-card">
                    <div class="voucher-stat-icon bg-gradient-to-br from-[#F4B860] to-[#E09028] shadow-md shadow-[#E09028]/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] sm:text-xs font-semibold text-[#8E8076] uppercase tracking-wider truncate">Tổng Voucher</div>
                        <div class="text-xl sm:text-2xl font-bold text-[#2E190E] mt-0.5 truncate">{{ $stats['total'] }}</div>
                    </div>
                </div>

                {{-- Card 2: Đang Diễn Ra --}}
                <div class="voucher-stat-card border-emerald-100 hover:border-emerald-300">
                    <div class="voucher-stat-icon bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-md shadow-emerald-500/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] sm:text-xs font-semibold text-emerald-600 uppercase tracking-wider truncate">Đang Diễn Ra</div>
                        <div class="text-xl sm:text-2xl font-bold text-emerald-700 mt-0.5 truncate">{{ $stats['running'] }}</div>
                    </div>
                </div>

                {{-- Card 3: Hết Hạn / Lượt --}}
                <div class="voucher-stat-card border-rose-100 hover:border-rose-300">
                    <div class="voucher-stat-icon bg-gradient-to-br from-rose-400 to-rose-600 shadow-md shadow-rose-500/25">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] sm:text-xs font-semibold text-rose-600 uppercase tracking-wider truncate">Hết Hạn / Lượt</div>
                        <div class="text-xl sm:text-2xl font-bold text-rose-700 mt-0.5 truncate">{{ $stats['expired'] }}</div>
                    </div>
                </div>

                {{-- Card 4: Vô Hiệu Hóa --}}
                <div class="voucher-stat-card border-gray-200 hover:border-gray-400">
                    <div class="voucher-stat-icon bg-gradient-to-br from-slate-400 to-slate-600 shadow-md shadow-slate-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] sm:text-xs font-semibold text-[#8E8076] uppercase tracking-wider truncate">Vô Hiệu Hóa</div>
                        <div class="text-xl sm:text-2xl font-bold text-gray-700 mt-0.5 truncate">{{ $stats['inactive'] }}</div>
                    </div>
                </div>
            </div>

            {{-- 2. Filters & Search Bar --}}
            <div class="voucher-filter-card">
                <form method="GET" action="{{ route('admin.vouchers.index') }}" class="flex flex-row flex-wrap lg:flex-nowrap items-center gap-2.5 w-full">
                    
                    {{-- Search by code --}}
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Tìm theo mã voucher (VD: BEAR-10K)..."
                               class="voucher-filter-input w-full pl-9 pr-3 py-2">
                        <div class="absolute left-3 top-2.5 text-[#8E8076]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    {{-- Filter by voucher type --}}
                    <div class="w-auto min-w-[155px]">
                        <select name="voucher_type" class="voucher-filter-select w-full py-2 px-3">
                            <option value="">-- Phân loại --</option>
                            <option value="ORDER" {{ request('voucher_type') === 'ORDER' ? 'selected' : '' }}>Mã Đơn Hàng</option>
                            <option value="SHIPPING" {{ request('voucher_type') === 'SHIPPING' ? 'selected' : '' }}>Mã Vận Chuyển</option>
                        </select>
                    </div>

                    {{-- Filter by real status --}}
                    <div class="w-auto min-w-[150px]">
                        <select name="status" class="voucher-filter-select w-full py-2 px-3">
                            <option value="">-- Trạng thái --</option>
                            <option value="RUNNING" {{ request('status') === 'RUNNING' ? 'selected' : '' }}>Đang diễn ra</option>
                            <option value="UPCOMING" {{ request('status') === 'UPCOMING' ? 'selected' : '' }}>Sắp diễn ra</option>
                            <option value="OUT_OF_STOCK" {{ request('status') === 'OUT_OF_STOCK' ? 'selected' : '' }}>Hết lượt dùng</option>
                            <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Đã hết hạn</option>
                            <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Vô hiệu hóa</option>
                        </select>
                    </div>

                    {{-- Filter by discount type --}}
                    <div class="w-auto min-w-[125px]">
                        <select name="discount_type" class="voucher-filter-select w-full py-2 px-3">
                            <option value="">-- Mức giảm --</option>
                            <option value="PERCENTAGE" {{ request('discount_type') === 'PERCENTAGE' ? 'selected' : '' }}>Phần trăm (%)</option>
                            <option value="FIXED" {{ request('discount_type') === 'FIXED' ? 'selected' : '' }}>Cố định (VNĐ)</option>
                        </select>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="submit" class="bg-gradient-to-r from-[#E09028] to-[#5C3219] hover:from-[#5C3219] hover:to-[#2C160B] text-white font-bold py-2 px-4 rounded-xl text-xs sm:text-sm transition flex items-center gap-1.5 shadow-xs" title="Lọc dữ liệu">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            <span>Lọc</span>
                        </button>
                        @if(request()->hasAny(['search', 'voucher_type', 'status', 'discount_type']))
                            <a href="{{ route('admin.vouchers.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-[#615248] rounded-xl transition text-xs sm:text-sm flex items-center justify-center font-bold" title="Xóa bộ lọc">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- 3. Vouchers Table --}}
            <div class="voucher-table-wrapper" x-data="vouchersList()">
                <div class="overflow-x-auto">
                    <table class="voucher-table">
                        <thead>
                            <tr class="voucher-table-head">
                                <th class="py-4 px-5">Mã Voucher</th>
                                <th class="py-4 px-5">Mức Giảm & Điều Kiện</th>
                                <th class="py-4 px-5">Thời Gian Áp Dụng</th>
                                <th class="py-4 px-5 text-center">Lượt Dùng</th>
                                <th class="py-4 px-5 text-center">Trạng Thái</th>
                                <th class="py-4 px-5 text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-amber-100/60">
                            @forelse($vouchers as $voucher)
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $isExpired = $voucher->end_date < $now;
                                    $isOutOfLimit = $voucher->used_count >= $voucher->usage_limit;
                                    $isNotStarted = $voucher->start_date > $now;
                                    $usagePercent = min(100, round(($voucher->used_count / max(1, $voucher->usage_limit)) * 100));
                                @endphp

                                <tr class="voucher-table-row">
                                    {{-- Code & Type --}}
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-2">
                                            <span class="voucher-code-badge">
                                                {{ $voucher->code }}
                                            </span>
                                            <button type="button" 
                                                    @click="copyCode('{{ $voucher->code }}')" 
                                                    class="text-gray-400 hover:text-amber-600 transition p-1 rounded" 
                                                    title="Sao chép mã">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>
                                        </div>
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            {{-- Voucher Type Badge --}}
                                            @if($voucher->voucher_type === 'SHIPPING')
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 border border-teal-200">
                                                    <svg class="w-3 h-3 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                                    <span>Vận Chuyển</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD]">
                                                    <svg class="w-3 h-3 text-[#E09028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                                    <span>Đơn Hàng</span>
                                                </span>
                                            @endif

                                            {{-- Scope Badge --}}
                                            @php $scopeBadge = $voucher->apply_scope_badge; @endphp
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md border {{ $scopeBadge['bg'] }}">
                                                <span>{{ $scopeBadge['label'] }}</span>
                                            </span>

                                            {{-- Discount Type --}}
                                            <span class="inline-block text-[11px] font-semibold px-2 py-0.5 rounded-md {{ $voucher->discount_type === 'PERCENTAGE' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ $voucher->discount_type === 'PERCENTAGE' ? 'Giảm %' : 'Giảm VNĐ' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Value & Condition --}}
                                    <td class="py-4 px-5">
                                        <div class="font-extrabold text-base text-gray-900">
                                            @if($voucher->discount_type === 'PERCENTAGE')
                                                Giảm {{ (float)$voucher->discount_value }}%
                                                @if($voucher->max_discount_value)
                                                    <span class="text-xs font-medium text-gray-500 block">Tối đa {{ number_format($voucher->max_discount_value, 0, ',', '.') }}đ</span>
                                                @endif
                                            @else
                                                Giảm {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            Đơn tối thiểu: <span class="font-semibold text-gray-700">{{ number_format($voucher->min_order_value, 0, ',', '.') }}đ</span>
                                        </div>
                                    </td>

                                    {{-- Dates --}}
                                    <td class="py-4 px-5">
                                        <div class="text-xs space-y-1">
                                            <div class="text-gray-600 flex items-center gap-1.5">
                                                <span class="text-gray-400 font-mono">Từ:</span> 
                                                <span class="font-medium">{{ $voucher->start_date->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="text-gray-600 flex items-center gap-1.5">
                                                <span class="text-gray-400 font-mono">Đến:</span> 
                                                <span class="font-medium">{{ $voucher->end_date->format('d/m/Y H:i') }}</span>
                                            </div>
                                            @if($isExpired)
                                                <span class="inline-block text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">Đã hết hạn</span>
                                            @elseif($isNotStarted)
                                                <span class="inline-block text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">Chưa bắt đầu</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Usage --}}
                                    <td class="py-4 px-5 text-center min-w-[130px]">
                                        <div class="font-bold text-gray-800 text-xs mb-1">
                                            {{ $voucher->used_count }} / {{ $voucher->usage_limit }}
                                        </div>
                                        <div class="voucher-progress-container">
                                            <div class="voucher-progress-fill {{ $usagePercent >= 100 ? 'bg-rose-500' : ($usagePercent > 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" 
                                                 style="width: {{ $usagePercent }}%"></div>
                                        </div>
                                        @if($isOutOfLimit)
                                            <span class="text-[10px] text-rose-500 font-bold block mt-1">Hết lượt dùng</span>
                                        @endif
                                    </td>

                                    {{-- Trạng Thái Thực Tế & Công Tắc --}}
                                    <td class="py-4 px-5 text-center min-w-[145px]">
                                        @php
                                            $badge = $voucher->real_status_badge;
                                        @endphp
                                        <div class="inline-flex flex-col items-center gap-1.5">
                                            {{-- Badge trạng thái thực tế --}}
                                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $badge['bg'] }} shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                                <span>{{ $badge['label'] }}</span>
                                            </span>

                                            {{-- Switch công tắc Admin --}}
                                            <form method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}" class="inline-block mt-0.5">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $voucher->status === 'ACTIVE' ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                                        title="{{ $voucher->status === 'ACTIVE' ? 'Bấm để Vô hiệu hóa' : 'Bấm để Kích hoạt (Đang áp dụng)' }}">
                                                    <span class="sr-only">Toggle status</span>
                                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $voucher->status === 'ACTIVE' ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="py-4 px-5 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('admin.vouchers.edit', $voucher) }}" 
                                               class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" 
                                               title="Chỉnh sửa voucher">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            
                                            <form id="delete-voucher-{{ $voucher->id }}" method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                        @click="confirmDelete('{{ $voucher->code }}', 'delete-voucher-{{ $voucher->id }}')"
                                                        class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" 
                                                        title="Xóa voucher">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-[#FFF5E6] text-[#E09028] flex items-center justify-center border border-[#EBDDCD]">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                        </div>
                                        <div class="text-base font-bold text-[#2E190E]">Chưa có mã giảm giá nào!</div>
                                        <p class="text-xs text-[#8E8076] mt-1 font-normal">Bấm nút "Tạo Voucher Mới" ở trên để thiết lập chương trình khuyến mãi đầu tiên.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Bar --}}
                @if($vouchers->total() > 0)
                    <div class="px-5 py-4 border-t border-amber-100 bg-amber-50/20 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
                        <div class="font-medium flex items-center gap-1.5">
                            <span>Hiển thị</span>
                            <span class="font-bold text-amber-950">{{ $vouchers->firstItem() ?? 0 }}</span>
                            <span>–</span>
                            <span class="font-bold text-amber-950">{{ $vouchers->lastItem() ?? 0 }}</span>
                            <span>trong tổng số</span>
                            <span class="font-bold text-amber-950">{{ $vouchers->total() }}</span>
                            <span>mã voucher (10 mã / trang)</span>
                        </div>

                        @if($vouchers->hasPages())
                            <div class="voucher-pagination">
                                {{ $vouchers->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

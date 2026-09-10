@extends('layouts.customer')

@section('title', 'Kho Voucher & Khuyến Mãi - Mật Ngọt Bear')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/customer-vouchers.css') }}?v={{ file_exists(public_path('css/customer-vouchers.css')) ? filemtime(public_path('css/customer-vouchers.css')) : time() }}">
@endsection

@section('content')
<div class="py-5 sm:py-7 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-24 font-sans"
     x-data="{
         copiedCode: null,
         detailModalOpen: false,
         detailVoucher: null,
         openDetailModal(voucher) {
             this.detailVoucher = voucher;
             this.detailModalOpen = true;
             document.body.style.overflow = 'hidden';
         },
         closeDetailModal() {
             this.detailModalOpen = false;
             document.body.style.overflow = '';
             setTimeout(() => {
                 if (!this.detailModalOpen) this.detailVoucher = null;
             }, 200);
         },
         copyVoucher(code) {
             navigator.clipboard.writeText(code).then(() => {
                 this.copiedCode = code;
                 if (typeof Toast !== 'undefined') {
                     Toast.fire({
                         icon: 'success',
                         title: 'Đã sao chép mã: ' + code
                     });
                 }
                 setTimeout(() => {
                     if (this.copiedCode === code) this.copiedCode = null;
                 }, 2500);
             });
         }
     }">
    <div class="max-w-6xl mx-auto">
        {{-- Staff Special Consultation Banner --}}
        @if(auth()->check() && auth()->user()->role === 'STAFF')
            <div class="mb-4 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-300 rounded-2xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#E08A1E] text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <div class="font-bold text-xs sm:text-sm text-[#5D4037] flex items-center gap-1.5">
                            <span>Chế độ Nhân viên tư vấn khách hàng</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#E08A1E] text-white uppercase tracking-wider">STAFF</span>
                        </div>
                        <p class="text-[11.5px] text-[#7D6B5D] mt-0.5 leading-relaxed">
                            Bấm nút <strong class="text-[#E08A1E] underline cursor-pointer">"Điều kiện"</strong> trên mỗi voucher để tra cứu chi tiết mức giảm, đơn tối thiểu, số lượt còn lại và danh mục/sản phẩm áp dụng để tư vấn cho khách.
                        </p>
                    </div>
                </div>
                <a href="{{ route('staff.dashboard') }}" class="shrink-0 text-xs font-semibold px-3 py-1.5 rounded-xl bg-white hover:bg-amber-100/60 border border-amber-200 text-[#7D6B5D] transition flex items-center gap-1 justify-center">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Về Dashboard Staff</span>
                </a>
            </div>
        @endif

        {{-- Hero Banner Section with Image --}}
        <div class="relative rounded-2xl overflow-hidden mb-5 shadow-md border border-[#D4A373]/30 text-white p-5 sm:p-7 min-h-[190px] sm:min-h-[215px] flex items-center bg-[#A06E4A]">
            {{-- Background Banner Image: 100% continuous and seamless --}}
            <img src="{{ asset('images/vouchers/voucher-hero-banner-v2.png') }}?v={{ time() }}" 
                 alt="Kho Voucher & Ưu Đãi Mật Ngọt Bear" 
                 class="absolute inset-0 w-full h-full object-cover object-right select-none pointer-events-none" />

            <div class="relative z-10 max-w-lg md:max-w-xl">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-black/25 border border-white/20 text-amber-200 text-[11px] font-semibold mb-2 backdrop-blur-xs shadow-xs">
                    <span>✨ Săn Deal Mật Ngọt</span>
                    <span>·</span>
                    <span>Tiết Kiệm Mỗi Ngày</span>
                </div>
                
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white mb-1.5 drop-shadow-[0_2px_4px_rgba(0,0,0,0.4)]">
                    Kho Voucher &amp; Ưu Đãi Độc Quyền
                </h1>
                
                <p class="text-xs text-white/95 leading-relaxed max-w-md sm:max-w-lg drop-shadow-[0_1px_3px_rgba(0,0,0,0.4)] font-medium">
                    Thu thập mã giảm giá mua gấu bông và mã miễn phí vận chuyển. Đón chờ các đợt mở mã sắp diễn ra để không bỏ lỡ ưu đãi!
                </p>

                {{-- Quick Metrics Pills --}}
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    <div class="bg-black/35 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/20 flex items-center gap-1.5 text-[11.5px] shadow-xs">
                        <span class="text-amber-300">🎁</span>
                        <span class="text-white/95"><span class="font-bold text-white">{{ $counts['available'] }}</span> mã đang mở</span>
                    </div>
                    <div class="bg-black/35 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/20 flex items-center gap-1.5 text-[11.5px] shadow-xs">
                        <span class="text-teal-200">⏰</span>
                        <span class="text-white/95"><span class="font-bold text-white">{{ $counts['upcoming'] }}</span> mã sắp tới</span>
                    </div>
                    @if($isAuthenticated)
                        <div class="bg-black/35 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/20 flex items-center gap-1.5 text-[11.5px] shadow-xs">
                            <span class="text-emerald-300">✓</span>
                            <span class="text-white/95">Đã dùng <span class="font-bold text-white">{{ $counts['used'] }}</span> mã</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="voucher-toolbar">
            <div class="voucher-toolbar-inner">
                
                {{-- Status Tabs: Tất cả | Sắp diễn ra | Khả dụng | Đã sử dụng --}}
                <div class="voucher-tabs-group shrink-0">
                    {{-- Tab 1: Tất cả --}}
                    <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['tab' => 'all'])) }}"
                       class="voucher-tab-link voucher-tab-all {{ $currentTab === 'all' ? 'is-active' : '' }}">
                        <span>Tất cả</span>
                        <span class="voucher-tab-badge">{{ $counts['all'] }}</span>
                    </a>

                    {{-- Tab 2: Sắp diễn ra (Nổi bật) --}}
                    <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}"
                       class="voucher-tab-link voucher-tab-upcoming {{ $currentTab === 'upcoming' ? 'is-active' : '' }}">
                        <span class="voucher-tab-icon">⏰</span>
                        <span>Sắp diễn ra</span>
                        <span class="voucher-tab-badge">{{ $counts['upcoming'] }}</span>
                    </a>

                    {{-- Tab 3: Khả dụng --}}
                    <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['tab' => 'available'])) }}"
                       class="voucher-tab-link voucher-tab-available {{ $currentTab === 'available' ? 'is-active' : '' }}">
                        <span class="voucher-tab-icon">✨</span>
                        <span>Khả dụng</span>
                        <span class="voucher-tab-badge">{{ $counts['available'] }}</span>
                    </a>

                    {{-- Tab 4: Đã sử dụng --}}
                    @if($isAuthenticated)
                        <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['tab' => 'used'])) }}"
                           class="voucher-tab-link voucher-tab-used {{ $currentTab === 'used' ? 'is-active' : '' }}">
                            <span class="voucher-tab-icon">✓</span>
                            <span>Đã dùng</span>
                            <span class="voucher-tab-badge">{{ $counts['used'] }}</span>
                        </a>
                    @else
                        <button type="button" onclick="openAuthModal('{{ route('customer.vouchers.index', ['tab' => 'used']) }}', 'Đăng nhập xem lịch sử mã', 'Vui lòng đăng nhập để xem danh sách các mã bạn đã áp dụng!')"
                                class="voucher-tab-link voucher-tab-used opacity-75" title="Đăng nhập để xem mã đã dùng">
                            <span class="voucher-tab-icon">✓</span>
                            <span>Đã dùng</span>
                            <span class="voucher-tab-lock-badge">Khóa</span>
                        </button>
                    @endif
                </div>

                {{-- Type Filter & Cute Search Bar --}}
                <div class="voucher-right-controls flex items-center gap-2 sm:gap-2.5 shrink-0 flex-nowrap">
                    {{-- Type Filter (Tất cả / Đơn hàng / Vận chuyển) --}}
                    <div class="voucher-type-switch shrink-0">
                        <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['type' => 'all'])) }}"
                           class="voucher-type-pill {{ $currentType === 'all' ? 'is-active' : '' }}">
                            Tất cả
                        </a>
                        <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['type' => 'ORDER'])) }}"
                           class="voucher-type-pill {{ $currentType === 'ORDER' ? 'is-active-order' : '' }}">
                            🛍️ Đơn hàng
                        </a>
                        <a href="{{ route('customer.vouchers.index', array_merge(request()->query(), ['type' => 'SHIPPING'])) }}"
                           class="voucher-type-pill {{ $currentType === 'SHIPPING' ? 'is-active-shipping' : '' }}">
                            🚚 Vận chuyển
                        </a>
                    </div>

                    {{-- Cute Search Bar with warm bear styling --}}
                    <form action="{{ route('customer.vouchers.index') }}" method="GET" class="voucher-search-form shrink-0">
                        <input type="hidden" name="tab" value="{{ $currentTab }}">
                        <input type="hidden" name="type" value="{{ $currentType }}">
                        
                        <div class="voucher-search-box">
                            <div class="voucher-search-icon-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}"
                                   placeholder="Tìm mã voucher..." 
                                   class="voucher-search-input focus:outline-none! focus:ring-0! focus:border-transparent!"
                                   style="outline: none !important; box-shadow: none !important; border: none !important;"
                                   autocomplete="off">
                            @if($search)
                                <a href="{{ route('customer.vouchers.index', ['tab' => $currentTab, 'type' => $currentType]) }}"
                                   class="voucher-search-clear" 
                                   title="Xóa tìm kiếm">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                            <button type="submit" class="voucher-search-btn" title="Tìm kiếm">
                                <span>Tìm</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            {{-- Active search indicator pill if searching --}}
            @if($search)
                <div class="mt-2.5 pt-2.5 border-t border-[#F0E2D2]/70 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5 text-[#7D6B5D]">
                        <span>Kết quả tìm kiếm cho:</span>
                        <span class="font-bold text-[#E08A1E] bg-[#FFF0DC] px-2 py-0.5 rounded-md border border-[#F6D89B] font-mono">
                            "{{ $search }}"
                        </span>
                        <span class="text-[11px]">({{ $vouchers->count() }} voucher)</span>
                    </div>
                    <a href="{{ route('customer.vouchers.index', ['tab' => $currentTab, 'type' => $currentType]) }}"
                       class="text-[#C2751D] hover:text-[#9A560F] font-semibold flex items-center gap-1 hover:underline">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        <span>Xem tất cả</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- Vouchers Grid --}}
        @if($vouchers->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl border border-[#F0E2D2] p-8 text-center max-w-sm mx-auto shadow-xs my-8">
                <div class="w-14 h-14 rounded-full bg-[#FFF0DC] text-amber-600 flex items-center justify-center text-2xl mx-auto mb-3">
                    🧸
                </div>
                <h3 class="text-sm font-semibold text-[#2B1810] mb-1">Không tìm thấy voucher phù hợp</h3>
                <p class="text-xs text-[#7D6B5D] mb-4 leading-relaxed">
                    @if($search)
                        Không có voucher nào khớp với từ khóa "{{ $search }}".
                    @elseif($currentTab === 'upcoming')
                        Hiện chưa có ưu đãi sắp diễn ra. Bạn hãy quay lại sau nhé!
                    @elseif($currentTab === 'used')
                        Bạn chưa sử dụng voucher nào trong các đơn hàng gần đây.
                    @else
                        Hiện chưa có mã giảm giá nào thuộc danh mục này.
                    @endif
                </p>
                <a href="{{ route('customer.vouchers.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#E08A1E] hover:bg-[#C2751D] text-white text-xs font-medium rounded-lg shadow-xs transition">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Xem tất cả voucher</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($vouchers as $voucher)
                    @php
                        $isShipping = $voucher->voucher_type === 'SHIPPING';
                        $isPercent = in_array($voucher->discount_type, ['PERCENT', 'PERCENTAGE']);
                        
                        // Accurate discount text & subtext from database
                        if ($isPercent) {
                            $discountDisplay = (int) $voucher->discount_value . '%';
                            $discountSubtext = ($voucher->max_discount_value > 0)
                                ? 'Tối đa ' . number_format($voucher->max_discount_value, 0, ',', '.') . 'đ'
                                : 'Giảm theo %';
                        } else {
                            $discountDisplay = number_format($voucher->discount_value, 0, ',', '.') . 'đ';
                            $discountSubtext = 'Giảm trực tiếp';
                        }
                        
                        // Fixed color themes for the 2 voucher types (Order vs Shipping)
                        if ($isShipping) {
                            $typeLabel = 'FREESHIP';
                            $typeScope = 'Phí vận chuyển';
                            $primaryColor = '#0D9488';
                        } else {
                            $typeLabel = 'GIẢM GIÁ';
                            $typeScope = 'Đơn hàng';
                            $primaryColor = '#E08A1E';
                        }

                        $voucherDetailData = [
                            'id' => $voucher->id,
                            'code' => $voucher->code,
                            'type' => $voucher->voucher_type,
                            'type_label' => $isShipping ? 'Miễn phí vận chuyển (Freeship)' : 'Giảm giá đơn hàng',
                            'discount_display' => $discountDisplay,
                            'discount_subtext' => $discountSubtext,
                            'min_order' => number_format($voucher->min_order_value ?? 0, 0, ',', '.') . 'đ',
                            'max_discount' => ($isPercent && ($voucher->max_discount_value ?? 0) > 0) ? number_format($voucher->max_discount_value, 0, ',', '.') . 'đ' : null,
                            'start_date' => $voucher->start_date->format('H:i d/m/Y'),
                            'end_date' => $voucher->end_date->format('H:i d/m/Y'),
                            'is_upcoming' => (bool) $voucher->is_upcoming,
                            'is_expired' => (bool) $voucher->is_expired,
                            'is_available' => (bool) $voucher->is_available,
                            'is_depleted' => (bool) $voucher->is_depleted,
                            'usage_limit' => $voucher->usage_limit,
                            'used_count' => (int) $voucher->used_count,
                            'remaining_count' => $voucher->usage_limit !== null ? max(0, $voucher->usage_limit - $voucher->used_count) : null,
                            'limit_per_user' => (int) ($voucher->limit_per_user ?? 1),
                            'apply_scope' => $voucher->apply_scope,
                            'apply_scope_label' => match($voucher->apply_scope) {
                                'CATEGORY' => 'Danh mục chỉ định',
                                'PRODUCT' => 'Sản phẩm chỉ định',
                                default => 'Toàn bộ sản phẩm'
                            },
                            'categories' => $voucher->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values(),
                            'products' => $voucher->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => number_format($p->price, 0, ',', '.') . 'đ'])->values(),
                            'copy_url' => route('products.index', ['voucher' => $voucher->code]),
                        ];
                    @endphp

                    <div class="voucher-ticket-card {{ $voucher->is_depleted || ($voucher->is_expired && !$voucher->is_upcoming) ? 'opacity-65 grayscale-[30%]' : '' }}">
                        
                        {{-- Left Coupon Stub (Always 128px, fixed 2-type colors) --}}
                        <div class="voucher-stub {{ $isShipping ? 'voucher-stub-shipping' : 'voucher-stub-order' }}">
                            {{-- Clean SVG Watermark (monochrome white with subtle opacity, avoids pink/color emoji bleed) --}}
                            @if($isShipping)
                                <svg class="voucher-watermark-svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                </svg>
                            @else
                                <svg class="voucher-watermark-svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3h-6c0-1.66 1.34-3 3-3zm7 17H5V8h14v12zm-7-8c-1.66 0-3-1.34-3-3H7c0 2.76 2.24 5 5 5s5-2.24 5-5h-2c0 1.66-1.34 3-3 3z"/>
                                </svg>
                            @endif

                            {{-- Top Tag --}}
                            <div class="w-full">
                                <span class="voucher-stub-tag">
                                    {{ $typeLabel }}
                                </span>
                            </div>

                            {{-- Discount Value --}}
                            <div class="w-full my-auto py-1">
                                <div class="text-xl sm:text-2xl font-bold tracking-tight leading-none text-white">
                                    {{ $discountDisplay }}
                                </div>
                                <div class="text-[9.5px] font-medium text-white/90 tracking-wide mt-1 truncate px-0.5">
                                    {{ $discountSubtext }}
                                </div>
                            </div>

                            {{-- Bottom Subtext --}}
                            <div class="w-full text-[10px] text-white/90 border-t border-white/20 pt-1 font-medium truncate">
                                {{ $typeScope }}
                            </div>
                        </div>

                        {{-- Right Coupon Body (Fixed Ratio & Layout) --}}
                        <div class="voucher-body">
                            
                            {{-- Notches at junction --}}
                            <div class="voucher-notch-top"></div>
                            <div class="voucher-notch-bottom"></div>

                            {{-- 1. Status Label & Voucher Code Header --}}
                            <div class="flex items-center justify-between gap-2 shrink-0" style="height: 24px;">
                                {{-- Status Flag --}}
                                @if($voucher->is_upcoming)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                                        Sắp diễn ra
                                    </span>
                                @elseif($voucher->is_depleted)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-normal bg-rose-50 text-rose-600 border border-rose-200">
                                        Hết lượt dùng
                                    </span>
                                @elseif($voucher->is_expired)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-normal bg-gray-100 text-gray-600 border border-gray-200">
                                        Hết hiệu lực
                                    </span>
                                @elseif($voucher->customer_reached_limit)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-normal bg-amber-50 text-amber-800 border border-amber-200">
                                        ✓ Đã dùng hết
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Khả dụng
                                    </span>
                                @endif

                                {{-- Voucher Code with Copy Button --}}
                                <div class="inline-flex items-center gap-1 bg-[#FAF6EE] border border-[#EBDDCD] px-2 py-0.5 rounded-lg">
                                    <span class="font-mono font-bold text-xs text-[#2B1810] tracking-wide">{{ $voucher->code }}</span>
                                    <button type="button" @click="copyVoucher('{{ $voucher->code }}')"
                                            class="ml-1 text-[10.5px] font-medium transition flex items-center gap-1 cursor-pointer"
                                            style="color: {{ $primaryColor }};">
                                        <template x-if="copiedCode !== '{{ $voucher->code }}'">
                                            <span class="flex items-center gap-0.5 hover:underline">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                <span>Chép</span>
                                            </span>
                                        </template>
                                        <template x-if="copiedCode === '{{ $voucher->code }}'">
                                            <span class="flex items-center gap-0.5 text-emerald-600 font-medium">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Đã chép!</span>
                                            </span>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            {{-- 2. Conditions & Restrictions (Always 2 clean lines) --}}
                            <div class="flex-1 flex flex-col justify-center py-1 text-xs space-y-0.5 min-h-0">
                                {{-- Line 1: Min Order & Max Discount --}}
                                <div class="flex items-baseline gap-1 text-[11.5px] truncate">
                                    <span class="text-[#8C7A6B] shrink-0">Đơn tối thiểu từ </span>
                                    <span class="font-medium text-[#2B1810]">{{ number_format($voucher->min_order_value ?? 0, 0, ',', '.') }}đ</span>
                                    @if($isPercent && ($voucher->max_discount_value ?? 0) > 0)
                                        <span class="text-[#8C7A6B] ml-1 shrink-0"> - Giảm tối đa:</span>
                                        <span class="font-medium text-[#2B1810]">{{ number_format($voucher->max_discount_value, 0, ',', '.') }}đ</span>
                                    @endif
                                </div>

                                {{-- Line 2: Scope --}}
                                <div class="flex items-baseline gap-1 text-[11.5px] truncate">
                                    <span class="text-[#8C7A6B] shrink-0">Áp dụng:</span>
                                    <span class="text-[#2B1810] truncate">
                                        @if($voucher->apply_scope === 'CATEGORY')
                                            Danh mục: {{ $voucher->categories->pluck('name')->join(', ') ?: 'Chỉ định' }}
                                        @elseif($voucher->apply_scope === 'PRODUCT')
                                            Sản phẩm: {{ $voucher->products->pluck('name')->take(2)->join(', ') ?: 'Chỉ định' }}
                                        @else
                                            Toàn bộ sản phẩm
                                        @endif
                                    </span>
                                </div>

                                {{-- Line 3: Chi tiết lượt dùng voucher toàn shop (Progress bar) & Mỗi khách hàng --}}
                                <div class="pt-1.5 pb-0.5 space-y-1.5">
                                    {{-- 1. Thanh progress chạy tổng số lượt dùng toàn hệ thống --}}
                                    @if($voucher->usage_limit)
                                        @php
                                            $percent = $voucher->usage_limit > 0 ? min(100, round(($voucher->used_count / $voucher->usage_limit) * 100)) : 0;
                                        @endphp
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-[11px] leading-none">
                                                <span class="text-[#7D6B5D] flex items-center gap-1">
                                                    <i class="fa-solid fa-fire text-[10px] {{ $percent >= 85 ? 'text-rose-500 animate-pulse' : 'text-[#E08A1E]' }}"></i>
                                                    <span>Đã dùng <strong class="text-[#2B1810] font-bold">{{ $voucher->used_count }}/{{ $voucher->usage_limit }}</strong> lượt</span>
                                                </span>
                                                <span class="font-bold text-[10.5px] {{ $percent >= 85 ? 'text-rose-600' : 'text-[#E08A1E]' }}">
                                                    {{ $percent }}%
                                                </span>
                                            </div>
                                            {{-- Track & Bar --}}
                                            <div class="w-full h-1.5 bg-[#F2E6D8] rounded-full overflow-hidden shadow-inner">
                                                <div class="h-full rounded-full transition-all duration-300 {{ $percent >= 85 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-[#E08A1E] to-[#F59E0B]' }}"
                                                     style="width: {{ $percent > 0 ? max(5, $percent) : 0 }}%;"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between text-[11px] text-[#7D6B5D] leading-none">
                                            <span class="flex items-center gap-1">
                                                <i class="fa-solid fa-ticket text-[10px] text-[#E08A1E]"></i>
                                                <span>Đã dùng <strong class="text-[#2B1810]">{{ $voucher->used_count }}</strong> lượt <span class="text-[10px] text-[#A8988B]">(Không giới hạn)</span></span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- 2. Thông tin lượt dùng của mỗi khách hàng --}}
                                    <div class="flex items-center gap-1.5 text-[10.5px] text-[#7D6B5D] flex-wrap">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-user-check text-[9px] {{ ($isAuthenticated && $voucher->customer_reached_limit) ? 'text-rose-500' : 'text-[#8C7A6B]' }}"></i>
                                            <span>Lượt dùng: <strong class="text-[#2B1810]">{{ $voucher->limit_per_user }} lượt</strong></span>
                                        </span>
                                        @if($isAuthenticated)
                                            <span class="px-1.5 py-0.5 rounded text-[9.5px] font-semibold {{ $voucher->customer_reached_limit ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                                {{ $voucher->customer_reached_limit ? 'Bạn đã hết lượt' : 'Bạn còn ' . max(0, $voucher->limit_per_user - $voucher->customer_used_count) . ' lượt' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Footer Row: Timeline & Action Button --}}
                            <div class="pt-2 border-t border-[#F5E8D8] flex items-center justify-between gap-2 shrink-0" style="height: 32px;">
                                
                                {{-- Time Info & Điều kiện button --}}
                                <div class="flex items-center gap-1.5 truncate text-[10.5px]">
                                    <div class="text-[#8C7A6B] truncate">
                                        @if($voucher->is_upcoming)
                                            <span class="text-teal-700">Mở: <strong class="font-mono font-medium">{{ $voucher->start_date->format('d/m/Y') }}</strong></span>
                                        @else
                                            <span>Hạn: <strong class="font-medium text-[#2B1810] font-mono">{{ $voucher->end_date->format('d/m/Y') }}</strong></span>
                                        @endif
                                    </div>

                                    <span class="text-[#D4C5B9]">·</span>

                                    <button type="button" 
                                            @click="openDetailModal({{ json_encode($voucherDetailData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }})"
                                            class="font-bold text-[#E08A1E] hover:text-[#B36B15] hover:underline flex items-center gap-0.5 cursor-pointer transition shrink-0"
                                            title="Bấm để xem chi tiết điều kiện sử dụng voucher">
                                        <i class="fa-solid fa-circle-info text-[10px]"></i>
                                        <span>Điều kiện</span>
                                    </button>
                                </div>

                                {{-- Action CTA --}}
                                <div class="shrink-0">
                                    @if($voucher->is_upcoming)
                                        <a href="{{ route('products.index', ['voucher' => $voucher->code]) }}"
                                           @click="copyVoucher('{{ $voucher->code }}')"
                                           title="Xem các sản phẩm áp dụng ưu đãi này"
                                           class="voucher-btn {{ $isShipping ? 'voucher-btn-upcoming-shipping' : 'voucher-btn-upcoming-order' }}">
                                            <span>Dùng sau</span>
                                            <span>→</span>
                                        </a>
                                    @elseif($voucher->is_available)
                                        <a href="{{ route('products.index', ['voucher' => $voucher->code]) }}"
                                           @click="copyVoucher('{{ $voucher->code }}')"
                                           title="Xem các sản phẩm áp dụng ưu đãi này"
                                           class="voucher-btn {{ $isShipping ? 'voucher-btn-shipping' : 'voucher-btn-order' }}">
                                            <span>Dùng ngay</span>
                                            <span>→</span>
                                        </a>
                                    @elseif($voucher->customer_reached_limit)
                                        <span class="voucher-btn-disabled">
                                            Hết lượt dùng
                                        </span>
                                    @else
                                        <span class="voucher-btn-disabled">
                                            Không khả dụng
                                        </span>
                                    @endif
                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- Voucher Condition Detail Modal (Hỗ trợ Khách hàng & Nhân viên tư vấn xem toàn bộ điều kiện) --}}
    <div x-show="detailModalOpen"
         x-cloak
         class="fixed inset-0 z-[9999] overflow-y-auto"
         aria-labelledby="voucher-condition-modal-title"
         role="dialog"
         aria-modal="true"
         @keydown.escape.window="closeDetailModal()">
        
        {{-- Backdrop --}}
        <div x-show="detailModalOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
             @click="closeDetailModal()"></div>

        {{-- Modal Dialog --}}
        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
            <div x-show="detailModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-[#FFFDF9] text-left shadow-2xl transition-all w-full max-w-lg border border-[#EBDDCD]"
                 @click.outside="closeDetailModal()">
                
                <template x-if="detailVoucher">
                    <div class="p-5 sm:p-6 space-y-4">
                        {{-- Modal Header --}}
                        <div class="flex items-start justify-between pb-3 border-b border-[#F0E2D2]">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-lg shadow-sm"
                                     :class="detailVoucher.type === 'SHIPPING' ? 'bg-[#0D9488]' : 'bg-[#E08A1E]'">
                                    <i class="fa-solid" :class="detailVoucher.type === 'SHIPPING' ? 'fa-truck-fast' : 'fa-ticket'"></i>
                                </div>
                                <div>
                                    <h3 id="voucher-condition-modal-title" class="text-base font-bold text-[#2B1810]">
                                        Chi Tiết Điều Kiện Voucher
                                    </h3>
                                    <p class="text-xs text-[#7D6B5D]" x-text="detailVoucher.type_label"></p>
                                </div>
                            </div>
                            <button type="button" @click="closeDetailModal()"
                                    class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#7D6B5D] hover:text-[#2B1810] hover:bg-[#F0E2D2] flex items-center justify-center transition cursor-pointer">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        {{-- Voucher Code & Discount Highlight Card --}}
                        <div class="rounded-xl p-3.5 border flex items-center justify-between gap-3"
                             :class="detailVoucher.type === 'SHIPPING' ? 'bg-teal-50/70 border-teal-200' : 'bg-amber-50/70 border-amber-200'">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold"
                                          :class="detailVoucher.type === 'SHIPPING' ? 'text-teal-800' : 'text-amber-900'">
                                        Mã voucher:
                                    </span>
                                    <span class="font-mono font-black text-sm px-2 py-0.5 rounded bg-white border border-[#EBDDCD] text-[#2B1810]"
                                          x-text="detailVoucher.code"></span>
                                </div>
                                <div class="mt-1 text-lg font-extrabold"
                                     :class="detailVoucher.type === 'SHIPPING' ? 'text-teal-700' : 'text-[#E08A1E]'"
                                     x-text="detailVoucher.discount_display + ' (' + detailVoucher.discount_subtext + ')'">
                                </div>
                            </div>
                            <button type="button" 
                                    @click="copyVoucher(detailVoucher.code)"
                                    class="px-3 py-1.5 rounded-lg font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                                    :class="detailVoucher.type === 'SHIPPING' ? 'bg-teal-600 hover:bg-teal-700 text-white' : 'bg-[#E08A1E] hover:bg-[#C2751D] text-white'">
                                <i class="fa-regular fa-copy"></i>
                                <span x-text="copiedCode === detailVoucher.code ? 'Đã chép!' : 'Chép mã'"></span>
                            </button>
                        </div>

                        {{-- Staff Special Consultation Guide Box --}}
                        @if(auth()->check() && auth()->user()->role === 'STAFF')
                            <div class="bg-gradient-to-r from-[#FFF9EE] to-[#FFF3DE] border border-[#F6D89B] rounded-xl p-3 text-xs text-[#7B4707] space-y-1 shadow-2xs">
                                <div class="font-bold flex items-center gap-1.5 text-[#A55F08]">
                                    <i class="fa-solid fa-lightbulb"></i>
                                    <span>Gợi ý tư vấn cho Nhân viên:</span>
                                </div>
                                <p class="leading-relaxed">
                                    Hướng dẫn khách mua đơn từ <strong class="text-[#2B1810]" x-text="detailVoucher.min_order"></strong>, nhập mã <strong class="font-mono text-[#E08A1E]" x-text="detailVoucher.code"></strong> ở bước thanh toán để được <strong class="text-[#2B1810]" x-text="detailVoucher.discount_display"></strong>.
                                    <span x-show="detailVoucher.remaining_count !== null">
                                        Hệ thống hiện còn <strong class="text-rose-600" x-text="detailVoucher.remaining_count"></strong> lượt sử dụng.
                                    </span>
                                </p>
                            </div>
                        @endif

                        {{-- Detailed Conditions List --}}
                        <div class="bg-white rounded-xl border border-[#F0E2D2] divide-y divide-[#F5E8D8] text-xs">
                            {{-- Row: Đơn tối thiểu --}}
                            <div class="flex items-center justify-between p-2.5">
                                <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                    <i class="fa-solid fa-cart-shopping text-[#E08A1E] w-4"></i>
                                    <span>Đơn tối thiểu:</span>
                                </span>
                                <span class="font-bold text-[#2B1810]" x-text="detailVoucher.min_order"></span>
                            </div>

                            {{-- Row: Mức giảm tối đa (nếu có) --}}
                            <template x-if="detailVoucher.max_discount">
                                <div class="flex items-center justify-between p-2.5">
                                    <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                        <i class="fa-solid fa-arrow-down-wide-short text-[#E08A1E] w-4"></i>
                                        <span>Mức giảm tối đa:</span>
                                    </span>
                                    <span class="font-bold text-[#2B1810]" x-text="detailVoucher.max_discount"></span>
                                </div>
                            </template>

                            {{-- Row: Thời gian hiệu lực --}}
                            <div class="flex items-center justify-between p-2.5">
                                <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[#E08A1E] w-4"></i>
                                    <span>Hiệu lực từ:</span>
                                </span>
                                <span class="font-mono font-medium text-[#2B1810]" x-text="detailVoucher.start_date + ' đến ' + detailVoucher.end_date"></span>
                            </div>

                            {{-- Row: Lượt dùng toàn hệ thống --}}
                            <div class="flex items-center justify-between p-2.5">
                                <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                    <i class="fa-solid fa-fire text-[#E08A1E] w-4"></i>
                                    <span>Lượt dùng toàn shop:</span>
                                </span>
                                <span class="font-semibold text-[#2B1810]">
                                    <span x-text="'Đã dùng ' + detailVoucher.used_count"></span>
                                    <span x-show="detailVoucher.usage_limit" x-text="' / ' + detailVoucher.usage_limit + ' lượt'"></span>
                                    <span x-show="!detailVoucher.usage_limit">(Không giới hạn)</span>
                                </span>
                            </div>

                            {{-- Row: Giới hạn mỗi khách hàng --}}
                            <div class="flex items-center justify-between p-2.5">
                                <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-tag text-[#E08A1E] w-4"></i>
                                    <span>Lượt dùng mỗi khách:</span>
                                </span>
                                <span class="font-semibold text-[#2B1810]" x-text="detailVoucher.limit_per_user + ' lượt/khách hàng'"></span>
                            </div>

                            {{-- Row: Phạm vi áp dụng --}}
                            <div class="p-2.5 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                        <i class="fa-solid fa-boxes-stacked text-[#E08A1E] w-4"></i>
                                        <span>Phạm vi áp dụng:</span>
                                    </span>
                                    <span class="font-bold text-[#2B1810]" x-text="detailVoucher.apply_scope_label"></span>
                                </div>

                                {{-- Categories list if apply_scope === 'CATEGORY' --}}
                                <template x-if="detailVoucher.apply_scope === 'CATEGORY' && detailVoucher.categories && detailVoucher.categories.length > 0">
                                    <div class="pt-1 flex flex-wrap gap-1.5 pl-5">
                                        <template x-for="cat in detailVoucher.categories" :key="cat.id">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 text-xs font-semibold">
                                                <span>🧸</span>
                                                <span x-text="cat.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </template>

                                {{-- Products list if apply_scope === 'PRODUCT' --}}
                                <template x-if="detailVoucher.apply_scope === 'PRODUCT' && detailVoucher.products && detailVoucher.products.length > 0">
                                    <div class="pt-1 space-y-1 pl-5 max-h-36 overflow-y-auto">
                                        <template x-for="prod in detailVoucher.products" :key="prod.id">
                                            <div class="flex items-center justify-between py-1 border-b border-[#F5E8D8]/50 text-[11.5px]">
                                                <span class="text-[#2B1810] font-medium truncate" x-text="prod.name"></span>
                                                <span class="text-[#E08A1E] font-mono font-bold shrink-0 ml-2" x-text="prod.price"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            {{-- Row: Phương thức thanh toán --}}
                            <div class="flex items-center justify-between p-2.5">
                                <span class="text-[#7D6B5D] flex items-center gap-1.5">
                                    <i class="fa-solid fa-credit-card text-[#E08A1E] w-4"></i>
                                    <span>Thanh toán áp dụng:</span>
                                </span>
                                <span class="font-medium text-[#2B1810]">Mọi hình thức (COD, QR, Ví điện tử)</span>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="text-[11px] text-[#8C7A6B] bg-[#FAF6EE] p-3 rounded-xl border border-[#EBDDCD] leading-relaxed space-y-1">
                            <div class="font-bold text-[#5D4037] flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation text-[10px] text-[#E08A1E]"></i>
                                <span>Lưu ý khi sử dụng:</span>
                            </div>
                            <ul class="list-disc pl-4 space-y-0.5">
                                <li>Mỗi đơn hàng được áp dụng đồng thời tối đa 01 Voucher Đơn hàng và 01 Voucher Vận chuyển.</li>
                                <li>Voucher không có giá trị quy đổi thành tiền mặt hoặc chuyển nhượng.</li>
                                <li>Nếu đơn hàng bị hủy hoặc hoàn trả hợp lệ, voucher sẽ tự động được hoàn lại nếu còn thời hạn.</li>
                            </ul>
                        </div>

                        {{-- Modal Footer Actions --}}
                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="closeDetailModal()"
                                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#7D6B5D] bg-white hover:bg-[#F2DECA] border border-[#EBDDCD] transition cursor-pointer">
                                Đóng
                            </button>
                            <a :href="detailVoucher.copy_url"
                               @click="copyVoucher(detailVoucher.code)"
                               class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                               :class="detailVoucher.type === 'SHIPPING' ? 'bg-[#0D9488] hover:bg-[#047857]' : 'bg-[#E08A1E] hover:bg-[#C2751D]'">
                                <span>Áp dụng mua sắm</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.customer')

@section('title', 'Kho Voucher & Khuyến Mãi - Mật Ngọt Bear')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/customer-vouchers.css') }}?v={{ file_exists(public_path('css/customer-vouchers.css')) ? filemtime(public_path('css/customer-vouchers.css')) : time() }}">
@endsection

@section('content')
<div class="py-5 sm:py-7 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-24 font-sans"
     x-data="{
         copiedCode: null,
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
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
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
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                {{-- Status Tabs: Tất cả | Sắp diễn ra | Khả dụng | Đã sử dụng --}}
                <div class="voucher-tabs-group">
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
                <div class="flex flex-wrap items-center gap-2.5">
                    {{-- Type Filter (Tất cả / Đơn hàng / Vận chuyển) --}}
                    <div class="voucher-type-switch">
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
                    <form action="{{ route('customer.vouchers.index') }}" method="GET" class="voucher-search-form">
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
                                   class="voucher-search-input"
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

                                {{-- Line 2: Scope & Used badge --}}
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
                                    @if($isAuthenticated && $voucher->customer_used_count > 0)
                                        <span class="text-[10px] text-amber-700 bg-amber-50 px-1 py-0.2 rounded border border-amber-200/50 shrink-0 ml-1">
                                            Đã dùng: {{ $voucher->customer_used_count }}/{{ $voucher->limit_per_user }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- 3. Footer Row: Timeline & Action Button --}}
                            <div class="pt-2 border-t border-[#F5E8D8] flex items-center justify-between gap-2 shrink-0" style="height: 32px;">
                                
                                {{-- Time Info --}}
                                <div class="truncate">
                                    @if($voucher->is_upcoming)
                                        <div class="text-[10.5px] text-teal-700 flex items-center gap-1 truncate">
                                            <span class="shrink-0">Mở từ:</span>
                                            <span class="font-mono font-medium truncate">{{ $voucher->start_date->format('H:i d/m/Y') }}</span>
                                        </div>
                                    @else
                                        <div class="text-[10.5px] text-[#8C7A6B] truncate">
                                            Hạn dùng: <span class="font-medium text-[#2B1810] font-mono">{{ $voucher->end_date->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
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
</div>
@endsection

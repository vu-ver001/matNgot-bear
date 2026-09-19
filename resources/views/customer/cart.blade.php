@extends('layouts.customer')

@section('title', 'Giỏ Hàng Của Bạn - Mật Ngọt Bear')

@section('content')
    <style>
        .mn-cart-bottom-bar {
            border-radius: 18px !important;
        }
        .mn-cart-items-list {
            display: flex;
            flex-direction: column;
            gap: 8px !important;
        }
        .mn-btn-buy {
            background-color: #BF5832 !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            font-size: 14px !important;
            letter-spacing: 0.02em;
            border-radius: 14px !important;
            padding: 12px 28px !important;
            box-shadow: 0 4px 14px rgba(191, 88, 50, 0.3) !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            border: none !important;
        }
        .mn-btn-buy:hover:not(:disabled) {
            background-color: #A94824 !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(191, 88, 50, 0.4) !important;
        }
        .mn-btn-buy:disabled {
            background-color: #D9CBC2 !important;
            color: #FFFFFF !important;
            opacity: 0.6 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
            transform: none !important;
        }
        .mn-btn-voucher-repo {
            background-color: #FFF3EC !important;
            color: #BA542D !important;
            border: 1px solid #FADACD !important;
            border-radius: 9999px !important;
            padding: 7px 16px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.2s ease !important;
        }
        .mn-btn-voucher-repo:hover {
            background-color: #FFE8DD !important;
            border-color: #F7C6B3 !important;
        }
        .mn-cart-check.is-active {
            background-color: #844B27 !important;
            border-color: #844B27 !important;
            color: #FFFFFF !important;
        }
        .mn-cart-check:not(.is-active) {
            background-color: #FFFFFF !important;
            border: 2px solid #D1C4B5 !important;
        }
        .mn-cart-check:not(.is-active):hover {
            border-color: #844B27 !important;
        }
        @media (min-width: 768px) {
            .mn-cart-header-cols {
                display: flex !important;
            }
            .mn-cart-item-actions {
                border-top: none !important;
                padding-top: 0 !important;
            }
        }
        @media (max-width: 767px) {
            .mn-cart-header-cols {
                display: none !important;
            }
        }
    </style>

    {{-- Main Container --}}
    <div class="pt-4 sm:pt-6 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-36 font-sans" x-data="cartComponent({{ json_encode(
        $cartItems->map(function ($item) {
            $price = (float) $item->effective_price;
            $rawImg = $item->effective_image;
            if (!empty($rawImg)) {
                $imgUrl = (str_starts_with($rawImg, 'http') || str_starts_with($rawImg, 'data:'))
                    ? $rawImg
                    : asset(ltrim($rawImg, '/'));
            } else {
                $imgUrl = asset('images/customer/product-placeholder.png');
            }

            $variants = ($item->product && $item->product->variants)
                ? $item->product->variants->map(function ($v) use ($imgUrl) {
                    $vImg = !empty($v->image_url)
                        ? ((str_starts_with($v->image_url, 'http') || str_starts_with($v->image_url, 'data:')) ? $v->image_url : asset($v->image_url))
                        : $imgUrl;
                    return [
                        'id' => $v->id,
                        'color' => $v->color,
                        'size' => $v->size,
                        'price' => (float) $v->price,
                        'sale_price' => (float) ($v->sale_price ?? 0),
                        'effective_price' => (float) $v->effective_price,
                        'stock_quantity' => (int) $v->stock_quantity,
                        'image_url' => $vImg,
                    ];
                })->values()->all()
                : [];

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'name' => $item->product->name,
                'variant_display' => $item->variant ? ($item->variant->color . ' · ' . $item->variant->size) : null,
                'quantity' => $item->quantity,
                'stock_quantity' => (int) $item->effective_stock,
                'unit_price' => $price,
                'line_total' => (float) ($price * $item->quantity),
                'image_url' => $imgUrl,
                'available_variants' => $variants,
            ];
        }),
    ) }})">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- 1. Page Title Section --}}
            <div class="flex items-center gap-3.5 mb-5 justify-between">
                <div class="flex items-center gap-3">
                    <x-bear-cart-icon />
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-[#2C1408] tracking-tight font-bold">Giỏ hàng Mật ngọt Bear</h1>
                        <p class="text-xs font-semibold text-[#786B61] mt-0.5">Kiểm tra danh sách gấu bông bạn đã chọn trước khi thanh toán</p>
                    </div>
                </div>
                {{-- Cart Pill Counter on Right (Matching Mockup) --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#F2CBB2] bg-[#FFF8F2] text-[#2B1810] font-bold text-xs sm:text-[13px] shadow-2xs shrink-0 select-none">
                    <svg class="w-4 h-4 text-[#BD551A]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-text="items.length + ' sản phẩm trong giỏ'">{{ $cartItems->count() }} sản phẩm trong giỏ</span>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 p-2.5 bg-[#FAF6F0] border-2 border-emerald-300/80 rounded-2xl shadow-xs text-emerald-900 flex items-center justify-between"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500 text-white text-xs shadow-xs shrink-0">
                            ✓
                        </span>
                        <span class="font-semibold text-sm">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-[#8E8076] hover:text-[#2E190E] p-1 transition">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-2.5 bg-[#FAF6F0] border-2 border-rose-300/80 rounded-2xl shadow-xs text-rose-900 flex items-center justify-between"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-500 text-white text-xs shadow-xs shrink-0">
                            ✕
                        </span>
                        <span class="font-semibold text-sm">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-[#8E8076] hover:text-[#2E190E] p-1 transition">&times;</button>
                </div>
            @endif

            @if ($cartItems->isEmpty())
                {{-- Empty Cart State (Content-fitted width) --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 text-center shadow-xs border border-[#F0E6D8] max-w-2xl mx-auto my-6">
                    {{-- Cute Circle Icon with fixed dimensions so it never deforms --}}
                    <div class="relative mx-auto mb-4 flex items-center justify-center" style="width: 104px; height: 104px; min-width: 104px; min-height: 104px;">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-[#FFF3D6] via-[#FFF9ED] to-[#FFFDF9] border-2 border-[#FDE68A] shadow-inner flex items-center justify-center" style="width: 104px; height: 104px; border-radius: 9999px;">
                            <span class="text-5xl select-none transform hover:scale-110 transition duration-300">🧸</span>
                        </div>
                        <span class="absolute -top-1 -right-1 text-base select-none animate-bounce" style="animation-duration: 2.2s;">✨</span>
                        <span class="absolute -bottom-1 -left-1 text-base select-none animate-pulse">💕</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FFF9EE] border border-[#FDE68A] text-[#C2751D] text-xs font-bold mb-3 shadow-2xs">
                        <span>🍯</span>
                        <span>Giỏ hàng đang chờ bé gấu đầu tiên</span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-[#2C1408] mb-2 tracking-tight">
                        Giỏ hàng của bạn đang trống!
                    </h2>
                    <p class="text-[#786B61] mb-6 leading-relaxed font-medium text-xs sm:text-sm max-w-md mx-auto">
                        Hãy chọn ngay những chú gấu bông dễ thương và quà tặng ngọt ngào từ Mật Ngọt Bear để sưởi ấm giỏ hàng nhé.
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#E08A1E] to-[#C2751D] hover:from-[#C2751D] hover:to-[#A35D12] text-white font-extrabold text-sm py-3 px-6 sm:px-7 rounded-xl shadow-md shadow-[#E08A1E]/25 transition transform hover:-translate-y-0.5 active:translate-y-0">
                            <span>Khám phá sản phẩm ngay</span>
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                        <a href="{{ route('customer.vouchers.index') }}"
                            class="inline-flex items-center justify-center gap-2 bg-[#FFF9EE] hover:bg-[#FFF3DD] text-[#C2751D] hover:text-[#9A4A0A] font-bold text-sm py-3 px-5 rounded-xl border border-[#FDE68A] transition shadow-2xs">
                            <span>🎟️</span>
                            <span>Săn voucher giảm giá</span>
                        </a>
                    </div>

                    {{-- 3 Sweet Perks --}}
                    <div class="pt-6 border-t border-[#F0E6D8] grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 text-left">
                        <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-[#FAF6EE]/80 border border-[#F0E6D8]">
                            <span class="text-xl">🎁</span>
                            <div>
                                <div class="text-xs font-bold text-[#2C1408]">Gói quà miễn phí</div>
                                <div class="text-[11px] text-[#786B61]">Tặng thiệp & thắt nơ xinh</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-[#FAF6EE]/80 border border-[#F0E6D8]">
                            <span class="text-xl">🚚</span>
                            <div>
                                <div class="text-xs font-bold text-[#2C1408]">Giao hàng nhanh</div>
                                <div class="text-[11px] text-[#786B61]">Freeship đơn từ 500.000đ</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-[#FAF6EE]/80 border border-[#F0E6D8]">
                            <span class="text-xl">🧸</span>
                            <div>
                                <div class="text-xs font-bold text-[#2C1408]">Bông gòn 100% êm</div>
                                <div class="text-[11px] text-[#786B61]">An toàn, không xẹp lún</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Suggested Products Section --}}
                @if(isset($suggestedProducts) && $suggestedProducts->isNotEmpty())
                    <div class="mt-8 w-full">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-[#2C1408] flex items-center gap-2">
                                    <span>🧸</span>
                                    <span>Gợi ý gấu bông được yêu thích</span>
                                </h3>
                                <p class="text-xs text-[#786B61] font-medium mt-0.5">Những bé gấu bông xinh xắn đang chờ bạn rước về</p>
                            </div>
                            <a href="{{ route('products.index') }}" class="text-xs font-bold text-[#E08A1E] hover:text-[#C2751D] hover:underline flex items-center gap-1">
                                <span>Xem tất cả</span>
                                <span>→</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            @foreach($suggestedProducts as $prod)
                                @php
                                    $pImg = $prod->images->firstWhere('is_primary', true) ?? $prod->images->first();
                                    $pImgUrl = $pImg 
                                        ? ((str_starts_with($pImg->image_url, 'data:') || str_starts_with($pImg->image_url, 'http')) ? $pImg->image_url : asset($pImg->image_url)) 
                                        : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=400&q=80';
                                    $pPrice = $prod->sale_price ?? $prod->price;
                                    $pHasDiscount = !empty($prod->sale_price) && $prod->sale_price < $prod->price;
                                    $pDiscount = $pHasDiscount && $prod->price > 0 ? round((($prod->price - $prod->sale_price) / $prod->price) * 100) : 0;
                                @endphp
                                <a href="{{ route('products.show', $prod->id) }}" 
                                   class="bg-white rounded-2xl p-3 border border-[#F0E6D8] hover:border-[#E08A1E] shadow-2xs hover:shadow-md transition group flex flex-col">
                                    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-[#FAF6EE] mb-2.5">
                                        <img src="{{ $pImgUrl }}" alt="{{ $prod->name }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-300">
                                        @if($pHasDiscount)
                                            <span class="absolute top-1.5 left-1.5 bg-rose-500 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-2xs">
                                                -{{ $pDiscount }}%
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-[#C2751D] bg-[#FFF9EE] px-2 py-0.5 rounded-md inline-block w-fit mb-1 border border-[#FDE68A]/60">
                                        {{ $prod->category->name ?? 'Gấu bông' }}
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-bold text-[#2C1408] group-hover:text-[#E08A1E] transition line-clamp-2 mb-1.5 flex-1 leading-snug">
                                        {{ $prod->name }}
                                    </h4>
                                    <div class="flex items-baseline gap-1.5 mt-auto">
                                        <span class="text-xs sm:text-sm font-extrabold text-[#E08A1E]">
                                            {{ number_format($pPrice, 0, ',', '.') }}đ
                                        </span>
                                        @if($pHasDiscount)
                                            <span class="text-[10px] text-[#A89A8E] line-through">
                                                {{ number_format($prod->price, 0, ',', '.') }}đ
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <form id="checkoutForm" action="{{ route('customer.checkout.index') }}" method="GET" @submit.prevent="handleCheckoutSubmit($event)">

                    {{-- Hidden inputs for selected items --}}
                    <template x-for="itemId in selectedItems" :key="itemId">
                        <input type="hidden" name="selected_items[]" :value="itemId">
                    </template>

                    {{-- 2. Table Header Bar (White rounded card matching mockup) --}}
                    <div class="bg-white rounded-2xl border border-[#F1E5D8] px-5 py-3.5 mb-2.5 flex items-center justify-between shadow-xs">
                        {{-- Left: Checkbox + CHỌN TẤT CẢ --}}
                        <div class="flex items-center gap-3">
                            <button type="button" @click="toggleSelectAll(!isAllSelected)"
                                class="mn-cart-check w-5 h-5 rounded-md flex items-center justify-center transition cursor-pointer shrink-0"
                                :class="isAllSelected ? 'is-active' : ''">
                                <svg x-show="isAllSelected" class="w-3.5 h-3.5 stroke-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <span class="cursor-pointer font-bold text-[#2B1810] hover:text-[#844B27] text-xs sm:text-[13px] tracking-wide uppercase transition select-none"
                                @click="toggleSelectAll(!isAllSelected)">
                                CHỌN TẤT CẢ
                            </span>
                        </div>

                        {{-- Right: Column titles aligned on desktop --}}
                        <div class="mn-cart-header-cols hidden md:flex items-center gap-3 sm:gap-6 text-xs font-bold text-[#7D6B5D] tracking-wider uppercase">
                            <div class="w-28 sm:w-32 text-center">ĐƠN GIÁ</div>
                            <div class="w-32 sm:w-36 text-center">SỐ LƯỢNG</div>
                            <div class="w-32 sm:w-36 text-center">THÀNH TIỀN</div>
                            <div class="w-10"></div>
                        </div>
                    </div>

                    {{-- 3. Cart Items List --}}
                    <div class="mn-cart-items-list mb-28">
                        @foreach ($cartItems as $item)
                            @php
                                $product = $item->product;
                                $variant = $item->variant;
                                $rawImg = $item->effective_image;
                                $imageUrl = (str_starts_with($rawImg, 'http') || str_starts_with($rawImg, 'data:')) ? $rawImg : asset($rawImg);
                                $price = $item->effective_price;
                                $originalPrice = $variant ? $variant->price : $product->price;
                                $hasDiscount = $price < $originalPrice;
                                $discountPercent = $hasDiscount && $originalPrice > 0 
                                    ? round((($originalPrice - $price) / $originalPrice) * 100) 
                                    : 0;
                                $effectiveStock = $item->effective_stock;
                            @endphp

                            <div class="bg-white rounded-2xl p-4 sm:p-5 transition-all duration-200 shadow-xs hover:shadow-md border flex flex-col md:flex-row md:items-center justify-between gap-4"
                                x-show="hasItem({{ $item->id }})"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                :class="isSelected({{ $item->id }}) ? 'border-[#E8CDBB] bg-[#FFFDF9]' : 'border-[#F1E5D8] hover:border-[#E8CDBB]'">

                                {{-- Left Group: Checkbox + Thumbnail + Product Info --}}
                                <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                                    {{-- Custom Brown Checkbox --}}
                                    <button type="button"
                                        @click="toggleItem({{ $item->id }}, !isSelected({{ $item->id }}), '{{ addslashes($product->name) }}')"
                                        class="mn-cart-check w-5 h-5 rounded-md flex items-center justify-center transition cursor-pointer shrink-0"
                                        :class="isSelected({{ $item->id }}) ? 'is-active' : ''">
                                        <svg x-show="isSelected({{ $item->id }})"
                                            class="w-3.5 h-3.5 stroke-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>

                                    {{-- Product Image --}}
                                    <a href="{{ route('products.show', $product->id) }}"
                                        class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border border-[#F0E6D8] shrink-0 group block shadow-2xs overflow-hidden bg-[#FAF6EE]"
                                        title="Xem chi tiết {{ $product->name }}">
                                        <img :src="getItemImageUrl({{ $item->id }}) || '{{ $imageUrl }}'" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover object-center transform transition duration-300 group-hover:scale-105"
                                            onerror="this.src='{{ asset('images/customer/product-placeholder.png') }}'">
                                        @if ($hasDiscount)
                                            <span class="absolute top-1.5 left-1.5 bg-rose-500 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-xs leading-none">
                                                -{{ $discountPercent }}%
                                            </span>
                                        @endif
                                    </a>

                                    {{-- Title, Category Badge & Variant Chip --}}
                                    <div class="flex-1 min-w-0">
                                        {{-- Cute Category Badge --}}
                                        <div class="mb-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 bg-[#FDECE7] text-[#BA5033] text-xs font-bold rounded-full">
                                                <svg class="w-3.5 h-3.5 text-[#BA5033]" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="6" cy="6" r="4"/>
                                                    <circle cx="18" cy="6" r="4"/>
                                                    <circle cx="12" cy="14" r="9"/>
                                                    <circle cx="8.5" cy="12" r="1.5" fill="#FFFFFF"/>
                                                    <circle cx="15.5" cy="12" r="1.5" fill="#FFFFFF"/>
                                                    <ellipse cx="12" cy="16" rx="3" ry="2" fill="#FFFFFF"/>
                                                    <ellipse cx="12" cy="15.5" rx="1.5" ry="1" fill="#BA5033"/>
                                                </svg>
                                                <span>{{ $product->category->name ?? 'Gấu Bông Hoạt Hình' }}</span>
                                            </span>
                                        </div>

                                        {{-- Product Name --}}
                                        <h3 class="font-extrabold text-[#2B1810] text-sm sm:text-base line-clamp-2 hover:text-[#BA5033] transition leading-snug">
                                            <a href="{{ route('products.show', $product->id) }}" title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </a>
                                        </h3>

                                        {{-- Variant Selector Button (Shopee style pill) --}}
                                        <div class="mt-2">
                                            @if ($variant || ($item->product->variants && $item->product->variants->count() > 0))
                                                <button type="button"
                                                    @click="openVariantSelector({{ $item->id }})"
                                                    class="group inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all duration-200 border border-[#F5D8B8] bg-[#FFF9F2] hover:bg-[#FFF2E0] text-[#8C5E32] shadow-2xs hover:shadow-xs cursor-pointer"
                                                    title="Bấm để đổi phân loại sản phẩm">
                                                    <span class="text-[#E08A1E] text-xs">✨</span>
                                                    <span>Phân loại:</span>
                                                    <span class="font-bold text-[#5C3219]"
                                                        x-text="getItemVariantDisplay({{ $item->id }}) || '{{ $variant ? ($variant->color . ' · ' . $variant->size) : 'Chọn phân loại' }}'">
                                                        {{ $variant ? ($variant->color . ' · ' . $variant->size) : 'Chọn phân loại' }}
                                                    </span>
                                                    <svg class="w-3 h-3 text-[#8C5E32] group-hover:translate-y-0.5 transition-transform shrink-0 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            @else
                                                <div class="flex flex-wrap gap-2 text-xs font-semibold text-[#786B61]">
                                                    @if ($product->size)
                                                        <span class="bg-[#F3EDE3] px-2.5 py-1 rounded-md text-[#5C3219]">Size: {{ $product->size }}</span>
                                                    @endif
                                                    @if ($product->color)
                                                        <span class="bg-[#F3EDE3] px-2.5 py-1 rounded-md text-[#5C3219]">Màu: {{ $product->color }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Stock alert --}}
                                        <template x-if="getItemStock({{ $item->id }}) <= 5">
                                            <p class="text-xs font-bold text-rose-500 mt-1.5 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                <span x-show="getItemStock({{ $item->id }}) <= 0">Phân loại này hiện đang tạm hết hàng</span>
                                                <span x-show="getItemStock({{ $item->id }}) > 0">Chỉ còn <span x-text="getItemStock({{ $item->id }})"></span> sản phẩm trong kho</span>
                                            </p>
                                        </template>
                                    </div>
                                </div>

                                {{-- Right Group: Price, Quantity Stepper, Total, Delete Button --}}
                                <div class="mn-cart-item-actions flex items-center justify-between md:justify-end gap-3 sm:gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-[#F5EBE1]">
                                    {{-- Unit Price --}}
                                    <div class="w-28 sm:w-32 text-left md:text-center">
                                        <span class="text-[11px] text-[#A8988A] block md:hidden font-medium">Đơn giá:</span>
                                        <div class="font-extrabold text-[#D95F16] text-base sm:text-lg"
                                            x-text="formatVND(getItemPrice({{ $item->id }}))">
                                            {{ number_format($price, 0, ',', '.') }}đ
                                        </div>
                                        @if ($hasDiscount)
                                            <div class="text-[11px] text-[#A8988A] line-through font-normal">
                                                {{ number_format($originalPrice, 0, ',', '.') }}đ
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Capsule Quantity Stepper (Exact Mockup Style) --}}
                                    <div class="w-32 sm:w-36 flex justify-center">
                                        <div class="inline-flex items-center justify-between bg-[#FAF5F1] border border-[#F2DFD0] rounded-full px-2.5 py-1 w-28 sm:w-32 shadow-2xs">
                                            <button type="button"
                                                @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) - 1)"
                                                :disabled="getItemQuantity({{ $item->id }}) <= 1"
                                                class="w-7 h-7 rounded-full flex items-center justify-center text-[#7D6B5D] hover:text-[#2B1810] font-bold text-base transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">
                                                −
                                            </button>

                                            <span class="font-bold text-[#2B1810] text-sm w-6 text-center select-none"
                                                x-text="getItemQuantity({{ $item->id }})">
                                                {{ $item->quantity }}
                                            </span>

                                            <button type="button"
                                                @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) + 1)"
                                                :disabled="getItemQuantity({{ $item->id }}) >= getItemStock({{ $item->id }})"
                                                class="w-7 h-7 rounded-full flex items-center justify-center text-[#7D6B5D] hover:text-[#2B1810] font-bold text-base transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Line Total --}}
                                    <div class="w-32 sm:w-36 text-right md:text-center">
                                        <span class="text-[11px] text-[#A8988A] block md:hidden font-medium">Thành tiền:</span>
                                        <div class="font-extrabold text-[#D95F16] text-base sm:text-lg"
                                            x-text="formatVND(getItemLineTotal({{ $item->id }}))">
                                            {{ number_format($price * $item->quantity, 0, ',', '.') }}đ
                                        </div>
                                    </div>

                                    {{-- Outline Trash Button with Lid --}}
                                    <div class="w-10 flex justify-end">
                                        <button type="button"
                                            @click="deleteItem({{ $item->id }})"
                                            class="text-[#A8988A] hover:text-rose-500 hover:bg-rose-50 p-2 rounded-xl transition cursor-pointer"
                                            title="Xóa sản phẩm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- 4. Floating Rounded Bottom Summary Bar (Bo góc mềm mại, không bo tròn pill) --}}
                    <div class="fixed bottom-4 left-0 right-0 z-40 px-4 sm:px-6 lg:px-8">
                        <div class="mn-cart-bottom-bar max-w-7xl mx-auto bg-white rounded-2xl border border-[#F1E5D8] shadow-xl p-3 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">

                            {{-- Left Actions: Xóa tất cả & Kho voucher pill button --}}
                            <div class="flex items-center justify-between sm:justify-start w-full sm:w-auto gap-4 sm:gap-5 shrink-0">
                                {{-- Clear All Button (không bị xuống dòng) --}}
                                <button type="button" @click="clearAllCart()"
                                    class="inline-flex items-center gap-1.5 text-xs sm:text-[13px] text-[#7D6B5D] hover:text-rose-600 transition font-medium cursor-pointer whitespace-nowrap shrink-0">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" style="width: 17px; height: 17px; min-width: 17px; min-height: 17px;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span class="whitespace-nowrap">Xóa tất cả</span>
                                </button>

                                {{-- Kho Voucher Pill Button --}}
                                <a href="{{ route('customer.vouchers.index') }}" 
                                   class="mn-btn-voucher-repo whitespace-nowrap shrink-0">
                                    <svg class="w-4 h-4 text-[#D95F16] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2 2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2 2 2 0 012-2 2 2 0 01-2-2V6z"/>
                                    </svg>
                                    <span class="whitespace-nowrap">Kho voucher</span>
                                </a>
                            </div>

                            {{-- Right Total & Mua Hang Button --}}
                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-5 sm:gap-6">
                                {{-- Vertical divider on desktop --}}
                                <div class="hidden sm:block h-10 w-px bg-[#EBDCCC]"></div>

                                <div class="text-right">
                                    <div class="text-xs text-[#7D6B5D] font-medium">
                                        Tổng thanh toán (<span class="font-bold text-[#2B1810]"
                                            x-text="selectedItems.length + ' sản phẩm'">{{ $cartItems->count() }} sản phẩm</span>):
                                    </div>
                                    <div class="text-2xl sm:text-3xl font-black text-[#D95F16] tracking-tight leading-none mt-1"
                                        x-text="formatVND(selectedSubtotal)">
                                        {{ number_format($cartItems->sum(fn($i) => $i->effective_price * $i->quantity), 0, ',', '.') }}đ
                                    </div>
                                </div>

                                <button type="submit" :disabled="selectedItems.length === 0"
                                    class="mn-btn-buy">
                                    <span>MUA HÀNG</span>
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>

                </form>
            @endif

        {{-- Shopee-style Variant Selector Modal --}}
        <div x-show="activeVariantModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs transition-opacity"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="closeVariantSelector()">
            
            {{-- Modal Card --}}
            <div @click.outside="closeVariantSelector()"
                 class="bg-white rounded-3xl shadow-2xl border border-[#F0E6D8] max-w-lg w-full overflow-hidden flex flex-col max-h-[90vh]"
                 x-show="activeVariantModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                {{-- Header with Product Preview --}}
                <div class="p-5 border-b border-[#F0E6D8] bg-[#FFFDF9] flex items-start gap-4 relative">
                    <img :src="activeVariantModal?.selectedVariant?.image_url || activeVariantModal?.item?.image_url" 
                         :alt="activeVariantModal?.item?.name" 
                         class="w-20 h-20 rounded-2xl object-cover border-2 border-[#EBDDCD] shadow-sm shrink-0 bg-white"
                         onerror="this.src='https://placehold.co/200x200/F7EFE9/5D4037?text=Gau+Bong'">
                    
                    <div class="flex-1 min-w-0 pr-6">
                        <h4 class="font-bold text-[#2C1408] text-sm sm:text-base line-clamp-1" x-text="activeVariantModal?.item?.name"></h4>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-xl sm:text-2xl font-black text-[#E08A1E]" 
                                  x-text="formatVND(activeVariantModal?.selectedVariant?.effective_price || activeVariantModal?.item?.unit_price)">
                            </span>
                            <template x-if="activeVariantModal?.selectedVariant && activeVariantModal?.selectedVariant?.price > activeVariantModal?.selectedVariant?.effective_price">
                                <span class="text-xs text-gray-400 line-through" 
                                      x-text="formatVND(activeVariantModal?.selectedVariant?.price)">
                                </span>
                            </template>
                        </div>
                        <div class="mt-1 text-xs text-[#786B61] flex items-center gap-1.5 font-medium">
                            <span>Kho:</span>
                            <span class="font-bold" 
                                  :class="(activeVariantModal?.selectedVariant?.stock_quantity ?? 0) <= 0 ? 'text-rose-600' : 'text-[#2C1408]'"
                                  x-text="activeVariantModal?.selectedVariant?.stock_quantity ?? 0">
                            </span>
                        </div>
                    </div>

                    {{-- Close X Button --}}
                    <button type="button" 
                            @click="closeVariantSelector()" 
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100 transition cursor-pointer"
                            title="Đóng">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Body: Color & Size Options --}}
                <div class="p-5 overflow-y-auto space-y-5 flex-1">
                    {{-- Color Selection --}}
                    <template x-if="activeVariantModal?.colors && activeVariantModal?.colors.length > 0">
                        <div>
                            <label class="block text-xs font-bold text-[#786B61] uppercase tracking-wider mb-2.5">
                                Màu sắc: <span class="text-[#2C1408] font-black normal-case" x-text="activeVariantModal?.selectedColor || 'Chưa chọn'"></span>
                            </label>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-for="color in activeVariantModal?.colors" :key="color">
                                    <button type="button" 
                                            @click="selectModalColor(color)"
                                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all border flex items-center gap-1.5 cursor-pointer relative"
                                            :class="activeVariantModal?.selectedColor === color 
                                                ? 'border-[#E08A1E] bg-[#FFF8EE] text-[#E08A1E] shadow-2xs ring-2 ring-[#E08A1E]/30' 
                                                : 'border-[#EBDDCD] bg-white text-[#2C1408] hover:border-[#E08A1E]/60 hover:bg-[#FAF6EE]'">
                                        <span x-text="color"></span>
                                        <svg x-show="activeVariantModal?.selectedColor === color" 
                                             class="w-3.5 h-3.5 text-[#E08A1E]" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Size Selection --}}
                    <template x-if="activeVariantModal?.sizes && activeVariantModal?.sizes.length > 0">
                        <div>
                            <label class="block text-xs font-bold text-[#786B61] uppercase tracking-wider mb-2.5">
                                Kích thước: <span class="text-[#2C1408] font-black normal-case" x-text="activeVariantModal?.selectedSize || 'Chưa chọn'"></span>
                            </label>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-for="size in activeVariantModal?.sizes" :key="size">
                                    <button type="button" 
                                            @click="selectModalSize(size)"
                                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all border flex items-center gap-1.5 cursor-pointer relative"
                                            :class="activeVariantModal?.selectedSize === size 
                                                ? 'border-[#E08A1E] bg-[#FFF8EE] text-[#E08A1E] shadow-2xs ring-2 ring-[#E08A1E]/30' 
                                                : 'border-[#EBDDCD] bg-white text-[#2C1408] hover:border-[#E08A1E]/60 hover:bg-[#FAF6EE]'">
                                        <span x-text="size"></span>
                                        <svg x-show="activeVariantModal?.selectedSize === size" 
                                             class="w-3.5 h-3.5 text-[#E08A1E]" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Out of Stock Notice --}}
                    <template x-if="activeVariantModal?.selectedVariant && (activeVariantModal?.selectedVariant?.stock_quantity ?? 0) <= 0">
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-center gap-2 text-xs font-bold text-rose-600">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Phân loại này hiện đang tạm hết hàng, bạn vui lòng chọn phân loại khác nhé!</span>
                        </div>
                    </template>
                </div>

                {{-- Footer Action Buttons --}}
                <div class="p-4 bg-[#FAF6F0] border-t border-[#F0E6D8] flex items-center justify-end gap-3">
                    <button type="button" 
                            @click="closeVariantSelector()" 
                            class="px-5 py-2.5 rounded-xl border border-[#D1C4B5] text-[#786B61] hover:text-[#2C1408] hover:bg-white text-xs font-bold transition cursor-pointer">
                        TRỞ LẠI
                    </button>
                    <button type="button" 
                            @click="confirmVariantChange()" 
                            :disabled="!activeVariantModal?.selectedVariant || (activeVariantModal?.selectedVariant?.stock_quantity ?? 0) <= 0 || activeVariantModal?.isUpdating"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#E08A1E] to-[#E67E17] hover:from-[#D17E17] hover:to-[#D1700F] text-white text-xs font-extrabold shadow-md shadow-[#E08A1E]/30 transition transform active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none flex items-center gap-2 cursor-pointer">
                        <template x-if="activeVariantModal?.isUpdating">
                            <svg class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span x-text="activeVariantModal?.isUpdating ? 'Đang cập nhật...' : 'XÁC NHẬN'"></span>
                    </button>
                </div>
            </div>
        </div>

        </div>
    </div>
@endsection

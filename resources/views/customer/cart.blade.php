@extends('layouts.customer')

@section('title', 'Giỏ Hàng Của Bạn - Mật Ngọt Bear')

@section('content')
    {{-- Main Container --}}
    <div class="py-10 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-36 font-sans" x-data="cartComponent({{ json_encode(
        $cartItems->map(function ($item) {
            $price = (float) $item->effective_price;
            $imgUrl = $item->effective_image;
            if (!str_starts_with($imgUrl, 'http')) {
                $imgUrl = asset($imgUrl);
            }

            $variants = ($item->product && $item->product->variants)
                ? $item->product->variants->map(function ($v) use ($imgUrl) {
                    $vImg = !empty($v->image_url)
                        ? (str_starts_with($v->image_url, 'http') ? $v->image_url : asset($v->image_url))
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

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- 1. Page Title Section --}}
            <div class="flex items-center gap-3.5 mb-5 justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-xl bg-[#5C3219] text-white flex items-center justify-center shadow-sm shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-[#2C1408] tracking-tight font-bold">Giỏ hàng Mật ngọt Bear</h1>
                        <p class="text-xs font-semibold text-[#786B61] mt-0.5">Kiểm tra danh sách gấu bông bạn đã chọn trước khi thanh toán</p>
                    </div>
                </div>
                {{-- Cart Pill Counter on Right --}}
                <div
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-[#E08A1E] bg-[#FFFBF4] text-[#2C1408] font-bold text-xs shadow-2xs shrink-0">
                    <svg class="w-3.5 h-3.5 text-[#E08A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
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
                                    $pImgUrl = $pImg ? asset($pImg->image_url) : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=400&q=80';
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

                    {{-- 2. Table Header Bar (Row Header) --}}
                    <div
                        class="bg-white rounded-xl border border-[#F0E6D8] p-2.5 px-4 mb-3 grid grid-cols-12 gap-2 items-center text-xs font-bold text-[#786B61] tracking-wider uppercase shadow-xs">
                        <div class="col-span-12 md:col-span-6 flex items-center gap-3.5">
                            {{-- Custom Rounded Orange Checkbox --}}
                            <button type="button" @click="toggleSelectAll(!isAllSelected)"
                                class="w-5 h-5 rounded-md flex items-center justify-center transition cursor-pointer shrink-0"
                                :class="isAllSelected ? 'bg-[#E08A1E] text-white shadow-xs' : 'border-2 border-[#D1C4B5] bg-white hover:border-[#E08A1E]'">
                                <svg x-show="isAllSelected" class="w-3.5 h-3.5 stroke-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <span class="cursor-pointer font-bold text-[#5C3219] hover:text-[#E08A1E] text-xs transition"
                                @click="toggleSelectAll(!isAllSelected)">
                                CHỌN TẤT CẢ
                            </span>
                        </div>
                        <div class="hidden md:block md:col-span-2 text-center text-[#786B61]">ĐƠN GIÁ</div>
                        <div class="hidden md:block md:col-span-2 text-center text-[#786B61]">SỐ LƯỢNG</div>
                        <div class="hidden md:block md:col-span-2 text-center text-[#786B61]">THÀNH TIỀN</div>
                    </div>

                    {{-- 3. Cart Items List --}}
                    <div class="space-y-3">
                        @foreach ($cartItems as $item)
                            @php
                                $product = $item->product;
                                $variant = $item->variant;
                                $imageUrl = $item->effective_image;
                                $price = $item->effective_price;
                                $originalPrice = $variant ? $variant->price : $product->price;
                                $hasDiscount = $price < $originalPrice;
                                $discountPercent = $hasDiscount && $originalPrice > 0 
                                    ? round((($originalPrice - $price) / $originalPrice) * 100) 
                                    : 0;
                                $effectiveStock = $item->effective_stock;
                            @endphp

                            <div class="bg-white rounded-2xl p-3.5 md:p-4 transition-all duration-200 grid grid-cols-12 gap-3 items-center shadow-xs hover:shadow-md"
                                x-show="hasItem({{ $item->id }})"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                :class="isSelected({{ $item->id }}) ? 'border-2 border-[#E08A1E] bg-[#FFFDF9]' :
                                    'border border-[#F0E6D8] hover:border-[#E08A1E]/50'">

                                {{-- Checkbox, Image & Product Info --}}
                                <div class="col-span-12 md:col-span-6 flex items-center gap-3">
                                    {{-- Custom Checkbox --}}
                                    <button type="button"
                                        @click="toggleItem({{ $item->id }}, !isSelected({{ $item->id }}), '{{ addslashes($product->name) }}')"
                                        class="w-5 h-5 rounded-md flex items-center justify-center transition cursor-pointer shrink-0"
                                        :class="isSelected({{ $item->id }}) ? 'bg-[#E08A1E] text-white shadow-xs' :
                                            'border-2 border-[#D1C4B5] bg-white hover:border-[#E08A1E]'">
                                        <svg x-show="isSelected({{ $item->id }})"
                                            class="w-3.5 h-3.5 stroke-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>

                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        {{-- Image Thumbnail with Zoom & Sale Badge --}}
                                        <a href="{{ route('products.show', $product->id) }}"
                                            class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border border-[#EBDDCD] shrink-0 group block shadow-2xs overflow-hidden bg-white"
                                            title="Xem chi tiết {{ $product->name }}">
                                            <img :src="getItemImageUrl({{ $item->id }}) || '{{ $imageUrl }}'" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover object-center transform transition duration-300 group-hover:scale-105">
                                            @if ($hasDiscount)
                                                <span class="absolute top-1.5 left-1.5 bg-rose-500 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-xs leading-none">
                                                    -{{ $discountPercent }}%
                                                </span>
                                            @endif
                                        </a>

                                        <div class="flex-1 min-w-0">
                                            {{-- Category Badge --}}
                                            <span
                                                class="inline-block px-2.5 py-0.5 bg-[#FFF9EE] text-[#E08A1E] border border-[#FDE68A] text-xs font-bold rounded-full mb-1.5">
                                                🧸 {{ $product->category->name ?? 'Gấu Bông Teddy' }}
                                            </span>

                                            {{-- Product Name --}}
                                            <h3>
                                                <a href="{{ route('products.show', $product->id) }}"
                                                    class="font-black font-semibold text-[#2C1408] text-base truncate block hover:text-[#E08A1E] transition leading-snug"
                                                    title="Xem chi tiết {{ $product->name }}">
                                                    {{ $product->name }}
                                                </a>
                                            </h3>

                                            {{-- Attributes / Shopee Variant Selector Button --}}
                                            <div class="mt-2">
                                                @if ($variant || ($item->product->variants && $item->product->variants->count() > 0))
                                                    <button type="button"
                                                        @click="openVariantSelector({{ $item->id }})"
                                                        class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all duration-200 border border-[#FDE68A] bg-[#FFF8ED] hover:bg-[#FFF2D6] text-[#9A4A0A] hover:border-[#E08A1E] shadow-2xs hover:shadow-xs text-left cursor-pointer"
                                                        title="Bấm để chọn phân loại khác như Shopee">
                                                        <span class="text-[#D97706] group-hover:scale-110 transition-transform">✨</span>
                                                        <span class="text-[#786B61] font-medium">Phân loại:</span>
                                                        <span class="text-[#2C1408] font-bold"
                                                            x-text="getItemVariantDisplay({{ $item->id }}) || '{{ $variant ? ($variant->color . ' · ' . $variant->size) : 'Chọn phân loại' }}'">
                                                            {{ $variant ? ($variant->color . ' · ' . $variant->size) : 'Chọn phân loại' }}
                                                        </span>
                                                        <svg class="w-3.5 h-3.5 text-[#9A4A0A] group-hover:translate-y-0.5 transition-transform shrink-0 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <div class="flex flex-wrap gap-2 text-xs font-semibold text-[#786B61]">
                                                        @if ($product->size)
                                                            <span class="bg-[#F3EDE3] px-2.5 py-1 rounded-md text-[#5C3219]">
                                                                Size: {{ $product->size }}
                                                            </span>
                                                        @endif
                                                        @if ($product->color)
                                                            <span class="bg-[#F3EDE3] px-2.5 py-1 rounded-md text-[#5C3219]">
                                                                Màu: {{ $product->color }}
                                                            </span>
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
                                </div>

                                {{-- Price (Mobile & Desktop) --}}
                                <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                    <span class="text-xs text-[#9CA3AF] block md:hidden">Đơn giá:</span>
                                    <div class="font-bold text-[#E08A1E] text-base sm:text-lg"
                                        x-text="formatVND(getItemPrice({{ $item->id }}))">
                                        {{ number_format($price, 0, ',', '.') }}đ
                                    </div>
                                    @if ($hasDiscount)
                                        <div class="text-xs text-[#9CA3AF] line-through font-normal mt-0.5">
                                            {{ number_format($originalPrice, 0, ',', '.') }}đ
                                        </div>
                                    @endif
                                </div>

                                {{-- Quantity Stepper --}}
                                <div class="col-span-4 md:col-span-2 flex justify-center">
                                    <div
                                        class="inline-flex items-center gap-3 bg-[#FAF8F5] border border-[#EBDDCD] rounded-xl px-3 py-1.5 shadow-inner">
                                        <button type="button"
                                            @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) - 1)"
                                            :disabled="getItemQuantity({{ $item->id }}) <= 1"
                                            class="text-gray-500 hover:text-[#2C1408] font-bold text-base transition disabled:opacity-30 disabled:cursor-not-allowed">
                                            −
                                        </button>

                                        <span class="font-bold text-[#2C1408] text-sm w-5 text-center"
                                            x-text="getItemQuantity({{ $item->id }})">{{ $item->quantity }}</span>

                                        <button type="button"
                                            @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) + 1)"
                                            :disabled="getItemQuantity({{ $item->id }}) >= getItemStock({{ $item->id }})"
                                            class="text-gray-500 hover:text-[#2C1408] font-bold text-base transition disabled:opacity-30 disabled:cursor-not-allowed">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Line Total & Delete Action --}}
                                <div class="col-span-4 md:col-span-2 flex items-center justify-end md:justify-around gap-2">
                                    <div class="font-bold text-[#E08A1E] text-base sm:text-lg">
                                        <span
                                            x-text="formatVND(getItemLineTotal({{ $item->id }}))">{{ number_format($price * $item->quantity, 0, ',', '.') }}đ</span>
                                    </div>

                                    <button type="button"
                                        @click="deleteItem({{ $item->id }})"
                                        class="text-gray-400 hover:text-rose-600 transition p-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                                        title="Xóa sản phẩm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- 4. Sticky Bottom Summary Bar --}}
                    <div
                        class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#F0E6D8] shadow-2xl py-3.5 px-4 sm:px-6 lg:px-8">
                        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">

                            {{-- Left Actions: Xóa tất cả & Kho voucher link --}}
                            <div class="flex items-center justify-between sm:justify-start w-full sm:w-auto gap-3">
                                <button type="button" @click="clearAllCart()"
                                    class="inline-flex items-center gap-1.5 text-xs text-[#786B61] hover:text-rose-600 hover:bg-rose-50 px-3 py-2 rounded-xl font-medium transition border border-transparent hover:border-rose-200">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    <span>Xóa tất cả</span>
                                </button>

                                <a href="{{ route('customer.vouchers.index') }}" 
                                   class="hidden sm:inline-flex items-center gap-1.5 text-xs text-[#C2751D] hover:text-[#9A4A0A] font-bold bg-[#FFF9EE] border border-[#FDE68A] px-3 py-1.5 rounded-lg transition hover:shadow-2xs">
                                    <span>🎟️</span>
                                    <span>Kho voucher</span>
                                </a>
                            </div>

                            {{-- Right Total & Buy Button --}}
                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-6">
                                <div class="text-right">
                                    <div class="text-xs text-[#786B61] font-medium">
                                        Tổng thanh toán (<span class="font-bold text-[#2C1408]"
                                            x-text="selectedItems.length + ' sản phẩm'">{{ $cartItems->count() }} sản
                                            phẩm</span>):
                                    </div>
                                    <div class="text-2xl sm:text-3xl font-bold text-[#E08A1E] tracking-tight leading-none mt-1"
                                        x-text="formatVND(selectedSubtotal)">
                                        {{ number_format($cartItems->sum(fn($i) => $i->effective_price * $i->quantity), 0, ',', '.') }}đ
                                    </div>
                                </div>

                                <button type="submit" :disabled="selectedItems.length === 0"
                                    class="bg-gradient-to-r from-[#E08A1E] to-[#E67E17] hover:from-[#D17E17] hover:to-[#D1700F] text-white font-extrabold text-sm py-3.5 px-8 rounded-2xl shadow-lg shadow-[#E08A1E]/30 transition transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none flex items-center gap-2 shrink-0 tracking-wide uppercase">
                                    <span>MUA HÀNG</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
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
                         class="w-20 h-20 rounded-2xl object-cover border-2 border-[#EBDDCD] shadow-sm shrink-0 bg-white">
                    
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

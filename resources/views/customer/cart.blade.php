@extends('layouts.customer')

@section('title', 'Giỏ Hàng Của Bạn - Mật Ngọt Bear')

@section('content')
    {{-- Main Container --}}
    <div class="py-10 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-36 font-sans" x-data="cartComponent({{ json_encode(
        $cartItems->map(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'stock_quantity' => $item->product->stock_quantity,
                'unit_price' => (float) $price,
                'line_total' => (float) ($price * $item->quantity),
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
                        <h1 class="text-xl sm:text-2xl font-black text-[#2C1408] tracking-tight">Giỏ hàng Mật ngọt Bear</h1>
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
                    <span>{{ $cartItems->count() }} sản phẩm trong giỏ</span>
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
                                <div class="text-xs font-bold text-[#2C1408]">Đồng giá ship 30k</div>
                                <div class="text-[11px] text-[#786B61]">Freeship đơn từ 300k</div>
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
                                CHỌN TẤT CẢ (<span x-text="items.length">{{ $cartItems->count() }}</span> SẢN PHẨM)
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
                                $primaryImage =
                                    $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                                $imageUrl = $primaryImage
                                    ? asset($primaryImage->image_url)
                                    : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=400&q=80';
                                $price = $product->sale_price ?? $product->price;
                                $hasDiscount = !empty($product->sale_price) && $product->sale_price < $product->price;
                                $discountPercent = $hasDiscount && $product->price > 0 
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100) 
                                    : 0;
                            @endphp

                            <div class="bg-white rounded-2xl p-3.5 md:p-4 transition-all duration-200 grid grid-cols-12 gap-3 items-center shadow-xs hover:shadow-md"
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
                                            class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-[#FAF6EE] border border-[#EBDDCD] shrink-0 group block shadow-2xs"
                                            title="Xem chi tiết {{ $product->name }}">
                                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
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
                                                    class="font-black text-[#2C1408] text-base truncate block hover:text-[#E08A1E] transition leading-snug"
                                                    title="Xem chi tiết {{ $product->name }}">
                                                    {{ $product->name }}
                                                </a>
                                            </h3>

                                            {{-- Attributes / Variants --}}
                                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-[#786B61] mt-2">
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

                                            {{-- Stock alert --}}
                                            @if ($product->stock_quantity <= 5)
                                                <p class="text-xs font-bold text-rose-500 mt-1.5 flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                    Chỉ còn {{ $product->stock_quantity }} sản phẩm trong kho
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Price (Mobile & Desktop) --}}
                                <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                    <span class="text-xs text-[#9CA3AF] block md:hidden">Đơn giá:</span>
                                    <div class="font-bold text-[#E08A1E] text-base sm:text-lg">
                                        {{ number_format($price, 0, ',', '.') }}đ
                                    </div>
                                    @if ($hasDiscount)
                                        <div class="text-xs text-[#9CA3AF] line-through font-normal mt-0.5">
                                            {{ number_format($product->price, 0, ',', '.') }}đ
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
                                            :disabled="getItemQuantity({{ $item->id }}) >= {{ $product->stock_quantity }}"
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
                                        @click="deleteItem({{ $item->id }}, '{{ addslashes($product->name) }}')"
                                        class="text-gray-400 hover:text-rose-600 transition p-1.5 rounded-lg hover:bg-rose-50"
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
                                        {{ number_format($cartItems->sum(fn($i) => ($i->product->sale_price ?? $i->product->price) * $i->quantity), 0, ',', '.') }}đ
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

        </div>
    </div>
@endsection

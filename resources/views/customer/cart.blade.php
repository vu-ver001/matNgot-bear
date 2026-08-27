<x-app-layout>
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
        {{-- Breadcrumb Component --}}
        <div class="hidden md:block max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-2">
            <x-breadcrumb :items="[
                ['label' => 'Trang Chủ', 'url' => route('home')],
                ['label' => 'Cửa Hàng', 'url' => route('products.index')],
                ['label' => 'Giỏ Hàng']
            ]" />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- 2. Page Title Section --}}
            <div class="flex items-start gap-4 mb-4 justify-between">
                <div class="flex items-start gap-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-[#5C3219] text-white flex items-center justify-center shadow-md shadow-[#5C3219]/20 shrink-0">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-[#2C1408] tracking-tight">Giỏ hàng Mật ngọt Bear</h1>
                        <p class="text-sm font-semibold text-[#786B61] mt-1">Kiểm tra danh sách gấu bông bạn đã chọn
                            trước
                            khi thanh toán</p>
                    </div>
                </div>
                {{-- Cart Pill Counter on Right --}}
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#E08A1E] bg-[#FFFBF4] text-[#2C1408] font-bold text-xs shadow-xs">
                    <svg class="w-4 h-4 text-[#E08A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span>{{ $cartItems->count() }} sản phẩm trong giỏ</span>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-4 p-2 bg-[#FAF6F0] border-2 border-emerald-300/80 rounded-2xl shadow-sm text-emerald-900 flex items-center justify-between"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xs shadow-sm shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </span>
                        <span class="font-semibold text-sm">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false"
                        class="text-[#8E8076] hover:text-[#2E190E] p-1 transition">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-2 bg-[#FAF6F0] border-2 border-rose-300/80 rounded-2xl shadow-sm text-rose-900 flex items-center justify-between"
                    x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-rose-400 to-rose-600 text-white text-xs shadow-sm shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </span>
                        <span class="font-semibold text-sm">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false"
                        class="text-[#8E8076] hover:text-[#2E190E] p-1 transition">&times;</button>
                </div>
            @endif

            @if ($cartItems->isEmpty())
                {{-- Empty Cart State --}}
                <div
                    class="bg-white rounded-3xl p-8 text-center shadow-xs border border-[#F0E6D8] max-w-2xl mx-auto my-4">
                    <div
                        class="w-32 h-32 mx-auto mb-6 bg-[#FFF9EE] border border-[#FDE68A] rounded-full flex items-center justify-center text-6xl shadow-inner">
                        🧸
                    </div>
                    <h3 class="text-2xl font-extrabold text-[#2C1408] mb-2">Giỏ hàng của bạn đang trống!</h3>
                    <p class="text-[#786B61] mb-8 leading-relaxed font-medium">Hãy chọn ngay những chú gấu bông dễ
                        thương và quà tặng ngọt ngào từ Mật Ngọt Bear nhé.</p>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#E08A1E] to-[#E67E17] hover:from-[#D17E17] hover:to-[#D1700F] text-white font-extrabold py-3.5 px-8 rounded-2xl shadow-lg shadow-[#E08A1E]/25 transition transform hover:-translate-y-0.5 active:translate-y-0">
                        <span>Khám phá sản phẩm ngay</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            @else
                <form id="checkoutForm" action="{{ route('customer.checkout.index') }}" method="GET" @submit.prevent="handleCheckoutSubmit($event)">

                    {{-- Hidden inputs for selected items --}}
                    <template x-for="itemId in selectedItems" :key="itemId">
                        <input type="hidden" name="selected_items[]" :value="itemId">
                    </template>

                    {{-- 3. Table Header Bar (Row Header) --}}
                    <div
                        class="bg-white rounded-xl border border-[#F0E6D8] p-2 px-4 mb-2 grid grid-cols-12 gap-2 items-center text-xs font-bold text-[#786B61] tracking-wider uppercase shadow-xs">
                        <div class="col-span-12 md:col-span-6 flex items-center gap-3.5">
                            {{-- Custom Rounded Orange Checkbox --}}
                            <button type="button" @click="toggleSelectAll(!isAllSelected)"
                                class="w-5 h-5 rounded-md flex items-center justify-center transition cursor-pointer shrink-0"
                                :class="isAllSelected ? 'bg-[#E08A1E] text-white shadow-xs' :
                                    'border-2 border-[#D1C4B5] bg-white'">
                                <svg x-show="isAllSelected" class="w-3.5 h-3.5 stroke-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <span class="cursor-pointer font-bold text-[#786B61] hover:text-[#2C1408]"
                                @click="toggleSelectAll(!isAllSelected)">
                                CHỌN TẤT CẢ (<span x-text="items.length">{{ $cartItems->count() }}</span>)
                            </span>
                        </div>
                        <div class="hidden md:block md:col-span-2 text-center">ĐƠN GIÁ</div>
                        <div class="hidden md:block md:col-span-2 text-center">SỐ LƯỢNG</div>
                        <div class="hidden md:block md:col-span-2 text-center">THÀNH TIỀN</div>
                    </div>

                    {{-- 4. Cart Items List (White Cards with orange border when selected) --}}
                    <div class="space-y-2">
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
                            @endphp

                            <div class="bg-white rounded-2xl p-3 md:p-4  transition-all grid grid-cols-12 gap-2 items-center shadow-xs"
                                :class="isSelected({{ $item->id }}) ? 'border-2 border-[#E08A1E] shadow-sm' :
                                    'border border-[#F0E6D8]'">

                                {{-- Checkbox, Image & Product Info --}}
                                <div class="col-span-12 md:col-span-6 flex items-center gap-2">
                                    {{-- Custom Checkbox --}}
                                    <button type="button"
                                        @click="toggleItem({{ $item->id }}, !isSelected({{ $item->id }}), '{{ addslashes($product->name) }}')"
                                        class="w-5 h-5 rounded-md flex items-center justify-center transition cursor-pointer shrink-0"
                                        :class="isSelected({{ $item->id }}) ? 'bg-[#E08A1E] text-white shadow-xs' :
                                            'border-2 border-[#D1C4B5] bg-white'">
                                        <svg x-show="isSelected({{ $item->id }})"
                                            class="w-3.5 h-3.5 stroke-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>

                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <a href="{{ route('products.show', $product->id) }}"
                                            class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-[#FBF9F5] border border-[#F0E6D8] shrink-0 group block"
                                            title="Xem chi tiết {{ $product->name }}">
                                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover object-center transform transition duration-300 group-hover:scale-105">
                                        </a>

                                        <div class="flex-1 min-w-0">
                                            <span
                                                class="inline-block px-3 py-0.5 bg-[#FFF9EE] text-[#E08A1E] border border-[#FDE68A] text-[11px] font-bold rounded-full mb-1.5">
                                                {{ $product->category->name ?? 'Gấu Bông Teddy' }}
                                            </span>
                                            <h3>
                                                <a href="{{ route('products.show', $product->id) }}"
                                                    class="font-black text-[#2C1408] text-base truncate block hover:text-[#E08A1E] transition"
                                                    title="Xem chi tiết {{ $product->name }}">
                                                    {{ $product->name }}
                                                </a>
                                            </h3>

                                            <div
                                                class="flex flex-wrap gap-2 text-xs font-semibold text-[#786B61] mt-2">
                                                @if ($product->size)
                                                    <span class="bg-[#F3EDE3] px-2.5 py-1 rounded-md">Size:
                                                        {{ $product->size }}</span>
                                                @endif
                                                @if ($product->color)
                                                    <span class="bg-[#F3EDE3] px-2.5 py-1 rounded-md">Màu:
                                                        {{ $product->color }}</span>
                                                @endif
                                            </div>

                                            {{-- Stock alert --}}
                                            @if ($product->stock_quantity <= 5)
                                                <p class="text-xs font-bold text-rose-500 mt-1">Chỉ còn
                                                    {{ $product->stock_quantity }} sản phẩm trong kho</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Price (Mobile & Desktop) --}}
                                <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                    <span class="text-xs text-[#9CA3AF] block md:hidden">Đơn giá:</span>
                                    <div class="font-bold text-[#E08A1E] text-base">
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
                                            -
                                        </button>

                                        <span class="font-bold text-[#2C1408] text-sm w-4 text-center"
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
                                <div
                                    class="col-span-4 md:col-span-2 flex items-center justify-end md:justify-around gap-2">
                                    <div class="font-bold text-[#E08A1E] text-base sm:text-lg">
                                        <span
                                            x-text="formatVND(getItemLineTotal({{ $item->id }}))">{{ number_format($price * $item->quantity, 0, ',', '.') }}đ</span>
                                    </div>

                                    <button type="button"
                                        @click="deleteItem({{ $item->id }}, '{{ addslashes($product->name) }}')"
                                        class="text-gray-400 hover:text-rose-600 transition p-1.5"
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

                    {{-- 5. Sticky Bottom Summary Bar (Exact Match to screenshot) --}}
                    <div
                        class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#F0E6D8] shadow-2xl py-3.5 px-6 sm:px-12">
                        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">

                            {{-- Left Action: Xóa tất cả --}}
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
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

    {{-- 6. Help circular badge at bottom right --}}
    {{-- <div class="fixed bottom-20 right-6 z-50">
        <button type="button"
            class="w-10 h-10 rounded-full bg-white border border-[#EBDDCD] shadow-lg flex items-center justify-center text-[#786B61] font-bold text-sm hover:text-[#E08A1E] transition"
            title="Trợ giúp">
            ?
        </button>
    </div> --}}
</x-app-layout>

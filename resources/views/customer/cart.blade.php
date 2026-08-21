<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-amber-900 leading-tight flex items-center gap-3">
                <span class="text-3xl">🛒</span> Giỏ Hàng Mật Ngọt Bear
            </h2>
            <span class="text-sm font-medium text-amber-700 bg-amber-100 px-3 py-1 rounded-full shadow-sm">
                {{ $cartItems->count() }} sản phẩm trong giỏ
            </span>
        </div>
    </x-slot>

    <div class="py-8 bg-amber-50/40 min-h-[calc(100vh-140px)] pb-32"
         x-data="cartComponent({{ json_encode($cartItems->map(function($item) {
             $price = $item->product->sale_price ?? $item->product->price;
             return [
                 'id' => $item->id,
                 'product_id' => $item->product_id,
                 'quantity' => $item->quantity,
                 'stock_quantity' => $item->product->stock_quantity,
                 'unit_price' => (float)$price,
                 'line_total' => (float)($price * $item->quantity)
             ];
         })) }})">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notification Toast / Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-sm text-emerald-800 flex items-center justify-between" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-sm text-rose-800 flex items-center justify-between" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
                </div>
            @endif

            @if($cartItems->isEmpty())
                {{-- Empty Cart State --}}
                <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-amber-100 max-w-2xl mx-auto my-8">
                    <div class="w-32 h-32 mx-auto mb-6 bg-amber-100/60 rounded-full flex items-center justify-center text-6xl shadow-inner">
                        🧸
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Giỏ hàng của bạn đang trống!</h3>
                    <p class="text-gray-500 mb-8 leading-relaxed">Hãy chọn ngay những chú gấu bông dễ thương và quà tặng ngọt ngào từ Mật Ngọt Bear nhé.</p>
                    <a href="{{ route('customer.products.index', [], false) ?? '#' }}" 
                       class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-3.5 px-8 rounded-full shadow-lg shadow-amber-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        <span>Khám phá sản phẩm ngay</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            @else
                <form id="checkoutForm" action="{{ route('customer.checkout.index') }}" method="GET">
                    
                    {{-- Hidden inputs for selected items --}}
                    <template x-for="itemId in selectedItems" :key="itemId">
                        <input type="hidden" name="selected_items[]" :value="itemId">
                    </template>

                    {{-- Cart Header Bar --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-4 mb-4 grid grid-cols-12 gap-4 items-center text-sm font-semibold text-amber-900/80">
                        <div class="col-span-12 md:col-span-5 flex items-center gap-3">
                            <input type="checkbox" 
                                   id="selectAllTop"
                                   @change="toggleSelectAll($event.target.checked)"
                                   :checked="isAllSelected"
                                   class="w-5 h-5 text-amber-600 bg-gray-100 border-amber-300 rounded focus:ring-amber-500 focus:ring-2 cursor-pointer transition">
                            <label for="selectAllTop" class="cursor-pointer font-bold text-gray-800 hover:text-amber-700">
                                Chọn tất cả (<span x-text="items.length"></span>)
                            </label>
                        </div>
                        <div class="hidden md:block md:col-span-2 text-center text-gray-500">Đơn Giá</div>
                        <div class="col-span-6 md:col-span-2 text-center text-gray-500">Số Lượng</div>
                        <div class="col-span-6 md:col-span-2 text-right text-gray-500">Số Tiền</div>
                        <div class="hidden md:block md:col-span-1 text-center text-gray-500">Thao Tác</div>
                    </div>

                    {{-- Cart Items List --}}
                    <div class="space-y-4">
                        @foreach($cartItems as $item)
                            @php
                                $product = $item->product;
                                $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                                $imageUrl = $primaryImage ? asset($primaryImage->image_url) : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=400&q=80';
                                $price = $product->sale_price ?? $product->price;
                                $hasDiscount = !empty($product->sale_price) && $product->sale_price < $product->price;
                            @endphp

                            <div class="bg-white rounded-2xl shadow-sm border border-amber-100/80 p-4 md:p-5 transition-all hover:shadow-md hover:border-amber-200 grid grid-cols-12 gap-4 items-center"
                                 :class="{ 'ring-2 ring-amber-400/50 bg-amber-50/20': isSelected({{ $item->id }}) }">
                                
                                {{-- Checkbox & Product Info --}}
                                <div class="col-span-12 md:col-span-5 flex items-center gap-3.5">
                                    <input type="checkbox" 
                                           value="{{ $item->id }}"
                                           x-model="selectedItems"
                                           class="w-5 h-5 text-amber-600 bg-gray-100 border-amber-300 rounded focus:ring-amber-500 focus:ring-2 cursor-pointer transition">

                                    <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-amber-50 border border-amber-100 shrink-0">
                                            <img src="{{ $imageUrl }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-full h-full object-cover object-center transform transition duration-300 hover:scale-105">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-800 text-[11px] font-semibold rounded-md mb-1">
                                                {{ $product->category->name ?? 'Gấu bông' }}
                                            </span>
                                            <h4 class="font-bold text-gray-900 text-base truncate hover:text-amber-600 transition">
                                                {{ $product->name }}
                                            </h4>
                                            
                                            <div class="flex flex-wrap gap-2 text-xs text-gray-500 mt-1">
                                                @if($product->size)
                                                    <span class="bg-gray-100 px-2 py-0.5 rounded">Size: {{ $product->size }}</span>
                                                @endif
                                                @if($product->color)
                                                    <span class="bg-gray-100 px-2 py-0.5 rounded">Màu: {{ $product->color }}</span>
                                                @endif
                                            </div>

                                            {{-- Stock alert --}}
                                            @if($product->stock_quantity <= 5)
                                                <p class="text-xs font-semibold text-rose-500 mt-1">Chỉ còn {{ $product->stock_quantity }} sản phẩm trong kho</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Price (Mobile & Desktop) --}}
                                <div class="col-span-6 md:col-span-2 text-left md:text-center">
                                    <span class="text-xs text-gray-400 block md:hidden">Đơn giá:</span>
                                    <div class="flex flex-col md:items-center">
                                        <span class="font-bold text-amber-900 text-base">
                                            {{ number_format($price, 0, ',', '.') }}đ
                                        </span>
                                        @if($hasDiscount)
                                            <span class="text-xs text-gray-400 line-through">
                                                {{ number_format($product->price, 0, ',', '.') }}đ
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Quantity Controller --}}
                                <div class="col-span-6 md:col-span-2 flex justify-end md:justify-center">
                                    <div class="inline-flex items-center border-2 border-amber-200 rounded-xl overflow-hidden shadow-inner bg-white">
                                        <button type="button" 
                                                @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) - 1)"
                                                :disabled="getItemQuantity({{ $item->id }}) <= 1"
                                                class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-amber-100 active:bg-amber-200 transition disabled:opacity-30 disabled:cursor-not-allowed font-bold">
                                            -
                                        </button>
                                        
                                        <input type="number" 
                                               :value="getItemQuantity({{ $item->id }})"
                                               @change="updateQuantity({{ $item->id }}, parseInt($event.target.value) || 1)"
                                               min="1" 
                                               max="{{ $product->stock_quantity }}"
                                               class="w-12 h-8 text-center text-sm font-bold border-0 focus:ring-0 p-0 text-gray-800 bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

                                        <button type="button" 
                                                @click="updateQuantity({{ $item->id }}, getItemQuantity({{ $item->id }}) + 1)"
                                                :disabled="getItemQuantity({{ $item->id }}) >= {{ $product->stock_quantity }}"
                                                class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-amber-100 active:bg-amber-200 transition disabled:opacity-30 disabled:cursor-not-allowed font-bold">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Line Total --}}
                                <div class="col-span-9 md:col-span-2 text-right">
                                    <span class="text-xs text-gray-400 block md:hidden">Thành tiền:</span>
                                    <span class="font-extrabold text-amber-600 text-lg"
                                          x-text="formatVND(getItemLineTotal({{ $item->id }}))">
                                    </span>
                                </div>

                                {{-- Remove Action --}}
                                <div class="col-span-3 md:col-span-1 text-right md:text-center">
                                    <button type="button" 
                                            @click="deleteItem({{ $item->id }})"
                                            class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Xóa sản phẩm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- Sticky Bottom Summary Bar (Shopee Style) --}}
                    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-amber-200 shadow-2xl py-3 px-4 sm:px-8">
                        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                            
                            <div class="flex items-center justify-between sm:justify-start w-full sm:w-auto gap-4">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           id="selectAllBottom"
                                           @change="toggleSelectAll($event.target.checked)"
                                           :checked="isAllSelected"
                                           class="w-5 h-5 text-amber-600 bg-gray-100 border-amber-300 rounded focus:ring-amber-500 cursor-pointer">
                                    <label for="selectAllBottom" class="cursor-pointer font-bold text-gray-800 text-sm hover:text-amber-700">
                                        Chọn Tất Cả (<span x-text="items.length"></span>)
                                    </label>
                                </div>

                                <button type="button" 
                                        @click="clearAllCart()"
                                        class="text-xs text-gray-500 hover:text-rose-600 underline font-medium transition">
                                    Xóa tất cả
                                </button>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-6">
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">
                                        Tổng thanh toán (<span class="font-bold text-amber-600" x-text="selectedItems.length"></span> sản phẩm):
                                    </div>
                                    <div class="text-2xl sm:text-3xl font-black text-amber-600 tracking-tight"
                                         x-text="formatVND(selectedSubtotal)">
                                        0đ
                                    </div>
                                </div>

                                <button type="submit" 
                                        :disabled="selectedItems.length === 0"
                                        class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-base py-3.5 px-8 sm:px-10 rounded-2xl shadow-lg shadow-amber-500/30 transition transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-40 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none flex items-center gap-2 shrink-0">
                                    <span>MUA HÀNG</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </div>

                        </div>
                    </div>

                </form>
            @endif

        </div>
    </div>
</x-app-layout>

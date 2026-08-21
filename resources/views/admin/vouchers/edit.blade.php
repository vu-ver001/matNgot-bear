<x-app-layout>
    <div class="py-8 bg-[#F9F5EE] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb Navigation --}}
            <x-breadcrumb :items="[
                // ['label' => 'Mật Ngọt Bear', 'url' => route('dashboard')],
                ['label' => 'Quản Lý Mã Voucher', 'url' => route('admin.vouchers.index')],
                ['label' => 'Chỉnh Sửa [' . $voucher->code . ']', 'url' => ''],
            ]" />

            {{-- Page Header --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[#EBDDCD]">
                <div class="flex items-center gap-3">
                    <div
                        class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#E09028] to-[#5C3219] flex items-center justify-center text-white shadow-md shadow-[#E09028]/25 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-bold text-[#2E190E] tracking-tight">Chỉnh Sửa Voucher
                            </h1>
                            <span
                                class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD]">
                                {{ $voucher->code }}
                            </span>
                        </div>
                        <p class="text-xs text-[#7E4A28] mt-0.5 font-normal">Cập nhật thông tin chiết khấu, phạm vi áp
                            dụng, hạn dùng và lượt sử dụng của voucher</p>
                    </div>
                </div>

                <a href="{{ route('admin.vouchers.index') }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD] rounded-xl font-medium text-xs shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Quay lại danh sách</span>
                </a>
            </div>

            {{-- Form Container --}}
            <form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}" x-data="voucherForm({
                code: '{{ old('code', $voucher->code) }}',
                voucher_type: '{{ old('voucher_type', $voucher->voucher_type) }}',
                apply_scope: '{{ old('apply_scope', $voucher->apply_scope) }}',
                selectedCategories: {{ json_encode(old('category_ids', $voucher->categories->pluck('id')->toArray())) }},
                selectedProducts: {{ json_encode(old('product_ids', $voucher->products->pluck('id')->toArray())) }},
                discount_type: '{{ old('discount_type', $voucher->discount_type) }}',
                discount_value: '{{ old('discount_value', (float) $voucher->discount_value) }}',
                min_order_value: '{{ old('min_order_value', (float) $voucher->min_order_value) }}',
                max_discount_value: '{{ old('max_discount_value', $voucher->max_discount_value ? (float) $voucher->max_discount_value : '') }}',
                start_date: '{{ old('start_date', $voucher->start_date?->format('Y-m-d H:i')) }}',
                end_date: '{{ old('end_date', $voucher->end_date?->format('Y-m-d H:i')) }}',
                usage_limit: '{{ old('usage_limit', $voucher->usage_limit) }}',
                used_count: {{ $voucher->used_count }},
                status: '{{ old('status', $voucher->status) }}',
            })"
                class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                @csrf
                @method('PUT')

                {{-- Left Column: Form Fields --}}
                {{-- 1. Phân Loại Voucher --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EBDDCD] shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-6 h-6 bg-[#FFF5E6] text-[#5C3219] font-bold rounded-lg text-xs flex items-center justify-center shrink-0 border border-[#EBDDCD]">01</span>
                        <span class="text-sm font-bold text-[#2E190E] uppercase tracking-wide">Phân Loại Voucher</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {{-- Option 1: Mã Đơn Hàng --}}
                        <label
                            class="relative flex items-start gap-3.5 p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                            :class="voucher_type === 'ORDER' ?
                                'border-[#E09028] bg-[#FFF5E6] shadow-xs ring-2 ring-[#E09028]/20' :
                                'border-[#EBDDCD] hover:border-[#DDA760] bg-white'">
                            <input type="radio" name="voucher_type" value="ORDER" x-model="voucher_type"
                                @change="onVoucherTypeChange()" class="mt-1 text-[#E09028] focus:ring-[#E09028]">
                            <div>
                                <div class="font-bold text-sm text-[#2E190E] flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#E09028]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                        </path>
                                    </svg>
                                    <span>Mã Giảm Giá Đơn Hàng</span>
                                </div>
                                <p class="text-xs text-[#615248] mt-1 font-normal leading-relaxed">
                                    Giảm trực tiếp vào tiền sản phẩm trong giỏ hàng (Toàn shop, theo danh mục hoặc sản
                                    phẩm).
                                </p>
                            </div>
                        </label>

                        {{-- Option 2: Mã Vận Chuyển --}}
                        <label
                            class="relative flex items-start gap-3.5 p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                            :class="voucher_type === 'SHIPPING' ?
                                'border-teal-500 bg-teal-50/50 shadow-xs ring-2 ring-teal-500/20' :
                                'border-[#EBDDCD] hover:border-teal-200 bg-white'">
                            <input type="radio" name="voucher_type" value="SHIPPING" x-model="voucher_type"
                                @change="onVoucherTypeChange()" class="mt-1 text-teal-600 focus:ring-teal-500">
                            <div>
                                <div class="font-bold text-sm text-[#2E190E] flex items-center gap-2">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                        </path>
                                    </svg>
                                    <span>Mã Giảm Phí Vận Chuyển</span>
                                </div>
                                <p class="text-xs text-[#615248] mt-1 font-normal leading-relaxed">
                                    Miễn phí hoặc giảm trừ chi phí giao hàng của đơn.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 2. Thông Tin Mã Voucher --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EBDDCD] shadow-xs space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-6 h-6 bg-[#FFF5E6] text-[#5C3219] font-bold rounded-lg text-xs flex items-center justify-center shrink-0 border border-[#EBDDCD]">02</span>
                        <span class="text-sm font-bold text-[#2E190E] uppercase tracking-wide">Mã Voucher</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#2E190E] mb-1.5">
                            Mã Voucher <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="code" x-model="code"
                                    @input="code = code.toUpperCase().replace(/[^A-Z0-9_\-]/g, '')" required
                                    class="uppercase font-bold tracking-widest w-full rounded-xl border-[#EBDDCD] focus:border-[#DDA760] focus:ring-[#DDA760] text-sm py-2.5 px-3.5 bg-[#FAF6F0] text-[#2E190E]">
                            </div>

                            <button type="button" @click="generateRandomCode()"
                                class="px-3.5 py-2.5 bg-[#FFF5E6] hover:bg-[#FAF6F0] text-[#5C3219] border border-[#EBDDCD] rounded-xl text-xs font-semibold transition flex items-center gap-2 shrink-0 shadow-xs"
                                title="Đổi mã ngẫu nhiên">
                                <svg class="w-4 h-4 text-[#E09028]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                <span>Đổi Mã Mới</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-[#8E8076] mt-1 font-normal">Chỉ gồm ký tự in hoa, số và dấu gạch
                            ngang (Mã đơn bắt đầu bằng <strong class="text-[#5C3219] font-semibold">BEAR-</strong>, mã
                            vận chuyển bắt đầu bằng <strong class="text-teal-800 font-semibold">SHIP-</strong>).</p>
                        <input type="hidden" name="status" :value="status">
                        @error('code')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 3. Phạm Vi Áp Dụng (Chỉ hiển thị khi voucher_type === 'ORDER') --}}
                <div x-show="voucher_type === 'ORDER'" x-transition
                    class="bg-white rounded-2xl p-5 sm:p-6 border border-[#EBDDCD] shadow-xs space-y-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-6 h-6 bg-[#FFF5E6] text-[#5C3219] font-bold rounded-lg text-xs flex items-center justify-center shrink-0 border border-[#EBDDCD]">03</span>
                            <span class="text-sm font-bold text-[#2E190E] uppercase tracking-wide">Phạm Vi Áp
                                Dụng</span>
                        </div>
                        <span class="text-xs text-[#7E4A28] font-normal">Chọn sản phẩm được áp dụng mã</span>
                    </div>

                    {{-- 3 Scope Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Scope 1: Toàn bộ cửa hàng --}}
                        <label
                            class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                            :class="apply_scope === 'ALL' ?
                                'border-[#E09028] bg-[#FFF5E6] ring-2 ring-[#E09028]/20 shadow-xs' :
                                'border-[#EBDDCD] hover:border-[#DDA760] bg-white'">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                                <input type="radio" name="apply_scope" value="ALL" x-model="apply_scope"
                                    class="text-[#E09028] focus:ring-[#E09028]">
                            </div>
                            <div class="mt-2.5">
                                <div class="font-bold text-sm text-[#2E190E]">Toàn Bộ Shop</div>
                                <p class="text-xs text-[#615248] mt-1 font-normal leading-relaxed">
                                    Áp dụng cho tất cả sản phẩm đang bán.
                                </p>
                            </div>
                        </label>

                        {{-- Scope 2: Theo Danh Mục --}}
                        <label
                            class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                            :class="apply_scope === 'CATEGORY' ?
                                'border-[#E09028] bg-[#FFF5E6] ring-2 ring-[#E09028]/20 shadow-xs' :
                                'border-[#EBDDCD] hover:border-[#DDA760] bg-white'">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <input type="radio" name="apply_scope" value="CATEGORY" x-model="apply_scope"
                                    class="text-[#E09028] focus:ring-[#E09028]">
                            </div>
                            <div class="mt-2.5">
                                <div class="font-bold text-sm text-[#2E190E]">Theo Danh Mục</div>
                                <p class="text-xs text-[#615248] mt-1 font-normal leading-relaxed">
                                    Chỉ áp dụng cho các danh mục được chọn.
                                </p>
                            </div>
                        </label>

                        {{-- Scope 3: Theo Sản Phẩm Cụ Thể --}}
                        <label
                            class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                            :class="apply_scope === 'PRODUCT' ?
                                'border-[#E09028] bg-[#FFF5E6] ring-2 ring-[#E09028]/20 shadow-xs' :
                                'border-[#EBDDCD] hover:border-[#DDA760] bg-white'">
                            <div class="flex items-center justify-between">
                                <div
                                    class="w-8 h-8 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <input type="radio" name="apply_scope" value="PRODUCT" x-model="apply_scope"
                                    class="text-[#E09028] focus:ring-[#E09028]">
                            </div>
                            <div class="mt-2.5">
                                <div class="font-bold text-sm text-[#2E190E]">Sản Phẩm Cụ Thể</div>
                                <p class="text-xs text-[#615248] mt-1 font-normal leading-relaxed">
                                    Chỉ định 1 hoặc nhiều sản phẩm cụ thể.
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Fallback hidden input when shipping --}}
                    <template x-if="voucher_type === 'SHIPPING'">
                        <input type="hidden" name="apply_scope" value="ALL">
                    </template>

                    {{-- DETAIL 1: CATEGORY SELECTION --}}
                    <div x-show="apply_scope === 'CATEGORY'" x-transition
                        class="pt-4 border-t border-amber-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Danh Mục Áp Dụng <span class="text-rose-500">*</span>
                                <span class="ml-2 font-normal text-amber-800"
                                    x-text="'(Đã chọn ' + selectedCategories.length + ' danh mục)'"></span>
                            </label>
                            <div class="flex items-center gap-2 text-xs">
                                <button type="button"
                                    @click="selectedCategories = [{{ $categories->pluck('id')->join(',') }}]"
                                    class="text-amber-700 font-bold hover:underline">Chọn tất cả</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="selectedCategories = []"
                                    class="text-gray-500 hover:underline">Bỏ chọn</button>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto pr-1">
                            @foreach ($categories as $category)
                                <label
                                    class="flex items-center gap-2.5 p-3 rounded-xl border transition cursor-pointer select-none"
                                    :class="selectedCategories.includes({{ $category->id }}) ?
                                        'border-amber-500 bg-amber-50/80 text-amber-950 font-bold' :
                                        'border-gray-200 bg-white text-gray-700 hover:bg-amber-50/20'">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                        x-model="selectedCategories"
                                        class="rounded text-amber-600 focus:ring-amber-500">
                                    <span class="text-xs flex-1 truncate">{{ $category->name }}</span>
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-semibold shrink-0">{{ $category->products_count }}
                                        SP</span>
                                </label>
                            @endforeach
                        </div>
                        @error('category_ids')
                            <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DETAIL 2: PRODUCT SELECTION --}}
                    <div x-show="apply_scope === 'PRODUCT'" x-transition
                        class="pt-4 border-t border-amber-100 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Danh Sách Sản Phẩm Áp Dụng <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-xs text-amber-800 font-semibold">Đã chọn: <strong
                                        x-text="selectedProducts.length" class="text-rose-600 font-black">0</strong>
                                    sản phẩm</span>
                            </div>

                            <div class="flex items-center gap-2 text-xs">
                                <button type="button"
                                    @click="selectedProducts = [{{ $products->pluck('id')->join(',') }}]"
                                    class="text-amber-700 font-bold hover:underline">Chọn tất cả</button>
                                <span class="text-gray-300">|</span>
                                <button type="button" @click="selectedProducts = []"
                                    class="text-gray-500 hover:underline">Bỏ chọn hết</button>
                            </div>
                        </div>

                        {{-- Product Filter Bar --}}
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                            <div class="sm:col-span-7 relative">
                                <input type="text" x-model="searchProduct"
                                    placeholder="🔍 Tìm theo tên gấu bông..."
                                    class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-xs py-2 px-3 pl-8">
                            </div>
                            <div class="sm:col-span-5">
                                <select x-model="filterCategory"
                                    class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-xs py-2 px-2.5">
                                    <option value="">Tất cả danh mục</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Products Grid --}}
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1 border border-amber-100 rounded-xl p-2.5 bg-amber-50/20">
                            @foreach ($products as $product)
                                @php
                                    $imgUrl =
                                        $product->images->first()?->image_url ??
                                        asset('images/product-placeholder.png');
                                    if (
                                        $imgUrl &&
                                        !str_starts_with($imgUrl, 'http') &&
                                        !str_starts_with($imgUrl, '/')
                                    ) {
                                        $imgUrl = asset('storage/' . $imgUrl);
                                    }
                                @endphp
                                <label
                                    class="flex items-center gap-3 p-2 rounded-xl border transition cursor-pointer select-none bg-white"
                                    :class="selectedProducts.includes({{ $product->id }}) ?
                                        'border-amber-500 bg-amber-50/70 ring-1 ring-amber-500/30' :
                                        'border-gray-200 hover:border-amber-200'"
                                    x-show="matchesProduct({{ $product->id }}, '{{ addslashes(mb_strtolower($product->name)) }}', '{{ $product->category_id }}')">
                                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                                        x-model="selectedProducts"
                                        class="rounded text-amber-600 focus:ring-amber-500">
                                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}"
                                        class="w-10 h-10 rounded-lg object-cover border border-amber-100 shrink-0 bg-white">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-gray-900 truncate">{{ $product->name }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span
                                                class="text-[11px] font-bold text-rose-600">{{ number_format($product->sale_price ?? $product->price, 0, ',', '.') }}đ</span>
                                            <span
                                                class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.2 rounded">{{ $product->category->name ?? 'Gấu bông' }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('product_ids')
                            <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 4. Mức Giảm Giá --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-amber-100/80 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-6 h-6 bg-amber-100 text-amber-900 font-bold rounded-lg text-xs flex items-center justify-center shrink-0">04</span>
                        <span class="text-sm font-bold text-amber-950 uppercase tracking-wide">Mức Giảm Giá</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        {{-- Loại Giảm --}}
                        <div class="sm:col-span-5">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Loại Giảm Giá <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="discount_type = 'PERCENTAGE'"
                                    class="py-2.5 px-3 rounded-xl border-2 text-xs font-bold transition flex items-center justify-center gap-1"
                                    :class="discount_type === 'PERCENTAGE' ? 'border-amber-500 bg-amber-50 text-amber-900' :
                                        'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <span>% Phần trăm</span>
                                </button>
                                <button type="button" @click="discount_type = 'FIXED'"
                                    class="py-2.5 px-3 rounded-xl border-2 text-xs font-bold transition flex items-center justify-center gap-1"
                                    :class="discount_type === 'FIXED' ? 'border-amber-500 bg-amber-50 text-amber-900' :
                                        'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <span>VNĐ Cố định</span>
                                </button>
                                <input type="hidden" name="discount_type" :value="discount_type">
                            </div>
                        </div>

                        {{-- Giá Trị Giảm --}}
                        <div class="sm:col-span-7">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Giá Trị Giảm <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="discount_value" x-model="discount_value"
                                    :max="discount_type === 'PERCENTAGE' ? 100 : null" :min="1"
                                    step="any" placeholder="VD: 15 hoặc 30000" required
                                    class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none w-full pr-14 rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-semibold text-amber-950 py-2.5 px-3.5">
                                <span class="absolute right-3.5 top-2.5 text-xs font-bold text-gray-400"
                                    x-text="discount_type === 'PERCENTAGE' ? '%' : 'VNĐ'"></span>
                            </div>
                            <div x-show="discount_type === 'FIXED' && discount_value > 0"
                                class="mt-1 flex items-center gap-1.5 text-xs font-medium text-amber-800 bg-amber-50/80 px-2.5 py-1 rounded-lg border border-amber-200/60 w-fit">
                                <span>🏷️</span>
                                <span>Bằng chữ: <span class="text-amber-950 font-bold"
                                        x-text="formatCurrency(discount_value)"></span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Giảm Tối Đa (Nếu chọn %) --}}
                    <div x-show="discount_type === 'PERCENTAGE'" x-transition
                        class="bg-amber-50/60 p-4 rounded-xl border border-amber-200/70">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-amber-950">
                                Mức Giảm Tối Đa (VNĐ)
                            </label>
                            <span class="text-[11px] text-amber-700 font-normal">Tùy chọn</span>
                        </div>
                        <div class="relative">
                            <input type="number" name="max_discount_value" x-model="max_discount_value"
                                placeholder="VD: 50000 (Để trống nếu không giới hạn tối đa)" min="0"
                                class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none w-full pr-14 rounded-xl border-amber-200 focus:border-amber-500 focus:ring-amber-500 text-sm py-2 px-3.5 bg-white font-medium text-amber-950">
                            <span class="absolute right-3.5 top-2 text-xs font-bold text-gray-400">VNĐ</span>
                        </div>
                        <div x-show="max_discount_value > 0"
                            class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-amber-800 bg-white/80 px-2.5 py-0.5 rounded-lg border border-amber-200/60 w-fit">
                            <span>🛡️</span>
                            <span>Tối đa: <span class="text-amber-950 font-bold"
                                    x-text="formatCurrency(max_discount_value)"></span></span>
                        </div>
                    </div>
                </div>

                {{-- 5. Điều Kiện & Thời Gian Áp Dụng --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-amber-100/80 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="w-6 h-6 bg-amber-100 text-amber-900 font-bold rounded-lg text-xs flex items-center justify-center shrink-0">05</span>
                        <span class="text-sm font-bold text-amber-950 uppercase tracking-wide">Điều Kiện & Thời
                            Gian</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Đơn Tối Thiểu --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Đơn Hàng Tối Thiểu (VNĐ)
                            </label>
                            <div class="relative">
                                <input type="number" name="min_order_value" x-model="min_order_value"
                                    placeholder="0" min="0"
                                    class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none w-full pr-14 rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm py-2.5 px-3.5 font-medium text-gray-800">
                                <span class="absolute right-3.5 top-2.5 text-xs font-bold text-gray-400">VNĐ</span>
                            </div>
                            <div x-show="min_order_value > 0"
                                class="mt-1 flex items-center gap-1.5 text-[11px] font-medium text-amber-800 bg-amber-50/80 px-2 py-0.5 rounded-lg border border-amber-200/60 w-fit">
                                <span>📦</span>
                                <span>Đơn từ: <span class="text-amber-950 font-bold"
                                        x-text="formatCurrency(min_order_value)"></span></span>
                            </div>
                            <p x-show="!min_order_value || min_order_value == 0"
                                class="text-[11px] text-gray-400 mt-1 font-normal">Nhập 0 nếu áp dụng cho mọi đơn.</p>
                        </div>

                        {{-- Số Lượng Lượt Dùng --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Tổng Số Lượt Dùng <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="usage_limit" x-model="usage_limit" :min="used_count"
                                required
                                class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm py-2.5 px-3.5 font-semibold text-amber-950">
                            <p class="text-[11px] text-gray-400 mt-1 font-normal">Đã có <strong
                                    class="text-amber-800 font-semibold" x-text="used_count"></strong> khách dùng mã
                                này.</p>
                        </div>
                    </div>

                    {{-- Date Presets --}}
                    <div class="pt-3 border-t border-gray-100">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-semibold text-gray-600">Gia hạn nhanh:</span>
                            <div class="flex gap-1.5 flex-wrap">
                                <button type="button" @click="setPresetDays(1)"
                                    class="px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-800 hover:bg-amber-100 rounded-lg transition border border-amber-200/60">+1
                                    ngày</button>
                                <button type="button" @click="setPresetDays(7)"
                                    class="px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-800 hover:bg-amber-100 rounded-lg transition border border-amber-200/60">+7
                                    ngày</button>
                                <button type="button" @click="setPresetDays(15)"
                                    class="px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-800 hover:bg-amber-100 rounded-lg transition border border-amber-200/60">+15
                                    ngày</button>
                                <button type="button" @click="setPresetDays(30)"
                                    class="px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-900 hover:bg-amber-200 rounded-lg transition border border-amber-300">+30
                                    ngày</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Ngày Bắt Đầu --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Thời Gian Bắt Đầu <span class="text-rose-500">*</span>
                                </label>
                                <x-datetime-picker name="start_date" x-model="start_date"
                                    placeholder="Chọn thời gian bắt đầu..." :required="true" />
                                <p class="text-[11px] text-gray-400 mt-1 font-normal">Thời điểm voucher bắt đầu có hiệu
                                    lực.</p>
                            </div>

                            {{-- Ngày Kết Thúc --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Thời Gian Kết Thúc <span class="text-rose-500">*</span>
                                </label>
                                <x-datetime-picker name="end_date" x-model="end_date"
                                    placeholder="Chọn thời gian kết thúc..." :required="true" />
                                <p class="text-[11px] text-gray-400 mt-1 font-normal">Phải lớn hơn thời gian bắt đầu.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Submit Buttons --}}
                <div class="pt-2 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.vouchers.index') }}"
                        class="px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 font-medium rounded-xl text-sm border border-gray-200 transition">
                        Hủy bỏ
                    </a>
                    <button type="submit"
                        class="px-7 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold rounded-xl text-sm shadow-md shadow-amber-500/20 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Cập Nhật Voucher</span>
                    </button>
                </div>

        </div>

        {{-- Right Column: Live Ticket Preview --}}
        <div class="lg:col-span-4 sticky top-6 space-y-4">
            <div class="text-xs font-bold text-[#7E4A28] uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-[#E09028]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                </svg>
                <span>Xem Trước Thẻ Voucher</span>
            </div>

            {{-- Voucher Ticket Card --}}
            <div class="rounded-3xl shadow-xl p-6 text-white relative overflow-hidden transition-all duration-300"
                :class="voucher_type === 'SHIPPING' ? 'bg-gradient-to-br from-teal-600 via-emerald-600 to-teal-700' :
                    'bg-gradient-to-br from-[#E09028] via-[#7E4A28] to-[#5C3219]'">

                {{-- Decorative cutout notches --}}
                <div class="absolute -left-3.5 top-1/2 -translate-y-1/2 w-7 h-7 bg-[#F9F5EE] rounded-full"></div>
                <div class="absolute -right-3.5 top-1/2 -translate-y-1/2 w-7 h-7 bg-[#F9F5EE] rounded-full"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-white/20">
                    <div class="flex items-center gap-2">
                        <template x-if="voucher_type === 'SHIPPING'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                </path>
                            </svg>
                        </template>
                        <template x-if="voucher_type === 'ORDER'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                        </template>
                        <span class="font-bold text-sm tracking-wider uppercase"
                            x-text="voucher_type === 'SHIPPING' ? 'FREESHIP / SHIP' : 'MẬT NGỌT BEAR'"></span>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase"
                        :class="status === 'ACTIVE' ? 'bg-emerald-400/30 text-emerald-100 border border-emerald-300/40' :
                            'bg-rose-400/30 text-rose-100 border border-rose-300/40'"
                        x-text="status === 'ACTIVE' ? 'ĐANG ÁP DỤNG' : 'VÔ HIỆU HÓA'">
                    </span>
                </div>

                {{-- Body --}}
                <div class="py-4 space-y-3">
                    <div class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm"
                        x-text="previewDiscountText">
                        Giảm 0%
                    </div>
                    <p class="text-xs leading-relaxed opacity-90 font-normal" x-text="previewConditionText">
                        Áp dụng cho mọi giá trị đơn hàng
                    </p>

                    {{-- Scope Badge on ticket --}}
                    <div
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/20 backdrop-blur-sm text-[11px] font-medium">
                        <span x-text="previewScopeText"></span>
                    </div>

                    {{-- Code box --}}
                    <div
                        class="bg-black/20 backdrop-blur-sm rounded-2xl p-3 border border-white/20 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-normal uppercase opacity-75">Mã ưu đãi</div>
                            <div class="font-bold text-base sm:text-lg tracking-widest text-amber-200"
                                x-text="code || 'MÃ_VOUCHER'"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] font-normal uppercase opacity-75">Đã dùng / Tổng</div>
                            <div class="text-xs font-bold text-white"
                                x-text="used_count + ' / ' + (usage_limit || 0) + ' lượt'"></div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="pt-3 border-t border-white/20 text-[11px] opacity-80 flex items-center justify-between">
                    <span class="font-normal">Hạn sử dụng:</span>
                    <span class="font-semibold" x-text="previewEndDate"></span>
                </div>
            </div>

            {{-- Usage Info Box --}}
            <div
                class="bg-white rounded-2xl p-4 border border-[#EBDDCD] text-xs text-[#615248] leading-relaxed space-y-2 shadow-xs font-normal">
                <div class="font-bold text-[#2E190E] flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#E09028]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    <span>Tình trạng sử dụng:</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#E09028] h-2 rounded-full transition-all"
                        :style="'width: ' + Math.min(100, (usage_limit > 0 ? (used_count / usage_limit * 100) : 0)) + '%'">
                    </div>
                </div>
                <div class="flex justify-between text-[11px] text-[#8E8076] font-medium">
                    <span>Đã dùng: <strong class="text-[#5C3219] font-semibold" x-text="used_count"></strong>
                        lượt</span>
                    <span>Còn lại: <strong class="text-emerald-700 font-semibold"
                            x-text="Math.max(0, usage_limit - used_count)"></strong> lượt</span>
                </div>
            </div>
        </div>

        </form>

    </div>
    </div>
</x-app-layout>

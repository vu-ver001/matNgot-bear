<x-app-layout>
    <div class="py-8 bg-[#FAF6EE] min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. Breadcrumb & Header --}}
            <x-breadcrumb :items="[
                ['label' => 'Quản Lý Mã Voucher', 'url' => route('admin.vouchers.index')],
                ['label' => 'Chỉnh Sửa [' . $voucher->code . ']']
            ]" />

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-[#5C3219] flex items-center justify-center text-[#F6D89B] shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#2C1408] tracking-tight">Chỉnh Sửa Voucher</h1>
                            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#FFF5E6] text-[#E08A1E] border border-[#E08A1E]/30">
                                {{ $voucher->code }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-medium text-[#786B61] mt-0.5">
                            Cập nhật thông tin chiết khấu, phạm vi áp dụng, hạn dùng và lượt sử dụng của voucher
                        </p>
                    </div>
                </div>

                <a href="{{ route('admin.vouchers.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-[#FFF5E6] text-[#5C3219] border border-[#EBDDCD] rounded-xl font-bold text-xs shadow-xs transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Quay lại danh sách</span>
                </a>
            </div>

            {{-- 2. Form Container --}}
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
                status: '{{ old('status', $voucher->status) }}',
            })"
                class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                @csrf
                @method('PUT')

                {{-- Hidden Inputs for Form Submission --}}
                <input type="hidden" name="voucher_type" :value="voucher_type">
                <input type="hidden" name="apply_scope" :value="apply_scope">
                <input type="hidden" name="discount_type" :value="discount_type">

                {{-- ========================================================================= --}}
                {{-- LEFT COLUMN: Form Sections --}}
                {{-- ========================================================================= --}}
                <div class="lg:col-span-8 space-y-5">

                    {{-- 01. PHÂN LOẠI VOUCHER --}}
                    <div class="voucher-form-card space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="voucher-section-badge">01</span>
                                <span class="voucher-section-title">PHÂN LOẠI VOUCHER</span>
                            </div>
                            {{-- Status toggle --}}
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-[#786B61]">Trạng thái:</span>
                                <select name="status" x-model="status"
                                    class="bg-[#FAF8F5] border border-[#EBDDCD] rounded-xl text-xs font-bold text-[#2C1408] px-3 py-1.5 focus:border-[#E08A1E] focus:ring-0">
                                    <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                                    <option value="INACTIVE">Tạm dừng (INACTIVE)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            {{-- Option 1: Mã Đơn Hàng --}}
                            <div @click="voucher_type = 'ORDER'; onVoucherTypeChange()" class="voucher-option-card"
                                :class="voucher_type === 'ORDER' ? 'is-active' : ''">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-[#FFF5E6] text-[#E08A1E] flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-sm text-[#2C1408]">Mã Giảm Giá Đơn Hàng</h3>
                                            <p class="text-xs text-[#786B61] mt-1 font-normal leading-relaxed">
                                                Giảm trực tiếp vào tiền sản phẩm trong giỏ hàng (Toàn shop, theo danh mục hoặc sản phẩm).
                                            </p>
                                        </div>
                                    </div>
                                    <div class="custom-radio-dot" :class="voucher_type === 'ORDER' ? 'is-active' : ''"></div>
                                </div>
                            </div>

                            {{-- Option 2: Mã Vận Chuyển --}}
                            <div @click="voucher_type = 'SHIPPING'; onVoucherTypeChange()" class="voucher-option-card"
                                :class="voucher_type === 'SHIPPING' ? 'is-active' : ''">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-[#FAF8F5] text-[#786B61] flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-sm text-[#2C1408]">Mã Giảm Phí Vận Chuyển</h3>
                                            <p class="text-xs text-[#786B61] mt-1 font-normal leading-relaxed">
                                                Miễn phí hoặc giảm trừ chi phí giao hàng của đơn.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="custom-radio-dot" :class="voucher_type === 'SHIPPING' ? 'is-active' : ''"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 02. MÃ VOUCHER --}}
                    <div class="voucher-form-card space-y-3.5">
                        <div class="flex items-center gap-2.5">
                            <span class="voucher-section-badge">02</span>
                            <span class="voucher-section-title">MÃ VOUCHER</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                                Mã Voucher <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                <input type="text" name="code" x-model="code"
                                    placeholder="VD: BEAR-2026, FREESHIP" required
                                    class="flex-1 bg-white border border-[#EBDDCD] rounded-xl px-4 py-2.5 text-sm font-bold text-[#2C1408] uppercase placeholder:normal-case placeholder:text-[#9CA3AF] placeholder:font-normal focus:border-[#E08A1E] focus:ring-0">

                                <button type="button" @click="generateRandomCode()"
                                    class="border border-[#E08A1E] bg-[#FFFBF4] hover:bg-[#FFF5E6] text-[#2C1408] font-bold text-xs px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition cursor-pointer shrink-0">
                                    <svg class="w-4 h-4 text-[#E08A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    <span>Tạo Mã Mới</span>
                                </button>
                            </div>
                            <p class="text-xs text-[#9CA3AF] mt-1.5">
                                Chỉ gồm ký tự in hoa, số và dấu gạch ngang (Mã đơn bắt đầu bằng <strong>BEAR-</strong>, mã vận chuyển bắt đầu bằng <strong>SHIP-</strong>).
                            </p>
                            @error('code')
                                <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 03. PHẠM VI ÁP DỤNG --}}
                    <div class="voucher-form-card space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="voucher-section-badge">03</span>
                                <span class="voucher-section-title">PHẠM VI ÁP DỤNG</span>
                            </div>
                            <span class="text-xs text-[#9CA3AF] font-medium hidden sm:inline">Chọn sản phẩm được áp dụng mã</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Scope ALL --}}
                            <div @click="apply_scope = 'ALL'" class="voucher-option-card"
                                :class="apply_scope === 'ALL' ? 'is-active' : ''">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="text-xl">🌐</div>
                                    <div class="custom-radio-dot" :class="apply_scope === 'ALL' ? 'is-active' : ''"></div>
                                </div>
                                <h4 class="font-bold text-sm text-[#2C1408]">Toàn Bộ Shop</h4>
                                <p class="text-xs text-[#786B61] mt-1 leading-relaxed">
                                    Áp dụng cho tất cả sản phẩm đang bán.
                                </p>
                            </div>

                            {{-- Scope CATEGORY --}}
                            <div @click="apply_scope = 'CATEGORY'" class="voucher-option-card"
                                :class="apply_scope === 'CATEGORY' ? 'is-active' : ''">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="text-xl">📁</div>
                                    <div class="custom-radio-dot" :class="apply_scope === 'CATEGORY' ? 'is-active' : ''"></div>
                                </div>
                                <h4 class="font-bold text-sm text-[#2C1408]">Theo Danh Mục</h4>
                                <p class="text-xs text-[#786B61] mt-1 leading-relaxed">
                                    Chỉ áp dụng cho các danh mục được chọn.
                                </p>
                            </div>

                            {{-- Scope PRODUCT --}}
                            <div @click="apply_scope = 'PRODUCT'" class="voucher-option-card"
                                :class="apply_scope === 'PRODUCT' ? 'is-active' : ''">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="text-xl">⬡</div>
                                    <div class="custom-radio-dot" :class="apply_scope === 'PRODUCT' ? 'is-active' : ''"></div>
                                </div>
                                <h4 class="font-bold text-sm text-[#2C1408]">Sản Phẩm Cụ Thể</h4>
                                <p class="text-xs text-[#786B61] mt-1 leading-relaxed">
                                    Chỉ định 1 hoặc nhiều sản phẩm cụ thể.
                                </p>
                            </div>
                        </div>

                        {{-- CATEGORY PICKER --}}
                        <div x-show="apply_scope === 'CATEGORY'" x-transition class="pt-3 border-t border-[#EBDDCD]">
                            <label class="block text-xs font-bold text-[#2C1408] mb-2">
                                Chọn danh mục áp dụng <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border border-[#EBDDCD] rounded-xl bg-[#FAF8F5]">
                                @foreach ($categories as $cat)
                                    <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-white cursor-pointer transition">
                                        <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                            x-model="selectedCategories"
                                            class="rounded text-[#E08A1E] focus:ring-[#E08A1E] border-[#EBDDCD]">
                                        <span class="text-xs font-bold text-[#2C1408]">{{ $cat->name }}</span>
                                        <span class="text-[10px] text-[#9CA3AF] ml-auto">({{ $cat->products_count }} sp)</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('category_ids')
                                <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PRODUCT PICKER --}}
                        <div x-show="apply_scope === 'PRODUCT'" x-transition class="pt-3 border-t border-[#EBDDCD] space-y-2.5">
                            <label class="block text-xs font-bold text-[#2C1408]">
                                Chọn sản phẩm áp dụng <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="searchProduct" placeholder="Tìm kiếm sản phẩm..."
                                class="w-full bg-white border border-[#EBDDCD] rounded-xl px-3 py-2 text-xs focus:border-[#E08A1E] focus:ring-0">

                            <div class="max-h-56 overflow-y-auto border border-[#EBDDCD] rounded-xl bg-[#FAF8F5] divide-y divide-[#EBDDCD]">
                                @foreach ($products as $prod)
                                    <label x-show="matchesProduct({{ $prod->id }}, '{{ strtolower(addslashes($prod->name)) }}', {{ $prod->category_id ?? 0 }})"
                                        class="flex items-center gap-3 p-2.5 hover:bg-white cursor-pointer transition">
                                        <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}"
                                            x-model="selectedProducts"
                                            class="rounded text-[#E08A1E] focus:ring-[#E08A1E] border-[#EBDDCD]">
                                        @php
                                            $primaryImg = $prod->images->firstWhere('is_primary', true) ?? $prod->images->first();
                                            $imgUrl = $primaryImg?->image_url;
                                            if ($imgUrl && !str_starts_with($imgUrl, 'http') && !str_starts_with($imgUrl, '/') && !str_starts_with($imgUrl, 'storage/')) {
                                                $imgUrl = asset('storage/' . $imgUrl);
                                            } elseif ($imgUrl && (str_starts_with($imgUrl, 'storage/') || str_starts_with($imgUrl, '/'))) {
                                                $imgUrl = asset($imgUrl);
                                            } else {
                                                $imgUrl = $imgUrl ?: 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=400&q=80';
                                            }
                                        @endphp
                                        <img src="{{ $imgUrl }}"
                                            class="w-9 h-9 rounded-lg object-cover border border-[#EBDDCD] shrink-0 bg-white"
                                            alt="{{ $prod->name }}"
                                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=400&q=80';">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-[#2C1408] truncate">{{ $prod->name }}</div>
                                            <div class="text-[11px] text-[#E08A1E] font-semibold">
                                                {{ number_format($prod->sale_price ?? $prod->price, 0, ',', '.') }}đ
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('product_ids')
                                <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 04. MỨC GIẢM GIÁ --}}
                    <div class="voucher-form-card space-y-4">
                        <div class="flex items-center gap-2.5">
                            <span class="voucher-section-badge">04</span>
                            <span class="voucher-section-title">MỨC GIẢM GIÁ</span>
                        </div>

                        {{-- Row 1: Loại Giảm Giá & Giá Trị Giảm --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Loại Giảm Giá --}}
                            <div>
                                <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                                    Loại Giảm Giá <span class="text-rose-500">*</span>
                                </label>
                                <div class="inline-flex rounded-xl p-1 bg-[#FAF8F5] border border-[#EBDDCD] w-full">
                                    <button type="button" @click="discount_type = 'PERCENTAGE'"
                                        class="flex-1 py-2 px-3 rounded-lg font-bold text-xs transition"
                                        :class="discount_type === 'PERCENTAGE' ? 'bg-[#E08A1E] text-white shadow-xs' : 'text-[#2C1408] hover:text-[#E08A1E]'">
                                        % Phần trăm
                                    </button>
                                    <button type="button" @click="discount_type = 'FIXED'"
                                        class="flex-1 py-2 px-3 rounded-lg font-bold text-xs transition"
                                        :class="discount_type === 'FIXED' ? 'bg-[#E08A1E] text-white shadow-xs' : 'text-[#2C1408] hover:text-[#E08A1E]'">
                                        VNĐ Cố định
                                    </button>
                                </div>
                            </div>

                            {{-- Giá Trị Giảm --}}
                            <div>
                                <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                                    Giá Trị Giảm <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="discount_value" x-model="discount_value"
                                        placeholder="VD: 15 hoặc 30" min="1" step="any" required
                                        class="w-full bg-white border border-[#EBDDCD] rounded-xl px-4 py-2.5 pr-10 text-sm font-bold text-[#2C1408] placeholder:text-[#9CA3AF] placeholder:font-normal focus:border-[#E08A1E] focus:ring-0">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-[#786B61]"
                                        x-text="discount_type === 'PERCENTAGE' ? '%' : 'VNĐ'">%</span>
                                </div>
                                @error('discount_value')
                                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Row 2: Mức Giảm Tối Đa (VNĐ) --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-[#2C1408]">Mức Giảm Tối Đa (VNĐ)</label>
                                <span class="text-xs text-[#9CA3AF]">Tùy chọn</span>
                            </div>
                            <div class="relative">
                                <input type="number" name="max_discount_value" x-model="max_discount_value"
                                    placeholder="VD: 50000 (Để trống nếu không giới hạn tối đa)" min="0" step="any"
                                    class="w-full bg-white border border-[#EBDDCD] rounded-xl px-4 py-2.5 pr-14 text-sm font-bold text-[#2C1408] placeholder:text-[#9CA3AF] placeholder:font-normal focus:border-[#E08A1E] focus:ring-0">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-[#786B61]">VNĐ</span>
                            </div>
                            @error('max_discount_value')
                                <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- 05. ĐIỀU KIỆN & THỜI GIAN --}}
                    <div class="voucher-form-card space-y-4">
                        <div class="flex items-center gap-2.5">
                            <span class="voucher-section-badge">05</span>
                            <span class="voucher-section-title">ĐIỀU KIỆN & THỜI GIAN</span>
                        </div>

                        {{-- Row 1: Đơn Hàng Tối Thiểu & Tổng Số Lượt Dùng --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                                    Đơn Hàng Tối Thiểu (VNĐ)
                                </label>
                                <div class="relative">
                                    <input type="number" name="min_order_value" x-model="min_order_value"
                                        min="0" step="any"
                                        class="w-full bg-white border border-[#EBDDCD] rounded-xl px-4 py-2.5 pr-14 text-sm font-bold text-[#2C1408] focus:border-[#E08A1E] focus:ring-0">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-[#786B61]">VNĐ</span>
                                </div>
                                <p class="text-xs text-[#9CA3AF] mt-1">Nhập 0 nếu áp dụng cho mọi đơn.</p>
                                @error('min_order_value')
                                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-[#2C1408]">
                                        Tổng Số Lượt Dùng <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-xs text-[#E08A1E] font-bold">Đã dùng: {{ $voucher->used_count }} lượt</span>
                                </div>
                                <input type="number" name="usage_limit" x-model="usage_limit" min="{{ max(1, $voucher->used_count) }}" required
                                    class="w-full bg-white border border-[#EBDDCD] rounded-xl px-4 py-2.5 text-sm font-bold text-[#2C1408] focus:border-[#E08A1E] focus:ring-0">
                                <p class="text-xs text-[#9CA3AF] mt-1">Phải lớn hơn hoặc bằng số lượt đã dùng ({{ $voucher->used_count }}).</p>
                                @error('usage_limit')
                                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Quick Duration Presets --}}
                        <div>
                            <label class="block text-xs font-bold text-[#2C1408] mb-2">
                                Gia hạn nhanh:
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setPresetDays(1)"
                                    :class="activePresetDays === 1 ? 'bg-[#E08A1E] text-white shadow-xs' :
                                        'border border-[#E08A1E] text-[#2C1408] hover:bg-[#FFFBF4]'"
                                    class="px-4 py-1.5 rounded-full font-bold text-xs transition cursor-pointer">
                                    +1 ngày
                                </button>
                                <button type="button" @click="setPresetDays(7)"
                                    :class="activePresetDays === 7 ? 'bg-[#E08A1E] text-white shadow-xs' :
                                        'border border-[#E08A1E] text-[#2C1408] hover:bg-[#FFFBF4]'"
                                    class="px-4 py-1.5 rounded-full font-bold text-xs transition cursor-pointer">
                                    +7 ngày
                                </button>
                                <button type="button" @click="setPresetDays(15)"
                                    :class="activePresetDays === 15 ? 'bg-[#E08A1E] text-white shadow-xs' :
                                        'border border-[#E08A1E] text-[#2C1408] hover:bg-[#FFFBF4]'"
                                    class="px-4 py-1.5 rounded-full font-bold text-xs transition cursor-pointer">
                                    +15 ngày
                                </button>
                                <button type="button" @click="setPresetDays(30)"
                                    :class="activePresetDays === 30 ? 'bg-[#E08A1E] text-white shadow-xs' :
                                        'border border-[#E08A1E] text-[#2C1408] hover:bg-[#FFFBF4]'"
                                    class="px-4 py-1.5 rounded-full font-bold text-xs transition cursor-pointer">
                                    +30 ngày
                                </button>
                            </div>
                        </div>

                        {{-- Row 2: Thời Gian Bắt Đầu & Kết Thúc --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                                    Thời Gian Bắt Đầu <span class="text-rose-500">*</span>
                                </label>
                                <x-datetime-picker name="start_date" :value="old('start_date', $voucher->start_date?->format('Y-m-d H:i'))" />
                                <p class="text-xs text-[#9CA3AF] mt-1">Thời điểm voucher bắt đầu có hiệu lực.</p>
                                @error('start_date')
                                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                                    Thời Gian Kết Thúc <span class="text-rose-500">*</span>
                                </label>
                                <x-datetime-picker name="end_date" :value="old('end_date', $voucher->end_date?->format('Y-m-d H:i'))" />
                                <p class="text-xs text-[#9CA3AF] mt-1">Phải lớn hơn thời gian bắt đầu.</p>
                                @error('end_date')
                                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Form Bottom Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('admin.vouchers.index') }}"
                            class="bg-[#FAF8F5] border border-[#EBDDCD] hover:bg-white text-[#2C1408] font-bold px-6 py-3 rounded-2xl transition text-xs sm:text-sm">
                            Hủy bỏ
                        </a>
                        <button type="submit"
                            class="bg-gradient-to-r from-[#A8642A] to-[#E08A1E] hover:from-[#965520] hover:to-[#D17E17] text-white font-bold px-8 py-3 rounded-2xl shadow-lg shadow-[#E08A1E]/30 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2 text-xs sm:text-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span>Cập Nhật Voucher</span>
                        </button>
                    </div>

                </div>

                {{-- ========================================================================= --}}
                {{-- RIGHT COLUMN: Sticky Live Preview & Rule Box --}}
                {{-- ========================================================================= --}}
                <div class="lg:col-span-4 sticky top-6 space-y-4">

                    {{-- Header title --}}
                    <div class="text-[#C97810] text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#C97810]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>XEM TRƯỚC THẺ VOUCHER</span>
                    </div>

                    {{-- Live Voucher Ticket Card --}}
                    <div class="voucher-ticket-preview" :class="voucher_type === 'SHIPPING' ? 'theme-shipping' : 'theme-order'">
                        {{-- Top row: Brand & Status --}}
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold tracking-wider uppercase flex items-center gap-1.5"
                                :class="voucher_type === 'SHIPPING' ? 'text-[#A7F3D0]' : 'text-[#F6D89B]'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                                <span>MẬT NGỌT BEAR</span>
                            </div>
                            <span class="text-white text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider"
                                :class="status === 'ACTIVE' ? (voucher_type === 'SHIPPING' ? 'bg-[#059669]' : 'bg-[#00A86B]') : 'bg-gray-500'">
                                <span x-text="status === 'ACTIVE' ? 'HOẠT ĐỘNG' : 'TẠM DỪNG'">HOẠT ĐỘNG</span>
                            </span>
                        </div>

                        {{-- Big Discount Headline --}}
                        <div class="text-3xl sm:text-4xl font-extrabold text-white mt-4 tracking-tight"
                            x-text="previewDiscountText">
                            Giảm 0đ
                        </div>

                        {{-- Subtitle condition --}}
                        <div class="text-xs text-[#D1C4B5] font-medium mt-1" x-text="previewConditionText">
                            Áp dụng cho mọi giá trị đơn hàng
                        </div>

                        {{-- Scope capsule badge --}}
                        <div class="ticket-pill-scope text-xs font-semibold px-3 py-1.5 rounded-full inline-flex items-center gap-1.5 mt-3"
                            x-text="previewScopeText">
                            🌐 Áp dụng toàn bộ shop
                        </div>

                        {{-- Ticket cutouts & dashed line --}}
                        <div class="ticket-cutout-left"></div>
                        <div class="ticket-cutout-right"></div>
                        <div class="ticket-divider-line"></div>

                        {{-- Voucher code & usage limit --}}
                        <div class="flex items-end justify-between">
                            <div>
                                <span class="text-[10px] text-[#A8988B] uppercase font-bold tracking-wider block">MÃ ƯU ĐÃI</span>
                                <div class="ticket-code-text font-extrabold text-lg sm:text-xl tracking-wider font-mono uppercase mt-0.5"
                                    x-text="code || 'MÃ_VOUCHER'">
                                    MÃ_VOUCHER
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#A8988B] uppercase font-bold tracking-wider block">SỐ LƯỢNG</span>
                                <div class="text-white font-bold text-sm sm:text-base mt-0.5">
                                    <span x-text="usage_limit">100</span> lượt
                                </div>
                            </div>
                        </div>

                        {{-- Footer expiration date --}}
                        <div class="ticket-footer flex items-center justify-between text-xs mt-3 pt-2 text-[#A8988B]">
                            <span>Hạn sử dụng:</span>
                            <span class="text-[#D1C4B5] font-semibold" x-text="previewEndDate">
                                20/09/2026 - 16:04
                            </span>
                        </div>
                    </div>

                    {{-- Rule Box (Matches screenshot) --}}
                    <div class="bg-[#FFFBF0] border border-[#FDE68A] rounded-2xl p-4 text-xs space-y-1.5 shadow-xs">
                        <div class="font-bold text-[#2C1408] text-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#E08A1E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span>Quy tắc áp dụng:</span>
                        </div>
                        <p class="text-[#786B61] leading-relaxed">
                            Khách hàng được chọn tối đa <strong>1 Mã Giảm Giá Đơn Hàng</strong> và <strong>1 Mã Giảm Phí Vận Chuyển</strong> trong cùng 1 lần thanh toán.
                        </p>
                    </div>

                </div>

            </form>
        </div>
    </div>
</x-app-layout>

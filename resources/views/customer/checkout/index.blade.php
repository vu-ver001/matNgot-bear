<x-app-layout>
    <style>
        /* Exact color palette and typography matching the mockup */
        :root {
            --bg-page: #FAF5ED;
            --bg-card: #FFFDF9;
            --bg-card-header: #FFF6EA;
            --border-card: #F2E4D4;
            --border-input: #EADBCC;
            --color-primary: #D68729;
            --color-primary-dark: #8A4819;
            --color-text-main: #2B1810;
            --color-text-muted: #7D6B5D;
            --bg-icon-inactive: #F4ECE1;
            --color-icon-inactive: #7D6B5D;
            --btn-submit-bg: #EADBCC;
            --btn-submit-text: #7D6B5D;
        }

        .mn-checkout-page {
            background-color: var(--bg-page);
            min-height: calc(100vh - 120px);
            font-family: 'Be Vietnam Pro', system-ui, -apple-system, sans-serif;
            color: var(--color-text-main);
        }

        /* Section Container */
        .mn-section-card {
            background-color: var(--bg-card);
            border: 1.5px solid var(--border-card);
            border-radius: 22px;
            box-shadow: 0 2px 8px rgba(43, 24, 16, 0.03);
            margin-bottom: 12px;
            position: relative;
        }

        /* Top Header Strip inside card */
        .mn-card-header {
            background-color: var(--bg-card-header);
            padding: 14px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1.5px solid var(--border-card);
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .mn-num-badge {
            width: 26px;
            height: 26px;
            background-color: var(--color-primary);
            color: #FFFFFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 12px;
            flex-shrink: 0;
        }

        .mn-header-title {
            font-weight: 800;
            font-size: 14px;
            color: var(--color-text-main);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }

        .mn-card-body {
            padding: 22px;
        }

        /* Input & Select fields */
        .mn-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--color-text-main);
            margin-bottom: 6px;
        }

        .mn-req {
            color: var(--color-primary);
            font-weight: 700;
        }

        .mn-input {
            width: 100%;
            padding: 13px 18px;
            border: 1.5px solid var(--border-input);
            border-radius: 16px;
            font-size: 14px;
            font-weight: 500;
            color: var(--color-text-main);
            background-color: #FFFFFF;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .mn-input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(214, 135, 41, 0.15);
        }

        .mn-input::placeholder {
            color: #A8988A;
            font-weight: 400;
        }

        .mn-subtext {
            color: var(--color-text-muted);
            font-size: 12px;
            margin-top: 6px;
        }

        /* Searchable dropdown panel */
        .searchable-dropdown-panel {
            position: absolute;
            z-index: 999;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #FFFFFF;
            border: 1.5px solid var(--border-input);
            border-radius: 18px;
            box-shadow: 0 12px 32px rgba(43, 24, 16, 0.16);
            padding: 10px;
            max-height: 280px;
            display: flex;
            flex-direction: column;
        }

        .searchable-dropdown-input {
            width: 100%;
            padding: 8px 12px;
            font-size: 12.5px;
            border: 1px solid var(--border-input);
            border-radius: 12px;
            background-color: #FAF5ED;
            color: var(--color-text-main);
            outline: none;
            box-sizing: border-box;
            margin-bottom: 6px;
        }

        .searchable-dropdown-input:focus {
            border-color: var(--color-primary);
            background-color: #FFFFFF;
        }

        .searchable-dropdown-list {
            overflow-y: auto;
            flex: 1;
            max-height: 190px;
        }

        .searchable-dropdown-item {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--color-text-main);
        }

        .searchable-dropdown-item:hover {
            background-color: #FFF6EA;
            color: var(--color-primary);
        }

        .searchable-dropdown-item.active {
            background-color: #FFF6EA;
            color: var(--color-primary);
            font-weight: 700;
        }

        /* Custom scrollbar */
        .searchable-dropdown-list::-webkit-scrollbar {
            width: 5px;
        }
        .searchable-dropdown-list::-webkit-scrollbar-track {
            background: #F8F4EC;
            border-radius: 4px;
        }
        .searchable-dropdown-list::-webkit-scrollbar-thumb {
            background: #D5C7B7;
            border-radius: 4px;
        }
        .searchable-dropdown-list::-webkit-scrollbar-thumb:hover {
            background: #D68729;
        }

        /* Shipping & Payment Options */
        .mn-option-card {
            border: 1.5px solid var(--border-input);
            border-radius: 18px;
            background-color: #FFFFFF;
            padding: 14px 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            user-select: none;
        }

        .mn-option-card:last-child {
            margin-bottom: 0;
        }

        .mn-option-card:hover {
            border-color: rgba(214, 135, 41, 0.6);
        }

        .mn-option-card.selected {
            border: 2px solid var(--color-primary);
            background-color: #FFFDF8;
            box-shadow: 0 2px 10px rgba(214, 135, 41, 0.08);
        }

        /* Radio dot indicator */
        .mn-radio-outer {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #D5C7B7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .mn-option-card.selected .mn-radio-outer {
            border-color: var(--color-primary);
        }

        .mn-radio-inner {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--color-primary);
        }

        /* Icon Container */
        .mn-icon-container {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background-color: var(--bg-icon-inactive);
            color: var(--color-icon-inactive);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .mn-option-card.selected .mn-icon-container {
            background-color: var(--color-primary);
            color: #FFFFFF;
        }

        /* Voucher Button */
        .mn-btn-voucher {
            background: linear-gradient(135deg, #8A4819 0%, #68320B 100%);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 14px;
            padding: 13px 26px;
            border-radius: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(104, 50, 11, 0.2);
            flex-shrink: 0;
        }

        .mn-btn-voucher:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        /* Shopee Voucher Card */
        .shopee-voucher-card {
            display: flex;
            border: 1.5px solid var(--border-input);
            border-radius: 14px;
            background: #FFFFFF;
            overflow: hidden;
            transition: all 0.2s ease;
            position: relative;
            box-shadow: 0 1px 4px rgba(43, 24, 16, 0.03);
        }

        .shopee-voucher-card:hover {
            border-color: rgba(214, 135, 41, 0.6);
            box-shadow: 0 3px 10px rgba(214, 135, 41, 0.08);
        }

        .shopee-voucher-card.selected {
            border: 2px solid var(--color-primary);
            background: #FFFDF8;
            box-shadow: 0 3px 10px rgba(214, 135, 41, 0.1);
        }

        .shopee-voucher-card.disabled {
            opacity: 0.65;
            background: #FAF8F5;
            border-color: #EADBCC;
        }

        .voucher-stub-order {
            width: 96px;
            min-width: 96px;
            background-color: #CE7E26;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            text-align: center;
            position: relative;
            border-right: 1.5px dashed rgba(255, 255, 255, 0.4);
        }

        .voucher-stub-shipping {
            width: 96px;
            min-width: 96px;
            background-color: #0D9488;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            text-align: center;
            position: relative;
            border-right: 1.5px dashed rgba(255, 255, 255, 0.4);
        }

        /* Right Column Sticky Card */
        .mn-summary-card {
            background-color: var(--bg-card);
            border: 1.5px solid var(--border-card);
            border-radius: 22px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(43, 24, 16, 0.03);
            margin-bottom: 24px;
        }

        .mn-summary-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .mn-pill-badge {
            background-color: var(--color-primary);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .mn-product-thumb {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background-color: #FFFFFF;
            border: 1.5px solid var(--border-input);
            position: relative;
            flex-shrink: 0;
            padding: 2px;
        }

        .mn-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 13px;
        }

        .mn-qty-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: var(--color-primary);
            color: #FFFFFF;
            font-weight: 800;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFFFFF;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

        .mn-btn-submit {
            width: 100%;
            background-color: var(--btn-submit-bg);
            color: var(--btn-submit-text);
            font-weight: 800;
            font-size: 15px;
            padding: 16px 20px;
            border-radius: 18px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            margin-top: 18px;
        }

        .mn-btn-submit:hover {
            background-color: var(--color-primary);
            color: #FFFFFF;
            box-shadow: 0 6px 18px rgba(214, 135, 41, 0.3);
            transform: translateY(-1px);
        }

        .mn-btn-submit svg {
            color: var(--btn-submit-text);
            transition: color 0.25s ease;
        }

        .mn-btn-submit:hover svg {
            color: #FFFFFF;
        }
    </style>

    @if(!empty($googleMapsApiKey))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
    @endif

    <script>
        window.__CHECKOUT_CONFIG__ = {
            subtotal: {{ (float) $subtotal }},
            defaultShippingFee: {{ (float) $shippingFee }},
            orderVouchers: @json($orderVouchers),
            shippingVouchers: @json($shippingVouchers),
            allVouchers: @json($allVouchers ?? $orderVouchers),
            usedVoucherCodes: @json($usedVoucherCodes ?? []),
            savedProfile: @json($savedProfile ?? null),
            userAddress: @json($user->address ?? ''),
            initialShipping: @json($initialShipping ?? null),
            googleMapsApiKey: @json($googleMapsApiKey ?? ''),
            calculateShippingUrl: '{{ route('customer.checkout.calculate_shipping') }}'
        };
    </script>

    <div class="mn-checkout-page py-8 sm:py-10 pb-36" 
         x-data="checkoutComponent()">
        
        {{-- Breadcrumb --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <x-breadcrumb :items="[
                ['label' => 'Trang Chủ', 'url' => route('home')],
                ['label' => 'Giỏ Hàng', 'url' => route('customer.cart')],
                ['label' => 'Thanh Toán']
            ]" />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Top Header Title --}}
            <div class="flex items-center gap-4 mb-8">
                <div class="w-13 h-13 rounded-2xl flex items-center justify-center shrink-0 shadow-md transition transform hover:scale-105"
                     style="width: 52px; height: 52px; background: linear-gradient(135deg, #DF8F30 0%, #9E5316 100%); box-shadow: 0 6px 16px rgba(184, 107, 29, 0.28); border: 2px solid #FFF5E6;">
                    {{-- Detailed Modern Golden Payment Card SVG --}}
                    <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="5" width="20" height="14" rx="3" fill="#FFFFFF" fill-opacity="0.18" stroke="#FFFFFF" stroke-width="1.8"/>
                        <path d="M2 9.5H22" stroke="#FFFFFF" stroke-width="1.8"/>
                        <rect x="5" y="13" width="3.5" height="2.5" rx="0.6" fill="#FFFFFF"/>
                        <circle cx="16.5" cy="14.2" r="1.5" fill="#FFFFFF" fill-opacity="0.7"/>
                        <circle cx="18.5" cy="14.2" r="1.5" fill="#FFFFFF" fill-opacity="0.5"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#2B1810] tracking-tight">Thanh Toán Đơn Hàng</h1>
                    <p class="text-xs sm:text-sm font-medium text-[#7D6B5D] mt-0.5">Điền thông tin giao hàng và chọn phương thức thanh toán</p>
                </div>
            </div>

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('customer.checkout.process') }}" method="POST" id="checkout-form" @submit="handleSubmit($event)">
                @csrf

                {{-- Hidden fields for selected items --}}
                @foreach($selectedItemIds as $id)
                    <input type="hidden" name="selected_items[]" value="{{ $id }}">
                @endforeach

                <input type="hidden" name="voucher_id" :value="selectedOrderVoucher ? selectedOrderVoucher.id : ''">
                <input type="hidden" name="shipping_voucher_id" :value="selectedShippingVoucher ? selectedShippingVoucher.id : ''">
                <input type="hidden" name="shipping_fee" :value="shippingFee">
                <input type="hidden" name="shipping_method" :value="shippingMethod">
                <input type="hidden" name="recipient_address" :value="fullAddress">
                <input type="hidden" name="province" :value="selectedProvince">
                <input type="hidden" name="ward" :value="selectedWard">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    {{-- Left Column: 01 to 05 Sections --}}
                    <div class="lg:col-span-7 space-y-3">

                        {{-- 01. THÔNG TIN NGƯỜI NHẬN --}}
                        <div class="mn-section-card">
                            <div class="mn-card-header">
                                <span class="mn-num-badge">01</span>
                                <h2 class="mn-header-title">THÔNG TIN NGƯỜI NHẬN</h2>
                            </div>

                            <div class="mn-card-body space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="mn-label">Họ và tên <span class="mn-req">*</span></label>
                                        <input type="text" name="recipient_name" required
                                               x-model="recipientName"
                                               placeholder="Nguyễn Văn A"
                                               class="mn-input">
                                        @error('recipient_name')
                                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mn-label">Số điện thoại <span class="mn-req">*</span></label>
                                        <input type="tel" name="recipient_phone" required
                                               x-model="recipientPhone"
                                               placeholder="0901 234 567"
                                               class="mn-input">
                                        @error('recipient_phone')
                                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="mn-label">Email</label>
                                    <input type="email" name="recipient_email"
                                           x-model="recipientEmail"
                                           placeholder="example@email.com"
                                           class="mn-input">
                                    <p class="mn-subtext">Nhận email xác nhận đơn hàng (không bắt buộc)</p>
                                </div>
                            </div>
                        </div>

                        {{-- 02. ĐỊA CHỈ GIAO HÀNG --}}
                        <div class="mn-section-card">
                            <div class="mn-card-header">
                                <span class="mn-num-badge">02</span>
                                <h2 class="mn-header-title">ĐỊA CHỈ GIAO HÀNG</h2>
                            </div>

                            <div class="mn-card-body space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    
                                    {{-- Searchable Province / City --}}
                                    <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false">
                                        <label class="mn-label">Tỉnh / Thành phố <span class="mn-req">*</span></label>
                                        <div @click="open = !open; if(open) $nextTick(() => $refs.provInput.focus())"
                                             class="mn-input flex items-center justify-between cursor-pointer">
                                            <span x-text="selectedProvince || 'Chọn tỉnh / thành phố'" 
                                                  :class="!selectedProvince ? 'text-[#A8988A]' : 'text-[#2B1810] font-medium'"
                                                  class="truncate pr-2"></span>
                                            <svg class="w-4 h-4 text-[#7D6B5D] shrink-0 transition-transform duration-200" 
                                                 :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>

                                        {{-- Dropdown Menu with Search --}}
                                        <div x-show="open" x-transition.origin.top.duration.150ms 
                                             class="searchable-dropdown-panel" style="display: none;">
                                            <input type="text" x-ref="provInput" x-model="search" 
                                                   placeholder="🔍 Tìm tỉnh / thành phố..." 
                                                   class="searchable-dropdown-input">
                                            <div class="searchable-dropdown-list">
                                                <template x-for="p in filterList(provinces, search)" :key="p.name">
                                                    <div @click="selectProvinceName(p.name); open = false; search = ''" 
                                                         class="searchable-dropdown-item"
                                                         :class="{ 'active': selectedProvince === p.name }">
                                                        <span x-text="p.name"></span>
                                                        <span x-show="selectedProvince === p.name" class="text-xs font-bold text-[#D68729]">✓</span>
                                                    </div>
                                                </template>
                                                <div x-show="filterList(provinces, search).length === 0" class="p-3 text-center text-xs text-[#7D6B5D]">
                                                    Không tìm thấy kết quả
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Searchable Ward / Commune (Phường / Xã / Thị trấn) --}}
                                    <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false">
                                        <label class="mn-label">Phường / Xã / Thị trấn <span class="mn-req">*</span></label>
                                        <div @click="if(availableWards.length > 0) { open = !open; if(open) $nextTick(() => $refs.wardInput.focus()); }"
                                             class="mn-input flex items-center justify-between cursor-pointer"
                                             :class="{ 'opacity-60 cursor-not-allowed': availableWards.length === 0 }">
                                            <span x-text="selectedWard || 'Chọn phường / xã / thị trấn'" 
                                                  :class="!selectedWard ? 'text-[#A8988A]' : 'text-[#2B1810] font-medium'"
                                                  class="truncate pr-2"></span>
                                            <svg class="w-4 h-4 text-[#7D6B5D] shrink-0 transition-transform duration-200" 
                                                 :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>

                                        {{-- Dropdown Menu with Search & Custom Entry --}}
                                        <div x-show="open" x-transition.origin.top.duration.150ms 
                                             class="searchable-dropdown-panel" style="display: none;">
                                            <input type="text" x-ref="wardInput" x-model="search" 
                                                   @keydown.enter.prevent="if(search.trim()) { selectedWard = search.trim(); open = false; search = ''; onAddressChange(); }"
                                                   placeholder="🔍 Tìm hoặc nhập phường / xã..." 
                                                   class="searchable-dropdown-input">
                                            <div class="searchable-dropdown-list">
                                                <template x-for="w in filterWards(availableWards, search)" :key="w">
                                                    <div @click="selectedWard = w; open = false; search = ''; onAddressChange()" 
                                                         class="searchable-dropdown-item !items-start !flex-col !py-2"
                                                         :class="{ 'active': selectedWard === w }">
                                                        <div class="flex items-center justify-between w-full">
                                                            <span class="font-medium text-[13px]" x-text="w"></span>
                                                            <span x-show="selectedWard === w" class="text-xs font-bold text-[#D68729]">✓</span>
                                                        </div>
                                                        <span x-show="getWardSubtitle(w)" 
                                                              class="text-[10px] text-[#8C7A6B] truncate max-w-full font-normal mt-0.5" 
                                                              x-text="getWardSubtitle(w)"></span>
                                                    </div>
                                                </template>
                                                {{-- Quick Add/Select if typing a custom or new commune name --}}
                                                <div x-show="search.trim() && !availableWards.includes(search.trim())" 
                                                     @click="selectedWard = search.trim(); open = false; search = ''; onAddressChange()"
                                                     class="p-2.5 px-3.5 text-xs text-[#D68729] hover:bg-[#FFF9F2] cursor-pointer flex items-center gap-2 border-t border-[#F2DECA] font-medium transition-colors">
                                                    <span class="text-sm">➕</span>
                                                    <span>Sử dụng: "<strong x-text="search.trim()"></strong>"</span>
                                                </div>
                                                <div x-show="filterWards(availableWards, search).length === 0 && !search.trim()" class="p-3 text-center text-xs text-[#7D6B5D]">
                                                    Không tìm thấy kết quả
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div>
                                    <label class="mn-label">Địa chỉ cụ thể <span class="mn-req">*</span></label>
                                    <input type="text" 
                                           id="street-address-input"
                                           x-model="streetAddress" 
                                           @input.debounce.500ms="onAddressChange()"
                                           required
                                           placeholder="Số nhà, tên đường, khu phố..."
                                           class="mn-input">
                                    @error('recipient_address')
                                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mn-label">Ghi chú đơn hàng</label>
                                    <input type="text" name="note"
                                           value="{{ old('note') }}"
                                           placeholder="VD: Giao giờ hành chính, gọi trước 30 phút..."
                                           class="mn-input">
                                </div>

                                {{-- Dynamic Distance / Location Status Badge (Google Maps & Regional Engine) --}}
                                <div class="p-3 rounded-2xl text-xs flex flex-wrap items-center justify-between gap-2 border transition-all"
                                     :class="distanceInfo.is_hanoi_inner ? 'bg-[#FFF9F2] border-[#F2DECA] text-[#2B1810]' : 'bg-[#F0FDF4] border-[#BBF7D0] text-[#166534]'">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-sm shrink-0"
                                             :class="distanceInfo.is_hanoi_inner ? 'bg-[#FFF0DC] text-[#D68729]' : 'bg-[#DCFCE7] text-[#16A34A]'">
                                            📍
                                        </div>
                                        <div>
                                            <div class="font-bold flex items-center gap-1.5 flex-wrap">
                                                <span x-text="distanceInfo.loading ? 'Đang tính toán khoảng cách...' : ('Khoảng cách ước tính: ~' + distanceInfo.distance_km + ' km')"></span>
                                                <span class="text-[10.5px] text-[#7D6B5D]" x-text="distanceInfo.duration_text ? ('(' + distanceInfo.duration_text + ' vận chuyển)') : ''"></span>
                                            </div>
                                            <div class="text-[10px] text-[#7D6B5D] mt-0.5">
                                                <span x-text="distanceInfo.source === 'GOOGLE_MAPS_API' ? '✨ Tính toán qua Google Maps API' : '📦 Tính theo bản đồ khoảng cách từ kho Mật Ngọt Bear (Số 41A, P.Phú Diễn, Hà Nội)'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <template x-if="distanceInfo.is_hanoi_inner">
                                            <span class="px-2.5 py-0.8 rounded-full text-[10.5px] font-bold bg-[#E6FFFA] text-[#0D9488] border border-[#99F6E4] flex items-center gap-1">
                                                <span>⚡</span> Nội thành HN (Hỗ trợ Hỏa Tốc)
                                            </span>
                                        </template>
                                        <template x-if="!distanceInfo.is_hanoi_inner && selectedProvince">
                                            <span class="px-2.5 py-0.8 rounded-full text-[10.5px] font-bold bg-[#FFF7ED] text-[#EA580C] border border-[#FFEDD5] flex items-center gap-1">
                                                <span>🚚</span> Ngoại thành / Liên tỉnh
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 03. PHƯƠNG THỨC VẬN CHUYỂN --}}
                        <div class="mn-section-card">
                            <div class="mn-card-header">
                                <span class="mn-num-badge">03</span>
                                <h2 class="mn-header-title">PHƯƠNG THỨC VẬN CHUYỂN</h2>
                            </div>

                            <div class="mn-card-body space-y-3">
                                {{-- Standard --}}
                                <div @click="selectShipping('standard', shippingOptions.standard.fee, shippingOptions.standard.time)"
                                     class="mn-option-card cursor-pointer"
                                     :class="{ 'selected': shippingMethod === 'standard' }">
                                    <div class="flex items-center gap-3.5">
                                        <div class="mn-radio-outer">
                                            <div class="mn-radio-inner" x-show="shippingMethod === 'standard'"></div>
                                        </div>
                                        <div class="mn-icon-container">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-[#2B1810]" x-text="shippingOptions.standard.name || 'Giao hàng tiêu chuẩn'">Giao hàng tiêu chuẩn</div>
                                            <div class="text-xs text-[#7D6B5D] mt-0.5" x-text="shippingOptions.standard.desc || 'Giao qua đối tác vận chuyển'">Giao qua đối tác vận chuyển</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-sm sm:text-base font-extrabold text-[#D68729]" x-text="formatVND(shippingOptions.standard.fee)">22.000đ</div>
                                        <div class="text-xs text-[#7D6B5D] mt-0.5" x-text="shippingOptions.standard.time">1 - 2 ngày</div>
                                    </div>
                                </div>

                                {{-- Fast --}}
                                <div @click="selectShipping('fast', shippingOptions.fast.fee, shippingOptions.fast.time)"
                                     class="mn-option-card cursor-pointer"
                                     :class="{ 'selected': shippingMethod === 'fast' }">
                                    <div class="flex items-center gap-3.5">
                                        <div class="mn-radio-outer">
                                            <div class="mn-radio-inner" x-show="shippingMethod === 'fast'"></div>
                                        </div>
                                        <div class="mn-icon-container">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 7h-3V5H1v10h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-3zm-1 2l1.5 2H16V9h2zM6 16.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                                <path d="M1 9h3v2H1zm0 3h2v2H1z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-[#2B1810]" x-text="shippingOptions.fast.name || 'Giao hàng nhanh'">Giao hàng nhanh</div>
                                            <div class="text-xs text-[#7D6B5D] mt-0.5" x-text="shippingOptions.fast.desc || 'Ưu tiên xử lý trong ngày'">Ưu tiên xử lý trong ngày</div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-sm sm:text-base font-extrabold text-[#D68729]" x-text="formatVND(shippingOptions.fast.fee)">32.000đ</div>
                                        <div class="text-xs text-[#7D6B5D] mt-0.5" x-text="shippingOptions.fast.time">Trong 24h</div>
                                    </div>
                                </div>

                                {{-- Express (Hỏa Tốc - ONLY FOR HANOI INNER) --}}
                                <div @click="shippingOptions.express.available ? selectShipping('express', shippingOptions.express.fee, shippingOptions.express.time) : alert('Phương thức Giao hàng hoả tốc (2 - 4 giờ) chỉ áp dụng cho khu vực nội thành Hà Nội.')"
                                     class="mn-option-card transition-all"
                                     :class="{
                                         'selected': shippingMethod === 'express' && shippingOptions.express.available,
                                         'opacity-55 bg-gray-50/90 border-dashed border-gray-300 cursor-not-allowed': !shippingOptions.express.available,
                                         'cursor-pointer': shippingOptions.express.available
                                     }">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="mn-radio-outer" :class="{ 'opacity-40': !shippingOptions.express.available }">
                                            <div class="mn-radio-inner" x-show="shippingMethod === 'express' && shippingOptions.express.available"></div>
                                        </div>
                                        <div class="mn-icon-container" :class="{ 'opacity-50 grayscale': !shippingOptions.express.available }">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M11 21h-1l1-7H7.5c-.58 0-.57-.32-.38-.66.19-.34.05-.08.07-.12C8.48 10.94 10.42 7.54 13 3h1l-1 7h3.5c.49 0 .56.33.47.51l-.07.15C12.9 17.55 11 21 11 21z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-[#2B1810] flex items-center gap-2 flex-wrap">
                                                <span>Giao hàng hoả tốc</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-[#FFF0DC] text-[#D68729]" x-show="shippingOptions.express.available">2 - 4 giờ</span>
                                            </div>
                                            <div class="text-xs text-[#7D6B5D] mt-0.5" x-show="shippingOptions.express.available" x-text="shippingOptions.express.desc || 'Giao trong 2 - 4 giờ tại Hà Nội'"></div>
                                            <div class="text-[11px] font-semibold text-rose-600 mt-0.5 flex items-center gap-1" x-show="!shippingOptions.express.available">
                                                <span>🚫</span>
                                                <span x-text="shippingOptions.express.disabled_reason || 'Chỉ áp dụng nội thành Hà Nội'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <template x-if="shippingOptions.express.available">
                                            <div>
                                                <div class="text-sm sm:text-base font-extrabold text-[#D68729]" x-text="formatVND(shippingOptions.express.fee)">55.000đ</div>
                                                <div class="text-xs text-[#7D6B5D] mt-0.5" x-text="shippingOptions.express.time">2 - 4 giờ</div>
                                            </div>
                                        </template>
                                        <template x-if="!shippingOptions.express.available">
                                            <span class="inline-block px-2 py-1 rounded-md text-[10.5px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                                Không khả dụng
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 04. PHƯƠNG THỨC THANH TOÁN --}}
                        <div class="mn-section-card">
                            <div class="mn-card-header">
                                <span class="mn-num-badge">04</span>
                                <h2 class="mn-header-title">PHƯƠNG THỨC THANH TOÁN</h2>
                            </div>

                            <div class="mn-card-body">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5 items-stretch">
                                    {{-- COD --}}
                                    <div @click="paymentMethod = 'COD'"
                                         class="mn-option-card h-full !items-center !m-0 !py-3.5 !px-4"
                                         :class="{ 'selected': paymentMethod === 'COD' }">
                                        <input type="radio" name="payment_method" value="COD" x-model="paymentMethod" class="sr-only">
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="mn-radio-outer shrink-0">
                                                <div class="mn-radio-inner" x-show="paymentMethod === 'COD'"></div>
                                            </div>
                                            <div class="mn-icon-container shrink-0">
                                                {{-- Cash Bill SVG matching mockup --}}
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13.5px] sm:text-sm font-bold text-[#2B1810] truncate">Thanh toán khi nhận hàng</div>
                                                <div class="text-[11px] sm:text-[11.5px] text-[#7D6B5D] mt-0.5 truncate">Trả tiền mặt khi nhận hàng (COD)</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- VNPay --}}
                                    <div @click="paymentMethod = 'VNPAY'"
                                         class="mn-option-card h-full !items-center !m-0 !py-3.5 !px-4"
                                         :class="{ 'selected': paymentMethod === 'VNPAY' }">
                                        <input type="radio" name="payment_method" value="VNPAY" x-model="paymentMethod" class="sr-only">
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="mn-radio-outer shrink-0">
                                                <div class="mn-radio-inner" x-show="paymentMethod === 'VNPAY'"></div>
                                            </div>
                                            <div class="mn-icon-container shrink-0">
                                                {{-- 4 Squares QR SVG matching mockup --}}
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <rect x="3" y="3" width="7.5" height="7.5" rx="2" />
                                                    <rect x="13.5" y="3" width="7.5" height="7.5" rx="2" />
                                                    <rect x="3" y="13.5" width="7.5" height="7.5" rx="2" />
                                                    <rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13.5px] sm:text-sm font-bold text-[#2B1810] truncate">VNPay QR</div>
                                                <div class="text-[11px] sm:text-[11.5px] text-[#7D6B5D] mt-0.5 truncate">Thanh toán bằng mã QR VNPay</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- MoMo --}}
                                    <div @click="paymentMethod = 'MOMO'"
                                         class="mn-option-card h-full !items-center !m-0 !py-3.5 !px-4"
                                         :class="{ 'selected': paymentMethod === 'MOMO' }">
                                        <input type="radio" name="payment_method" value="MOMO" x-model="paymentMethod" class="sr-only">
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="mn-radio-outer shrink-0">
                                                <div class="mn-radio-inner" x-show="paymentMethod === 'MOMO'"></div>
                                            </div>
                                            <div class="mn-icon-container shrink-0">
                                                {{-- Smiley Face SVG matching mockup --}}
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="9" />
                                                    <circle cx="9" cy="10" r="1.2" fill="currentColor" />
                                                    <circle cx="15" cy="10" r="1.2" fill="currentColor" />
                                                    <path d="M8.5 14c1 1.5 2.5 2 3.5 2s2.5-.5 3.5-2" stroke-linecap="round" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13.5px] sm:text-sm font-bold text-[#2B1810] truncate">Ví MoMo</div>
                                                <div class="text-[11px] sm:text-[11.5px] text-[#7D6B5D] mt-0.5 truncate">Thanh toán qua ví điện tử MoMo</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bank Transfer --}}
                                    <div @click="paymentMethod = 'BANK_TRANSFER'"
                                         class="mn-option-card h-full !items-center !m-0 !py-3.5 !px-4"
                                         :class="{ 'selected': paymentMethod === 'BANK_TRANSFER' }">
                                        <input type="radio" name="payment_method" value="BANK_TRANSFER" x-model="paymentMethod" class="sr-only">
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="mn-radio-outer shrink-0">
                                                <div class="mn-radio-inner" x-show="paymentMethod === 'BANK_TRANSFER'"></div>
                                            </div>
                                            <div class="mn-icon-container shrink-0">
                                                {{-- Bank Card SVG matching mockup --}}
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13.5px] sm:text-sm font-bold text-[#2B1810] truncate">Chuyển khoản ngân hàng</div>
                                                <div class="text-[11px] sm:text-[11.5px] text-[#7D6B5D] mt-0.5 truncate">Quét mã VietQR chuyển khoản nhanh</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bank Transfer Info Box (Matches Screenshot & with VietQR) --}}
                                <div x-show="paymentMethod === 'BANK_TRANSFER'" x-transition.origin.top.duration.250ms
                                     class="mt-4 p-5 sm:p-6 bg-white border-2 border-[#EADBCC] rounded-2xl shadow-xs" style="display: none;">
                                    
                                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                                        {{-- Transfer Details List --}}
                                        <div class="flex-1 w-full">
                                            <h3 class="text-xs sm:text-sm font-black text-[#2B1810] uppercase tracking-wider mb-4 flex items-center justify-between">
                                                <span>THÔNG TIN CHUYỂN KHOẢN</span>
                                                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    Tự động xác nhận
                                                </span>
                                            </h3>

                                            <div class="space-y-3.5 text-xs sm:text-sm">
                                                <div class="flex justify-between items-center py-1.5 border-b border-[#F7EFE6]">
                                                    <span class="text-[#7D6B5D] font-medium">Ngân hàng</span>
                                                    <span class="font-extrabold text-[#2B1810]">MB Bank (Ngân hàng Quân Đội)</span>
                                                </div>

                                                <div class="flex justify-between items-center py-1.5 border-b border-[#F7EFE6]">
                                                    <span class="text-[#7D6B5D] font-medium">Số tài khoản</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-extrabold text-[#2B1810] tracking-wider text-sm sm:text-base">0377466205</span>
                                                        <button type="button" @click="copyText('0377466205', 'Đã sao chép số tài khoản!')" 
                                                                class="text-[#D68729] hover:text-[#8A4819] text-[11px] font-bold px-2 py-0.5 rounded-md bg-[#FFF6EA] hover:bg-[#FFE8CC] transition cursor-pointer border border-[#FAD9B5]">
                                                            Sao chép
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-center py-1.5 border-b border-[#F7EFE6]">
                                                    <span class="text-[#7D6B5D] font-medium">Chủ tài khoản</span>
                                                    <span class="font-extrabold text-[#2B1810]">NGUYỄN NGỌC ANH</span>
                                                </div>

                                                <div class="flex justify-between items-center py-1.5">
                                                    <span class="text-[#7D6B5D] font-medium">Nội dung CK</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-extrabold text-[#D68729] tracking-wider text-sm sm:text-base" x-text="transferCode">MNB-322696</span>
                                                        <button type="button" @click="copyText(transferCode, 'Đã sao chép nội dung chuyển khoản!')" 
                                                                class="text-[#D68729] hover:text-[#8A4819] text-[11px] font-bold px-2 py-0.5 rounded-md bg-[#FFF6EA] hover:bg-[#FFE8CC] transition cursor-pointer border border-[#FAD9B5]">
                                                            Sao chép
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- VietQR Image --}}
                                        <div class="w-full md:w-auto flex flex-col items-center justify-center p-3 bg-[#FAF5ED] border border-[#EADBCC] rounded-2xl shrink-0">
                                            <div class="relative w-36 h-36 bg-white p-1.5 rounded-xl shadow-xs border border-[#EADBCC] flex items-center justify-center">
                                                <img :src="vietQrUrl" alt="VietQR Thanh Toán" class="w-full h-full object-contain"
                                                     onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MNB-TRANSFER'">
                                            </div>
                                            <div class="mt-2 text-center">
                                                <p class="text-[11.5px] font-bold text-[#2B1810]">Quét mã VietQR</p>
                                                <p class="text-[10px] text-[#7D6B5D]">Mở app ngân hàng quét mã</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 05. MÃ GIẢM GIÁ (SHOPEE STYLE - DẠNG ĐÓNG MỞ HÀNG NGANG & TỰ ĐỘNG FREESHIP) --}}
                        <div class="mn-section-card" x-data="{ openShopVouchers: false, openShippingVouchers: false, shopFilter: 'ALL', shipFilter: 'ALL' }">
                            <div class="mn-card-header">
                                <span class="mn-num-badge">05</span>
                                <h2 class="mn-header-title">MÃ GIẢM GIÁ & KHUYẾN MÃI</h2>
                            </div>

                            <div class="mn-card-body space-y-4">
                                {{-- Manual Voucher Code Input --}}
                                <div class="flex items-center gap-2.5">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#7D6B5D]">
                                            {{-- Ticket Tag SVG --}}
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/>
                                            </svg>
                                        </div>
                                        <input type="text" x-model="voucherInput" @keydown.enter.prevent="applyVoucherByCode()"
                                               placeholder="Nhập mã voucher (VD: SALE20, FREESHIP30K...)"
                                               class="mn-input pl-10">
                                    </div>
                                    <button type="button" @click="applyVoucherByCode()" class="mn-btn-voucher">
                                        Áp dụng
                                    </button>
                                </div>

                                {{-- Voucher Status Message --}}
                                <template x-if="voucherMessage">
                                    <div class="p-3 rounded-xl text-xs font-semibold flex items-center justify-between transition-all"
                                         :class="voucherSuccess ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
                                        <div class="flex items-center gap-2">
                                            <span x-text="voucherSuccess ? '✓' : '✕'" class="font-bold"></span>
                                            <span x-text="voucherMessage"></span>
                                        </div>
                                        <button type="button" @click="voucherMessage = ''" class="text-xs underline hover:opacity-80 cursor-pointer">Đóng</button>
                                    </div>
                                </template>

                                {{-- Applied Vouchers Summary Bar --}}
                                <div class="bg-[#FFF8EE] border border-[#F2DECA] rounded-xl p-2.5 flex flex-wrap items-center justify-between gap-2 text-xs"
                                     x-show="selectedOrderVoucher || selectedShippingVoucher">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-[#2B1810]">Đang áp dụng:</span>
                                        <template x-if="selectedOrderVoucher">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#E08A1E] text-white font-bold text-[10.5px]">
                                                <span>🛍️ <span x-text="selectedOrderVoucher.code"></span></span>
                                                <button type="button" @click="selectedOrderVoucher = null" class="hover:text-black font-extrabold cursor-pointer" title="Gỡ mã">×</button>
                                            </span>
                                        </template>
                                        <template x-if="selectedShippingVoucher">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#0D9488] text-white font-bold text-[10.5px]">
                                                <span>🚚 <span x-text="selectedShippingVoucher.code"></span> (Freeship)</span>
                                                <button type="button" @click="selectedShippingVoucher = null" class="hover:text-black font-extrabold cursor-pointer" title="Gỡ mã">×</button>
                                            </span>
                                        </template>
                                    </div>
                                    <span class="font-extrabold text-[#D68729] text-[11.5px]" x-text="'Tiết kiệm: ' + formatVND(orderDiscount + shippingDiscount)"></span>
                                </div>

                                {{-- NESTED COMPACT ACCORDIONS --}}
                                <div class="space-y-2.5 pt-1" x-data="{ activeVoucherTab: 'SHIPPING' }">
                                    
                                    {{-- ITEM 1: VOUCHER VẬN CHUYỂN --}}
                                    <div class="space-y-2">
                                        <button type="button" 
                                                @click="activeVoucherTab = activeVoucherTab === 'SHIPPING' ? null : 'SHIPPING'"
                                                class="w-full py-2.5 px-3.5 rounded-xl border-2 transition-all flex items-center justify-between text-left cursor-pointer bg-white"
                                                :class="activeVoucherTab === 'SHIPPING' ? 'border-[#0D9488] shadow-xs bg-[#F0FDF4]' : 'border-[#EADBCC] hover:border-[#0D9488]'">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm"
                                                     :class="selectedShippingVoucher ? 'bg-[#0D9488] text-white' : 'bg-[#E6FFFA] text-[#0D9488]'">
                                                    🚚
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-black text-[#2B1810] uppercase flex items-center gap-1.5">
                                                        <span>VOUCHER VẬN CHUYỂN</span>
                                                    </div>
                                                    <div class="text-[11px] truncate mt-0.5 font-bold text-[#0D9488]" 
                                                         x-text="selectedShippingVoucher ? '✨ Tự động áp dụng: [' + selectedShippingVoucher.code + ']' : 'Bấm để xem và chọn mã freeship'">
                                                    </div>
                                                </div>
                                            </div>
                                            <svg class="w-4 h-4 text-[#7D6B5D] transition-transform duration-200 shrink-0 ml-2" 
                                                 :class="{ 'rotate-180': activeVoucherTab === 'SHIPPING' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        {{-- LIST OPENS DIRECTLY UNDER SHIPPING BUTTON --}}
                                        <div x-show="activeVoucherTab === 'SHIPPING'" x-transition.origin.top.duration.200ms
                                             class="p-2.5 sm:p-3 bg-white border-2 border-[#EADBCC] rounded-2xl shadow-xs space-y-2" style="display: none;">
                                            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                                <template x-for="v in sortedShippingVouchers" :key="v.id">
                                                    <div class="shopee-voucher-card"
                                                         :class="{
                                                            'selected': selectedShippingVoucher && selectedShippingVoucher.id === v.id,
                                                            'disabled': getVoucherStatus(v).key !== 'ACTIVE' || !isEligible(v)
                                                         }">
                                                        {{-- Left Stub --}}
                                                        <div class="voucher-stub-shipping">
                                                            <svg class="w-3.5 h-3.5 text-white mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                                            </svg>
                                                            <div class="font-extrabold text-[11px] sm:text-xs leading-tight text-white"
                                                                 x-text="v.discount_type === 'PERCENTAGE' ? 'GIẢM ' + parseFloat(v.discount_value) + '%' : 'GIẢM ' + (parseFloat(v.discount_value)/1000) + 'K'">
                                                            </div>
                                                            <div class="text-[7.5px] uppercase font-bold tracking-wider text-white opacity-90 mt-0.5">FREESHIP</div>
                                                        </div>

                                                        {{-- Right Info --}}
                                                        <div class="flex-1 p-2 sm:p-2.5 flex flex-col justify-between min-w-0">
                                                            <div class="flex items-start justify-between gap-1.5">
                                                                <div>
                                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                                        <span class="px-1.5 py-0.2 rounded bg-[#FFF0DC] text-[#D68729] font-extrabold text-[10px] tracking-wide border border-[#FAD9B5]"
                                                                              x-text="v.code"></span>
                                                                        <span class="font-bold text-[11.5px] text-[#2B1810]"
                                                                              x-text="v.discount_type === 'PERCENTAGE' ? 'Giảm ' + parseFloat(v.discount_value) + '% phí ship' : 'Giảm ' + formatVND(v.discount_value)"></span>
                                                                    </div>
                                                                    <div class="text-[10px] text-[#7D6B5D] mt-0.5">
                                                                        Đơn tối thiểu: <span class="font-semibold text-[#2B1810]" x-text="formatVND(v.min_order_value || 0)"></span>
                                                                    </div>
                                                                </div>

                                                                {{-- Action Button --}}
                                                                <div class="shrink-0">
                                                                    <button type="button" @click="toggleShippingVoucher(v)"
                                                                            class="px-2.5 py-1 rounded-lg text-[10.5px] font-bold transition cursor-pointer"
                                                                            :class="selectedShippingVoucher && selectedShippingVoucher.id === v.id ? 
                                                                                'bg-[#0D9488] text-white shadow-xs' : 
                                                                                (getVoucherStatus(v).key === 'ACTIVE' && isEligible(v) ? 'bg-[#FFF0DC] text-[#D68729] hover:bg-[#0D9488] hover:text-white border border-[#FAD9B5]' : 'bg-[#F4F4F4] text-[#A0A0A0] cursor-not-allowed')">
                                                                        <span x-text="selectedShippingVoucher && selectedShippingVoucher.id === v.id ? '✓ Đang dùng' : (getVoucherStatus(v).key === 'ACTIVE' ? (isEligible(v) ? 'Áp dụng' : 'Chưa đủ ĐK') : getVoucherStatus(v).btnText)"></span>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            {{-- Status Badge & Timing --}}
                                                            <div class="flex items-center justify-between text-[10px] mt-1 pt-1 border-t border-[#F7EFE6]">
                                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold border"
                                                                      :class="getVoucherStatus(v).badgeClass"
                                                                      x-text="getVoucherStatus(v).label"></span>
                                                                <span class="text-[#A8988A] text-[9.5px]" x-text="v.end_date ? 'HSD: ' + formatDate(v.end_date) : 'Vô thời hạn'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <div x-show="sortedShippingVouchers.length === 0" class="text-center py-3 text-xs text-[#7D6B5D]">
                                                    Không có voucher vận chuyển nào.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ITEM 2: VOUCHER CỦA SHOP --}}
                                    <div class="space-y-2">
                                        <button type="button" 
                                                @click="activeVoucherTab = activeVoucherTab === 'SHOP' ? null : 'SHOP'"
                                                class="w-full py-2.5 px-3.5 rounded-xl border-2 transition-all flex items-center justify-between text-left cursor-pointer bg-white"
                                                :class="activeVoucherTab === 'SHOP' ? 'border-[#E08A1E] shadow-xs bg-[#FFFDF9]' : 'border-[#EADBCC] hover:border-[#D68729]'">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-sm"
                                                     :class="selectedOrderVoucher ? 'bg-[#E08A1E] text-white' : 'bg-[#FFF0DC] text-[#D68729]'">
                                                    🛍️
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-black text-[#2B1810] uppercase flex items-center gap-1.5">
                                                        <span>VOUCHER CỦA SHOP</span>
                                                    </div>
                                                    <div class="text-[11px] truncate mt-0.5" 
                                                         :class="selectedOrderVoucher ? 'font-bold text-[#E08A1E]' : 'text-[#7D6B5D]'"
                                                         x-text="selectedOrderVoucher ? '✓ Đang dùng: [' + selectedOrderVoucher.code + ']' : 'Bấm để chọn mã giảm giá'">
                                                    </div>
                                                </div>
                                            </div>
                                            <svg class="w-4 h-4 text-[#7D6B5D] transition-transform duration-200 shrink-0 ml-2" 
                                                 :class="{ 'rotate-180': activeVoucherTab === 'SHOP' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        {{-- LIST OPENS DIRECTLY UNDER SHOP BUTTON --}}
                                        <div x-show="activeVoucherTab === 'SHOP'" x-transition.origin.top.duration.200ms
                                             class="p-2.5 sm:p-3 bg-white border-2 border-[#EADBCC] rounded-2xl shadow-xs space-y-2" style="display: none;">
                                            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                                <template x-for="v in sortedOrderVouchers" :key="v.id">
                                                    <div class="shopee-voucher-card"
                                                         :class="{
                                                            'selected': selectedOrderVoucher && selectedOrderVoucher.id === v.id,
                                                            'disabled': getVoucherStatus(v).key !== 'ACTIVE' || !isEligible(v)
                                                         }">
                                                        {{-- Left Stub --}}
                                                        <div class="voucher-stub-order">
                                                            <svg class="w-3.5 h-3.5 text-white mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                            </svg>
                                                            <div class="font-extrabold text-[11px] sm:text-xs leading-tight text-white"
                                                                 x-text="v.discount_type === 'PERCENTAGE' ? 'GIẢM ' + parseFloat(v.discount_value) + '%' : 'GIẢM ' + (parseFloat(v.discount_value)/1000) + 'K'">
                                                            </div>
                                                            <div class="text-[7.5px] uppercase font-bold tracking-wider text-white opacity-90 mt-0.5">SHOP</div>
                                                        </div>

                                                        {{-- Right Info --}}
                                                        <div class="flex-1 p-2 sm:p-2.5 flex flex-col justify-between min-w-0">
                                                            <div class="flex items-start justify-between gap-1.5">
                                                                <div>
                                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                                        <span class="px-1.5 py-0.2 rounded bg-[#FFF0DC] text-[#D68729] font-extrabold text-[10px] tracking-wide border border-[#FAD9B5]"
                                                                              x-text="v.code"></span>
                                                                        <span class="font-bold text-[11.5px] text-[#2B1810]"
                                                                              x-text="v.discount_type === 'PERCENTAGE' ? 'Giảm ' + parseFloat(v.discount_value) + '%' : 'Giảm ' + formatVND(v.discount_value)"></span>
                                                                    </div>
                                                                    <div class="text-[10px] text-[#7D6B5D] mt-0.5">
                                                                        Đơn tối thiểu: <span class="font-semibold text-[#2B1810]" x-text="formatVND(v.min_order_value || 0)"></span>
                                                                    </div>
                                                                </div>

                                                                {{-- Action Button --}}
                                                                <div class="shrink-0">
                                                                    <button type="button" @click="toggleOrderVoucher(v)"
                                                                            class="px-2.5 py-1 rounded-lg text-[10.5px] font-bold transition cursor-pointer"
                                                                            :class="selectedOrderVoucher && selectedOrderVoucher.id === v.id ? 
                                                                                'bg-[#D68729] text-white shadow-xs' : 
                                                                                (getVoucherStatus(v).key === 'ACTIVE' && isEligible(v) ? 'bg-[#FFF0DC] text-[#D68729] hover:bg-[#D68729] hover:text-white border border-[#FAD9B5]' : 'bg-[#F4F4F4] text-[#A0A0A0] cursor-not-allowed')">
                                                                        <span x-text="selectedOrderVoucher && selectedOrderVoucher.id === v.id ? '✓ Đang dùng' : (getVoucherStatus(v).key === 'ACTIVE' ? (isEligible(v) ? 'Áp dụng' : 'Chưa đủ ĐK') : getVoucherStatus(v).btnText)"></span>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            {{-- Status Badge & Timing --}}
                                                            <div class="flex items-center justify-between text-[10px] mt-1 pt-1 border-t border-[#F7EFE6]">
                                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold border"
                                                                      :class="getVoucherStatus(v).badgeClass"
                                                                      x-text="getVoucherStatus(v).label"></span>
                                                                <span class="text-[#A8988A] text-[9.5px]" x-text="v.end_date ? 'HSD: ' + formatDate(v.end_date) : 'Vô thời hạn'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <div x-show="sortedOrderVouchers.length === 0" class="text-center py-3 text-xs text-[#7D6B5D]">
                                                    Không có voucher đơn hàng nào từ shop.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Right Column: Sticky Summary --}}
                    <div class="lg:col-span-5 space-y-3 lg:sticky lg:top-6">

                        {{-- Card: ĐƠN HÀNG CỦA BẠN --}}
                        <div class="mn-summary-card">
                            <div class="mn-summary-header">
                                <h3 class="mn-header-title">ĐƠN HÀNG CỦA BẠN</h3>
                                <span class="mn-pill-badge">
                                    {{ $cartItems->sum('quantity') }} sản phẩm
                                </span>
                            </div>

                            <div class="space-y-4 divide-y divide-[#F4E8D8]">
                                @foreach($cartItems as $item)
                                    @php
                                        $unitPrice = $item->product->sale_price ?? $item->product->price;
                                        $lineTotal = $unitPrice * $item->quantity;
                                        $primaryImage = $item->product->images->firstWhere('is_primary', true) ?? $item->product->images->first();
                                        $imageUrl = $primaryImage ? asset($primaryImage->image_path) : 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80';
                                        
                                        $specs = [];
                                        if (!empty($item->product->size)) { $specs[] = $item->product->size; }
                                        if (!empty($item->product->color)) { $specs[] = $item->product->color; }
                                        $specsText = !empty($specs) ? implode(' · ', $specs) : ($item->product->category->name ?? 'Gấu bông');
                                    @endphp
                                    <div class="flex items-center gap-3.5 pt-4 first:pt-0">
                                        {{-- Image with orange quantity badge --}}
                                        <div class="mn-product-thumb">
                                            <img src="{{ $imageUrl }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 onerror="this.src='https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=300&auto=format&fit=crop&q=80'">
                                            <span class="mn-qty-badge">
                                                {{ $item->quantity }}
                                            </span>
                                        </div>

                                        {{-- Details --}}
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-xs sm:text-sm text-[#2B1810] line-clamp-2 leading-snug">{{ $item->product->name }}</h4>
                                            <p class="text-xs text-[#7D6B5D] mt-1">{{ $specsText }}</p>
                                        </div>

                                        {{-- Price --}}
                                        <div class="text-right shrink-0">
                                            <span class="text-sm sm:text-base font-extrabold text-[#D68729]">
                                                {{ number_format($lineTotal, 0, ',', '.') }}đ
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Card: CHI TIẾT THANH TOÁN --}}
                        <div class="mn-summary-card">
                            <h3 class="mn-header-title pb-3 border-b border-[#F4E8D8] mb-4">
                                CHI TIẾT THANH TOÁN
                            </h3>

                            <div class="space-y-3 text-xs sm:text-sm mt-3">
                                <div class="flex justify-between items-center text-[#7D6B5D]">
                                    <span>Tạm tính ({{ $cartItems->sum('quantity') }} sản phẩm)</span>
                                    <span class="font-extrabold text-[#2B1810]" x-text="formatVND(subtotal)">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                                </div>

                                <div class="flex justify-between items-start text-[#7D6B5D]">
                                    <div>
                                        <span>Phí vận chuyển</span>
                                        <span class="block text-[11px] text-[#7D6B5D] mt-0.5" x-text="selectedShippingTime">1 - 2 ngày</span>
                                    </div>
                                    <div class="text-right">
                                        <template x-if="shippingDiscount > 0">
                                            <div>
                                                <span class="line-through text-gray-400 font-medium text-xs mr-1" x-text="formatVND(shippingFee)"></span>
                                                <span class="font-extrabold text-teal-600" x-text="formatVND(Math.max(0, shippingFee - shippingDiscount))">0đ</span>
                                            </div>
                                        </template>
                                        <template x-if="!shippingDiscount || shippingDiscount <= 0">
                                            <span class="font-extrabold text-[#2B1810]" x-text="formatVND(shippingFee)">{{ number_format($shippingFee, 0, ',', '.') }}đ</span>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="orderDiscount > 0">
                                    <div class="flex justify-between items-center text-emerald-600 font-bold">
                                        <span class="flex items-center gap-1">
                                            <span>🛍️ Giảm giá voucher</span>
                                            <span class="text-[11px] font-normal opacity-85" x-text="'[' + (selectedOrderVoucher ? selectedOrderVoucher.code : '') + ']'"></span>
                                        </span>
                                        <span x-text="'-' + formatVND(orderDiscount)"></span>
                                    </div>
                                </template>

                                <template x-if="selectedShippingVoucher">
                                    <div class="flex justify-between items-center text-teal-600 font-bold">
                                        <span class="flex items-center gap-1">
                                            <span>🚚 Giảm phí vận chuyển</span>
                                            <span class="text-[11px] font-normal opacity-85" x-text="'[' + selectedShippingVoucher.code + ']'"></span>
                                        </span>
                                        <span x-text="shippingDiscount > 0 ? ('-' + formatVND(shippingDiscount)) : 'Miễn phí'"></span>
                                    </div>
                                </template>

                                <div class="pt-4 border-t border-[#F4E8D8] flex justify-between items-baseline">
                                    <div>
                                        <span class="text-xs sm:text-sm font-extrabold text-[#2B1810] block">Tổng thanh toán</span>
                                        <span class="text-[11px] text-[#7D6B5D] font-normal block mt-0.5">Đã bao gồm VAT (nếu có)</span>
                                    </div>
                                    <span class="text-2xl sm:text-3xl font-extrabold text-[#D68729] tracking-tight" x-text="formatVND(finalTotal)">{{ number_format($subtotal + $shippingFee, 0, ',', '.') }}đ</span>
                                </div>
                            </div>

                            {{-- Submit Button with Reload/Checkmark Icon --}}
                            <button type="submit" class="mn-btn-submit">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span>Đặt Hàng Ngay</span>
                            </button>

                            {{-- Trust Badges --}}
                            <div class="pt-4 flex items-center justify-between text-[11px] sm:text-xs text-[#7D6B5D] px-1 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">🔒</span>
                                    <span>Bảo mật</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">🐻</span>
                                    <span>Hàng chính hãng</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">🔄</span>
                                    <span>Đổi trả 7 ngày</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        window.__CHECKOUT_CONFIG__ = {
            subtotal: {{ (float) $subtotal }},
            defaultShippingFee: {{ (float) $shippingFee }},
            orderVouchers: @json($orderVouchers),
            shippingVouchers: @json($shippingVouchers),
            allVouchers: @json($allVouchers ?? $orderVouchers),
            userAddress: @json($user->address ?? ''),
            initialShipping: @json($initialShipping ?? null),
            googleMapsApiKey: @json($googleMapsApiKey ?? ''),
            calculateShippingUrl: '{{ route('customer.checkout.calculate_shipping') }}'
        };

        function checkoutComponent(customConfig) {
            const config = customConfig || window.__CHECKOUT_CONFIG__ || {};
            const initialShip = config.initialShipping || null;

            return {
                subtotal: config.subtotal || 0,
                shippingFee: config.defaultShippingFee !== undefined ? config.defaultShippingFee : 22000,
                shippingMethod: 'standard',
                selectedShippingTime: '1 - 2 ngày',
                orderVouchers: config.orderVouchers || [],
                shippingVouchers: config.shippingVouchers || [],
                allVouchers: config.allVouchers || [],
                usedVoucherCodes: config.usedVoucherCodes || [],
                selectedOrderVoucher: null,
                selectedShippingVoucher: null,
                paymentMethod: 'COD',
                voucherInput: '',
                voucherMessage: '',
                voucherSuccess: false,
                suggestedChips: (config.allVouchers || []).map(v => v.code).slice(0, 4),

                // Shipping options & distance info
                shippingOptions: initialShip && initialShip.options ? initialShip.options : {
                    standard: { id: 'standard', name: 'Giao hàng tiêu chuẩn', desc: 'Giao qua đối tác vận chuyển', fee: 22000, time: '1 - 2 ngày', available: true },
                    fast: { id: 'fast', name: 'Giao hàng nhanh', desc: 'Ưu tiên xử lý trong ngày', fee: 32000, time: 'Trong 24h', available: true },
                    express: { id: 'express', name: 'Giao hàng hoả tốc', desc: 'Giao trong 2 - 4 giờ tại Hà Nội', fee: 55000, time: '2 - 4 giờ', available: true, disabled_reason: '' }
                },
                distanceInfo: {
                    distance_km: initialShip?.distance_km || 6.5,
                    duration_text: initialShip?.duration_text || '16 phút',
                    is_hanoi_inner: initialShip?.is_hanoi_inner !== undefined ? initialShip.is_hanoi_inner : true,
                    source: initialShip?.source || 'REGIONAL_MATRIX',
                    loading: false
                },

                // Form fields pre-filled from remembered profile / latest order (fully editable)
                recipientName: config.savedProfile?.recipient_name || '{{ addslashes($user->full_name ?? '') }}',
                recipientPhone: config.savedProfile?.recipient_phone || '{{ addslashes($user->phone ?? '') }}',
                recipientEmail: config.savedProfile?.recipient_email || '{{ addslashes($user->email ?? '') }}',
                selectedProvince: config.savedProfile?.province || 'Hà Nội',
                selectedWard: config.savedProfile?.ward || '',
                streetAddress: config.savedProfile?.street || '',

                // 63 Provinces / Cities in Vietnam with post-merger administrative wards & communes
                provinces: [
                    {
                        name: 'Hà Nội',
                        wards: [
                            'Phường Phú Diễn', 'Phường Phúc Diễn', 'Phường Cầu Giấy', 'Phường Dịch Vọng', 'Phường Dịch Vọng Hậu',
                            'Phường Nghĩa Đô', 'Phường Nghĩa Tân', 'Phường Yên Hòa', 'Phường Trung Hòa', 'Phường Cống Vị',
                            'Phường Điện Biên', 'Phường Đội Cấn', 'Phường Kim Mã', 'Phường Liễu Giai', 'Phường Ngọc Hà',
                            'Phường Thành Công', 'Phường Giảng Võ', 'Phường Quán Thánh', 'Phường Trúc Bạch', 'Phường Hàng Bạc',
                            'Phường Hàng Gai', 'Phường Tràng Tiền', 'Phường Cửa Đông', 'Phường Cửa Nam', 'Phường Hàng Bông',
                            'Phường Hàng Đào', 'Phường Bách Khoa', 'Phường Minh Khai', 'Phường Trương Định', 'Phường Đồng Tâm',
                            'Phường Vĩnh Tuy', 'Phường Bạch Mai', 'Phường Cát Linh', 'Phường Hàng Bột', 'Phường Láng Hạ',
                            'Phường Láng Thượng', 'Phường Ô Chợ Dừa', 'Phường Kim Liên', 'Phường Khâm Thiên', 'Phường Hạ Đình',
                            'Phường Khương Mai', 'Phường Khương Đình', 'Phường Thanh Xuân Bắc', 'Phường Thanh Xuân Trung',
                            'Phường Thanh Xuân Nam', 'Phường Mỹ Đình 1', 'Phường Mỹ Đình 2', 'Phường Mễ Trì', 'Phường Phú Đô',
                            'Phường Trung Văn', 'Phường Tây Mỗ', 'Phường Đại Mỗ', 'Phường Cổ Nhuế 1', 'Phường Cổ Nhuế 2',
                            'Phường Xuân Đỉnh', 'Phường Đông Ngạc', 'Phường Thụy Phương', 'Phường Mộ Lao', 'Phường Văn Quán',
                            'Phường Hà Cầu', 'Phường La Khê', 'Phường Quang Trung', 'Phường Vạn Phúc', 'Phường Kiến Hưng',
                            'Phường Đại Kim', 'Phường Định Công', 'Phường Giáp Bát', 'Phường Hoàng Liệt', 'Phường Tân Mai',
                            'Phường Thịnh Liệt', 'Phường Yên Sở', 'Phường Bồ Đề', 'Phường Gia Thụy', 'Phường Ngọc Lâm',
                            'Phường Sài Đồng', 'Phường Thạch Bàn', 'Phường Long Biên', 'Phường Bưởi', 'Phường Nhật Tân',
                            'Phường Quảng An', 'Phường Thụy Khuê', 'Phường Xuân La', 'Phường Hoàn Kiếm',
                            'Xã Thiên Lộc', 'Xã Tiên Dương', 'Xã Kim Chung', 'Xã Hải Bối', 'Xã Vĩnh Ngọc',
                            'Xã Cổ Loa', 'Xã Nam Hồng', 'Xã Tiền Phong', 'Xã Mê Linh', 'Xã Đan Phượng',
                            'Xã Hoài Đức', 'Xã An Khánh', 'Xã Tân Triều', 'Xã Cổ Bi', 'Xã Phù Đổng',
                            'Xã Thanh Trì', 'Xã Sóc Sơn', 'Xã Ba Vì', 'Thị xã Sơn Tây'
                        ]
                    },
                    {
                        name: 'TP. Hồ Chí Minh',
                        wards: [
                            'Phường Bến Nghé', 'Phường Bến Thành', 'Phường Cầu Kho', 'Phường Cầu Ông Lãnh', 'Phường Đa Kao',
                            'Phường Tân Định', 'Phường Nguyễn Thái Bình', 'Phường Phạm Ngũ Lão', 'Phường Võ Thị Sáu',
                            'Phường 1', 'Phường 2', 'Phường 3', 'Phường 4', 'Phường 5', 'Phường 7', 'Phường 9',
                            'Phường 10', 'Phường 12', 'Phường 14', 'Phường Tân Phong', 'Phường Tân Phú', 'Phường Tân Quy',
                            'Phường Phú Mỹ', 'Phường Tân Hưng', 'Phường Thảo Điền', 'Phường An Phú', 'Phường Hiệp Phú',
                            'Phường Linh Chiểu', 'Phường Linh Trung', 'Phường Linh Tây', 'Phường Tăng Nhơn Phú A',
                            'Phường Bình Thạnh', 'Phường Gò Vấp', 'Phường Phú Nhuận', 'Phường Tân Bình', 'Phường Tân Phú',
                            'Phường Bình Tân', 'Xã Hóc Môn', 'Xã Củ Chi', 'Xã Bình Chánh', 'Xã Nhà Bè', 'Xã Cần Giờ'
                        ]
                    },
                    {
                        name: 'Đà Nẵng',
                        wards: [
                            'Phường Hải Châu 1', 'Phường Hải Châu 2', 'Phường Thạch Thang', 'Phường Thuận Phước',
                            'Phường Hòa Cường Bắc', 'Phường Hòa Cường Nam', 'Phường Vĩnh Trung', 'Phường Tân Chính',
                            'Phường Tam Thuận', 'Phường Chính Gián', 'Phường An Khê', 'Phường Thanh Khê Đông',
                            'Phường An Hải Bắc', 'Phường An Hải Tây', 'Phường Phước Mỹ', 'Phường Nại Hiên Đông',
                            'Phường Thọ Quang', 'Phường Mỹ An', 'Phường Khuê Mỹ', 'Phường Hòa Quý', 'Phường Hòa Hải',
                            'Phường Khuê Trung', 'Phường Hòa Thọ Đông', 'Phường Hòa Phát', 'Phường Hòa Xuân',
                            'Phường Hòa Khánh Bắc', 'Phường Hòa Khánh Nam', 'Phường Hòa Minh', 'Xã Hòa Vang'
                        ]
                    },
                    {
                        name: 'Hải Phòng',
                        wards: [
                            'Phường Hoàng Văn Thụ', 'Phường Minh Khai', 'Phường Phan Bội Châu', 'Phường Thượng Lý',
                            'Phường Cầu Đất', 'Phường Lạc Viên', 'Phường Lương Khánh Thiện', 'Phường Lê Lợi',
                            'Phường An Biên', 'Phường An Dương', 'Phường Cát Dài', 'Phường Đông Hải', 'Phường Quán Toan',
                            'Phường Vĩnh Niệm', 'Phường Dư Hàng Kênh', 'Phường Đằng Giang', 'Phường Đằng Hải',
                            'Xã Thủy Nguyên', 'Xã An Lão', 'Xã Tiên Lãng', 'Xã Vĩnh Bảo', 'Xã Kiến Thụy', 'Huyện Cát Hải'
                        ]
                    },
                    {
                        name: 'Cần Thơ',
                        wards: [
                            'Phường An Cư', 'Phường An Hòa', 'Phường An Khánh', 'Phường Tân An', 'Phường Xuân Khánh',
                            'Phường Bình Thủy', 'Phường Trà An', 'Phường Trà Nóc', 'Phường Long Hòa', 'Phường Hưng Phú',
                            'Phường Lê Bình', 'Phường Ba Láng', 'Phường Thốt Nốt', 'Phường Thuận An', 'Xã Phong Điền'
                        ]
                    },
                    {
                        name: 'Bình Dương',
                        wards: [
                            'Phường Phú Cường', 'Phường Hiệp Thành', 'Phường Chánh Nghĩa', 'Phường Phú Lợi', 'Phường Định Hòa',
                            'Phường Lái Thiêu', 'Phường An Phú', 'Phường Bình Chuẩn', 'Phường Thuận Giao',
                            'Phường Dĩ An', 'Phường Tân Đông Hiệp', 'Phường An Bình', 'Phường Đông Hòa', 'Phường Bến Cát', 'Phường Tân Uyên'
                        ]
                    },
                    {
                        name: 'Đồng Nai',
                        wards: [
                            'Phường Trung Dũng', 'Phường Quyết Thắng', 'Phường Thống Nhất', 'Phường Tân Phong', 'Phường Trảng Dài',
                            'Phường Long Bình', 'Phường Tam Hiệp', 'Phường Xuân An', 'Phường Xuân Bình', 'Xã Long Thành', 'Xã Nhơn Trạch', 'Xã Trảng Bom'
                        ]
                    },
                    {
                        name: 'Quảng Ninh',
                        wards: [
                            'Phường Bãi Cháy', 'Phường Hồng Gai', 'Phường Cao Thắng', 'Phường Hồng Hà', 'Phường Hà Khẩu',
                            'Phường Cẩm Trung', 'Phường Cẩm Thành', 'Phường Uông Bí', 'Phường Móng Cái', 'Phường Quảng Yên'
                        ]
                    },
                    {
                        name: 'Bắc Ninh',
                        wards: ['Phường Suối Hoa', 'Phường Tiền An', 'Phường Đại Phúc', 'Phường Kinh Bắc', 'Phường Võ Cường', 'Phường Từ Sơn', 'Xã Thuận Thành', 'Xã Yên Phong', 'Xã Quế Võ']
                    },
                    {
                        name: 'Hải Dương',
                        wards: ['Phường Trần Phú', 'Phường Lê Thanh Nghị', 'Phường Hải Tân', 'Phường Cẩm Thượng', 'Phường Chí Linh', 'Xã Kinh Môn', 'Xã Nam Sách', 'Xã Bình Giang']
                    },
                    {
                        name: 'Hưng Yên',
                        wards: ['Phường Hiến Nam', 'Phường Lê Lợi', 'Phường An Tảo', 'Phường Lam Sơn', 'Phường Mỹ Hào', 'Xã Văn Giang', 'Xã Yên Mỹ', 'Xã Khoái Châu']
                    },
                    {
                        name: 'Vĩnh Phúc',
                        wards: ['Phường Ngô Quyền', 'Phường Liên Bảo', 'Phường Tích Sơn', 'Phường Khai Quang', 'Phường Trưng Trắc', 'Phường Hùng Vương', 'Xã Bình Xuyên', 'Xã Vĩnh Tường']
                    },
                    {
                        name: 'Thái Nguyên',
                        wards: ['Phường Phan Đình Phùng', 'Phường Hoàng Văn Thụ', 'Phường Trưng Vương', 'Phường Quang Trung', 'Phường Vạn Xuân', 'Phường Sông Công', 'Phường Phổ Yên']
                    },
                    {
                        name: 'Bắc Giang',
                        wards: ['Phường Ngô Quyền', 'Phường Lê Lợi', 'Phường Trần Phú', 'Phường Hoàng Văn Thụ', 'Phường Việt Yên', 'Xã Hiệp Hòa', 'Xã Lạng Giang']
                    },
                    {
                        name: 'Phú Thọ',
                        wards: ['Phường Gia Cẩm', 'Phường Tiên Cát', 'Phường Nông Trang', 'Phường Bến Gót', 'Thị xã Phú Thọ', 'Xã Lâm Thao', 'Xã Phù Ninh']
                    },
                    {
                        name: 'Nam Định',
                        wards: ['Phường Vị Hoàng', 'Phường Trần Hưng Đạo', 'Phường Cửa Bắc', 'Phường Lộc Vượng', 'Xã Mỹ Lộc', 'Xã Ý Yên', 'Xã Giao Thủy']
                    },
                    {
                        name: 'Thái Bình',
                        wards: ['Phường Lê Hồng Phong', 'Phường Bồ Xuyên', 'Phường Kỳ Bá', 'Phường Trần Hưng Đạo', 'Xã Vũ Thư', 'Xã Kiến Xương', 'Xã Đông Hưng']
                    },
                    {
                        name: 'Ninh Bình',
                        wards: ['Phường Vân Giang', 'Phường Nam Thành', 'Phường Tân Thành', 'Phường Bích Đào', 'Phường Tam Điệp', 'Phường Hoa Lư', 'Xã Gia Viễn']
                    },
                    {
                        name: 'Hà Nam',
                        wards: ['Phường Minh Khai', 'Phường Lương Khánh Thiện', 'Phường Quang Trung', 'Phường Hai Bà Trưng', 'Phường Duy Tiên', 'Xã Kim Bảng', 'Xã Thanh Liêm']
                    },
                    {
                        name: 'Thanh Hóa',
                        wards: ['Phường Ba Đình', 'Phường Lam Sơn', 'Phường Ngọc Trạo', 'Phường Đông Thọ', 'Phường Đông Sơn', 'Phường Sầm Sơn', 'Phường Bỉm Sơn', 'Phường Nghi Sơn']
                    },
                    {
                        name: 'Nghệ An',
                        wards: ['Phường Trường Thi', 'Phường Lê Lợi', 'Phường Hưng Dũng', 'Phường Quang Trung', 'Phường Cửa Lò', 'Phường Thái Hòa', 'Xã Diễn Châu', 'Xã Nghi Lộc']
                    },
                    {
                        name: 'Hà Tĩnh',
                        wards: ['Phường Bắc Hà', 'Phường Nam Hà', 'Phường Trần Phú', 'Phường Hà Huy Tập', 'Phường Hồng Lĩnh', 'Phường Kỳ Anh', 'Xã Thiên Lộc', 'Xã Cẩm Xuyên', 'Xã Can Lộc']
                    },
                    {
                        name: 'Quảng Bình',
                        wards: ['Phường Đồng Mỹ', 'Phường Hải Đình', 'Phường Nam Lý', 'Phường Bắc Lý', 'Phường Ba Đồn', 'Xã Bố Trạch', 'Xã Lệ Thủy']
                    },
                    {
                        name: 'Quảng Trị',
                        wards: ['Phường 1', 'Phường 2', 'Phường 5', 'Phường Đông Lương', 'Thị xã Quảng Trị', 'Xã Gio Linh', 'Xã Vĩnh Linh']
                    },
                    {
                        name: 'Thừa Thiên Huế',
                        wards: ['Phường Vĩnh Ninh', 'Phường Phú Nhuận', 'Phường Thuận Thành', 'Phường Phú Hội', 'Phường Xuân Phú', 'Phường Hương Thủy', 'Phường Hương Trà']
                    },
                    {
                        name: 'Quảng Nam',
                        wards: ['Phường An Mỹ', 'Phường Tân Thạnh', 'Phường Phước Hòa', 'Phường Minh An (Hội An)', 'Phường Cẩm Phô (Hội An)', 'Phường Điện Bàn', 'Xã Núi Thành']
                    },
                    {
                        name: 'Quảng Ngãi',
                        wards: ['Phường Trần Phú', 'Phường Lê Hồng Phong', 'Phường Nghĩa Lộ', 'Phường Chánh Lộ', 'Phường Đức Phổ', 'Xã Bình Sơn', 'Xã Tư Nghĩa']
                    },
                    {
                        name: 'Bình Định',
                        wards: ['Phường Lê Lợi', 'Phường Trần Hưng Đạo', 'Phường Ngô Mây', 'Phường Ghềnh Ráng', 'Phường An Nhơn', 'Phường Hoài Nhơn', 'Xã Tuy Phước']
                    },
                    {
                        name: 'Phú Yên',
                        wards: ['Phường 1', 'Phường 4', 'Phường 7', 'Phường 9', 'Phường Phú Lâm', 'Phường Sông Cầu', 'Phường Đông Hòa', 'Xã Tuy An']
                    },
                    {
                        name: 'Khánh Hòa',
                        wards: ['Phường Lộc Thọ', 'Phường Tân Lập', 'Phường Phước Tiến', 'Phường Vĩnh Hải', 'Phường Cam Ranh', 'Phường Ninh Hòa', 'Huyện Vạn Ninh']
                    },
                    {
                        name: 'Ninh Thuận',
                        wards: ['Phường Kinh Dinh', 'Phường Thanh Sơn', 'Phường Mỹ Hương', 'Phường Đô Vinh', 'Xã Ninh Hải', 'Xã Ninh Phước']
                    },
                    {
                        name: 'Bình Thuận',
                        wards: ['Phường Đức Nghĩa', 'Phường Phú Thủy', 'Phường Mũi Né', 'Phường Hàm Tiến', 'Phường La Gi', 'Xã Phan Thiết', 'Xã Bắc Bình']
                    },
                    {
                        name: 'Bà Rịa - Vũng Tàu',
                        wards: ['Phường 1', 'Phường 2', 'Phường 7', 'Phường Thắng Tam', 'Phường Nguyễn An Ninh', 'Phường Bà Rịa', 'Phường Phú Mỹ', 'Xã Long Điền']
                    },
                    {
                        name: 'Lâm Đồng',
                        wards: ['Phường 1 (Đà Lạt)', 'Phường 2 (Đà Lạt)', 'Phường 10 (Đà Lạt)', 'Phường Lộc Sơn (Bảo Lộc)', 'Phường B’Lao', 'Xã Đức Trọng', 'Xã Đơn Dương']
                    },
                    {
                        name: 'Đắk Lắk',
                        wards: ['Phường Thắng Lợi', 'Phường Tân Lợi', 'Phường Tự An', 'Phường Tân An', 'Phường Buôn Hồ', 'Xã Krông Pắc', 'Xã Cư M’gar']
                    },
                    {
                        name: 'Đắk Nông',
                        wards: ['Phường Nghĩa Thành', 'Phường Nghĩa Phú', 'Phường Nghĩa Đức', 'Phường Gia Nghĩa', 'Xã Đắk R’lấp', 'Xã Cư Jút']
                    },
                    {
                        name: 'Gia Lai',
                        wards: ['Phường Hoa Lư', 'Phường Tây Sơn', 'Phường Diên Hồng', 'Phường Hội Thương', 'Phường An Khê', 'Phường Ayun Pa', 'Xã Chư Sê']
                    },
                    {
                        name: 'Kon Tum',
                        wards: ['Phường Quyết Thắng', 'Phường Thống Nhất', 'Phường Quang Trung', 'Phường Duy Tân', 'Xã Đắk Hà', 'Xã Ngọc Hồi']
                    },
                    {
                        name: 'Tây Ninh',
                        wards: ['Phường 1', 'Phường 2', 'Phường 3', 'Phường Hiệp Ninh', 'Phường Trảng Bàng', 'Phường Hòa Thành', 'Xã Gò Dầu']
                    },
                    {
                        name: 'Bình Phước',
                        wards: ['Phường Tân Phú', 'Phường Tân Bình', 'Phường Tân Xuân', 'Phường Đồng Xoài', 'Phường Bình Long', 'Phường Phước Long', 'Xã Chơn Thành']
                    },
                    {
                        name: 'Long An',
                        wards: ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 4 (Tân An)', 'Phường Kiến Tường', 'Xã Bến Lức', 'Xã Cần Giuộc', 'Xã Đức Hòa']
                    },
                    {
                        name: 'Tiền Giang',
                        wards: ['Phường 1', 'Phường 4', 'Phường 5', 'Phường 7 (Mỹ Tho)', 'Phường Gò Công', 'Phường Cai Lậy', 'Xã Châu Thành', 'Xã Cái Bè']
                    },
                    {
                        name: 'Bến Tre',
                        wards: ['Phường An Hội', 'Phường Phú Khương', 'Phường Phú Tân', 'Xã Châu Thành', 'Xã Ba Tri', 'Xã Giồng Trôm', 'Xã Mỏ Cày']
                    },
                    {
                        name: 'Trà Vinh',
                        wards: ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 9', 'Phường Duyên Hải', 'Xã Càng Long', 'Xã Cầu Ngang', 'Xã Tiểu Cần']
                    },
                    {
                        name: 'Vĩnh Long',
                        wards: ['Phường 1', 'Phường 2', 'Phường 4', 'Phường 9', 'Phường Bình Minh', 'Xã Long Hồ', 'Xã Mang Thít', 'Xã Tam Bình']
                    },
                    {
                        name: 'Đồng Tháp',
                        wards: ['Phường 1', 'Phường 2', 'Phường Hòa Thuận (Cao Lãnh)', 'Phường Sa Đéc', 'Phường Hồng Ngự', 'Xã Lấp Vò', 'Xã Tháp Mười']
                    },
                    {
                        name: 'An Giang',
                        wards: ['Phường Mỹ Long', 'Phường Mỹ Bình', 'Phường Mỹ Xuyên (Long Xuyên)', 'Phường Châu Phú A (Châu Đốc)', 'Phường Tân Châu', 'Xã Chợ Mới']
                    },
                    {
                        name: 'Kiên Giang',
                        wards: ['Phường Vĩnh Thanh', 'Phường Vĩnh Lạc', 'Phường Rạch Sỏi (Rạch Giá)', 'Phường Hà Tiên', 'Phường Dương Đông (Phú Quốc)', 'Phường An Thới (Phú Quốc)']
                    },
                    {
                        name: 'Hậu Giang',
                        wards: ['Phường 1', 'Phường 3', 'Phường 4 (Vị Thanh)', 'Phường Ngã Bảy', 'Phường Long Mỹ', 'Xã Châu Thành A', 'Xã Phụng Hiệp']
                    },
                    {
                        name: 'Sóc Trăng',
                        wards: ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 6', 'Phường Vĩnh Châu', 'Phường Ngã Năm', 'Xã Mỹ Xuyên', 'Xã Kế Sách']
                    },
                    {
                        name: 'Bạc Liêu',
                        wards: ['Phường 1', 'Phường 3', 'Phường 7', 'Phường Nhà Mát', 'Phường Giá Rai', 'Xã Hòa Bình', 'Xã Vĩnh Lợi', 'Xã Đông Hải']
                    },
                    {
                        name: 'Cà Mau',
                        wards: ['Phường 1', 'Phường 2', 'Phường 5', 'Phường 8', 'Phường 9', 'Xã Năm Căn', 'Xã Trần Văn Thời', 'Xã Cái Nước', 'Xã Đầm Dơi']
                    },
                    {
                        name: 'Hòa Bình',
                        wards: ['Phường Phương Lâm', 'Phường Đồng Tiến', 'Phường Tân Thịnh', 'Phường Tân Hòa', 'Xã Lương Sơn', 'Xã Kỳ Sơn', 'Xã Mai Châu']
                    },
                    {
                        name: 'Sơn La',
                        wards: ['Phường Quyết Thắng', 'Phường Tô Hiệu', 'Phường Chiềng Cơi', 'Phường Chiềng Lề', 'Phường Mộc Châu', 'Xã Mai Sơn', 'Xã Thuận Châu']
                    },
                    {
                        name: 'Điện Biên',
                        wards: ['Phường Mường Thanh', 'Phường Tân Thanh', 'Phường Nam Thanh', 'Phường Thanh Bình', 'Phường Mường Lay', 'Xã Điện Biên', 'Xã Tuần Giáo']
                    },
                    {
                        name: 'Lai Châu',
                        wards: ['Phường Tân Phong', 'Phường Đoàn Kết', 'Phường Quyết Tiến', 'Phường Đông Phong', 'Xã Tam Đường', 'Xã Phong Thổ']
                    },
                    {
                        name: 'Lào Cai',
                        wards: ['Phường Kim Tân', 'Phường Cốc Lếu', 'Phường Bắc Cường', 'Phường Sa Pa', 'Phường Bát Xát', 'Xã Bảo Thắng', 'Xã Bắc Hà']
                    },
                    {
                        name: 'Yên Bái',
                        wards: ['Phường Đồng Tâm', 'Phường Minh Tân', 'Phường Yên Ninh', 'Phường Nguyễn Thái Học', 'Phường Nghĩa Lộ', 'Xã Trấn Yên', 'Xã Văn Yên']
                    },
                    {
                        name: 'Tuyên Quang',
                        wards: ['Phường Phan Thiết', 'Phường Tân Quang', 'Phường Minh Xuân', 'Phường Nông Tiến', 'Xã Sơn Dương', 'Xã Yên Sơn', 'Xã Hàm Yên']
                    },
                    {
                        name: 'Hà Giang',
                        wards: ['Phường Trần Phú', 'Phường Nguyễn Trãi', 'Phường Minh Khai', 'Phường Ngọc Hà', 'Xã Vị Xuyên', 'Xã Đồng Văn', 'Xã Mèo Vạc']
                    },
                    {
                        name: 'Cao Bằng',
                        wards: ['Phường Hợp Giang', 'Phường Sông Bằng', 'Phường Tân Giang', 'Phường Đề Thám', 'Xã Hòa An', 'Xã Trùng Khánh', 'Xã Quảng Hòa']
                    },
                    {
                        name: 'Bắc Kạn',
                        wards: ['Phường Đức Xuân', 'Phường Sông Cầu', 'Phường Phùng Chí Kiên', 'Phường Nguyễn Thị Minh Khai', 'Xã Chợ Đồn', 'Xã Ba Bể']
                    },
                    {
                        name: 'Lạng Sơn',
                        wards: ['Phường Hoàng Văn Thụ', 'Phường Tam Thanh', 'Phường Vĩnh Trại', 'Phường Chi Lăng', 'Xã Cao Lộc', 'Xã Hữu Lũng', 'Xã Lộc Bình']
                    }
                ],

                availableWards: [],
                availableWardDetails: [],

                init() {
                    if (this.allVouchers && this.allVouchers.length > 0) {
                        const dbCodes = this.allVouchers.map(v => v.code);
                        this.suggestedChips = [...new Set([...this.suggestedChips, ...dbCodes])].slice(0, 4);
                    }

                    if (config.userAddress) {
                        this.streetAddress = config.userAddress;
                    }

                    this.selectProvinceName('Hà Nội');

                    // If initial shipping is provided, apply initial values
                    if (config.initialShipping && config.initialShipping.options) {
                        this.shippingOptions = config.initialShipping.options;
                        const cur = this.shippingOptions[this.shippingMethod] || this.shippingOptions.standard;
                        this.shippingFee = cur.fee;
                        this.selectedShippingTime = cur.time;
                    }

                    // Auto-apply best available Freeship voucher by default!
                    this.$nextTick(() => {
                        const bestFreeship = this.sortedShippingVouchers.find(v => this.isEligible(v) && this.getVoucherStatus(v).key === 'ACTIVE');
                        if (bestFreeship) {
                            this.selectedShippingVoucher = bestFreeship;
                        }
                        this.initGooglePlaces();
                    });

                    // Load complete official 34 merged provinces and 3,321 wards from bando.com.vn dataset
                    this.loadAdministrativeData();
                },

                async loadAdministrativeData() {
                    try {
                        const res = await fetch('/data/dvhc_vietnam.json');
                        if (res.ok) {
                            const data = await res.json();
                            if (Array.isArray(data) && data.length > 0) {
                                this.provinces = data;
                                const cur = this.provinces.find(p => p.name === this.selectedProvince || this.normalizeStr(p.name).includes(this.normalizeStr(this.selectedProvince)));
                                if (cur) {
                                    this.availableWards = cur.wards || [];
                                    this.availableWardDetails = cur.wardDetails || [];
                                }
                            }
                        }
                    } catch (e) {
                        console.warn('Official DVHC load note:', e);
                    }
                },

                initGooglePlaces() {
                    try {
                        if (window.google && window.google.maps && window.google.maps.places) {
                            const input = document.getElementById('street-address-input');
                            if (input) {
                                const autocomplete = new google.maps.places.Autocomplete(input, {
                                    componentRestrictions: { country: 'vn' },
                                    fields: ['address_components', 'formatted_address', 'geometry']
                                });
                                autocomplete.addListener('place_changed', () => {
                                    const place = autocomplete.getPlace();
                                    if (place && place.formatted_address) {
                                        this.streetAddress = place.formatted_address;
                                        this.onAddressChange();
                                    }
                                });
                            }
                        }
                    } catch (e) {
                        console.warn('Google Places Autocomplete init skipped:', e);
                    }
                },

                // Normalize Vietnamese text for instant fuzzy search
                normalizeStr(str) {
                    if (!str) return '';
                    return str.toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/đ/g, 'd');
                },

                filterList(list, search) {
                    if (!search || !search.trim()) return list;
                    const query = this.normalizeStr(search.trim());
                    return list.filter(item => {
                        const nameMatch = this.normalizeStr(item.name).includes(query);
                        const truocMatch = item.truocsapnhap ? this.normalizeStr(item.truocsapnhap).includes(query) : false;
                        return nameMatch || truocMatch;
                    });
                },

                filterWards(wards, search) {
                    if (!search || !search.trim()) return wards;
                    const query = this.normalizeStr(search.trim());
                    
                    if (this.availableWardDetails && this.availableWardDetails.length > 0) {
                        const matched = this.availableWardDetails.filter(w => {
                            const matchName = this.normalizeStr(w.name).includes(query);
                            const matchOld = w.truocsapnhap ? this.normalizeStr(w.truocsapnhap).includes(query) : false;
                            return matchName || matchOld;
                        });
                        if (matched.length > 0) {
                            return matched.map(m => m.name);
                        }
                    }
                    
                    return wards.filter(w => this.normalizeStr(w).includes(query));
                },

                getWardSubtitle(wardName) {
                    if (!this.availableWardDetails || this.availableWardDetails.length === 0) return '';
                    const detail = this.availableWardDetails.find(d => d.name === wardName);
                    if (detail && detail.truocsapnhap && !detail.truocsapnhap.includes('giữ nguyên')) {
                        return 'Sáp nhập từ: ' + detail.truocsapnhap;
                    }
                    return '';
                },

                selectProvinceName(name) {
                    this.selectedProvince = name;
                    const prov = this.provinces.find(p => p.name === name);
                    if (prov) {
                        this.availableWards = prov.wards || [];
                        this.availableWardDetails = prov.wardDetails || [];
                        this.selectedWard = this.availableWards[0] || '';
                    } else {
                        this.availableWards = [];
                        this.availableWardDetails = [];
                        this.selectedWard = '';
                    }
                    this.onAddressChange();
                },

                onAddressChange() {
                    this.updateShippingCalculation();
                },

                async updateShippingCalculation() {
                    this.distanceInfo.loading = true;
                    try {
                        const url = config.calculateShippingUrl || '/customer/checkout/calculate-shipping';
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                province: this.selectedProvince,
                                ward: this.selectedWard,
                                address: this.streetAddress,
                                subtotal: this.subtotal
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.options) {
                                this.shippingOptions = data.options;
                                this.distanceInfo.distance_km = data.distance_km;
                                this.distanceInfo.duration_text = data.duration_text;
                                this.distanceInfo.is_hanoi_inner = data.is_hanoi_inner;
                                this.distanceInfo.source = data.source;

                                // If currently on express and express is not available, revert to standard
                                if (this.shippingMethod === 'express' && !this.shippingOptions.express.available) {
                                    this.shippingMethod = 'standard';
                                }

                                const cur = this.shippingOptions[this.shippingMethod] || this.shippingOptions.standard;
                                this.shippingFee = cur.fee;
                                this.selectedShippingTime = cur.time;
                            }
                        }
                    } catch (err) {
                        console.error('Lỗi tính phí vận chuyển:', err);
                    } finally {
                        this.distanceInfo.loading = false;
                    }
                },

                get fullAddress() {
                    const parts = [];
                    if (this.streetAddress) parts.push(this.streetAddress.trim());
                    if (this.selectedWard) parts.push(this.selectedWard);
                    if (this.selectedProvince) parts.push(this.selectedProvince);
                    return parts.join(', ');
                },

                transferCode: 'MNB-' + Math.floor(100000 + Math.random() * 900000),

                get vietQrUrl() {
                    const amount = Math.round(this.finalTotal || 0);
                    const content = encodeURIComponent(this.transferCode);
                    return `https://img.vietqr.io/image/MB-0377466205-compact2.png?amount=${amount}&addInfo=${content}&accountName=NGUYEN%20NGOC%20ANH`;
                },

                copyText(text, msg) {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text);
                        alert(msg);
                    } else {
                        const input = document.createElement('input');
                        input.value = text;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                        alert(msg);
                    }
                },

                selectShipping(method, fee, time) {
                    this.shippingMethod = method;
                    this.shippingFee = fee;
                    this.selectedShippingTime = time;
                },

                voucherTab: 'ORDER',

                isEligible(v) {
                    if (!v) return false;
                    return this.subtotal >= parseFloat(v.min_order_value || 0);
                },

                calculateSavings(v) {
                    if (!v) return 0;
                    const baseAmount = v.voucher_type === 'SHIPPING' ? this.shippingFee : this.subtotal;
                    let discount = 0;
                    if (v.discount_type === 'PERCENTAGE') {
                        discount = (baseAmount * parseFloat(v.discount_value)) / 100;
                        if (v.max_discount_value) {
                            discount = Math.min(discount, parseFloat(v.max_discount_value));
                        }
                    } else {
                        discount = Math.min(baseAmount, parseFloat(v.discount_value));
                    }
                    return discount;
                },

                getVoucherStatus(v) {
                    if (!v) return { key: 'EXPIRED', label: '🔴 Đã hết hạn', badgeClass: 'bg-[#FDECEB] text-[#E04B4B] border-[#F8BDB8]', btnText: 'Hết hạn' };
                    const now = new Date();

                    if (v.status === 'INACTIVE') {
                        return { key: 'EXPIRED', label: '🔴 Tạm ngưng', badgeClass: 'bg-gray-100 text-gray-500 border-gray-200', btnText: 'Tạm ngưng' };
                    }
                    if (v.end_date && new Date(v.end_date) < now) {
                        return { key: 'EXPIRED', label: '🔴 Đã hết hạn', badgeClass: 'bg-[#FDECEB] text-[#E04B4B] border-[#F8BDB8]', btnText: 'Hết hạn' };
                    }
                    if (v.usage_limit && parseInt(v.used_count || 0) >= parseInt(v.usage_limit)) {
                        return { key: 'EXPIRED', label: '🔴 Đã hết lượt', badgeClass: 'bg-[#FDECEB] text-[#E04B4B] border-[#F8BDB8]', btnText: 'Hết lượt' };
                    }
                    if (v.start_date && new Date(v.start_date) > now) {
                        return { key: 'UPCOMING', label: '🟡 Sắp diễn ra', badgeClass: 'bg-[#FFF8E6] text-[#D4981E] border-[#FFE8A3]', btnText: 'Chưa mở' };
                    }
                    return { key: 'ACTIVE', label: '🟢 Đang diễn ra', badgeClass: 'bg-[#E8F8F0] text-[#1E9E60] border-[#B8EED0]', btnText: 'Áp dụng' };
                },

                filterVouchers(list, filterKey) {
                    if (!list) return [];
                    if (filterKey === 'ALL') return list;
                    return list.filter(v => this.getVoucherStatus(v).key === filterKey);
                },

                getFilteredVouchers(list, filterKey) {
                    return this.filterVouchers(list, filterKey);
                },

                get sortedOrderVouchers() {
                    return [...this.orderVouchers].sort((a, b) => {
                        const aStatus = this.getVoucherStatus(a).key;
                        const bStatus = this.getVoucherStatus(b).key;
                        
                        const statusWeight = { 'ACTIVE': 3, 'UPCOMING': 2, 'EXPIRED': 1 };
                        if ((statusWeight[aStatus] || 0) !== (statusWeight[bStatus] || 0)) {
                            return (statusWeight[bStatus] || 0) - (statusWeight[aStatus] || 0);
                        }

                        const aEligible = this.isEligible(a) ? 1 : 0;
                        const bEligible = this.isEligible(b) ? 1 : 0;
                        if (aEligible !== bEligible) return bEligible - aEligible;
                        
                        const aVal = a.discount_type === 'PERCENTAGE' ? (this.subtotal * parseFloat(a.discount_value)) / 100 : parseFloat(a.discount_value);
                        const bVal = b.discount_type === 'PERCENTAGE' ? (this.subtotal * parseFloat(b.discount_value)) / 100 : parseFloat(b.discount_value);
                        return bVal - aVal;
                    });
                },

                get sortedShippingVouchers() {
                    return [...this.shippingVouchers].sort((a, b) => {
                        const aStatus = this.getVoucherStatus(a).key;
                        const bStatus = this.getVoucherStatus(b).key;
                        
                        const statusWeight = { 'ACTIVE': 3, 'UPCOMING': 2, 'EXPIRED': 1 };
                        if ((statusWeight[aStatus] || 0) !== (statusWeight[bStatus] || 0)) {
                            return (statusWeight[bStatus] || 0) - (statusWeight[aStatus] || 0);
                        }

                        const aEligible = this.isEligible(a) ? 1 : 0;
                        const bEligible = this.isEligible(b) ? 1 : 0;
                        if (aEligible !== bEligible) return bEligible - aEligible;

                        const aVal = a.discount_type === 'PERCENTAGE' ? (this.shippingFee * parseFloat(a.discount_value)) / 100 : parseFloat(a.discount_value);
                        const bVal = b.discount_type === 'PERCENTAGE' ? (this.shippingFee * parseFloat(b.discount_value)) / 100 : parseFloat(b.discount_value);
                        return bVal - aVal;
                    });
                },

                toggleOrderVoucher(v) {
                    const status = this.getVoucherStatus(v);
                    if (status.key === 'EXPIRED') {
                        this.voucherMessage = `Mã [${v.code}] đã hết hạn hoặc hết lượt sử dụng.`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (status.key === 'UPCOMING') {
                        this.voucherMessage = `Mã [${v.code}] sắp diễn ra (bắt đầu từ ${this.formatDate(v.start_date)}).`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (!this.isEligible(v)) {
                        const diff = parseFloat(v.min_order_value) - this.subtotal;
                        this.voucherMessage = `Đơn hàng chưa đủ điều kiện (mua thêm ${this.formatVND(diff)} để áp dụng).`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (this.selectedOrderVoucher && this.selectedOrderVoucher.id === v.id) {
                        this.selectedOrderVoucher = null;
                        this.voucherMessage = `Đã bỏ chọn voucher shop [${v.code}].`;
                        this.voucherSuccess = true;
                    } else {
                        this.selectedOrderVoucher = v;
                        this.voucherMessage = `Áp dụng thành công voucher shop [${v.code}]!`;
                        this.voucherSuccess = true;
                    }
                },

                toggleShippingVoucher(v) {
                    const status = this.getVoucherStatus(v);
                    if (status.key === 'EXPIRED') {
                        this.voucherMessage = `Mã freeship [${v.code}] đã hết hạn hoặc hết lượt sử dụng.`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (status.key === 'UPCOMING') {
                        this.voucherMessage = `Mã freeship [${v.code}] sắp diễn ra (bắt đầu từ ${this.formatDate(v.start_date)}).`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (!this.isEligible(v)) {
                        const diff = parseFloat(v.min_order_value) - this.subtotal;
                        this.voucherMessage = `Đơn hàng chưa đủ điều kiện (mua thêm ${this.formatVND(diff)} để áp dụng).`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (this.selectedShippingVoucher && this.selectedShippingVoucher.id === v.id) {
                        this.selectedShippingVoucher = null;
                        this.voucherMessage = `Đã bỏ chọn voucher vận chuyển [${v.code}].`;
                        this.voucherSuccess = true;
                    } else {
                        this.selectedShippingVoucher = v;
                        this.voucherMessage = `Áp dụng thành công voucher vận chuyển [${v.code}]!`;
                        this.voucherSuccess = true;
                    }
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    try {
                        const d = new Date(dateStr);
                        return d.toLocaleDateString('vi-VN');
                    } catch (e) {
                        return dateStr;
                    }
                },

                applyChip(code) {
                    this.voucherInput = code;
                    this.applyVoucherByCode();
                },

                applyVoucherByCode() {
                    const code = (this.voucherInput || '').trim().toUpperCase();
                    if (!code) {
                        this.voucherMessage = 'Vui lòng nhập mã voucher';
                        this.voucherSuccess = false;
                        return;
                    }

                    if (this.usedVoucherCodes && this.usedVoucherCodes.map(c => c.toUpperCase()).includes(code)) {
                        this.voucherMessage = `Mã [${code}] này bạn đã sử dụng trên đơn hàng trước đó rồi nhé!`;
                        this.voucherSuccess = false;
                        return;
                    }

                    const matched = this.allVouchers.find(v => v.code.toUpperCase() === code);

                    if (!matched) {
                        this.voucherMessage = `Mã giảm giá [${code}] không tồn tại trong hệ thống.`;
                        this.voucherSuccess = false;
                        return;
                    }

                    const status = this.getVoucherStatus(matched);
                    if (status.key === 'EXPIRED') {
                        this.voucherMessage = `Mã [${matched.code}] đã hết hạn hoặc hết lượt sử dụng.`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (status.key === 'UPCOMING') {
                        this.voucherMessage = `Mã [${matched.code}] chưa tới thời gian áp dụng (bắt đầu từ ${this.formatDate(matched.start_date)}).`;
                        this.voucherSuccess = false;
                        return;
                    }
                    if (!this.isEligible(matched)) {
                        const diff = parseFloat(matched.min_order_value) - this.subtotal;
                        this.voucherMessage = `Đơn hàng chưa đủ điều kiện (mua thêm ${this.formatVND(diff)} để áp dụng mã [${matched.code}]).`;
                        this.voucherSuccess = false;
                        return;
                    }

                    if (matched.voucher_type === 'ORDER') {
                        this.selectedOrderVoucher = matched;
                        this.voucherMessage = `Áp dụng thành công voucher shop [${matched.code}]!`;
                    } else if (matched.voucher_type === 'SHIPPING') {
                        this.selectedShippingVoucher = matched;
                        this.voucherMessage = `Áp dụng thành công voucher vận chuyển [${matched.code}]!`;
                    }
                    this.voucherSuccess = true;
                },

                removeVoucher() {
                    this.selectedOrderVoucher = null;
                    this.selectedShippingVoucher = null;
                    this.voucherInput = '';
                    this.voucherMessage = '';
                    this.voucherSuccess = false;
                },

                get orderDiscount() {
                    if (!this.selectedOrderVoucher) return 0;
                    const v = this.selectedOrderVoucher;
                    if (this.subtotal < parseFloat(v.min_order_value || 0)) return 0;

                    let discount = 0;
                    if (v.discount_type === 'PERCENTAGE') {
                        discount = (this.subtotal * parseFloat(v.discount_value)) / 100;
                        if (v.max_discount_value) {
                            discount = Math.min(discount, parseFloat(v.max_discount_value));
                        }
                    } else {
                        discount = Math.min(this.subtotal, parseFloat(v.discount_value));
                    }
                    return discount;
                },

                get shippingDiscount() {
                    if (!this.selectedShippingVoucher) return 0;
                    const v = this.selectedShippingVoucher;
                    if (this.subtotal < parseFloat(v.min_order_value || 0)) return 0;

                    let discount = 0;
                    if (v.discount_type === 'PERCENTAGE') {
                        discount = (this.shippingFee * parseFloat(v.discount_value)) / 100;
                        if (v.max_discount_value) {
                            discount = Math.min(discount, parseFloat(v.max_discount_value));
                        }
                    } else {
                        discount = Math.min(this.shippingFee, parseFloat(v.discount_value));
                    }
                    return discount;
                },

                get finalTotal() {
                    const total = Math.max(0, this.subtotal - this.orderDiscount) + Math.max(0, this.shippingFee - this.shippingDiscount);
                    return Math.max(0, total);
                },

                formatVND(value) {
                    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value).replace('₫', 'đ');
                },

                handleSubmit(e) {
                    if (!this.fullAddress || !this.streetAddress.trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập địa chỉ cụ thể để giao hàng.');
                        return false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>

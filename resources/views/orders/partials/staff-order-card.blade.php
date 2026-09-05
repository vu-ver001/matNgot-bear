{{-- Thẻ đơn hàng (Order Card) chuyên dụng cho Staff & Admin --}}
@php
    $productCount = $order->details->count();
    $chatRoute = Route::has(($isStaff ? 'staff' : 'admin') . '.support.index') 
        ? route(($isStaff ? 'staff' : 'admin') . '.support.index') 
        : route('home');
@endphp

<div class="order-card-ecommerce staff-order-card"
     :class="{ 'is-selected': isSelected({{ $order->id }}) }"
     x-data="{ showAllProducts: false }">

    <!-- 1. Card Header: Checkbox, Mã đơn hàng, Tên người nhận, Icon nhắn tin, Trạng thái đơn -->
    <div class="order-card-header flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Checkbox chọn đơn -->
            @if($order->canTransitionTo('SHIPPING'))
                <label class="custom-order-checkbox flex items-center cursor-pointer select-none" title="Chọn đơn để giao hàng">
                    <input type="checkbox"
                           value="{{ $order->id }}"
                           :checked="isSelected({{ $order->id }})"
                           @change="toggleOrder({{ $order->id }})"
                           class="w-4.5 h-4.5 rounded border-[#D4C3B3] text-[#B87309] focus:ring-[#B87309] cursor-pointer">
                </label>
            @endif

            <!-- Mã đơn hàng -->
            <a href="{{ route($routePrefix . '.show', $order) }}" 
               class="font-extrabold text-sm sm:text-base text-[#4E342E] hover:text-[#B87309] transition flex items-center gap-1"
               title="Xem chi tiết đơn hàng #{{ $order->order_code }}">
                <span class="text-amber-700 font-black">#</span><span>{{ $order->order_code }}</span>
            </a>

            <span class="text-stone-300">|</span>

            <!-- Tên người nhận (bao gồm tên khách hàng) -->
            <div class="flex items-center gap-1.5 text-sm font-bold text-[#4E342E]">
                <i class="fa-regular fa-user text-xs text-[#8E8076]"></i>
                <span>{{ $order->recipient_name }}</span>
            </div>

            <!-- Nút Nhắn tin là icon (chuyển hướng đến trang nhắn tin) -->
            <a href="{{ $chatRoute }}" 
               class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200/80 flex items-center justify-center transition shadow-2xs hover:scale-105" 
               title="Nhắn tin cho khách hàng">
                <i class="fa-regular fa-comment-dots text-xs"></i>
            </a>
        </div>

        <!-- Trạng thái đơn hàng & Trạng thái thanh toán -->
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <x-order-status-badge :status="$order->order_status" />
            <x-payment-status-badge :status="$order->payment_status" />
        </div>
    </div>

    <!-- 2. Danh sách sản phẩm -->
    <div class="divide-y divide-[#F7EFE6]">
        @foreach ($order->details as $pIndex => $detail)
            @php
                $isFirstProduct = ($pIndex === 0);
                $product = $detail->product;
                $rawImg = $product?->images?->where('is_primary', true)->first()?->image_url
                    ?? $product?->images?->first()?->image_url;
                $imageUrl = $rawImg
                    ? (str_starts_with($rawImg, 'http') ? $rawImg : asset($rawImg))
                    : 'https://placehold.co/120x120/fef3c7/78350f?text=Bear';

                $variationParts = [];
                if ($product && !empty($product->size)) {
                    $variationParts[] = 'Size: ' . $product->size;
                }
                if ($product && !empty($product->color)) {
                    $variationParts[] = 'Màu: ' . $product->color;
                }
                $variation = !empty($variationParts) ? implode(', ', $variationParts) : 'Tiêu chuẩn';

                $currentPrice = (float) $detail->product_price;
                $originalPrice = ($product && $product->price > $currentPrice)
                    ? (float) $product->price
                    : $currentPrice;
                $productUrl = $detail->product_id
                    ? route('products.show', $detail->product_id)
                    : '#';
            @endphp
            <div x-show="{{ $isFirstProduct ? 'true' : 'showAllProducts' }}"
                 @if(!$isFirstProduct) x-cloak x-transition @endif
                 class="order-product-row flex items-start gap-4">
                <a href="{{ $productUrl }}" class="shrink-0 group" target="_blank">
                    <img src="{{ $imageUrl }}" 
                         alt="{{ $detail->product_name }}"
                         class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-xl border border-amber-200/80 bg-stone-50 shadow-2xs group-hover:scale-102 transition"
                         onerror="this.src='https://placehold.co/120x120/fef3c7/78350f?text=Bear'">
                </a>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div class="min-w-0">
                            <a href="{{ $productUrl }}" 
                               target="_blank"
                               class="font-bold text-sm sm:text-base text-[#4E342E] hover:text-[#B87309] transition line-clamp-2">
                                {{ $detail->product_name }}
                            </a>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-[#8E8076]">
                                <span class="bg-[#FAF7F2] px-2 py-0.5 rounded border border-[#EFE5D8] font-medium text-stone-600">
                                    {{ $variation }}
                                </span>
                                <span>Số lượng: <strong class="text-[#4E342E] font-bold">x{{ $detail->quantity }}</strong></span>
                            </div>
                        </div>

                        <div class="text-left sm:text-right shrink-0">
                            @if($originalPrice > $currentPrice)
                                <div class="text-xs text-stone-400 line-through">
                                    {{ number_format($originalPrice, 0, ',', '.') }}đ
                                </div>
                            @endif
                            <div class="text-sm sm:text-base font-extrabold text-[#E08A1E]">
                                {{ number_format($currentPrice, 0, ',', '.') }} đ
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Toggle sản phẩm nếu đơn có >= 2 sản phẩm -->
    @if($productCount >= 2)
        <div class="px-5 py-2 bg-amber-50/30 border-t border-[#F7EFE6] text-center">
            <button type="button" 
                    @click="showAllProducts = !showAllProducts"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#B87309] hover:text-[#8C4A19] transition cursor-pointer">
                <span x-show="!showAllProducts">Xem thêm {{ $productCount - 1 }} sản phẩm khác <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i></span>
                <span x-show="showAllProducts" x-cloak>Thu gọn bớt sản phẩm <i class="fa-solid fa-chevron-up text-[10px] ml-0.5"></i></span>
            </button>
        </div>
    @endif

    <!-- 3. Dải thông tin địa chỉ & ghi chú -->
    <div class="bg-[#FDFBF7] px-5 py-3 border-t border-b border-[#F0E6DA] flex flex-col md:flex-row md:items-center justify-between gap-2 text-xs text-[#795548]">
        <div class="flex flex-col sm:flex-row sm:items-center gap-x-4 gap-y-1">
            @if(!empty($order->recipient_address))
                <div class="flex items-center gap-1.5" title="Địa chỉ nhận hàng">
                    <i class="fa-solid fa-location-dot text-amber-600 text-[11px]"></i>
                    <span class="line-clamp-1 max-w-md">Địa chỉ: {{ $order->recipient_address }}</span>
                </div>
            @endif

            @if(!empty($order->note))
                <div class="flex items-center gap-1.5 text-amber-800 bg-amber-100/60 px-2 py-0.5 rounded font-medium">
                    <i class="fa-regular fa-note-sticky text-[11px]"></i>
                    <span class="line-clamp-1">Ghi chú: {{ $order->note }}</span>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <span>Ngày đặt: <strong class="text-[#4E342E] font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</strong></span>
        </div>
    </div>

    <!-- 4. Chân thẻ: Thành tiền & Thao tác nghiệp vụ -->
    <div class="order-card-footer flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Thành tiền -->
        <div class="flex items-baseline gap-2">
            <span class="text-xs text-[#795548]">Thành tiền:</span>
            <span class="text-base sm:text-lg font-black text-[#E08A1E]">
                {{ number_format($order->total_amount, 0, ',', '.') }} đ
            </span>
        </div>

        <!-- Thao tác nhanh -->
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            <!-- Xem chi tiết -->
            <a href="{{ route($routePrefix . '.show', $order) }}" class="btn-card-action btn-card-secondary text-xs">
                <i class="fa-regular fa-eye"></i> Chi tiết
            </a>

            <!-- In hóa đơn / Phiếu gửi nếu có -->
            @if(Route::has('customer.orders.invoice'))
                <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank" class="btn-card-action btn-card-ghost text-xs" title="In phiếu gửi hàng">
                    <i class="fa-solid fa-file-invoice"></i> In phiếu
                </a>
            @endif

            <!-- Chuyển trạng thái từng bước nhanh -->
            @if(in_array($order->order_status, ['PENDING', 'CONFIRMED', 'PREPARING']))
                @if($order->order_status === 'PENDING')
                    <form action="{{ route($routePrefix . '.updateStatus', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="order_status" value="CONFIRMED">
                        <button type="submit" class="btn-card-action btn-card-blue text-xs" title="Xác nhận đơn">
                            <i class="fa-solid fa-circle-check"></i> Xác nhận
                        </button>
                    </form>
                @elseif($order->order_status === 'CONFIRMED')
                    <form action="{{ route($routePrefix . '.updateStatus', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="order_status" value="PREPARING">
                        <button type="submit" class="btn-card-action btn-card-primary text-xs" title="Chuẩn bị hàng">
                            <i class="fa-solid fa-box-open"></i> Chuẩn bị hàng
                        </button>
                    </form>
                @elseif($order->order_status === 'PREPARING')
                    @if($order->canTransitionTo('SHIPPING'))
                        <form action="{{ route($routePrefix . '.updateStatus', $order) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="order_status" value="SHIPPING">
                            <button type="submit" class="btn-card-action btn-card-primary text-xs" title="Giao hàng ngay">
                                <i class="fa-solid fa-truck-fast"></i> Giao hàng ngay
                            </button>
                        </form>
                    @elseif($order->payment_method !== 'COD' && $order->payment_status !== 'PAID')
                        <span class="text-xs font-semibold text-amber-700" title="Đơn thanh toán trước chưa được xác nhận thanh toán">
                            <i class="fa-solid fa-lock"></i> Chờ thanh toán
                        </span>
                    @endif
                @endif
            @elseif($order->order_status === 'SHIPPING')
                <form action="{{ route($routePrefix . '.updateStatus', $order) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="order_status" value="COMPLETED">
                    <button type="submit" class="btn-card-action btn-card-emerald text-xs" title="Xác nhận hoàn thành">
                        <i class="fa-solid fa-circle-check"></i> Đã giao thành công
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

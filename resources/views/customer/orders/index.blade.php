<x-customer-account-layout title="Đơn hàng của tôi" :flush="true">
    <div class="p-4 sm:p-8">
        <div class="min-w-0">
            <div class="orders-ui" x-data="{ showContactModal: false }">
                <!-- Contact Seller Modal -->
                @include('customer.orders.partials.contact-modal')

                @include('orders.partials.alerts')
                @include('orders.partials.stats', ['isStaff' => false])

                <div class="panel-card">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">
                                <i class="fa-solid fa-boxes-packing"></i>
                                Đơn hàng của tôi
                            </div>
                            <div class="panel-subtitle">Tra cứu, lọc và theo dõi tiến trình đơn hàng của bạn</div>
                        </div>
                    </div>

                    <!-- Status Tabs (Pills) -->
                    @php
                        $routePrefix = 'customer.orders';
                        $tabs = [
                            '' => ['label' => 'Tất cả', 'count' => $stats['total'] ?? null],
                            'PENDING' => ['label' => 'Chờ xác nhận', 'count' => $stats['pending'] ?? 0],
                            'CONFIRMED' => ['label' => 'Đã xác nhận', 'count' => $stats['confirmed'] ?? 0],
                            'PREPARING' => ['label' => 'Chờ lấy hàng', 'count' => $stats['preparing'] ?? 0],
                            'SHIPPING' => ['label' => 'Chờ giao hàng', 'count' => $stats['shipping'] ?? 0],
                            'COMPLETED' => ['label' => 'Đã giao', 'count' => $stats['completed'] ?? 0],
                            'RETURNED' => ['label' => 'Trả hàng', 'count' => $stats['returned'] ?? 0],
                            'CANCELLED' => ['label' => 'Đã hủy', 'count' => $stats['cancelled'] ?? 0],
                        ];
                    @endphp
                    <div class="nav-pills">
                        @foreach ($tabs as $value => $tab)
                            <a href="{{ route($routePrefix.'.index', array_merge(request()->except('order_status', 'page'), $value ? ['order_status' => $value] : [])) }}"
                               class="nav-pill {{ (string) request('order_status') === $value ? 'active' : '' }}">
                                <span>{{ $tab['label'] }}</span>
                                @if (isset($tab['count']))
                                    <span class="nav-pill-count">{{ $tab['count'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <!-- Original Search & Filter Toolbar with Lọc and Đặt lại buttons -->
                    @include('orders.partials.filters', ['routePrefix' => $routePrefix])

                    <!-- Orders Cards List -->
                    <div class="orders-container space-y-4 mt-6">
                        @forelse ($orders as $order)
                            @php
                                $card = $order->toCustomerCardData();
                                $hasUnpaidOnline = $order->canPayOnline();
                                $productCount = count($card['products']);
                            @endphp

                            <!-- Order Card Item -->
                            <div class="order-card-ecommerce" x-data="{ showAllProducts: false }">
                                <!-- 1. Card Header: Shop info & Order Status -->
                                <div class="order-card-header flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-[#E08A1E]/15 text-[#8C4A19] flex items-center justify-center text-sm font-bold">
                                            🧸
                                        </div>
                                        <span class="font-bold text-sm text-[#4E342E]">{{ $card['shop']['name'] }}</span>
                                        
                                        @if($card['shop']['favorite'])
                                            <span class="badge-shopee-favorite">
                                                <i class="fa-solid fa-check text-[9px]"></i> Yêu Thích
                                            </span>
                                        @endif

                                        @if($card['shop']['chatEnabled'])
                                            <button type="button" 
                                                    @click="showContactModal = true" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-[#8C4A19] hover:bg-amber-100/60 rounded-lg transition border border-amber-200 cursor-pointer">
                                                <i class="fa-regular fa-comment-dots text-amber-600"></i>
                                                <span>Chat</span>
                                            </button>
                                        @endif

                                        <a href="{{ $card['shop']['shopUrl'] }}" 
                                           class="inline-flex items-center gap-1 text-xs font-medium text-stone-500 hover:text-[#B87309] hover:underline">
                                            <i class="fa-solid fa-store text-[10px]"></i>
                                            <span>Xem Shop</span>
                                        </a>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 flex-wrap">
                                        <div class="order-delivery-status">
                                            <i class="fa-solid fa-truck-fast text-emerald-600"></i>
                                            <span>{{ $card['order']['deliveryStatus'] }}</span>
                                        </div>

                                        <span class="text-stone-300 hidden md:inline">|</span>

                                        <span class="order-status-badge-tag {{ match($card['order']['status']) {
                                            'COMPLETED' => 'bg-emerald-100 text-emerald-800',
                                            'SHIPPING'  => 'bg-cyan-100 text-cyan-800',
                                            'PREPARING' => 'bg-purple-100 text-purple-800',
                                            'CONFIRMED' => 'bg-blue-100 text-blue-800',
                                            'PENDING'   => 'bg-amber-100 text-amber-800',
                                            'CANCELLED' => 'bg-rose-100 text-rose-800',
                                            'RETURNED'  => 'bg-stone-200 text-stone-700',
                                            default     => 'bg-stone-100 text-stone-800'
                                        } }}">
                                            {{ $card['order']['statusLabel'] }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 2. Products List -->
                                <div class="divide-y divide-[#F7EFE6]">
                                    @foreach ($card['products'] as $pIndex => $product)
                                        @php $isFirstProduct = ($pIndex === 0); @endphp
                                        <div x-show="{{ $isFirstProduct ? 'true' : 'showAllProducts' }}"
                                             @if(!$isFirstProduct) x-cloak x-transition @endif
                                             class="order-product-row flex items-start gap-4">
                                            <a href="{{ $product['productUrl'] }}" class="shrink-0 group">
                                                <img src="{{ $product['image'] }}" 
                                                     alt="{{ $product['name'] }}"
                                                     class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-2xl border border-amber-200/80 bg-stone-50 shadow-2xs group-hover:scale-102 transition"
                                                     onerror="this.src='https://placehold.co/120x120/fef3c7/78350f?text=Bear'">
                                            </a>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <a href="{{ $product['productUrl'] }}" 
                                                           class="font-bold text-sm sm:text-base text-[#4E342E] hover:text-[#B87309] transition line-clamp-2">
                                                            {{ $product['name'] }}
                                                        </a>
                                                        <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-[#8E8076]">
                                                            <span class="bg-[#FAF7F2] px-2.5 py-0.5 rounded-md border border-[#EFE5D8] font-medium text-stone-600">
                                                                {{ $product['variation'] }}
                                                            </span>
                                                            <span>Số lượng: <strong class="text-[#4E342E] font-bold">x{{ $product['quantity'] }}</strong></span>
                                                        </div>
                                                    </div>

                                                    <div class="text-left sm:text-right shrink-0">
                                                        @if($product['price']['original'] > $product['price']['current'])
                                                            <div class="text-xs text-stone-400 line-through">
                                                                {{ number_format($product['price']['original'], 0, ',', '.') }}đ
                                                            </div>
                                                        @endif
                                                        <div class="text-sm sm:text-base font-extrabold text-[#E08A1E]">
                                                            {{ number_format($product['price']['current'], 0, ',', '.') }} {{ $product['price']['currency'] === 'VND' ? 'đ' : $product['price']['currency'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Toggle products if order has >= 2 products -->
                                @if($productCount >= 2)
                                    <div class="px-5 py-2.5 bg-amber-50/40 border-t border-[#F7EFE6] text-center">
                                        <button type="button" 
                                                @click="showAllProducts = !showAllProducts"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#B87309] hover:text-[#8C4A19] transition cursor-pointer">
                                            <span x-show="!showAllProducts">Xem thêm {{ $productCount - 1 }} sản phẩm khác <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i></span>
                                            <span x-show="showAllProducts" x-cloak>Ẩn bớt sản phẩm <i class="fa-solid fa-chevron-up text-[10px] ml-0.5"></i></span>
                                        </button>
                                    </div>
                                @endif

                                <!-- 3. Clean Payment Summary Bar (Chỉ Ngày đặt và Thành tiền) -->
                                <div class="bg-[#FDFBF7] px-5 py-3.5 border-t border-b border-[#F0E6DA] flex items-center justify-between gap-3 text-xs text-[#795548]">
                                    <div class="flex items-center gap-2">
                                        <span>Ngày đặt: <strong class="text-[#4E342E] font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</strong></span>
                                    </div>

                                    <div class="text-right">
                                        <span class="text-sm sm:text-base font-semibold text-[#4E342E]">
                                            Thành tiền:
                                            <span class="text-lg sm:text-xl font-black text-[#E08A1E] ml-1">
                                                {{ number_format($card['payment']['total'], 0, ',', '.') }} {{ $card['payment']['currency'] === 'VND' ? 'đ' : $card['payment']['currency'] }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <!-- 4. Actions Footer -->
                                <div class="order-card-footer flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('customer.orders.invoice', $order) }}" 
                                           target="_blank" 
                                           class="btn-card-ghost text-xs" 
                                           title="Xem và in hóa đơn điện tử">
                                            <i class="fa-solid fa-file-invoice"></i>
                                            <span>Hóa đơn</span>
                                        </a>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                        <!-- Xem chi tiết -->
                                        <a href="{{ route('customer.orders.show', $order) }}" class="btn-card-action btn-card-secondary">
                                            <i class="fa-regular fa-eye"></i> Xem chi tiết
                                        </a>

                                        <!-- Mua lại (buyAgain) -->
                                        @if($card['actions']['buyAgain'])
                                            <form action="{{ route('customer.orders.reorder', $order->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn-card-action btn-card-primary">
                                                    <i class="fa-solid fa-cart-arrow-down"></i> Mua Lại
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Liên hệ Người bán (contactSeller) -->
                                        @if($card['actions']['contactSeller'])
                                            <button type="button" 
                                                    @click="showContactModal = true" 
                                                    class="btn-card-action btn-card-secondary">
                                                <i class="fa-regular fa-message"></i> Liên hệ Người bán
                                            </button>
                                        @endif

                                        <!-- Đánh giá (review) -->
                                        @if($card['actions']['review'])
                                            @php
                                                $firstProdId = $order->details->first()?->product_id;
                                            @endphp
                                            <a href="{{ $firstProdId ? route('products.show', $firstProdId) . '#reviews-section' : '#' }}" 
                                               class="btn-card-action btn-card-primary">
                                                <i class="fa-solid fa-star text-amber-200"></i> Đánh giá
                                            </a>
                                        @endif

                                        <!-- Thanh toán online nếu chưa thanh toán -->
                                        @if($hasUnpaidOnline)
                                            <a href="{{ route('customer.payment.qr', $order) }}" 
                                               class="btn-card-action bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white!">
                                                <i class="fa-solid fa-credit-card"></i> Thanh toán ngay
                                            </a>
                                        @endif

                                        <!-- Xác nhận đã nhận hàng nếu đang SHIPPING -->
                                        @if($order->order_status === 'SHIPPING')
                                            <form action="{{ route('customer.orders.complete', $order->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn đã nhận được kiện hàng và muốn xác nhận hoàn tất đơn hàng #{{ $order->order_code }}?')">
                                                @csrf
                                                <button type="submit" class="btn-card-action bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white!">
                                                    <i class="fa-solid fa-circle-check"></i> Đã nhận được hàng
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Hủy đơn nếu đang PENDING -->
                                        @if($order->order_status === 'PENDING')
                                            <form action="{{ route('customer.orders.cancel', $order->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn yêu cầu hủy đơn hàng #{{ $order->order_code }}?')">
                                                @csrf
                                                <button type="submit" class="btn-card-action text-rose-700! hover:bg-rose-50 border border-rose-200">
                                                    <i class="fa-solid fa-xmark"></i> Hủy đơn
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Empty State -->
                            <div class="p-10 text-center text-[#8E8076] bg-white rounded-2xl border border-amber-200/60">
                                <i class="fa-solid fa-box-open text-3xl text-amber-300 mb-2 block"></i>
                                Không tìm thấy đơn hàng nào phù hợp với điều kiện lọc.
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if ($orders->hasPages())
                        <div class="mt-6">
                            {{ $orders->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-customer-account-layout>

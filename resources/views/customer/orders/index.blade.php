<x-customer-account-layout title="Đơn hàng của tôi" :flush="true">
    <div class="p-4 sm:p-8">
        <div class="min-w-0">
            <div class="orders-ui">

                @include('orders.partials.alerts')

                <header class="customer-orders-hero"
                        style="--customer-orders-hero-image: url('{{ asset('images/orders/customer-order-banner.png') }}')">
                    <div class="customer-orders-hero-copy">
                        <span class="customer-orders-hero-kicker">
                            <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                            Đơn hàng của bạn
                        </span>
                        <h1>Theo dõi hành trình của những bé gấu</h1>
                        <p>Cập nhật trạng thái, thanh toán và xác nhận nhận hàng tại đây.</p>
                    </div>
                </header>

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
                            'SHIPPING' => ['label' => 'Đang giao hàng', 'count' => $stats['shipping'] ?? 0],
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
                            <div class="order-card-ecommerce" x-data="{ showAllProducts: false, showNotReceived: false, openChangePayment: false, openReturnModal: false }">
                                <!-- 1. Card Header: Store identity & Order Status -->
                                <div class="order-card-header flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3">
                                    <div class="flex items-center gap-2 sm:gap-2.5 shrink-0 whitespace-nowrap overflow-x-auto max-w-full pb-0.5 md:pb-0">
                                        <div class="w-7 h-7 rounded-lg bg-[#E08A1E]/15 text-[#8C4A19] flex items-center justify-center text-sm font-bold shrink-0">
                                            🧸
                                        </div>
                                        <span class="font-bold text-sm text-[#4E342E] shrink-0 whitespace-nowrap">{{ $card['shop']['name'] }}</span>

                                        <!-- Nút Nhắn tin là icon (giống bên Staff) -->
                                        <a href="{{ route('customer.messages.index') }}" 
                                           class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200/80 flex items-center justify-center transition shadow-2xs hover:scale-105 shrink-0" 
                                           title="Chat với Shop">
                                            <i class="fa-regular fa-comment-dots text-xs"></i>
                                        </a>

                                        <span class="text-stone-300 shrink-0">|</span>
                                        <a href="{{ route('customer.orders.show', $order) }}" class="text-xs text-[#8E8076] hover:text-amber-800 font-mono font-bold shrink-0 whitespace-nowrap">
                                            #{{ $order->order_code }}
                                        </a>
                                    </div>

                                    <div class="flex items-center gap-2 sm:gap-3 flex-wrap md:justify-end min-w-0">
                                        <div class="order-delivery-status text-xs sm:text-[12.5px] min-w-0">
                                            <i class="fa-solid fa-truck-fast text-emerald-600 shrink-0"></i>
                                            <span class="break-words leading-tight">{{ $card['order']['deliveryStatus'] }}</span>
                                        </div>

                                        <span class="text-stone-300 hidden lg:inline shrink-0">|</span>

                                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                            <x-order-status-badge :order="$order" />
                                            <x-payment-status-badge :status="$order->payment_status" />
                                        </div>
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

                                <!-- 3. Clean Payment Summary Bar -->
                                <div class="bg-[#FDFBF7] px-5 py-3.5 border-t border-b border-[#F0E6DA] flex items-center justify-between gap-3 text-xs text-[#795548]">
                                    <div class="flex items-center gap-2">
                                        <span>Ngày đặt: <strong class="text-[#4E342E] font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</strong></span>
                                        @if($hasUnpaidOnline && $order->paymentExpiresAt())
                                            <span class="text-amber-700 font-medium hidden sm:inline">· Hạn thanh toán: <strong>{{ $order->paymentExpiresAt()->format('H:i - d/m') }}</strong></span>
                                        @endif
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

                                        <!-- 1. Xác nhận đã nhận được hàng (Khi đang giao hoặc shop đã báo giao xong nhưng khách chưa xác nhận) -->
                                        @if($card['actions']['confirmReceived'])
                                            <form action="{{ route('customer.orders.confirm_received', $order->id) }}" 
                                                  method="POST" 
                                                  class="inline">
                                                @csrf
                                                <button type="submit" class="btn-card-action bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white! cursor-pointer shadow-xs">
                                                    <i class="fa-solid fa-circle-check"></i> Đã nhận được hàng
                                                </button>
                                            </form>
                                        @endif

                                        <!-- 2. Yêu cầu Trả hàng / Hoàn tiền -->
                                        @if($card['actions']['requestReturn'])
                                            <button type="button" 
                                                    @click="openReturnModal = true" 
                                                    class="btn-card-action text-rose-700! hover:bg-rose-50 border border-rose-200 cursor-pointer">
                                                <i class="fa-solid fa-arrow-rotate-left"></i> Trả hàng / Hoàn tiền
                                            </button>
                                            @include('customer.orders.partials.return-request-modal', ['order' => $order])
                                        @endif

                                        <!-- 3. Đang chờ shop duyệt trả hàng -->
                                        @if($card['actions']['hasPendingReturn'])
                                            <span class="btn-card-action text-amber-800! bg-amber-50 border border-amber-300 cursor-default">
                                                <i class="fa-solid fa-hourglass-half text-amber-600"></i> Chờ duyệt trả hàng
                                            </span>
                                        @endif

                                        <!-- 4. Đánh giá (CHỈ hiện khi đã xác nhận nhận hàng và có sản phẩm chưa đánh giá) -->
                                        @if($card['actions']['review'])
                                            <a href="{{ route('customer.orders.review', $order) }}" 
                                               data-open-order-review-modal
                                               data-order-id="{{ $order->id }}"
                                               class="btn-card-action btn-card-primary cursor-pointer">
                                                <i class="fa-solid fa-star text-amber-200"></i> Đánh giá
                                            </a>
                                        @endif

                                        <!-- 5. Mua lại (CHỈ hiện khi đã xác nhận nhận hàng, hoặc đơn đã hủy / đã trả hàng) -->
                                        @if($card['actions']['buyAgain'])
                                            <form action="{{ route('customer.orders.reorder', $order->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn-card-action btn-card-primary">
                                                    <i class="fa-solid fa-cart-arrow-down"></i> Mua Lại
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Chat với Shop -->
                                        <a href="{{ route('customer.messages.index') }}" 
                                           class="btn-card-action btn-card-secondary"
                                           title="Chat với Shop">
                                            <i class="fa-regular fa-comment-dots text-amber-700"></i> Chat với Shop
                                        </a>

                                        <!-- 7. Thanh toán online nếu chưa thanh toán -->
                                        @if($hasUnpaidOnline)
                                            <button type="button" @click="openChangePayment = true"
                                                    class="btn-card-action bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white! cursor-pointer">
                                                <i class="fa-solid fa-credit-card"></i> Thanh toán
                                            </button>
                                            @include('customer.orders.partials.payment-method-modal', ['modalState' => 'openChangePayment'])
                                        @endif

                                        <!-- 8. Hủy đơn nếu đang PENDING -->
                                        @if($order->order_status === 'PENDING')
                                            <form action="{{ route('customer.orders.cancel', $order->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->order_code }}?')">
                                                @csrf
                                                <button type="submit" class="btn-card-action text-rose-700! hover:bg-rose-50 border border-rose-200 cursor-pointer">
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

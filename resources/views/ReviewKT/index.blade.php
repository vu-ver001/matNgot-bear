@php
    $pageTitle = 'Đánh giá của tôi';
@endphp

<x-customer-account-layout :title="$pageTitle" :flush="true">
    <div class="my-reviews-page">
        {{-- 1. HERO BANNER --}}
        <header
            class="my-reviews-hero"
            style="--my-reviews-banner: url('{{ asset('images/ReviewKT/my-reviews-banner.png') }}')"
        >
            <div class="my-reviews-hero__copy">
                <h1 class="my-reviews-hero__calligraphy-title">
                    <span>Mỗi đánh giá là một</span>
                    <span>cái ôm yêu thương đến gần hơn <span class="my-reviews-hero__heart">♡</span></span>
                </h1>
                <p class="my-reviews-hero__desc">
                    Chia sẻ cảm nhận về những bé gấu bạn đã nhận được nhé!
                </p>
            </div>
        </header>

        {{-- 2. THÔNG BÁO FLASH NẾU CÓ --}}
        @if (session('success'))
            <div class="my-reviews-flash-alert is-success" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="my-reviews-flash-alert is-error" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- 3. CONTAINER CHÍNH CHỨA TAB & SẮP XẾP --}}
        <section
            class="my-reviews-main"
            x-data="{ currentTab: '{{ $activeTab }}', showLightbox: false, lightboxPhoto: '' }"
        >
            {{-- THANH ĐIỀU HƯỚNG TAB & SORT --}}
            <div class="my-reviews-toolbar-row">
                {{-- 2 nút tab dạng viên thuốc bo góc --}}
                <div class="my-reviews-tab-pills" role="tablist" aria-label="Tab đánh giá">
                    <button
                        type="button"
                        role="tab"
                        class="my-reviews-tab-pill"
                        :class="{ 'is-active': currentTab === 'pending' }"
                        :aria-selected="currentTab === 'pending' ? 'true' : 'false'"
                        @click="currentTab = 'pending'; const url = new URL(window.location); url.searchParams.set('tab', 'pending'); window.history.replaceState({}, '', url);"
                    >
                        <svg class="my-reviews-tab-pill__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>Chưa đánh giá ({{ $pendingCount }})</span>
                    </button>

                    <button
                        type="button"
                        role="tab"
                        class="my-reviews-tab-pill"
                        :class="{ 'is-active': currentTab === 'reviewed' }"
                        :aria-selected="currentTab === 'reviewed' ? 'true' : 'false'"
                        @click="currentTab = 'reviewed'; const url = new URL(window.location); url.searchParams.set('tab', 'reviewed'); window.history.replaceState({}, '', url);"
                    >
                        <svg class="my-reviews-tab-pill__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        <span>Đã đánh giá ({{ $reviewedCount }})</span>
                    </button>
                </div>

                {{-- Sắp xếp theo --}}
                <div class="my-reviews-sort-box">
                    <span class="my-reviews-sort-label">Sắp xếp theo:</span>
                    <div class="my-reviews-sort-select-wrap">
                        <select
                            class="my-reviews-sort-select"
                            onchange="const url = new URL(window.location); url.searchParams.set('sort', this.value); window.location = url;"
                        >
                            <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Mới hoàn thành gần đây</option>
                            <option value="oldest" @selected(($sort ?? '') === 'oldest')>Cũ nhất</option>
                            <option value="rating_desc" @selected(($sort ?? '') === 'rating_desc')>Đánh giá cao nhất</option>
                            <option value="rating_asc" @selected(($sort ?? '') === 'rating_asc')>Đánh giá thấp nhất</option>
                        </select>
                        <svg class="my-reviews-sort-select__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- TAB 1: CHƯA ĐÁNH GIÁ (DẠNG DANH SÁCH ĐƠN HÀNG THEO MẪU MỚI)   --}}
            {{-- ============================================================== --}}
            <div
                x-show="currentTab === 'pending'"
                x-cloak
                class="my-reviews-tab-panel"
                role="tabpanel"
                aria-label="Sản phẩm chưa đánh giá"
            >
                @if ($pendingItems->isEmpty())
                    {{-- Empty state tab Chưa đánh giá --}}
                    <div class="my-reviews-empty">
                        <div class="my-reviews-empty__icon-wrap">
                            <span class="my-reviews-empty__emoji" aria-hidden="true">🎉</span>
                        </div>
                        <h3 class="my-reviews-empty__title">Bạn đã đánh giá hết rồi!</h3>
                        <p class="my-reviews-empty__desc">
                            Cảm ơn bạn đã chia sẻ cảm nhận về những sản phẩm của Mật Ngọt Bear.
                        </p>
                        <a href="{{ route('products.index') }}" class="my-reviews-empty-btn">
                            <span>Xem sản phẩm</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @else
                    @php
                        $pendingOrders = $pendingItems->groupBy('order_id')->filter(function ($itemsInOrder) {
                            $order = $itemsInOrder->first()->order;
                            $completedDate = $order->completed_at ?? $order->updated_at;
                            return $completedDate && ! $completedDate->copy()->addDays(30)->isPast();
                        });
                    @endphp
                    @if ($pendingOrders->isEmpty())
                        {{-- Empty state tab Chưa đánh giá khi không còn đơn nào trong hạn 30 ngày --}}
                        <div class="my-reviews-empty">
                            <div class="my-reviews-empty__icon-wrap">
                                <span class="my-reviews-empty__emoji" aria-hidden="true">🎉</span>
                            </div>
                            <h3 class="my-reviews-empty__title">Bạn đã đánh giá hết rồi!</h3>
                            <p class="my-reviews-empty__desc">
                                Cảm ơn bạn đã chia sẻ cảm nhận về những sản phẩm của Mật Ngọt Bear.
                            </p>
                            <a href="{{ route('products.index') }}" class="my-reviews-empty-btn">
                                <span>Xem sản phẩm</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @else
                        <div class="my-reviews-orders-list">
                            @foreach ($pendingOrders as $orderId => $itemsInOrder)
                                @php
                                    $order = $itemsInOrder->first()->order;
                                    $completedDate = $order->completed_at ?? $order->updated_at;
                                @endphp
                                <article
                                    class="my-reviews-order-card"
                                    x-data="{ showMore: false }"
                                    tabindex="0"
                                    role="link"
                                    aria-label="Đơn hàng #{{ $order->order_code }}"
                                    onclick="if (!event.target.closest('button, [data-open-review-modal], [data-open-order-review-modal], a')) { window.location.href = '{{ route('customer.orders.show', $order) }}'; }"
                                    onkeydown="if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('button, [data-open-review-modal], [data-open-order-review-modal], a')) { event.preventDefault(); window.location.href = '{{ route('customer.orders.show', $order) }}'; }"
                                >
                                    {{-- Header đơn hàng: Đơn hàng: #... | Hoàn thành: ... --}}
                                    <div class="my-reviews-order-card__header">
                                        <div class="my-reviews-order-card__header-info">
                                            <svg class="my-reviews-order-card__header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15" aria-hidden="true">
                                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                                <path d="M3 6h18"/>
                                                <path d="M16 10a4 4 0 0 1-8 0"/>
                                            </svg>
                                            <span class="my-reviews-order-card__code-label">Đơn hàng:</span>
                                            <span class="my-reviews-order-card__code-val">#{{ $order->order_code }}</span>
                                            <span class="my-reviews-order-card__sep" aria-hidden="true">|</span>
                                            <span class="my-reviews-order-card__date-label">Hoàn thành:</span>
                                            <span class="my-reviews-order-card__date-val">{{ $completedDate ? $completedDate->format('d/m/Y') : '—' }}</span>
                                        </div>
                                        <svg class="my-reviews-order-card__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6"/>
                                        </svg>
                                    </div>

                                    {{-- Body hàng ngang gọn gàng: Bên trái là sản phẩm (+ Xem thêm nếu có), bên phải là Callout + Nút đánh giá --}}
                                    <div class="my-reviews-order-item">
                                        {{-- Cột 1: Thông tin sản phẩm (Hiện 1 sản phẩm đầu tiên, có nút Xem thêm nếu đơn > 1 sản phẩm) --}}
                                        <div class="my-reviews-order-item__product-col">
                                            @php
                                                $firstItem = $itemsInOrder->first();
                                                $firstProd = $firstItem->product;
                                                $firstImg = $firstProd?->images?->firstWhere('is_primary', true)?->image_url
                                                    ?? $firstProd?->images?->first()?->image_url
                                                    ?? asset('images/customer/product-placeholder.png');
                                                $variantParts = [];
                                                if (!empty($firstProd?->color)) {
                                                    $variantParts[] = 'Màu ' . $firstProd->color;
                                                }
                                                if (!empty($firstProd?->size)) {
                                                    $variantParts[] = 'Size ' . $firstProd->size;
                                                }
                                                if (empty($variantParts) && !empty($firstProd?->material)) {
                                                    $variantParts[] = $firstProd->material;
                                                }
                                                $variantText = !empty($variantParts) ? implode(' / ', $variantParts) : null;
                                            @endphp
                                            <div class="my-reviews-order-item__product">
                                                <img
                                                    src="{{ $firstImg }}"
                                                    alt="{{ $firstItem->product_name ?? $firstProd?->name ?? 'Sản phẩm' }}"
                                                    class="my-reviews-order-item__img"
                                                    loading="lazy"
                                                >
                                                <div class="my-reviews-order-item__info">
                                                    <h4 class="my-reviews-order-item__name">
                                                        {{ $firstItem->product_name ?? $firstProd?->name ?? 'Sản phẩm' }}
                                                    </h4>
                                                    @if ($variantText)
                                                        <div class="my-reviews-order-item__variant">
                                                            Phân loại: {{ $variantText }}
                                                        </div>
                                                    @endif
                                                    <div class="my-reviews-order-item__qty">
                                                        Số lượng: <span class="my-reviews-order-item__qty-num">x{{ $firstItem->quantity }}</span>
                                                    </div>
                                                    <div class="my-reviews-order-item__price">
                                                        {{ number_format($firstItem->product_price, 0, ',', '.') }}đ
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Nút Xem thêm & Danh sách các sản phẩm còn lại khi mở rộng --}}
                                            @if ($itemsInOrder->count() > 1)
                                                <div
                                                    x-show="showMore"
                                                    x-cloak
                                                    class="my-reviews-order-extra-products"
                                                >
                                                    @foreach ($itemsInOrder->slice(1) as $subItem)
                                                        @php
                                                            $subProd = $subItem->product;
                                                            $subImg = $subProd?->images?->firstWhere('is_primary', true)?->image_url
                                                                ?? $subProd?->images?->first()?->image_url
                                                                ?? asset('images/customer/product-placeholder.png');
                                                            $subVariantParts = [];
                                                            if (!empty($subProd?->color)) {
                                                                $subVariantParts[] = 'Màu ' . $subProd->color;
                                                            }
                                                            if (!empty($subProd?->size)) {
                                                                $subVariantParts[] = 'Size ' . $subProd->size;
                                                            }
                                                            if (empty($subVariantParts) && !empty($subProd?->material)) {
                                                                $subVariantParts[] = $subProd->material;
                                                            }
                                                            $subVariantText = !empty($subVariantParts) ? implode(' / ', $subVariantParts) : null;
                                                        @endphp
                                                        <div class="my-reviews-order-subproduct">
                                                            <img
                                                                src="{{ $subImg }}"
                                                                alt="{{ $subItem->product_name ?? $subProd?->name ?? 'Sản phẩm' }}"
                                                                class="my-reviews-order-item__img"
                                                                loading="lazy"
                                                            >
                                                            <div class="my-reviews-order-item__info">
                                                                <h4 class="my-reviews-order-item__name">
                                                                    {{ $subItem->product_name ?? $subProd?->name ?? 'Sản phẩm' }}
                                                                </h4>
                                                                @if ($subVariantText)
                                                                    <div class="my-reviews-order-item__variant">
                                                                        Phân loại: {{ $subVariantText }}
                                                                    </div>
                                                                @endif
                                                                <div class="my-reviews-order-item__qty">
                                                                    Số lượng: <span class="my-reviews-order-item__qty-num">x{{ $subItem->quantity }}</span>
                                                                </div>
                                                                <div class="my-reviews-order-item__price">
                                                                    {{ number_format($subItem->product_price, 0, ',', '.') }}đ
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <button
                                                    type="button"
                                                    class="my-reviews-toggle-more"
                                                    @click.stop="showMore = !showMore"
                                                    :aria-expanded="showMore ? 'true' : 'false'"
                                                >
                                                    <span x-show="!showMore" class="my-reviews-toggle-more__label">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                                            <path d="m6 9 6 6 6-6"/>
                                                        </svg>
                                                        <span>Xem thêm {{ $itemsInOrder->count() - 1 }} sản phẩm</span>
                                                    </span>
                                                    <span x-show="showMore" x-cloak class="my-reviews-toggle-more__label">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                                            <path d="m18 15-6-6-6 6"/>
                                                        </svg>
                                                        <span>Thu gọn</span>
                                                    </span>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- Cột 2: Khối thao tác bên phải chia làm 2 hàng (Hàng trên: Hạn đánh giá ngắn gọn, Hàng dưới: Nút đánh giá) --}}
                                        @php
                                            $completedDate = $order->completed_at ?? $order->updated_at;
                                            if ($completedDate) {
                                                $deadline = $completedDate->copy()->addDays(30)->startOfDay();
                                                $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($deadline, false));
                                            } else {
                                                $daysLeft = 30;
                                            }
                                        @endphp
                                        <div class="my-reviews-order-item__side">
                                            {{-- Hàng 1 (trên): Dòng chữ ngắn gọn thông báo số ngày còn lại để đánh giá --}}
                                            <div
                                                class="my-reviews-order-countdown {{ $daysLeft <= 3 ? 'is-urgent' : '' }}"
                                                title="Đánh giá sản phẩm trong vòng 30 ngày kể từ khi giao hàng thành công"
                                            >
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                <span>
                                                    @if ($daysLeft > 1)
                                                        Chỉ còn {{ $daysLeft }} ngày để đánh giá
                                                    @elseif ($daysLeft === 1)
                                                        Chỉ còn 1 ngày để đánh giá
                                                    @else
                                                        Hôm nay là hạn chót để đánh giá
                                                    @endif
                                                </span>
                                            </div>

                                            {{-- Hàng 2 (dưới): Nút Viết đánh giá --}}
                                            <button
                                                type="button"
                                                class="my-reviews-order-item__btn-write"
                                                data-open-order-review-modal
                                                data-order-id="{{ $order->id }}"
                                            >
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15" aria-hidden="true">
                                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                                </svg>
                                                <span>Viết đánh giá</span>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- ============================================================== --}}
            {{-- TAB 2: ĐÃ ĐÁNH GIÁ (GRID 3 CỘT THEO MẪU ẢNH 2)                --}}
            {{-- ============================================================== --}}
            <div
                x-show="currentTab === 'reviewed'"
                x-cloak
                class="my-reviews-tab-panel"
                role="tabpanel"
                aria-label="Đánh giá đã viết"
            >
                @if ($reviews->isEmpty())
                    {{-- Empty state tab Đã đánh giá --}}
                    <div class="my-reviews-empty">
                        <div class="my-reviews-empty__icon-wrap">
                            <span class="my-reviews-empty__emoji" aria-hidden="true">📝</span>
                        </div>
                        <h3 class="my-reviews-empty__title">Bạn chưa có đánh giá nào.</h3>
                        <p class="my-reviews-empty__desc">
                            Sau khi hoàn thành đơn hàng, bạn có thể chia sẻ cảm nhận về sản phẩm tại đây.
                        </p>
                        <a href="{{ route('products.index') }}" class="my-reviews-empty-btn">
                            <span>Khám phá sản phẩm ngay</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @else
                    <div class="my-reviews-reviewed-list">
                        @foreach ($reviews as $review)
                            @php
                                $prod = $review->product;
                                $img = $prod?->images?->firstWhere('is_primary', true)?->image_url
                                    ?? $prod?->images?->first()?->image_url
                                    ?? asset('images/customer/product-placeholder.png');
                                $variantParts = [];
                                if (!empty($prod?->color)) {
                                    $variantParts[] = 'Màu ' . $prod->color;
                                }
                                if (!empty($prod?->size)) {
                                    $variantParts[] = 'Size ' . $prod->size;
                                }
                                if (empty($variantParts) && !empty($prod?->material)) {
                                    $variantParts[] = $prod->material;
                                }
                                $variantText = !empty($variantParts) ? implode(' / ', $variantParts) : null;
                            @endphp
                            <article class="my-reviews-reviewed-card">
                                {{-- Body hàng ngang: Trái là Ảnh + Chi tiết & Nhận xét, Phải là Nút Sửa đánh giá --}}
                                <div class="my-reviews-reviewed-card__body">
                                    {{-- Cột trái: Ảnh sản phẩm + Nội dung đánh giá --}}
                                    <div class="my-reviews-reviewed-card__main">
                                        {{-- Thumbnail sản phẩm --}}
                                        <div class="my-reviews-reviewed-card__img-wrap">
                                            @if ($prod)
                                                <a href="{{ route('products.show', $prod->id) }}" title="{{ $prod->name }}">
                                                    <img src="{{ $img }}" alt="{{ $prod->name }}" class="my-reviews-reviewed-card__img" loading="lazy">
                                                </a>
                                            @else
                                                <img src="{{ $img }}" alt="Sản phẩm" class="my-reviews-reviewed-card__img" loading="lazy">
                                            @endif
                                        </div>

                                        {{-- Thông tin sản phẩm, số sao & hộp nhận xét --}}
                                        <div class="my-reviews-reviewed-card__info">
                                            <h3 class="my-reviews-reviewed-card__name">
                                                @if ($prod)
                                                    <a href="{{ route('products.show', $prod->id) }}">{{ $prod->name }}</a>
                                                @else
                                                    <span>Sản phẩm</span>
                                                @endif
                                            </h3>

                                            {{-- Hàng sao đánh giá, thời gian, phân loại & mã đơn hàng --}}
                                            <div class="my-reviews-reviewed-card__stars-row">
                                                <div class="my-reviews-reviewed-card__stars" aria-label="{{ $review->rating }}/5 sao">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg
                                                            class="my-reviews-star-filled {{ $i <= $review->rating ? 'is-gold' : 'is-gray' }}"
                                                            viewBox="0 0 24 24"
                                                            fill="currentColor"
                                                            aria-hidden="true"
                                                        >
                                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="my-reviews-reviewed-card__rating-text">
                                                    @if ($review->rating === 5)
                                                        Tuyệt vời
                                                    @elseif ($review->rating === 4)
                                                        Hài lòng
                                                    @elseif ($review->rating === 3)
                                                        Bình thường
                                                    @elseif ($review->rating === 2)
                                                        Chưa tốt
                                                    @else
                                                        Kém
                                                    @endif
                                                @if ($variantText)
                                                    <span class="my-reviews-reviewed-card__dot" aria-hidden="true">•</span>
                                                    <span class="my-reviews-reviewed-card__variant">
                                                        Phân loại: {{ $variantText }}
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Khối nhận xét trích dẫn --}}
                                            <div class="my-reviews-reviewed-card__comment-box">
                                                <svg class="my-reviews-reviewed-card__quote-icon" viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true">
                                                    <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/>
                                                </svg>
                                                <p class="my-reviews-reviewed-card__comment-text">{{ $review->comment }}</p>
                                            </div>

                                            {{-- Hình ảnh khách hàng đính kèm khi đánh giá --}}
                                            @if (!empty($review->images) && is_array($review->images) && count($review->images) > 0)
                                                <div class="my-reviews-reviewed-photos">
                                                    @foreach ($review->images as $photoUrl)
                                                        <div
                                                            class="my-reviews-reviewed-photo-item"
                                                            role="button"
                                                            tabindex="0"
                                                            @click="lightboxPhoto = '{{ $photoUrl }}'; showLightbox = true"
                                                            @keydown.enter="lightboxPhoto = '{{ $photoUrl }}'; showLightbox = true"
                                                            title="Bấm để xem ảnh phóng to"
                                                        >
                                                            <img src="{{ $photoUrl }}" alt="Ảnh đánh giá" loading="lazy">
                                                            <div class="my-reviews-reviewed-photo-zoom-icon">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="15" height="15" aria-hidden="true">
                                                                    <circle cx="11" cy="11" r="8"/>
                                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                                                    <line x1="11" y1="8" x2="11" y2="14"/>
                                                                    <line x1="8" y1="11" x2="14" y2="11"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Nhãn nhỏ nếu đã từng chỉnh sửa --}}
                                            @if ($review->is_edited)
                                                <div class="my-reviews-reviewed-card__edited-note">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                                        <circle cx="12" cy="12" r="10"/>
                                                        <path d="M12 6v6l4 2"/>
                                                    </svg>
                                                    <span>Đã chỉnh sửa lúc {{ $review->updated_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Cột phải: Góc trên là Ngày đánh giá, Góc dưới là Nút Sửa đánh giá --}}
                                    <div class="my-reviews-reviewed-card__side">
                                        <div class="my-reviews-reviewed-card__date" title="Thời gian gửi đánh giá">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                            <span>{{ $review->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        @if ($review->canBeEdited())
                                            <button
                                                type="button"
                                                class="my-reviews-action-btn is-edit"
                                                data-open-review-modal
                                                data-review-id="{{ $review->id }}"
                                                data-product-id="{{ $review->product_id }}"
                                                data-order-id="{{ $review->order_id }}"
                                                data-order-code="{{ $review->order?->order_code ?? '' }}"
                                                data-product-name="{{ $prod?->name ?? 'Sản phẩm' }}"
                                                data-product-image="{{ $img }}"
                                                data-rating="{{ $review->rating }}"
                                                data-comment="{{ $review->comment }}"
                                                data-images="{{ json_encode($review->images ?? []) }}"
                                                data-is-edited="false"
                                            >
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true">
                                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                                </svg>
                                                <span>Sửa đánh giá</span>
                                            </button>
                                        @endif

                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Phân trang --}}
                    <div class="my-reviews-pagination">
                        {{ $reviews->appends(['tab' => 'reviewed', 'sort' => $sort ?? 'latest'])->links() }}
                    </div>
                @endif
            </div>

            {{-- LIGHTBOX PHÓNG TO ẢNH ĐÁNH GIÁ --}}
            <div
                x-show="showLightbox"
                x-cloak
                class="my-reviews-lightbox-backdrop"
                @click="showLightbox = false"
                @keydown.escape.window="showLightbox = false"
            >
                <div class="my-reviews-lightbox-content" @click.stop>
                    <button
                        type="button"
                        class="my-reviews-lightbox-close"
                        @click="showLightbox = false"
                        aria-label="Đóng ảnh"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                    <img :src="lightboxPhoto" alt="Ảnh đánh giá phóng to" class="my-reviews-lightbox-img">
                </div>
            </div>
        </section>
    </div>

    {{-- LẮNG NGHE EVENT HOÀN THÀNH ĐÁNH GIÁ ĐỂ RELOAD ĐỒNG BỘ DATA & COUNT --}}
    <script>
        window.addEventListener('review:order_completed', function () {
            setTimeout(function () {
                window.location.reload();
            }, 900);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const autoOrderId = urlParams.get('order_id') || urlParams.get('order');
            if (autoOrderId) {
                setTimeout(function () {
                    if (window.openOrderReviewModal) {
                        window.openOrderReviewModal(autoOrderId);
                    } else {
                        const btn = document.querySelector(`[data-open-order-review-modal][data-order-id="${autoOrderId}"]`);
                        if (btn) btn.click();
                    }
                }, 350);
            }
        });
    </script>
</x-customer-account-layout>

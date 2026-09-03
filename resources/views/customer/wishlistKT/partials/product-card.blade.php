@php
    $isInactive = $item['status'] !== \App\Models\Product::STATUS_ACTIVE;
    $isOutOfStock = $item['stock_quantity'] <= 0;
    $canAddToCart = ! $isInactive && ! $isOutOfStock;
    $hasSalePrice = $item['sale_price'] !== null && (float) $item['sale_price'] < (float) $item['price'];
    $discountPercent = $hasSalePrice
        ? (int) round((1 - ((float) $item['sale_price'] / (float) $item['price'])) * 100)
        : 0;
    $rawImage = $item['primary_image'];
    $imageUrl = $rawImage && \Illuminate\Support\Str::startsWith($rawImage, ['http://', 'https://', '//'])
        ? $rawImage
        : ($rawImage ? asset(ltrim($rawImage, '/')) : null);
    $roundedRating = (int) round($item['average_rating'] ?? 0);
@endphp

<article class="wishlist-card" data-wishlist-card data-product-id="{{ $item['product_id'] }}">
    <div class="wishlist-card-media">
        <div class="wishlist-card-fallback" aria-hidden="true">
            <svg viewBox="0 0 160 145">
                <circle cx="42" cy="35" r="23" />
                <circle cx="118" cy="35" r="23" />
                <circle cx="80" cy="72" r="54" />
                <ellipse cx="80" cy="85" rx="27" ry="22" />
                <circle cx="61" cy="65" r="4" />
                <circle cx="99" cy="65" r="4" />
                <path d="M74 80c4-4 8-4 12 0-1 7-11 7-12 0Zm6 7v7m-11-1c7 7 15 7 22 0" />
            </svg>
        </div>

        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $item['product_name'] }}" loading="lazy" data-product-image>
        @endif

        <div class="wishlist-card-badges">
            @if ($isInactive)
                <span class="wishlist-stock-badge is-unavailable">Ngừng bán</span>
            @elseif ($isOutOfStock)
                <span class="wishlist-stock-badge is-out">Tạm hết hàng</span>
            @elseif ($hasSalePrice)
                <span class="wishlist-sale-badge">Giảm {{ $discountPercent }}%</span>
            @else
                <span class="wishlist-stock-badge is-available">Còn hàng</span>
            @endif
        </div>

        <form method="POST" action="{{ route('customer.wishlist.destroy', $item['product_id']) }}" data-wishlist-remove-form>
            @csrf
            @method('DELETE')
            <button type="submit" class="wishlist-remove-button" aria-label="Xóa {{ $item['product_name'] }} khỏi danh sách yêu thích">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.8 5.3a5.2 5.2 0 0 0-7.4 0L12 6.7l-1.4-1.4a5.2 5.2 0 0 0-7.4 7.4L12 21l8.8-8.3a5.2 5.2 0 0 0 0-7.4Z" />
                </svg>
            </button>
        </form>
    </div>

    <div class="wishlist-card-body">
        <h3>{{ $item['product_name'] }}</h3>

        <div class="wishlist-rating" aria-label="{{ $item['average_rating'] ? 'Đánh giá '.$item['average_rating'].' trên 5' : 'Chưa có đánh giá' }}">
            <span aria-hidden="true">
                @for ($star = 1; $star <= 5; $star++)
                    <svg @class(['is-filled' => $star <= $roundedRating]) viewBox="0 0 24 24">
                        <path d="m12 2.8 2.8 5.7 6.3.9-4.6 4.4 1.1 6.3-5.6-3-5.6 3 1.1-6.3-4.6-4.4 6.3-.9L12 2.8Z" />
                    </svg>
                @endfor
            </span>
            <small>{{ $item['reviews_count'] > 0 ? $item['reviews_count'].' đánh giá' : 'Chưa có đánh giá' }}</small>
        </div>

        <div class="wishlist-price">
            @if ($hasSalePrice)
                <strong>{{ number_format((float) $item['sale_price'], 0, ',', '.') }}₫</strong>
                <del>{{ number_format((float) $item['price'], 0, ',', '.') }}₫</del>
            @else
                <strong>{{ number_format((float) $item['price'], 0, ',', '.') }}₫</strong>
            @endif
        </div>

        <form method="POST" action="{{ route('customer.cart.store', $item['product_id']) }}" data-wishlist-cart-form>
            @csrf
            <button
                type="submit"
                @class(['wishlist-cart-button', 'is-disabled' => ! $canAddToCart])
                title="{{ $isInactive ? 'Sản phẩm hiện không khả dụng' : ($isOutOfStock ? 'Sản phẩm đang hết hàng' : 'Thêm sản phẩm vào giỏ hàng') }}"
                @disabled(! $canAddToCart)
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L20.5 8H6m4 11.5h.01m7 0h.01" />
                </svg>
                <span>
                    @if ($isInactive)
                        Sản phẩm không khả dụng
                    @elseif ($isOutOfStock)
                        Tạm hết hàng
                    @else
                        Thêm vào giỏ hàng
                    @endif
                </span>
            </button>
        </form>
    </div>
</article>

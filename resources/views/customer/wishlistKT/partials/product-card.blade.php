@php
    $regularPrice = (float) ($item['price'] ?? 0);
    $salePrice = !empty($item['sale_price']) ? (float) $item['sale_price'] : null;
    $hasSale = !empty($salePrice) && $salePrice < $regularPrice;
    $discountPct = ($hasSale && $regularPrice > 0) ? round((($regularPrice - $salePrice) / $regularPrice) * 100) : 0;
    $rawImage = $item['primary_image'] ?? null;
    $imgUrl = $rawImage && \Illuminate\Support\Str::startsWith($rawImage, ['http://', 'https://', '//'])
        ? $rawImage
        : ($rawImage ? asset(ltrim($rawImage, '/')) : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' . urlencode($item['product_name']));
    $isInactive = ($item['status'] ?? 'ACTIVE') !== 'ACTIVE';
    $isOutOfStock = ((int) ($item['stock_quantity'] ?? 0)) <= 0;
    $rating = !empty($item['average_rating']) ? (float) $item['average_rating'] : 5.0;
    $soldCount = $item['sold_count'] ?? 0;
    $categoryName = $item['category_name'] ?? 'Gấu bông';
@endphp

<div class="product-card" data-wishlist-card data-product-id="{{ $item['product_id'] }}">
    <div class="product-card-img-wrap">
        <div class="wishlist-card-badges">
            @if ($isInactive)
                <span class="wishlist-stock-badge is-unavailable">Ngừng bán</span>
            @elseif ($isOutOfStock)
                <span class="wishlist-stock-badge is-out">Tạm hết hàng</span>
            @endif

            @if ($hasSale && $discountPct > 0)
                <span class="card-badge-sale">-{{ $discountPct }}%</span>
            @endif
        </div>

        <form method="POST" action="{{ route('customer.wishlist.destroy', $item['product_id']) }}" data-wishlist-remove-form style="position: absolute; top: 10px; right: 10px; z-index: 10; margin: 0;">
            @csrf
            @method('DELETE')
            @if (request('view') === 'account')
                <input type="hidden" name="view" value="account">
            @endif
            <button type="submit" class="btn-wishlist-card active" title="Bỏ khỏi yêu thích" aria-label="Bỏ {{ $item['product_name'] }} khỏi danh sách yêu thích" style="position: static;">
                <i class="fa-solid fa-heart" style="color: #E57373;"></i>
            </button>
        </form>

        <a href="{{ route('products.show', $item['product_id']) }}">
            <img src="{{ $imgUrl }}" alt="{{ $item['product_name'] }}" class="product-card-img" onerror="this.src='https://placehold.co/600x600/f5e6ca/7c4a2d?text=Gau+Bong'">
        </a>
    </div>

    <div class="product-card-body">
        <div>
            <div class="product-card-category">{{ $categoryName }}</div>
            <div class="product-card-title-wrap">
                <a href="{{ route('products.show', $item['product_id']) }}" title="{{ $item['product_name'] }}">
                    <h3 class="product-card-title" title="{{ $item['product_name'] }}">{{ $item['product_name'] }}</h3>
                </a>
                <div class="product-title-tooltip" role="tooltip">{{ $item['product_name'] }}</div>
            </div>
        </div>
        <div>
            <div class="product-card-prices">
                @if ($hasSale)
                    <span class="price-current">{{ number_format($salePrice, 0, ',', '.') }} đ</span>
                    <span class="price-old">{{ number_format($regularPrice, 0, ',', '.') }} đ</span>
                @else
                    <span class="price-current" style="color: var(--primary-dark, #5D4037);">{{ number_format($regularPrice, 0, ',', '.') }} đ</span>
                @endif
            </div>
            <div class="product-card-footer">
                <div class="product-card-meta">
                    <span class="rating-badge-pill" title="Đánh giá {{ number_format($rating, 1) }} sao">
                        <i class="fa-solid fa-star"></i> {{ number_format($rating, 1) }}
                    </span>
                    <span class="sold-count-text">Đã bán {{ $soldCount }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

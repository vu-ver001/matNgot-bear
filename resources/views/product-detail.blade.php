@extends('layouts.customer')

@section('title', $product->name . ' - Mật Ngọt Bear')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/product-detail.css') }}">
@endsection

@section('content')

@php
    $hasSale = $product->is_on_sale;
    $discountPct = $hasSale ? round((($product->price - $product->sale_price) / $product->price) * 100) : 0;
    $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $primaryUrl = $primaryImg ? $primaryImg->image_url : 'https://placehold.co/800x800/f5e6ca/7c4a2d?text=' . urlencode($product->name);
@endphp

<div class="detail-page-container">

    <!-- Breadcrumb -->
    <div class="breadcrumb-nav">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang Chủ</a>
        <span>/</span>
        <a href="{{ route('products.index') }}">Danh Sách Sản Phẩm</a>
        <span>/</span>
        <a href="{{ route('products.index', ['category_id' => $product->category_id]) }}">{{ $product->category->name ?? 'Gấu Bông' }}</a>
        <span>/</span>
        <span style="font-weight: 700; color: var(--primary-dark);">{{ $product->name }}</span>
    </div>

    <!-- Product Detail Card -->
    <div class="product-detail-grid">
        
        <!-- Left Column: Gallery -->
        <div class="detail-gallery">
            <div class="gallery-main-frame">
                <img id="main-preview-img" src="{{ $primaryUrl }}" alt="{{ $product->name }}" class="gallery-main-img" onerror="this.src='https://placehold.co/800x800/f5e6ca/7c4a2d?text=Gau+Bong'">
            </div>

            <!-- Thumbnails -->
            <div class="gallery-thumbs-row">
                @if($product->images->count() > 0)
                    @foreach($product->images as $index => $img)
                        <div class="gallery-thumb-item {{ ($img->id === $primaryImg?->id || $index === 0) ? 'active' : '' }}" onclick="switchMainImage('{{ $img->image_url }}', this)">
                            <img src="{{ $img->image_url }}" alt="Thumbnail {{ $index + 1 }}" onerror="this.src='https://placehold.co/100x100?text=Gau'">
                        </div>
                    @endforeach
                @else
                    <div class="gallery-thumb-item active">
                        <img src="{{ $primaryUrl }}" alt="Thumbnail">
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Info & Action -->
        <div class="detail-info-col">
            <!-- Category Badge & Wishlist Heart (Góc trên cùng bên phải) -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                <a href="{{ route('products.index', ['category_id' => $product->category_id]) }}" class="detail-cat-badge" style="margin-bottom: 0;">
                    <i class="fa-solid fa-paw" style="color: var(--honey-dark);"></i> {{ $product->category->name ?? 'Gấu Bông' }}
                </a>
                <button type="button" class="btn-wishlist-card" data-product-id="{{ $product->id }}" onclick="toggleWishlist({ id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->price }}, sale_price: {{ $product->sale_price ?? 'null' }}, image_url: '{{ $primaryUrl }}' }, event)" title="Lưu vào yêu thích" style="position: static; width: 44px; height: 44px; font-size: 18px; box-shadow: 0 4px 14px rgba(78, 52, 46, 0.1);">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>

            <!-- Title -->
            <h1 class="detail-product-title">{{ $product->name }}</h1>

            @php
                $avgRating = $product->reviews_count > 0 ? round($product->avg_rating, 1) : 5.0;
                $fullStars = floor($avgRating);
                $hasHalf = ($avgRating - $fullStars) >= 0.5;
            @endphp

            <!-- Rating & Sold -->
            <div class="detail-rating-row">
                <a href="#reviews-section" class="stars-group" style="text-decoration: none;" title="Xem các đánh giá">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $fullStars)
                            <i class="fa-solid fa-star"></i>
                        @elseif($i == $fullStars + 1 && $hasHalf)
                            <i class="fa-solid fa-star-half-stroke"></i>
                        @else
                            <i class="fa-regular fa-star" style="color: #D7CCC8;"></i>
                        @endif
                    @endfor
                    <strong style="color: var(--text-main); margin-left: 4px;">{{ number_format($avgRating, 1) }}</strong>
                </a>
                <span>&bull;</span>
                <a href="#reviews-section" style="color: var(--text-muted); font-weight: 700; text-decoration: underline;" title="Xem chi tiết đánh giá">{{ $product->reviews_count ?? 0 }} đánh giá</a>
                <span>&bull;</span>
                <span><i class="fa-solid fa-fire" style="color: #FF5722;"></i> Đã bán {{ $product->sold_count ?? 0 }} em gấu</span>
            </div>

            <!-- Price Box -->
            <div class="detail-price-box">
                @if($hasSale)
                    <div class="detail-price-current">{{ number_format($product->sale_price, 0, ',', '.') }} đ</div>
                    <div class="detail-price-old">{{ number_format($product->price, 0, ',', '.') }} đ</div>
                    <div class="detail-sale-badge">-{{ $discountPct }}% TIẾT KIỆM</div>
                @else
                    <div class="detail-price-current" style="color: var(--primary-dark);">{{ number_format($product->price, 0, ',', '.') }} đ</div>
                @endif
            </div>

            <!-- Specifications -->
            <div class="specs-grid">
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-ruler"></i> Kích thước</div>
                    <div class="spec-value">{{ $product->size ?? 'Tiêu chuẩn' }}</div>
                </div>
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-palette"></i> Màu sắc</div>
                    <div class="spec-value">{{ $product->color ?? 'Tự nhiên' }}</div>
                </div>
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-feather"></i> Chất liệu</div>
                    <div class="spec-value">{{ $product->material ?? 'Bông PP 3D' }}</div>
                </div>
            </div>

            <!-- Stock status & Quantity -->
            <div class="stock-status-row">
                <div>
                    @if($product->stock_quantity > 0)
                        <span class="stock-pill in-stock">
                            <i class="fa-solid fa-circle-check"></i> Còn {{ $product->stock_quantity }} sản phẩm
                        </span>
                    @else
                        <span class="stock-pill out-stock">
                            <i class="fa-solid fa-circle-xmark"></i> Tạm hết hàng
                        </span>
                    @endif
                </div>

                <!-- Quantity Selector -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-muted);">Số lượng:</span>
                    <div class="qty-selector">
                        <button type="button" class="qty-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" id="detail-quantity" class="qty-input" value="1" min="1" max="{{ $product->stock_quantity }}" readonly>
                        <button type="button" class="qty-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                </div>
            </div>

            <!-- Actions Buttons -->
            <div class="action-buttons-group">
                <button type="button" class="btn-add-cart-main" onclick="handleAddToCart()" {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                    <i class="fa-solid fa-bag-shopping"></i> Thêm Vào Giỏ
                </button>
                <button type="button" class="btn-buy-now" onclick="handleBuyNow()" {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                    <i class="fa-solid fa-bolt"></i> Mua Ngay
                </button>
            </div>

            <!-- Store Guarantee -->
            <div class="guarantees-card">
                <div class="guarantee-item">
                    <i class="fa-solid fa-gift" style="color: var(--honey-dark);"></i>
                    <span>Tặng kèm thiệp viết tay & nơ gói quà xinh xắn.</span>
                </div>
                <div class="guarantee-item">
                    <i class="fa-solid fa-shield-heart" style="color: var(--honey-dark);"></i>
                    <span>100% Gòn xoắn 3 chiều tinh khiết không rụng lông.</span>
                </div>
                <div class="guarantee-item">
                    <i class="fa-solid fa-truck" style="color: var(--honey-dark);"></i>
                    <span>Đóng gói hút chân không cẩn thận, giao nhanh toàn quốc.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Description & Guide Section -->
    <div class="detail-tabs-section">
        <div class="detail-tab-header">
            <i class="fa-solid fa-book-open" style="color: var(--honey-dark);"></i>
            Mô Tả Chi Tiết & Hướng Dẫn Bảo Quản
        </div>
        <div class="detail-desc-content">
            <p style="margin-bottom: 1rem;">
                {{ $product->description ?: 'Chú gấu bông ' . $product->name . ' là món quà tuyệt vời dành tặng cho bản thân, người yêu hoặc bạn bè trong những dịp đặc biệt. Lớp vải nhung bên ngoài siêu mềm mịn, không gây kích ứng cho da nhạy cảm hay trẻ nhỏ.' }}
            </p>

            <h4 style="font-family: 'Be Vietnam Pro', sans-serif; font-size: 18px; font-weight: 700; color: var(--primary-dark); margin: 1.5rem 0 10px 0;">
                🧸 Hướng dẫn giặt & vệ sinh gấu bông:
            </h4>
            <ul style="padding-left: 1.5rem; display: flex; flex-direction: column; gap: 8px;">
                <li>Nên cho gấu bông vào túi giặt lưới trước khi cho vào máy giặt.</li>
                <li>Sử dụng nước giặt dịu nhẹ, chọn chế độ giặt êm hoặc sấy nhẹ.</li>
                <li>Phơi gấu bông ở nơi thoáng mát, có gió hoặc nắng nhẹ để bông luôn thơm tho và tơi xốp.</li>
                <li>Với gấu bông size lớn, bạn có thể tháo đường chỉ sau lưng để lấy bông ra giặt vỏ riêng.</li>
            </ul>
        </div>
    </div>

    <!-- Customer Reviews Section (Đánh Giá Sản Phẩm Từ Khách Hàng) -->
    <div class="detail-tabs-section" id="reviews-section" style="margin-top: 2rem;">
        <div class="detail-tab-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-star-half-stroke" style="color: var(--honey-dark);"></i>
                <span>Đánh Giá Sản Phẩm ({{ $product->reviews_count ?? 0 }})</span>
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted);">
                <i class="fa-solid fa-shield-check" style="color: var(--success);"></i> 100% Đánh giá từ khách mua hàng thực tế
            </div>
        </div>

        <div class="detail-desc-content" style="padding-top: 1rem;">
            <!-- Rating Overview Card -->
            <div class="review-overview-card">
                <!-- Left: Big Score -->
                <div class="review-score-box">
                    <div class="review-big-score">{{ number_format($avgRating, 1) }}</div>
                    <div class="review-stars-large">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $fullStars)
                                <i class="fa-solid fa-star"></i>
                            @elseif($i == $fullStars + 1 && $hasHalf)
                                <i class="fa-solid fa-star-half-stroke"></i>
                            @else
                                <i class="fa-regular fa-star" style="color: #D7CCC8;"></i>
                            @endif
                        @endfor
                    </div>
                    <div class="review-total-text">{{ $product->reviews_count }} lượt đánh giá</div>
                </div>

                <!-- Center: Progress Bars Breakdown -->
                <div class="review-bars-breakdown">
                    @for($star = 5; $star >= 1; $star--)
                        @php
                            $starCount = $ratingCounts[$star] ?? 0;
                            $starPct = $product->reviews_count > 0 ? round(($starCount / $product->reviews_count) * 100) : ($star === 5 ? 100 : 0);
                        @endphp
                        <div class="review-bar-row">
                            <span class="bar-label">{{ $star }} <i class="fa-solid fa-star" style="color: var(--honey); font-size: 11px;"></i></span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ $starPct }}%;"></div>
                            </div>
                            <span class="bar-count">{{ $starCount }}</span>
                        </div>
                    @endfor
                </div>

                <!-- Right: Star Filter Buttons -->
                <div class="review-filter-chips">
                    <button type="button" class="star-filter-chip active" onclick="filterReviews('all', this)">
                        Tất Cả ({{ $product->reviews_count }})
                    </button>
                    @for($star = 5; $star >= 1; $star--)
                        <button type="button" class="star-filter-chip" onclick="filterReviews({{ $star }}, this)">
                            {{ $star }} Sao ({{ $ratingCounts[$star] ?? 0 }})
                        </button>
                    @endfor
                </div>
            </div>

            <!-- Review Items List -->
            <div class="review-items-list" id="reviewItemsList">
                @forelse($product->reviews as $review)
                    <div class="review-item-card" data-rating="{{ $review->rating }}">
                        <div class="review-item-header">
                            <div class="review-author-info">
                                <div class="review-avatar">
                                    {{ mb_substr($review->user->full_name ?? 'K', 0, 1) }}
                                </div>
                                <div>
                                    <div class="review-author-name">
                                        {{ $review->user->full_name ?? 'Khách hàng thân thiết' }}
                                        <span class="badge-verified-buyer">
                                            <i class="fa-solid fa-circle-check"></i> Đã mua hàng
                                        </span>
                                    </div>
                                    <div class="review-meta-line">
                                        <div class="review-item-stars">
                                            @for($s = 1; $s <= 5; $s++)
                                                @if($s <= $review->rating)
                                                    <i class="fa-solid fa-star"></i>
                                                @else
                                                    <i class="fa-regular fa-star" style="color: #D7CCC8;"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="review-date">&bull; {{ $review->created_at ? $review->created_at->format('d/m/Y H:i') : 'Vừa xong' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="review-item-comment">
                            {{ $review->comment }}
                        </div>

                        <div class="review-item-footer">
                            <div class="review-variant-tag">
                                <i class="fa-solid fa-paw" style="color: var(--honey);"></i> Phân loại: {{ $product->size ?? 'Size tiêu chuẩn' }} - {{ $product->color ?? 'Màu tự nhiên' }}
                            </div>
                            <button type="button" class="btn-helpful-like" onclick="this.classList.toggle('liked'); const countSpan = this.querySelector('span'); if(countSpan) { let n = parseInt(countSpan.innerText) || 0; countSpan.innerText = this.classList.contains('liked') ? n + 1 : Math.max(0, n - 1); }">
                                <i class="fa-regular fa-thumbs-up"></i> Hữu ích (<span>0</span>)
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="review-empty-state">
                        <div class="review-empty-icon"><i class="fa-solid fa-comment-dots"></i></div>
                        <h4>Chưa có đánh giá nào cho sản phẩm này</h4>
                        <p>Hãy là người đầu tiên sở hữu chú gấu bông này và để lại nhận xét đáng yêu bạn nhé!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <div style="margin-top: 3rem;">
            <h2 class="related-section-title">
                <i class="fa-solid fa-heart" style="color: #E57373;"></i>
                Có Thể Bạn Cũng Thích
            </h2>
            <div class="related-grid">
                @foreach($relatedProducts as $rel)
                    @php
                        $relImg = $rel->images->firstWhere('is_primary', true) ?? $rel->images->first();
                        $relImgUrl = $relImg ? $relImg->image_url : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' . urlencode($rel->name);
                        $relSale = !empty($rel->sale_price) && $rel->sale_price < $rel->price;
                    @endphp
                    <div class="product-card">
                        <div class="product-card-img-wrap">
                            @if($relSale)
                                <span class="card-badge-sale">Sale</span>
                            @endif
                            <button type="button" class="btn-wishlist-card" data-product-id="{{ $rel->id }}" onclick="toggleWishlist({ id: {{ $rel->id }}, name: '{{ addslashes($rel->name) }}', price: {{ $rel->price }}, sale_price: {{ $rel->sale_price ?? 'null' }}, image_url: '{{ $relImgUrl }}' }, event)" title="Lưu vào yêu thích">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                            <a href="{{ route('products.show', $rel->id) }}">
                                <img src="{{ $relImgUrl }}" alt="{{ $rel->name }}" class="product-card-img" onerror="this.src='https://placehold.co/600x600/f5e6ca/7c4a2d?text=Gau+Bong'">
                            </a>
                        </div>
                        <div class="product-card-body">
                            <div>
                                <div class="product-card-category">{{ $rel->category->name ?? 'Gấu Bông' }}</div>
                                <a href="{{ route('products.show', $rel->id) }}">
                                    <h3 class="product-card-title">{{ $rel->name }}</h3>
                                </a>
                            </div>
                            <div>
                                <div class="product-card-prices">
                                    @if($relSale)
                                        <span class="price-current">{{ number_format($rel->sale_price, 0, ',', '.') }} đ</span>
                                        <span class="price-old">{{ number_format($rel->price, 0, ',', '.') }} đ</span>
                                    @else
                                        <span class="price-current" style="color: var(--primary-dark);">{{ number_format($rel->price, 0, ',', '.') }} đ</span>
                                    @endif
                                </div>
                                <div class="product-card-footer">
                                    <span><i class="fa-solid fa-ruler"></i> {{ $rel->size ?? 'Free size' }}</span>
                                    <button type="button" class="btn-add-cart-quick" onclick="addToCart({{ $rel->id }}, '{{ addslashes($rel->name) }}')" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection

@section('scripts')
<script>
    const maxStock = {{ (int) $product->stock_quantity }};

    function switchMainImage(url, thumbEl) {
        document.getElementById('main-preview-img').src = url;
        document.querySelectorAll('.gallery-thumb-item').forEach(el => el.classList.remove('active'));
        thumbEl.classList.add('active');
    }

    function changeQuantity(delta) {
        const input = document.getElementById('detail-quantity');
        let current = parseInt(input.value) || 1;
        let next = current + delta;
        if (next < 1) next = 1;
        if (maxStock > 0 && next > maxStock) {
            next = maxStock;
            Toast.fire({
                icon: 'warning',
                title: `Số lượng tối đa trong kho là ${maxStock} em gấu.`
            });
        }
        input.value = next;
    }

    function handleAddToCart() {
        const qty = parseInt(document.getElementById('detail-quantity').value) || 1;
        addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', qty);
    }

    function handleBuyNow() {
        const qty = parseInt(document.getElementById('detail-quantity').value) || 1;
        addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', qty, 'checkout');
    }

    function filterReviews(star, btn) {
        document.querySelectorAll('.star-filter-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');

        const cards = document.querySelectorAll('.review-item-card');
        cards.forEach(card => {
            const cardRating = card.getAttribute('data-rating');
            if (star === 'all' || cardRating == star) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endsection

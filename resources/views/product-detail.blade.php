@extends('layouts.customer')

@section('title', $product->name . ' - Mật Ngọt Bear')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/product-detail.css') }}">
@endsection

@section('content')

@php
    $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $primaryUrl = $primaryImg ? $primaryImg->image_url : 'https://placehold.co/800x800/f5e6ca/7c4a2d?text=' . urlencode($product->name);

    // Xử lý biến thể từ CSDL
    $variants = $product->variants;
    $variantSizes = $variants->pluck('size')->filter()->unique()->values();
    if ($variantSizes->isEmpty() && !empty($product->available_sizes)) {
        $variantSizes = collect($product->available_sizes);
    }
    
    // Gom màu sắc kèm ảnh mẫu tương ứng
    $variantColors = $variants->unique('color')->values();
    if ($variantColors->isEmpty() && !empty($product->available_colors)) {
        $variantColors = collect($product->available_colors)->map(function($cl) use ($primaryUrl) {
            return (object)[
                'color' => $cl,
                'image_url' => $primaryUrl,
            ];
        });
    }

    $defaultVariant = $variants->firstWhere('is_default', true) ?? $variants->first();
    $initialSize = $defaultVariant ? $defaultVariant->size : ($variantSizes->first() ?? 'Tiêu chuẩn');
    $initialColor = $defaultVariant ? $defaultVariant->color : ($variantColors->first()?->color ?? 'Tự nhiên');
    $initialImg = ($defaultVariant && !empty($defaultVariant->image_url)) ? $defaultVariant->image_url : $primaryUrl;
    $initialStock = $defaultVariant ? (int) $defaultVariant->stock_quantity : (int) $product->stock_quantity;

    // Trạng thái sale của biến thể mặc định
    $initialIsOnSale = $defaultVariant ? $defaultVariant->is_on_sale : false;
    $initialIsUpcoming = $defaultVariant ? $defaultVariant->is_sale_upcoming : false;
    $initialDiscountPct = $defaultVariant ? $defaultVariant->discount_percent : 0;
    $initialPrice = $defaultVariant ? (float) $defaultVariant->price : (float) $product->price;
    $initialSalePrice = $defaultVariant ? (float) $defaultVariant->sale_price : null;
    $initialEffectivePrice = $initialIsOnSale ? $initialSalePrice : $initialPrice;
    $initialRemainingSec = $defaultVariant ? $defaultVariant->sale_remaining_seconds : 0;

    // Tổng hợp toàn bộ ảnh sản phẩm & ảnh các biến thể con hiển thị ở gallery
    $galleryList = collect();
    foreach ($product->images as $pImg) {
        if (!empty($pImg->image_url) && !$galleryList->contains($pImg->image_url)) {
            $galleryList->push($pImg->image_url);
        }
    }
    foreach ($variants as $v) {
        if (!empty($v->image_url) && !$galleryList->contains($v->image_url)) {
            $galleryList->push($v->image_url);
        }
    }
    if ($galleryList->isEmpty()) {
        $galleryList->push($primaryUrl);
    }

    // Chuẩn bị danh sách ảnh cho Lightbox Modal (kèm chú thích phân loại)
    $modalGalleryItems = collect();
    foreach ($product->images as $pImg) {
        if (!empty($pImg->image_url)) {
            $modalGalleryItems->push([
                'url' => $pImg->image_url,
                'label' => $pImg->is_primary ? 'Ảnh đại diện chính' : 'Ảnh sản phẩm',
            ]);
        }
    }
    foreach ($variants as $v) {
        if (!empty($v->image_url)) {
            $varLabel = trim(($v->size ? $v->size : '') . ($v->size && $v->color ? ' - ' : '') . ($v->color ? $v->color : ''));
            $existing = $modalGalleryItems->firstWhere('url', $v->image_url);
            if (!$existing) {
                $modalGalleryItems->push([
                    'url' => $v->image_url,
                    'label' => $varLabel ? 'Phân loại: ' . $varLabel : 'Ảnh chi tiết biến thể',
                ]);
            } else if ($existing && str_contains($existing['label'], 'Ảnh') && $varLabel) {
                $modalGalleryItems = $modalGalleryItems->map(function($item) use ($v, $varLabel) {
                    if ($item['url'] === $v->image_url) {
                        $item['label'] = 'Phân loại: ' . $varLabel;
                    }
                    return $item;
                });
            }
        }
    }
    if ($modalGalleryItems->isEmpty()) {
        $modalGalleryItems->push([
            'url' => $primaryUrl,
            'label' => $product->name,
        ]);
    }
@endphp

<div class="detail-page-container">

    <!-- Breadcrumb -->
    <div class="breadcrumb-nav">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang Chủ</a>
        <span>/</span>
        <a href="{{ route('products.index') }}">Danh Sách Sản Phẩm</a>
        <span>/</span>
        <a href="{{ route('products.index', ['category_id' => $product->category_id, 'sort' => 'best_seller']) }}">{{ $product->category->name ?? 'Gấu Bông' }}</a>
        <span>/</span>
        <span style="font-weight: 700; color: var(--primary-dark);">{{ $product->name }}</span>
    </div>

    <!-- Product Detail Card -->
    <div class="product-detail-grid">
        
        <!-- Left Column: Gallery -->
        <div class="detail-gallery">
            <div class="gallery-main-frame" onclick="openImageModal()" title="Bấm vào để xem ảnh chi tiết">
                <img id="main-preview-img" src="{{ $initialImg }}" alt="{{ $product->name }}" class="gallery-main-img" onerror="this.src='https://placehold.co/800x800/f5e6ca/7c4a2d?text=Gau+Bong'">
                <div class="gallery-zoom-badge">
                    <i class="fa-solid fa-expand"></i> Phóng to ảnh
                </div>
            </div>

            <!-- Thumbnails -->
            <div class="gallery-thumbs-row">
                @foreach($galleryList as $index => $gUrl)
                    <div class="gallery-thumb-item {{ ($gUrl === $initialImg || ($index === 0 && empty($initialImg))) ? 'active' : '' }}" 
                         data-img-url="{{ $gUrl }}" 
                         onclick="switchMainImage('{{ $gUrl }}', this); openImageModal('{{ $gUrl }}');"
                         title="Bấm để xem chi tiết ảnh">
                        <img src="{{ $gUrl }}" alt="Thumbnail {{ $index + 1 }}" onerror="this.src='https://placehold.co/100x100?text=Gau'">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Column: Info & Action -->
        <div class="detail-info-col">
            <!-- Category Badge & Wishlist Heart (Góc trên cùng bên phải) -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                <a href="{{ route('products.index', ['category_id' => $product->category_id, 'sort' => 'best_seller']) }}" class="detail-cat-badge" style="margin-bottom: 0;">
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

            <!-- 1. BỘ THỜI GIAN ĐẾM NGƯỢC KHUYẾN MÃI (ẢNH 2) -->
            <div class="sale-countdown-banner" id="sale-countdown-banner" style="{{ $initialIsOnSale ? 'display: flex;' : 'display: none;' }}">
                <div class="sale-banner-left">
                    <span class="sale-banner-title"><i class="fa-solid fa-bolt"></i> Ngày Siêu Mua Sắm</span>
                </div>
                <div class="sale-banner-right">
                    <span class="sale-timer-label"><i class="fa-regular fa-clock"></i> KẾT THÚC TRONG</span>
                    <div class="sale-timer-digits">
                        <span class="timer-box" id="timer-box-h">00</span>
                        <span class="timer-box" id="timer-box-m">00</span>
                        <span class="timer-box" id="timer-box-s">00</span>
                    </div>
                </div>
            </div>

            <!-- 2. KHỐI GIÁ TIỀN & THÔNG BÁO FLASH SALE SẮP DIỄN RA (ẢNH 3) -->
            <div class="detail-price-box {{ $initialIsOnSale ? 'has-countdown' : '' }}" id="detail-price-box">
                <div class="detail-price-main-row">
                    <div class="detail-price-current" id="detail-price-current" style="{{ $initialIsOnSale ? 'color: #D32F2F;' : 'color: var(--primary-dark);' }}">
                        {{ number_format($initialEffectivePrice, 0, ',', '.') }} đ
                    </div>
                    <div class="detail-price-old" id="detail-price-old" style="{{ $initialIsOnSale ? 'display: inline;' : 'display: none;' }}">
                        {{ number_format($initialPrice, 0, ',', '.') }} đ
                    </div>
                    <div class="detail-sale-badge" id="detail-sale-badge" style="{{ $initialIsOnSale ? 'display: inline-flex;' : 'display: none;' }}">
                        -{{ $initialDiscountPct }}% TIẾT KIỆM
                    </div>
                </div>

                <!-- 3. KHUYẾN MÃI SẮP DIỄN RA TRONG TƯƠNG LAI (ẢNH 3) -->
                <div class="upcoming-sale-bar" id="upcoming-sale-bar" style="{{ $initialIsUpcoming ? 'display: flex;' : 'display: none;' }}">
                    <div class="upcoming-sale-left">
                        <span class="upcoming-flash-badge"><i class="fa-solid fa-clock-rotate-left"></i> <em>FLASH</em> SALE</span>
                        <span class="upcoming-flash-time" id="upcoming-flash-time">
                            @if($defaultVariant && $defaultVariant->sale_start_at)
                                BẮT ĐẦU SAU {{ $defaultVariant->sale_start_at->format('H:i') }}, {{ $defaultVariant->sale_start_at->day }} Thg {{ $defaultVariant->sale_start_at->month }}
                            @else
                                BẮT ĐẦU SỚM
                            @endif
                        </span>
                        <span class="upcoming-flash-discount" id="upcoming-flash-discount">
                            @if($initialIsUpcoming)
                                Giảm {{ $initialDiscountPct }}%
                            @endif
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-right upcoming-flash-chevron"></i>
                </div>
            </div>

            <!-- KHỐI PHÂN LOẠI SHOPEE: MÀU SẮC KÈM ẢNH & KÍCH THƯỚC (ĂN LIỀN VỚI CSDL) -->
            <div class="shopee-variant-box">
                <!-- 1. PHÂN LOẠI MÀU SẮC (CÓ ẢNH THU NHỎ BÊN CẠNH NHƯ SHOPEE) -->
                @if($variantColors->count() > 0)
                <div class="shopee-variant-row">
                    <div class="shopee-variant-label">Phân Loại</div>
                    <div class="shopee-variant-options" id="color-options-container">
                        @foreach($variantColors as $cVar)
                            @php
                                $initVarForColor = $variants->first(function($v) use ($cVar, $initialSize) {
                                    return $v->color === $cVar->color && $v->size === $initialSize;
                                }) ?? $variants->firstWhere('color', $cVar->color);
                                
                                $thumbUrl = ($initVarForColor && !empty($initVarForColor->image_url)) 
                                            ? $initVarForColor->image_url 
                                            : (!empty($cVar->image_url) ? $cVar->image_url : $primaryUrl);
                                $isColorActive = ($cVar->color === $initialColor);
                            @endphp
                            <button 
                                type="button" 
                                class="shopee-color-option {{ $isColorActive ? 'active' : '' }}" 
                                data-color="{{ $cVar->color }}"
                                data-img="{{ $thumbUrl }}"
                                onmouseenter="previewVariantColorBtn(this)"
                                onmouseleave="restoreSelectedImage()"
                                onclick="selectProductColor('{{ addslashes($cVar->color) }}', this)"
                                title="{{ $cVar->color }}"
                            >
                                <img src="{{ $thumbUrl }}" alt="{{ $cVar->color }}" class="shopee-color-thumb" onerror="this.src='{{ $primaryUrl }}'">
                                <span class="shopee-color-text">{{ $cVar->color }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 2. KÍCH THƯỚC (SIZE) -->
                @if($variantSizes->count() > 0)
                <div class="shopee-variant-row">
                    <div class="shopee-variant-label">Size</div>
                    <div class="shopee-variant-options" id="size-options-container">
                        @foreach($variantSizes as $sz)
                            @php
                                $isSizeActive = ($sz === $initialSize);
                            @endphp
                            <button 
                                type="button" 
                                class="shopee-size-option {{ $isSizeActive ? 'active' : '' }}" 
                                data-size="{{ $sz }}"
                                onclick="selectProductSize('{{ addslashes($sz) }}', this)"
                            >
                                {{ $sz }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Specifications -->
            <div class="specs-grid">
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-ruler"></i> Kích thước</div>
                    <div class="spec-value" id="spec-size-value">{{ $product->size ?: ($product->available_sizes[0] ?? 'Tiêu chuẩn') }}</div>
                </div>
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-palette"></i> Màu sắc</div>
                    <div class="spec-value" id="spec-color-value">{{ $product->color ?: ($product->available_colors[0] ?? 'Tự nhiên') }}</div>
                </div>
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-feather"></i> Chất liệu</div>
                    <div class="spec-value">{{ $product->material ?? 'Bông PP đàn hồi 4 chiều, vải mềm cao cấp' }}</div>
                </div>
            </div>

            <!-- Stock status & Quantity -->
            <div class="stock-status-row">
                <div id="stock-pill-container">
                    @if($initialStock > 0)
                        <span class="stock-pill in-stock" id="product-stock-pill">
                            <i class="fa-solid fa-circle-check"></i> Còn <strong id="product-stock-qty">{{ $initialStock }}</strong> sản phẩm
                        </span>
                    @else
                        <span class="stock-pill out-stock" id="product-stock-pill">
                            <i class="fa-solid fa-circle-xmark"></i> Tạm hết hàng
                        </span>
                    @endif
                </div>

                <!-- Quantity Selector -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-muted);">Số lượng:</span>
                    <div class="qty-selector {{ $initialStock <= 0 ? 'opacity-50 pointer-events-none' : '' }}" id="qty-selector-wrap">
                        <button type="button" class="qty-btn" id="qty-btn-minus" onclick="changeQuantity(-1)" {{ $initialStock <= 0 ? 'disabled' : '' }}>-</button>
                        <input type="number" id="detail-quantity" class="qty-input" value="{{ $initialStock > 0 ? 1 : 0 }}" min="{{ $initialStock > 0 ? 1 : 0 }}" max="{{ $initialStock }}" readonly>
                        <button type="button" class="qty-btn" id="qty-btn-plus" onclick="changeQuantity(1)" {{ $initialStock <= 0 ? 'disabled' : '' }}>+</button>
                    </div>
                </div>
            </div>

            <!-- Actions Buttons -->
            <div class="action-buttons-group" id="action-buttons-wrap">
                @if($initialStock > 0)
                    <button type="button" class="btn-add-cart-main" id="btn-add-cart" onclick="handleAddToCart()">
                        <i class="fa-solid fa-bag-shopping"></i> Thêm Vào Giỏ
                    </button>
                    <button type="button" class="btn-buy-now" id="btn-buy-now" onclick="handleBuyNow()">
                        <i class="fa-solid fa-bolt"></i> Mua Ngay
                    </button>
                @else
                    <button type="button" class="btn-add-cart-main opacity-60 cursor-not-allowed" id="btn-add-cart" style="background: #786B61;" onclick="handleAddToCart()">
                        <i class="fa-solid fa-circle-xmark"></i> Tạm Hết Hàng
                    </button>
                    <button type="button" class="btn-buy-now opacity-60 cursor-not-allowed" id="btn-buy-now" style="background: #A8988A;" onclick="handleBuyNow()">
                        <i class="fa-solid fa-ban"></i> Hết Hàng
                    </button>
                @endif
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
                        $relDiscountPct = ($relSale && $rel->price > 0) ? round((($rel->price - $rel->sale_price) / $rel->price) * 100) : 0;
                    @endphp
                    <div class="product-card">
                        <div class="product-card-img-wrap">
                            @if($relSale && $relDiscountPct > 0)
                                <span class="card-badge-sale">-{{ $relDiscountPct }}%</span>
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

<!-- SHOPEE-STYLE PRODUCT IMAGE DETAIL MODAL (LIGHTBOX) -->
<div id="product-image-modal" class="product-image-modal-overlay" onclick="handleModalBackdropClick(event)">
    <div class="product-image-modal-container" onclick="event.stopPropagation()">
        <!-- Left: Large Image & Controls -->
        <div class="modal-gallery-viewer">
            <div class="modal-counter-badge" id="modal-counter-badge">1 / {{ $modalGalleryItems->count() }}</div>
            
            <button type="button" class="modal-nav-btn prev" onclick="navigateModalImage(-1)" title="Ảnh trước (Phím mũi tên trái)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="modal-large-img-wrap">
                <img id="modal-large-img" src="{{ $initialImg }}" alt="{{ $product->name }}" class="modal-large-img" onerror="this.src='{{ $primaryUrl }}'">
            </div>

            <button type="button" class="modal-nav-btn next" onclick="navigateModalImage(1)" title="Ảnh kế tiếp (Phím mũi tên phải)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="modal-active-label" id="modal-active-label">Ảnh chi tiết</div>
        </div>

        <!-- Right: Product Info & Thumbs Grid -->
        <div class="modal-gallery-sidebar">
            <div class="modal-sidebar-header">
                <span class="modal-cat-tag">
                    <i class="fa-solid fa-paw"></i> {{ $product->category->name ?? 'Mật Ngọt Bear' }}
                </span>
                <button type="button" class="modal-close-btn" onclick="closeImageModal()" title="Đóng (Phím ESC)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-product-title">{{ $product->name }}</div>

            <div class="modal-price-box">
                <span class="modal-price-current" id="modal-price-current">{{ number_format($initialEffectivePrice, 0, ',', '.') }} đ</span>
                @if($initialIsOnSale && $initialPrice > $initialEffectivePrice)
                    <span class="modal-price-old">{{ number_format($initialPrice, 0, ',', '.') }} đ</span>
                @endif
            </div>

            <div class="modal-thumbs-section-title">
                <i class="fa-solid fa-images" style="color: var(--honey-dark);"></i>
                Tất Cả Hình Ảnh & Phân Loại ({{ $modalGalleryItems->count() }})
            </div>

            <div class="modal-thumbs-grid" id="modal-thumbs-grid">
                @foreach($modalGalleryItems as $idx => $mItem)
                    <div class="modal-thumb-box {{ $mItem['url'] === $initialImg ? 'active' : '' }}" 
                         data-idx="{{ $idx }}"
                         data-url="{{ $mItem['url'] }}"
                         data-label="{{ $mItem['label'] }}"
                         onclick="selectModalThumb({{ $idx }})"
                         title="{{ $mItem['label'] }}">
                        <img src="{{ $mItem['url'] }}" alt="{{ $mItem['label'] }}" onerror="this.src='{{ $primaryUrl }}'">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let maxStock = {{ (int) ($initialStock ?? $product->stock_quantity) }};
    const primaryImageUrl = "{{ $primaryUrl }}";
    const productVariants = @json($product->variants);
    const modalGalleryItems = @json($modalGalleryItems->values());
    let selectedSize = "{{ addslashes($initialSize) }}";
    let selectedColor = "{{ addslashes($initialColor) }}";
    let activeImageUrl = "{{ $initialImg }}";
    let currentModalIndex = 0;

    function openImageModal(targetUrl = null) {
        const modal = document.getElementById('product-image-modal');
        if (!modal) return;

        // Tìm index của ảnh được chỉ định hoặc ảnh đang hiển thị ngoài trang chính (activeImageUrl)
        const checkUrl = targetUrl || activeImageUrl;
        let foundIdx = modalGalleryItems.findIndex(item => {
            if (!item.url || !checkUrl) return false;
            return item.url === checkUrl || 
                   item.url.endsWith(checkUrl.replace(/^[a-z]+:\/\/[^/]+/i, '')) ||
                   checkUrl.endsWith(item.url.replace(/^[a-z]+:\/\/[^/]+/i, ''));
        });
        if (foundIdx === -1) foundIdx = 0;

        currentModalIndex = foundIdx;
        updateModalDisplay();

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('product-image-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleModalBackdropClick(e) {
        if (e.target.id === 'product-image-modal') {
            closeImageModal();
        }
    }

    function selectModalThumb(idx) {
        if (idx < 0 || idx >= modalGalleryItems.length) return;
        currentModalIndex = idx;
        updateModalDisplay();
    }

    function navigateModalImage(direction) {
        if (!modalGalleryItems || modalGalleryItems.length <= 1) return;
        currentModalIndex += direction;
        if (currentModalIndex < 0) {
            currentModalIndex = modalGalleryItems.length - 1;
        } else if (currentModalIndex >= modalGalleryItems.length) {
            currentModalIndex = 0;
        }
        updateModalDisplay();
    }

    function updateModalDisplay() {
        if (!modalGalleryItems || modalGalleryItems.length === 0) return;
        const currentItem = modalGalleryItems[currentModalIndex];
        if (!currentItem) return;

        const largeImg = document.getElementById('modal-large-img');
        const counterBadge = document.getElementById('modal-counter-badge');
        const activeLabel = document.getElementById('modal-active-label');

        if (largeImg) {
            largeImg.style.opacity = '0.3';
            largeImg.src = currentItem.url;
            largeImg.onload = () => { largeImg.style.opacity = '1'; };
            setTimeout(() => { largeImg.style.opacity = '1'; }, 80);
        }

        if (counterBadge) {
            counterBadge.innerText = `${currentModalIndex + 1} / ${modalGalleryItems.length}`;
        }

        if (activeLabel) {
            activeLabel.innerText = currentItem.label || 'Ảnh chi tiết';
        }

        document.querySelectorAll('.modal-thumb-box').forEach((box, i) => {
            if (i === currentModalIndex) {
                box.classList.add('active');
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                box.classList.remove('active');
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('product-image-modal');
        if (!modal || !modal.classList.contains('active')) return;

        if (e.key === 'Escape') {
            closeImageModal();
        } else if (e.key === 'ArrowLeft') {
            navigateModalImage(-1);
        } else if (e.key === 'ArrowRight') {
            navigateModalImage(1);
        }
    });

    function switchMainImage(url, thumbEl) {
        if (!url) return;
        const mainImg = document.getElementById('main-preview-img');
        if (mainImg) mainImg.src = url;
        activeImageUrl = url;
        document.querySelectorAll('.gallery-thumb-item').forEach(el => el.classList.remove('active'));
        if (thumbEl) thumbEl.classList.add('active');
    }

    function syncGalleryThumbnail(url) {
        if (!url) return;
        document.querySelectorAll('.gallery-thumb-item').forEach(el => {
            const itemUrl = el.getAttribute('data-img-url') || (el.querySelector('img') ? el.querySelector('img').src : '');
            if (itemUrl === url || (itemUrl && url && (itemUrl.endsWith(url.replace(/^[a-z]+:\/\/[^/]+/i, '')) || url.endsWith(itemUrl.replace(/^[a-z]+:\/\/[^/]+/i, ''))))) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    function changeQuantity(delta) {
        const input = document.getElementById('detail-quantity');
        let current = parseInt(input.value) || 1;
        let next = current + delta;
        if (next < 1) next = 1;
        if (maxStock > 0 && next > maxStock) {
            next = maxStock;
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'warning',
                    title: `Số lượng tối đa trong kho là ${maxStock} em gấu.`
                });
            } else {
                alert(`Số lượng tối đa trong kho là ${maxStock} em gấu.`);
            }
        }
        input.value = next;
    }

    // Bộ đếm thời gian đếm ngược kết thúc khuyến mãi
    let countdownInterval = null;

    function startCountdown(seconds) {
        if (countdownInterval) clearInterval(countdownInterval);
        let secLeft = Math.max(0, parseInt(seconds) || 0);
        renderCountdown(secLeft);

        countdownInterval = setInterval(() => {
            secLeft--;
            if (secLeft <= 0) {
                clearInterval(countdownInterval);
                matchVariantAndUpdate();
            } else {
                renderCountdown(secLeft);
            }
        }, 1000);
    }

    function renderCountdown(secLeft) {
        const hours = Math.floor(secLeft / 3600);
        const mins = Math.floor((secLeft % 3600) / 60);
        const secs = secLeft % 60;

        const elH = document.getElementById('timer-box-h');
        const elM = document.getElementById('timer-box-m');
        const elS = document.getElementById('timer-box-s');

        if (elH) elH.innerText = String(hours).padStart(2, '0');
        if (elM) elM.innerText = String(mins).padStart(2, '0');
        if (elS) elS.innerText = String(secs).padStart(2, '0');
    }

    function formatUpcomingDateText(d) {
        if (!d) return 'BẮT ĐẦU SỚM';
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        const day = d.getDate();
        const month = d.getMonth() + 1;
        return `BẮT ĐẦU SAU ${h}:${m}, ${day} Thg ${month}`;
    }

    // Kích hoạt đồng hồ đếm ngược ngay khi tải trang nếu biến thể mặc định đang sale
    @if($initialIsOnSale && $initialRemainingSec > 0)
        startCountdown({{ $initialRemainingSec }});
    @endif

    function previewVariantColorBtn(btn) {
        if (!btn) return;
        const imgUrl = btn.getAttribute('data-img');
        if (imgUrl) previewVariantImage(imgUrl);
    }

    function previewVariantImage(url) {
        if (!url) return;
        const mainImg = document.getElementById('main-preview-img');
        if (mainImg) mainImg.src = url;
    }

    function restoreSelectedImage() {
        const mainImg = document.getElementById('main-preview-img');
        if (mainImg && activeImageUrl) {
            mainImg.src = activeImageUrl;
        }
    }

    function selectProductColor(color, el) {
        selectedColor = color;

        document.querySelectorAll('#color-options-container .shopee-color-option').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');

        // Cập nhật spec hiển thị
        const specColor = document.getElementById('spec-color-value');
        if (specColor) specColor.innerText = color;

        matchVariantAndUpdate();
    }

    function selectProductSize(size, el) {
        selectedSize = size;
        document.querySelectorAll('#size-options-container .shopee-size-option').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');

        // Cập nhật spec hiển thị
        const specSize = document.getElementById('spec-size-value');
        if (specSize) specSize.innerText = size;

        matchVariantAndUpdate();
    }

    function matchVariantAndUpdate() {
        if (!productVariants || productVariants.length === 0) return;

        // Tìm variant khớp cả size và color
        let matched = productVariants.find(v => v.size === selectedSize && v.color === selectedColor);
        if (!matched) {
            matched = productVariants.find(v => v.color === selectedColor && (!v.size || v.size === selectedSize)) 
                   || productVariants.find(v => v.color === selectedColor) 
                   || productVariants.find(v => v.size === selectedSize) 
                   || productVariants[0];
        }

        if (matched) {
            if (matched.size && !selectedSize) selectedSize = matched.size;
            if (matched.color && !selectedColor) selectedColor = matched.color;

            // 1. TỰ ĐỘNG THAY ĐỔI RA ĐÚNG HÌNH ẢNH CỦA SẢN PHẨM CON ĐÓ LÊN LÀM ẢNH CHÍNH
            const matchedImg = matched.image_url || primaryImageUrl;
            if (matchedImg) {
                const mainImg = document.getElementById('main-preview-img');
                if (mainImg) {
                    mainImg.src = matchedImg;
                }
                activeImageUrl = matchedImg;
                syncGalleryThumbnail(matchedImg);
            }

            // 2. TỰ ĐỘNG CẬP NHẬT CẢ ẢNH NHỎ BÊN CẠNH CHỖ MÀU SẮC (PHÂN LOẠI) KHỚP VỚI CSDL SẢN PHẨM CON
            const colorButtons = document.querySelectorAll('#color-options-container .shopee-color-option');
            colorButtons.forEach(btn => {
                const btnColor = btn.getAttribute('data-color');
                // Tìm biến thể sản phẩm con theo màu này và size đang chọn
                let colorVar = productVariants.find(v => v.color === btnColor && v.size === selectedSize);
                if (!colorVar) {
                    // Nếu không có size hiện tại thì lấy theo màu
                    colorVar = productVariants.find(v => v.color === btnColor);
                }
                const colorImg = (colorVar && colorVar.image_url) ? colorVar.image_url : primaryImageUrl;
                
                btn.setAttribute('data-img', colorImg);
                const thumbImg = btn.querySelector('.shopee-color-thumb');
                if (thumbImg && colorImg) {
                    thumbImg.src = colorImg;
                }
            });

            // Cập nhật spec hiển thị
            const specSize = document.getElementById('spec-size-value');
            if (specSize && matched.size) specSize.innerText = matched.size;
            const specColor = document.getElementById('spec-color-value');
            if (specColor && matched.color) specColor.innerText = matched.color;
            const priceCurrentEl = document.getElementById('detail-price-current');
            const priceOldEl = document.getElementById('detail-price-old');
            const saleBadgeEl = document.getElementById('detail-sale-badge');
            const countdownBannerEl = document.getElementById('sale-countdown-banner');
            const upcomingBarEl = document.getElementById('upcoming-sale-bar');
            const priceBoxEl = document.getElementById('detail-price-box');

            const regularPrice = Number(matched.price);
            const salePrice = matched.sale_price ? Number(matched.sale_price) : null;
            const now = new Date();
            const startAt = matched.sale_start_at ? new Date(matched.sale_start_at) : null;
            const endAt = matched.sale_end_at ? new Date(matched.sale_end_at) : null;

            // Xác định trạng thái khuyến mãi của từng sản phẩm con (variant)
            let isOnSale = false;
            let isUpcoming = false;
            let discountPercent = 0;
            let remainingSeconds = 0;

            if (salePrice && salePrice > 0 && salePrice < regularPrice) {
                // 5. CÔNG THỨC TÍNH % TIẾT KIỆM: round(((price - sale_price) / price) * 100)
                discountPercent = Math.round(((regularPrice - salePrice) / regularPrice) * 100);

                if (startAt && now < startAt) {
                    // 2. KHUYẾN MÃI SẮP DIỄN RA TRONG TƯƠNG LAI
                    isUpcoming = true;
                } else if (endAt && now > endAt) {
                    // 3. KHUYẾN MÃI ĐÃ HẾT HẠN TRONG QUÁ KHỨ -> Chỉ hiển thị mỗi giá thường
                    isOnSale = false;
                } else {
                    // 1. ĐANG TRONG THỜI GIAN GIẢM GIÁ (ACTIVE)
                    isOnSale = true;
                    if (endAt) {
                        remainingSeconds = Math.max(0, Math.floor((endAt - now) / 1000));
                    }
                }
            }

            if (isOnSale) {
                // 1. NẾU ĐANG TRONG THỜI GIAN GIẢM: Hiển thị bộ thời gian chạy ngược (Ảnh 2)
                if (countdownBannerEl) countdownBannerEl.style.display = 'flex';
                if (priceBoxEl) priceBoxEl.classList.add('has-countdown');
                if (upcomingBarEl) upcomingBarEl.style.display = 'none';

                if (priceCurrentEl) {
                    priceCurrentEl.innerText = salePrice.toLocaleString('vi-VN') + ' đ';
                    priceCurrentEl.style.color = '#D32F2F';
                }
                if (priceOldEl) {
                    priceOldEl.innerText = regularPrice.toLocaleString('vi-VN') + ' đ';
                    priceOldEl.style.display = 'inline';
                }
                if (saleBadgeEl) {
                    saleBadgeEl.innerText = `-${discountPercent}% TIẾT KIỆM`;
                    saleBadgeEl.style.display = 'inline-flex';
                }

                // Chạy đếm ngược đến lúc kết thúc
                startCountdown(remainingSeconds);

            } else if (isUpcoming) {
                // 2. NẾU KHUYẾN MÃI SẮP DIỄN RA: Dưới giá tiền có chữ "Flash sale ... % bắt đầu từ..." (Ảnh 3)
                if (countdownInterval) clearInterval(countdownInterval);
                if (countdownBannerEl) countdownBannerEl.style.display = 'none';
                if (priceBoxEl) priceBoxEl.classList.remove('has-countdown');
                
                if (upcomingBarEl) {
                    upcomingBarEl.style.display = 'flex';
                    const timeEl = document.getElementById('upcoming-flash-time');
                    const discEl = document.getElementById('upcoming-flash-discount');
                    if (timeEl) timeEl.innerText = formatUpcomingDateText(startAt);
                    if (discEl) discEl.innerText = `Giảm ${discountPercent}%`;
                }

                if (priceCurrentEl) {
                    priceCurrentEl.innerText = regularPrice.toLocaleString('vi-VN') + ' đ';
                    priceCurrentEl.style.color = 'var(--primary-dark)';
                }
                if (priceOldEl) priceOldEl.style.display = 'none';
                if (saleBadgeEl) saleBadgeEl.style.display = 'none';

            } else {
                // 3. NẾU KHUYẾN MÃI ĐÃ KẾT THÚC HOẶC KHÔNG CÓ KHUYẾN MÃI: Sẽ hiển thị mỗi giá thôi
                if (countdownInterval) clearInterval(countdownInterval);
                if (countdownBannerEl) countdownBannerEl.style.display = 'none';
                if (priceBoxEl) priceBoxEl.classList.remove('has-countdown');
                if (upcomingBarEl) upcomingBarEl.style.display = 'none';

                if (priceCurrentEl) {
                    priceCurrentEl.innerText = regularPrice.toLocaleString('vi-VN') + ' đ';
                    priceCurrentEl.style.color = 'var(--primary-dark)';
                }
                if (priceOldEl) priceOldEl.style.display = 'none';
                if (saleBadgeEl) saleBadgeEl.style.display = 'none';
            }

            // Cập nhật số lượng tồn kho theo sản phẩm con (variant)
            if (matched.stock_quantity !== undefined && matched.stock_quantity !== null) {
                maxStock = parseInt(matched.stock_quantity) || 0;
                
                const stockPillContainer = document.getElementById('stock-pill-container');
                if (stockPillContainer) {
                    if (maxStock > 0) {
                        stockPillContainer.innerHTML = `<span class="stock-pill in-stock" id="product-stock-pill"><i class="fa-solid fa-circle-check"></i> Còn <strong>${maxStock}</strong> sản phẩm</span>`;
                    } else {
                        stockPillContainer.innerHTML = `<span class="stock-pill out-stock" id="product-stock-pill"><i class="fa-solid fa-circle-xmark"></i> Tạm hết hàng</span>`;
                    }
                }

                // Cập nhật ô input số lượng
                const qtyInput = document.getElementById('detail-quantity');
                const qtyWrap = document.getElementById('qty-selector-wrap');
                const btnMinus = document.getElementById('qty-btn-minus');
                const btnPlus = document.getElementById('qty-btn-plus');

                if (qtyInput) {
                    qtyInput.max = maxStock;
                    if (maxStock <= 0) {
                        qtyInput.value = 0;
                        qtyInput.min = 0;
                        if (qtyWrap) qtyWrap.classList.add('opacity-50', 'pointer-events-none');
                        if (btnMinus) btnMinus.disabled = true;
                        if (btnPlus) btnPlus.disabled = true;
                    } else {
                        qtyInput.min = 1;
                        if (parseInt(qtyInput.value) <= 0 || parseInt(qtyInput.value) > maxStock) {
                            qtyInput.value = 1;
                        }
                        if (qtyWrap) qtyWrap.classList.remove('opacity-50', 'pointer-events-none');
                        if (btnMinus) btnMinus.disabled = false;
                        if (btnPlus) btnPlus.disabled = false;
                    }
                }

                // Cập nhật các nút Thêm Vào Giỏ / Mua Ngay
                const btnAddCart = document.getElementById('btn-add-cart');
                const btnBuyNow = document.getElementById('btn-buy-now');
                if (btnAddCart && btnBuyNow) {
                    if (maxStock > 0) {
                        btnAddCart.className = 'btn-add-cart-main';
                        btnAddCart.style.background = '';
                        btnAddCart.innerHTML = '<i class="fa-solid fa-bag-shopping"></i> Thêm Vào Giỏ';
                        btnBuyNow.className = 'btn-buy-now';
                        btnBuyNow.style.background = '';
                        btnBuyNow.innerHTML = '<i class="fa-solid fa-bolt"></i> Mua Ngay';
                    } else {
                        btnAddCart.className = 'btn-add-cart-main opacity-60 cursor-not-allowed';
                        btnAddCart.style.background = '#786B61';
                        btnAddCart.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Tạm Hết Hàng';
                        btnBuyNow.className = 'btn-buy-now opacity-60 cursor-not-allowed';
                        btnBuyNow.style.background = '#A8988A';
                        btnBuyNow.innerHTML = '<i class="fa-solid fa-ban"></i> Hết Hàng';
                    }
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        matchVariantAndUpdate();
    });

    function handleAddToCart() {
        if (!window.isCustomerAuthenticated) {
            openAuthModal(window.location.href, 'Đăng nhập để thêm vào giỏ hàng', 'Vui lòng đăng nhập tài khoản Mật Ngọt Bear để thêm sản phẩm vào giỏ hàng của bạn bạn nhé!');
            return;
        }
        if (maxStock <= 0) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Sản phẩm hiện đang tạm hết hàng.' });
            } else {
                alert('Sản phẩm hiện đang tạm hết hàng.');
            }
            return;
        }
        const qty = parseInt(document.getElementById('detail-quantity').value) || 1;
        addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', qty);
    }

    function handleBuyNow() {
        if (!window.isCustomerAuthenticated) {
            const qty = parseInt(document.getElementById('detail-quantity')?.value) || 1;
            const targetCheckoutUrl = "{{ route('customer.checkout.index') }}?product_id={{ $product->id }}&quantity=" + qty;
            openAuthModal(targetCheckoutUrl, 'Đăng nhập để Mua ngay', 'Vui lòng đăng nhập hoặc đăng ký tài khoản Mật Ngọt Bear để tiến hành mua hàng ngay bạn nhé!');
            return;
        }
        if (maxStock <= 0) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Sản phẩm hiện đang tạm hết hàng.' });
            } else {
                alert('Sản phẩm hiện đang tạm hết hàng.');
            }
            return;
        }
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

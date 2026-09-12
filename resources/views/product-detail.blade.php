@extends('layouts.customer')

@section('title', $product->name . ' - Mật Ngọt Bear')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/product-detail.css') }}">
@endsection

@section('content')

@php
    $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $primaryUrl = $primaryImg ? $primaryImg->image_url : 'https://placehold.co/800x800/f5e6ca/7c4a2d?text=' . urlencode($product->name);

    // 1. Chỉ hiển thị ảnh trong "Bộ ảnh chung" ($product->images) vì bộ ảnh chung đã gồm cả ảnh của từng sản phẩm con
    $galleryList = collect();
    $galleryFileHashes = [];

    foreach ($product->images as $pImg) {
        if (!empty($pImg->image_url) && !$galleryList->contains($pImg->image_url)) {
            $galleryList->push($pImg->image_url);
            $localPath = public_path(ltrim(parse_url($pImg->image_url, PHP_URL_PATH) ?? '', '/'));
            if (file_exists($localPath)) {
                $galleryFileHashes[$pImg->image_url] = md5_file($localPath);
            }
        }
    }

    // Fallback nếu sản phẩm chưa có ảnh trong Bộ ảnh chung
    if ($galleryList->isEmpty()) {
        foreach ($product->variants as $v) {
            if (!empty($v->image_url) && !$galleryList->contains($v->image_url)) {
                $galleryList->push($v->image_url);
            }
        }
    }
    if ($galleryList->isEmpty()) {
        $galleryList->push($primaryUrl);
    }

    // Xử lý biến thể từ CSDL & Đồng bộ ảnh biến thể với Bộ ảnh chung
    $variants = $product->variants;
    foreach ($variants as $v) {
        $matchedUrl = null;
        if ($galleryList->contains($v->image_url)) {
            $matchedUrl = $v->image_url;
        } else if (!empty($v->image_url)) {
            $vPath = public_path(ltrim(parse_url($v->image_url, PHP_URL_PATH) ?? '', '/'));
            if (file_exists($vPath)) {
                $vHash = md5_file($vPath);
                foreach ($galleryFileHashes as $gUrl => $gHash) {
                    if ($vHash === $gHash) {
                        $matchedUrl = $gUrl;
                        break;
                    }
                }
            }
        }
        if ($matchedUrl) {
            $v->image_url = $matchedUrl;
        }
    }

    $variantSizes = $variants->pluck('size')->filter()->unique()->values();
    if ($variantSizes->isEmpty() && !empty($product->available_sizes)) {
        $variantSizes = collect($product->available_sizes);
    }
    
    // Gom màu sắc kèm ảnh mẫu tương ứng
    $variantColors = $variants->filter(fn($v) => !empty($v->color))->unique('color')->values();
    if ($variantColors->isEmpty() && !empty($product->available_colors)) {
        $variantColors = collect($product->available_colors)->map(function($cl) use ($primaryUrl) {
            return (object)[
                'color' => $cl,
                'image_url' => $primaryUrl,
            ];
        });
    }

    $initialSize = null;
    $initialColor = null;
    $initialImg = $primaryUrl;
    $initialStock = (int) $product->stock_quantity;

    // Trạng thái giá ban đầu: lấy giá thấp nhất từ sản phẩm con hoặc giá cha
    $initialPrice = (float) $product->lowest_price;
    $initialSalePrice = $product->lowest_sale_price;
    $initialIsOnSale = !empty($initialSalePrice) && (float)$initialSalePrice < $initialPrice;
    $initialIsUpcoming = false;
    $initialDiscountPct = ($initialIsOnSale && $initialPrice > 0) ? round((($initialPrice - $initialSalePrice) / $initialPrice) * 100) : 0;
    $initialEffectivePrice = $initialIsOnSale ? (float)$initialSalePrice : $initialPrice;
    $initialRemainingSec = 0;

    // Chuẩn bị danh sách ảnh cho Lightbox Modal (chỉ từ bộ ảnh chung, không lặp ảnh)
    $modalGalleryItems = collect();
    foreach ($galleryList as $gUrl) {
        $matchedVar = $variants->firstWhere('image_url', $gUrl);
        $label = ($gUrl === $primaryUrl) ? 'Ảnh đại diện chính' : 'Ảnh sản phẩm';
        if ($matchedVar) {
            $varLabel = trim(($matchedVar->size ? $matchedVar->size : '') . ($matchedVar->size && $matchedVar->color ? ' - ' : '') . ($matchedVar->color ? $matchedVar->color : ''));
            if ($varLabel) {
                $label = 'Phân loại: ' . $varLabel;
            }
        }
        $modalGalleryItems->push([
            'url' => $gUrl,
            'label' => $label,
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
                         onclick="switchMainImage('{{ $gUrl }}', this);"
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
                            BẮT ĐẦU SỚM
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
                                $cVarObj = $variants->firstWhere('color', $cVar->color);
                                $thumbUrl = ($cVarObj && !empty($cVarObj->image_url)) 
                                            ? $cVarObj->image_url 
                                            : (!empty($cVar->image_url) ? $cVar->image_url : $primaryUrl);
                            @endphp
                            <button 
                                type="button" 
                                class="shopee-color-option" 
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
                            <button 
                                type="button" 
                                class="shopee-size-option" 
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
                    <div class="spec-value" id="spec-size-value">--</div>
                </div>
                <div class="spec-item">
                    <div class="spec-label"><i class="fa-solid fa-palette"></i> Màu sắc</div>
                    <div class="spec-value" id="spec-color-value">--</div>
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

                <!-- Right: Star & Media Filter Buttons -->
                <div class="review-filter-chips">
                    <button type="button" class="star-filter-chip active" data-filter="all" onclick="filterReviews('all', this)">
                        Tất Cả ({{ $product->reviews_count }})
                    </button>
                    <button type="button" class="star-filter-chip chip-has-images" data-filter="with_images" onclick="filterReviews('with_images', this)">
                        <i class="fa-solid fa-camera"></i> Có Hình Ảnh ({{ $withImagesCount ?? 0 }})
                    </button>
                    @for($star = 5; $star >= 1; $star--)
                        <button type="button" class="star-filter-chip" data-filter="{{ $star }}" onclick="filterReviews({{ $star }}, this)">
                            {{ $star }} Sao ({{ $ratingCounts[$star] ?? 0 }})
                        </button>
                    @endfor
                </div>
            </div>

            <!-- Review Items List -->
            <div class="review-items-list" id="reviewItemsList">
                @forelse($product->reviews as $review)
                    @php
                        $hasImages = !empty($review->images) && is_array($review->images) && count($review->images) > 0;
                    @endphp
                    <div class="review-item-card" data-rating="{{ $review->rating }}" data-has-images="{{ $hasImages ? '1' : '0' }}">
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

                        @if($hasImages)
                            <!-- Bộ sưu tập ảnh thực tế do khách hàng gửi kèm -->
                            <div class="review-images-gallery">
                                @php
                                    $reviewImgsUrls = array_map(fn($img) => asset($img), $review->images);
                                @endphp
                                @foreach($review->images as $imgIdx => $img)
                                    <div class="review-image-item" onclick='openReviewLightbox(@json($reviewImgsUrls), {{ $imgIdx }})' title="Nhấp xem ảnh lớn">
                                        <img src="{{ asset($img) }}" alt="Ảnh đánh giá từ khách hàng" class="review-image-thumb" loading="lazy">
                                        <div class="review-image-overlay">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                            <span>Xem ảnh</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

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
    let maxStock = {{ (int) $product->stock_quantity }};
    const productTotalStock = {{ (int) $product->stock_quantity }};
    const primaryImageUrl = "{{ $primaryUrl }}";
    const productBasePrice = {{ (float) $product->lowest_price }};
    const productBaseSalePrice = {{ $product->lowest_sale_price ? (float)$product->lowest_sale_price : 'null' }};
    const productVariants = @json($variants->values());
    const modalGalleryItems = @json($modalGalleryItems->values());
    const hasColorVariants = {{ $variantColors->count() > 0 ? 'true' : 'false' }};
    const hasSizeVariants = {{ $variantSizes->count() > 0 ? 'true' : 'false' }};
    let selectedSize = null;
    let selectedColor = null;
    let currentMatchedVariant = null;
    let activeImageUrl = "{{ $primaryUrl }}";
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
        const normUrl = (u) => {
            if (!u) return '';
            try {
                return new URL(u, window.location.origin).pathname.replace(/^\/+/, '');
            } catch(e) {
                return u.replace(/^[a-z]+:\/\/[^/]+/i, '').replace(/^\/+/, '');
            }
        };
        const targetNorm = normUrl(url);

        let matchedThumb = null;
        document.querySelectorAll('.gallery-thumb-item').forEach(el => {
            const itemUrl = el.getAttribute('data-img-url') || (el.querySelector('img') ? el.querySelector('img').src : '');
            const itemNorm = normUrl(itemUrl);
            if (itemNorm === targetNorm || (targetNorm && itemNorm && (targetNorm.endsWith(itemNorm) || itemNorm.endsWith(targetNorm)))) {
                el.classList.add('active');
                matchedThumb = el;
            } else {
                el.classList.remove('active');
            }
        });

        if (matchedThumb) {
            matchedThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
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
        if (!btn || btn.classList.contains('disabled')) return;
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

    // ==========================================
    // LOGIC LIÊN KẾT PHÂN LOẠI & SIZE ĂN KHỚP CSDL (SHOPEE)
    // ==========================================
    function selectProductColor(color, el) {
        if (el && el.classList.contains('disabled')) return;

        // Bấm 1 lần là chọn, bấm lại đúng màu đó lần 2 là bỏ chọn
        if (selectedColor === color) {
            selectedColor = null;
        } else {
            selectedColor = color;
        }

        updateVariantOptionsState('color');
        matchVariantAndUpdate();
    }

    function selectProductSize(size, el) {
        if (el && el.classList.contains('disabled')) return;

        // Bấm 1 lần là chọn, bấm lại đúng size đó lần 2 là bỏ chọn
        if (selectedSize === size) {
            selectedSize = null;
        } else {
            selectedSize = size;
        }

        updateVariantOptionsState('size');
        matchVariantAndUpdate();
    }

    function updateVariantOptionsState(changedFrom = 'color') {
        if (!productVariants || productVariants.length === 0) return;

        const activeVariants = productVariants.filter(v => v.status !== 'INACTIVE');
        const colorButtons = document.querySelectorAll('#color-options-container .shopee-color-option');
        const sizeButtons = document.querySelectorAll('#size-options-container .shopee-size-option');

        // TRƯỜNG HỢP 1: CẢ 2 ĐỀU ĐANG CHƯA CHỌN (TRẠNG THÁI GỐC BAN ĐẦU)
        if (!selectedColor && !selectedSize) {
            colorButtons.forEach(btn => {
                btn.classList.remove('active', 'disabled');
                btn.removeAttribute('disabled');
            });
            sizeButtons.forEach(btn => {
                btn.classList.remove('active', 'disabled');
                btn.removeAttribute('disabled');
            });
            return;
        }

        // TRƯỜNG HỢP 2: CHỈ CHỌN MÀU (CHƯA CHỌN SIZE)
        if (selectedColor && !selectedSize) {
            colorButtons.forEach(btn => {
                const btnColor = btn.getAttribute('data-color');
                btn.classList.remove('disabled');
                btn.removeAttribute('disabled');
                if (btnColor === selectedColor) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Tìm các size hợp lệ có trong CSDL của màu này
            const validSizes = activeVariants
                .filter(v => v.color === selectedColor)
                .map(v => v.size)
                .filter(Boolean);

            sizeButtons.forEach(btn => {
                const btnSize = btn.getAttribute('data-size');
                btn.classList.remove('active');
                if (validSizes.includes(btnSize)) {
                    btn.classList.remove('disabled');
                    btn.removeAttribute('disabled');
                } else {
                    btn.classList.add('disabled');
                    btn.setAttribute('disabled', 'disabled');
                }
            });
            return;
        }

        // TRƯỜNG HỢP 3: CHỈ CHỌN SIZE (CHƯA CHỌN MÀU)
        if (!selectedColor && selectedSize) {
            sizeButtons.forEach(btn => {
                const btnSize = btn.getAttribute('data-size');
                btn.classList.remove('disabled');
                btn.removeAttribute('disabled');
                if (btnSize === selectedSize) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Tìm các màu hợp lệ có trong CSDL của size này
            const validColors = activeVariants
                .filter(v => v.size === selectedSize)
                .map(v => v.color)
                .filter(Boolean);

            colorButtons.forEach(btn => {
                const btnColor = btn.getAttribute('data-color');
                btn.classList.remove('active');
                if (validColors.includes(btnColor)) {
                    btn.classList.remove('disabled');
                    btn.removeAttribute('disabled');
                } else {
                    btn.classList.add('disabled');
                    btn.setAttribute('disabled', 'disabled');
                }
            });
            return;
        }

        // TRƯỜNG HỢP 4: CHỌN CẢ MÀU VÀ SIZE
        if (selectedColor && selectedSize) {
            // Kiểm tra xem tổ hợp này có hợp lệ không trong CSDL
            const comboExists = activeVariants.some(v => v.color === selectedColor && v.size === selectedSize);
            if (!comboExists) {
                if (changedFrom === 'color') {
                    selectedSize = null;
                    updateVariantOptionsState('color');
                    return;
                } else {
                    selectedColor = null;
                    updateVariantOptionsState('size');
                    return;
                }
            }

            colorButtons.forEach(btn => {
                const btnColor = btn.getAttribute('data-color');
                if (btnColor === selectedColor) {
                    btn.classList.add('active');
                    btn.classList.remove('disabled');
                    btn.removeAttribute('disabled');
                } else {
                    btn.classList.remove('active');
                    const hasWithSelectedSize = activeVariants.some(v => v.color === btnColor && v.size === selectedSize);
                    if (hasWithSelectedSize) {
                        btn.classList.remove('disabled');
                        btn.removeAttribute('disabled');
                    } else {
                        btn.classList.add('disabled');
                        btn.setAttribute('disabled', 'disabled');
                    }
                }
            });

            sizeButtons.forEach(btn => {
                const btnSize = btn.getAttribute('data-size');
                if (btnSize === selectedSize) {
                    btn.classList.add('active');
                    btn.classList.remove('disabled');
                    btn.removeAttribute('disabled');
                } else {
                    btn.classList.remove('active');
                    const hasWithSelectedColor = activeVariants.some(v => v.size === btnSize && v.color === selectedColor);
                    if (hasWithSelectedColor) {
                        btn.classList.remove('disabled');
                        btn.removeAttribute('disabled');
                    } else {
                        btn.classList.add('disabled');
                        btn.setAttribute('disabled', 'disabled');
                    }
                }
            });
        }
    }

    function matchVariantAndUpdate() {
        const specSize = document.getElementById('spec-size-value');
        const specColor = document.getElementById('spec-color-value');
        const priceCurrentEl = document.getElementById('detail-price-current');
        const priceOldEl = document.getElementById('detail-price-old');
        const saleBadgeEl = document.getElementById('detail-sale-badge');
        const countdownBannerEl = document.getElementById('sale-countdown-banner');
        const upcomingBarEl = document.getElementById('upcoming-sale-bar');
        const priceBoxEl = document.getElementById('detail-price-box');
        const stockPillContainer = document.getElementById('stock-pill-container');
        const qtyInput = document.getElementById('detail-quantity');
        const qtyWrap = document.getElementById('qty-selector-wrap');
        const btnMinus = document.getElementById('qty-btn-minus');
        const btnPlus = document.getElementById('qty-btn-plus');
        const btnAddCart = document.getElementById('btn-add-cart');
        const btnBuyNow = document.getElementById('btn-buy-now');

        const activeVariants = (productVariants || []).filter(v => v.status !== 'INACTIVE');

        // Helper hiển thị giá tiền
        function renderPriceDisplay(regPrice, sPrice, discountPct, isSale, isUp, startAt, remSec) {
            if (isSale) {
                if (countdownBannerEl) countdownBannerEl.style.display = 'flex';
                if (priceBoxEl) priceBoxEl.classList.add('has-countdown');
                if (upcomingBarEl) upcomingBarEl.style.display = 'none';

                if (priceCurrentEl) {
                    priceCurrentEl.innerText = Number(sPrice).toLocaleString('vi-VN') + ' đ';
                    priceCurrentEl.style.color = '#D32F2F';
                }
                if (priceOldEl) {
                    priceOldEl.innerText = Number(regPrice).toLocaleString('vi-VN') + ' đ';
                    priceOldEl.style.display = 'inline';
                }
                if (saleBadgeEl) {
                    saleBadgeEl.innerText = `-${discountPct}% TIẾT KIỆM`;
                    saleBadgeEl.style.display = 'inline-flex';
                }
                startCountdown(remSec);
            } else if (isUp) {
                if (countdownInterval) clearInterval(countdownInterval);
                if (countdownBannerEl) countdownBannerEl.style.display = 'none';
                if (priceBoxEl) priceBoxEl.classList.remove('has-countdown');

                if (upcomingBarEl) {
                    upcomingBarEl.style.display = 'flex';
                    const timeEl = document.getElementById('upcoming-flash-time');
                    const discEl = document.getElementById('upcoming-flash-discount');
                    if (timeEl) timeEl.innerText = formatUpcomingDateText(startAt);
                    if (discEl) discEl.innerText = `Giảm ${discountPct}%`;
                }

                if (priceCurrentEl) {
                    priceCurrentEl.innerText = Number(regPrice).toLocaleString('vi-VN') + ' đ';
                    priceCurrentEl.style.color = 'var(--primary-dark)';
                }
                if (priceOldEl) priceOldEl.style.display = 'none';
                if (saleBadgeEl) saleBadgeEl.style.display = 'none';
            } else {
                if (countdownInterval) clearInterval(countdownInterval);
                if (countdownBannerEl) countdownBannerEl.style.display = 'none';
                if (priceBoxEl) priceBoxEl.classList.remove('has-countdown');
                if (upcomingBarEl) upcomingBarEl.style.display = 'none';

                if (sPrice && Number(sPrice) > 0 && Number(sPrice) < Number(regPrice)) {
                    if (priceCurrentEl) {
                        priceCurrentEl.innerText = Number(sPrice).toLocaleString('vi-VN') + ' đ';
                        priceCurrentEl.style.color = '#D32F2F';
                    }
                    if (priceOldEl) {
                        priceOldEl.innerText = Number(regPrice).toLocaleString('vi-VN') + ' đ';
                        priceOldEl.style.display = 'inline';
                    }
                    if (saleBadgeEl) {
                        saleBadgeEl.innerText = `-${discountPct}% TIẾT KIỆM`;
                        saleBadgeEl.style.display = 'inline-flex';
                    }
                } else {
                    if (priceCurrentEl) {
                        priceCurrentEl.innerText = Number(regPrice).toLocaleString('vi-VN') + ' đ';
                        priceCurrentEl.style.color = 'var(--primary-dark)';
                    }
                    if (priceOldEl) priceOldEl.style.display = 'none';
                    if (saleBadgeEl) saleBadgeEl.style.display = 'none';
                }
            }
        }

        // Helper cập nhật tồn kho & trạng thái các nút mua
        function renderStockAndButtons(stock) {
            maxStock = Math.max(0, parseInt(stock) || 0);
            if (stockPillContainer) {
                if (maxStock > 0) {
                    stockPillContainer.innerHTML = `<span class="stock-pill in-stock" id="product-stock-pill"><i class="fa-solid fa-circle-check"></i> Còn <strong>${maxStock}</strong> sản phẩm</span>`;
                } else {
                    stockPillContainer.innerHTML = `<span class="stock-pill out-stock" id="product-stock-pill"><i class="fa-solid fa-circle-xmark"></i> Tạm hết hàng</span>`;
                }
            }

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

        // TRƯỜNG HỢP 1: CẢ 2 ĐỀU ĐANG CHƯA CHỌN (TRẠNG THÁI GỐC BAN ĐẦU)
        if (!selectedColor && !selectedSize) {
            currentMatchedVariant = null;
            if (specSize) specSize.innerText = '--';
            if (specColor) specColor.innerText = '--';

            // Khôi phục ảnh đại diện chính ban đầu
            const mainImg = document.getElementById('main-preview-img');
            if (mainImg) mainImg.src = primaryImageUrl;
            activeImageUrl = primaryImageUrl;
            syncGalleryThumbnail(primaryImageUrl);

            // Khôi phục giá gốc / sale thấp nhất của sản phẩm cha
            const hasSale = (productBaseSalePrice && productBaseSalePrice < productBasePrice);
            const discountPct = (hasSale && productBasePrice > 0) ? Math.round(((productBasePrice - productBaseSalePrice) / productBasePrice) * 100) : 0;
            renderPriceDisplay(productBasePrice, productBaseSalePrice, discountPct, false, false, null, 0);

            // Tổng tồn kho toàn bộ sản phẩm
            renderStockAndButtons(productTotalStock);
            return;
        }

        // TRƯỜNG HỢP 2: CHỈ CÓ MÀU (CHƯA CHỌN SIZE)
        if (selectedColor && !selectedSize) {
            currentMatchedVariant = null;
            if (specColor) specColor.innerText = selectedColor;
            if (specSize) specSize.innerText = '--';

            // Đổi sang ảnh của sản phẩm con có màu này
            const colorVars = activeVariants.filter(v => v.color === selectedColor);
            const firstWithImg = colorVars.find(v => v.image_url);
            const newImg = firstWithImg ? firstWithImg.image_url : primaryImageUrl;
            if (newImg) {
                const mainImg = document.getElementById('main-preview-img');
                if (mainImg) mainImg.src = newImg;
                activeImageUrl = newImg;
                syncGalleryThumbnail(newImg);
            }

            // Giá thấp nhất của các biến thể màu này
            const prices = colorVars.map(v => Number(v.price)).filter(p => p > 0);
            const salePrices = colorVars.map(v => v.sale_price ? Number(v.sale_price) : null).filter(Boolean);
            const minPrice = prices.length > 0 ? Math.min(...prices) : productBasePrice;
            const minSalePrice = salePrices.length > 0 ? Math.min(...salePrices) : null;
            const hasSale = (minSalePrice && minSalePrice < minPrice);
            const discountPct = (hasSale && minPrice > 0) ? Math.round(((minPrice - minSalePrice) / minPrice) * 100) : 0;
            renderPriceDisplay(minPrice, minSalePrice, discountPct, false, false, null, 0);

            // Tổng tồn kho màu này
            const sumStock = colorVars.reduce((acc, v) => acc + (parseInt(v.stock_quantity) || 0), 0);
            renderStockAndButtons(sumStock);
            return;
        }

        // TRƯỜNG HỢP 3: CHỈ CÓ SIZE (CHƯA CHỌN MÀU)
        if (!selectedColor && selectedSize) {
            currentMatchedVariant = null;
            if (specSize) specSize.innerText = selectedSize;
            if (specColor) specColor.innerText = '--';

            // Đổi ảnh nếu có ảnh theo size
            const sizeVars = activeVariants.filter(v => v.size === selectedSize);
            const firstWithImg = sizeVars.find(v => v.image_url);
            if (firstWithImg && firstWithImg.image_url) {
                const mainImg = document.getElementById('main-preview-img');
                if (mainImg) mainImg.src = firstWithImg.image_url;
                activeImageUrl = firstWithImg.image_url;
                syncGalleryThumbnail(firstWithImg.image_url);
            }

            // Giá thấp nhất của các biến thể size này
            const prices = sizeVars.map(v => Number(v.price)).filter(p => p > 0);
            const salePrices = sizeVars.map(v => v.sale_price ? Number(v.sale_price) : null).filter(Boolean);
            const minPrice = prices.length > 0 ? Math.min(...prices) : productBasePrice;
            const minSalePrice = salePrices.length > 0 ? Math.min(...salePrices) : null;
            const hasSale = (minSalePrice && minSalePrice < minPrice);
            const discountPct = (hasSale && minPrice > 0) ? Math.round(((minPrice - minSalePrice) / minPrice) * 100) : 0;
            renderPriceDisplay(minPrice, minSalePrice, discountPct, false, false, null, 0);

            // Tổng tồn kho size này
            const sumStock = sizeVars.reduce((acc, v) => acc + (parseInt(v.stock_quantity) || 0), 0);
            renderStockAndButtons(sumStock);
            return;
        }

        // TRƯỜNG HỢP 4: CÓ CẢ MÀU VÀ SIZE (KHỚP CHÍNH XÁC BIẾN THỂ)
        const matched = activeVariants.find(v => v.color === selectedColor && v.size === selectedSize);
        currentMatchedVariant = matched || null;
        if (matched) {
            if (specSize) specSize.innerText = matched.size;
            if (specColor) specColor.innerText = matched.color;

            // Ảnh chính
            const matchedImg = matched.image_url || primaryImageUrl;
            if (matchedImg) {
                const mainImg = document.getElementById('main-preview-img');
                if (mainImg) mainImg.src = matchedImg;
                activeImageUrl = matchedImg;
                syncGalleryThumbnail(matchedImg);
            }

            // Khuyến mãi của biến thể con này
            const regularPrice = Number(matched.price);
            const salePrice = matched.sale_price ? Number(matched.sale_price) : null;
            const now = new Date();
            const startAt = matched.sale_start_at ? new Date(matched.sale_start_at) : null;
            const endAt = matched.sale_end_at ? new Date(matched.sale_end_at) : null;

            let isOnSale = false;
            let isUpcoming = false;
            let discountPercent = 0;
            let remainingSeconds = 0;

            if (salePrice && salePrice > 0 && salePrice < regularPrice) {
                discountPercent = Math.round(((regularPrice - salePrice) / regularPrice) * 100);
                if (startAt && now < startAt) {
                    isUpcoming = true;
                } else if (endAt && now > endAt) {
                    isOnSale = false;
                } else {
                    isOnSale = true;
                    if (endAt) {
                        remainingSeconds = Math.max(0, Math.floor((endAt - now) / 1000));
                    }
                }
            }

            renderPriceDisplay(regularPrice, salePrice, discountPercent, isOnSale, isUpcoming, startAt, remainingSeconds);
            renderStockAndButtons(matched.stock_quantity);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateVariantOptionsState();
        matchVariantAndUpdate();
    });

    function handleAddToCart() {
        if (!window.isCustomerAuthenticated) {
            openAuthModal(window.location.href, 'Đăng nhập để thêm vào giỏ hàng', 'Vui lòng đăng nhập tài khoản Mật Ngọt Bear để thêm sản phẩm vào giỏ hàng của bạn bạn nhé!');
            return;
        }
        if (hasColorVariants && !selectedColor) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Vui lòng chọn phân loại màu sắc!' });
            } else {
                alert('Vui lòng chọn phân loại màu sắc!');
            }
            return;
        }
        if (hasSizeVariants && !selectedSize) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Vui lòng chọn kích thước (size)!' });
            } else {
                alert('Vui lòng chọn kích thước (size)!');
            }
            return;
        }
        if (maxStock <= 0) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Sản phẩm hoặc phân loại này hiện đang tạm hết hàng.' });
            } else {
                alert('Sản phẩm hoặc phân loại này hiện đang tạm hết hàng.');
            }
            return;
        }
        const qty = parseInt(document.getElementById('detail-quantity').value) || 1;
        const variantId = currentMatchedVariant ? currentMatchedVariant.id : null;
        addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', qty, false, variantId);
    }

    function handleBuyNow() {
        if (!window.isCustomerAuthenticated) {
            const qty = parseInt(document.getElementById('detail-quantity')?.value) || 1;
            const variantId = currentMatchedVariant ? currentMatchedVariant.id : null;
            let targetCheckoutUrl = "{{ route('customer.checkout.index') }}?product_id={{ $product->id }}&quantity=" + qty;
            if (variantId) {
                targetCheckoutUrl += "&variant_id=" + variantId;
            }
            openAuthModal(targetCheckoutUrl, 'Đăng nhập để Mua ngay', 'Vui lòng đăng nhập hoặc đăng ký tài khoản Mật Ngọt Bear để tiến hành mua hàng ngay bạn nhé!');
            return;
        }
        if (hasColorVariants && !selectedColor) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Vui lòng chọn phân loại màu sắc!' });
            } else {
                alert('Vui lòng chọn phân loại màu sắc!');
            }
            return;
        }
        if (hasSizeVariants && !selectedSize) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Vui lòng chọn kích thước (size)!' });
            } else {
                alert('Vui lòng chọn kích thước (size)!');
            }
            return;
        }
        if (maxStock <= 0) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'warning', title: 'Sản phẩm hoặc phân loại này hiện đang tạm hết hàng.' });
            } else {
                alert('Sản phẩm hoặc phân loại này hiện đang tạm hết hàng.');
            }
            return;
        }
        const qty = parseInt(document.getElementById('detail-quantity').value) || 1;
        const variantId = currentMatchedVariant ? currentMatchedVariant.id : null;
        addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', qty, 'checkout', variantId);
    }

    // Modal Lightbox xem ảnh review phóng to
    let currentReviewLightboxList = [];
    let currentReviewLightboxIdx = 0;

    function openReviewLightbox(imgUrls, initialIdx = 0) {
        if (!imgUrls || imgUrls.length === 0) return;
        currentReviewLightboxList = imgUrls;
        currentReviewLightboxIdx = Math.max(0, Math.min(initialIdx, imgUrls.length - 1));

        const modal = document.getElementById('review-lightbox-modal');
        const imgEl = document.getElementById('review-lightbox-img');
        const counterEl = document.getElementById('review-lightbox-counter');

        if (imgEl) imgEl.src = currentReviewLightboxList[currentReviewLightboxIdx];
        if (counterEl) counterEl.innerText = `${currentReviewLightboxIdx + 1} / ${currentReviewLightboxList.length}`;

        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function navigateReviewLightbox(direction) {
        if (!currentReviewLightboxList || currentReviewLightboxList.length <= 1) return;
        currentReviewLightboxIdx = (currentReviewLightboxIdx + direction + currentReviewLightboxList.length) % currentReviewLightboxList.length;

        const imgEl = document.getElementById('review-lightbox-img');
        const counterEl = document.getElementById('review-lightbox-counter');

        if (imgEl) {
            imgEl.style.opacity = '0.3';
            imgEl.src = currentReviewLightboxList[currentReviewLightboxIdx];
            setTimeout(() => { imgEl.style.opacity = '1'; }, 80);
        }
        if (counterEl) counterEl.innerText = `${currentReviewLightboxIdx + 1} / ${currentReviewLightboxList.length}`;
    }

    function closeReviewLightbox() {
        const modal = document.getElementById('review-lightbox-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Lắng nghe phím bấm khi đang mở modal review lightbox
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('review-lightbox-modal');
        if (!modal || !modal.classList.contains('active')) return;
        if (e.key === 'Escape') closeReviewLightbox();
        else if (e.key === 'ArrowLeft') navigateReviewLightbox(-1);
        else if (e.key === 'ArrowRight') navigateReviewLightbox(1);
    });

    // Lọc đánh giá: hỗ trợ 'all', 'with_images', và mức sao 1..5
    let currentReviewFilter = 'all';

    function filterReviews(filterVal, btn) {
        currentReviewFilter = String(filterVal);

        // 1. Cập nhật nút chip active
        document.querySelectorAll('.star-filter-chip').forEach(c => {
            const dataVal = c.getAttribute('data-filter') || 'all';
            if (dataVal === currentReviewFilter) {
                c.classList.add('active');
            } else {
                c.classList.remove('active');
            }
        });

        // 2. Đồng bộ giá trị với dropdown select
        const selectEl = document.getElementById('reviewFilterSelect');
        if (selectEl && selectEl.value !== currentReviewFilter) {
            selectEl.value = currentReviewFilter;
        }

        // 3. Lọc danh sách thẻ đánh giá
        const cards = document.querySelectorAll('.review-item-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardRating = card.getAttribute('data-rating');
            const cardHasImages = card.getAttribute('data-has-images') === '1';

            let isMatch = false;
            if (currentReviewFilter === 'all') {
                isMatch = true;
            } else if (currentReviewFilter === 'with_images') {
                isMatch = cardHasImages;
            } else if (cardRating === currentReviewFilter) {
                isMatch = true;
            }

            if (isMatch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // 4. Hiển thị thông báo khi bộ lọc không có đánh giá nào
        let emptyFilterNotice = document.getElementById('review-filter-empty-notice');
        if (visibleCount === 0) {
            if (!emptyFilterNotice) {
                emptyFilterNotice = document.createElement('div');
                emptyFilterNotice.id = 'review-filter-empty-notice';
                emptyFilterNotice.className = 'review-empty-state';
                emptyFilterNotice.innerHTML = `
                    <div class="review-empty-icon"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                    <h4>Chưa có đánh giá nào phù hợp</h4>
                    <p>Không tìm thấy đánh giá nào với tiêu chí bạn đang chọn. Hãy thử chọn mức lọc khác bạn nhé!</p>
                `;
                const list = document.getElementById('reviewItemsList');
                if (list) list.appendChild(emptyFilterNotice);
            } else {
                emptyFilterNotice.style.display = 'block';
            }
        } else if (emptyFilterNotice) {
            emptyFilterNotice.style.display = 'none';
        }
    }

    // Tự động khởi chạy đồng bộ hiển thị và lọc phân loại theo CSDL khi tải trang
    document.addEventListener('DOMContentLoaded', () => {
        updateVariantOptionsState('color');
        matchVariantAndUpdate();
    });
</script>

<!-- Modal Lightbox Xem Ảnh Đánh Giá Lớn -->
<div id="review-lightbox-modal" class="review-lightbox-modal" onclick="if(event.target === this) closeReviewLightbox()">
    <div class="review-lightbox-container">
        <button type="button" class="review-lightbox-close" onclick="closeReviewLightbox()" aria-label="Đóng ảnh phóng to" title="Đóng (Esc)">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <button type="button" class="review-lightbox-arrow prev" onclick="navigateReviewLightbox(-1)" aria-label="Ảnh trước" title="Ảnh trước">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="review-lightbox-body">
            <img id="review-lightbox-img" src="" alt="Ảnh đánh giá của khách">
            <div class="review-lightbox-counter" id="review-lightbox-counter">1 / 1</div>
        </div>
        <button type="button" class="review-lightbox-arrow next" onclick="navigateReviewLightbox(1)" aria-label="Ảnh tiếp theo" title="Ảnh tiếp theo">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>
@endsection

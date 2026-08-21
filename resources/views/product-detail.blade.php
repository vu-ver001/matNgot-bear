@extends('layouts.customer')

@section('title', $product->name . ' - Mật Ngọt Bear')

@section('styles')
<style>
    .detail-page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
    }

    /* Breadcrumbs */
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .breadcrumb-nav a:hover {
        color: var(--honey-dark);
    }

    /* Main Detail Grid */
    .product-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1.1fr;
        gap: 3.5rem;
        align-items: start;
        margin-bottom: 4rem;
        background: #FFFFFF;
        padding: 2.5rem;
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-subtle);
    }

    /* Left Gallery */
    .detail-gallery {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .gallery-main-frame {
        width: 100%;
        padding-top: 100%;
        position: relative;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--bg-surface);
        border: 1px solid var(--border);
    }

    .gallery-main-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-main-frame:hover .gallery-main-img {
        transform: scale(1.05);
    }

    .gallery-thumbs-row {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 6px;
    }

    .gallery-thumb-item {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-md);
        border: 2px solid var(--border);
        overflow: hidden;
        cursor: pointer;
        background: var(--bg-surface);
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .gallery-thumb-item:hover, .gallery-thumb-item.active {
        border-color: var(--honey);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(229, 152, 25, 0.25);
    }

    .gallery-thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Right Info */
    .detail-info-col {
        display: flex;
        flex-direction: column;
    }

    .detail-cat-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--bg-surface);
        color: var(--primary-dark);
        border: 1px solid var(--border);
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        width: fit-content;
        margin-bottom: 10px;
    }

    .detail-product-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 32px;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1.25;
        margin-bottom: 12px;
    }

    .detail-rating-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-light);
        font-size: 13px;
        color: var(--text-muted);
    }

    .stars-group {
        color: #FFA000;
        display: flex;
        align-items: center;
        gap: 2px;
    }

    /* Price Box */
    .detail-price-box {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: baseline;
        gap: 16px;
        margin-bottom: 1.75rem;
    }

    .detail-price-current {
        font-family: 'Montserrat', sans-serif;
        font-size: 32px;
        font-weight: 800;
        color: #D32F2F;
    }

    .detail-price-old {
        font-size: 18px;
        color: var(--text-light);
        text-decoration: line-through;
    }

    .detail-sale-badge {
        background: linear-gradient(135deg, #FF5252 0%, #D32F2F 100%);
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 999px;
    }

    /* Specifications Table / Card */
    .specs-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 1.75rem;
    }

    .spec-item {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 12px;
        text-align: center;
    }

    .spec-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-light);
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .spec-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--primary-dark);
    }

    /* Stock & Quantity Control */
    .stock-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .stock-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .stock-pill.in-stock {
        background: var(--success-bg);
        color: var(--success);
        border: 1px solid #C8E6C9;
    }

    .stock-pill.out-stock {
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid #FFCDD2;
    }

    /* Quantity Box */
    .qty-selector {
        display: flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        background: var(--bg-surface);
        width: fit-content;
    }

    .qty-btn {
        width: 38px;
        height: 38px;
        border: none;
        background: transparent;
        color: var(--primary-dark);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease;
    }

    .qty-btn:hover:not(:disabled) {
        background: #FFFFFF;
    }

    .qty-input {
        width: 50px;
        height: 38px;
        border: none;
        background: #FFFFFF;
        text-align: center;
        font-weight: 700;
        font-size: 15px;
        color: var(--text-main);
        outline: none;
    }

    /* CTA Buttons */
    .action-buttons-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .btn-buy-now {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
        color: #3E2723;
        font-weight: 800;
        font-size: 15px;
        padding: 14px 20px;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(229, 152, 25, 0.35);
        transition: all 0.25s ease;
    }

    .btn-buy-now:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(229, 152, 25, 0.5);
    }

    .btn-add-cart-main {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #5D4037 0%, #3E2723 100%);
        color: #FFFFFF;
        font-weight: 800;
        font-size: 15px;
        padding: 14px 20px;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(62, 39, 35, 0.25);
        transition: all 0.25s ease;
    }

    .btn-add-cart-main:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(62, 39, 35, 0.35);
    }

    /* Store Guarantees Checklist */
    .guarantees-card {
        background: var(--honey-light);
        border: 1px dashed var(--honey);
        border-radius: var(--radius-md);
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .guarantee-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: var(--text-main);
        font-weight: 600;
    }

    /* Tabs Description Section */
    .detail-tabs-section {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 2.5rem;
        margin-bottom: 4rem;
        box-shadow: var(--shadow-subtle);
    }

    .detail-tab-header {
        font-family: 'Montserrat', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-light);
    }

    .detail-desc-content {
        font-size: 15px;
        line-height: 1.8;
        color: var(--text-main);
    }

    /* Related Products */
    .related-section-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: var(--primary-dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    @media (max-width: 992px) {
        .product-detail-grid { grid-template-columns: 1fr; gap: 2rem; padding: 1.5rem; }
        .related-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 576px) {
        .specs-grid { grid-template-columns: 1fr; }
        .action-buttons-group { grid-template-columns: 1fr; }
        .related-grid { grid-template-columns: 1fr; }
        .detail-product-title { font-size: 24px; }
    }
</style>
@endsection

@section('content')

@php
    $hasSale = !empty($product->sale_price) && $product->sale_price < $product->price;
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
            <!-- Category Badge -->
            <a href="{{ route('products.index', ['category_id' => $product->category_id]) }}" class="detail-cat-badge">
                <i class="fa-solid fa-paw" style="color: var(--honey-dark);"></i> {{ $product->category->name ?? 'Gấu Bông' }}
            </a>

            <!-- Title -->
            <h1 class="detail-product-title">{{ $product->name }}</h1>

            <!-- Rating & Sold -->
            <div class="detail-rating-row">
                <div class="stars-group">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <strong style="color: var(--text-main); margin-left: 4px;">5.0</strong>
                </div>
                <span>&bull;</span>
                <span>{{ $product->reviews_count ?? 0 }} đánh giá</span>
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

            <h4 style="font-family: 'Montserrat', sans-serif; font-size: 18px; font-weight: 700; color: var(--primary-dark); margin: 1.5rem 0 10px 0;">
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
        addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', qty);
        Swal.fire({
            title: 'Đặt Hàng Thành Công!',
            html: `Bạn đã chọn mua <strong>${qty} x {{ $product->name }}</strong>.<br>Nhân viên Mật Ngọt Bear sẽ liên hệ xác nhận đơn hàng qua hotline ngay nhé! 🧸`,
            icon: 'success',
            confirmButtonColor: '#5D4037',
            confirmButtonText: 'Tiếp tục mua sắm'
        });
    }
</script>
@endsection

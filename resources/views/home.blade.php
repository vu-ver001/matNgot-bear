@extends('layouts.customer')

@section('title', 'Mật Ngọt Bear - Gấu Bông Cao Cấp & Quà Tặng Ngọt Ngào')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endsection

@section('content')

    <!-- 1. HERO BANNER SECTION -->
    <section class="hero-banner-section">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge-pill">
                    <i class="fa-solid fa-sparkles"></i> Bộ Sưu Tập Mới 2026
                </div>
                <h1 class="hero-title">
                    Thế Giới Gấu Bông <br>
                    <span class="highlight">Êm Mềm & Ngọt Ngào</span>
                </h1>
                <p class="hero-subtitle">
                    Từng chú gấu bông tại Mật Ngọt Bear đều được chế tác từ bông PP 3D tinh khiết siêu mịn, an toàn tuyệt đối và mang lại cảm giác ôm ấm áp nhất.
                </p>
                <div class="hero-cta-group">
                    <a href="{{ route('products.index') }}" class="btn-honey-main">
                        <i class="fa-solid fa-bag-shopping"></i> Khám Phá Cửa Hàng
                    </a>
                    <a href="{{ route('products.index', ['sort' => 'best_seller']) }}" class="btn-brown-main">
                        <i class="fa-solid fa-fire"></i> Mẫu Bán Chạy Nhất
                    </a>
                </div>
            </div>

            <div class="hero-img-box">
                <div class="hero-img-backdrop"></div>
                <img src="https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=800&auto=format&fit=crop&q=80" alt="Gấu bông Mật Ngọt Bear" class="hero-main-img" onerror="this.src='https://placehold.co/600x600/f5e6ca/7c4a2d?text=Mat+Ngot+Bear'">
            </div>
        </div>
    </section>

    <!-- 2. TRUST BADGES -->
    <section class="trust-badges-section">
        <div class="trust-grid">
            <div class="trust-item">
                <div class="trust-icon-box">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="trust-text">
                    <h5>Giao Hàng Toàn Quốc 30K</h5>
                    <p>Freeship đơn hàng từ 500.000đ</p>
                </div>
            </div>

            <div class="trust-item">
                <div class="trust-icon-box">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <div class="trust-text">
                    <h5>100% Bông PP Cao Cấp</h5>
                    <p>Kháng khuẩn, không xẹp lún</p>
                </div>
            </div>

            <div class="trust-item">
                <div class="trust-icon-box">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="trust-text">
                    <h5>Đổi Trả 7 Ngày</h5>
                    <p>Miễn phí nếu lỗi sản xuất</p>
                </div>
            </div>

            <div class="trust-item">
                <div class="trust-icon-box">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div class="trust-text">
                    <h5>Gói Quà Miễn Phí</h5>
                    <p>Tặng kèm nơ & thiệp chúc mừng</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. FEATURED CATEGORIES SHOWCASE -->
    <section class="section-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fa-solid fa-layer-group" style="color: var(--honey-dark);"></i>
                    Danh Mục Nổi Bật
                </h2>
                <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Khám phá các bộ sưu tập gấu bông theo kích thước và xu hướng hot</p>
            </div>
            <a href="{{ route('products.index') }}" class="section-view-all">
                Xem tất cả <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="home-categories-grid">
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="home-cat-card">
                    <div class="home-cat-icon">
                        <i class="fa-solid fa-paw"></i>
                    </div>
                    <div class="home-cat-name">{{ $category->name }}</div>
                    <div class="home-cat-count">{{ $category->products_count ?? 0 }} sản phẩm</div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 4. BEST SELLERS SECTION -->
    <section class="section-container" style="background: var(--bg-surface); border-radius: var(--radius-xl);">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fa-solid fa-fire" style="color: #FF5722;"></i>
                    Sản Phẩm Bán Chạy Nhất
                </h2>
                <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Những mẫu gấu bông được yêu thích và đặt mua nhiều nhất tuần này</p>
            </div>
            <a href="{{ route('products.index', ['sort' => 'best_seller']) }}" class="section-view-all">
                Xem thêm <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="products-grid-4">
            @foreach($featuredProducts as $product)
                @php
                    $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                    $imgUrl = $primaryImg ? $primaryImg->image_url : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' . urlencode($product->name);
                    $hasSale = !empty($product->sale_price) && $product->sale_price < $product->price;
                    $discountPct = $hasSale ? round((($product->price - $product->sale_price) / $product->price) * 100) : 0;
                @endphp
                <div class="product-card">
                    <div class="product-card-img-wrap">
                        @if($hasSale)
                            <span class="card-badge-sale">-{{ $discountPct }}%</span>
                        @endif
                        <span class="card-badge-hot"><i class="fa-solid fa-fire"></i> HOT</span>
                        <a href="{{ route('products.show', $product->id) }}">
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="product-card-img" onerror="this.src='https://placehold.co/600x600/f5e6ca/7c4a2d?text=Gau+Bong'">
                        </a>
                    </div>
                    <div class="product-card-body">
                        <div>
                            <div class="product-card-category">{{ $product->category->name ?? 'Gấu bông' }}</div>
                            <a href="{{ route('products.show', $product->id) }}">
                                <h3 class="product-card-title">{{ $product->name }}</h3>
                            </a>
                        </div>
                        <div>
                            <div class="product-card-prices">
                                @if($hasSale)
                                    <span class="price-current">{{ number_format($product->sale_price, 0, ',', '.') }} đ</span>
                                    <span class="price-old">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                @else
                                    <span class="price-current" style="color: var(--primary-dark);">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                @endif
                            </div>
                            <div class="product-card-footer">
                                <span><i class="fa-solid fa-bag-shopping" style="color: var(--honey-dark);"></i> Đã bán {{ $product->sold_count ?? 0 }}</span>
                                <button type="button" class="btn-add-cart-quick" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')" title="Thêm vào giỏ">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 5. GIFT PROMO CALLOUT BANNER -->
    <div class="section-container" style="padding-top: 1rem; padding-bottom: 1rem;">
        <div class="gift-promo-banner">
            <div>
                <span style="background: rgba(246, 216, 155, 0.2); color: var(--honey-gold); padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase;">Dịch Vụ Quà Tặng Độc Quyền</span>
                <h3 style="font-family: 'Be Vietnam Pro', sans-serif; font-size: 32px; font-weight: 800; margin: 12px 0; color: #FFFFFF;">Bạn Muốn Gửi Gắm Yêu Thương Đến Người Ấy?</h3>
                <p style="color: #D7CCC8; font-size: 15px; line-height: 1.6; margin-bottom: 1.5rem;">
                    Mật Ngọt Bear hỗ trợ gói hộp quà nơ sang trọng, xịt nước hoa thơm dịu và đính kèm thiệp viết tay theo lời nhắn của bạn. Giao hàng chuẩn giờ cho ngày kỷ niệm và sinh nhật!
                </p>
                <a href="{{ route('products.index') }}" class="btn-honey-main">
                    <i class="fa-solid fa-gift"></i> Chọn Quà Tặng Ngay
                </a>
            </div>
            <div style="text-align: center;">
                <img src="https://images.unsplash.com/photo-1546776310-eef45dd6d63c?w=600&auto=format&fit=crop&q=80" alt="Gói quà tặng gấu bông" style="width: 100%; max-width: 320px; border-radius: var(--radius-lg); border: 3px solid rgba(255,255,255,0.2); box-shadow: 0 12px 32px rgba(0,0,0,0.3);" onerror="this.src='https://placehold.co/400x300/f5e6ca/7c4a2d?text=Gift+Box'">
            </div>
        </div>
    </div>

    <!-- 6. NEW ARRIVALS -->
    <section class="section-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fa-solid fa-sparkles" style="color: var(--honey);"></i>
                    Hàng Mới Về
                </h2>
                <p style="font-size: 14px; color: var(--text-muted); margin-top: 4px;">Những người bạn gấu bông mới nhất vừa cập bến nhà Mật Ngọt</p>
            </div>
            <a href="{{ route('products.index', ['sort' => 'latest']) }}" class="section-view-all">
                Xem tất cả <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="products-grid-4">
            @foreach($newArrivals as $product)
                @php
                    $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                    $imgUrl = $primaryImg ? $primaryImg->image_url : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' . urlencode($product->name);
                    $hasSale = !empty($product->sale_price) && $product->sale_price < $product->price;
                @endphp
                <div class="product-card">
                    <div class="product-card-img-wrap">
                        @if($hasSale)
                            <span class="card-badge-sale">Sale</span>
                        @endif
                        <a href="{{ route('products.show', $product->id) }}">
                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="product-card-img" onerror="this.src='https://placehold.co/600x600/f5e6ca/7c4a2d?text=Gau+Bong'">
                        </a>
                    </div>
                    <div class="product-card-body">
                        <div>
                            <div class="product-card-category">{{ $product->category->name ?? 'Gấu bông' }}</div>
                            <a href="{{ route('products.show', $product->id) }}">
                                <h3 class="product-card-title">{{ $product->name }}</h3>
                            </a>
                        </div>
                        <div>
                            <div class="product-card-prices">
                                @if($hasSale)
                                    <span class="price-current">{{ number_format($product->sale_price, 0, ',', '.') }} đ</span>
                                    <span class="price-old">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                @else
                                    <span class="price-current" style="color: var(--primary-dark);">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                @endif
                            </div>
                            <div class="product-card-footer">
                                <span><i class="fa-solid fa-ruler" style="color: var(--text-light);"></i> {{ $product->size ?? 'Nhiều size' }}</span>
                                <button type="button" class="btn-add-cart-quick" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')" title="Thêm vào giỏ">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

@endsection

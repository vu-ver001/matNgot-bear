@extends('layouts.customer')

@section('title', 'Mật Ngọt Bear - Gấu Bông Cao Cấp & Quà Tặng Ngọt Ngào')

@section('styles')
<style>
    /* Hero Banner */
    .hero-banner-section {
        background: linear-gradient(135deg, #FBF6EF 0%, #F5EAE0 50%, #EDE0D4 100%);
        padding: 4rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid var(--border);
    }

    .hero-container {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        align-items: center;
        gap: 3rem;
    }

    .hero-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #FFFFFF;
        border: 1px solid rgba(229, 152, 25, 0.4);
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        color: var(--honey-dark);
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 10px rgba(229, 152, 25, 0.15);
    }

    .hero-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 48px;
        font-weight: 700;
        color: var(--primary-dark);
        line-height: 1.15;
        margin-bottom: 1.25rem;
        letter-spacing: -1px;
    }

    .hero-title span.highlight {
        color: var(--honey-dark);
        background: linear-gradient(120deg, rgba(246, 216, 155, 0.5) 0%, rgba(229, 152, 25, 0.2) 100%);
        padding: 0 8px;
        border-radius: var(--radius-sm);
    }

    .hero-subtitle {
        font-size: 16px;
        line-height: 1.6;
        color: var(--text-muted);
        margin-bottom: 2rem;
        max-width: 520px;
    }

    .hero-cta-group {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .hero-img-box {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-img-backdrop {
        width: 440px;
        height: 440px;
        border-radius: 50%;
        background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
        opacity: 0.3;
        position: absolute;
        filter: blur(40px);
        animation: floatShape 6s ease-in-out infinite alternate;
    }

    .hero-main-img {
        width: 100%;
        max-width: 480px;
        height: auto;
        position: relative;
        z-index: 2;
        border-radius: var(--radius-xl);
        box-shadow: 0 20px 48px rgba(62, 39, 35, 0.18);
        border: 4px solid #FFFFFF;
        transition: transform 0.4s ease;
    }

    .hero-main-img:hover {
        transform: scale(1.03) rotate(-1deg);
    }

    @keyframes floatShape {
        0% { transform: scale(0.9) translate(0, 0); }
        100% { transform: scale(1.1) translate(20px, -20px); }
    }

    /* Trust Badges */
    .trust-badges-section {
        background: #FFFFFF;
        padding: 2rem 1.5rem;
        border-bottom: 1px solid var(--border);
    }

    .trust-grid {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px;
    }

    .trust-icon-box {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-md);
        background: var(--honey-light);
        color: var(--honey-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .trust-text h5 {
        font-family: 'Montserrat', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 2px;
    }

    .trust-text p {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Section Layouts */
    .section-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 3.5rem 1.5rem;
    }

    .section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 2rem;
        border-bottom: 2px solid var(--border-light);
        padding-bottom: 1rem;
    }

    .section-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }

    .section-view-all {
        font-size: 14px;
        font-weight: 700;
        color: var(--honey-dark);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .section-view-all:hover {
        color: var(--primary-dark);
        transform: translateX(4px);
    }

    /* Category Showcase Cards */
    .home-categories-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    .home-cat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.75rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        box-shadow: var(--shadow-subtle);
        transition: all 0.3s ease;
    }

    .home-cat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
        border-color: var(--honey);
        background: var(--honey-light);
    }

    .home-cat-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: var(--bg-surface);
        color: var(--honey-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(62, 39, 35, 0.06);
        transition: all 0.3s ease;
    }

    .home-cat-card:hover .home-cat-icon {
        background: var(--honey);
        color: #FFFFFF;
        transform: scale(1.1);
    }

    .home-cat-name {
        font-family: 'Montserrat', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 4px;
    }

    .home-cat-count {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* Products Grid */
    .products-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }

    /* Promo Banner */
    .gift-promo-banner {
        background: linear-gradient(135deg, #795548 0%, #5D4037 100%);
        color: #FFFFFF;
        border-radius: var(--radius-xl);
        padding: 3rem 4rem;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        align-items: center;
        gap: 3rem;
        box-shadow: var(--shadow-card);
        margin: 2rem auto;
        position: relative;
        overflow: hidden;
    }

    .gift-promo-banner::after {
        content: '🧸';
        position: absolute;
        right: -20px;
        bottom: -30px;
        font-size: 200px;
        opacity: 0.08;
    }

    @media (max-width: 992px) {
        .hero-container { grid-template-columns: 1fr; text-align: center; }
        .hero-subtitle { margin: 0 auto 2rem auto; }
        .hero-cta-group { justify-content: center; }
        .trust-grid { grid-template-columns: 1fr 1fr; }
        .home-categories-grid { grid-template-columns: 1fr 1fr; }
        .products-grid-4 { grid-template-columns: repeat(2, 1fr); }
        .gift-promo-banner { grid-template-columns: 1fr; padding: 2rem; text-align: center; }
    }

    @media (max-width: 576px) {
        .hero-title { font-size: 34px; }
        .trust-grid { grid-template-columns: 1fr; }
        .home-categories-grid { grid-template-columns: 1fr; }
        .products-grid-4 { grid-template-columns: 1fr; }
    }
</style>
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
                <h3 style="font-family: 'Montserrat', sans-serif; font-size: 32px; font-weight: 800; margin: 12px 0; color: #FFFFFF;">Bạn Muốn Gửi Gắm Yêu Thương Đến Người Ấy?</h3>
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

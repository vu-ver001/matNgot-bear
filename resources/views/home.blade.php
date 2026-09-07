@extends('layouts.customer')

@section('title', 'Mật Ngọt Bear - Gấu Bông Cao Cấp & Quà Tặng Ngọt Ngào')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endsection

@section('content')

    <!-- 1. HERO BANNER SLIDESHOW (5 Banner Gấu Bông - Tự động 5s - Nút < > & Dots) -->
    <section class="hero-banner-section">
        <div class="hero-banner-wrapper">
            <div class="hero-banner-card" id="heroBannerCard" onmouseenter="pauseHeroSlider()" onmouseleave="resumeHeroSlider()">
                <!-- Navigation Arrows -->
                <button type="button" class="hero-nav-arrow prev" onclick="moveHeroSlide(-1)" aria-label="Banner trước">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" class="hero-nav-arrow next" onclick="moveHeroSlide(1)" aria-label="Banner tiếp theo">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Indicator Dots -->
                <div class="hero-nav-dots" id="heroNavDots">
                    <span class="hero-dot active" onclick="goToHeroSlide(0)" title="Banner 1: Thế Giới Gấu Bông"></span>
                    <span class="hero-dot" onclick="goToHeroSlide(1)" title="Banner 2: Butter Bear"></span>
                    <span class="hero-dot" onclick="goToHeroSlide(2)" title="Banner 3: Teddy Mr. Bean"></span>
                    <span class="hero-dot" onclick="goToHeroSlide(3)" title="Banner 4: Gấu Bông Couple"></span>
                    <span class="hero-dot" onclick="goToHeroSlide(4)" title="Banner 5: Teddy Khổng Lồ"></span>
                </div>

                <div class="hero-slides-wrapper" id="heroSlidesWrapper">
                    <!-- SLIDE 1: THẾ GIỚI GẤU BÔNG CLASSIC -->
                    <div class="hero-slide active" data-slide="0">
                        <div class="hero-banner-bg" style="background-image: url('{{ asset('images/home-hero-banner.png') }}?v={{ file_exists(public_path('images/home-hero-banner.png')) ? filemtime(public_path('images/home-hero-banner.png')) : time() }}');"></div>
                        <div class="hero-banner-content">
                            <div class="hero-collection-badge">
                                <i class="fa-solid fa-fan"></i> BỘ SƯU TẬP MỚI 2026 <i class="fa-solid fa-fan"></i>
                            </div>
                            <h1 class="hero-main-title">
                                <span class="title-dark">Thế Giới Gấu Bông</span>
                                <span class="title-accent">Êm Mềm &amp; Ngọt Ngào</span>
                                <span class="title-heart-doodle">♡</span>
                            </h1>
                            <p class="hero-main-desc">
                                Từng chú gấu bông tại Mật Ngọt Bear đều được chế tác từ bông PP 3D tinh khiết siêu mịn, an toàn tuyệt đối và mang lại cảm giác ôm ấm áp nhất.
                            </p>
                            <div class="hero-features-glass">
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-cloud"></i></div>
                                    <div class="feature-name">Bông PP 3D</div>
                                    <div class="feature-sub">siêu mềm mịn</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-shield-heart"></i></div>
                                    <div class="feature-name">An toàn</div>
                                    <div class="feature-sub">cho mọi lứa tuổi</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-heart"></i></div>
                                    <div class="feature-name">Thiết kế</div>
                                    <div class="feature-sub">đáng yêu</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-gift"></i></div>
                                    <div class="feature-name">Quà tặng</div>
                                    <div class="feature-sub">ý nghĩa</div>
                                </div>
                            </div>
                            <div class="hero-btn-actions">
                                <a href="{{ route('products.index') }}" class="btn-hero-primary">
                                    <i class="fa-solid fa-bag-shopping"></i> KHÁM PHÁ CỬA HÀNG
                                </a>
                                <a href="{{ route('products.index', ['sort' => 'best_seller']) }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play"></i> MẪU BÁN CHẠY NHẤT
                                </a>
                            </div>
                            <div class="hero-bottom-doodle">
                                <span class="doodle-bear">🧸</span>
                                <span class="doodle-text">Ôm là thích mê ! ♡</span>
                            </div>
                        </div>
                    </div>

                    <!-- SLIDE 2: BUTTER BEAR ĐỘC QUYỀN -->
                    <div class="hero-slide" data-slide="1">
                        <div class="hero-banner-bg" style="background-image: url('{{ asset('images/hero-banner-2.jpg') }}?v={{ file_exists(public_path('images/hero-banner-2.jpg')) ? filemtime(public_path('images/hero-banner-2.jpg')) : time() }}');"></div>
                        <div class="hero-banner-content">
                            <div class="hero-collection-badge">
                                <i class="fa-solid fa-sparkles"></i> BUTTER BEAR ĐỘC QUYỀN <i class="fa-solid fa-sparkles"></i>
                            </div>
                            <h2 class="hero-main-title">
                                <span class="title-dark">Gấu Bơ Butter Bear</span>
                                <span class="title-accent">Má Hồng &amp; Đáng Yêu</span>
                                <span class="title-heart-doodle">♡</span>
                            </h2>
                            <p class="hero-main-desc">
                                Dòng gấu bơ Butter Bear mềm mại với chất lông xoắn mịn xốp, chiếc nơ lụa ngọt ngào sẽ sưởi ấm trái tim bạn trong từng khoảnh khắc.
                            </p>
                            <div class="hero-features-glass">
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                                    <div class="feature-name">Lông xoắn mịn</div>
                                    <div class="feature-sub">êm ái như bơ</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-face-smile-wink"></i></div>
                                    <div class="feature-name">Má hồng xinh</div>
                                    <div class="feature-sub">thêu tay tỉ mỉ</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-ribbon"></i></div>
                                    <div class="feature-name">Thiết kế nơ</div>
                                    <div class="feature-sub">sang trọng</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-star"></i></div>
                                    <div class="feature-name">Quà tặng</div>
                                    <div class="feature-sub">vạn người mê</div>
                                </div>
                            </div>
                            <div class="hero-btn-actions">
                                <a href="{{ route('products.index', ['search' => 'Butter Bear']) }}" class="btn-hero-primary">
                                    <i class="fa-solid fa-bag-shopping"></i> BỘ SƯU TẬP BƠ
                                </a>
                                <a href="{{ route('products.index', ['sort' => 'latest']) }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play"></i> SẢN PHẨM MỚI NHẤT
                                </a>
                            </div>
                            <div class="hero-bottom-doodle">
                                <span class="doodle-bear">🧈</span>
                                <span class="doodle-text">Ngọt ngào như chiếc bánh bơ ! ♡</span>
                            </div>
                        </div>
                    </div>

                    <!-- SLIDE 3: TEDDY MR. BEAN VINTAGE -->
                    <div class="hero-slide" data-slide="2">
                        <div class="hero-banner-bg" style="background-image: url('{{ asset('images/hero-banner-3.jpg') }}?v={{ file_exists(public_path('images/hero-banner-3.jpg')) ? filemtime(public_path('images/hero-banner-3.jpg')) : time() }}');"></div>
                        <div class="hero-banner-content">
                            <div class="hero-collection-badge">
                                <i class="fa-solid fa-coffee"></i> TEDDY MR. BEAN VINTAGE <i class="fa-solid fa-coffee"></i>
                            </div>
                            <h2 class="hero-main-title">
                                <span class="title-dark">Ký Ức Tuổi Thơ</span>
                                <span class="title-accent">Ấm Áp &amp; Thân Thương</span>
                                <span class="title-heart-doodle">♡</span>
                            </h2>
                            <p class="hero-main-desc">
                                Phiên bản gấu bông Teddy Mr. Bean kinh điển dệt len màu nâu cà phê sữa, người bạn đồng hành tri kỷ gắn bó với bao thế hệ yêu mến.
                            </p>
                            <div class="hero-features-glass">
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-scroll"></i></div>
                                    <div class="feature-name">Len dệt cao cấp</div>
                                    <div class="feature-sub">không xù lông</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-circle-dot"></i></div>
                                    <div class="feature-name">Mắt cúc gỗ</div>
                                    <div class="feature-sub">cổ điển tinh xảo</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-mitten"></i></div>
                                    <div class="feature-name">Khăn len ấm</div>
                                    <div class="feature-sub">thêu tay tỉ mỉ</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-gem"></i></div>
                                    <div class="feature-name">Kỷ niệm</div>
                                    <div class="feature-sub">vô giá</div>
                                </div>
                            </div>
                            <div class="hero-btn-actions">
                                <a href="{{ route('products.index', ['search' => 'Mr. Bean']) }}" class="btn-hero-primary">
                                    <i class="fa-solid fa-bag-shopping"></i> KHÁM PHÁ MR. BEAN
                                </a>
                                <a href="{{ route('products.index') }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play"></i> TẤT CẢ SẢN PHẨM
                                </a>
                            </div>
                            <div class="hero-bottom-doodle">
                                <span class="doodle-bear">☕</span>
                                <span class="doodle-text">Mộc mạc, bình dị &amp; đầy ắp tình thương ! ♡</span>
                            </div>
                        </div>
                    </div>

                    <!-- SLIDE 4: GẤU BÔNG COUPLE YÊU THƯƠNG -->
                    <div class="hero-slide" data-slide="3">
                        <div class="hero-banner-bg" style="background-image: url('{{ asset('images/hero-banner-4.jpg') }}?v={{ file_exists(public_path('images/hero-banner-4.jpg')) ? filemtime(public_path('images/hero-banner-4.jpg')) : time() }}');"></div>
                        <div class="hero-banner-content">
                            <div class="hero-collection-badge">
                                <i class="fa-solid fa-heart-pulse"></i> QUÀ TẶNG TÌNH YÊU <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <h2 class="hero-main-title">
                                <span class="title-dark">Gấu Bông Couple</span>
                                <span class="title-accent">Trao Gửi Yêu Thương</span>
                                <span class="title-heart-doodle">♡</span>
                            </h2>
                            <p class="hero-main-desc">
                                Cặp đôi gấu Teddy ngọt ngào ôm trái tim Cozy Hugs là món quà kỷ niệm hoàn hảo nhất dành tặng cho một nửa yêu thương của bạn.
                            </p>
                            <div class="hero-features-glass">
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-people-arrows"></i></div>
                                    <div class="feature-name">Cặp đôi lứa</div>
                                    <div class="feature-sub">gắn kết đồng điệu</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-heart-circle-check"></i></div>
                                    <div class="feature-name">Gối tim thêu</div>
                                    <div class="feature-sub">lời hẹn ước</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-box-tissue"></i></div>
                                    <div class="feature-name">Hộp quà nơ</div>
                                    <div class="feature-sub">sang xịn mịn</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                                    <div class="feature-name">Thiệp viết tay</div>
                                    <div class="feature-sub">chân thành</div>
                                </div>
                            </div>
                            <div class="hero-btn-actions">
                                <a href="{{ route('products.index', ['search' => 'Couple']) }}" class="btn-hero-primary">
                                    <i class="fa-solid fa-bag-shopping"></i> CHỌN QUÀ CHO NGƯỜI ẤY
                                </a>
                                <a href="{{ route('products.index') }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play"></i> BỘ SƯU TẬP QUÀ TẶNG
                                </a>
                            </div>
                            <div class="hero-bottom-doodle">
                                <span class="doodle-bear">💕</span>
                                <span class="doodle-text">Món quà ngọt ngào gắn kết hai trái tim ! ♡</span>
                            </div>
                        </div>
                    </div>

                    <!-- SLIDE 5: TEDDY KHỔNG LỒ & GỐI ÔM -->
                    <div class="hero-slide" data-slide="4">
                        <div class="hero-banner-bg" style="background-image: url('{{ asset('images/hero-banner-5.jpg') }}?v={{ file_exists(public_path('images/hero-banner-5.jpg')) ? filemtime(public_path('images/hero-banner-5.jpg')) : time() }}');"></div>
                        <div class="hero-banner-content">
                            <div class="hero-collection-badge">
                                <i class="fa-solid fa-moon"></i> TEDDY KHỔNG LỒ &amp; GỐI ÔM <i class="fa-solid fa-moon"></i>
                            </div>
                            <h2 class="hero-main-title">
                                <span class="title-dark">Gấu Bông Khổng Lồ</span>
                                <span class="title-accent">Ôm Trọn Giấc Mơ</span>
                                <span class="title-heart-doodle">♡</span>
                            </h2>
                            <p class="hero-main-desc">
                                Những chú gấu khổng lồ và gối ôm dài êm ái như mây trời, mang lại cảm giác an tâm, thư giãn và bình yên sau một ngày dài bận rộn.
                            </p>
                            <div class="hero-features-glass">
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-maximize"></i></div>
                                    <div class="feature-name">Kích thước lớn</div>
                                    <div class="feature-sub">1m2 đến 1m8</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-feather-pointed"></i></div>
                                    <div class="feature-name">Bông xoắn 3D</div>
                                    <div class="feature-sub">không xẹp lún</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-jug-detergent"></i></div>
                                    <div class="feature-name">Giặt dễ dàng</div>
                                    <div class="feature-sub">khóa kéo tiện lợi</div>
                                </div>
                                <div class="feature-glass-divider"></div>
                                <div class="feature-glass-item">
                                    <div class="feature-icon"><i class="fa-solid fa-bed"></i></div>
                                    <div class="feature-name">Vòng tay ấm</div>
                                    <div class="feature-sub">êm ru cả đêm</div>
                                </div>
                            </div>
                            <div class="hero-btn-actions">
                                <a href="{{ route('products.index', ['search' => 'Khổng lồ']) }}" class="btn-hero-primary">
                                    <i class="fa-solid fa-bag-shopping"></i> XEM GẤU KHỔNG LỒ
                                </a>
                                <a href="{{ route('products.index', ['search' => 'Gối']) }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play"></i> BỘ SƯU TẬP GỐI ÔM
                                </a>
                            </div>
                            <div class="hero-bottom-doodle">
                                <span class="doodle-bear">☁️</span>
                                <span class="doodle-text">Vòng ôm êm ái cho giấc ngủ ngọt ngào ! ♡</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Script Tự Động Slideshow 5 Giây, Điều Hướng & Pause khi Hover -->
    <script>
        let currentHeroIndex = 0;
        let heroAutoPlayTimer = null;

        function getHeroSlides() {
            return document.querySelectorAll('.hero-slide');
        }

        function getHeroDots() {
            return document.querySelectorAll('.hero-dot');
        }

        function showHeroSlide(index) {
            const slides = getHeroSlides();
            const dots = getHeroDots();
            if (slides.length === 0) return;

            if (index >= slides.length) currentHeroIndex = 0;
            else if (index < 0) currentHeroIndex = slides.length - 1;
            else currentHeroIndex = index;

            slides.forEach((slide, idx) => {
                if (idx === currentHeroIndex) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });

            dots.forEach((dot, idx) => {
                if (idx === currentHeroIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function moveHeroSlide(step) {
            showHeroSlide(currentHeroIndex + step);
            restartHeroSlider();
        }

        function goToHeroSlide(index) {
            showHeroSlide(index);
            restartHeroSlider();
        }

        function startHeroSlider() {
            if (heroAutoPlayTimer) clearInterval(heroAutoPlayTimer);
            heroAutoPlayTimer = setInterval(() => {
                showHeroSlide(currentHeroIndex + 1);
            }, 5000);
        }

        function pauseHeroSlider() {
            if (heroAutoPlayTimer) {
                clearInterval(heroAutoPlayTimer);
                heroAutoPlayTimer = null;
            }
        }

        function resumeHeroSlider() {
            startHeroSlider();
        }

        function restartHeroSlider() {
            pauseHeroSlider();
            resumeHeroSlider();
        }

        document.addEventListener('DOMContentLoaded', () => {
            startHeroSlider();
        });
    </script>

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
            @foreach($categories->take(4) as $category)
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
                    $hasSale = $product->is_on_sale;
                    $discountPct = $hasSale ? round((($product->price - $product->sale_price) / $product->price) * 100) : 0;
                @endphp
                <div class="product-card">
                    <div class="product-card-img-wrap">
                        @if($hasSale)
                            <span class="card-badge-sale">-{{ $discountPct }}%</span>
                        @endif
                        <span class="card-badge-hot"><i class="fa-solid fa-fire"></i> HOT</span>
                        <button type="button" class="btn-wishlist-card" data-product-id="{{ $product->id }}" onclick="toggleWishlist({ id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->price }}, sale_price: {{ $product->sale_price ?? 'null' }}, image_url: '{{ $imgUrl }}' }, event)" title="Lưu vào yêu thích">
                            <i class="fa-regular fa-heart"></i>
                        </button>
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
                                @if($product->stock_quantity > 0)
                                    <button type="button" class="btn-add-cart-quick" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn-add-cart-quick" style="opacity: 0.5; background: #e5e5e5; color: #888; cursor: not-allowed;" onclick="if(!window.isCustomerAuthenticated) { openAuthModal(window.location.href, 'Đăng nhập để Thêm vào giỏ'); } else { Toast.fire({icon: 'warning', title: 'Sản phẩm tạm hết hàng!'}); }" title="Tạm hết hàng">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 5. GIFT PROMO CALLOUT BANNER (BUTTER BEAR THEME) -->
    <div class="section-container" style="padding-top: 1rem; padding-bottom: 1rem;">
        <div class="gift-promo-banner">
            <div class="gift-promo-content">
                <span class="gift-promo-badge">Dịch Vụ Quà Tặng Độc Quyền</span>
                <h3 class="gift-promo-title">Bạn Muốn Gửi Gắm Yêu Thương Đến Người Ấy?</h3>
                <p class="gift-promo-desc">
                    Mật Ngọt Bear hỗ trợ gói hộp quà nơ sang trọng, xịt nước hoa thơm dịu và đính kèm thiệp viết tay theo lời nhắn của bạn. Giao hàng chuẩn giờ cho ngày kỷ niệm và sinh nhật!
                </p>
                <a href="{{ route('products.index') }}" class="gift-promo-btn">
                    <i class="fa-solid fa-gift"></i> Chọn Quà Tặng Ngay
                </a>
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
                    $hasSale = $product->is_on_sale;
                @endphp
                <div class="product-card">
                    <div class="product-card-img-wrap">
                        @if($hasSale)
                            <span class="card-badge-sale">Sale</span>
                        @endif
                        <button type="button" class="btn-wishlist-card" data-product-id="{{ $product->id }}" onclick="toggleWishlist({ id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $product->price }}, sale_price: {{ $product->sale_price ?? 'null' }}, image_url: '{{ $imgUrl }}' }, event)" title="Lưu vào yêu thích">
                            <i class="fa-regular fa-heart"></i>
                        </button>
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
                                @if($product->stock_quantity > 0)
                                    <button type="button" class="btn-add-cart-quick" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')" title="Thêm vào giỏ">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn-add-cart-quick" style="opacity: 0.5; background: #e5e5e5; color: #888; cursor: not-allowed;" onclick="if(!window.isCustomerAuthenticated) { openAuthModal(window.location.href, 'Đăng nhập để Thêm vào giỏ'); } else { Toast.fire({icon: 'warning', title: 'Sản phẩm tạm hết hàng!'}); }" title="Tạm hết hàng">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

@endsection

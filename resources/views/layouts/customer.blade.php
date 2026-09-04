<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mật Ngọt Bear - Thế Giới Gấu Bông Cao Cấp')</title>

    <!-- Google Fonts Be Vietnam Pro -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Customer Layout CSS (Tách riêng bởi Khánh Vân) -->
    <link rel="stylesheet" href="{{ asset('css/customer-layout.css') }}">
    @yield('styles')
</head>
<body>

    <!-- Top Announcement Bar with Quick Role Switcher for Testing -->
    <div class="top-announcement">
        <div>
            <i class="fa-solid fa-gift" style="color: var(--honey-gold);"></i>
            <strong>Ưu đãi ngọt ngào:</strong> Miễn phí gói quà & thiệp viết tay cho đơn hàng từ 299.000đ!
        </div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 11px;">
            <span style="color: #D7CCC8;">[Test vai trò]:</span>
            <a href="{{ route('switch-role', 'admin') }}" style="color: {{ auth()->check() && auth()->user()->role === 'ADMIN' ? '#4CAF50' : '#F6D89B' }}; font-weight: 700; text-decoration: underline;" title="Đăng nhập tài khoản Admin">Admin</a> &bull;
            <a href="{{ route('switch-role', 'staff') }}" style="color: {{ auth()->check() && auth()->user()->role === 'STAFF' ? '#4CAF50' : '#F6D89B' }}; font-weight: 700; text-decoration: underline;" title="Đăng nhập tài khoản Staff">Staff</a> &bull;
            <a href="{{ route('switch-role', 'customer') }}" style="color: {{ auth()->check() && auth()->user()->role === 'CUSTOMER' ? '#4CAF50' : '#F6D89B' }}; font-weight: 700; text-decoration: underline;" title="Đăng nhập tài khoản Khách hàng">Khách hàng</a> &bull;
            <a href="{{ route('switch-role', 'guest') }}" style="color: {{ !auth()->check() ? '#4CAF50' : '#D7CCC8' }}; text-decoration: underline;" title="Chưa đăng nhập">Khách vãng lai</a>
        </div>
    </div>

    <!-- Main Header -->
    <header class="site-header">
        <!-- Component Header (Không gồm thanh danh mục con) -->
        @include('partials.header')

        <!-- Navigation Menu Row with Mega Menu (Image 1 Style) -->
        <nav class="nav-bar">
            <div class="nav-container">
                @php
                    $headerCategories = \App\Models\Category::all();
                    $catClassic = $headerCategories->firstWhere('name', 'TEDDY CLASSIC');
                    $catButter  = $headerCategories->firstWhere('name', 'BUTTER BEAR');
                    $catMrBean  = $headerCategories->firstWhere('name', 'TEDDY MR. BEAN');
                    $catCouple  = $headerCategories->firstWhere('name', 'TEDDY COUPLE');
                    $catPillow  = $headerCategories->firstWhere('name', 'GỐI BÔNG TEDDY');
                @endphp
                <ul class="nav-menu">
                    <!-- 1. TRANG CHỦ -->
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i> TRANG CHỦ
                        </a>
                    </li>

                    <!-- 2. TEDDY CLASSIC (Mega Dropdown) -->
                    @if($catClassic)
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => $catClassic->id]) }}" class="nav-link {{ request('category_id') == $catClassic->id ? 'active' : '' }}">
                            TEDDY CLASSIC <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GẤU BÔNG TEDDY CAO CẤP</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'sub' => 'Gấu Bông Teddy Socola']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy Socola</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'sub' => 'Gấu Bông Teddy Logo Baby']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Teddy Logo Baby</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'sub' => 'Gấu Teddy Boy Đeo Nơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Teddy Boy Đeo Nơ</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">KÍCH THƯỚC KHỦNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'size' => '1m8']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m8 – 2m</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'size' => '1m7']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m7</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'size' => '1m6']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m6</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">MÀU SẮC ĐƯỢC YÊU THÍCH</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'search' => 'Socola']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Nâu Socola Quý Phái</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'search' => 'Vàng Kem']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Vàng Bơ Kem Sữa</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id, 'search' => 'Hồng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hồng Pastel Ngọt Ngào</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">COMBO QUÀ TẶNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Gấu Teddy Thắt Nơ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Thiệp Viết Tay Kèm Hoa</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catClassic->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Xịt Nước Hoa Thơm Lâu</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    <!-- 3. BUTTER BEAR (Mega Dropdown) -->
                    @if($catButter)
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => $catButter->id]) }}" class="nav-link {{ request('category_id') == $catButter->id ? 'active' : '' }}">
                            BUTTER BEAR <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">BUTTER BEAR NỔI BẬT</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'sub' => 'Gấu Bơ Đội Mũ Bơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Đội Mũ Bơ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'sub' => 'Butter Bear Váy Hồng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Váy Hồng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'sub' => 'Butter Bear Cầm Bánh Mì']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Cầm Bánh Mì</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">KÍCH THƯỚC PHỔ BIẾN</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'size' => '35cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 35cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'size' => '45cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 45cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'size' => '60cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 60cm</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">PHONG CÁCH</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'search' => 'Má Hồng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Má Hồng Dễ Thương</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'search' => 'Váy']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Công Chúa Váy Xòe</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id, 'search' => 'Bánh Mì']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Bé Bơ Ăn Sáng Cute</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">COMBO QUÀ TẶNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Quà Sinh Nhật Gấu Bơ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Combo Kèm Đèn Led Đom Đóm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catButter->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Trong Suốt Quý Phái</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    <!-- 4. TEDDY MR. BEAN (Mega Dropdown) -->
                    @if($catMrBean)
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="nav-link {{ request('category_id') == $catMrBean->id ? 'active' : '' }}">
                            TEDDY MR. BEAN <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">MR. BEAN NỔI BẬT</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id, 'sub' => 'Gấu Bông Mr. Bean Mắt Cúc']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Mắt Cúc 40cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id, 'sub' => 'Mr. Bean To 80cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Khổng Lồ 80cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id, 'sub' => 'Combo Mr. Bean Mini']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Quà Vintage Mini</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">CHẤT LIỆU VINTAGE</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Len Dệt Sợi Thô Cổ Điển</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Nơ Kẻ Sọc Vintage</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mắt Cúc Áo Thủ Công Chuẩn Phim</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">KÍCH CỠ ĐA DẠNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id, 'size' => '25cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Size Mini 25cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id, 'size' => '40cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Size Chuẩn 40cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id, 'size' => '80cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Size Ôm 80cm</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">QUÀ TẶNG KỶ NIỆM</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Giấy Kraft Mộc</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Thiệp Chúc Mừng Vintage</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catMrBean->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Quà Tặng Độc Đáo Fan Phim</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    <!-- 5. TEDDY COUPLE (Dropdown) -->
                    @if($catCouple)
                    <li class="nav-item has-dropdown">
                        <a href="{{ route('products.index', ['category_id' => $catCouple->id]) }}" class="nav-link {{ request('category_id') == $catCouple->id ? 'active' : '' }}">
                            TEDDY COUPLE <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('products.index', ['category_id' => $catCouple->id, 'sub' => 'Cặp Gấu Cô Dâu Chú Rể']) }}" class="dropdown-item">
                                <i class="fa-solid fa-rings-wedding" style="color: #E91E63; margin-right: 8px;"></i> Cặp Gấu Cô Dâu Chú Rể
                            </a>
                            <a href="{{ route('products.index', ['category_id' => $catCouple->id, 'sub' => 'Cặp Gấu Áo Đôi Trái Tim']) }}" class="dropdown-item">
                                <i class="fa-solid fa-heart" style="color: #D32F2F; margin-right: 8px;"></i> Cặp Gấu Áo Đôi Trái Tim
                            </a>
                            <a href="{{ route('products.index', ['category_id' => $catCouple->id, 'sub' => 'Set Gấu Ôm Bó Hoa Kỷ Niệm']) }}" class="dropdown-item">
                                <i class="fa-solid fa-spa" style="color: #E59819; margin-right: 8px;"></i> Set Gấu Ôm Bó Hoa Kỷ Niệm
                            </a>
                        </div>
                    </li>
                    @endif

                    <!-- 6. GỐI BÔNG TEDDY (Mega Dropdown) -->
                    @if($catPillow)
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="nav-link {{ request('category_id') == $catPillow->id ? 'active' : '' }}">
                            GỐI BÔNG TEDDY <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI ÔM DÀI</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id, 'sub' => 'Gối Ôm Dài 1m2 - 1m5']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Dài Teddy 1m2 – 1m5</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id, 'size' => '1m2']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Dáng Nằm Siêu Êm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Ruột Bông Bi Thái</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI TỰA & VĂN PHÒNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id, 'sub' => 'Gối Tựa Lưng Mặt Gấu']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Tựa Lưng Mặt Gấu 40cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Tựa Êm Ái Chống Đau Lưng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Đệm Lưng Có Quai Cố Định Ghế</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI MỀN 2 TRONG 1</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id, 'sub' => 'Gối Mền 2 Trong 1']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Mền Gấu Kèm Chăn 1m6</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Chăn Nỉ Tuyết Ấm Áp Tiện Lợi</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Du Lịch & Văn Phòng</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">TIỆN ÍCH PHÒNG NGỦ</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Vải Nhung Spandex Co Giãn 4 Chiều</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Kháng Khuẩn Không Xẹp Lún</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => $catPillow->id]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Giặt Máy Thoải Mái</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endif

                    <!-- 7. TẤT CẢ SẢN PHẨM -->
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') && !request('category_id') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> TẤT CẢ SẢN PHẨM
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content Body -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Component Footer -->
    @include('partials.footer')

    <!-- Global Scripts -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            background: '#FAF6F0',
            color: '#3E2723'
        });

        // Close role dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            document.querySelectorAll('.role-dropdown.show').forEach(dd => {
                if (!dd.parentElement.contains(e.target)) {
                    dd.classList.remove('show');
                }
            });
        });

        // Cart & Wishlist state (distinct item count)
        let cartItemsCount = {{ (int) ($realCartCount ?? 0) }};
        let wishlistCount = parseInt(localStorage.getItem('mn_wishlist_count') || '0');
        updateCartBadge();
        updateWishlistBadge();

        function updateCartBadge() {
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.innerText = cartItemsCount;
                badge.style.display = cartItemsCount > 0 ? 'flex' : 'flex';
            }
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) wBadge.innerText = wishlistCount;
        }

        function fetchCartCount() {
            fetch('{{ route('customer.cart.count') }}')
                .then(r => r.json())
                .then(data => {
                    if (data && data.cart_count !== undefined) {
                        cartItemsCount = data.cart_count;
                        updateCartBadge();
                    }
                })
                .catch(() => {});
        }

        // Auto sync when page gains focus or is restored from bfcache
        window.addEventListener('focus', fetchCartCount);
        window.addEventListener('pageshow', fetchCartCount);

        function addToCart(productId, productName = 'Gấu bông', qty = 1, redirectMode = false) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            return fetch('{{ route('customer.cart.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: qty
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.cart_count !== undefined) {
                            cartItemsCount = data.cart_count;
                        } else {
                            cartItemsCount++;
                        }
                        updateCartBadge();

                        // Ensure newly added item is added to persisted selected list
                        if (data.cartItem && data.cartItem.id) {
                            try {
                                const saved = localStorage.getItem('mn_selected_cart_items');
                                if (saved !== null) {
                                    const arr = JSON.parse(saved);
                                    if (Array.isArray(arr) && !arr.includes(data.cartItem.id)) {
                                        arr.push(data.cartItem.id);
                                        localStorage.setItem('mn_selected_cart_items', JSON.stringify(arr));
                                    }
                                }
                            } catch (e) {}
                        }

                        if (redirectMode === 'checkout' || redirectMode === true) {
                            const cartItemId = data.cartItem?.id;
                            const targetCheckoutUrl = cartItemId 
                                ? "{{ route('customer.checkout.index') }}?selected_items[]=" + cartItemId
                                : "{{ route('customer.checkout.index') }}?product_id=" + productId + "&quantity=" + qty;

                            if (!window.isCustomerAuthenticated) {
                                openAuthModal(targetCheckoutUrl);
                                return true;
                            }

                            window.location.href = targetCheckoutUrl;
                        } else if (redirectMode === 'cart') {
                            window.location.href = "{{ route('customer.cart') }}";
                        } else {
                            Toast.fire({
                                icon: 'success',
                                title: `Đã thêm "${productName}" vào giỏ hàng!`
                            });
                        }
                        return true;
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Không thể thêm sản phẩm vào giỏ hàng'
                        });
                        return false;
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    Toast.fire({
                        icon: 'error',
                        title: 'Có lỗi xảy ra khi thêm vào giỏ hàng!'
                    });
                    return false;
                });
        }

        function showToastCart() {
            if (cartItemsCount === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Giỏ hàng của bạn đang trống',
                    text: 'Hãy chọn ngay những chú gấu bông xinh xắn bạn nhé!',
                    confirmButtonColor: '#5D4037',
                    confirmButtonText: 'Khám phá sản phẩm ngay'
                }).then(() => {
                    window.location.href = "{{ route('products.index') }}";
                });
            } else {
                window.location.href = "{{ route('customer.cart') }}";
            }
        }

        // ================= WISHLIST MANAGER =================
        function getWishlist() {
            try {
                return JSON.parse(localStorage.getItem('mn_wishlist_items') || '[]');
            } catch(e) {
                return [];
            }
        }

        function saveWishlist(items) {
            localStorage.setItem('mn_wishlist_items', JSON.stringify(items));
            localStorage.setItem('mn_wishlist_count', items.length);
            updateWishlistBadge();
        }

        function updateWishlistBadge() {
            const items = getWishlist();
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) wBadge.innerText = items.length;

            // Sync all heart buttons on page
            document.querySelectorAll('.btn-wishlist-card, .btn-wishlist-detail').forEach(btn => {
                const pId = parseInt(btn.getAttribute('data-product-id'));
                const isFav = items.some(item => item.id === pId);
                if (isFav) {
                    btn.classList.add('active');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.className = 'fa-solid fa-heart';
                    }
                } else {
                    btn.classList.remove('active');
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.className = 'fa-regular fa-heart';
                    }
                }
            });
        }

        function toggleWishlist(product, e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            let items = getWishlist();
            const index = items.findIndex(item => item.id === product.id);

            if (index > -1) {
                // Remove from wishlist
                items.splice(index, 1);
                saveWishlist(items);
                showWishlistHeaderToast(`Đã xóa "${product.name}" khỏi danh sách yêu thích`, false);
            } else {
                // Add to wishlist
                items.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    sale_price: product.sale_price || null,
                    image_url: product.image_url,
                    url: `/products/${product.id}`
                });
                saveWishlist(items);
                showWishlistHeaderToast(`Đã thêm "${product.name}" vào danh sách yêu thích! ❤️`, true);
            }
        }

        function showWishlistHeaderToast(message, isSuccess = true) {
            // 1. SweetAlert2 Toast at top-end
            Toast.fire({
                icon: isSuccess ? 'success' : 'info',
                title: message
            });

            // 2. Animate Wishlist icon in header
            const headerBtn = document.getElementById('wishlist-header-btn');
            if (headerBtn) {
                const headerIcon = headerBtn.querySelector('i');
                if (headerIcon) {
                    headerIcon.classList.add('heart-pulse-anim');
                    setTimeout(() => headerIcon.classList.remove('heart-pulse-anim'), 700);
                }
            }
        }

        // Global Auth Modal Helper
        window.isCustomerAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

        function openAuthModal(targetUrl = null) {
            const modal = document.getElementById('mn-auth-modal');
            if (!modal) return;
            
            const loginBtn = document.getElementById('mn-auth-login-btn');
            const registerBtn = document.getElementById('mn-auth-register-btn');
            const baseLogin = "{{ route('login') }}";
            const baseRegister = "{{ route('register') }}";
            
            if (targetUrl) {
                loginBtn.href = `${baseLogin}?redirect=${encodeURIComponent(targetUrl)}`;
                registerBtn.href = `${baseRegister}?redirect=${encodeURIComponent(targetUrl)}`;
            } else {
                loginBtn.href = baseLogin;
                registerBtn.href = baseRegister;
            }
            
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                const card = modal.querySelector('.mn-auth-card');
                if (card) {
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }
            });
        }

        function closeAuthModal() {
            const modal = document.getElementById('mn-auth-modal');
            if (!modal) return;
            modal.classList.add('opacity-0', 'pointer-events-none');
            const card = modal.querySelector('.mn-auth-card');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }
    </script>

    {{-- Global Auth Required Modal for Checkout / Buy Now --}}
    <div id="mn-auth-modal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none" style="display: none;" onclick="if(event.target === this) closeAuthModal();">
        <div class="mn-auth-card relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-[#F2DECA] p-6 sm:p-8 text-center transform scale-95 transition-transform duration-300">
            {{-- Close button --}}
            <button type="button" onclick="closeAuthModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-[#FFF9F2] hover:bg-[#F2DECA] text-[#7D6B5D] flex items-center justify-center text-lg transition cursor-pointer">
                ✕
            </button>

            {{-- Cute Icon / Badge --}}
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-[#FFF0DC] to-[#FFE3BA] flex items-center justify-center text-3xl shadow-md shadow-[#E08A1E]/15 mb-4">
                🧸
            </div>

            <h3 class="text-xl sm:text-2xl font-black text-[#2B1810] mb-2 tracking-tight">
                Đăng nhập để thanh toán
            </h3>
            <p class="text-xs sm:text-sm text-[#7D6B5D] leading-relaxed mb-5 font-medium">
                Đăng nhập hoặc tạo tài khoản <strong class="text-[#2B1810]">Mật Ngọt Bear</strong> để tiếp tục đặt hàng, nhận voucher ưu đãi và tích lũy điểm thưởng!
            </p>

            {{-- Value Props Checklist --}}
            <div class="bg-[#FFFDF9] border border-[#F2DECA] rounded-2xl p-3.5 mb-6 text-left space-y-2 text-xs font-semibold text-[#5D4037]">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎟️</span>
                    <span>Áp dụng mã giảm giá voucher & miễn phí vận chuyển</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base">📍</span>
                    <span>Lưu địa chỉ giao hàng tiện lợi cho các lần mua sau</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base">📦</span>
                    <span>Theo dõi trạng thái và tiến độ giao hàng chi tiết</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                <a id="mn-auth-login-btn" href="{{ route('login') }}" class="w-full bg-gradient-to-r from-[#E08A1E] to-[#D68729] hover:from-[#D17E17] hover:to-[#C2751D] text-white font-extrabold py-3.5 px-6 rounded-2xl shadow-lg shadow-[#E08A1E]/30 flex items-center justify-center gap-2 text-sm transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Đăng nhập tài khoản</span>
                </a>
                
                <a id="mn-auth-register-btn" href="{{ route('register') }}" class="w-full bg-[#FFF9F2] hover:bg-[#FFF0DC] text-[#8C4A19] font-extrabold py-3.5 px-6 rounded-2xl border border-[#F2DECA] flex items-center justify-center gap-2 text-sm transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Đăng ký tài khoản mới</span>
                </a>
            </div>

            <div class="mt-5 text-center">
                <button type="button" onclick="closeAuthModal()" class="text-xs font-bold text-[#A8988A] hover:text-[#7D6B5D] transition cursor-pointer">
                    Tiếp tục xem sản phẩm
                </button>
            </div>
        </div>
    </div>
>>>>>>> f12e814d1d386a89c48d970cc87df82e53dbb6fd
    </script>
    @yield('scripts')
</body>
</html>

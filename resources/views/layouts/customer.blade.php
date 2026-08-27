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
                <ul class="nav-menu">
                    <!-- 1. TRANG CHỦ -->
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i> TRANG CHỦ
                        </a>
                    </li>

                    <!-- 2. TEDDY CLASSIC (Mega Dropdown) -->
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => 1]) }}" class="nav-link {{ request('category_id') == 1 ? 'active' : '' }}">
                            TEDDY CLASSIC <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GẤU BÔNG TEDDY CAO CẤP</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Teddy 1m4 - 1m5', 'size' => '1m4']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m4 – 1m5</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Bông Teddy 1m - 1m2', 'size' => '1m2']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bông Teddy 1m – 1m2</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Bông Teddy Nhỏ', 'size' => '30cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bông Teddy Nhỏ</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GẤU TEDDY TO BỰ</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Bông 1m8 - 2m', 'size' => '1m8']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m8 – 2m</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Bông Teddy 1m6', 'size' => '1m6']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bông Teddy 1m6</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Bông Khổng Lồ 1m7', 'size' => '1m7']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bông Khổng Lồ 1m7</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GẤU TEDDY GIÁ RẺ</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu 100K - 200K', 'max_price' => 200000]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu 100K – 200K</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu 65K - 100K', 'max_price' => 100000]) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu 65K – 100K</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Mini 50K']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Mini Bỏ Túi</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GẤU BÔNG DỄ THƯƠNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Bông Ghi Âm', 'search' => 'ghi âm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bông Ghi Âm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Búp Bê Bông']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Búp Bê Bông</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 1, 'sub' => 'Gấu Teddy Áo Len', 'search' => 'áo len']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy Áo Len</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- 3. BUTTER BEAR (Mega Dropdown) -->
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => 2]) }}" class="nav-link {{ request('category_id') == 2 ? 'active' : '' }}">
                            BUTTER BEAR <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">BUTTER BEAR NỔI BẬT</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Butter Bear Đội Mũ Bơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Đội Mũ Bơ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Butter Bear Váy Hồng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Váy Hồng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Butter Bear Cầm Bánh Mì']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Cầm Bánh Mì</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">KÍCH THƯỚC PHỔ BIẾN</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Butter Bear 35cm', 'size' => '35cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 35cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Butter Bear 45cm', 'size' => '45cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 45cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Butter Bear 60cm', 'size' => '60cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 60cm</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">PHỤ KIỆN BUTTER BEAR</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Móc Khóa Butter Bear', 'search' => 'móc khóa']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Móc Khóa Butter Bear</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Balo Butter Bear']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Balo Butter Bear</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Túi Đeo Chéo Gấu Bơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Túi Đeo Chéo Gấu Bơ</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">COMBO QUÀ TẶNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Set Quà Sinh Nhật Butter Bear']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Quà Sinh Nhật Butter Bear</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Combo Gấu Bơ Kèm Hộp Đèn']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Combo Kèm Hộp Đèn Led</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Set Butter Bear Tốt Nghiệp']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Tốt Nghiệp</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- 4. CAPYBARA & TREND (Mega Dropdown) -->
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => 3]) }}" class="nav-link {{ request('category_id') == 3 ? 'active' : '' }}">
                            CAPYBARA &amp; TREND <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">CAPYBARA ĐÌNH ĐÁM</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Capybara Rút Nước Mũi']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Capybara Rút Nước Mũi</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Capybara Balo Rùa']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Capybara Balo Rùa Xanh</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Capybara Đội Mũ Cam']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Capybara Đội Quả Cam</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Capybara Bánh Mì Bơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Capybara Bánh Mì</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">HOT TREND TIKTOK</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Gấu Bông Loopy Hồng', 'search' => 'loopy']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hải Ly Loopy Hồng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Gấu Dâu Lotso Thơm Mùi Dâu', 'search' => 'lotso']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Dâu Lotso Hương Dâu</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Vịt Trắng Trầm Cảm Lalafanfan']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Vịt Má Hồng Lalafanfan</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Mèo Hoàng Thượng Mặt Quạu']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mèo Mặt Quạu</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">NHÂN VẬT HOẠT HÌNH</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Thỏ Bảy Màu & Shin']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Shin Cậu Bé Bút Chì</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Doraemon Đội Chuông Vàng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Doraemon Chuông Vàng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Pikachu & Pokemon']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Pikachu Pokemon Siêu Cấp</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Stitch Xanh Tai To']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Stitch Xanh Tai To</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">SIZE SIÊU KHỔNG LỒ</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Capybara Khổng Lồ 1m2']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Capybara 1m2 Khổng Lồ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Loopy Khổng Lồ 1m']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Loopy 1m Ôm Cực Đã</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Lotso Đại 1m4']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Lotso Đại 1m4</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- 5. BỘ SƯU TẬP THEO MÙA (Dropdown Thường) -->
                    <li class="nav-item has-dropdown">
                        <a href="{{ route('products.index', ['category_id' => 4]) }}" class="nav-link {{ request('category_id') == 4 ? 'active' : '' }}">
                            BST THEO MÙA <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'BST Valentine Tỏ Tình']) }}" class="dropdown-item">
                                <i class="fa-solid fa-heart" style="color: #E91E63; margin-right: 8px;"></i> BST Valentine Tỏ Tình
                            </a>
                            <a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'BST Giáng Sinh Ấm Áp']) }}" class="dropdown-item">
                                <i class="fa-solid fa-tree" style="color: #2E7D32; margin-right: 8px;"></i> BST Giáng Sinh Noel
                            </a>
                            <a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'BST Tốt Nghiệp Trưởng Thành']) }}" class="dropdown-item">
                                <i class="fa-solid fa-graduation-cap" style="color: #1976D2; margin-right: 8px;"></i> BST Gấu Tốt Nghiệp
                            </a>
                            <a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'BST Sinh Nhật Đáng Yêu']) }}" class="dropdown-item">
                                <i class="fa-solid fa-cake-candles" style="color: #E59819; margin-right: 8px;"></i> BST Quà Tặng Sinh Nhật
                            </a>
                        </div>
                    </li>

                    <!-- 6. GỐI BÔNG TEDDY (Mega Dropdown) -->
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => 5]) }}" class="nav-link {{ request('category_id') == 5 ? 'active' : '' }}">
                            GỐI BÔNG TEDDY <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI ÔM DÀI</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Ôm Dài 1m2 - 1m5']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Dài 1m2 – 1m5</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Ôm Hình Trụ Khủng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Hình Trụ Khủng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Ôm Bông Bi Thái']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Bông Bi Thái</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI TỰA & VĂN PHÒNG</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Tựa Lưng Mặt Gấu 40cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Tựa Lưng Mặt Gấu</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Đệm Ngồi Bông Êm Ái']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Đệm Ngồi Bông Êm Ái</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Kê Cổ Chữ U']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Kê Cổ Chữ U</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI MỀN 2 TRONG 1</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Mền Gấu Bông 2 Trong 1']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Mền Gấu Kèm Chăn</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Đút Tay Mùa Đông']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Đút Tay Giữ Ấm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Chăn Nỉ Tuyết']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Chăn Nỉ Tuyết</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">GỐI TRÒN & CUTE</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Tròn Mặt Gấu Cười']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Tròn Mặt Gấu Cười</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Bánh Mì Teddy']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Bánh Mì Teddy</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 5, 'sub' => 'Gối Trái Tim Teddy']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Trái Tim Teddy</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

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

<<<<<<< HEAD
        // Cart & Wishlist state
        let cartItemsCount = parseInt(localStorage.getItem('mn_cart_count') || '0');
=======
        // Cart & Wishlist state (distinct item count)
        let cartItemsCount = {{ (int) ($realCartCount ?? 0) }};
        let wishlistCount = parseInt(localStorage.getItem('mn_wishlist_count') || '0');
>>>>>>> f12e814d1d386a89c48d970cc87df82e53dbb6fd
        updateCartBadge();
        updateWishlistBadge();

        function updateCartBadge() {
            const badge = document.getElementById('cart-count');
<<<<<<< HEAD
            if (badge) badge.innerText = cartItemsCount;
=======
            if (badge) {
                badge.innerText = cartItemsCount;
                badge.style.display = cartItemsCount > 0 ? 'flex' : 'flex';
            }
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) wBadge.innerText = wishlistCount;
>>>>>>> f12e814d1d386a89c48d970cc87df82e53dbb6fd
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

<<<<<<< HEAD
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
=======
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

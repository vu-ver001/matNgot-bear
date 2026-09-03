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

    <!-- Vite Scripts & Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    <!-- Component Footer (Hidden on Checkout, Cart, Payment, and Orders) -->
    @if (!request()->routeIs('customer.cart*') && !request()->routeIs('customer.checkout*') && !request()->routeIs('customer.payment.*') && !request()->routeIs('customer.orders.*') && !isset($hideFooter))
        @include('partials.footer')
    @endif

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
                badge.innerText = cartItemsCount > 99 ? '99+' : cartItemsCount;
                badge.style.display = cartItemsCount > 0 ? 'flex' : 'none';
            }
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) {
                wBadge.innerText = wishlistCount > 99 ? '99+' : wishlistCount;
                wBadge.style.display = wishlistCount > 0 ? 'flex' : 'none';
            }
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

        window.userRole = "{{ auth()->check() ? auth()->user()->role : 'GUEST' }}";
        window.isCustomerAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

        function addToCart(productId, productName = 'Gấu bông', qty = 1, redirectMode = false) {
            // 1. Chưa đăng nhập: Bật Popup Đăng nhập
            if (!window.isCustomerAuthenticated) {
                if (redirectMode === 'checkout' || redirectMode === true) {
                    const targetCheckoutUrl = "{{ route('customer.checkout.index') }}?product_id=" + productId + "&quantity=" + qty;
                    openAuthModal(targetCheckoutUrl, 'Đăng nhập để Mua ngay', 'Vui lòng đăng nhập hoặc đăng ký tài khoản Mật Ngọt Bear để tiến hành thanh toán đơn hàng ngay bạn nhé!');
                } else {
                    openAuthModal(window.location.href, 'Đăng nhập để Thêm vào giỏ', 'Vui lòng đăng nhập hoặc tạo tài khoản Mật Ngọt Bear để thêm sản phẩm vào giỏ hàng và tích lũy ưu đãi!');
                }
                return false;
            }

            // 2. Tài khoản Nhân viên (STAFF) hoặc Quản trị viên (ADMIN): Không được mua hàng
            if (window.userRole === 'STAFF' || window.userRole === 'ADMIN') {
                const roleName = window.userRole === 'ADMIN' ? 'Quản Trị Viên (Admin)' : 'Nhân Viên (Staff)';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Không khả dụng cho ' + roleName,
                        html: `Tài khoản <strong>${roleName}</strong> chỉ dùng để quản lý hệ thống, không có chức năng thêm vào giỏ hàng hay đặt hàng.<br><br>Vui lòng chuyển sang tài khoản <strong>Khách hàng</strong> để trải nghiệm mua sắm!`,
                        confirmButtonColor: '#E08A1E',
                        confirmButtonText: 'Đã hiểu'
                    });
                } else {
                    alert(`Tài khoản ${roleName} không có chức năng thêm sản phẩm vào giỏ hàng hay đặt hàng.`);
                }
                return false;
            }

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
                .then(response => {
                    if (response.status === 401) {
                        openAuthModal(window.location.href, 'Đăng nhập để Thêm vào giỏ');
                        throw new Error('Unauthenticated');
                    }
                    return response.json();
                })
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
                    if (error.message !== 'Unauthenticated') {
                        console.error('Error adding to cart:', error);
                        Toast.fire({
                            icon: 'error',
                            title: 'Có lỗi xảy ra khi thêm vào giỏ hàng!'
                        });
                    }
                    return false;
                });
        }

        function showToastCart() {
            if (!window.isCustomerAuthenticated) {
                openAuthModal("{{ route('customer.cart') }}", 'Đăng nhập xem Giỏ hàng', 'Vui lòng đăng nhập tài khoản Mật Ngọt Bear để xem và quản lý giỏ hàng của bạn!');
                return;
            }
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

        function saveWishlist(list) {
            localStorage.setItem('mn_wishlist_items', JSON.stringify(list));
            wishlistCount = list.length;
            localStorage.setItem('mn_wishlist_count', wishlistCount);
            updateWishlistBadge();
        }

        function isInWishlist(productId) {
            const list = getWishlist();
            return list.some(item => item.id == productId);
        }

        function toggleWishlist(productId, productName = 'Gấu bông', price = '', image = '', e = null) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            let list = getWishlist();
            const index = list.findIndex(item => item.id == productId);

            if (index > -1) {
                list.splice(index, 1);
                saveWishlist(list);
                updateHeartIcons(productId, false);
                Toast.fire({
                    icon: 'info',
                    title: `Đã bỏ "${productName}" khỏi danh sách yêu thích`
                });
            } else {
                list.push({
                    id: productId,
                    name: productName,
                    price: price,
                    image: image
                });
                saveWishlist(list);
                updateHeartIcons(productId, true);
                Toast.fire({
                    icon: 'success',
                    title: `Đã lưu "${productName}" vào yêu thích! ❤️`
                });
            }
        }

        function updateHeartIcons(productId, isFav) {
            document.querySelectorAll(`[data-wishlist-btn="${productId}"]`).forEach(btn => {
                const icon = btn.querySelector('i');
                if (icon) {
                    if (isFav) {
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                        btn.style.color = '#E08A1E';
                    } else {
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                        btn.style.color = '';
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const list = getWishlist();
            list.forEach(item => {
                updateHeartIcons(item.id, true);
            });
        });

        function updateWishlistBadge() {
            const items = getWishlist();
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) wBadge.innerText = items.length;
        }

        // Global Auth Modal Helper
        window.isCustomerAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

        function openAuthModal(targetUrl = null, customTitle = null, customDesc = null) {
            const modal = document.getElementById('mn-auth-modal');
            if (!modal) return;
            
            const loginBtn = document.getElementById('mn-auth-login-btn');
            const registerBtn = document.getElementById('mn-auth-register-btn');
            const titleEl = document.getElementById('mn-auth-modal-title');
            const descEl = document.getElementById('mn-auth-modal-desc');
            const baseLogin = "{{ route('login') }}";
            const baseRegister = "{{ route('register') }}";
            
            if (customTitle && titleEl) {
                titleEl.innerText = customTitle;
            } else if (titleEl) {
                titleEl.innerText = 'Đăng nhập tài khoản';
            }

            if (customDesc && descEl) {
                descEl.innerText = customDesc;
            } else if (descEl) {
                descEl.innerHTML = 'Đăng nhập hoặc tạo tài khoản <strong class="text-[#2B1810]">Mật Ngọt Bear</strong> để thêm vào giỏ hàng, mua hàng và tích lũy ưu đãi!';
            }

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

            <h3 id="mn-auth-modal-title" class="text-xl sm:text-2xl font-black text-[#2B1810] mb-2 tracking-tight">
                Đăng nhập tài khoản
            </h3>
            <p id="mn-auth-modal-desc" class="text-xs sm:text-sm text-[#7D6B5D] leading-relaxed mb-5 font-medium">
                Đăng nhập hoặc tạo tài khoản <strong class="text-[#2B1810]">Mật Ngọt Bear</strong> để thêm vào giỏ hàng, mua hàng và tích lũy ưu đãi!
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

    @yield('scripts')
</body>
</html>

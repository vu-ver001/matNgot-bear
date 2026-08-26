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

        // Cart & Wishlist state
        let cartItemsCount = {{ (int) ($realCartCount ?? 0) }};
        let wishlistCount = parseInt(localStorage.getItem('mn_wishlist_count') || '0');
        updateCartBadge();

        function updateCartBadge() {
            const badge = document.getElementById('cart-count');
            if (badge) badge.innerText = cartItemsCount;
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) wBadge.innerText = wishlistCount;
        }

        function addToCart(productId, productName = 'Gấu bông', qty = 1, redirectAfter = false) {
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
                        Toast.fire({
                            icon: 'success',
                            title: `Đã thêm "${productName}" vào giỏ hàng!`
                        });

                        if (redirectAfter) {
                            setTimeout(() => {
                                window.location.href = "{{ route('customer.cart') }}";
                            }, 300);
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

        function showToastWishlist() {
            wishlistCount++;
            localStorage.setItem('mn_wishlist_count', wishlistCount);
            updateCartBadge();
            Toast.fire({
                icon: 'success',
                title: 'Đã lưu vào danh sách yêu thích!'
            });
        }
    </script>
    @yield('scripts')
</body>
</html>

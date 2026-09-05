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

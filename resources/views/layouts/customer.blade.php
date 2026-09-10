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
    @stack('styles')
</head>
<body>



    <!-- Main Header -->
    <header class="site-header">
        <!-- Component Header (Không gồm thanh danh mục con) -->
        @include('partials.header')

        <!-- Navigation Menu Row with Mega Menu (Image 1 Style) (Hidden on Cart & Checkout) -->
        @if (!request()->routeIs('customer.cart*') && !request()->routeIs('customer.checkout*') && !request()->routeIs('customer.payment.*'))
        <nav class="nav-bar">
            <div class="nav-container">
                @php
                    $pinnedCategories = \App\Models\Category::where('is_pinned', true)
                        ->where('is_active', true)
                        ->orderBy('id', 'asc')
                        ->get();
                    $activeProductsMap = \App\Models\Product::where('status', 'ACTIVE')->pluck('name', 'id')->toArray();
                @endphp
                <ul class="nav-menu">
                    <!-- 1. CỐ ĐỊNH: TRANG CHỦ -->
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-house"></i> TRANG CHỦ
                        </a>
                    </li>

                    <!-- 2. CÁC DANH MỤC Ở GIỮA ĐƯỢC GHIM TRONG QUẢN LÝ DANH MỤC -->
                    @foreach($pinnedCategories as $cat)
                        @php
                            $normalizedName = strtoupper(trim($cat->name));
                            $menuConfig = $cat->header_menu_config;
                            $validCols = [];

                            if (!empty($menuConfig) && is_array($menuConfig)) {
                                foreach (array_slice($menuConfig, 0, 4) as $col) {
                                    $colItems = [];
                                    foreach (array_slice($col['items'] ?? [], 0, 3) as $item) {
                                        $pId = $item['product_id'] ?? null;
                                        if ($pId) {
                                            // CHỈ LẤY SẢN PHẨM ĐANG Ở TRẠNG THÁI ACTIVE
                                            if (isset($activeProductsMap[$pId])) {
                                                $colItems[] = [
                                                    'name' => !empty($item['name']) ? $item['name'] : $activeProductsMap[$pId],
                                                    'url'  => route('products.show', $pId),
                                                ];
                                            }
                                        } elseif (!empty($item['url'])) {
                                            $colItems[] = [
                                                'name' => $item['name'] ?? 'Xem chi tiết',
                                                'url'  => $item['url'],
                                            ];
                                        }
                                    }
                                    // NẾU CỘT NÀY CÓ ÍT NHẤT 1 SẢN PHẨM ACTIVE THÌ MỚI HIỂN THỊ CỘT NÀY
                                    if (count($colItems) > 0) {
                                        $validCols[] = [
                                            'title' => $col['title'] ?? 'BỘ SƯU TẬP',
                                            'items' => $colItems,
                                        ];
                                    }
                                }
                            }
                        @endphp

                        @if(!empty($validCols) && count($validCols) > 0)
                            <!-- MEGAMENU ĐỘNG ĐƯỢC ADMIN CẤU HÌNH (CHỈ HIỂN THỊ CÁC CỘT CÓ SẢN PHẨM ACTIVE) -->
                            <li class="nav-item has-megamenu">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }} <i class="fa-solid fa-chevron-down"></i>
                                </a>
                                <div class="megamenu-panel">
                                    <div class="megamenu-container" style="grid-template-columns: repeat({{ min(4, count($validCols)) }}, 1fr);">
                                        @foreach($validCols as $col)
                                            <div class="megamenu-col">
                                                <div class="megamenu-heading">
                                                    <i class="fa-solid fa-paw" style="color: var(--honey-dark); font-size: 11px;"></i>
                                                    {{ $col['title'] ?? 'BỘ SƯU TẬP' }}
                                                </div>
                                                <ul class="megamenu-list">
                                                    @foreach($col['items'] as $item)
                                                        <li>
                                                            <a href="{{ $item['url'] }}" class="megamenu-link">
                                                                <i class="fa-solid fa-angle-right"></i> {{ $item['name'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                        @elseif(!empty($menuConfig) && is_array($menuConfig))
                            <!-- Nếu danh mục có cấu hình menu nhưng tất cả sản phẩm đều bị tạm ẩn thì hiển thị nút bấm danh mục thường, không có dropdown menu rỗng -->
                            <li class="nav-item">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }}
                                </a>
                            </li>

                        @elseif($normalizedName === 'TEDDY CLASSIC')
                            <!-- TEDDY CLASSIC (Mega Dropdown) -->
                            <li class="nav-item has-megamenu">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }} <i class="fa-solid fa-chevron-down"></i>
                                </a>
                                <div class="megamenu-panel">
                                    <div class="megamenu-container">
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">GẤU BÔNG TEDDY CAO CẤP</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gấu Bông Teddy Socola', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy Socola</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gấu Bông Teddy Logo Baby', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Teddy Logo Baby</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gấu Teddy Boy Đeo Nơ', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Teddy Boy Đeo Nơ</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">KÍCH THƯỚC KHỦNG</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '1m8', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m8 – 2m</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '1m7', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m7</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '1m6', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Teddy 1m6</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">MÀU SẮC ĐƯỢC YÊU THÍCH</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'search' => 'Socola', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Nâu Socola Quý Phái</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'search' => 'Vàng Kem', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Vàng Bơ Kem Sữa</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'search' => 'Hồng', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hồng Pastel Ngọt Ngào</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">COMBO QUÀ TẶNG</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Gấu Teddy Thắt Nơ</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Thiệp Viết Tay Kèm Hoa</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Xịt Nước Hoa Thơm Lâu</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        @elseif($normalizedName === 'BUTTER BEAR')
                            <!-- BUTTER BEAR (Mega Dropdown) -->
                            <li class="nav-item has-megamenu">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }} <i class="fa-solid fa-chevron-down"></i>
                                </a>
                                <div class="megamenu-panel">
                                    <div class="megamenu-container">
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">BUTTER BEAR NỔI BẬT</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gấu Bơ Đội Mũ Bơ', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Đội Mũ Bơ</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Butter Bear Váy Hồng', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Váy Hồng</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Butter Bear Cầm Bánh Mì', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear Cầm Bánh Mì</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">KÍCH THƯỚC PHỔ BIẾN</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '35cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 35cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '45cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 45cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '60cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Butter Bear 60cm</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">PHONG CÁCH</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'search' => 'Má Hồng', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Má Hồng Dễ Thương</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'search' => 'Váy', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Công Chúa Váy Xòe</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'search' => 'Bánh Mì', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Bé Bơ Ăn Sáng Cute</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">COMBO QUÀ TẶNG</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Quà Sinh Nhật Gấu Bơ</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Combo Kèm Đèn Led Đom Đóm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Trong Suốt Quý Phái</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        @elseif($normalizedName === 'TEDDY MR. BEAN')
                            <!-- TEDDY MR. BEAN (Mega Dropdown) -->
                            <li class="nav-item has-megamenu">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }} <i class="fa-solid fa-chevron-down"></i>
                                </a>
                                <div class="megamenu-panel">
                                    <div class="megamenu-container">
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">MR. BEAN NỔI BẬT</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gấu Bông Mr. Bean Mắt Cúc', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Mắt Cúc 40cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Mr. Bean To 80cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Khổng Lồ 80cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Combo Mr. Bean Mini', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Quà Vintage Mini</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">CHẤT LIỆU VINTAGE</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Len Dệt Sợi Thô Cổ Điển</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Nơ Kẻ Sọc Vintage</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mắt Cúc Áo Thủ Công Chuẩn Phim</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">KÍCH CỠ ĐA DẠNG</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '25cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Size Mini 25cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '40cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Size Chuẩn 40cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '80cm', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Size Ôm 80cm</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">QUÀ TẶNG KỶ NIỆM</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Giấy Kraft Mộc</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Thiệp Chúc Mừng Vintage</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Quà Tặng Độc Đáo Fan Phim</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        @elseif($normalizedName === 'TEDDY COUPLE')
                            <!-- TEDDY COUPLE (Dropdown) -->
                            <li class="nav-item has-dropdown">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }} <i class="fa-solid fa-chevron-down"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Cặp Gấu Cô Dâu Chú Rể', 'sort' => 'best_seller']) }}#catalog-layout" class="dropdown-item">
                                        <i class="fa-solid fa-rings-wedding" style="color: #E91E63; margin-right: 8px;"></i> Cặp Gấu Cô Dâu Chú Rể
                                    </a>
                                    <a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Cặp Gấu Áo Đôi Trái Tim', 'sort' => 'best_seller']) }}#catalog-layout" class="dropdown-item">
                                        <i class="fa-solid fa-heart" style="color: #D32F2F; margin-right: 8px;"></i> Cặp Gấu Áo Đôi Trái Tim
                                    </a>
                                    <a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Set Gấu Ôm Bó Hoa Kỷ Niệm', 'sort' => 'best_seller']) }}#catalog-layout" class="dropdown-item">
                                        <i class="fa-solid fa-spa" style="color: #E59819; margin-right: 8px;"></i> Set Gấu Ôm Bó Hoa Kỷ Niệm
                                    </a>
                                </div>
                            </li>

                        @elseif($normalizedName === 'GỐI BÔNG TEDDY')
                            <!-- GỐI BÔNG TEDDY (Mega Dropdown) -->
                            <li class="nav-item has-megamenu">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }} <i class="fa-solid fa-chevron-down"></i>
                                </a>
                                <div class="megamenu-panel">
                                    <div class="megamenu-container">
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">GỐI ÔM DÀI</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gối Ôm Dài 1m2 - 1m5', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Dài Teddy 1m2 – 1m5</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'size' => '1m2', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Dáng Nằm Siêu Êm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Ôm Ruột Bông Bi Thái</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">GỐI TỰA & VĂN PHÒNG</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gối Tựa Lưng Mặt Gấu', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Tựa Lưng Mặt Gấu 40cm</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Tựa Êm Ái Chống Đau Lưng</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Đệm Lưng Có Quai Cố Định Ghế</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">GỐI MỀN 2 TRONG 1</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sub' => 'Gối Mền 2 Trong 1', 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Mền Gấu Kèm Chăn 1m6</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Chăn Nỉ Tuyết Ấm Áp Tiện Lợi</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gối Du Lịch & Văn Phòng</a></li>
                                            </ul>
                                        </div>
                                        <div class="megamenu-col">
                                            <div class="megamenu-heading">TIỆN ÍCH PHÒNG NGỦ</div>
                                            <ul class="megamenu-list">
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Vải Nhung Spandex Co Giãn 4 Chiều</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Kháng Khuẩn Không Xẹp Lún</a></li>
                                                <li><a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Giặt Máy Thoải Mái</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        @else
                            <!-- DANH MỤC KHÁC ĐƯỢC GHIM (VÍ DỤ: TEDDY IU HOẶC DANH MỤC MỚI) -->
                            @php
                                $catProducts = $cat->products()->where('status', 'ACTIVE')->take(5)->get();
                            @endphp
                            <li class="nav-item {{ $catProducts->count() > 0 ? 'has-dropdown' : '' }}">
                                <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="nav-link {{ request('category_id') == $cat->id ? 'active' : '' }}">
                                    {{ $cat->name }}
                                    @if($catProducts->count() > 0)
                                        <i class="fa-solid fa-chevron-down"></i>
                                    @endif
                                </a>
                                @if($catProducts->count() > 0)
                                    <div class="dropdown-menu">
                                        @foreach($catProducts as $cp)
                                            <a href="{{ route('products.show', $cp->id) }}" class="dropdown-item">
                                                <i class="fa-solid fa-paw" style="color: var(--honey-dark); margin-right: 8px;"></i>
                                                {{ Str::limit($cp->name, 28) }}
                                            </a>
                                        @endforeach
                                        <a href="{{ route('products.index', ['category_id' => $cat->id, 'sort' => 'best_seller']) }}#catalog-layout" class="dropdown-item" style="font-weight: 700; color: var(--primary); border-top: 1px dashed var(--border-light);">
                                            <i class="fa-solid fa-arrow-right" style="margin-right: 8px;"></i> Xem tất cả sản phẩm
                                        </a>
                                    </div>
                                @endif
                            </li>
                        @endif
                    @endforeach

                    <!-- 3. CỐ ĐỊNH: TẤT CẢ SẢN PHẨM -->
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}#catalog-layout" class="nav-link {{ request()->routeIs('products.index') && !request('category_id') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> TẤT CẢ SẢN PHẨM
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        @endif
    </header>

    <!-- Main Content Body -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Nút Cuộn Lên Đầu Trang (Back to Top) -->
    <button type="button" id="backToTopBtn" class="back-to-top-btn" onclick="scrollToTop()" title="Lên đầu trang" aria-label="Lên đầu trang">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <!-- Component Footer (Hidden on Checkout, Cart, Payment, and Orders) -->
    @if (!request()->routeIs('customer.cart*') && !request()->routeIs('customer.checkout*') && !request()->routeIs('customer.payment.*') && !request()->routeIs('customer.orders.*') && !isset($hideFooter))
        @include('partials.footer')
    @endif

    <!-- Global Scripts -->
    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        window.addEventListener('scroll', () => {
            const btn = document.getElementById('backToTopBtn');
            if (!btn) return;
            if (window.scrollY > 350) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        });

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

        // Xử lý khi click vào các liên kết danh mục gấu bông & tất cả sản phẩm có #catalog-layout
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href*="#catalog-layout"]');
            if (!link) return;

            const url = new URL(link.href, window.location.origin);
            const currentPath = window.location.pathname.replace(/\/$/, '');
            const targetPath = url.pathname.replace(/\/$/, '');

            if (currentPath === targetPath) {
                // Đang ở cùng trang /products
                if (url.search === window.location.search) {
                    // Cùng tham số lọc, cuộn mượt xuống #catalog-layout
                    e.preventDefault();
                    const target = document.getElementById('catalog-layout');
                    if (target) {
                        const header = document.querySelector('header.site-header');
                        const headerHeight = header ? header.getBoundingClientRect().height : 125;
                        const targetY = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 15;
                        window.scrollTo({
                            top: Math.max(0, targetY),
                            behavior: 'smooth'
                        });
                    }
                }
            }
        });

        // Guest Cart Manager
        function getGuestCart() {
            try {
                return JSON.parse(localStorage.getItem('mn_guest_cart') || '[]');
            } catch(e) {
                return [];
            }
        }

        window.userRole = "{{ auth()->check() ? auth()->user()->role : 'GUEST' }}";
        window.isCustomerAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        window.dbWishlistProductIds = @json(auth()->check() ? \App\Models\WishlistItem::where('user_id', auth()->id())->pluck('product_id')->map(fn($id) => (int)$id)->toArray() : []);

        // Cart & Wishlist state (distinct product count)
        let cartItemsCount = window.isCustomerAuthenticated ? {{ (int) ($realCartCount ?? 0) }} : 0;
        let wishlistCount = window.isCustomerAuthenticated ? {{ (int) ($realWishlistCount ?? 0) }} : 0;
        updateCartBadge();
        updateWishlistBadge();

        function saveGuestCart(cart) {
            localStorage.setItem('mn_guest_cart', JSON.stringify(cart));
            cartItemsCount = window.isCustomerAuthenticated ? cart.length : 0;
            updateCartBadge();
        }

        function updateCartBadge() {
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.innerText = cartItemsCount > 99 ? '99+' : cartItemsCount;
                badge.style.display = 'flex';
            }
            updateWishlistBadge();
        }

        function updateWishlistBadge() {
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) {
                wBadge.innerText = wishlistCount > 99 ? '99+' : wishlistCount;
                wBadge.style.display = 'flex';
            }
        }

        function fetchCartCount() {
            if (window.isCustomerAuthenticated) {
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
        }

        function addToCart(productId, productName = 'Gấu bông', qty = 1, redirectMode = false) {
            // Tài khoản Nhân viên (STAFF) hoặc Quản trị viên (ADMIN): Không được mua hàng
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

            // 1. Kiểm tra nếu khách chưa đăng nhập (khách vãng lai):
            // Không được thêm vào giỏ hàng hay mua ngay -> Hiển thị Popup Đăng nhập đồng bộ như yêu cầu
            if (!window.isCustomerAuthenticated) {
                if (redirectMode === 'checkout' || redirectMode === true) {
                    const targetCheckoutUrl = "{{ route('customer.checkout.index') }}?product_id=" + productId + "&quantity=" + qty;
                    openAuthModal(
                        targetCheckoutUrl, 
                        'Đăng nhập để Mua ngay', 
                        'Vui lòng đăng nhập hoặc đăng ký tài khoản Mật Ngọt Bear để tiến hành thanh toán đơn hàng ngay bạn nhé!'
                    );
                } else {
                    openAuthModal(
                        window.location.href, 
                        'Đăng nhập để thêm vào giỏ hàng', 
                        'Vui lòng đăng nhập tài khoản Mật Ngọt Bear để thêm sản phẩm vào giỏ hàng của bạn bạn nhé!'
                    );
                }
                return false;
            }

            // 2. Nếu khách đã đăng nhập: Lưu vào CSDL
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
                        openAuthModal(
                            window.location.href, 
                            'Đăng nhập để thêm vào giỏ hàng', 
                            'Vui lòng đăng nhập tài khoản Mật Ngọt Bear để thêm sản phẩm vào giỏ hàng của bạn bạn nhé!'
                        );
                        throw new Error('Unauthenticated');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (data.cart_count !== undefined) {
                            cartItemsCount = data.cart_count;
                        }
                        updateCartBadge();

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
                    } else {
                        Toast.fire({
                            icon: 'warning',
                            title: data.message || 'Không thể thêm sản phẩm vào giỏ hàng.'
                        });
                    }
                })
                .catch(err => {
                    console.error('Lỗi khi thêm giỏ hàng:', err);
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

        // ================= WISHLIST MANAGER (ĂN LIỀN VỚI CSDL) =================
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
            const pid = parseInt(productId);
            if (window.isCustomerAuthenticated && Array.isArray(window.dbWishlistProductIds)) {
                return window.dbWishlistProductIds.includes(pid);
            }
            const list = getWishlist();
            return list.some(item => item.id == pid);
        }

        function updateHeartIcons(productId, isFav) {
            const pid = parseInt(productId);
            document.querySelectorAll(`[data-product-id="${pid}"], [data-wishlist-btn="${pid}"]`).forEach(btn => {
                const icon = btn.querySelector('i');
                if (icon) {
                    if (isFav) {
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                        btn.classList.add('active');
                        btn.style.color = '#E57373';
                    } else {
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                        btn.classList.remove('active');
                        btn.style.color = '';
                    }
                }
            });
        }

        function syncAllHeartIcons() {
            if (window.isCustomerAuthenticated && Array.isArray(window.dbWishlistProductIds)) {
                window.dbWishlistProductIds.forEach(id => {
                    updateHeartIcons(id, true);
                });
            } else {
                const list = getWishlist();
                list.forEach(item => {
                    if (item && item.id) {
                        updateHeartIcons(item.id, true);
                    }
                });
            }
        }

        function toggleWishlist(param1, param2 = null, price = '', image = '', e = null) {
            let id, name, eventObj;

            if (typeof param1 === 'object' && param1 !== null && param1.id) {
                id = parseInt(param1.id);
                name = param1.name || 'Gấu bông';
                eventObj = param2;
            } else {
                id = parseInt(param1);
                name = param2 || 'Gấu bông';
                eventObj = e;
            }

            if (eventObj && typeof eventObj.stopPropagation === 'function') {
                eventObj.preventDefault();
                eventObj.stopPropagation();
            }

            // Tài khoản STAFF hoặc ADMIN: Không có chức năng yêu thích
            if (window.userRole === 'STAFF' || window.userRole === 'ADMIN') {
                return false;
            }

            // 1. Nếu khách chưa đăng nhập: Mở popup đăng nhập để lưu trực tiếp vào CSDL
            if (!window.isCustomerAuthenticated) {
                openAuthModal(
                    window.location.href, 
                    'Đăng nhập để lưu yêu thích', 
                    'Vui lòng đăng nhập tài khoản Mật Ngọt Bear để lưu sản phẩm vào danh sách yêu thích của bạn bạn nhé!'
                );
                return false;
            }

            // 2. Nếu đã đăng nhập: Toggle trực tiếp vào CSDL bảng wishlist_items
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const isCurrentlyFav = Array.isArray(window.dbWishlistProductIds) && window.dbWishlistProductIds.includes(id);

            // Optimistic UI toggle: đổi màu ngay lập tức cho thao tác cực mượt
            updateHeartIcons(id, !isCurrentlyFav);

            return fetch('{{ route('customer.wishlist.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ product_id: id })
            })
            .then(res => {
                if (res.status === 401) {
                    updateHeartIcons(id, isCurrentlyFav);
                    openAuthModal(window.location.href, 'Đăng nhập để lưu yêu thích');
                    throw new Error('Unauthenticated');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const isFav = (data.action === 'added');
                    updateHeartIcons(id, isFav);

                    if (data.wishlist_count !== undefined) {
                        wishlistCount = data.wishlist_count;
                        updateWishlistBadge();
                    }

                    if (Array.isArray(window.dbWishlistProductIds)) {
                        const idx = window.dbWishlistProductIds.indexOf(id);
                        if (isFav && idx === -1) {
                            window.dbWishlistProductIds.push(id);
                        } else if (!isFav && idx > -1) {
                            window.dbWishlistProductIds.splice(idx, 1);
                        }
                    }

                    Toast.fire({
                        icon: isFav ? 'success' : 'info',
                        title: isFav ? `Đã lưu "${name}" vào yêu thích! ❤️` : `Đã bỏ "${name}" khỏi yêu thích`
                    });
                } else {
                    updateHeartIcons(id, isCurrentlyFav);
                    Toast.fire({
                        icon: 'warning',
                        title: data.message || 'Không thể cập nhật danh sách yêu thích.'
                    });
                }
            })
            .catch(err => {
                updateHeartIcons(id, isCurrentlyFav);
                console.error('Lỗi khi cập nhật yêu thích trong CSDL:', err);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            syncAllHeartIcons();
            updateWishlistBadge();
            updateCartBadge();
        });

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
            }, 300);
        }

        // ==========================================================
        // GLOBAL QUICK VARIANT SELECTION MODAL (Ảnh 3)
        // ==========================================================
        let qvProduct = null;
        let qvSelectedSize = '';
        let qvSelectedColor = '';
        let qvMaxStock = 1;

        async function openQuickVariantModal(productId) {
            const modal = document.getElementById('quick-variant-modal');
            const loading = document.getElementById('quick-variant-loading');
            const content = document.getElementById('quick-variant-content');
            if (!modal) return;

            modal.style.display = 'flex';
            requestAnimationFrame(() => modal.classList.add('show'));
            if (loading) loading.style.display = 'block';
            if (content) content.style.display = 'none';

            try {
                const res = await fetch(`/api/products/${productId}`);
                const json = await res.json();
                if (!json.success || !json.data) {
                    throw new Error(json.message || 'Không thể tải sản phẩm');
                }

                qvProduct = json.data;
                qvMaxStock = qvProduct.stock_quantity || 1;

                // Ảnh
                const primaryImg = (qvProduct.images && qvProduct.images.find(i => i.is_primary)) || (qvProduct.images && qvProduct.images[0]) || null;
                const imgUrl = (primaryImg && primaryImg.image_url) ? primaryImg.image_url : 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=' + encodeURIComponent(qvProduct.name);
                document.getElementById('qv-img').src = imgUrl;

                // Tên & Danh mục
                document.getElementById('qv-cat').innerText = (qvProduct.category && qvProduct.category.name) ? qvProduct.category.name : 'GẤU BÔNG';
                document.getElementById('qv-title').innerText = qvProduct.name;

                // Giá
                const price = Number(qvProduct.price || 0);
                const salePrice = qvProduct.sale_price ? Number(qvProduct.sale_price) : null;
                const isOnSale = qvProduct.is_on_sale !== undefined ? Boolean(qvProduct.is_on_sale) : (salePrice !== null && salePrice < price);
                const curPriceEl = document.getElementById('qv-price-cur');
                const oldPriceEl = document.getElementById('qv-price-old');

                if (isOnSale && salePrice) {
                    curPriceEl.innerText = salePrice.toLocaleString('vi-VN') + ' đ';
                    oldPriceEl.innerText = price.toLocaleString('vi-VN') + ' đ';
                    oldPriceEl.style.display = 'inline-block';
                } else {
                    curPriceEl.innerText = price.toLocaleString('vi-VN') + ' đ';
                    oldPriceEl.style.display = 'none';
                }

                // Render Size Chips từ DB
                const sizeWrap = document.getElementById('qv-size-chips');
                const sizes = (qvProduct.available_sizes && qvProduct.available_sizes.length) ? qvProduct.available_sizes : [qvProduct.size || 'Size chuẩn'];
                qvSelectedSize = sizes.includes(qvProduct.size) ? qvProduct.size : sizes[0];
                document.getElementById('qv-selected-size-label').innerText = qvSelectedSize;

                sizeWrap.innerHTML = sizes.map(sz => `
                    <button type="button" class="variant-chip ${sz === qvSelectedSize ? 'active' : ''}" onclick="selectQvSize('${sz.replace(/'/g, "\\'")}', this)">
                        <i class="fa-solid fa-check chip-check-icon"></i>
                        <span>${sz}</span>
                    </button>
                `).join('');

                // Render Color Chips từ DB
                const colorWrap = document.getElementById('qv-color-chips');
                const colors = (qvProduct.available_colors && qvProduct.available_colors.length) ? qvProduct.available_colors : [qvProduct.color || 'Tự nhiên'];
                qvSelectedColor = colors.includes(qvProduct.color) ? qvProduct.color : colors[0];
                document.getElementById('qv-selected-color-label').innerText = qvSelectedColor;

                colorWrap.innerHTML = colors.map(cl => `
                    <button type="button" class="variant-chip variant-color-chip ${cl === qvSelectedColor ? 'active' : ''}" onclick="selectQvColor('${cl.replace(/'/g, "\\'")}', this)">
                        <span class="color-dot-indicator"></span>
                        <span>${cl}</span>
                        <i class="fa-solid fa-check chip-check-icon"></i>
                    </button>
                `).join('');

                // Số lượng
                document.getElementById('qv-qty-input').value = 1;

                if (loading) loading.style.display = 'none';
                if (content) content.style.display = 'block';
            } catch (e) {
                console.error('Lỗi khi mở modal biến thể:', e);
                closeQuickVariantModal();
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'error', title: 'Không thể mở tùy chọn sản phẩm.' });
                }
            }
        }

        function closeQuickVariantModal() {
            const modal = document.getElementById('quick-variant-modal');
            if (!modal) return;
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }

        function selectQvSize(size, el) {
            qvSelectedSize = size;
            document.getElementById('qv-selected-size-label').innerText = size;
            document.querySelectorAll('#qv-size-chips .variant-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
        }

        function selectQvColor(color, el) {
            qvSelectedColor = color;
            document.getElementById('qv-selected-color-label').innerText = color;
            document.querySelectorAll('#qv-color-chips .variant-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
        }

        function changeQvQty(delta) {
            const input = document.getElementById('qv-qty-input');
            let val = (parseInt(input.value) || 1) + delta;
            if (val < 1) val = 1;
            if (qvMaxStock > 0 && val > qvMaxStock) {
                val = qvMaxStock;
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'warning', title: `Chỉ còn ${qvMaxStock} sản phẩm trong kho!` });
                }
            }
            input.value = val;
        }

        function handleQvAddToCart() {
            if (!qvProduct) return;
            const qty = parseInt(document.getElementById('qv-qty-input').value) || 1;
            closeQuickVariantModal();
            addToCart(qvProduct.id, qvProduct.name, qty);
        }

        function handleQvBuyNow() {
            if (!qvProduct) return;
            const qty = parseInt(document.getElementById('qv-qty-input').value) || 1;
            closeQuickVariantModal();
            addToCart(qvProduct.id, qvProduct.name, qty, 'checkout');
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

    {{-- Global Quick Variant Selection Modal (Ảnh 3) --}}
    <div id="quick-variant-modal" onclick="if(event.target === this) closeQuickVariantModal();">
        <div class="quick-variant-card">
            <button type="button" class="quick-variant-close-btn" onclick="closeQuickVariantModal()" title="Đóng">✕</button>
            
            <div id="quick-variant-loading" style="text-align: center; padding: 2.5rem 1rem;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 32px; color: var(--honey);"></i>
                <div style="margin-top: 10px; font-weight: 700; color: var(--text-muted); font-size: 13.5px;">Đang tải thông tin sản phẩm...</div>
            </div>

            <div id="quick-variant-content" style="display: none;">
                <div class="quick-variant-header">
                    <div class="quick-variant-img-wrap">
                        <img id="qv-img" src="" alt="Sản phẩm">
                    </div>
                    <div class="quick-variant-info">
                        <div class="quick-variant-cat" id="qv-cat">GẤU BÔNG</div>
                        <h4 class="quick-variant-title" id="qv-title">Tên sản phẩm</h4>
                        <div class="quick-variant-prices">
                            <span class="quick-variant-price-cur" id="qv-price-cur">0 đ</span>
                            <span class="quick-variant-price-old" id="qv-price-old" style="display: none;">0 đ</span>
                        </div>
                    </div>
                </div>

                <div class="quick-variant-body">
                    <!-- Chọn Kích Thước Lấy Từ CSDL -->
                    <div class="variant-group">
                        <div class="variant-label-row">
                            <span class="variant-title"><i class="fa-solid fa-ruler-combined" style="color: var(--honey-dark);"></i> Chọn Kích Thước:</span>
                            <span class="variant-selected-value" id="qv-selected-size-label">Size chuẩn</span>
                        </div>
                        <div class="variant-chips-wrap" id="qv-size-chips"></div>
                    </div>

                    <!-- Chọn Màu Sắc Lấy Từ CSDL -->
                    <div class="variant-group">
                        <div class="variant-label-row">
                            <span class="variant-title"><i class="fa-solid fa-palette" style="color: var(--honey-dark);"></i> Chọn Màu Sắc:</span>
                            <span class="variant-selected-value" id="qv-selected-color-label">Tự nhiên</span>
                        </div>
                        <div class="variant-chips-wrap" id="qv-color-chips"></div>
                    </div>

                    <!-- Chọn Số Lượng -->
                    <div class="quick-variant-qty-row">
                        <div style="font-size: 13px; font-weight: 700; color: var(--text-main);">Số lượng:</div>
                        <div class="qty-selector">
                            <button type="button" class="qty-btn" onclick="changeQvQty(-1)">-</button>
                            <input type="number" id="qv-qty-input" class="qty-input" value="1" min="1" readonly>
                            <button type="button" class="qty-btn" onclick="changeQvQty(1)">+</button>
                        </div>
                    </div>

                    <!-- 2 Nút Hành Động: Thêm Vào Giỏ & Mua Ngay -->
                    <div class="quick-variant-actions">
                        <button type="button" class="btn-qv-cart" onclick="handleQvAddToCart()">
                            <i class="fa-solid fa-bag-shopping"></i> Thêm Vào Giỏ
                        </button>
                        <button type="button" class="btn-qv-buy" onclick="handleQvBuyNow()">
                            <i class="fa-solid fa-bolt"></i> Mua Ngay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @yield('scripts')
    @stack('scripts')
</body>
</html>

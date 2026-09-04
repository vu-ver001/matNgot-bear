<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Quản Trị Hệ Thống') - Mật Ngọt Bear</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script áp dụng trạng thái Mini Sidebar ngay lập tức trước khi render HTML để triệt tiêu độ khựng -->
    <script>
        (function() {
            if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('admin-sidebar-collapsed');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Admin Dashboard Layout CSS (Tách riêng bởi Khánh Vân) -->
    <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">
    @yield('styles')
</head>
<body>

    @php
        $adminUser = auth()->user();
        $adminInitial = mb_strtoupper(mb_substr($adminUser->full_name ?? $adminUser->name ?? 'A', 0, 1, 'UTF-8'));
        $adminName = $adminUser->full_name ?? $adminUser->name ?? 'Quản Trị Viên';
        $adminEmail = $adminUser->email ?? 'admin@matngotbear.com';
    @endphp

    <!-- ====== SIDEBAR (GIAO DIỆN PASTEL THEO ẢNH 1 & ẢNH 2) ====== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-content">
                <a href="{{ route('home') }}" class="sidebar-brand-name" title="Về trang chủ Mật Ngọt Bear">Mật Ngọt Bear</a>
                <span class="sidebar-brand-sub">KHU VỰC QUẢN LÝ</span>
            </div>
            <button type="button" class="sidebar-collapse-btn" onclick="toggleSidebar()" title="Thu gọn menu" id="sidebarCollapseBtn">
                <i class="fa-solid fa-chevron-left" id="sidebarToggleIcon"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Tổng Quan</div>
            <!-- Mục của bạn nhóm (Dashboard & Thống kê) -->
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard*') || ($currentPage ?? '') === 'dashboard' ? 'active' : '' }}" data-title="Dashboard & Thống kê">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="sidebar-link-text">Dashboard &amp; Thống kê</span>
            </a>

            <div class="sidebar-section-label">Sản Phẩm &amp; Danh Mục</div>
            <!-- Phần của Khánh Vân -->
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products*') || ($currentPage ?? '') === 'products' ? 'active' : '' }}" data-title="Quản lý Sản phẩm">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-link-text">Quản lý Sản phẩm</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories*') || ($currentPage ?? '') === 'categories' ? 'active' : '' }}" data-title="Quản lý Danh mục">
                <i class="fa-solid fa-folder-tree"></i>
                <span class="sidebar-link-text">Quản lý Danh mục</span>
            </a>

            <div class="sidebar-section-label">Bán Hàng &amp; Tài Chính</div>
            <a href="{{ route('admin.vouchers.index') }}" class="sidebar-link {{ request()->routeIs('admin.vouchers*') || ($currentPage ?? '') === 'vouchers' ? 'active' : '' }}" data-title="Quản lý Voucher">
                <i class="fa-solid fa-ticket"></i>
                <span class="sidebar-link-text">Quản lý Voucher</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders*') || ($currentPage ?? '') === 'orders' ? 'active' : '' }}" data-title="Quản lý Đơn hàng">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-link-text">Quản lý Đơn hàng</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="sidebar-link {{ request()->routeIs('admin.payments*') || ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Quản lý Thanh toán">
                <i class="fa-solid fa-credit-card"></i>
                <span class="sidebar-link-text">Quản lý Thanh toán</span>
            </a>

            <div class="sidebar-section-label">Người Dùng</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users*') || request()->routeIs('admin.customers*') || ($currentPage ?? '') === 'customers' || ($currentPage ?? '') === 'users' ? 'active' : '' }}" data-title="Quản lý Người Dùng">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-link-text">Quản lý Người Dùng</span>
            </a>

            <div class="sidebar-section-label">Hỗ Trợ &amp; Báo Cáo</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews*') || ($currentPage ?? '') === 'reviews' ? 'active' : '' }}" data-title="Quản lý Review">
                <i class="fa-solid fa-star-half-stroke"></i>
                <span class="sidebar-link-text">Quản lý Review</span>
            </a>
            <a href="{{ route('admin.reports.revenue') }}" class="sidebar-link {{ request()->routeIs('admin.reports*') || ($currentPage ?? '') === 'reports' ? 'active' : '' }}" data-title="Báo cáo Doanh thu">
                <i class="fa-solid fa-chart-line"></i>
                <span class="sidebar-link-text">Báo cáo Doanh thu</span>
            </a>
        </nav>

        <!-- Sidebar Footer & Popup Người Dùng (Theo chuẩn Ảnh 1 & 2) -->
        <div class="sidebar-footer-wrap">
            <!-- Popup Card (Ảnh 2) -->
            <div class="sidebar-user-popup" id="sidebarUserPopup">
                <div class="user-popup-header">
                    <div class="user-popup-avatar">
                        {{ $adminInitial }}
                    </div>
                    <div class="user-popup-info">
                        <div class="user-popup-name">{{ $adminName }}</div>
                        <div class="user-popup-email" title="{{ $adminEmail }}">{{ $adminEmail }}</div>
                    </div>
                </div>

                <div class="user-popup-divider"></div>

                <div class="user-popup-menu">
                    <a href="{{ route('profile.edit') }}" class="user-popup-item active">
                        <i class="fa-regular fa-user"></i>
                        <span>Hồ sơ</span>
                    </a>
                    <div class="user-popup-item disabled">
                        <i class="fa-solid fa-lock"></i>
                        <span>Đổi mật khẩu</span>
                        <span class="badge-not-connected">Chưa kết nối</span>
                    </div>
                </div>

                <div class="user-popup-divider"></div>

                <form method="POST" action="{{ route('logout') }}" class="user-popup-logout-form">
                    @csrf
                    <button type="submit" class="user-popup-item logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </div>

            <!-- Trigger bar (Ảnh 1) -->
            <div class="sidebar-user-trigger" onclick="toggleUserPopup(event)" id="sidebarUserTrigger" title="Tài khoản cá nhân">
                <div class="sidebar-user-avatar">
                    {{ $adminInitial }}
                </div>
                <div class="sidebar-user-details">
                    <div class="sidebar-user-name">{{ $adminName }}</div>
                    <div class="sidebar-user-email" title="{{ $adminEmail }}">{{ $adminEmail }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Script khôi phục trạng thái thu/mở tức thì không giật layout -->
    <script>
        (function() {
            if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                document.getElementById('adminSidebar')?.classList.add('collapsed');
            }
        })();
    </script>

    <!-- ====== MAIN CONTENT ====== -->
    <div class="admin-main" id="adminMain">
        <script>
            (function() {
                if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                    document.getElementById('adminMain')?.classList.add('expanded');
                }
            })();
        </script>
        <div class="admin-content">
            @yield('content')
        </div>
    </div>

    <!-- Script Điều Khiển Đóng / Mở Menu & Popup Card Người Dùng -->
    <script>
        function toggleUserPopup(e) {
            if (e) e.stopPropagation();
            const popup = document.getElementById('sidebarUserPopup');
            popup?.classList.toggle('show');
        }

        // Đóng popup khi click ra ngoài
        document.addEventListener('click', function(e) {
            const popup = document.getElementById('sidebarUserPopup');
            const trigger = document.getElementById('sidebarUserTrigger');
            if (popup && popup.classList.contains('show')) {
                if (!popup.contains(e.target) && !trigger?.contains(e.target)) {
                    popup.classList.remove('show');
                }
            }
        });

        function toggleSidebar() {
            const isCollapsed = document.documentElement.classList.contains('admin-sidebar-collapsed') ||
                                document.getElementById('adminSidebar')?.classList.contains('collapsed');
            if (isCollapsed) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
        }

        function updateCollapseIcon(collapsed) {
            const icon = document.getElementById('sidebarToggleIcon');
            if (icon) {
                icon.className = collapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
            }
        }

        function expandSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            document.documentElement.classList.remove('admin-sidebar-collapsed');
            sidebar?.classList.remove('collapsed');
            main?.classList.remove('expanded');
            updateCollapseIcon(false);
            localStorage.setItem('mn_admin_sidebar_collapsed', '0');
        }

        function collapseSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            document.documentElement.classList.add('admin-sidebar-collapsed');
            sidebar?.classList.add('collapsed');
            main?.classList.add('expanded');
            updateCollapseIcon(true);
            localStorage.setItem('mn_admin_sidebar_collapsed', '1');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('mn_admin_sidebar_collapsed') === '1') {
                updateCollapseIcon(true);
            }
        });
    </script>
</body>
</html>

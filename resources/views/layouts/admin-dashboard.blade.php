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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Admin Dashboard Layout CSS (Tách riêng bởi Khánh Vân) -->
    <link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">
    @yield('styles')
</head>
<body>

    <!-- ====== SIDEBAR (NÂU PASTEL & TỰ ĐỘNG ĐÓNG MỞ) ====== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon" onclick="toggleSidebar()" title="Đóng / Mở Menu"><i class="fa-solid fa-paw"></i></div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">Mật Ngọt Bear</span>
                <span class="sidebar-brand-sub">Bảng Quản Trị Admin</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Tổng Quan</div>
            <!-- Mục của bạn nhóm (Dashboard & Thống kê) -->
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard*') || ($currentPage ?? '') === 'dashboard' ? 'active' : '' }}" data-title="Dashboard & Thống kê" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="sidebar-link-text">Dashboard & Thống kê</span>
            </a>

            <div class="sidebar-section-label">Sản Phẩm & Danh Mục</div>
            <!-- Phần của Khánh Vân -->
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products*') || ($currentPage ?? '') === 'products' ? 'active' : '' }}" data-title="Quản lý Sản phẩm" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-link-text">Quản lý Sản phẩm</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories*') || ($currentPage ?? '') === 'categories' ? 'active' : '' }}" data-title="Quản lý Danh mục" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-folder-tree"></i>
                <span class="sidebar-link-text">Quản lý Danh mục</span>
            </a>

            <div class="sidebar-section-label">Bán Hàng & Tài Chính</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.vouchers.index') }}" class="sidebar-link {{ request()->routeIs('admin.vouchers*') || ($currentPage ?? '') === 'vouchers' ? 'active' : '' }}" data-title="Quản lý Voucher" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-ticket"></i>
                <span class="sidebar-link-text">Quản lý Voucher</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders*') || ($currentPage ?? '') === 'orders' ? 'active' : '' }}" data-title="Quản lý Đơn hàng" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-link-text">Quản lý Đơn hàng</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="sidebar-link {{ request()->routeIs('admin.payments*') || ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Quản lý Thanh toán" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-credit-card"></i>
                <span class="sidebar-link-text">Quản lý Thanh toán</span>
            </a>

            <div class="sidebar-section-label">Người Dùng</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users*') || request()->routeIs('admin.customers*') || ($currentPage ?? '') === 'customers' || ($currentPage ?? '') === 'users' ? 'active' : '' }}" data-title="Quản lý Người Dùng" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-link-text">Quản lý Người Dùng</span>
            </a>
            <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ request()->routeIs('admin.staff*') || ($currentPage ?? '') === 'staff' ? 'active' : '' }}" data-title="Quản lý Staff" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-user-tie"></i>
                <span class="sidebar-link-text">Quản lý Staff</span>
            </a>

            <div class="sidebar-section-label">Hỗ Trợ & Báo Cáo</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('admin.reviews.index') }}" class="sidebar-link {{ request()->routeIs('admin.reviews*') || ($currentPage ?? '') === 'reviews' ? 'active' : '' }}" data-title="Quản lý Review" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-star-half-stroke"></i>
                <span class="sidebar-link-text">Quản lý Review</span>
            </a>
            <a href="{{ route('admin.reports.revenue') }}" class="sidebar-link {{ request()->routeIs('admin.reports*') || ($currentPage ?? '') === 'reports' ? 'active' : '' }}" data-title="Báo cáo Doanh thu" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-chart-line"></i>
                <span class="sidebar-link-text">Báo cáo Doanh thu</span>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <div class="sidebar-avatar"><i class="fa-solid fa-user-shield"></i></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->full_name ?? 'Admin' }}</div>
                    <div class="sidebar-user-role">Quản trị viên</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout-btn" title="Đăng Xuất">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng Xuất</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ====== MAIN CONTENT ====== -->
    <div class="admin-main" id="adminMain">
        <div class="admin-topbar">
            <div class="topbar-left">
                <div class="topbar-title">@yield('page-title', 'Bảng Quản Trị')</div>
            </div>
            <div class="topbar-actions">
                <a href="{{ route('home') }}" class="topbar-btn topbar-btn-home">
                    <i class="fa-solid fa-store"></i> Về Cửa Hàng
                </a>
            </div>
        </div>

        <div class="admin-content">
            @yield('content')
        </div>
    </div>

    <!-- Script Tự Động Đóng / Mở Menu & Thu Gọn -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            if (sidebar.classList.contains('collapsed')) {
                expandSidebar();
            } else {
                collapseSidebar();
            }
        }

        function expandSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            sidebar.classList.remove('collapsed');
            main.classList.remove('expanded');
            localStorage.setItem('mn_admin_sidebar_collapsed', '0');
        }

        function collapseSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            sidebar.classList.add('collapsed');
            main.classList.add('expanded');
            localStorage.setItem('mn_admin_sidebar_collapsed', '1');
        }

        // Khi click vào 1 item trên sidebar:
        // - Nếu đang thu gọn -> Click icon sẽ MỞ RỘNG menu ra đầy đủ
        // - Nếu đang mở rộng -> Click tiêu đề sẽ TỰ ĐỘNG THU BÉ menu lại
        function handleSidebarItemClick(e, elem) {
            const sidebar = document.getElementById('adminSidebar');
            const isCollapsed = sidebar.classList.contains('collapsed');

            if (isCollapsed) {
                e.preventDefault();
                expandSidebar();
            } else {
                localStorage.setItem('mn_admin_sidebar_collapsed', '1');
            }
        }

        // Khôi phục trạng thái sidebar đã lưu
        document.addEventListener('DOMContentLoaded', () => {
            const savedState = localStorage.getItem('mn_admin_sidebar_collapsed');
            if (savedState === '1') {
                collapseSidebar();
            } else {
                expandSidebar();
            }
        });
    </script>
</body>
</html>

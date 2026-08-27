<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Bảng Xử Lý Nhân Viên') - Mật Ngọt Bear</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script áp dụng trạng thái Mini Sidebar ngay lập tức trước khi render HTML để triệt tiêu độ khựng -->
    <script>
        (function() {
            if (localStorage.getItem('mn_staff_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('staff-sidebar-collapsed');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/staff-layout.css') }}">
    @yield('styles')
</head>
<body>

    <!-- ====== STAFF SIDEBAR (NÂU PASTEL & TỰ ĐỘNG ĐÓNG MỞ) ====== -->
    <aside class="staff-sidebar" id="staffSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon" onclick="toggleStaffSidebar()" title="Đóng / Mở Menu"><i class="fa-solid fa-paw"></i></div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">Mật Ngọt Bear</span>
                <span class="sidebar-brand-sub">Khu Vực Xử Lý Staff</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Vận Hành & Đơn Hàng</div>
            <!-- Các mục của bạn nhóm (Trang trống chờ code) -->
            <a href="{{ route('staff.dashboard') }}" class="sidebar-link {{ request()->routeIs('staff.dashboard*') || ($currentPage ?? '') === 'dashboard' ? 'active' : '' }}" data-title="Dashboard vận hành">
                <i class="fa-solid fa-chart-line"></i>
                <span class="sidebar-link-text">Dashboard vận hành</span>
            </a>
            <a href="{{ route('staff.orders.index') }}" class="sidebar-link {{ request()->routeIs('staff.orders*') || ($currentPage ?? '') === 'orders' ? 'active' : '' }}" data-title="Quản lý đơn hàng">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-link-text">Quản lý đơn hàng</span>
            </a>
            <a href="{{ route('staff.order-status.index') }}" class="sidebar-link {{ request()->routeIs('staff.order-status*') || ($currentPage ?? '') === 'order-status' ? 'active' : '' }}" data-title="Xử lý trạng thái đơn">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span class="sidebar-link-text">Xử lý trạng thái đơn</span>
            </a>

            <div class="sidebar-section-label">Thanh Toán & Hỗ Trợ</div>
            <!-- Các mục của bạn nhóm (Trang trống chờ code) -->
            <a href="{{ route('staff.payments.index') }}" class="sidebar-link {{ request()->routeIs('staff.payments*') || ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Xử lý thanh toán">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-link-text">Xử lý thanh toán</span>
            </a>
            <a href="{{ route('staff.support.index') }}" class="sidebar-link {{ request()->routeIs('staff.support*') || ($currentPage ?? '') === 'support' ? 'active' : '' }}" data-title="Hỗ trợ khách hàng">
                <i class="fa-solid fa-comments"></i>
                <span class="sidebar-link-text">Hỗ trợ khách hàng</span>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <div class="sidebar-avatar"><i class="fa-solid fa-user-gear"></i></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->full_name ?? 'Nhân Viên Staff' }}</div>
                    <div class="sidebar-user-role">Nhân viên Vận hành</div>
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

    <!-- Script khôi phục trạng thái thu/mở tức thì không giật layout -->
    <script>
        (function() {
            if (localStorage.getItem('mn_staff_sidebar_collapsed') === '1') {
                document.getElementById('staffSidebar')?.classList.add('collapsed');
            }
        })();
    </script>

    <!-- ====== MAIN CONTENT ====== -->
    <div class="staff-main" id="staffMain">
        <script>
            (function() {
                if (localStorage.getItem('mn_staff_sidebar_collapsed') === '1') {
                    document.getElementById('staffMain')?.classList.add('expanded');
                }
            })();
        </script>
        <div class="staff-topbar">
            <div class="topbar-left">
                <button type="button" class="topbar-btn" onclick="toggleStaffSidebar()" title="Đóng / Mở Menu" style="cursor: pointer; border: 1px solid var(--border-dark);">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>
                <div class="topbar-title">@yield('page-title', 'Xử Lý Nghiệp Vụ Nhân Viên')</div>
            </div>
            <div class="topbar-actions">
                <a href="{{ route('home') }}" class="topbar-btn topbar-btn-home">
                    <i class="fa-solid fa-store"></i> Về Cửa Hàng
                </a>
            </div>
        </div>

        <div class="staff-content">
            @yield('content')
        </div>
    </div>

    <!-- Script Điều Khiển Đóng / Mở Menu theo Chân Gấu cho Staff -->
    <script>
        function toggleStaffSidebar() {
            const isCollapsed = document.documentElement.classList.contains('staff-sidebar-collapsed') ||
                                document.getElementById('staffSidebar')?.classList.contains('collapsed');
            if (isCollapsed) {
                expandStaffSidebar();
            } else {
                collapseStaffSidebar();
            }
        }

        function expandStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            const main = document.getElementById('staffMain');
            document.documentElement.classList.remove('staff-sidebar-collapsed');
            sidebar?.classList.remove('collapsed');
            main?.classList.remove('expanded');
            localStorage.setItem('mn_staff_sidebar_collapsed', '0');
        }

        function collapseStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            const main = document.getElementById('staffMain');
            document.documentElement.classList.add('staff-sidebar-collapsed');
            sidebar?.classList.add('collapsed');
            main?.classList.add('expanded');
            localStorage.setItem('mn_staff_sidebar_collapsed', '1');
        }
    </script>
</body>
</html>

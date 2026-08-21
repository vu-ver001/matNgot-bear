<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Bảng Xử Lý Nhân Viên') - Mật Ngọt Bear</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="{{ asset('css/staff-layout.css') }}">
    @yield('styles')
</head>
<body>

    <!-- ====== STAFF SIDEBAR (NÂU PASTEL & TỰ ĐỘNG ĐÓNG MỞ) ====== -->
    <aside class="staff-sidebar" id="staffSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon" onclick="toggleStaffSidebar()" title="Đóng / Mở Menu"><i class="fa-solid fa-headset"></i></div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">Mật Ngọt Bear</span>
                <span class="sidebar-brand-sub">Khu Vực Xử Lý Staff</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Vận Hành & Đơn Hàng</div>
            <!-- Các mục của bạn nhóm (Trang trống chờ code) -->
            <a href="{{ route('staff.dashboard') }}" class="sidebar-link {{ ($currentPage ?? '') === 'dashboard' ? 'active' : '' }}" data-title="Dashboard vận hành" onclick="handleStaffSidebarItemClick(event, this)">
                <i class="fa-solid fa-chart-line"></i>
                <span class="sidebar-link-text">Dashboard vận hành</span>
            </a>
            <a href="{{ route('staff.orders.index') }}" class="sidebar-link {{ ($currentPage ?? '') === 'orders' ? 'active' : '' }}" data-title="Quản lý đơn hàng" onclick="handleStaffSidebarItemClick(event, this)">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-link-text">Quản lý đơn hàng</span>
            </a>
            <a href="{{ route('staff.order-status.index') }}" class="sidebar-link {{ ($currentPage ?? '') === 'order-status' ? 'active' : '' }}" data-title="Xử lý trạng thái đơn" onclick="handleStaffSidebarItemClick(event, this)">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span class="sidebar-link-text">Xử lý trạng thái đơn</span>
            </a>

            <div class="sidebar-section-label">Thanh Toán & Hỗ Trợ</div>
            <!-- Các mục của bạn nhóm (Trang trống chờ code) -->
            <a href="{{ route('staff.payments.index') }}" class="sidebar-link {{ ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Xử lý thanh toán" onclick="handleStaffSidebarItemClick(event, this)">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-link-text">Xử lý thanh toán</span>
            </a>
            <a href="{{ route('staff.support.index') }}" class="sidebar-link {{ ($currentPage ?? '') === 'support' ? 'active' : '' }}" data-title="Hỗ trợ khách hàng" onclick="handleStaffSidebarItemClick(event, this)">
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

    <!-- ====== MAIN CONTENT ====== -->
    <div class="staff-main" id="staffMain">
        <div class="staff-topbar">
            <div class="topbar-left">
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

    <!-- Script Tự Động Đóng / Mở Menu & Thu Gọn cho Staff -->
    <script>
        function toggleStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            if (sidebar.classList.contains('collapsed')) {
                expandStaffSidebar();
            } else {
                collapseStaffSidebar();
            }
        }

        function expandStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            const main = document.getElementById('staffMain');
            sidebar.classList.remove('collapsed');
            main.classList.remove('expanded');
            localStorage.setItem('mn_staff_sidebar_collapsed', '0');
        }

        function collapseStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            const main = document.getElementById('staffMain');
            sidebar.classList.add('collapsed');
            main.classList.add('expanded');
            localStorage.setItem('mn_staff_sidebar_collapsed', '1');
        }

        function handleStaffSidebarItemClick(e, elem) {
            const sidebar = document.getElementById('staffSidebar');
            const isCollapsed = sidebar.classList.contains('collapsed');

            if (isCollapsed) {
                e.preventDefault();
                expandStaffSidebar();
            } else {
                localStorage.setItem('mn_staff_sidebar_collapsed', '1');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedState = localStorage.getItem('mn_staff_sidebar_collapsed');
            if (savedState === '1') {
                collapseStaffSidebar();
            } else {
                expandStaffSidebar();
            }
        });
    </script>
</body>
</html>

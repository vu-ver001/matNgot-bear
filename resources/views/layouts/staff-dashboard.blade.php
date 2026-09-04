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

    @php
        $staffUser = auth()->user();
        $staffInitial = mb_strtoupper(mb_substr($staffUser->full_name ?? $staffUser->name ?? 'S', 0, 1, 'UTF-8'));
        $staffName = $staffUser->full_name ?? $staffUser->name ?? 'Nhân Viên Staff';
        $staffEmail = $staffUser->email ?? 'staff1@matngotbear.com';
    @endphp

    <!-- ====== STAFF SIDEBAR (GIAO DIỆN PASTEL THEO ẢNH 1 & ẢNH 2) ====== -->
    <aside class="staff-sidebar" id="staffSidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-content">
                <a href="{{ route('home') }}" class="sidebar-brand-name" title="Về trang chủ Mật Ngọt Bear">Mật Ngọt Bear</a>
                <span class="sidebar-brand-sub">KHU VỰC XỬ LÝ</span>
            </div>
            <button type="button" class="sidebar-collapse-btn" onclick="toggleStaffSidebar()" title="Thu gọn menu" id="staffSidebarCollapseBtn">
                <i class="fa-solid fa-chevron-left" id="staffSidebarToggleIcon"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Vận Hành &amp; Đơn Hàng</div>
            <!-- Các mục của bạn nhóm -->
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

            <div class="sidebar-section-label">Thanh Toán &amp; Hỗ Trợ</div>
            <!-- Các mục của bạn nhóm -->
            <a href="{{ route('staff.payments.index') }}" class="sidebar-link {{ request()->routeIs('staff.payments*') || ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Xử lý thanh toán">
                <i class="fa-solid fa-receipt"></i>
                <span class="sidebar-link-text">Xử lý thanh toán</span>
            </a>
            <a href="{{ route('staff.support.index') }}" class="sidebar-link {{ request()->routeIs('staff.support*') || ($currentPage ?? '') === 'support' ? 'active' : '' }}" data-title="Hỗ trợ khách hàng">
                <i class="fa-solid fa-comments"></i>
                <span class="sidebar-link-text">Hỗ trợ khách hàng</span>
            </a>
        </nav>

        <!-- Sidebar Footer & Popup Người Dùng (Theo chuẩn Ảnh 1 & 2) -->
        <div class="sidebar-footer-wrap">
            <!-- Popup Card (Ảnh 2) -->
            <div class="sidebar-user-popup" id="staffUserPopup">
                <div class="user-popup-header">
                    <div class="user-popup-avatar">
                        {{ $staffInitial }}
                    </div>
                    <div class="user-popup-info">
                        <div class="user-popup-name">{{ $staffName }}</div>
                        <div class="user-popup-email" title="{{ $staffEmail }}">{{ $staffEmail }}</div>
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
            <div class="sidebar-user-trigger" onclick="toggleStaffUserPopup(event)" id="staffUserTrigger" title="Tài khoản nhân viên">
                <div class="sidebar-user-avatar">
                    {{ $staffInitial }}
                </div>
                <div class="sidebar-user-details">
                    <div class="sidebar-user-name">{{ $staffName }}</div>
                    <div class="sidebar-user-email" title="{{ $staffEmail }}">{{ $staffEmail }}</div>
                </div>
            </div>
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
        <div class="staff-content">
            @yield('content')
        </div>
    </div>

    <!-- Script Điều Khiển Đóng / Mở Menu & Popup Card Cho Staff -->
    <script>
        function toggleStaffUserPopup(e) {
            if (e) e.stopPropagation();
            const popup = document.getElementById('staffUserPopup');
            popup?.classList.toggle('show');
        }

        // Đóng popup khi click ra ngoài
        document.addEventListener('click', function(e) {
            const popup = document.getElementById('staffUserPopup');
            const trigger = document.getElementById('staffUserTrigger');
            if (popup && popup.classList.contains('show')) {
                if (!popup.contains(e.target) && !trigger?.contains(e.target)) {
                    popup.classList.remove('show');
                }
            }
        });

        function toggleStaffSidebar() {
            const isCollapsed = document.documentElement.classList.contains('staff-sidebar-collapsed') ||
                                document.getElementById('staffSidebar')?.classList.contains('collapsed');
            if (isCollapsed) {
                expandStaffSidebar();
            } else {
                collapseStaffSidebar();
            }
        }

        function updateStaffCollapseIcon(collapsed) {
            const icon = document.getElementById('staffSidebarToggleIcon');
            if (icon) {
                icon.className = collapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
            }
        }

        function expandStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            const main = document.getElementById('staffMain');
            document.documentElement.classList.remove('staff-sidebar-collapsed');
            sidebar?.classList.remove('collapsed');
            main?.classList.remove('expanded');
            updateStaffCollapseIcon(false);
            localStorage.setItem('mn_staff_sidebar_collapsed', '0');
        }

        function collapseStaffSidebar() {
            const sidebar = document.getElementById('staffSidebar');
            const main = document.getElementById('staffMain');
            document.documentElement.classList.add('staff-sidebar-collapsed');
            sidebar?.classList.add('collapsed');
            main?.classList.add('expanded');
            updateStaffCollapseIcon(true);
            localStorage.setItem('mn_staff_sidebar_collapsed', '1');
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('mn_staff_sidebar_collapsed') === '1') {
                updateStaffCollapseIcon(true);
            }
        });
    </script>
</body>
</html>

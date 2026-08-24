<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'Quản Trị Hệ Thống') - Mật Ngọt Bear</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bg-page: #FDFBF7;
            --bg-card: #FFFFFF;
            --bg-surface: #F8F4EE;
            --bg-cream: #F5EFEB;
            
            /* Pastel Brown Theme */
            --pastel-brown-main: #8D6E63;
            --pastel-brown-dark: #5D4037;
            --pastel-brown-deep: #4E342E;
            --pastel-brown-light: #EFEBE9;
            --pastel-brown-surface: #FAF6F0;
            
            --honey: #E59819;
            --honey-light: #FFF8E7;
            --honey-dark: #B87309;
            --honey-gold: #F6D89B;
            
            --text-main: #4E342E;
            --text-muted: #795548;
            --text-light: #9E8076;
            
            --border: #EADFCF;
            --border-light: #F2EAE0;
            --danger: #C62828;
            
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            
            --shadow-subtle: 0 4px 16px rgba(109, 76, 65, 0.05);
            --shadow-card: 0 10px 30px rgba(109, 76, 65, 0.08);
            --shadow-hover: 0 16px 40px rgba(109, 76, 65, 0.14);
            
            --sidebar-width: 270px;
            --sidebar-collapsed-width: 72px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', -apple-system, sans-serif;
        }

        body {
            background: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ====== SIDEBAR (NÂU PASTEL THEME) ====== */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #795548 0%, #5D4037 100%);
            color: #FDFBF7;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s ease;
            box-shadow: 4px 0 24px rgba(78, 52, 46, 0.12);
        }

        /* Khi Sidebar Thu Gọn (Mini Sidebar) */
        .admin-sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-brand {
            padding: 1.25rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            position: relative;
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #4E342E;
            box-shadow: 0 4px 12px rgba(229, 152, 25, 0.3);
            cursor: pointer;
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            white-space: nowrap;
            transition: opacity 0.2s ease;
        }

        .admin-sidebar.collapsed .sidebar-brand-text {
            opacity: 0;
            display: none;
        }

        .sidebar-brand-name {
            font-size: 17px;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.3px;
        }

        .sidebar-brand-sub {
            font-size: 10px;
            color: #D7CCC8;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.75rem 0;
        }

        .sidebar-section-label {
            font-size: 9.5px;
            font-weight: 800;
            color: #D7CCC8;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 0.75rem 1.25rem 0.35rem;
            white-space: nowrap;
            transition: opacity 0.2s ease;
        }

        .admin-sidebar.collapsed .sidebar-section-label {
            opacity: 0;
            display: none;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 1.25rem;
            font-size: 13px;
            font-weight: 700;
            color: #EFEBE9;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            border-left-color: var(--honey-gold);
        }

        .sidebar-link.active {
            background: rgba(246, 216, 155, 0.2);
            color: var(--honey-gold);
            border-left-color: var(--honey-gold);
            font-weight: 800;
        }

        .sidebar-link i {
            width: 22px;
            min-width: 22px;
            text-align: center;
            font-size: 16px;
        }

        .sidebar-link-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: opacity 0.2s ease;
        }

        .admin-sidebar.collapsed .sidebar-link-text {
            opacity: 0;
            display: none;
        }

        /* Tooltip khi hover trên mini sidebar */
        .admin-sidebar.collapsed .sidebar-link:hover::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 10px;
            background: #4E342E;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            z-index: 999;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            pointer-events: none;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar-footer-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .sidebar-avatar {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #F6D89B, #E59819);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4E342E;
            font-size: 13px;
        }

        .sidebar-user-info {
            overflow: hidden;
            white-space: nowrap;
        }

        .admin-sidebar.collapsed .sidebar-user-info {
            display: none;
        }

        .sidebar-user-name {
            font-size: 12.5px;
            font-weight: 800;
            color: #FFFFFF;
        }

        .sidebar-user-role {
            font-size: 10px;
            color: #D7CCC8;
        }

        .sidebar-logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #FFCDD2;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .sidebar-logout-btn:hover {
            background: rgba(198, 40, 40, 0.4);
            color: #FFFFFF;
        }

        .admin-sidebar.collapsed .sidebar-logout-btn span {
            display: none;
        }

        /* ====== MAIN AREA ====== */
        .admin-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .admin-main.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .admin-topbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--shadow-subtle);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Nút Toggle Sidebar */
        .btn-sidebar-toggle {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            color: #5D4037;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-sidebar-toggle:hover {
            background: var(--honey-light);
            color: var(--honey-dark);
            border-color: var(--honey);
            transform: scale(1.05);
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--pastel-brown-dark);
            letter-spacing: -0.3px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .topbar-btn-home {
            background: var(--pastel-brown-light);
            color: var(--pastel-brown-dark);
            border: 1px solid var(--border);
        }

        .topbar-btn-home:hover {
            background: var(--pastel-brown-main);
            color: #FFFFFF;
        }

        .admin-content {
            padding: 2rem;
            flex: 1;
        }

        /* Placeholder page styling */
        .placeholder-page {
            background: #FFFFFF;
            border: 2px dashed var(--border);
            border-radius: var(--radius-lg);
            padding: 4rem 2rem;
            text-align: center;
            max-width: 620px;
            margin: 2rem auto;
            box-shadow: var(--shadow-subtle);
        }

        .placeholder-icon {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: var(--pastel-brown-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: var(--pastel-brown-main);
            margin-bottom: 1.5rem;
        }

        .placeholder-title {
            font-size: 20px;
            font-weight: 900;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .placeholder-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .placeholder-tip {
            background: var(--pastel-brown-surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 18px;
            text-align: left;
            font-size: 12px;
            color: var(--text-main);
            line-height: 1.7;
        }

        .placeholder-tip code {
            background: rgba(141, 110, 99, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 800;
            color: var(--pastel-brown-dark);
        }

        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            .admin-main {
                margin-left: 0 !important;
            }
        }
    </style>
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
            <a href="{{ route('admin.page', 'dashboard') }}" class="sidebar-link {{ ($currentPage ?? '') === 'dashboard' ? 'active' : '' }}" data-title="Dashboard & Thống kê" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="sidebar-link-text">Dashboard & Thống kê</span>
            </a>

            <div class="sidebar-section-label">Sản Phẩm & Danh Mục</div>
            <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ ($currentPage ?? '') === 'products' ? 'active' : '' }}" data-title="Quản lý Sản phẩm" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-box-open"></i>
                <span class="sidebar-link-text">Quản lý Sản phẩm</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ ($currentPage ?? '') === 'categories' ? 'active' : '' }}" data-title="Quản lý Danh mục" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-folder-tree"></i>
                <span class="sidebar-link-text">Quản lý Danh mục</span>
            </a>

            <div class="sidebar-section-label">Bán Hàng & Tài Chính</div>
            <a href="{{ route('admin.vouchers.index') }}" class="sidebar-link {{ request()->routeIs('admin.vouchers.*') || ($currentPage ?? '') === 'vouchers' ? 'active' : '' }}" data-title="Quản lý Voucher">
                <i class="fa-solid fa-ticket"></i>
                <span class="sidebar-link-text">Quản lý Voucher</span>
            </a>
            <a href="{{ route('admin.page', 'orders') }}" class="sidebar-link {{ ($currentPage ?? '') === 'orders' ? 'active' : '' }}" data-title="Quản lý Đơn hàng" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="sidebar-link-text">Quản lý Đơn hàng</span>
            </a>
            <a href="{{ route('admin.page', 'payments') }}" class="sidebar-link {{ ($currentPage ?? '') === 'payments' ? 'active' : '' }}" data-title="Quản lý Thanh toán" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-credit-card"></i>
                <span class="sidebar-link-text">Quản lý Thanh toán</span>
            </a>

            <div class="sidebar-section-label">Người Dùng</div>
            <a href="{{ route('admin.page', 'customers') }}" class="sidebar-link {{ ($currentPage ?? '') === 'customers' ? 'active' : '' }}" data-title="Quản lý Customer" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-users"></i>
                <span class="sidebar-link-text">Quản lý Customer</span>
            </a>
            <a href="{{ route('admin.page', 'staff') }}" class="sidebar-link {{ ($currentPage ?? '') === 'staff' ? 'active' : '' }}" data-title="Quản lý Staff" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-user-tie"></i>
                <span class="sidebar-link-text">Quản lý Staff</span>
            </a>

            <div class="sidebar-section-label">Hỗ Trợ</div>
            <a href="{{ route('admin.page', 'reviews') }}" class="sidebar-link {{ ($currentPage ?? '') === 'reviews' ? 'active' : '' }}" data-title="Quản lý Review" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-star-half-stroke"></i>
                <span class="sidebar-link-text">Quản lý Review</span>
            </a>
            <a href="{{ route('admin.page', 'support') }}" class="sidebar-link {{ ($currentPage ?? '') === 'support' ? 'active' : '' }}" data-title="Hỗ trợ Khách hàng" onclick="handleSidebarItemClick(event, this)">
                <i class="fa-solid fa-headset"></i>
                <span class="sidebar-link-text">Hỗ trợ Khách hàng</span>
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

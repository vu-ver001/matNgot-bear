<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mật Ngọt Bear - Thế Giới Gấu Bông Cao Cấp')</title>

    <!-- Google Fonts Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bg-page: #FDFBF7;
            --bg-card: #FFFFFF;
            --bg-surface: #F8F4EE;
            --bg-cream: #F5EFEB;
            
            --primary: #8D6E63;
            --primary-dark: #5D4037;
            --primary-light: #BCAAA4;
            --primary-pastel: #A1887F;
            
            --honey: #E59819;
            --honey-light: #FFF8E7;
            --honey-dark: #B87309;
            --honey-gold: #F6D89B;
            
            --text-main: #4E342E;
            --text-muted: #795548;
            --text-light: #9E8076;
            
            --border: #EADFCF;
            --border-light: #F2EAE0;
            
            --success: #2E7D32;
            --success-bg: #E8F5E9;
            --danger: #C62828;
            --danger-bg: #FFEBEE;
            
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            
            --shadow-subtle: 0 4px 16px rgba(109, 76, 65, 0.05);
            --shadow-card: 0 10px 30px rgba(109, 76, 65, 0.08);
            --shadow-hover: 0 16px 40px rgba(109, 76, 65, 0.14);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Top Announcement Bar with Quick Role Switcher for Testing */
        .top-announcement {
            background: linear-gradient(90deg, #795548 0%, #6D4C41 50%, #795548 100%);
            color: #FDFBF7;
            font-size: 12px;
            font-weight: 500;
            padding: 7px 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .top-announcement a {
            color: var(--honey-gold);
            font-weight: 700;
        }

        /* Main Header */
        header.site-header {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 4px 20px rgba(62, 39, 35, 0.04);
        }

        .header-main-row {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        /* Brand Logo */
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #3E2723;
            box-shadow: 0 4px 14px rgba(229, 152, 25, 0.35);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 21px;
            font-weight: 800;
            color: var(--primary-dark);
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .brand-slogan {
            font-size: 10px;
            font-weight: 700;
            color: var(--honey-dark);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Search Bar */
        .header-search-form {
            flex: 1;
            max-width: 540px;
            position: relative;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            background: var(--bg-surface);
            border: 2px solid var(--border);
            border-radius: 999px;
            padding: 10px 50px 10px 18px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            outline: none;
            transition: all 0.25s ease;
        }

        .search-input:focus {
            border-color: var(--honey);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(229, 152, 25, 0.15);
        }

        .search-submit-btn {
            position: absolute;
            right: 5px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .search-submit-btn:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #E59819 0%, #B87309 100%);
        }

        /* Header Right Utility */
        .header-utility-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }

        .hotline-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: var(--honey-light);
            border: 1px solid rgba(229, 152, 25, 0.3);
            border-radius: 999px;
        }

        .hotline-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--honey);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .hotline-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .hotline-meta span.label {
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .hotline-meta span.number {
            font-size: 13px;
            font-weight: 800;
            color: var(--primary-dark);
        }

        .utility-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            text-decoration: none;
        }

        .utility-btn:hover {
            background: #FFFFFF;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-subtle);
        }

        /* Icon-only round buttons (Giỏ hàng, Wishlist, Đơn của tôi) */
        .utility-icon-btn {
            width: 40px;
            height: 40px;
            min-width: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 50%;
            color: var(--text-main);
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .utility-icon-btn:hover {
            background: #FFFFFF;
            border-color: var(--honey);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(109, 76, 65, 0.12);
        }

        .btn-auth-pill {
            background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%);
            color: #FFFFFF !important;
            border: none;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(109, 76, 65, 0.2);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-auth-pill:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #E59819 0%, #B87309 100%);
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(229, 152, 25, 0.3);
        }

        .badge-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #D32F2F;
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFFFFF;
        }

        /* Navigation Bar (Menu Row) */
        nav.nav-bar {
            background: var(--bg-surface);
            border-top: 1px solid var(--border-light);
            position: relative;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 2px;
        }

        .nav-item {
            position: static;
        }

        .nav-item.has-megamenu {
            position: static;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 800;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--honey-dark);
            border-bottom-color: var(--honey);
            background: rgba(255, 255, 255, 0.8);
        }

        .nav-link i.fa-chevron-down {
            font-size: 9px;
            opacity: 0.7;
            transition: transform 0.2s ease;
        }

        .nav-item:hover .nav-link i.fa-chevron-down {
            transform: rotate(180deg);
        }

        /* ==================================================== */
        /* MEGA MENU DROPDOWN (Like Image 1)                    */
        /* ==================================================== */
        .megamenu-panel {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border-top: 2px solid var(--honey);
            box-shadow: 0 20px 40px rgba(62, 39, 35, 0.12);
            padding: 24px 0;
            display: none;
            z-index: 300;
            animation: fadeInMenu 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .nav-item.has-megamenu:hover .megamenu-panel {
            display: block;
        }

        .megamenu-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }

        .megamenu-col {
            display: flex;
            flex-direction: column;
        }

        .megamenu-heading {
            font-size: 13px;
            font-weight: 800;
            color: #4E342E;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #F6D89B;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .megamenu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .megamenu-link {
            font-size: 12.5px;
            font-weight: 600;
            color: #5D4037;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 0;
        }

        .megamenu-link:hover {
            color: var(--honey-dark);
            transform: translateX(4px);
        }

        .megamenu-link i {
            font-size: 10px;
            color: var(--honey);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .megamenu-link:hover i {
            opacity: 1;
        }

        /* Dropdown Simple */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: #FFFFFF;
            min-width: 230px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 8px 0;
            box-shadow: var(--shadow-card);
            display: none;
            z-index: 300;
            animation: fadeInMenu 0.2s ease;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 18px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-main);
            transition: all 0.15s ease;
        }

        .dropdown-item:hover {
            background: var(--honey-light);
            color: var(--honey-dark);
            padding-left: 22px;
        }

        /* Role dropdown - toggle via click */
        .role-dropdown {
            display: none !important;
        }

        .role-dropdown.show {
            display: block !important;
        }

        /* Admin Direct Links in Header */
        .nav-admin-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-direct-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%);
            color: #FFFFFF;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 8px rgba(109, 76, 65, 0.2);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .admin-direct-link:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #E59819 0%, #B87309 100%);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(229, 152, 25, 0.3);
        }

        /* Main Page Container */
        main.main-content {
            flex: 1;
        }

        /* Footer */
        footer.site-footer {
            background: linear-gradient(180deg, #5D4037 0%, #4E342E 100%);
            color: #EFEBE9;
            padding: 4rem 1.5rem 2rem 1.5rem;
            margin-top: 4rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-col h4 {
            font-size: 16px;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 1.25rem;
            position: relative;
            padding-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 32px;
            height: 3px;
            background: var(--honey);
            border-radius: 999px;
        }

        .footer-col p {
            font-size: 13.5px;
            line-height: 1.7;
            color: #BCAAA4;
            margin-bottom: 12px;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-links a {
            font-size: 13.5px;
            font-weight: 600;
            color: #D7CCC8;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            color: var(--honey-gold);
            transform: translateX(4px);
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 2rem auto 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #8D6E63;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Product Cards */
        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: var(--shadow-subtle);
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
            border-color: #D7CCC8;
        }

        .product-card-img-wrap {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            background: var(--bg-surface);
        }

        .product-card-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-card-img {
            transform: scale(1.08);
        }

        .card-badge-sale {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, #FF5252 0%, #D32F2F 100%);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 999px;
            box-shadow: 0 2px 8px rgba(211, 47, 47, 0.3);
            z-index: 2;
        }

        .card-badge-hot {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
            color: #4E342E;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 999px;
            box-shadow: 0 2px 8px rgba(229, 152, 25, 0.3);
            z-index: 2;
        }

        .product-card-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
        }

        .product-card-category {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .product-card-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 42px;
        }

        .product-card-prices {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 12px;
        }

        .price-current {
            font-size: 17px;
            font-weight: 900;
            color: #D32F2F;
        }

        .price-old {
            font-size: 12.5px;
            color: var(--text-light);
            text-decoration: line-through;
            font-weight: 600;
        }

        .product-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid var(--border-light);
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .btn-add-cart-quick {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: var(--bg-surface);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-add-cart-quick:hover {
            background: var(--honey);
            color: #FFFFFF;
            border-color: var(--honey);
            transform: scale(1.1);
        }

        /* Buttons */
        .btn-honey-main {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
            color: #4E342E;
            font-weight: 800;
            font-size: 13.5px;
            padding: 12px 26px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(229, 152, 25, 0.3);
            transition: all 0.25s ease;
        }

        .btn-honey-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(229, 152, 25, 0.45);
        }

        .btn-brown-main {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%);
            color: #FFFFFF;
            font-weight: 800;
            font-size: 13.5px;
            padding: 12px 26px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(109, 76, 65, 0.25);
            transition: all 0.25s ease;
        }

        .btn-brown-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(109, 76, 65, 0.35);
        }

        @keyframes fadeInMenu {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .nav-link { font-size: 11.5px; padding: 12px 8px; }
        }

        @media (max-width: 992px) {
            .header-main-row { flex-wrap: wrap; gap: 1rem; }
            .header-search-form { order: 3; max-width: 100%; width: 100%; }
            .footer-container { grid-template-columns: 1fr 1fr; }
            .nav-menu { overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
            .nav-menu::-webkit-scrollbar { display: none; }
            .nav-container { flex-wrap: wrap; }
            .megamenu-panel { display: none !important; }
        }

        @media (max-width: 576px) {
            .footer-container { grid-template-columns: 1fr; }
            .hotline-pill { display: none; }
        }
    </style>
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
        <div class="header-main-row">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-logo">
                <div class="brand-icon">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-name">Mật Ngọt Bear</span>
                    <span class="brand-slogan">Thế Giới Gấu Bông</span>
                </div>
            </a>

            <!-- Central Search Bar -->
            <form action="{{ route('products.index') }}" method="GET" class="header-search-form">
                <div class="search-input-wrapper">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Tìm kiếm gấu bông yêu thích (Teddy, Capybara, Loopy...)"
                        value="{{ request('search') }}"
                        autocomplete="off"
                    >
                    <button type="submit" class="search-submit-btn" title="Tìm kiếm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>

            <!-- Utility Group -->
            <div class="header-utility-group">
                <!-- Hotline -->
                <a href="tel:0979896616" class="hotline-pill">
                    <div class="hotline-icon">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div class="hotline-meta">
                        <span class="label">Hotline 24/7</span>
                        <span class="number">097.989.6616</span>
                    </div>
                </a>

                <!-- Wishlist (Yêu thích) -->
                <a href="{{ route('customer.wishlist') }}" class="utility-icon-btn" title="Danh sách yêu thích">
                    <i class="fa-regular fa-heart" style="font-size: 16px; color: #E57373;"></i>
                    <span class="badge-count" id="wishlist-count">0</span>
                </a>

                <!-- Cart (Giỏ hàng - Chỉ để icon) -->
                <a href="{{ route('customer.cart') }}" class="utility-icon-btn" title="Giỏ hàng">
                    <i class="fa-solid fa-bag-shopping" style="font-size: 16px; color: var(--honey-dark);"></i>
                    <span class="badge-count" id="cart-count">0</span>
                </a>

                <!-- My Orders (Đơn của tôi) -->
                <a href="{{ route('customer.orders.index') }}" class="utility-icon-btn" title="Đơn hàng của tôi">
                    <i class="fa-solid fa-clipboard-list" style="font-size: 16px; color: #8D6E63;"></i>
                </a>

                <!-- Nút Đăng nhập / Đăng xuất & Tài khoản cạnh icon "Đơn của tôi" -->
                <div style="position: relative;">
                    @guest
                        <a href="{{ route('login') }}" class="btn-auth-pill" title="Đăng nhập tài khoản">
                            <i class="fa-solid fa-right-to-bracket"></i> ĐĂNG NHẬP
                        </a>
                    @endguest

                    @auth
                        @php $userRole = auth()->user()->role; @endphp
                        @if($userRole === 'ADMIN')
                            <a href="javascript:void(0)" class="btn-auth-pill" style="background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%);" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
                                <i class="fa-solid fa-shield-halved"></i> ADMIN <i class="fa-solid fa-chevron-down" style="font-size: 9px;"></i>
                            </a>
                            <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 220px;">
                                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #8D6E63, #6D4C41); color: #fff; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user-shield"></i></div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px; color: #5D4037;">{{ auth()->user()->full_name }}</div>
                                        <div style="font-size: 10.5px; color: var(--text-light);">Quản trị viên</div>
                                    </div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <span><i class="fa-solid fa-user-pen" style="color: #8D6E63; margin-right: 6px;"></i> Hồ Sơ Cá Nhân</span>
                                </a>
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                    <span><i class="fa-solid fa-gauge-high" style="color: #8D6E63; margin-right: 6px;"></i> Quản Lý</span>
                                    <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                                </a>
                                <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 12.5px; font-weight: 600; color: var(--danger);">
                                            <span><i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i> Đăng Xuất</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif($userRole === 'STAFF')
                            <a href="javascript:void(0)" class="btn-auth-pill" style="background: linear-gradient(135deg, #A1887F 0%, #795548 100%);" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
                                <i class="fa-solid fa-user-tag"></i> STAFF <i class="fa-solid fa-chevron-down" style="font-size: 9px;"></i>
                            </a>
                            <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 220px;">
                                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #A1887F, #795548); color: #fff; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user-tie"></i></div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px; color: #5D4037;">{{ auth()->user()->full_name }}</div>
                                        <div style="font-size: 10.5px; color: var(--text-light);">Nhân viên</div>
                                    </div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <span><i class="fa-solid fa-user-pen" style="color: #8D6E63; margin-right: 6px;"></i> Hồ Sơ Cá Nhân</span>
                                </a>
                                <a href="{{ route('staff.dashboard') }}" class="dropdown-item">
                                    <span><i class="fa-solid fa-boxes-packing" style="color: #8D6E63; margin-right: 6px;"></i> Xử Lý Đơn Hàng</span>
                                    <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-light);"></i>
                                </a>
                                <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 12.5px; font-weight: 600; color: var(--danger);">
                                            <span><i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i> Đăng Xuất</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="javascript:void(0)" class="btn-auth-pill" style="background: linear-gradient(135deg, #D7CCC8 0%, #BCAAA4 100%); color: #4E342E !important;" onclick="this.parentElement.querySelector('.dropdown-menu').classList.toggle('show')">
                                <i class="fa-solid fa-user"></i> KHÁCH HÀNG <i class="fa-solid fa-chevron-down" style="font-size: 9px;"></i>
                            </a>
                            <div class="dropdown-menu role-dropdown" style="right: 0; left: auto; min-width: 220px;">
                                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #D7CCC8, #BCAAA4); color: #4E342E; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user"></i></div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 12.5px; color: #5D4037;">{{ auth()->user()->full_name }}</div>
                                        <div style="font-size: 10.5px; color: var(--text-light);">Khách hàng thân thiết</div>
                                    </div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <span><i class="fa-solid fa-user-pen" style="color: #8D6E63; margin-right: 6px;"></i> Hồ Sơ Cá Nhân</span>
                                </a>
                                <a href="{{ route('customer.orders.index') }}" class="dropdown-item">
                                    <span><i class="fa-solid fa-clipboard-list" style="color: #8D6E63; margin-right: 6px;"></i> Đơn Hàng Của Tôi</span>
                                </a>
                                <div style="border-top: 1px solid var(--border-light); margin-top: 4px; padding-top: 4px;">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-size: 12.5px; font-weight: 600; color: var(--danger);">
                                            <span><i class="fa-solid fa-right-from-bracket" style="margin-right: 6px;"></i> Đăng Xuất</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

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
                                    <div class="megamenu-heading">PHỤ KIỆN GẤU BƠ</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Móc Khóa Butter Bear']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Móc Khóa Gấu Bơ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Túi Đeo Chéo Gấu Bơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Túi Đeo Chéo Gấu Bơ</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Kẹp Tóc Gấu Butter Bear']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Kẹp Tóc Gấu Bơ</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">SET QUÀ TẶNG BƠ</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Hộp Quà Butter Bear']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Butter Bear</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Combo Gấu Bơ + Hoa Sáp']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Combo Gấu Bơ + Hoa Sáp</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 2, 'sub' => 'Gấu Bơ Kèm Thiệp']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bơ Kèm Thiệp</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- 4. TEDDY MR. BEAN (Mega Dropdown) -->
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => 3]) }}" class="nav-link {{ request('category_id') == 3 ? 'active' : '' }}">
                            TEDDY MR. BEAN <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">MR. BEAN CỔ ĐIỂN</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Gấu Mr. Bean Mắt Cúc 40cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Mr. Bean Mắt Cúc</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Gấu Mr. Bean Đan Len']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Mr. Bean Đan Len</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Gấu Mr. Bean Vintage']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Mr. Bean Vintage</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">SIZE ĐẶC BIỆT</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Mr. Bean Nhỏ 25cm', 'size' => '25cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Nhỏ 25cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Mr. Bean Vừa 40cm', 'size' => '40cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Vừa 40cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Mr. Bean Khổng Lồ 80cm', 'size' => '80cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Mr. Bean Khổng Lồ 80cm</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">PHỤ KIỆN MR. BEAN</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Móc Khóa Gấu Nâu']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Móc Khóa Gấu Nâu</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Áo Len Cho Gấu Mr. Bean']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Áo Len Cho Gấu</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Cà Vạt Đỏ Mr. Bean']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cà Vạt Đỏ Mr. Bean</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">QUÀ TẶNG ĐỘC ĐÁO</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Set Quà Mr. Bean Vintage']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Quà Mr. Bean Vintage</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Combo Fan Mr. Bean']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Combo Fan Mr. Bean</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 3, 'sub' => 'Hộp Quà Kỷ Niệm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Quà Kỷ Niệm</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- 5. TEDDY COUPLE (Mega Dropdown) -->
                    <li class="nav-item has-megamenu">
                        <a href="{{ route('products.index', ['category_id' => 4]) }}" class="nav-link {{ request('category_id') == 4 ? 'active' : '' }}">
                            TEDDY COUPLE <i class="fa-solid fa-chevron-down"></i>
                        </a>
                        <div class="megamenu-panel">
                            <div class="megamenu-container">
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">CẶP ĐÔI TÌNH YÊU</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Cặp Gấu Cô Dâu Chú Rể']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cặp Cô Dâu Chú Rể</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Cặp Teddy Áo Đôi Trái Tim']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cặp Teddy Áo Đôi</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Cặp Gấu Ôm Bó Hoa']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cặp Gấu Ôm Bó Hoa</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">QUÀ TẶNG VALENTINE</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Set Kỷ Niệm 100 Ngày']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Set Kỷ Niệm 100 Ngày</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Hộp Mica Couple Sang Trọng']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Hộp Mica Sang Trọng</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Gấu Bông Tỏ Tình']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Gấu Bông Tỏ Tình</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">KÍCH THƯỚC COUPLE</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Couple 40cm Dễ Thương', 'size' => '40cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Couple 40cm Dễ Thương</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Couple 50cm - 60cm', 'size' => '50cm']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Couple 50cm – 60cm</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Couple Khổng Lồ 1m2', 'size' => '1m2']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Couple Khổng Lồ 1m2</a></li>
                                    </ul>
                                </div>
                                <div class="megamenu-col">
                                    <div class="megamenu-heading">CẶP ĐÔI THEO MÀU</div>
                                    <ul class="megamenu-list">
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Cặp Hồng - Trắng Pastel']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cặp Hồng – Trắng Pastel</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Cặp Nâu Socola - Kem Bơ']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cặp Nâu Socola – Kem</a></li>
                                        <li><a href="{{ route('products.index', ['category_id' => 4, 'sub' => 'Cặp Nâu Đất Vintage']) }}" class="megamenu-link"><i class="fa-solid fa-angle-right"></i> Cặp Nâu Đất Vintage</a></li>
                                    </ul>
                                </div>
                            </div>
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

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <!-- Col 1: Store Intro -->
            <div class="footer-col">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1rem;">
                    <div class="brand-icon" style="width: 40px; height: 40px; font-size: 20px;">
                        <i class="fa-solid fa-paw"></i>
                    </div>
                    <span style="font-size: 20px; font-weight: 900; color: #FFFFFF; letter-spacing: -0.5px;">Mật Ngọt Bear</span>
                </div>
                <p>Thương hiệu gấu bông cao cấp hàng đầu Việt Nam. Chúng mình mang đến những người bạn nhồi bông mềm mại, êm ái, an toàn 100% cho làn da và chất lượng thêu tỉ mỉ chuẩn từng đường kim mũi chỉ.</p>
                <p><i class="fa-solid fa-location-dot" style="color: var(--honey-gold); margin-right: 8px;"></i> Showroom: 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</p>
                <p><i class="fa-solid fa-phone" style="color: var(--honey-gold); margin-right: 8px;"></i> Hotline tư vấn & đặt hàng: <strong>097.989.6616</strong></p>
            </div>

            <!-- Col 2: Categories -->
            <div class="footer-col">
                <h4>BỘ SƯU TẬP TEDDY</h4>
                <ul class="footer-links" id="footer-categories-list">
                    <li><a href="{{ route('products.index', ['category_id' => 1]) }}"><i class="fa-solid fa-angle-right"></i> Teddy Classic Cổ Điển</a></li>
                    <li><a href="{{ route('products.index', ['category_id' => 2]) }}"><i class="fa-solid fa-angle-right"></i> Butter Bear Siêu Hot</a></li>
                    <li><a href="{{ route('products.index', ['category_id' => 3]) }}"><i class="fa-solid fa-angle-right"></i> Teddy Mr. Bean Vintage</a></li>
                    <li><a href="{{ route('products.index', ['category_id' => 4]) }}"><i class="fa-solid fa-angle-right"></i> Teddy Couple Đôi Bạn</a></li>
                    <li><a href="{{ route('products.index', ['category_id' => 5]) }}"><i class="fa-solid fa-angle-right"></i> Gối Bông Teddy Đa Năng</a></li>
                </ul>
            </div>

            <!-- Col 3: Customer Service -->
            <div class="footer-col">
                <h4>CHÍNH SÁCH BÁN HÀNG</h4>
                <ul class="footer-links">
                    <li><a href="javascript:void(0)" onclick="Swal.fire({title:'Chính sách đổi trả', text:'Đổi trả miễn phí trong 7 ngày nếu lỗi sản xuất hoặc không đúng mẫu.', icon:'info', confirmButtonColor:'#5D4037'})"><i class="fa-solid fa-angle-right"></i> Đổi Trả Trong 7 Ngày</a></li>
                    <li><a href="javascript:void(0)" onclick="Swal.fire({title:'Phí giao hàng', text:'Đồng giá ship 30.000đ toàn quốc. Miễn phí ship đơn từ 500k.', icon:'info', confirmButtonColor:'#5D4037'})"><i class="fa-solid fa-angle-right"></i> Giao Hàng Toàn Quốc 30k</a></li>
                    <li><a href="javascript:void(0)" onclick="Swal.fire({title:'Bảo hành gấu bông', text:'Bảo hành đường chỉ may và bông nhồi trọn đời tại cửa hàng.', icon:'info', confirmButtonColor:'#5D4037'})"><i class="fa-solid fa-angle-right"></i> Bảo Hành Đường May Trọn Đời</a></li>
                    <li><a href="javascript:void(0)" onclick="Swal.fire({title:'Dịch vụ gói quà', text:'Miễn phí gói quà tặng kèm nơ và thiệp viết tay xinh xắn.', icon:'info', confirmButtonColor:'#5D4037'})"><i class="fa-solid fa-angle-right"></i> Gói Quà & Tặng Thiệp Xinh</a></li>
                </ul>
            </div>

            <!-- Col 4: Fanpage & Admin portal -->
            <div class="footer-col">
                <h4>KẾT NỐI VỚI CHÚNG MÌNH</h4>
                <p>Theo dõi fanpage Mật Ngọt Bear để nhận voucher giảm giá 15% cho đơn hàng đầu tiên!</p>
                <div style="display: flex; gap: 10px;">
                    <a href="https://facebook.com" target="_blank" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #FFFFFF;"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://tiktok.com" target="_blank" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #FFFFFF;"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="https://instagram.com" target="_blank" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: #FFFFFF;"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>&copy; 2026 Mật Ngọt Bear. Bản quyền thuộc về Mật Ngọt Bear - Thế giới gấu bông mềm mịn.</div>
            <div style="display: flex; gap: 16px;">
                <span><i class="fa-solid fa-shield-halved" style="color: var(--honey-gold);"></i> 100% Bông Sạch Kháng Khuẩn</span>
                <span><i class="fa-solid fa-truck-fast" style="color: var(--honey-gold);"></i> Đóng Gói Hút Chân Không Gọn Gàng</span>
            </div>
        </div>
    </footer>

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
        let cartItemsCount = parseInt(localStorage.getItem('mn_cart_count') || '0');
        let wishlistCount = parseInt(localStorage.getItem('mn_wishlist_count') || '0');
        updateCartBadge();

        function updateCartBadge() {
            const badge = document.getElementById('cart-count');
            if (badge) badge.innerText = cartItemsCount;
            const wBadge = document.getElementById('wishlist-count');
            if (wBadge) wBadge.innerText = wishlistCount;
        }

        function addToCart(productId, productName = 'Gấu bông', qty = 1) {
            cartItemsCount += qty;
            localStorage.setItem('mn_cart_count', cartItemsCount);
            updateCartBadge();
            Toast.fire({
                icon: 'success',
                title: `Đã thêm "${productName}" vào giỏ hàng!`
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
                Swal.fire({
                    icon: 'success',
                    title: `Giỏ hàng (${cartItemsCount} sản phẩm)`,
                    text: 'Bạn đã chọn ' + cartItemsCount + ' món đồ dễ thương. Hãy tiến hành thanh toán nhé!',
                    confirmButtonColor: '#5D4037',
                    confirmButtonText: 'Xem thêm sản phẩm'
                });
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

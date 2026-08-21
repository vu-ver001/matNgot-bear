<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mật Ngọt Bear - Quản Trị Hệ Thống</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #8D6E63;
            
            --honey: #E59819;
            --honey-light: #FFF8E7;
            --honey-dark: #B87309;
            --honey-gold: #F6D89B;
            
            --text-main: #4E342E;
            --text-muted: #795548;
            --text-light: #9E8076;
            
            --border: #EADFCF;
            --border-light: #F0E7DB;
            
            --success: #2E7D32;
            --success-bg: #E8F5E9;
            --warning: #EF6C00;
            --warning-bg: #FFF3E0;
            --danger: #C62828;
            --danger-bg: #FFEBEE;
            
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            
            --shadow-subtle: 0 2px 8px rgba(109, 76, 65, 0.04);
            --shadow-card: 0 8px 24px rgba(109, 76, 65, 0.07);
            --shadow-hover: 0 14px 32px rgba(109, 76, 65, 0.12);
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
        }

        /* Top Header */
        header.admin-header {
            background: linear-gradient(135deg, #795548 0%, #5D4037 100%);
            color: #FFFFFF;
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .header-container {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #FFFFFF;
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
            box-shadow: 0 4px 12px rgba(229, 152, 25, 0.35);
        }

        .brand-title {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .brand-title span.badge-admin {
            font-size: 11px;
            font-weight: 600;
            color: #F6D89B;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            backdrop-filter: blur(4px);
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            background: #F6D89B;
            color: #3E2723;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        /* Main Layout */
        main.admin-main {
            max-width: 1440px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
            flex: 1;
        }

        /* Quick Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-subtle);
            transition: all 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-card);
            border-color: #D7CCC8;
        }

        .stat-info .stat-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .stat-value {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.brown { background: #EFEBE9; color: #5D4037; }
        .stat-icon.honey { background: var(--honey-light); color: var(--honey-dark); }
        .stat-icon.green { background: var(--success-bg); color: var(--success); }
        .stat-icon.red { background: var(--danger-bg); color: var(--danger); }

        /* Tabs Navigation */
        .tabs-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-surface);
            padding: 6px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            margin-bottom: 1.75rem;
            width: fit-content;
        }

        .tab-btn {
            border: none;
            background: transparent;
            color: var(--text-muted);
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 700;
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.5);
        }

        .tab-btn.active {
            background: var(--bg-card);
            color: var(--primary-dark);
            box-shadow: 0 2px 8px rgba(62, 39, 35, 0.08);
        }

        /* Panel Cards */
        .panel-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 1.75rem;
            box-shadow: var(--shadow-card);
        }

        .panel-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-light);
        }

        .panel-title {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Toolbar & Filters */
        .toolbar-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 260px;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 14px;
        }

        .input-control, .select-control {
            width: 100%;
            background: var(--bg-page);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 10px 14px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }

        .search-box .input-control {
            padding-left: 38px;
        }

        .input-control:focus, .select-control:focus {
            border-color: var(--primary-light);
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(93, 64, 55, 0.1);
        }

        .filter-select {
            min-width: 160px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #5D4037 0%, #3E2723 100%);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(62, 39, 35, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(62, 39, 35, 0.3);
            background: linear-gradient(135deg, #4E342E 0%, #2D1B17 100%);
        }

        .btn-honey {
            background: linear-gradient(135deg, #F6D89B 0%, #E59819 100%);
            color: #3E2723;
            box-shadow: 0 4px 12px rgba(229, 152, 25, 0.25);
        }

        .btn-honey:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(229, 152, 25, 0.35);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background: var(--bg-surface);
            border-color: #D7CCC8;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-icon:hover {
            color: var(--primary-dark);
            background: var(--bg-surface);
            border-color: #D7CCC8;
        }

        .btn-icon.danger:hover {
            background: var(--danger-bg);
            color: var(--danger);
            border-color: #FFCDD2;
        }

        /* Modern Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: var(--bg-card);
        }

        table.modern-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        table.modern-table th {
            background: var(--bg-surface);
            color: var(--text-muted);
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.modern-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            color: var(--text-main);
        }

        table.modern-table tr:last-child td {
            border-bottom: none;
        }

        table.modern-table tr:hover td {
            background: rgba(247, 243, 235, 0.4);
        }

        /* Product Table Elements */
        .product-cell {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .product-thumb {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            object-fit: cover;
            border: 1px solid var(--border);
            background: var(--bg-surface);
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .product-thumb:hover {
            transform: scale(1.1);
        }

        .product-meta .product-name {
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .product-meta .product-sku {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-category {
            background: #EFEBE9;
            color: #5D4037;
            border: 1px solid #D7CCC8;
        }

        .badge-active {
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid #C8E6C9;
        }

        .badge-inactive {
            background: #EEEEEE;
            color: #757575;
            border: 1px solid #E0E0E0;
        }

        .badge-stock-normal {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .badge-stock-low {
            background: #FFF3E0;
            color: #E65100;
            border: 1px solid #FFE0B2;
            animation: pulse 2s infinite;
        }

        .badge-stock-out {
            background: #FFEBEE;
            color: #C62828;
            border: 1px solid #FFCDD2;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Price Formatting */
        .price-sale {
            color: #D32F2F;
            font-weight: 700;
        }

        .price-original.crossed {
            text-decoration: line-through;
            color: var(--text-light);
            font-size: 12px;
            margin-left: 4px;
        }

        /* Category Slider Carousel */
        .category-slider-wrapper {
            position: relative;
            margin-bottom: 2rem;
        }

        .category-slider-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .category-slider-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-slider-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-slider-track {
            display: flex;
            gap: 1.25rem;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 4px 2px 14px 2px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .category-slider-track::-webkit-scrollbar {
            display: none;
        }

        .category-card {
            flex: 0 0 250px;
            min-width: 250px;
            max-width: 250px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
            transition: all 0.25s ease;
            box-shadow: var(--shadow-subtle);
            user-select: none;
        }

        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-card);
            border-color: #D7CCC8;
        }

        .category-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .category-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-md);
            background: var(--honey-light);
            color: var(--honey-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .category-name {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--text-muted);
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-main);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .page-btn:hover:not(:disabled) {
            background: var(--bg-surface);
            border-color: #D7CCC8;
        }

        .page-btn.active {
            background: var(--primary-dark);
            color: #FFFFFF;
            border-color: var(--primary-dark);
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Modal Backdrop & Dialog */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(45, 27, 23, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-dialog {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            width: 100%;
            max-width: 780px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.2);
            animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            0% { transform: scale(0.94); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-family: 'Be Vietnam Pro', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
        }

        .modal-body {
            padding: 1.75rem;
        }

        .modal-footer {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            background: var(--bg-surface);
            border-bottom-left-radius: var(--radius-xl);
            border-bottom-right-radius: var(--radius-xl);
        }

        /* Form Controls inside Modal */
        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-label span.req {
            color: var(--danger);
        }

        textarea.input-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Gallery Manager inside Modal */
        .gallery-section {
            background: var(--bg-surface);
            border: 1px dashed var(--border);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            margin-top: 0.5rem;
        }

        .gallery-input-row {
            display: flex;
            gap: 8px;
            margin-bottom: 1rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
        }

        .gallery-item-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            box-shadow: var(--shadow-subtle);
        }

        .gallery-item-card.is-primary {
            border: 2px solid var(--honey);
            background: var(--honey-light);
        }

        .gallery-item-thumb {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
        }

        .gallery-item-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 4px;
        }

        .primary-badge-btn {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-muted);
            cursor: pointer;
        }

        .gallery-item-card.is-primary .primary-badge-btn {
            background: var(--honey);
            color: #3E2723;
            border-color: var(--honey-dark);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--border);
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            main.admin-main { padding: 1rem; }
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
            .header-container { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="admin-header">
        <div class="header-container">
            <a href="/admin" class="brand-logo">
                <div class="brand-icon">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <div class="brand-title">
                    Mật Ngọt Bear
                    <span class="badge-admin">Admin Dashboard</span>
                </div>
            </a>

            <div class="header-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline" style="color: #FFFFFF; border-color: rgba(255,255,255,0.3); font-size: 13px; padding: 6px 14px; background: rgba(255,255,255,0.1);">
                    <i class="fa-solid fa-bars-staggered"></i> Menu Quản Trị
                </a>
                <a href="/" target="_blank" class="btn btn-outline" style="color: #FFFFFF; border-color: rgba(255,255,255,0.25); font-size: 13px; padding: 6px 14px;">
                    <i class="fa-solid fa-store"></i> Xem Cửa Hàng
                </a>
                <div class="user-pill">
                    <div class="user-avatar">AD</div>
                    <span>Quản Trị Viên</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-main">
        
        <!-- Stats Summary -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Tổng Sản Phẩm</div>
                    <div class="stat-value" id="stat-total-products">--</div>
                </div>
                <div class="stat-icon brown">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Danh Mục</div>
                    <div class="stat-value" id="stat-total-categories">--</div>
                </div>
                <div class="stat-icon honey">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Đang Bán</div>
                    <div class="stat-value" id="stat-active-products">--</div>
                </div>
                <div class="stat-icon green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <div class="stat-label">Cảnh Báo Tồn Kho (&le; 5)</div>
                    <div class="stat-value" id="stat-low-stock">--</div>
                </div>
                <div class="stat-icon red">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs-nav">
            <button class="tab-btn active" id="tab-btn-products" onclick="switchTab('products')">
                <i class="fa-solid fa-cubes"></i> Quản Lý Sản Phẩm
            </button>
            <button class="tab-btn" id="tab-btn-categories" onclick="switchTab('categories')">
                <i class="fa-solid fa-folder-tree"></i> Quản Lý Danh Mục
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: PRODUCT MANAGEMENT PANEL -->
        <!-- ============================================== -->
        <div id="panel-products" class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fa-solid fa-cubes" style="color: var(--primary);"></i>
                    Danh Sách Sản Phẩm & Tồn Kho
                </div>
                <button class="btn btn-primary" onclick="openProductModal()">
                    <i class="fa-solid fa-plus"></i> Thêm Sản Phẩm Mới
                </button>
            </div>

            <!-- Toolbar & Filters -->
            <div class="toolbar-grid">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="prod-search" class="input-control" placeholder="Tìm theo tên sản phẩm..." oninput="debounceSearchProduct()">
                </div>

                <div class="filter-select">
                    <select id="prod-filter-category" class="select-control" onchange="loadProducts()">
                        <option value="">-- Tất cả danh mục --</option>
                    </select>
                </div>

                <div class="filter-select">
                    <select id="prod-filter-status" class="select-control" onchange="loadProducts()">
                        <option value="">-- Trạng thái --</option>
                        <option value="ACTIVE">Đang bán (ACTIVE)</option>
                        <option value="INACTIVE">Ngừng bán (INACTIVE)</option>
                    </select>
                </div>

                <div class="filter-select">
                    <select id="prod-sort" class="select-control" onchange="loadProducts()">
                        <option value="created_at_desc">Mới nhất</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                        <option value="stock_quantity_asc">Tồn kho ít nhất</option>
                        <option value="sold_count_desc">Bán chạy nhất</option>
                    </select>
                </div>

                <button class="btn btn-outline" onclick="resetProductFilters()" title="Làm mới bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>

            <!-- Products Table -->
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#ID</th>
                            <th>Sản Phẩm</th>
                            <th>Danh Mục</th>
                            <th>Giá Bán</th>
                            <th>Tồn Kho</th>
                            <th>Đã Bán</th>
                            <th>Trạng Thái</th>
                            <th style="text-align: right; width: 120px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--primary);"></i>
                                <div style="margin-top: 8px; color: var(--text-muted);">Đang tải danh sách sản phẩm...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info" id="products-pagination-info">Hiển thị 0 / 0 sản phẩm</div>
                <div class="pagination-controls" id="products-pagination-controls"></div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: CATEGORY MANAGEMENT PANEL -->
        <!-- ============================================== -->
        <div id="panel-categories" class="panel-card" style="display: none;">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fa-solid fa-folder-tree" style="color: var(--honey-dark);"></i>
                    Quản Lý Danh Mục Sản Phẩm
                </div>
                <button class="btn btn-honey" onclick="openCategoryModal()">
                    <i class="fa-solid fa-plus"></i> Thêm Danh Mục Mới
                </button>
            </div>

            <!-- Category Slider Carousel -->
            <div class="category-slider-wrapper">
                <div class="category-slider-header">
                    <div class="category-slider-title">
                        <i class="fa-solid fa-layer-group" style="color: var(--honey-dark);"></i>
                        Danh Sách Danh Mục
                    </div>
                    <div class="category-slider-controls">
                        <button type="button" class="btn-icon" id="cat-btn-prev" onclick="slideCategories(-1)" title="Xem danh mục trước">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn-icon" id="cat-btn-next" onclick="slideCategories(1)" title="Xem danh mục tiếp theo">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="category-slider-track" id="category-grid-container">
                    <!-- Dynamically rendered simple cards -->
                </div>
            </div>

            <!-- Category Table View -->
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#ID</th>
                            <th>Tên Danh Mục</th>
                            <th>Mô Tả</th>
                            <th>Số Sản Phẩm</th>
                            <th>Trạng Thái</th>
                            <th style="text-align: right; width: 120px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="categories-table-body">
                        <!-- Dynamically rendered -->
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ============================================== -->
    <!-- MODAL: ADD / EDIT PRODUCT -->
    <!-- ============================================== -->
    <div class="modal-backdrop" id="modal-product">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-title" id="modal-product-title">Thêm Sản Phẩm Mới</div>
                <button class="btn-icon" onclick="closeProductModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-product" onsubmit="saveProduct(event)">
                    <input type="hidden" id="prod-id" value="">

                    <!-- Row 1: Name & Category -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Tên sản phẩm <span class="req">*</span></label>
                            <input type="text" id="prod-form-name" class="input-control" required placeholder="Ví dụ: Gấu Teddy Nơ Hồng 30cm">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Danh mục cha <span class="req">*</span></label>
                            <select id="prod-form-category" class="select-control" required>
                                <option value="">-- Chọn danh mục --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Pricing & Stock -->
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Giá gốc (VNĐ) <span class="req">*</span></label>
                            <input type="number" id="prod-form-price" class="input-control" required min="0" step="1000" placeholder="189000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Giá khuyến mãi (VNĐ)</label>
                            <input type="number" id="prod-form-sale-price" class="input-control" min="0" step="1000" placeholder="149000 (nếu có)">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số lượng tồn kho <span class="req">*</span></label>
                            <input type="number" id="prod-form-stock" class="input-control" required min="0" placeholder="50">
                        </div>
                    </div>

                    <!-- Row 3: Attributes -->
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Kích thước (size)</label>
                            <input type="text" id="prod-form-size" class="input-control" placeholder="30cm, 1m2...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Màu sắc (color)</label>
                            <input type="text" id="prod-form-color" class="input-control" placeholder="Nâu, Hồng pastel...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Chất liệu (material)</label>
                            <input type="text" id="prod-form-material" class="input-control" placeholder="Bông PP 3D, Vải nhung...">
                        </div>
                    </div>

                    <!-- Row 4: Status -->
                    <div class="form-group">
                        <label class="form-label">Trạng thái kinh doanh</label>
                        <select id="prod-form-status" class="select-control">
                            <option value="ACTIVE">ACTIVE - Đang mở bán</option>
                            <option value="INACTIVE">INACTIVE - Ngừng kinh doanh</option>
                        </select>
                    </div>

                    <!-- Row 5: Description -->
                    <div class="form-group">
                        <label class="form-label">Mô tả sản phẩm</label>
                        <textarea id="prod-form-desc" class="input-control" rows="3" placeholder="Mô tả chi tiết về sản phẩm gấu bông..."></textarea>
                    </div>

                    <!-- Row 6: Image Gallery Manager -->
                    <div class="form-group">
                        <label class="form-label">Quản lý Ảnh sản phẩm (Gallery)</label>
                        <div class="gallery-section">
                            <div class="gallery-input-row">
                                <input type="text" id="gallery-input-url" class="input-control" placeholder="Nhập đường dẫn URL ảnh (VD: https://placehold.co/600x600...)">
                                <button type="button" class="btn btn-honey" onclick="addImageToGallery()">
                                    <i class="fa-solid fa-plus"></i> Thêm Ảnh
                                </button>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                                * Mẹo: Click "Đặt làm đại diện" để chọn ảnh chính cho sản phẩm. Có thể sắp xếp thứ tự hiển thị.
                            </div>
                            
                            <!-- Gallery items grid -->
                            <div class="gallery-grid" id="gallery-preview-grid">
                                <!-- Dynamically filled -->
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeProductModal()">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-product">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu Sản Phẩm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL: ADD / EDIT CATEGORY -->
    <!-- ============================================== -->
    <div class="modal-backdrop" id="modal-category">
        <div class="modal-dialog" style="max-width: 520px;">
            <div class="modal-header">
                <div class="modal-title" id="modal-category-title">Thêm Danh Mục Mới</div>
                <button class="btn-icon" onclick="closeCategoryModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-category" onsubmit="saveCategory(event)">
                    <input type="hidden" id="cat-id" value="">

                    <div class="form-group">
                        <label class="form-label">Tên danh mục <span class="req">*</span></label>
                        <input type="text" id="cat-form-name" class="input-control" required placeholder="Ví dụ: Gấu Bông Khổng Lồ">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mô tả danh mục</label>
                        <textarea id="cat-form-desc" class="input-control" rows="3" placeholder="Mô tả tóm tắt về loại danh mục này..."></textarea>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 1rem;">
                        <input type="checkbox" id="cat-form-active" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                        <label for="cat-form-active" style="font-size: 14px; font-weight: 600; cursor: pointer;">Kích hoạt danh mục này</label>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Hủy</button>
                        <button type="submit" class="btn btn-honey" id="btn-save-category">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu Danh Mục
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- JAVASCRIPT LOGIC -->
    <!-- ============================================== -->
    <script>
        // Global State
        let categoriesList = [];
        let currentProductPage = 1;
        let productGallery = []; // Array of { image_url: '', is_primary: bool, sort_order: int }
        let searchTimeout = null;

        // Custom SweetAlert Toast Mixin
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#FAF6F0',
            color: '#3E2723',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            loadCategories();
            loadProducts();
            loadStats();
        });

        // Switch Tab Logic
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`).classList.add('active');

            if (tab === 'products') {
                document.getElementById('panel-products').style.display = 'block';
                document.getElementById('panel-categories').style.display = 'none';
                loadProducts();
            } else {
                document.getElementById('panel-products').style.display = 'none';
                document.getElementById('panel-categories').style.display = 'block';
                loadCategories();
            }
        }

        // ==========================================
        // 1. STATS LOGIC
        // ==========================================
        async function loadStats() {
            try {
                const [prodRes, catRes] = await Promise.all([
                    fetch('/api/admin/products?per_page=100'),
                    fetch('/api/admin/categories?all=true')
                ]);
                const prodData = await prodRes.json();
                const catData = await catRes.json();

                if (prodData.success) {
                    const products = prodData.data.data || [];
                    document.getElementById('stat-total-products').innerText = prodData.data.total || products.length;
                    
                    const activeCount = products.filter(p => p.status === 'ACTIVE').length;
                    document.getElementById('stat-active-products').innerText = activeCount;

                    const lowStockCount = products.filter(p => p.stock_quantity <= 5).length;
                    document.getElementById('stat-low-stock').innerText = lowStockCount;
                }

                if (catData.success) {
                    document.getElementById('stat-total-categories').innerText = (catData.data || []).length;
                }
            } catch (err) {
                console.error("Lỗi khi tải thống kê:", err);
            }
        }

        // ==========================================
        // 2. CATEGORY CRUD
        // ==========================================
        async function loadCategories() {
            try {
                // Call Public API to get categories with products_count
                const res = await fetch('/api/categories');
                const data = await res.json();

                if (data.success) {
                    categoriesList = data.data;
                    renderCategoryDropdowns();
                    renderCategoryViews();
                }
            } catch (err) {
                console.error("Lỗi tải danh mục:", err);
                Toast.fire({ icon: 'error', title: 'Không thể tải danh mục sản phẩm' });
            }
        }

        function renderCategoryDropdowns() {
            const filterDropdown = document.getElementById('prod-filter-category');
            const formDropdown = document.getElementById('prod-form-category');
            
            const currentFilterVal = filterDropdown.value;
            const currentFormVal = formDropdown.value;

            let filterOptions = '<option value="">-- Tất cả danh mục --</option>';
            let formOptions = '<option value="">-- Chọn danh mục --</option>';

            categoriesList.forEach(c => {
                filterOptions += `<option value="${c.id}">${c.name}</option>`;
                formOptions += `<option value="${c.id}">${c.name}</option>`;
            });

            filterDropdown.innerHTML = filterOptions;
            formDropdown.innerHTML = formOptions;

            filterDropdown.value = currentFilterVal;
            formDropdown.value = currentFormVal;
        }

        function slideCategories(direction) {
            const track = document.getElementById('category-grid-container');
            if (!track) return;
            const scrollAmount = 270; // card width (250px) + gap (20px)
            track.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }

        function renderCategoryViews() {
            // Render Cards Track
            const grid = document.getElementById('category-grid-container');
            const tbody = document.getElementById('categories-table-body');

            if (categoriesList.length === 0) {
                grid.innerHTML = '<div class="empty-state" style="padding: 1.5rem; width: 100%;"><i class="fa-solid fa-folder-open"></i><p>Chưa có danh mục nào.</p></div>';
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Chưa có danh mục nào.</td></tr>';
                return;
            }

            // Simple Compact Cards (Icon, Name, Status Badge - không xuống dòng)
            grid.innerHTML = categoriesList.map(c => `
                <div class="category-card">
                    <div class="category-card-top">
                        <div class="category-icon">
                            <i class="fa-solid fa-paw"></i>
                        </div>
                        <span class="badge ${c.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${c.is_active ? '<i class="fa-solid fa-check"></i> Hoạt động' : 'Tạm ẩn'}
                        </span>
                    </div>
                    <div class="category-name" title="${c.name}">${c.name}</div>
                </div>
            `).join('');

            // Table
            tbody.innerHTML = categoriesList.map(c => `
                <tr>
                    <td><strong>#${c.id}</strong></td>
                    <td style="font-weight: 700;">${c.name}</td>
                    <td style="color: var(--text-muted); max-width: 320px;">${c.description || '-'}</td>
                    <td>
                        <span class="badge badge-category">
                            <i class="fa-solid fa-box"></i> ${c.products_count || 0} SP
                        </span>
                    </td>
                    <td>
                        <span class="badge ${c.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${c.is_active ? 'Kích hoạt' : 'Tạm ẩn'}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px;">
                            <button class="btn-icon" onclick="editCategory(${c.id})" title="Sửa">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn-icon danger" onclick="deleteCategory(${c.id}, '${c.name}', ${c.products_count || 0})" title="Xóa">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function openCategoryModal() {
            document.getElementById('modal-category-title').innerText = 'Thêm Danh Mục Mới';
            document.getElementById('cat-id').value = '';
            document.getElementById('cat-form-name').value = '';
            document.getElementById('cat-form-desc').value = '';
            document.getElementById('cat-form-active').checked = true;
            document.getElementById('modal-category').classList.add('open');
        }

        function closeCategoryModal() {
            document.getElementById('modal-category').classList.remove('open');
        }

        function editCategory(id) {
            const cat = categoriesList.find(c => c.id === id);
            if (!cat) return;

            document.getElementById('modal-category-title').innerText = 'Chỉnh Sửa Danh Mục';
            document.getElementById('cat-id').value = cat.id;
            document.getElementById('cat-form-name').value = cat.name;
            document.getElementById('cat-form-desc').value = cat.description || '';
            document.getElementById('cat-form-active').checked = !!cat.is_active;
            document.getElementById('modal-category').classList.add('open');
        }

        async function saveCategory(e) {
            e.preventDefault();
            const id = document.getElementById('cat-id').value;
            const payload = {
                name: document.getElementById('cat-form-name').value.trim(),
                description: document.getElementById('cat-form-desc').value.trim(),
                is_active: document.getElementById('cat-form-active').checked
            };

            const url = id ? `/api/admin/categories/${id}` : '/api/admin/categories';
            const method = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    Toast.fire({ icon: 'success', title: data.message || 'Lưu danh mục thành công!' });
                    closeCategoryModal();
                    loadCategories();
                    loadStats();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể lưu danh mục',
                        text: data.message || 'Vui lòng kiểm tra lại thông tin.',
                        confirmButtonColor: '#5D4037'
                    });
                }
            } catch (err) {
                console.error("Lỗi lưu category:", err);
                Toast.fire({ icon: 'error', title: 'Lỗi máy chủ kết nối' });
            }
        }

        async function deleteCategory(id, name, productsCount) {
            if (productsCount > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Không thể xóa!',
                    text: `Danh mục "${name}" hiện có ${productsCount} sản phẩm. Hãy xóa hoặc chuyển sản phẩm trước.`,
                    confirmButtonColor: '#5D4037'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Xác nhận xóa?',
                text: `Bạn có chắc muốn xóa danh mục "${name}" không?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#C62828',
                cancelButtonColor: '#8D6E63',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy'
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch(`/api/admin/categories/${id}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        Toast.fire({ icon: 'success', title: data.message || 'Xóa danh mục thành công!' });
                        loadCategories();
                        loadStats();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message, confirmButtonColor: '#5D4037' });
                    }
                } catch (err) {
                    Toast.fire({ icon: 'error', title: 'Lỗi kết nối khi xóa' });
                }
            }
        }

        // ==========================================
        // 3. PRODUCT CRUD & STOCK MANAGEMENT
        // ==========================================
        function debounceSearchProduct() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentProductPage = 1;
                loadProducts();
            }, 350);
        }

        function resetProductFilters() {
            document.getElementById('prod-search').value = '';
            document.getElementById('prod-filter-category').value = '';
            document.getElementById('prod-filter-status').value = '';
            document.getElementById('prod-sort').value = 'created_at_desc';
            currentProductPage = 1;
            loadProducts();
        }

        async function loadProducts(page = currentProductPage) {
            currentProductPage = page;
            const search = document.getElementById('prod-search').value.trim();
            const categoryId = document.getElementById('prod-filter-category').value;
            const status = document.getElementById('prod-filter-status').value;
            const sortVal = document.getElementById('prod-sort').value;

            let sortBy = 'created_at';
            let sortDir = 'desc';
            if (sortVal === 'price_asc') { sortBy = 'price'; sortDir = 'asc'; }
            if (sortVal === 'price_desc') { sortBy = 'price'; sortDir = 'desc'; }
            if (sortVal === 'stock_quantity_asc') { sortBy = 'stock_quantity'; sortDir = 'asc'; }
            if (sortVal === 'sold_count_desc') { sortBy = 'sold_count'; sortDir = 'desc'; }

            const queryParams = new URLSearchParams({
                page: page,
                per_page: 8,
                sort_by: sortBy,
                sort_dir: sortDir
            });

            if (search) queryParams.append('search', search);
            if (categoryId) queryParams.append('category_id', categoryId);
            if (status) queryParams.append('status', status);

            try {
                const res = await fetch(`/api/admin/products?${queryParams.toString()}`);
                const data = await res.json();

                if (data.success) {
                    renderProductsTable(data.data);
                }
            } catch (err) {
                console.error("Lỗi tải sản phẩm:", err);
                Toast.fire({ icon: 'error', title: 'Không thể tải danh sách sản phẩm' });
            }
        }

        function renderProductsTable(paginator) {
            const tbody = document.getElementById('products-table-body');
            const products = paginator.data || [];

            if (products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fa-solid fa-box-open"></i>
                            <p>Không tìm thấy sản phẩm nào phù hợp.</p>
                        </td>
                    </tr>
                `;
                document.getElementById('products-pagination-info').innerText = 'Hiển thị 0 sản phẩm';
                document.getElementById('products-pagination-controls').innerHTML = '';
                return;
            }

            tbody.innerHTML = products.map(p => {
                // Find primary image or first image
                const primaryImg = (p.images && p.images.find(img => img.is_primary)) || (p.images && p.images[0]) || { image_url: 'https://placehold.co/100x100?text=No+Image' };
                
                // Stock Badge
                let stockBadge = '';
                if (p.stock_quantity === 0) {
                    stockBadge = `<span class="badge badge-stock-out"><i class="fa-solid fa-circle-xmark"></i> Hết hàng</span>`;
                } else if (p.stock_quantity <= 5) {
                    stockBadge = `<span class="badge badge-stock-low"><i class="fa-solid fa-triangle-exclamation"></i> Còn ${p.stock_quantity}</span>`;
                } else {
                    stockBadge = `<span class="badge badge-stock-normal"><i class="fa-solid fa-check"></i> ${p.stock_quantity}</span>`;
                }

                // Price display
                const priceFormatted = Number(p.price).toLocaleString('vi-VN') + ' đ';
                const saleFormatted = p.sale_price ? Number(p.sale_price).toLocaleString('vi-VN') + ' đ' : null;

                return `
                    <tr>
                        <td><strong>#${p.id}</strong></td>
                        <td>
                            <div class="product-cell">
                                <img src="${primaryImg.image_url}" alt="${p.name}" class="product-thumb" onerror="this.src='https://placehold.co/100x100?text=Gau+Bong'">
                                <div class="product-meta">
                                    <div class="product-name">${p.name}</div>
                                    <div class="product-sku">Kích thước: ${p.size || 'N/A'} &bull; Màu: ${p.color || 'N/A'}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-category">
                                ${p.category ? p.category.name : 'Chưa phân loại'}
                            </span>
                        </td>
                        <td>
                            ${saleFormatted 
                                ? `<span class="price-sale">${saleFormatted}</span><span class="price-original crossed">${priceFormatted}</span>`
                                : `<span style="font-weight: 700; color: var(--text-main);">${priceFormatted}</span>`
                            }
                        </td>
                        <td>${stockBadge}</td>
                        <td>
                            <span style="font-weight: 600; color: var(--text-muted); font-size: 13px;">
                                <i class="fa-solid fa-fire" style="color: #FF5722;"></i> ${p.sold_count || 0}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${p.status === 'ACTIVE' ? 'badge-active' : 'badge-inactive'}">
                                ${p.status === 'ACTIVE' ? 'Đang bán' : 'Ngừng bán'}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn-icon" onclick="editProduct(${p.id})" title="Chỉnh sửa sản phẩm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-icon danger" onclick="toggleProductStatus(${p.id}, '${p.name}', '${p.status}')" title="${p.status === 'ACTIVE' ? 'Ngừng kinh doanh' : 'Xóa / Tạm dừng'}">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Pagination info & controls
            document.getElementById('products-pagination-info').innerText = 
                `Hiển thị trang ${paginator.current_page} / ${paginator.last_page} (Tổng ${paginator.total} sản phẩm)`;

            renderPaginationControls(paginator);
        }

        function renderPaginationControls(paginator) {
            const container = document.getElementById('products-pagination-controls');
            let html = '';

            // Prev Button
            html += `<button class="page-btn" ${paginator.current_page === 1 ? 'disabled' : ''} onclick="loadProducts(${paginator.current_page - 1})"><i class="fa-solid fa-chevron-left"></i></button>`;

            // Page numbers
            for (let i = 1; i <= paginator.last_page; i++) {
                if (i === 1 || i === paginator.last_page || (i >= paginator.current_page - 1 && i <= paginator.current_page + 1)) {
                    html += `<button class="page-btn ${i === paginator.current_page ? 'active' : ''}" onclick="loadProducts(${i})">${i}</button>`;
                } else if (i === paginator.current_page - 2 || i === paginator.current_page + 2) {
                    html += `<span style="padding: 0 4px; color: var(--text-light);">...</span>`;
                }
            }

            // Next Button
            html += `<button class="page-btn" ${paginator.current_page === paginator.last_page ? 'disabled' : ''} onclick="loadProducts(${paginator.current_page + 1})"><i class="fa-solid fa-chevron-right"></i></button>`;

            container.innerHTML = html;
        }

        // ==========================================
        // 4. PRODUCT MODAL & GALLERY MANAGEMENT
        // ==========================================
        function openProductModal() {
            document.getElementById('modal-product-title').innerText = 'Thêm Sản Phẩm Mới';
            document.getElementById('prod-id').value = '';
            document.getElementById('prod-form-name').value = '';
            document.getElementById('prod-form-category').value = categoriesList.length > 0 ? categoriesList[0].id : '';
            document.getElementById('prod-form-price').value = '';
            document.getElementById('prod-form-sale-price').value = '';
            document.getElementById('prod-form-stock').value = '20';
            document.getElementById('prod-form-size').value = '';
            document.getElementById('prod-form-color').value = '';
            document.getElementById('prod-form-material').value = '';
            document.getElementById('prod-form-status').value = 'ACTIVE';
            document.getElementById('prod-form-desc').value = '';

            // Reset Gallery with sample placeholder
            productGallery = [
                {
                    image_url: 'https://placehold.co/600x600/f5e6ca/7c4a2d?text=Gau+Bong+Mat+Ngot',
                    is_primary: true,
                    sort_order: 0
                }
            ];
            renderGalleryGrid();

            document.getElementById('modal-product').classList.add('open');
        }

        function closeProductModal() {
            document.getElementById('modal-product').classList.remove('open');
        }

        async function editProduct(id) {
            try {
                const res = await fetch(`/api/admin/products/${id}`);
                const data = await res.json();

                if (!data.success || !data.data) {
                    Toast.fire({ icon: 'error', title: 'Không tìm thấy thông tin sản phẩm' });
                    return;
                }

                const p = data.data;
                document.getElementById('modal-product-title').innerText = `Chỉnh Sửa: ${p.name}`;
                document.getElementById('prod-id').value = p.id;
                document.getElementById('prod-form-name').value = p.name;
                document.getElementById('prod-form-category').value = p.category_id;
                document.getElementById('prod-form-price').value = p.price;
                document.getElementById('prod-form-sale-price').value = p.sale_price || '';
                document.getElementById('prod-form-stock').value = p.stock_quantity;
                document.getElementById('prod-form-size').value = p.size || '';
                document.getElementById('prod-form-color').value = p.color || '';
                document.getElementById('prod-form-material').value = p.material || '';
                document.getElementById('prod-form-status').value = p.status;
                document.getElementById('prod-form-desc').value = p.description || '';

                // Fill Gallery
                productGallery = (p.images && p.images.length > 0) ? p.images.map(img => ({
                    image_url: img.image_url,
                    is_primary: !!img.is_primary,
                    sort_order: img.sort_order || 0
                })) : [];

                renderGalleryGrid();
                document.getElementById('modal-product').classList.add('open');
            } catch (err) {
                console.error("Lỗi lấy chi tiết sản phẩm:", err);
                Toast.fire({ icon: 'error', title: 'Lỗi tải chi tiết sản phẩm' });
            }
        }

        function addImageToGallery() {
            const input = document.getElementById('gallery-input-url');
            const url = input.value.trim();
            if (!url) {
                Toast.fire({ icon: 'warning', title: 'Vui lòng nhập đường dẫn URL ảnh' });
                return;
            }

            const isFirst = productGallery.length === 0;
            productGallery.push({
                image_url: url,
                is_primary: isFirst,
                sort_order: productGallery.length
            });

            input.value = '';
            renderGalleryGrid();
        }

        function renderGalleryGrid() {
            const grid = document.getElementById('gallery-preview-grid');
            if (productGallery.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--text-light); font-size: 13px; padding: 10px;">Chưa có ảnh nào trong bộ sưu tập.</div>';
                return;
            }

            grid.innerHTML = productGallery.map((img, idx) => `
                <div class="gallery-item-card ${img.is_primary ? 'is-primary' : ''}">
                    <img src="${img.image_url}" class="gallery-item-thumb" onerror="this.src='https://placehold.co/300x300?text=Loi+Anh'">
                    <div style="width: 100%; margin-bottom: 6px; text-align: center;">
                        <button type="button" class="primary-badge-btn" onclick="setPrimaryImage(${idx})">
                            ${img.is_primary ? '★ Ảnh Đại Diện' : 'Đặt làm đại diện'}
                        </button>
                    </div>
                    <div class="gallery-item-actions">
                        <button type="button" class="btn-icon" style="width: 26px; height: 26px; font-size: 11px;" onclick="moveImageOrder(${idx}, -1)" title="Lên trên" ${idx === 0 ? 'disabled' : ''}>
                            <i class="fa-solid fa-arrow-up"></i>
                        </button>
                        <button type="button" class="btn-icon" style="width: 26px; height: 26px; font-size: 11px;" onclick="moveImageOrder(${idx}, 1)" title="Xuống dưới" ${idx === productGallery.length - 1 ? 'disabled' : ''}>
                            <i class="fa-solid fa-arrow-down"></i>
                        </button>
                        <button type="button" class="btn-icon danger" style="width: 26px; height: 26px; font-size: 11px;" onclick="removeImageFromGallery(${idx})" title="Xóa ảnh">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function setPrimaryImage(index) {
            productGallery.forEach((img, i) => {
                img.is_primary = (i === index);
            });
            renderGalleryGrid();
        }

        function moveImageOrder(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= productGallery.length) return;

            const temp = productGallery[index];
            productGallery[index] = productGallery[newIndex];
            productGallery[newIndex] = temp;

            // Re-assign sort orders
            productGallery.forEach((img, i) => img.sort_order = i);
            renderGalleryGrid();
        }

        function removeImageFromGallery(index) {
            const wasPrimary = productGallery[index].is_primary;
            productGallery.splice(index, 1);

            if (wasPrimary && productGallery.length > 0) {
                productGallery[0].is_primary = true;
            }
            productGallery.forEach((img, i) => img.sort_order = i);
            renderGalleryGrid();
        }

        async function saveProduct(e) {
            e.preventDefault();
            const id = document.getElementById('prod-id').value;

            // Validation
            const price = parseFloat(document.getElementById('prod-form-price').value);
            const salePriceInput = document.getElementById('prod-form-sale-price').value;
            const salePrice = salePriceInput ? parseFloat(salePriceInput) : null;

            if (salePrice !== null && salePrice >= price) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Giá khuyến mãi không hợp lệ',
                    text: 'Giá khuyến mãi phải nhỏ hơn giá gốc của sản phẩm.',
                    confirmButtonColor: '#5D4037'
                });
                return;
            }

            const payload = {
                name: document.getElementById('prod-form-name').value.trim(),
                category_id: parseInt(document.getElementById('prod-form-category').value),
                price: price,
                sale_price: salePrice,
                stock_quantity: parseInt(document.getElementById('prod-form-stock').value),
                size: document.getElementById('prod-form-size').value.trim() || null,
                color: document.getElementById('prod-form-color').value.trim() || null,
                material: document.getElementById('prod-form-material').value.trim() || null,
                status: document.getElementById('prod-form-status').value,
                description: document.getElementById('prod-form-desc').value.trim() || null,
                images: productGallery.map((img, idx) => ({
                    image_url: img.image_url,
                    is_primary: !!img.is_primary,
                    sort_order: idx
                }))
            };

            const url = id ? `/api/admin/products/${id}` : '/api/admin/products';
            const method = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    Toast.fire({ icon: 'success', title: data.message || 'Lưu sản phẩm thành công!' });
                    closeProductModal();
                    loadProducts();
                    loadStats();
                } else {
                    let errMsg = data.message || 'Vui lòng kiểm tra lại dữ liệu nhập.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể lưu sản phẩm',
                        html: errMsg,
                        confirmButtonColor: '#5D4037'
                    });
                }
            } catch (err) {
                console.error("Lỗi lưu sản phẩm:", err);
                Toast.fire({ icon: 'error', title: 'Lỗi kết nối máy chủ' });
            }
        }

        async function toggleProductStatus(id, name, currentStatus) {
            const result = await Swal.fire({
                title: 'Ngừng kinh doanh sản phẩm?',
                text: `Bạn có chắc muốn chuyển trạng thái sản phẩm "${name}" sang INACTIVE (Ngừng bán)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF6C00',
                cancelButtonColor: '#8D6E63',
                confirmButtonText: 'Đồng ý chuyển',
                cancelButtonText: 'Hủy'
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch(`/api/admin/products/${id}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        Toast.fire({ icon: 'success', title: data.message || 'Cập nhật trạng thái thành công!' });
                        loadProducts();
                        loadStats();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Lỗi', text: data.message, confirmButtonColor: '#5D4037' });
                    }
                } catch (err) {
                    Toast.fire({ icon: 'error', title: 'Lỗi máy chủ khi cập nhật' });
                }
            }
        }
    </script>
</body>
</html>

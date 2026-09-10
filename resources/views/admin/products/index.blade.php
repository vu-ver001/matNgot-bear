@extends('layouts.admin-dashboard')

@php $currentPage = 'products'; @endphp

@section('page-title', 'Quản Lý Sản Phẩm Gấu Bông')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-products-index.css') }}">
@endsection

@section('content')

<div class="prod-index-wrap">
<!-- Header Banner -->
<div class="prod-page-header">
    <div class="prod-page-title-group">
        <h1>
            Quản Lý Sản Phẩm Gấu Bông
        </h1>
        <div class="prod-page-desc">
            Theo dõi kho hàng, phân loại kích thước/màu sắc, bảng giá bán và trạng thái kinh doanh theo thời gian thực.
        </div>
    </div>
    <div class="prod-page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn-gold-primary">
            <i class="fa-solid fa-plus"></i>
            <span>Thêm Sản Phẩm Mới</span>
        </a>
    </div>
</div>

<!-- 1. Stats Summary (4 Thẻ KPI Kính Mờ Be Sữa Cao Cấp) -->
<div class="stats-grid">
    <div class="stat-card stat-total" id="card-filter-all" onclick="filterByKpi('all')" title="Nhấn để xem tất cả">
        <div class="stat-info">
            <div class="stat-label">Tổng Sản Phẩm</div>
            <div class="stat-value" id="stat-total-products">--</div>
            <div class="stat-sub" id="stat-sub-total"><i class="fa-solid fa-database"></i> Dữ liệu toàn kho</div>
        </div>
        <div class="stat-icon brown">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </div>

    <div class="stat-card stat-active" id="card-filter-active" onclick="filterByKpi('active')" title="Nhấn để lọc đang kinh doanh">
        <div class="stat-info">
            <div class="stat-label">Đang Kinh Doanh</div>
            <div class="stat-value" id="stat-active-products" style="color: #2E7D32;">--</div>
            <div class="stat-sub" id="stat-sub-active" style="color: #2E7D32;"><i class="fa-solid fa-store"></i> Hiển thị ngoài shop</div>
        </div>
        <div class="stat-icon green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>

    <div class="stat-card stat-inactive" id="card-filter-inactive" onclick="filterByKpi('inactive')" title="Nhấn để lọc ngừng kinh doanh">
        <div class="stat-info">
            <div class="stat-label">Ngừng Kinh Doanh</div>
            <div class="stat-value" id="stat-inactive-products" style="color: #D97706;">--</div>
            <div class="stat-sub" id="stat-sub-inactive" style="color: #D97706;"><i class="fa-solid fa-eye-slash"></i> Tạm ẩn khỏi khách</div>
        </div>
        <div class="stat-icon honey">
            <i class="fa-solid fa-circle-pause"></i>
        </div>
    </div>

    <div class="stat-card stat-warning" id="card-filter-warning" onclick="filterByKpi('warning')" title="Nhấn để lọc cảnh báo tồn kho (≤ 5)">
        <div class="stat-info">
            <div class="stat-label">Cảnh Báo Tồn Kho (&le; 5)</div>
            <div class="stat-value" id="stat-low-stock" style="color: #C62828;">--</div>
            <div class="stat-sub" id="stat-sub-warning" style="color: #C62828;"><i class="fa-solid fa-triangle-exclamation"></i> Cần nhập thêm hàng</div>
        </div>
        <div class="stat-icon red">
            <i class="fa-solid fa-boxes-packing"></i>
        </div>
    </div>
</div>

<!-- 2. Products Panel Card -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <span class="panel-title-icon">
                <i class="fa-solid fa-box-open"></i>
            </span>
            <span>Danh Sách Sản Phẩm</span>
            <span id="prod-counter-pill" class="badge-category" style="margin-left: 4px; font-size: 12px;">Đang tải...</span>
        </div>
        <div class="panel-header-actions">
            <!-- Cụm chuyển đổi Chế độ xem: Sản phẩm cha vs Sản phẩm con -->
            <div class="segment-view-switch">
                <button type="button" class="segment-btn active" id="btn-view-parents" onclick="switchViewMode('parents')" title="Xem danh sách Sản phẩm cha">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Sản phẩm cha</span>
                    <span class="segment-count-badge" id="badge-count-parents">--</span>
                </button>
                <button type="button" class="segment-btn" id="btn-view-variants" onclick="switchViewMode('variants')" title="Xem chi tiết các Sản phẩm con (Kích thước & Màu sắc)">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Sản phẩm con</span>
                    <span class="segment-count-badge" id="badge-count-variants">--</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toolbar & Filters -->
    <div class="toolbar-grid">
        <!-- Search Box -->
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="prod-search" class="input-control" placeholder="Tìm theo tên gấu bông, mã số ID, SKU..." oninput="onSearchInput()">
            <button type="button" id="prod-search-clear" class="search-clear-btn" onclick="clearSearch()" title="Xóa tìm kiếm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Filter Danh Mục -->
        <div class="filter-select">
            <select id="prod-cat-filter" class="select-control" onchange="loadCurrentData(1)">
                <option value="">-- Tất cả danh mục --</option>
            </select>
        </div>

        <!-- Filter Trạng Thái -->
        <div class="filter-select">
            <select id="prod-status-filter" class="select-control" onchange="onFilterDropdownChange()">
                <option value="">-- Trạng thái bán --</option>
                <option value="ACTIVE">Đang kinh doanh</option>
                <option value="INACTIVE">Ngừng kinh doanh</option>
            </select>
        </div>

        <!-- Filter Kho Hàng -->
        <div class="filter-select">
            <select id="prod-stock-filter" class="select-control" onchange="onFilterDropdownChange()">
                <option value="">-- Tình trạng kho --</option>
                <option value="in_stock">Còn hàng dồi dào (>5)</option>
                <option value="low_stock">Sắp hết hàng (1-5)</option>
                <option value="out_of_stock">Đã hết hàng (0)</option>
            </select>
        </div>

        <!-- Sắp Xếp -->
        <div class="filter-select">
            <select id="prod-sort-filter" class="select-control" onchange="loadCurrentData(1)">
                <option value="latest">Mới nhất trước</option>
                <option value="price_asc">Giá: Thấp &rarr; Cao</option>
                <option value="price_desc">Giá: Cao &rarr; Thấp</option>
                <option value="stock_asc">Tồn kho: Ít nhất</option>
                <option value="best_seller">Bán chạy nhất</option>
            </select>
        </div>

        <!-- Reset Button -->
        <button type="button" class="btn-tool-icon" onclick="resetProductFilters()" title="Làm mới bộ lọc về mặc định">
            <i class="fa-solid fa-rotate-left"></i>
        </button>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table class="data-table">
            <thead id="products-table-head">
                <tr>
                    <th style="width: 65px; text-align: center;">Ảnh</th>
                    <th style="width: 210px;">Thông Tin Gấu Bông</th>
                    <th style="width: 130px;">Danh Mục</th>
                    <th style="width: 140px;">Giá Bán</th>
                    <th style="width: 140px;">Phân Loại (Size/Màu)</th>
                    <th style="width: 145px; text-align: center;">Tồn Kho</th>
                    <th style="width: 100px; text-align: center;">Trạng Thái</th>
                    <th style="width: 110px; text-align: right;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <tr>
                    <td colspan="8" style="text-align: center; padding: 4rem;">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 28px; color: var(--mn-gold-dark);"></i>
                        <p style="margin-top: 12px; color: var(--mn-brown-subtle); font-weight: 600;">Đang kết nối cơ sở dữ liệu gấu bông...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" id="products-pagination">
        <!-- Rendered via JS -->
    </div>
</div>
</div> <!-- end .prod-index-wrap -->

<!-- 3. Quick View Modal (Xem Nhanh Chi Tiết & Biến Thể Con) -->
<div class="modal-backdrop-luxury" id="quickViewModal" onclick="handleBackdropClick(event)">
    <div class="modal-dialog-luxury">
        <div class="modal-header-luxury">
            <div class="modal-title">
                <i class="fa-solid fa-gem" style="color: var(--mn-gold-dark);"></i>
                <span id="qv-modal-title">Chi Tiết Sản Phẩm</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeQuickView()" title="Đóng cửa sổ">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body-luxury" id="qv-modal-body">
            <!-- Dynamic Content -->
        </div>

        <div class="modal-footer-luxury">
            <a href="#" id="qv-shop-link" target="_blank" class="page-btn" style="text-decoration: none;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem ngoài Shop
            </a>
            <a href="#" id="qv-edit-link" class="btn-gold-primary" style="padding: 9px 18px; font-size: 13px;">
                <i class="fa-solid fa-pen-to-square"></i> Chỉnh Sửa Sản Phẩm
            </a>
            <button type="button" class="page-btn" onclick="closeQuickView()">Đóng</button>
        </div>
    </div>
</div>

<script>
    let searchTimeout = null;
    let categoriesList = [];
    let currentLoadedProducts = [];
    let currentLoadedVariants = [];
    let selectedKpiFilter = null;
    let currentViewMode = 'parents'; // 'parents' hoặc 'variants'

    document.addEventListener('DOMContentLoaded', () => {
        loadCategoriesSelect();
        loadCurrentData(1);
    });

    // ==========================================
    // CHUYỂN ĐỔI CHẾ ĐỘ XEM: CHA vs CON
    // ==========================================
    function switchViewMode(mode) {
        if (currentViewMode === mode) return;
        currentViewMode = mode;

        // Cập nhật nút bấm
        document.getElementById('btn-view-parents').classList.toggle('active', mode === 'parents');
        document.getElementById('btn-view-variants').classList.toggle('active', mode === 'variants');

        // Cập nhật tiêu đề bảng
        const thead = document.getElementById('products-table-head');
        if (mode === 'parents') {
            thead.innerHTML = `
                <tr>
                    <th style="width: 65px; text-align: center;">Ảnh</th>
                    <th style="width: 210px;">Thông Tin Gấu Bông</th>
                    <th style="width: 130px;">Danh Mục</th>
                    <th style="width: 140px;">Giá Bán</th>
                    <th style="width: 140px;">Phân Loại (Size/Màu)</th>
                    <th style="width: 145px; text-align: center;">Tồn Kho</th>
                    <th style="width: 100px; text-align: center;">Trạng Thái</th>
                    <th style="width: 110px; text-align: right;">Thao Tác</th>
                </tr>
            `;
            // Cập nhật text phụ KPI
            document.getElementById('stat-sub-total').innerHTML = '<i class="fa-solid fa-database"></i> Dữ liệu toàn kho';
            document.getElementById('stat-sub-active').innerHTML = '<i class="fa-solid fa-store"></i> Hiển thị ngoài shop';
            document.getElementById('stat-sub-inactive').innerHTML = '<i class="fa-solid fa-eye-slash"></i> Tạm ẩn khỏi khách';
            document.getElementById('stat-sub-warning').innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Cần nhập thêm hàng';
        } else {
            thead.innerHTML = `
                <tr>
                    <th style="width: 60px; text-align: center;">Ảnh</th>
                    <th style="width: 75px; text-align: center;">ID Cha</th>
                    <th style="width: 75px; text-align: center;">ID Con</th>
                    <th>Sản Phẩm Cha &amp; SKU Con</th>
                    <th style="width: 105px;">Kích Thước</th>
                    <th style="width: 115px;">Màu Sắc</th>
                    <th style="width: 140px;">Giá Bán &amp; KM</th>
                    <th style="width: 90px; text-align: center;">Tồn Kho</th>
                    <th style="width: 110px; text-align: center;">Trạng Thái</th>
                    <th style="width: 85px; text-align: right;">Thao Tác</th>
                </tr>
            `;
            // Cập nhật text phụ KPI cho phân loại con
            document.getElementById('stat-sub-total').innerHTML = '<i class="fa-solid fa-layer-group"></i> Tổng biến thể size/màu';
            document.getElementById('stat-sub-active').innerHTML = '<i class="fa-solid fa-store"></i> Phân loại đang bán';
            document.getElementById('stat-sub-inactive').innerHTML = '<i class="fa-solid fa-eye-slash"></i> Phân loại tạm ẩn';
            document.getElementById('stat-sub-warning').innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Phân loại tồn kho ≤ 5';
        }

        // Tải lại dữ liệu trang 1 theo mode mới
        loadCurrentData(1);
    }

    async function loadCategoriesSelect() {
        try {
            const res = await fetch('/api/categories');
            const data = await res.json();
            if (data.success) {
                categoriesList = data.data;
                const filterSelect = document.getElementById('prod-cat-filter');
                
                let filterHtml = '<option value="">-- Tất cả danh mục --</option>';
                categoriesList.forEach(c => {
                    filterHtml += `<option value="${c.id}">${c.name}</option>`;
                });
                filterSelect.innerHTML = filterHtml;
            }
        } catch (e) {
            console.error("Lỗi nạp danh mục:", e);
        }
    }

    function loadCurrentData(page = 1) {
        if (currentViewMode === 'parents') {
            loadProducts(page);
        } else {
            loadVariants(page);
        }
    }

    // ==========================================
    // 1. TẢI DANH SÁCH SẢN PHẨM CHA
    // ==========================================
    async function loadProducts(page = 1) {
        const tbody = document.getElementById('products-table-body');
        const search = document.getElementById('prod-search').value.trim();
        const categoryId = document.getElementById('prod-cat-filter').value;
        const status = document.getElementById('prod-status-filter').value;
        const stockStatus = (selectedKpiFilter === 'warning') ? 'warning' : document.getElementById('prod-stock-filter').value;
        const sort = document.getElementById('prod-sort-filter').value;

        const params = new URLSearchParams({ page, per_page: 8, sort });
        if (search) params.append('search', search);
        if (categoryId) params.append('category_id', categoryId);
        if (status) params.append('status', status);
        if (stockStatus) params.append('stock_status', stockStatus);

        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 3.5rem;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 26px; color: var(--mn-gold-dark);"></i>
                    <p style="margin-top: 10px; color: var(--mn-brown-subtle); font-weight: 600;">Đang cập nhật danh sách gấu bông...</p>
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`/api/admin/products?${params.toString()}`);
            const data = await res.json();

            if (data.success) {
                currentLoadedProducts = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                const meta = data.meta || { current_page: 1, last_page: 1, total: currentLoadedProducts.length };
                renderProductsTable(currentLoadedProducts, meta);
                updateProductStats();
                syncKpiActiveState();

                const counterPill = document.getElementById('prod-counter-pill');
                if (counterPill) {
                    counterPill.innerText = `Tìm thấy ${meta.total} sản phẩm cha`;
                }
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem;">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-triangle-exclamation" style="color: var(--mn-red);"></i>
                                <h3>Không thể tải dữ liệu</h3>
                                <p>${data.message || 'Vui lòng thử lại sau.'}</p>
                                <button type="button" class="btn-gold-primary" onclick="loadProducts(1)">Tải lại</button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } catch (e) {
            console.error("Lỗi loadProducts:", e);
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem;">
                        <div class="empty-state-box">
                            <i class="fa-solid fa-circle-exclamation" style="color: var(--mn-red);"></i>
                            <h3>Lỗi kết nối máy chủ</h3>
                            <p>Đã xảy ra lỗi khi lấy dữ liệu sản phẩm. Vui lòng kiểm tra lại kết nối.</p>
                            <button type="button" class="btn-gold-primary" onclick="loadProducts(1)">Thử lại ngay</button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    function renderProductsTable(products, meta) {
        const tbody = document.getElementById('products-table-body');
        if (!products || products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3.5rem;">
                        <div class="empty-state-box">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>Không tìm thấy gấu bông nào</h3>
                            <p>Không có sản phẩm nào khớp với từ khóa tìm kiếm hoặc bộ lọc hiện tại.</p>
                            <button type="button" class="page-btn" style="margin: 0 auto; display: inline-flex;" onclick="resetProductFilters()">
                                <i class="fa-solid fa-rotate-left"></i> Đặt lại bộ lọc
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            document.getElementById('products-pagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = products.map(p => {
            const primaryImg = (p.images && p.images.find(img => img.is_primary)) || (p.images && p.images[0]) || { image_url: 'https://placehold.co/100x100/F7EFE9/5D4037?text=Gau+Bong' };
            const imgCount = (p.images && p.images.length) || 0;
            const variantCount = (p.variants && p.variants.length) || 0;

            const isOnSale = p.is_on_sale !== undefined ? Boolean(p.is_on_sale) : (p.sale_price && Number(p.sale_price) < Number(p.price));
            let discountPercent = 0;
            if (isOnSale && p.price && p.sale_price) {
                discountPercent = Math.round(((Number(p.price) - Number(p.sale_price)) / Number(p.price)) * 100);
            }

            let sizesList = [];
            let colorsList = [];
            if (p.variants && p.variants.length > 0) {
                sizesList = [...new Set(p.variants.map(v => v.size).filter(Boolean))];
                colorsList = [...new Set(p.variants.map(v => v.color).filter(Boolean))];
            } else {
                if (p.size) sizesList.push(p.size);
                if (p.color) colorsList.push(p.color);
            }

            let stockHtml = '';
            const stockQty = Number(p.stock_quantity) || 0;
            // Kiểm tra sản phẩm cha có chứa ít nhất 1 phân loại con (size/màu) có tồn kho <= 5
            const hasWarningVariant = p.variants && p.variants.some(v => Number(v.stock_quantity) <= 5);

            if (stockQty <= 0) {
                stockHtml = `<span class="stock-pill out-stock"><i class="fa-solid fa-circle-xmark"></i> 0</span>`;
            } else if (hasWarningVariant) {
                const minVariantStock = Math.min(...p.variants.map(v => Number(v.stock_quantity)));
                stockHtml = `
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="stock-pill in-stock">
                            <i class="fa-solid fa-circle-check"></i> ${stockQty}
                        </span>
                        <i class="fa-solid fa-circle-exclamation stock-warning-icon" style="color: #E08A1E; font-size: 15px; cursor: help;" title="Có sản phẩm con tồn kho &le; 5 (thấp nhất: ${minVariantStock})"></i>
                    </div>
                `;
            } else {
                stockHtml = `<span class="stock-pill in-stock"><i class="fa-solid fa-circle-check"></i> ${stockQty}</span>`;
            }

            const soldCount = p.sold_count || 0;

            return `
                <tr>
                    <!-- 1. Ảnh sản phẩm -->
                    <td style="text-align: center;">
                        <div class="prod-thumb-cell" style="margin: 0 auto;">
                            <img src="${primaryImg.image_url}" class="prod-thumb-img" alt="${p.name}" onerror="this.src='https://placehold.co/100x100/F7EFE9/5D4037?text=Gau'">
                            ${imgCount > 1 ? `<span class="prod-thumb-badge" title="${imgCount} ảnh thư viện"><i class="fa-solid fa-images"></i> ${imgCount}</span>` : ''}
                        </div>
                    </td>

                    <!-- 2. Tên & Danh mục -->
                    <td>
                        <div class="prod-info-wrap">
                            <a href="javascript:void(0)" onclick="openQuickView(${p.id})" class="prod-title-link" title="${p.name}">
                                ${p.name}
                            </a>
                            <div class="prod-meta-tags">
                                <span class="badge-id">#${p.id}</span>
                                ${variantCount > 0 ? `
                                    <span class="badge-variant-pill" title="Sản phẩm có ${variantCount} phân loại kích thước/màu sắc">
                                        <i class="fa-solid fa-layer-group"></i> ${variantCount} mẫu
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                    </td>

                    <!-- 3. Danh Mục Trực Quan -->
                    <td>
                        <span class="badge-category">
                            <i class="fa-solid fa-tag"></i> ${p.category ? p.category.name : 'Chưa phân loại'}
                        </span>
                    </td>

                    <!-- 4. Giá Bán & Khuyến Mãi -->
                    <td>
                        <div class="price-display-wrap">
                            ${isOnSale 
                                ? `
                                    <div class="price-sale-highlight">
                                        ${Number(p.sale_price).toLocaleString('vi-VN')} đ
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span class="price-original-crossed">${Number(p.price).toLocaleString('vi-VN')} đ</span>
                                        ${discountPercent > 0 ? `<span class="badge-sale-percent">-${discountPercent}%</span>` : ''}
                                    </div>
                                  `
                                : `
                                    <div class="price-main">${Number(p.price).toLocaleString('vi-VN')} đ</div>
                                    ${p.sale_price && Number(p.sale_price) < Number(p.price)
                                        ? `<div style="font-size: 11px; color: var(--mn-brown-subtle);"><i class="fa-regular fa-clock"></i> Hết hạn KM</div>`
                                        : ''
                                    }
                                  `
                            }
                        </div>
                    </td>

                    <!-- 5. Phân Loại Tóm Tắt -->
                    <td>
                        <div class="variant-chips-wrap">
                            ${sizesList.length > 0 
                                ? sizesList.slice(0, 1).map(s => `<span class="chip-tag"><i class="fa-solid fa-ruler-combined" style="font-size: 9px;"></i> ${s}</span>`).join('') 
                                : ''
                            }
                            ${colorsList.length > 0 
                                ? colorsList.slice(0, 1).map(c => `<span class="chip-tag" style="background:#FFF9EC; border-color:#F0C475; color:#8D6E63;"><i class="fa-solid fa-palette" style="font-size: 9px;"></i> ${c}</span>`).join('') 
                                : ''
                            }
                            ${(sizesList.length > 1 || colorsList.length > 1) 
                                ? `<span class="chip-tag" style="color: var(--mn-gold-dark); font-weight: 800;">+more</span>` 
                                : ''
                            }
                            ${(sizesList.length === 0 && colorsList.length === 0) 
                                ? `<span style="color: var(--mn-brown-subtle); font-size: 12px;">Tiêu chuẩn</span>` 
                                : ''
                            }
                        </div>
                    </td>

                    <!-- 6. Tồn Kho & Đã Bán -->
                    <td style="text-align: center; min-width: 140px;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px;">
                            ${stockHtml}
                            <div class="sold-tag">
                                <i class="fa-solid fa-fire" style="color: #E65100;"></i> Đã bán: ${soldCount}
                            </div>
                        </div>
                    </td>

                    <!-- 7. Trạng Thái Kinh Doanh (Switch toggle giống sản phẩm con) -->
                    <td style="text-align: center;">
                        <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                            <div class="switch-toggle-box" onclick="toggleProductStatus(${p.id}, '${p.status}', '${p.name.replace(/'/g, "\\'")}')" title="Bấm để ${p.status === 'ACTIVE' ? 'tạm ngừng bán' : 'mở bán'} sản phẩm này">
                                <div class="switch-toggle-track ${p.status === 'ACTIVE' ? 'active' : ''}">
                                    <span class="switch-toggle-thumb"></span>
                                </div>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; color: ${p.status === 'ACTIVE' ? '#10B981' : '#8D6E63'};">
                                ${p.status === 'ACTIVE' ? 'Bật' : 'Tắt'}
                            </span>
                        </div>
                    </td>

                    <!-- 8. Thao Tác (Có Xem nhanh) -->
                    <td style="text-align: right;">
                        <div class="actions-cell-wrap">
                            <!-- Xem nhanh -->
                            <button type="button" class="btn-action-round view" onclick="openQuickView(${p.id})" title="Xem nhanh toàn bộ chi tiết & biến thể">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <!-- Chỉnh sửa -->
                            <a href="/admin/products/${p.id}/edit" class="btn-action-round edit" title="Chỉnh sửa sản phẩm">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <!-- Đổi trạng thái / Ngừng bán -->
                            <button type="button" class="btn-action-round ${p.status === 'ACTIVE' ? 'toggle-off' : 'toggle-on'}" 
                                    onclick="toggleProductStatus(${p.id}, '${p.status}', '${p.name.replace(/'/g, "\\'")}')" 
                                    title="${p.status === 'ACTIVE' ? 'Tạm ngừng kinh doanh' : 'Mở bán trở lại'}">
                                <i class="fa-solid ${p.status === 'ACTIVE' ? 'fa-pause' : 'fa-play'}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        renderPagination(meta, 'products-pagination', 'loadProducts');
    }

    // ==========================================
    // 2. TẢI DANH SÁCH SẢN PHẨM CON (BIẾN THỂ)
    // ==========================================
    async function loadVariants(page = 1) {
        const tbody = document.getElementById('products-table-body');
        const search = document.getElementById('prod-search').value.trim();
        const categoryId = document.getElementById('prod-cat-filter').value;
        const status = document.getElementById('prod-status-filter').value;
        const stockStatus = (selectedKpiFilter === 'warning') ? 'warning' : document.getElementById('prod-stock-filter').value;
        const sort = document.getElementById('prod-sort-filter').value;

        const params = new URLSearchParams({ page, per_page: 10, sort });
        if (search) params.append('search', search);
        if (categoryId) params.append('category_id', categoryId);
        if (status) params.append('status', status);
        if (stockStatus) params.append('stock_status', stockStatus);

        tbody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 3.5rem;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 26px; color: var(--mn-gold-dark);"></i>
                    <p style="margin-top: 10px; color: var(--mn-brown-subtle); font-weight: 600;">Đang kết nối chi tiết các sản phẩm con...</p>
                </td>
            </tr>
        `;

        try {
            const res = await fetch(`/api/admin/product-variants?${params.toString()}`);
            const data = await res.json();

            if (data.success) {
                currentLoadedVariants = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                const meta = data.meta || { current_page: 1, last_page: 1, total: currentLoadedVariants.length };
                renderVariantsTable(currentLoadedVariants, meta);
                updateProductStats();
                syncKpiActiveState();

                const counterPill = document.getElementById('prod-counter-pill');
                if (counterPill) {
                    counterPill.innerText = `Tìm thấy ${meta.total} phân loại con`;
                }
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 3rem;">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-triangle-exclamation" style="color: var(--mn-red);"></i>
                                <h3>Không thể tải dữ liệu</h3>
                                <p>${data.message || 'Vui lòng thử lại sau.'}</p>
                                <button type="button" class="btn-gold-primary" onclick="loadVariants(1)">Tải lại</button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } catch (e) {
            console.error("Lỗi loadVariants:", e);
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 3rem;">
                        <div class="empty-state-box">
                            <i class="fa-solid fa-circle-exclamation" style="color: var(--mn-red);"></i>
                            <h3>Lỗi kết nối máy chủ</h3>
                            <p>Đã xảy ra lỗi khi lấy dữ liệu phân loại con. Vui lòng thử lại.</p>
                            <button type="button" class="btn-gold-primary" onclick="loadVariants(1)">Thử lại ngay</button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    function renderVariantsTable(variants, meta) {
        const tbody = document.getElementById('products-table-body');
        if (!variants || variants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 3.5rem;">
                        <div class="empty-state-box">
                            <i class="fa-solid fa-layer-group"></i>
                            <h3>Không tìm thấy sản phẩm con nào</h3>
                            <p>Không có phân loại nào khớp với từ khóa hoặc bộ lọc hiện tại.</p>
                            <button type="button" class="page-btn" style="margin: 0 auto; display: inline-flex;" onclick="resetProductFilters()">
                                <i class="fa-solid fa-rotate-left"></i> Đặt lại bộ lọc
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            document.getElementById('products-pagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = variants.map(v => {
            const p = v.product || {};
            const imgUrl = v.image_url || 'https://placehold.co/100x100/F7EFE9/5D4037?text=Gau';
            const isOnSale = Boolean(v.sale_price && Number(v.sale_price) < Number(v.price));
            const isActive = v.status === 'ACTIVE';

            let stockHtml = '';
            const stockQty = Number(v.stock_quantity) || 0;
            if (stockQty <= 0) {
                stockHtml = `<span class="stock-pill out-stock"><i class="fa-solid fa-circle-xmark"></i> 0</span>`;
            } else if (stockQty <= 5) {
                stockHtml = `<span class="stock-pill low-stock"><i class="fa-solid fa-triangle-exclamation"></i> ${stockQty}</span>`;
            } else {
                stockHtml = `<span class="stock-pill in-stock"><i class="fa-solid fa-circle-check"></i> ${stockQty}</span>`;
            }

            return `
                <tr>
                    <!-- 1. Ảnh Biến Thể -->
                    <td style="text-align: center;">
                        <div class="prod-thumb-cell" style="margin: 0 auto;">
                            <img src="${imgUrl}" class="prod-thumb-img" alt="${v.sku}" onerror="this.src='https://placehold.co/100x100/F7EFE9/5D4037?text=Gau'">
                        </div>
                    </td>

                    <!-- 2. ID Cha (Theo đúng yêu cầu) -->
                    <td style="text-align: center;">
                        <span class="badge-id" style="font-weight: 800; font-size: 12px; background: #FFF3E0; color: #E65100; border-color: #FFE0B2;" title="Mã ID Sản phẩm cha">
                            #${v.product_id}
                        </span>
                    </td>

                    <!-- 2b. ID Con (Theo đúng yêu cầu) -->
                    <td style="text-align: center;">
                        <span class="badge-id" style="font-weight: 800; font-size: 12px; background: #E8F5E9; color: #2E7D32; border-color: #C8E6C9;" title="Mã ID Phân loại con">
                            #${v.id}
                        </span>
                    </td>

                    <!-- 3. Tên Sản Phẩm Cha & SKU Con -->
                    <td>
                        <div class="prod-info-wrap">
                            <a href="/admin/products/${v.product_id}/edit" class="prod-title-link" title="Bấm để chỉnh sửa sản phẩm cha">
                                ${p.name || 'Sản phẩm #' + v.product_id}
                            </a>
                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 3px;">
                                <span style="font-size: 11px; font-weight: 700; color: #8D6E63;">
                                    <i class="fa-solid fa-barcode"></i> ${v.sku || '--'}
                                </span>
                                <span class="badge-category" style="font-size: 10.5px; padding: 1px 6px;">
                                    <i class="fa-solid fa-tag"></i> ${p.category ? p.category.name : 'Chưa phân loại'}
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- 4. Kích Thước -->
                    <td>
                        <span class="chip-tag">
                            <i class="fa-solid fa-ruler-combined" style="font-size: 9px;"></i> ${v.size || 'Tiêu chuẩn'}
                        </span>
                    </td>

                    <!-- 5. Màu Sắc -->
                    <td>
                        <span class="chip-tag" style="background:#FFF9EC; border-color:#F0C475; color:#8D6E63;">
                            <i class="fa-solid fa-palette" style="font-size: 9px;"></i> ${v.color || 'Tự nhiên'}
                        </span>
                    </td>

                    <!-- 6. Giá Bán & Khuyến Mãi -->
                    <td>
                        <div class="price-display-wrap">
                            ${isOnSale 
                                ? `
                                    <div class="price-sale-highlight">
                                        ${Number(v.sale_price).toLocaleString('vi-VN')} đ
                                    </div>
                                    <div class="price-original-crossed">
                                        ${Number(v.price).toLocaleString('vi-VN')} đ
                                    </div>
                                  `
                                : `
                                    <div class="price-main">${Number(v.price).toLocaleString('vi-VN')} đ</div>
                                  `
                            }
                        </div>
                    </td>

                    <!-- 7. Tồn Kho Con -->
                    <td style="text-align: center;">
                        ${stockHtml}
                    </td>

                    <!-- 8. Trạng Thái Con: Switch Toggle Button (Ảnh 3) -->
                    <td style="text-align: center;">
                        <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                            <div class="switch-toggle-box" onclick="toggleVariantStatusQuick(${v.id}, '${v.status}')" title="Bấm để ${isActive ? 'tạm ngừng bán' : 'mở bán'} phân loại này">
                                <div class="switch-toggle-track ${isActive ? 'active' : ''}">
                                    <span class="switch-toggle-thumb"></span>
                                </div>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; color: ${isActive ? '#10B981' : '#8D6E63'};">
                                ${isActive ? 'Bật' : 'Tắt'}
                            </span>
                        </div>
                    </td>

                    <!-- 9. Thao Tác: BỎ NÚT XEM NHANH THEO YÊU CẦU -->
                    <td style="text-align: right;">
                        <div class="actions-cell-wrap" style="justify-content: flex-end;">
                            <!-- Chỉnh sửa -->
                            <a href="/admin/products/${v.product_id}/edit" class="btn-action-round edit" title="Chỉnh sửa sản phẩm cha">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <!-- Bật / Tắt trạng thái con -->
                            <button type="button" class="btn-action-round ${isActive ? 'toggle-off' : 'toggle-on'}" 
                                    onclick="toggleVariantStatusQuick(${v.id}, '${v.status}')" 
                                    title="${isActive ? 'Tạm ngừng bán phân loại này' : 'Mở bán lại'}">
                                <i class="fa-solid ${isActive ? 'fa-pause' : 'fa-play'}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        renderPagination(meta, 'products-pagination', 'loadVariants');
    }

    // Đổi trạng thái trực tiếp của 1 phân loại con
    async function toggleVariantStatusQuick(id, currentStatus) {
        try {
            const res = await fetch(`/api/admin/product-variants/${id}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Đã cập nhật',
                    text: data.message,
                    timer: 1400,
                    showConfirmButton: false
                });
                loadCurrentData(1);
            } else {
                Swal.fire('Lỗi', data.message || 'Không thể đổi trạng thái.', 'error');
            }
        } catch (e) {
            console.error("Lỗi toggleVariantStatusQuick:", e);
            Swal.fire('Lỗi', 'Không thể kết nối máy chủ.', 'error');
        }
    }

    // ==========================================
    // BỘ LỌC & THỐNG KÊ KPI ĂN KHỚP VỚI TAB
    // ==========================================
    function filterByKpi(type) {
        selectedKpiFilter = type;
        const statusSelect = document.getElementById('prod-status-filter');
        const stockSelect = document.getElementById('prod-stock-filter');

        if (type === 'all') {
            statusSelect.value = '';
            stockSelect.value = '';
        } else if (type === 'active') {
            statusSelect.value = 'ACTIVE';
            stockSelect.value = '';
        } else if (type === 'inactive') {
            statusSelect.value = 'INACTIVE';
            stockSelect.value = '';
        } else if (type === 'warning') {
            statusSelect.value = '';
            stockSelect.value = '';
        }

        syncKpiActiveState();
        loadCurrentData(1);
    }

    function onFilterDropdownChange() {
        const status = document.getElementById('prod-status-filter').value;
        const stockStatus = document.getElementById('prod-stock-filter').value;

        if (status === 'ACTIVE' && !stockStatus) {
            selectedKpiFilter = 'active';
        } else if (status === 'INACTIVE' && !stockStatus) {
            selectedKpiFilter = 'inactive';
        } else {
            selectedKpiFilter = null;
        }

        syncKpiActiveState();
        loadCurrentData(1);
    }

    function syncKpiActiveState() {
        document.querySelectorAll('.prod-index-wrap .stat-card').forEach(c => c.classList.remove('active-kpi'));
        if (selectedKpiFilter) {
            document.getElementById(`card-filter-${selectedKpiFilter}`)?.classList.add('active-kpi');
        }
    }

    async function updateProductStats() {
        if (currentViewMode === 'parents') {
            // Lấy thống kê cho sản phẩm cha (Logic cảnh báo: chứa ít nhất 1 phân loại con <= 5)
            fetch('/api/admin/products/stats')
                .then(r => r.json())
                .then(d => { 
                    if (d.success && d.data) {
                        document.getElementById('stat-total-products').innerText = d.data.total;
                        document.getElementById('stat-active-products').innerText = d.data.active;
                        document.getElementById('stat-inactive-products').innerText = d.data.inactive;
                        document.getElementById('stat-low-stock').innerText = d.data.warning;
                        document.getElementById('badge-count-parents').innerText = d.data.total;
                    }
                })
                .catch(() => {});

            // Lấy tổng số con để hiển thị trên badge
            fetch('/api/admin/product-variants/stats')
                .then(r => r.json())
                .then(d => { if (d.success && d.data) document.getElementById('badge-count-variants').innerText = d.data.total; })
                .catch(() => {});
        } else {
            // Lấy thống kê cho sản phẩm con
            fetch('/api/admin/product-variants/stats')
                .then(r => r.json())
                .then(d => {
                    if (d.success && d.data) {
                        document.getElementById('stat-total-products').innerText = d.data.total;
                        document.getElementById('stat-active-products').innerText = d.data.active;
                        document.getElementById('stat-inactive-products').innerText = d.data.inactive;
                        document.getElementById('stat-low-stock').innerText = d.data.warning;
                        document.getElementById('badge-count-variants').innerText = d.data.total;
                    }
                })
                .catch(() => {});

            // Lấy tổng số cha để hiển thị trên badge
            fetch('/api/admin/products/stats')
                .then(r => r.json())
                .then(d => { if (d.success && d.data) document.getElementById('badge-count-parents').innerText = d.data.total; })
                .catch(() => {});
        }
    }

    function onSearchInput() {
        const val = document.getElementById('prod-search').value.trim();
        const clearBtn = document.getElementById('prod-search-clear');
        if (clearBtn) {
            clearBtn.style.display = val ? 'block' : 'none';
        }
        debounceSearchProduct();
    }

    function clearSearch() {
        document.getElementById('prod-search').value = '';
        const clearBtn = document.getElementById('prod-search-clear');
        if (clearBtn) clearBtn.style.display = 'none';
        loadCurrentData(1);
    }

    function debounceSearchProduct() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadCurrentData(1), 320);
    }

    function resetProductFilters() {
        document.getElementById('prod-search').value = '';
        const clearBtn = document.getElementById('prod-search-clear');
        if (clearBtn) clearBtn.style.display = 'none';

        document.getElementById('prod-cat-filter').value = '';
        document.getElementById('prod-status-filter').value = '';
        document.getElementById('prod-stock-filter').value = '';
        document.getElementById('prod-sort-filter').value = 'latest';
        syncKpiActiveState();
        loadCurrentData(1);
    }

    async function toggleProductStatus(id, currentStatus, productName = '') {
        const isCurrentlyActive = currentStatus === 'ACTIVE';
        const actionText = isCurrentlyActive ? 'ngừng kinh doanh' : 'mở bán trở lại';
        const confirmColor = isCurrentlyActive ? '#C62828' : '#2E7D32';

        const result = await Swal.fire({
            title: `Xác nhận ${actionText}?`,
            html: `Bạn có chắc muốn ${actionText} sản phẩm <strong>${productName || '#' + id}</strong>?<br><small style="color:#795548;">Sản phẩm sẽ ${isCurrentlyActive ? 'tạm ẩn khỏi danh mục khách hàng' : 'hiển thị lại cho khách hàng đặt mua'}.</small>`,
            icon: isCurrentlyActive ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#8D6E63',
            confirmButtonText: `Đồng ý ${actionText}`,
            cancelButtonText: 'Giữ nguyên'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch(`/api/admin/products/${id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Thành công', 
                        text: data.message, 
                        timer: 1600, 
                        showConfirmButton: false 
                    });
                    loadCurrentData(1);
                } else {
                    Swal.fire('Lỗi', data.message || 'Không thể cập nhật trạng thái.', 'error');
                }
            } catch (err) {
                console.error("Lỗi toggleProductStatus:", err);
                Swal.fire('Lỗi', 'Không thể kết nối máy chủ để cập nhật.', 'error');
            }
        }
    }

    /* QUICK VIEW MODAL LOGIC */
    function openQuickView(productId) {
        const product = currentLoadedProducts.find(p => p.id === productId);
        if (!product) return;

        document.getElementById('qv-modal-title').innerText = `${product.name} (#${product.id})`;
        document.getElementById('qv-edit-link').href = `/admin/products/${product.id}/edit`;
        document.getElementById('qv-shop-link').href = `/products/${product.id}`;

        const primaryImg = (product.images && product.images.find(img => img.is_primary)) || (product.images && product.images[0]) || { image_url: 'https://placehold.co/400x400/F7EFE9/5D4037?text=Gau+Bong' };
        const imagesList = product.images || [];

        const isOnSale = product.is_on_sale !== undefined ? Boolean(product.is_on_sale) : (product.sale_price && Number(product.sale_price) < Number(product.price));
        const variants = product.variants || [];

        let modalBodyHtml = `
            <div class="qv-grid">
                <!-- Cột Trái: Ảnh & Thư viện -->
                <div class="qv-gallery">
                    <img src="${primaryImg.image_url}" id="qv-main-preview" class="qv-main-img" alt="${product.name}">
                    ${imagesList.length > 1 ? `
                        <div class="qv-thumbs-row">
                            ${imagesList.map((img, idx) => `
                                <img src="${img.image_url}" class="qv-thumb-item ${img.image_url === primaryImg.image_url ? 'active' : ''}" 
                                     onclick="changeQvPreview('${img.image_url}', this)" alt="Ảnh ${idx + 1}">
                            `).join('')}
                        </div>
                    ` : ''}
                </div>

                <!-- Cột Phải: Thông tin chi tiết -->
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span class="badge-category">${product.category ? product.category.name : 'Chưa phân loại'}</span>
                        <span class="status-pill ${product.status === 'ACTIVE' ? 'active' : 'inactive'}" style="cursor: default;">
                            <span class="dot"></span> ${product.status === 'ACTIVE' ? 'Đang kinh doanh' : 'Ngừng bán'}
                        </span>
                    </div>

                    <h2 class="qv-info-title">${product.name}</h2>

                    <div class="qv-price-box">
                        ${isOnSale ? `
                            <div class="price-sale-highlight" style="font-size: 22px;">
                                ${Number(product.sale_price).toLocaleString('vi-VN')} đ
                            </div>
                            <div class="price-original-crossed" style="font-size: 15px;">
                                ${Number(product.price).toLocaleString('vi-VN')} đ
                            </div>
                            <span class="badge-sale-percent">FLASH SALE</span>
                        ` : `
                            <div class="price-main" style="font-size: 22px;">
                                ${Number(product.price).toLocaleString('vi-VN')} đ
                            </div>
                        `}
                    </div>

                    <div style="font-size: 13px; color: var(--mn-brown-subtle); line-height: 1.5; margin-bottom: 14px;">
                        ${product.description ? product.description.substring(0, 160) + '...' : 'Sản phẩm gấu bông cao cấp, êm ái, an toàn cho mọi lứa tuổi.'}
                    </div>

                    <div style="display: flex; gap: 20px; font-size: 13px; font-weight: 700; color: var(--mn-brown-deep); margin-bottom: 12px;">
                        <div><i class="fa-solid fa-boxes-stacked" style="color: var(--mn-gold-dark);"></i> Tổng kho: <strong>${product.stock_quantity}</strong> con</div>
                        <div><i class="fa-solid fa-fire" style="color: #E65100;"></i> Đã bán: <strong>${product.sold_count || 0}</strong></div>
                    </div>

                    <!-- Bảng biến thể nếu có -->
                    ${variants.length > 0 ? `
                        <div style="font-size: 13px; font-weight: 800; color: var(--mn-brown-deep); margin-top: 14px;">
                            <i class="fa-solid fa-layer-group" style="color: var(--mn-gold-dark);"></i> Danh sách ${variants.length} Phân Loại (Biến Thể Con):
                        </div>
                        <div style="max-height: 160px; overflow-y: auto; border: 1px solid var(--mn-beige-border); border-radius: 8px; margin-top: 6px;">
                            <table class="qv-variants-table" style="margin-top: 0;">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Màu Sắc</th>
                                        <th>Giá Bán</th>
                                        <th>Tồn Kho</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${variants.map(v => `
                                        <tr>
                                            <td><strong>${v.size || '—'}</strong></td>
                                            <td>${v.color || '—'}</td>
                                            <td>${Number(v.sale_price || v.price).toLocaleString('vi-VN')} đ</td>
                                            <td><span style="font-weight: 700; ${v.stock_quantity <= 5 ? 'color: var(--mn-red);' : ''}">${v.stock_quantity}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        document.getElementById('qv-modal-body').innerHTML = modalBodyHtml;
        document.getElementById('quickViewModal').classList.add('show');
    }

    function changeQvPreview(imgUrl, thumbElem) {
        document.getElementById('qv-main-preview').src = imgUrl;
        document.querySelectorAll('.qv-thumb-item').forEach(el => el.classList.remove('active'));
        if (thumbElem) thumbElem.classList.add('active');
    }

    function closeQuickView() {
        document.getElementById('quickViewModal').classList.remove('show');
    }

    function handleBackdropClick(event) {
        if (event.target.id === 'quickViewModal') {
            closeQuickView();
        }
    }

    /* PAGINATION LOGIC */
    function renderPagination(meta, containerId, funcName) {
        const wrap = document.getElementById(containerId);
        if (!meta || meta.last_page <= 1) {
            wrap.innerHTML = `<div class="pagination-info">Hiển thị <strong>${meta.total}</strong> kết quả</div>`;
            return;
        }

        let html = `<div class="pagination-info">Trang <strong>${meta.current_page}</strong> / <strong>${meta.last_page}</strong> (Tổng <strong>${meta.total}</strong> mục)</div>`;
        html += '<div class="pagination-controls">';
        html += `<button class="page-btn" ${meta.current_page === 1 ? 'disabled' : ''} onclick="${funcName}(${meta.current_page - 1})"><i class="fa-solid fa-chevron-left"></i></button>`;

        for (let i = 1; i <= meta.last_page; i++) {
            if (i === 1 || i === meta.last_page || (i >= meta.current_page - 1 && i <= meta.current_page + 1)) {
                html += `<button class="page-btn ${i === meta.current_page ? 'active' : ''}" onclick="${funcName}(${i})">${i}</button>`;
            } else if (i === meta.current_page - 2 || i === meta.current_page + 2) {
                html += `<span style="padding: 0 4px; color: var(--mn-brown-subtle);">...</span>`;
            }
        }

        html += `<button class="page-btn" ${meta.current_page === meta.last_page ? 'disabled' : ''} onclick="${funcName}(${meta.current_page + 1})"><i class="fa-solid fa-chevron-right"></i></button>`;
        html += '</div>';
        wrap.innerHTML = html;
    }
</script>
@endsection
